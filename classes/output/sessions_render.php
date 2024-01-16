<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_table\dynamic as dynamic_table;

require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/notify_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helpers/attendance.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/filters/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/sessions_form.php');
$PAGE->requires->js_call_amd('mod_hybridteaching/sessions', 'init');

class hybridteaching_sessions_render extends \table_sql implements dynamic_table {
    const EMPTY = "-";
    protected $hybridteaching;
    protected $typelist;
    protected $cm;
    protected $context;
    protected $coursecontext;

    public function __construct(stdClass $hybridteaching, int $typelist) {
        $this->hybridteaching = $hybridteaching;
        $this->typelist = $typelist;
        if (!empty($this->hybridteaching)) {
            $this->cm = get_coursemodule_from_instance('hybridteaching', $this->hybridteaching->id);
            $this->context = context_module::instance($this->cm->id);
            $this->coursecontext = context_course::instance($this->cm->course);
        }
    }

    /**
     * Builds the XHTML to display the control
     *
     * @param string $query
     * @return string
     */
    public function print_sessions_table() {
        global $OUTPUT, $CFG, $DB, $PAGE;

        $id = required_param('id', PARAM_INT);
        $hybridteachingid = optional_param('h', 0, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $perpage = optional_param('perpage', get_config('hybridteaching', 'resultsperpage'), PARAM_INT);
        $sort = optional_param('sort', 'starttime', PARAM_ALPHANUMEXT);
        $dir = optional_param('dir', 'DESC', PARAM_ALPHA);
        $slist = optional_param('l', 1, PARAM_INT);

        $columns = [
            'strgroup' => get_string('group'),
            'strtype' => get_string('type', 'hybridteaching'),
            'strname' => get_string('name'),
            'strdate' => get_string('date'),
            'strstart' => get_string('start', 'hybridteaching'),
            'strduration' => get_string('duration', 'hybridteaching'),
            'strrecording' => get_string('recording', 'hybridteaching'),
            'strchats' => get_string('chats', 'hybridteaching'),
            'strattendance' => get_string('attendance', 'hybridteaching'),
            'strmaterials' => get_string('materials', 'hybridteaching'),
            'stroptions' => get_string('actions', 'hybridteaching'),
            'attexempt' => get_string('attexempt', 'hybridteaching'),
        ];

        $sortexclusions = ['strrecording', 'strattendance', 'strmaterials', 'stroptions', 'strstart', 'strchats'];
        foreach ($columns as $key => $column) {
            $columnnames = $this->get_column_name($key);
            if ($sort != $columnnames) {
                $columnicon = "";
                $columndir = "ASC";
            } else {
                $columndir = $dir == "ASC" ? "DESC" : "ASC";
                $columnicon = ($dir == "ASC") ? "sort_asc" : "sort_desc";
                $columnicon = $OUTPUT->pix_icon('t/' . $columnicon,
                    get_string(strtolower($columndir)), 'core', ['class' => 'iconsort']);
            }
            if (!in_array($key, $sortexclusions)) {
                $columns[$key] = "<a href=\"sessions.php?sort=".$columnnames.
                    "&dir=$columndir&id=$id&l=".$this->typelist."\">".$column."</a>$columnicon";
            }
        }

        $columns['mastercheckbox'] = new \core\output\checkbox_toggleall('sessions-table', true, [
            'id' => 'select-all-sessions',
            'name' => 'select-all-sessions',
            'label' => get_string('selectall'),
            'labelclasses' => 'sr-only',
            'classes' => 'm-1',
            'checked' => false,
        ]);

        $this->check_session_filters();
        $sfiltering = new session_filtering();
        $params = [];
        list($extrasql, $params) = $sfiltering->get_sql_filter();
        if ($this->hybridteaching->sessionscheduling) {
            $params = $params + ['starttime' => time()];
        }

        if (!has_capability('mod/hybridteaching:viewhiddenitems', $this->context)) {
            $visibleitems = " visible = :visible ";
            $params = $params + ['visible' => 1];
            !empty($extrasql) ? $extrasql = $extrasql . ' AND ' . $visibleitems : $extrasql = $extrasql . $visibleitems;
        }

        $groupmode = groups_get_activity_groupmode($this->cm);
        if (!has_capability('mod/hybridteaching:viewallsessions', $this->context) && $groupmode == SEPARATEGROUPS) {
            list($extragroup, $paramsgroup) = $this->get_group_filter();
            $params = $params + $paramsgroup;
            !empty($extrasql) ? $extrasql = $extrasql . ' AND ' . $extragroup : $extrasql = $extrasql . $extragroup;
        }

        $optionsformparams = [
            'id' => $id,
            'l' => $this->typelist,
        ];
        $optionsform = new session_options_form(null, $optionsformparams);

        $return = $OUTPUT->box_start('generalbox');
        $baseurl = new moodle_url('/mod/hybridteaching/sessions.php', ['id' => $id, 'l' => $this->typelist,
            'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, ]);

        $table = new html_table();
        $table->head = $this->get_table_header($columns);
        $table->colclasses = ['leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign', ];
        $table->id = 'hybridteachingsessions';
        $table->attributes['class'] = 'sessionstable generaltable';
        $table->data = [];

        $url = new moodle_url('/mod/hybridteaching/classes/action/session_action.php', ['sesskey' => sesskey()]);
        $sessioncontroller = new sessions_controller($this->hybridteaching);
        $operator = $this->get_operator();
        $sessionslist = $sessioncontroller->load_sessions($page, $perpage, $params, $extrasql, $operator, $sort, $dir);
        $sessionscount = $sessioncontroller->count_sessions($params, $operator);
        $return .= $OUTPUT->paging_bar($sessionscount, $page, $perpage, $baseurl);

        $returnurl = new moodle_url('/mod/hybridteaching/sessions.php?id='.$this->cm->id.'&l='.$this->typelist);
        $cache = cache::make('mod_hybridteaching', 'sessatt');
        foreach ($sessionslist as $session) {
            $date = $session['starttime'];
            $sessionid = $session['id'];
            $hour = date('H:i', $date);
            if (!empty($date)) {
                if ($this->typelist == SESSION_LIST) {
                    $date = date('l, j \d\e F \d\e Y, H:i', $date);
                } else {
                    $date = date('l, j \d\e F \d\e Y', $date);
                }
            } else {
                $date = self::EMPTY;
            }

            $sessatt = [];
            $sessatt['sessatt_string'] = '';
            if ($this->hybridteaching->useattendance) {
                $cachekey = $sessionid . '_' . $session['groupid'];
                $sessatt = $cache->get($cachekey);

                if ($sessatt === false) {
                    $this->hybridteaching->context = $this->context;
                    $this->hybridteaching->coursecontext = $this->coursecontext;
                    if (!empty($att = attendance::calculate_session_att($this->hybridteaching, $sessionid, $session['groupid']))) {
                        $sessatt = $att;
                    }
                    $cache->set($cachekey, $sessatt);
                }
            }

            $materialsinfo = $this->get_files_url($session);
            $recordingbutton = $materialsinfo['recordingbutton'];
            $fileurls = $materialsinfo['fileurls'];
            $chaturls = $this->get_chats_url($session);

            $body = [
                'class' => '',
                'sessionid' => $session['id'],
                'group' => $session['groupid'] == 0 ? get_string('allgroups', 'hybridteaching')
                    : groups_get_group($session['groupid'])->name,
                'name' => $session['name'],
                'typevc' => $session['typevc'],
                'description' => $session['description'],
                'date' => $date,
                'hour' => $hour,
                'attexempt' => html_writer::checkbox('attexempt[]', $session['attexempt'],
                    $session['attexempt'], '', ['class' => 'attexempt', 'data-id' => $sessionid]),
                'duration' => !empty($session['duration']) && $session['duration'] > 0 ?
                    helper::get_hours_format($session['duration']) : self::EMPTY,
                'recordingbutton' => $recordingbutton,
                'attendance' => is_array($sessatt) && isset($sessatt['sessatt_string']) ? $sessatt['sessatt_string'] : '',
                'materials' => $fileurls,
                'chaturls' => $chaturls,
                'enabled' => $session['visible'],
                'visiblechat' => $session['visiblechat'],
                'visiblerecord' => $session['visiblerecord'],
                'checkbox' => new \core\output\checkbox_toggleall('sessions-table', false, [
                    'id' => 'session-' . $sessionid,
                    'name' => 'session[]',
                    'classes' => 'm-1',
                    'checked' => false,
                    'value' => $sessionid,
                ]),
            ];

            $hybridteachingid = empty($hybridteaching) ? $this->cm->instance : $hybridteachingid;
            $params = ['sid' => $sessionid, 'h' => $hybridteachingid, 'id' => $id, 'l' => $slist];
            $urledit = '/mod/hybridteaching/programsessions.php?id='.$this->cm->id .'&s='.$sessionid;

            $options = $this->get_table_options($body, $params, $url, $urledit);
            $class = $options['class'];
            $options = $options['options'];
            // Add a row to the table.
            $row = $this->get_session_row($body, $options);
            if (!empty($class)) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        echo $OUTPUT->render_from_template('mod_hybridteaching/sessionsmodal', null);

        // Add filters.
        $sfiltering->display_add();
        $sfiltering->display_active();
        $optionsform->display();

        $sessiontable = html_writer::table($table);
        if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
            $sessiontable .= $this->get_bulk_options_select();
        }

        $sessiontable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
        $sessiontable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
        $sessiontable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'l', 'value' => $this->typelist]);
        $return .= html_writer::tag('form', $sessiontable, ['method' => 'post',
            'action' => new moodle_url('/mod/hybridteaching/classes/action/session_action.php'), ]);
        $return .= $OUTPUT->paging_bar($sessionscount, $page, $perpage, $baseurl);
        $return .= $OUTPUT->box_end();
        notify_controller::show();

        return $return;
    }

    public function get_table_header($columns) {
        global $OUTPUT;
        $header = [];
        if ($this->typelist == SESSION_LIST) {
                $header = [
                    (has_capability('mod/hybridteaching:sessionsfulltable', $this->context))
                        ? $OUTPUT->render($columns['mastercheckbox']) : '',
                    $columns['strgroup'],
                    (has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) ? $columns['strtype'] : '',
                    $columns['strname'],
                    $columns['strdate'],
                    $columns['strduration'],
                    (has_capability('mod/hybridteaching:viewrecordings', $this->context)) ? $columns['strrecording'] : '',
                    $columns['strchats'],
                   (has_capability('mod/hybridteaching:sessionsfulltable', $this->context) && $this->hybridteaching->grade) ?
                        $columns['strattendance'] : '',
                    (has_capability('mod/hybridteaching:sessionsfulltable', $this->context) && $this->hybridteaching->grade) ?
                        $columns['attexempt'] : '',
                    $columns['strmaterials'],
                    $columns['stroptions'],
                ];
        } else if ($this->typelist == PROGRAM_SESSION_LIST) {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strdate'],
                $columns['strstart'],
                $columns['strduration'],
                $columns['strgroup'],
                $columns['strname'],
                ($this->hybridteaching->grade) ? $columns['attexempt'] : '',
                $columns['strmaterials'],
                $columns['stroptions'],
            ];
        }
        return $header;
    }

