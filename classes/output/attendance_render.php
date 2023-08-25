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

require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/attendance_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/filters/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/attendance_form.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/attsessions_form.php');
$PAGE->requires->js_call_amd('mod_hybridteaching/attendance', 'init');

class hybridteaching_attendance_render extends \table_sql implements dynamic_table {
    protected $hybridteaching;
    protected $cm;
    protected $context;

    public function __construct(stdClass $hybridteaching ) {
        $this->hybridteaching = $hybridteaching;
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
    public function print_attendance_table() {
        global $OUTPUT, $DB, $PAGE, $USER, $CFG;

        $id = required_param('id', PARAM_INT);
        $hybridteachingid = $this->hybridteaching->id;
        $page = optional_param('page', 0, PARAM_INT);
        $perpage = optional_param('perpage', get_config('hybridteaching', 'resultsperpage'), PARAM_INT);
        $sort = optional_param('sort', 'id', PARAM_ALPHANUMEXT);
        $dir = optional_param('dir', 'ASC', PARAM_ALPHA);
        $view = optional_param('view', 'sessionattendance', PARAM_TEXT);
        $sessionid = optional_param('sessionid', 0, PARAM_INT);
        $columns = [
            'strgroup' => get_string('group'),
            'strname' => get_string('name'),
            'strdate' => get_string('date'),
            'strduration' => get_string('duration', 'mod_hybridteaching'),
            'strtype' => get_string('type', 'mod_hybridteaching'),
            'strattendance' => get_string('attendance', 'mod_hybridteaching'),
            'strgrade' => get_string('gradenoun'),
            'stroptions' => get_string('actions', 'mod_hybridteaching'),
            'strvc' => get_string('videoconference', 'mod_hybridteaching'),
            'strclassroom' => get_string('classroom', 'mod_hybridteaching'),
            'strusername' => get_string('user'),
            'strpfp' => get_string('picture'),
            'strfirstlastname' => get_string('firstname') . ' / ' . get_string('lastname'),
            'strentrytime' => get_string('entrytime', 'hybridteaching'),
            'strleavetime' => get_string('leavetime', 'hybridteaching'),
            'strpermanence' => get_string('permanence', 'hybridteaching'),
        ];

        $params = [];
        $extrasql = [];
        $userid = $USER->id;
        $editing = 1;
        $viewsexclusion = ['studentattendance', 'extendedsessionatt',];
        if (!has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $userid, $doanything = true)) {
            var_dump(false, $userid);
            if (!in_array($view, $viewsexclusion)) {
                $view = 'studentattendance';
            }
            $editing = 0;
            $params['userid'] = $userid;
        }
        if ($view == 'extendedsessionatt') {
            var_dump('extended');
            $userpicture = $OUTPUT->user_picture($USER);
            $userurl = new moodle_url('/user/view.php', array('id' => $USER->id));
            $userlink = html_writer::link($userurl, $userpicture .' '. fullname($USER));
            echo $userlink;
            $sessoptionsformparams = [
                'id' => $id,
                'hid' => $hybridteachingid,
                'sessionid' => $sessionid,
            ];
            $sessionoptions = new attsessions_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                 $view, $sessoptionsformparams);
        }
        $sortexclusions = ['stroptions', 'strvc', 'strclassroom'];
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
                $columns[$key] = "<a href=\"attendance.php?sort=". $columnnames .
                "&dir=$columndir&id=$id&view=" . $view . "\">". $column ."</a>$columnicon";
            }
        }

        $columns['mastercheckbox'] = new \core\output\checkbox_toggleall('attendance-table', true, [
            'id' => 'select-all-attendance',
            'name' => 'select-all-attendance',
            'label' => get_string('selectall'),
            'labelclasses' => 'sr-only',
            'classes' => 'm-1',
            'checked' => false,
        ]);

        $this->check_attendance_filters();

        $groupmode = groups_get_activity_groupmode($this->cm);
        if (!has_capability('mod/hybridteaching:sessionsfulltable', $this->context) && $groupmode == SEPARATEGROUPS) {
            list($extragroup, $paramsgroup) = $this->get_group_filter();
            $params = $params + $paramsgroup;
            !empty($extrasql) ? $extrasql = $extrasql . ' AND ' . $extragroup : $extrasql = $extrasql . $extragroup;
        }
        $optionsformparams = [
            'id' => $id,
        ];
        $optionsform = new attendance_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
             $view . '&sessionid=' . $sessionid , $optionsformparams);

        $return = $OUTPUT->box_start('generalbox');

        $table = new html_table();
        $table->head = $this->get_table_header($columns, $view, $editing);
        $table->colclasses = array('leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'hybridteachingattendance';
        $table->attributes['class'] = 'attendancetable generaltable';
        $table->data = array();

        $url = new moodle_url('/mod/hybridteaching/classes/action/attendance_action.php',
             array('sesskey' => sesskey(), 'view' => $view, 'sessionid' => $sessionid));
        $attendance_controller = new attendance_controller($this->hybridteaching);
        $sessions_controller = new sessions_controller($this->hybridteaching);
        $operator = $this->get_operator();
        $attendancelist = $attendance_controller->load_attendance($page, $perpage, $params, $extrasql,
         $operator, $sort, $dir, $view, $sessionid);
        $attendanceassist = $attendance_controller->load_attendance_assistance($page, $perpage, $params, $extrasql, $operator, $sort, $dir);
        $attendancecount = $attendance_controller->count_attendance($params, $operator);
        $returnurl = new moodle_url('/mod/hybridteaching/attendance.php?id='.$this->cm->id.'');

        foreach ($attendancelist as $attendance) {
            $session = $sessions_controller->get_session($attendance['sessionid']);
            $date = $session->starttime;
            $hour = date('H:i', $date);
            $attendanceid = $attendance['id'];
            $date = date('l, j \d\e F \d\e Y H:i', $date);
            foreach($attendanceassist as $attassist) {
                if ($attassist['sessionid'] == $attendance['sessionid']) {
                    $attassistance = $attassist;
                    continue;
                }
            }
            $sessionsuser = $attendance_controller->load_sessions_attendant($attendance);
            $attributes = array(
                'type'  => 'checkbox',
                'name'  => $session->name,
                'value' => get_string('active'),
                'class' => 'attendance-validated');
            $attendance['status'] == 1 ? $attributes['checked'] = true : $attributes['checked'] = false ;
            $submitb = html_writer::empty_tag('input', $attributes);
            
            $userpicture = $OUTPUT->user_picture($sessionsuser);
            $userurl = new moodle_url('/user/view.php', array('id' => $USER->id));
            $body = [ 
                'class' => '',
                'attendanceid' => $attendance['id'],
                'group' => $session->groupid == 0 ? get_string('commonattendance', 'hybridteaching') : groups_get_group($session->groupid)->name,
                'name' => $session->name,
                'username' => $sessionsuser->firstname . ' ' . $sessionsuser->lastname . ' ' . $userpicture,
                'date' => $date,
                'duration' => helper::get_hours_format($session->duration),
                'pfp' => $userpicture,
                'firstlastname' => $sessionsuser->firstname . ' / ' . $sessionsuser->lastname,
                'entrytime' => $hour,
                'leavetime' => date('H:i', $session->starttime + $session->duration),
                'permanence' => '4',
                'attendance' => $submitb . '<br>' . '45 min',
                'type' => $attendance['type'] == 0 ? get_string('classroom', 'hybridteaching') : get_string('videoconference', 'hybridteaching'),
                'grade' => $attendance['grade'] . ' / 10',
                'vc' => $attassistance['vc'],
                'classroom' => $attassistance['classroom'],
                'enabled' => $attendance['visible'],
                'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                    'id' => 'attendance-' . $attendanceid,
                    'name' => 'attendance[]',
                    'classes' => 'm-1',
                    'checked' => false,
                    'value' => $attendanceid
                ])
            ];
            $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
            $params = array('attid' => $attendanceid, 'h' => $hybridteachingid, 'id' => $id,
             'returnurl' => $returnurl, 'view' => $view, 'userid' => $userid, 'editing' => $editing);

            $options = $this->get_table_options($body, $params, $url, $attendance['sessionid']);
            $class = $options['class'];
            $options = $options['options'];
            // Add a row to the table.
            $row = $this->get_attendance_row($body, $options, $view, $editing);
            if (!empty($class)) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        // add filters
        $optionsform->display();
        if (isset($sessionoptions)) {
            $sessionoptions->display();
        }

        $attendancetable = html_writer::table($table);
        if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
            $attendancetable .= $this->get_bulk_options_select();
        }

        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
        $return .= html_writer::tag('form', $attendancetable, array('method' => 'post', 
            'action' => '/mod/hybridteaching/classes/action/attendance_action.php?view=' . $view . '&sessionid=' . $sessionid ));
        $baseurl = new moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, array('id' => $id, 
            'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid));
        $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
        $return .= $OUTPUT->box_end();

        return $return;
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
            case 'strattendance':
                return 'attendance';
                break;
            case 'strvc':
                return 'strvc';
                break;
            case 'strclassroom':
                return 'strclassroom';
                break;
            case 'strgrade':
                return 'grade';
                break;
            case 'strusername':
                return 'username';
                break;
            case 'strpfp':
                return 'picture';
                break;
            case 'strfirstlastname':
                return 'firstlastname';
                break;
            case 'strentrytime':
                return 'entrytime';
                break;
            case 'strleavetime':
                return 'leavetime';
                break;
            case 'strpermanence':
                return 'permanence';
                break;
            default:
                return $column;
                break;
        }
    }
    
    public function get_table_options($body, $params, $url, $sessionid) {
        $options = '';
        $arrayoptions = $this->build_options($body, $params, $url, $sessionid);
        if (has_capability('mod/hybridteaching:attendancesactions', $this->context)) {
            if ($params['view'] != 'sessionattendance') {
                $options .= $arrayoptions['view'];
            } else {
                $options .= $arrayoptions['view'] . $arrayoptions['visible'];
            }
        }
        return ['options' => $options, 'class' => $arrayoptions['class']];
    }

    public function get_table_header($columns, $headers, $editing) {
        global $OUTPUT;
        $header = [];
        if ($headers == 'sessionattendance') {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strgroup'],
                $columns['strname'],
                $columns['strdate'],
                $columns['strduration'],
                $columns['strvc'],
                $columns['strclassroom'],
                $columns['stroptions'],
            ];
        } else if ($headers == 'studentattendance') {
            if ($editing) {
                $header = [
                    $OUTPUT->render($columns['mastercheckbox']),
                    $columns['strgroup'],
                    $columns['strname'],
                    $columns['strusername'],
                    $columns['strdate'],
                    $columns['strduration'],
                    (has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) ? $columns['strtype'] : '',
                    $columns['strattendance'],
                    $columns['strgrade'],
                    $columns['stroptions'],
                ];
            } else {
                $header = [
                    $OUTPUT->render($columns['mastercheckbox']),
                    $columns['strgroup'],
                    $columns['strname'],
                    $columns['strdate'],
                    $columns['strduration'],
                    (has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) ? $columns['strtype'] : '',
                    $columns['strattendance'],
                    $columns['strgrade'],
                    $columns['stroptions'],
                ];
            }
        } else if ($headers == 'extendedsessionatt') {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strpfp'],
                $columns['strfirstlastname'],
                (has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) ? $columns['strtype'] : '',
                $columns['strentrytime'],
                $columns['strleavetime'],
                $columns['strpermanence'],
                $columns['strattendance'],
                $columns['strgrade'],
                $columns['stroptions'],
            ];
        }
        

        return $header;
    }

    public function build_options($body, $params, $url, $sessionid) {
        global $OUTPUT;

        $class = '';

        $view = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'view', 'sessionid' => $sessionid))),
            $OUTPUT->pix_icon('i/preview', get_string('view'), 'moodle', array('class' => 'iconsmall')),
        );
        if ($params['view'] == 'sessionattendance') {
            if ($body['enabled']) {
                $visible = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'disable'))),
                    $OUTPUT->pix_icon('i/hide', get_string('disable'), 'moodle', array('class' => 'iconsmall')));
            } else {
                $visible = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'enable'))),
                    $OUTPUT->pix_icon('i/show', get_string('enable'), 'moodle', array('class' => 'iconsmall')));
                $class = 'dimmed_text';
            }
            $options = [
                'view' => $view,
                'visible' => $visible,
                'class' => $class,
            ];
        } else {
            $options = [
                'view' => $view,
                'class' => $class,
            ];
        }

        return $options;
    }

    public function get_attendance_row($params, $options, $tableview, $editing) {
        global $OUTPUT;
        $type = $this->hybridteaching->typevc;
        $typealias = '';
        if (!empty($type) && has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) {
            $typealias = get_string('attendance', 'hybridteaching');
        }

        $row = '';
        switch($tableview) {
            case 'sessionattendance':
                $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['group'],
                $params['name'], $params['date'], $params['duration'], $params['vc'],
                $params['classroom'], $options));
                break;
            case 'studentattendance':
                if ($editing) {
                    $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['group'],
                    $params['name'], $params['username'], $params['date'], $params['duration'], $params['type'],$params['attendance'],
                    $params['grade'], $options));
                    break;
                }
                $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['group'],
                $params['name'], $params['date'], $params['duration'], $params['type'],$params['attendance'],
                $params['grade'], $options));
                break;
            case 'extendedsessionatt':
                $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['pfp'],
                $params['firstlastname'], $params['type'], $params['entrytime'], $params['leavetime'],
                $params['permanence'], $params['attendance'], $params['grade'], $options));
                break;
            default:
                $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['group'],
                $params['name'], $params['date'], $params['duration'], $params['type'],$params['attendance'],
                $params['grade'], $options));
                break;
        }
        
        return $row;
    }

    public function get_bulk_options_select() {
        $selectactionparams = array(
            'id' => 'attendanceid',
            'class' => 'ml-2',
            'data-action' => 'toggle',
            'data-togglegroup' => 'attendance-table',
            'data-toggle' => 'action',
            'disabled' => 'disabled'
        );

        $options = [
            'bulkupdateduration' => get_string('updatesesduration', 'hybridteaching'),
            'bulkupdatestarttime' => get_string('updatesesstarttime', 'hybridteaching'),
            'bulkdelete' => get_string('deletesessions', 'hybridteaching')
        ];

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

    public function get_operator() {
        $operator = attendance_controller::OPERATOR_GREATER_THAN;

        return $operator;
    }

    public function check_attendance_filters() {
        global $SESSION;
        if (!isset($_GET['sort'])) {
            unset($SESSION->attendance_filtering);
        }
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