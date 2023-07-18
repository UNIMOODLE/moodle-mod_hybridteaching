<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     mod_hybridteaching
 * @category    admin
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_table\dynamic as dynamic_table;

require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/filters/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/sessions_form.php');
$PAGE->requires->js_call_amd('mod_hybridteaching/sessions', 'init');

class hybridteaching_sessions_render extends \table_sql implements dynamic_table {
    protected $hybridteaching;
    protected $typelist;
    protected $cm;
    protected $context;

    public function __construct(stdClass $hybridteaching, int $typelist) {
        $this->hybridteaching = $hybridteaching;
        $this->typelist = $typelist;
        if (!empty($this->hybridteaching)) {
            $this->cm = get_coursemodule_from_instance('hybridteaching', $this->hybridteaching->id);
            $this->context = context_module::instance($this->cm->id);
        }
    }

    /**
     * Builds the XHTML to display the control
     *
     * @param string $query
     * @return string
     */
    public function print_sessions_table() {
        global $OUTPUT, $DB, $PAGE;

        $id = required_param('id', PARAM_INT);
        $hybridteachingid = optional_param('h', 0, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $perpage = optional_param('perpage', 10, PARAM_INT);
        $sort = optional_param('sort', 'name', PARAM_ALPHANUMEXT);
        $dir = optional_param('dir', 'ASC', PARAM_ALPHA);

        $columns = [
            'strgroup' => get_string('group'),
            'strtype' => get_string('type', 'mod_hybridteaching'),
            'strname' => get_string('name'),
            'strdate' => get_string('date'),
            'strstart' => get_string('start', 'mod_hybridteaching'),
            'strduration' => get_string('duration', 'mod_hybridteaching'),
            'strrecording' => get_string('recording', 'mod_hybridteaching'),
            'strattendance' => get_string('attendance', 'mod_hybridteaching'),
            'strmaterials' => get_string('materials', 'mod_hybridteaching'),
            'stroptions' => get_string('actions', 'mod_hybridteaching')
        ];

        $sortexclusions = ['strrecording', 'strattendance', 'strmaterials', 'stroptions', 'strstart'];
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

        $groupmode = groups_get_activity_groupmode($this->cm);
        if (!has_capability('mod/hybridteaching:sessionsfulltable', $this->context) && $groupmode == SEPARATEGROUPS) {
            list($extragroup, $paramsgroup) = $this->get_group_filter();
            $params = $params + $paramsgroup;
            !empty($extrasql) ? $extrasql = $extrasql . ' AND ' . $extragroup : $extrasql = $extrasql . $extragroup;
        }

        $optionsformparams = [
            'id' => $id,
            'l' => $this->typelist
        ];
        $optionsform = new session_options_form(null, $optionsformparams);

        $return = $OUTPUT->box_start('generalbox');

        $table = new html_table();
        $table->head = $this->get_table_header($columns);
        $table->colclasses = array('leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'hybridteachingsessions';
        $table->attributes['class'] = 'sessionstable generaltable';
        $table->data = array();

        $url = new moodle_url('/mod/hybridteaching/classes/action/session_action.php', array('sesskey' => sesskey()));
        $sessioncontroller = new sessions_controller($this->hybridteaching);
        $operator = $this->get_operator();
        $sessionslist = $sessioncontroller->load_sessions($page, $perpage, $params, $extrasql, $operator, $sort, $dir);
        $sessionscount = $sessioncontroller->count_sessions($params, $operator);

        $returnurl = new moodle_url('/mod/hybridteaching/sessions.php?id='.$this->cm->id.'&l='.$this->typelist);
        foreach ($sessionslist as $session) {
            $date = $session['starttime'];
            $sessionid = $session['id'];
            $hour = date('H:i', $date);
            if ($this->typelist == SESSION_LIST) {
                $date = date('l, j \d\e F \d\e Y, H:i', $date);
            } else {
                $date = date('l, j \d\e F \d\e Y', $date);
            }
            $body = [
                'class' => '',
                'sessionid' => $session['id'],
                'group' => $session['groupid'] == 0 ? get_string('commonsession', 'hybridteaching') : groups_get_group($session['groupid'])->name,
                'name' => $session['name'],
                'description' => $session['description'],
                'date' => $date,
                'hour' => $hour,
                'duration' => helper::get_hours_format($session['duration']),
                'recordingbutton' => html_writer::start_tag('input', ['type' => 'button', 'value' => get_string('recording', 'mod_hybridteaching')]),
                'attendance' => '75/100 (75%)',
                'materials' => 'Recursos',
                'enabled' => $session['visible'],
                'checkbox' => new \core\output\checkbox_toggleall('sessions-table', false, [
                    'id' => 'session-' . $sessionid,
                    'name' => 'session[]',
                    'classes' => 'm-1',
                    'checked' => false,
                    'value' => $sessionid
                ])
            ];

            $hybridteachingid = empty($hybridteaching) ? $this->cm->instance : $hybridteachingid;
            $params = array('sid' => $sessionid, 'h' => $hybridteachingid, 'id' => $id, 'returnurl' => $returnurl);
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

        // add filters
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
        $return .= html_writer::tag('form', $sessiontable, array('method' => 'post', 
            'action' => '/mod/hybridteaching/classes/action/session_action.php'));
        $baseurl = new moodle_url('/mod/hybridteaching/sessions.php', array('id' => $id, 'l' => $this->typelist, 
            'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));
        $return .= $OUTPUT->paging_bar($sessionscount, $page, $perpage, $baseurl);
        $return .= $OUTPUT->box_end();

        return $return;
    }

    public function get_table_header($columns) {
        global $OUTPUT;
        $header = [];
        if ($this->typelist == SESSION_LIST) {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strgroup'],
                (has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) ? $columns['strtype'] : '',
                $columns['strname'],
                $columns['strdate'],
                $columns['strduration'],
                $columns['strrecording'],
                $columns['strattendance'],
                $columns['strmaterials'],
                $columns['stroptions']
            ];
        } else if ($this->typelist == PROGRAM_SESSION_LIST) {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strdate'],
                $columns['strstart'],
                $columns['strduration'],
                $columns['strgroup'],
                $columns['strname'],
                $columns['strmaterials'],
                $columns['stroptions']
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
                    $arrayoptions['record'] . $arrayoptions['attendance'];
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
            $visible = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'disable'))),
                $OUTPUT->pix_icon('t/hide', get_string('disable'), 'moodle', array('class' => 'iconsmall')));
        } else {
            $visible = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'enable'))),
                $OUTPUT->pix_icon('t/show', get_string('enable'), 'moodle', array('class' => 'iconsmall')));
            $class = 'dimmed_text';
        }

        $edit = html_writer::link(new moodle_url($urledit),
            $OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle', array('class' => 'iconsmall')));

        $delete = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'delete'))),
            $OUTPUT->pix_icon('t/delete', get_string('delete'), 'moodle', array('class' => 'iconsmall')),
            array('onclick' => 'if (!confirm("'.get_string('deleteconfirm', 'mod_hybridteaching', $body['name']).'"))
            { return false; }')
        );

        $info = html_writer::link(new moodle_url(''),
            $OUTPUT->pix_icon('docs', get_string('actions', 'mod_hybridteaching'), 'moodle', array('class' => 'iconsmall')));

        $record = html_writer::link(new moodle_url(''),
            $OUTPUT->pix_icon('i/messagecontentvideo', get_string('actions', 'mod_hybridteaching'),
                'moodle', array('class' => 'iconsmall')));

        $attendance = html_writer::link(new moodle_url(''),
            $OUTPUT->pix_icon('i/unchecked', get_string('actions', 'mod_hybridteaching'), 'moodle', array('class' => 'iconsmall')));

        $options = [
            'visible' => $visible,
            'class' => $class,
            'edit' => $edit,
            'delete' => $delete,
            'info' => $info,
            'record' => $record,
            'attendance' => $attendance
        ];

        return $options;
    }

    public function get_session_row($params, $options) {
        global $OUTPUT;
        $type = $this->hybridteaching->typevc;
        $typealias = '';
        if (!empty($type) && has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) {
            $typealias = get_string('alias', 'hybridteachvc_'.$type);
        }

        $row = '';
        if ($this->typelist == SESSION_LIST) {
            $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['group'], $typealias,
                $params['name'], $params['date'], $params['duration'], $params['recordingbutton'],
                $params['attendance'], $params['materials'], $options));
        } else if ($this->typelist == PROGRAM_SESSION_LIST) {
            $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['date'], $params['hour'],
                $params['duration'], $params['group'], $params['name'], $params['materials'], $options));
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
        $selectactionparams = array(
            'id' => 'sessionid',
            'class' => 'ml-2',
            'data-action' => 'toggle',
            'data-togglegroup' => 'sessions-table',
            'data-toggle' => 'action',
            'disabled' => 'disabled'
        );

        if ($this->typelist == SESSION_LIST) {
            $options = [
                'bulkdelete' => get_string('deletesessions', 'hybridteaching')
            ];
        } else if ($this->typelist == PROGRAM_SESSION_LIST) {
            $options = [
                'bulkupdateduration' => get_string('updatesesduration', 'hybridteaching'),
                'bulkupdatestarttime' => get_string('updatesesstarttime', 'hybridteaching'),
                'bulkdelete' => get_string('deletesessions', 'hybridteaching')
            ];
        }

        $attributes = array(
            'type'  => 'submit',
            'name'  => 'go',
            'value' => get_string('go', 'hybridteaching'),
            'class' => 'btn btn-secondary');
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
            'strstart' => get_string('start', 'mod_hybridteaching'),
            'strduration' => get_string('duration', 'mod_hybridteaching')
        ];

        $return = $OUTPUT->heading(get_string('withselectedsessions', 'mod_hybridteaching'));
        $return .= $OUTPUT->box_start('generalbox');

        $table = new html_table();
        $table->head = $columns;
        $table->colclasses = array('leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'hybridteachingsessions';
        $table->attributes['class'] = 'sessionstable generaltable';
        $table->data = array();

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
                'duration' => helper::get_hours_format($session['duration'])
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
        return array($extrasql, $params);
    }
}