    public function get_operator() {
        $operator = sessions_controller::OPERATOR_GREATER_THAN;
        if ($this->typelist == SESSION_LIST) {
            $operator = sessions_controller::OPERATOR_LESS_THAN;
        }

        return $operator;
    }

    public function get_table_options($body, $params, $url, $urledit) {
        $options = '';
        $arrayoptions = $this->build_options($body, $params, $url, $urledit);
        if ($this->typelist == SESSION_LIST) {
            if (has_capability('mod/hybridteaching:sessionsactions', $this->context)) {
                $options .= $arrayoptions['visible'] . $arrayoptions['delete']. $arrayoptions['info'] .
                    $arrayoptions['record'] . $arrayoptions['chats'] . $arrayoptions['attendance'];
            }
        } else if ($this->typelist == PROGRAM_SESSION_LIST) {
            $options .= $arrayoptions['edit'] . $arrayoptions['info']. $arrayoptions['delete'];
        }
        return ['options' => $options, 'class' => $arrayoptions['class']];
    }

    public function build_options($body, $params, $url, $urledit) {
        global $OUTPUT;

        $class = '';
        if ($body['enabled']) {
            $visible = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'disable'])),
                $OUTPUT->pix_icon('t/hide', get_string('disable'), 'moodle', ['class' => 'iconsmall']));
        } else {
            $visible = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'enable'])),
                $OUTPUT->pix_icon('t/show', get_string('enable'), 'moodle', ['class' => 'iconsmall']));
            $class = 'dimmed_text';
        }

        $edit = html_writer::link(new moodle_url($urledit),
            $OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle', ['class' => 'iconsmall']));

        $delete = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'delete'])),
            $OUTPUT->pix_icon('t/delete', get_string('delete'), 'moodle', ['class' => 'iconsmall']),
            ['onclick' => 'if (!confirm("'.get_string('deleteconfirm', 'hybridteaching', $body['name']).'"))
            { return false; }', ]
        );

        $info = '';
        if ($this->typelist == SESSION_LIST) {
            $info .= html_writer::link('',
                $OUTPUT->pix_icon('docs', get_string('actions', 'hybridteaching')),
                ['class' => 'sessinfo', 'data-id' => $body['sessionid'], 'data-toggle' => 'modal',
                'data-target' => '#sessinfomodal', ]);
        }

        if ($body['visiblerecord']) {
            $record = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'visiblerecord'])),
                $OUTPUT->pix_icon('i/messagecontentvideo', get_string('hiderecords', 'hybridteaching'),
                    'moodle', ['class' => 'iconsmall']));
        } else {
            $record = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'visiblerecord'])),
                $OUTPUT->pix_icon('i/messagecontentvideo', get_string('visiblerecords', 'hybridteaching'),
                    'moodle', ['class' => 'iconsmall fa-film-slash']));
        }

        if ($body['visiblechat']) {
            $chats = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'visiblechat'])),
                $OUTPUT->pix_icon('t/message', get_string('hidechats', 'hybridteaching'),
                    'moodle', ['class' => 'iconsmall']));
        } else {
            $chats = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'visiblechat'])),
                $OUTPUT->pix_icon('t/message', get_string('visiblechats', 'hybridteaching'),
                    'moodle', ['class' => 'iconsmall fa-comment-slash']));
        }

        $attendance = html_writer::link(new moodle_url(''),
            $OUTPUT->pix_icon('i/unchecked', get_string('actions', 'hybridteaching'), 'moodle', ['class' => 'iconsmall']));

        $options = [
            'visible' => $visible,
            'class' => $class,
            'edit' => $edit,
            'delete' => $delete,
            'info' => $info,
            'record' => $record,
            'chats' => $chats,
            'attendance' => $attendance,
        ];

        return $options;
    }

    public function get_session_row($params, $options) {
        global $OUTPUT;
        $type = $params['typevc'];
        $typealias = '';
        if (has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) {
            if (!empty($type)) {
                $typealias = get_string('alias', 'hybridteachvc_'.$type);
            } else {
                $typealias = self::EMPTY;
            }
        }

        $row = '';
        if ($this->typelist == SESSION_LIST) {
            $row = new html_table_row(
                [
                    (has_capability('mod/hybridteaching:sessionsfulltable', $this->context))
                        ? $OUTPUT->render($params['checkbox']) : '',
                    $params['group'],
                    $typealias,
                    $params['name'],
                    $params['date'],
                    $params['duration'],
                    (has_capability('mod/hybridteaching:viewrecordings', $this->context)) ? $params['recordingbutton'] : '',
                    $params['chaturls'],
                    (has_capability('mod/hybridteaching:sessionsfulltable', $this->context) && $this->hybridteaching->grade) ?
                        $params['attendance'] : '',
                    (has_capability('mod/hybridteaching:sessionsfulltable', $this->context) && $this->hybridteaching->grade) ?
                        $params['attexempt'] : '',
                    $params['enabled'] ? $params['materials'] : '',
                    $options, ]);
        } else if ($this->typelist == PROGRAM_SESSION_LIST) {
            $row = new html_table_row([$OUTPUT->render($params['checkbox']), $params['date'], $params['hour'],
                $params['duration'], $params['group'], $params['name'], 
                    ($this->hybridteaching->grade) ? $params['attexempt'] : '', $params['materials'], $options, ]);
        }

        return $row;
    }

    public function get_column_name($column) {
        switch ($column) {
            case 'strgroup':
                return 'groupid';
                break;
            case 'strtype':
                return 'typevc';
                break;
            case 'strdate':
                return 'starttime';
                break;
            case 'strduration':
                return 'duration';
                break;
            case 'strname':
                return 'name';
                break;
            default:
                return $column;
                break;
        }
    }

    public function check_session_filters() {
        global $SESSION;
        if (isset($_GET['l']) && !isset($_GET['sort'])) {
            unset($SESSION->session_filtering);
        }
    }

    public function get_bulk_options_select() {
        $selectactionparams = [
            'id' => 'sessionid',
            'class' => 'ml-2',
            'data-action' => 'toggle',
            'data-togglegroup' => 'sessions-table',
            'data-toggle' => 'action',
            'disabled' => 'disabled',
        ];

        if ($this->typelist == SESSION_LIST) {
            $options = [
                'bulkdelete' => get_string('deletesessions', 'hybridteaching'),
            ];
        } else if ($this->typelist == PROGRAM_SESSION_LIST) {
            $options = [
                'bulkupdateduration' => get_string('updatesesduration', 'hybridteaching'),
                'bulkupdatestarttime' => get_string('updatesesstarttime', 'hybridteaching'),
                'bulkdelete' => get_string('deletesessions', 'hybridteaching'),
            ];
        }

        $attributes = [
            'type'  => 'submit',
            'name'  => 'go',
            'value' => get_string('go', 'hybridteaching'),
            'class' => 'btn btn-secondary', ];
        $submitb = html_writer::empty_tag('input', $attributes);

        $label = html_writer::tag('label', get_string("withselectedsessions", 'hybridteaching'),
            ['for' => 'sessionid', 'class' => 'col-form-label d-inline']);
        $select = html_writer::select($options, 'action', '', ['' => 'choosedots'], $selectactionparams);

        return html_writer::tag('div', $label . $select . $submitb);
    }

    public function print_sessions_bulk_table($slist) {
        global $OUTPUT, $DB, $PAGE;

        [$insql, $params] = $DB->get_in_or_equal($slist, SQL_PARAMS_NAMED, 'id');
        $extrasql = 'id ' . $insql;

        $columns = [
            'strname' => get_string('name'),
            'strdate' => get_string('date'),
            'strstart' => get_string('start', 'hybridteaching'),
            'strduration' => get_string('duration', 'hybridteaching'),
        ];

        $return = $OUTPUT->heading(get_string('withselectedsessions', 'hybridteaching'));
        $return .= $OUTPUT->box_start('generalbox');

        $table = new html_table();
        $table->head = $columns;
        $table->colclasses = ['leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign', ];
        $table->id = 'hybridteachingsessions';
        $table->attributes['class'] = 'sessionstable generaltable';
        $table->data = [];

        $sessioncontroller = new sessions_controller($this->hybridteaching);
        $sessionslist = $sessioncontroller->load_sessions(0, 0, $params, $extrasql);

        foreach ($sessionslist as $session) {
            $date = $session['starttime'];
            $hour = date('H:i', $date);
            if ($this->typelist == SESSION_LIST) {
                $date = date('l, j \d\e F \d\e Y, H:i', $date);
            } else {
                $date = date('l, j \d\e F \d\e Y', $date);
            }
            $body = [
                'name' => $session['name'],
                'date' => $date,
                'hour' => $hour,
                'duration' => helper::get_hours_format($session['duration']),
            ];

            // Add a row to the table.
            $row = new html_table_row($body);
            $table->data[] = $row;
        }

        $return .= html_writer::table($table);
        $return .= $OUTPUT->box_end();

        return $return;
    }

    public function get_group_filter() {
        global $DB, $USER, $COURSE;
        $groups = groups_get_all_groups($COURSE->id, $USER->id, $this->cm->groupingid);
        $grouparray[0] = 0;
        foreach ($groups as $group) {
            $grouparray[$group->id] = $group->id;
        }

        [$insql, $params] = $DB->get_in_or_equal($grouparray, SQL_PARAMS_NAMED, 'groupid');
        $extrasql = 'groupid ' . $insql;
        return [$extrasql, $params];
    }

    public function get_files_url($session) {
        global $CFG, $OUTPUT;

        $fileurls = '';
        $recordingbutton = get_string('norecording', 'hybridteaching');
        if (has_capability('mod/hybridteaching:viewrecordings', $this->context)) {
            if (!has_capability('mod/hybridteaching:sessionsactions', $this->context) && $session['visiblerecord'] == 0) {
                $recordingbutton = get_string('norecording', 'hybridteaching');
            } else {
                if ($session['userecordvc'] == 1 && $session['processedrecording'] >= 0) {
                    if ($session['storagereference'] > 0) {
                        $classstorage = sessions_controller::get_subpluginstorage_class($session['storagereference']);
                        $config = helper::subplugin_config_exists($session['storagereference'], 'store');

                        if ($config) {
                            sessions_controller::require_subplugin_store($classstorage['type']);
                            $classname = $classstorage['classname'];
                            $sessionrecording = new $classname();
                            if (method_exists($sessionrecording, 'get_recording')) {
                                $urlrecording = $CFG->wwwroot . '/mod/hybridteaching/loadrecording.php?cid='.
                                    $this->hybridteaching->course.'&sid='.$session['id'].'&id='.$this->cm->id;
                                $recordingbutton = html_writer::link($urlrecording, get_string('watchrecording',
                                    'mod_hybridteaching'), ['target' => '_blank', 'class' => 'btn btn-secondary']);
                            }

                            // Download recordings.
                            // In case is not permitted download recordings with some subplugin.
                            if (method_exists($sessionrecording, 'download_recording')) {
                                if (has_capability('mod/hybridteaching:downloadrecordings', $this->context)) {
                                    // You can download if can manage sessionsfulltable (supposed teacher)
                                    // or view and downloadrecords in ht (supposed student).
                                    if ($this->hybridteaching->downloadrecords && has_capability('mod/hybridteaching:view',
                                        $this->context) || has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) {
                                        $downloadrecording = $CFG->wwwroot . '/mod/hybridteaching/loadrecording.php?cid='.
                                            $this->hybridteaching->course.'&sid='.$session['id'].'&id='.$this->cm->id.'&download=true';
                                        $downloadrecordingbutton = html_writer::link
                                            (new moodle_url($downloadrecording), $OUTPUT->pix_icon('t/download',
                                            get_string('download'), 'moodle', ['class' => 'iconsmall']));

                                        $recordingbutton .= ' '.$downloadrecordingbutton;
                                    }
                                }
                            }
                        }
                    }

                    $config = helper::subplugin_config_exists($session['vcreference'], 'vc');
                    if ($config) {
                        // Check if exists recording meeting to get the url.
                        sessions_controller::require_subplugin_session($session['typevc']);
                        $classname = sessions_controller::get_subpluginvc_class($session['typevc']);
                        $sessionrecording = new $classname($session['id']);

                        // Get recording if there are subplugin without api to download recordings (ex. BBB).
                        // Then, get the recording from url from vc subplugin or similar.
                        if ($session['storagereference'] == -1) {
                            if (method_exists($sessionrecording, 'get_recording')) {
                                $urlrecording = $sessionrecording->get_recording($this->context);
                                if (isset($urlrecording['recording']) && $urlrecording['recording'] != '') {
                                    $recordingbutton = html_writer::link($urlrecording['recording'], get_string('watchrecording',
                                        'mod_hybridteaching'), ['target' => '_blank', 'class' => 'btn btn-secondary']);
                                }
                                if (isset($urlrecording['materials']) && $urlrecording['materials'] != '') {
                                    $fileurls .= html_writer::tag('a', get_string('notesurlmeeting', 'hybridteaching'),
                                    ['href' => $urlrecording['materials'], 'target' => '_blank']);
                                }
                            }
                        }
                    }
                }
            }
        }

        $fileurl = '';
        if (!empty($session['sessionfiles'])) {
            $filestorage = new file_storage();
            $files = $filestorage->get_area_files($this->context->id, 'mod_hybridteaching', 'session', $session['id']);
            if ($fileurls != '') {
                $fileurls .= html_writer::start_tag('br');
            }
            foreach ($files as $file) {
                if (!empty($file) && $file->get_filename() != '.') {
                    $fileurl = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename(),
                        true
                    );
                    $fileurls .= html_writer::tag('a', $file->get_filename(), ['href' => $fileurl])
                        . html_writer::start_tag('br');
                }
            }
        }

        return ['recordingbutton' => $recordingbutton, 'fileurls' => $fileurls];
    }

    public function get_chats_url($session) {
        $fileurls = '';
        $fileurl = '';
        $filestorage = new file_storage();
        if (!has_capability('mod/hybridteaching:viewchat', $this->context) ||
              (!has_capability('mod/hybridteaching:sessionsactions', $this->context) && $session->visiblechat == 0)) {
            return '';
        }
        $files = $filestorage->get_area_files($this->context->id, 'mod_hybridteaching', 'chats', $session['id']);
        if (!empty($files)) {
            if ($fileurls != '') {
                $fileurls .= html_writer::start_tag('br');
            }
            foreach ($files as $file) {
                if (!empty($file) && $file->get_filename() != '.') {
                    $fileurl = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename(),
                        true
                    );
                    $fileurls .= html_writer::tag('a', $file->get_filename(), ['href' => $fileurl])
                        . html_writer::start_tag('br');
                }
            }
        }

        $config = helper::subplugin_config_exists($session['vcreference'], 'vc');
        if (is_object($config) || ($config != 0 && $config != -1)) {
            // Check if exists urlchat meeting to get the url.
            sessions_controller::require_subplugin_session($session['typevc']);
            $classname = sessions_controller::get_subpluginvc_class($session['typevc']);
            $sessionrecording = new $classname($session['id']);
            $urlchatmeeting = $sessionrecording->get_chat_url($this->context);
            if ($urlchatmeeting != '') {
                $fileurls .= html_writer::tag('a', get_string('chaturlmeeting', 'hybridteaching'),
                    ['href' => $urlchatmeeting, 'target' => '_blank']);
            }
        }

        return $fileurls;
    }
}
