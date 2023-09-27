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
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/filters/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/attendance_form.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/attsessions_form.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helpers/grades.php');
$PAGE->requires->js_call_amd('mod_hybridteaching/attendance', 'init');

class hybridteaching_attendance_render extends \table_sql implements dynamic_table {
    const EMPTY = "-";
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
        global $OUTPUT, $DB, $PAGE, $USER, $CFG, $COURSE;

        $id = required_param('id', PARAM_INT);
        $hybridteachingid = $this->hybridteaching->id;
        $page = optional_param('page', 0, PARAM_INT);
        $perpage = optional_param('perpage', get_config('hybridteaching', 'resultsperpage'), PARAM_INT);
        $sort = optional_param('sort', 'id', PARAM_ALPHANUMEXT);
        $dir = optional_param('dir', 'ASC', PARAM_ALPHA);
        $view = optional_param('view', 'sessionattendance', PARAM_TEXT);
        $sessionid = optional_param('sessionid', 0, PARAM_INT);
        $helpatt = $OUTPUT->help_icon('gradenoun', 'mod_hybridteaching');
        $selectedsession = optional_param('selectedsession', $sessionid, PARAM_INT);
        $attid = optional_param('attid', 1, PARAM_INT);
        $selecteduser = optional_param('selecteduser', $attid, PARAM_INT);
        $editing = optional_param('editing', 1, PARAM_INT);
        $userid = optional_param('userid', $USER->id, PARAM_INT);
        $attendance_controller = new attendance_controller($this->hybridteaching);
        $sessions_controller = new sessions_controller($this->hybridteaching);
        $columns = [
            'strgroup' => get_string('group'),
            'strname' => get_string('name'),
            'strdate' => get_string('date'),
            'strduration' => get_string('duration', 'mod_hybridteaching'),
            'strtype' => get_string('type', 'mod_hybridteaching'),
            'strattendance' => get_string('attendance', 'mod_hybridteaching'),
            'strgrade' => get_string('gradenoun', 'mod_hybridteaching') . $helpatt,
            'stroptions' => get_string('actions', 'mod_hybridteaching'),
            'strvc' => get_string('videoconference', 'mod_hybridteaching'),
            'strclassroom' => get_string('classroom', 'mod_hybridteaching'),
            'strusername' => get_string('user'),
            'strpfp' => get_string('picture'),
            'strfirstlastname' => get_string('firstname') . ' / ' . get_string('lastname'),
            'strentrytime' => get_string('entrytime', 'hybridteaching'),
            'strleavetime' => get_string('leavetime', 'hybridteaching'),
            'strpermanence' => get_string('permanence', 'hybridteaching'),
            'strhour' => get_string('hour', 'hybridteaching'),
            'strlogaction' => get_string('action'),
            'strlogmark' => get_string('marks', 'hybridteaching'),
            'strcombinedatt' => get_string('combinedatt', 'hybridteaching')
        ];

        $params = [];
        $extrasql = [];
        $params['userid'] = $userid;
        $params['editing'] = $editing;
        $grades = new grades();
        $viewsexclusion = ['studentattendance'];
        if (!has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $userid, $doanything = true)) {
            if (!in_array($view, $viewsexclusion)) {
                $view = 'studentattendance';
            }
            $editing = 0;
            $params['userid'] = $userid;
        }
        $params['view'] = $view;
        if ($view == 'attendlog' || $view == 'extendedsessionatt') {
            $selectedsession ? $sessionid = $selectedsession : $sessionid = 0;
            $selecteduser ? $attid = $selecteduser : $selecteduser = $attid;
            
            $sessoptionsformparams = [
                'id' => $id,
                'hid' => $hybridteachingid,
                'sessionid' => $sessionid,
                'selectedsession' => $selectedsession,
                'selecteduser' => $selecteduser,
                'view' => $view,
            ];
            if ($view == 'attendlog') {
                $sessionoptions = new attsessions_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                'attendlog' . '&sessionid=' . $selectedsession . '&attid=' . $attid . '&editing=' . $editing, $sessoptionsformparams);
            }  else {
                $sessionoptions = new attsessions_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                 'extendedsessionatt' . '&sessionid=' . $selectedsession . '&attuser=' . $selecteduser . '&editing=' . $editing, $sessoptionsformparams);
            }
        } 

        $sortexclusions = ['stroptions', 'strvc', 'strclassroom', 'strhour', 'strlogmark', 'strlogaction'];
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
                "&dir=$columndir&id=$id&view=" . $view . "&sessionid=" . $sessionid . "\">". $column ."</a>$columnicon";
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
        $table->head = $this->get_table_header($columns, $view, $editing, $sessionid);
        $table->colclasses = array('leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'hybridteachingattendance';
        $table->attributes['class'] = 'attendancetable generaltable';
        $table->data = array();

        $url = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php',
             array('sesskey' => sesskey(), 'view' => $view, 'sessionid' => $sessionid));

        $operator = $this->get_operator();
        $attendancecount = $attendance_controller->count_attendance($params, $operator);
        $returnurl = new moodle_url('/mod/hybridteaching/attendance.php?id='.$this->cm->id.'');
        if ($view == 'attendlog') {
            $att = $attendance_controller->hybridteaching_get_attendance_from_id($attid);
            $att->sessionid != $sessionid ? $att = $attendance_controller->hybridteaching_get_attendance($this->hybridteaching, $sessionid) : '';
            if (!$logs = $attendance_controller->hybridteaching_get_attendance_logs($att->id)) {
                if (isset($sessionoptions) && $editing) {
                    $sessionoptions->display();
                }
                echo '<br><h2>No logs found for this user in the session.<h2>';
                $return .= $OUTPUT->box_end();
                echo '<br>' . "<a href='attendance.php?id=".$id."&view=extendedsessionatt&sessionid=" .
                  $sessionid ."' class='btn btn-info' role='button'>volver</a>";
                 
                return $return;
            }
            $session = $sessions_controller->get_session($att->sessionid);
            $firstlog = $logs[array_key_first($logs)];
            $lastlog = $logs[array_key_last($logs)];
            foreach ($logs as $log) {
                $mark = '';
                $log->id == $lastlog->id && $log->action == 0 ? $mark = get_string('lastexit', 'hybridteaching') : false;
                $log->id == $firstlog->id ? $mark = get_string('firstentry', 'hybridteaching') : false;
                $body = [ 
                    'class' => '',
                    'hour' => date('H:i:s | d/m/y', $log->timecreated),
                    'logaction' => $log->action ? get_string('sessionentry', 'hybridteaching'): get_string('sessionexit', 'hybridteaching'),
                    'mark' => $mark != '' ? $mark : self::EMPTY,
                ];
                $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                $params = array('attid' => $attid, 'h' => $hybridteachingid, 'id' => $id,
                 'returnurl' => $returnurl, 'view' => $view, 'userid' => $userid, 'editing' => $editing);
    
                $options = $this->get_table_options($body, $params, $url, $att->sessionid);
                $class = $options['class'];
                $options = $options['options'];
                // Add a row to the table.
                $row = $this->get_attendance_row($body, $options, $view, $editing);
                if (!empty($class)) {
                    $row->attributes['class'] = $class;
                }
                $table->data[] = $row;
            }

            $attendancetable = html_writer::table($table);
            if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
                $attendancetable .= $this->get_bulk_options_select($view);
            }
            if (isset($sessionoptions) && $editing) {
                $sessionoptions->display();
            }
                        
            if (!$session->duration) {
                $return .= html_writer::tag('span', get_string('sessiondate', 'hybridteaching') . ' ' . get_string('noduration', 'hybridteaching'));
                $return .= html_writer::empty_tag('br');
                $return .= html_writer::tag('a', 'volver', ['href' => 'attendance.php?id='.$id.'&view=extendedsessionatt&sessionid=' .
                $sessionid.'', 'class' => 'btn btn-primary']);
                $return .= $OUTPUT->box_end();
                return $return;
            }
            $connectiontime = $attendance_controller->hybridteaching_get_attendance($this->hybridteaching, $session, $att->userid)->connectiontime;
            $participantpercent = round(($connectiontime / $session->duration) * 100, 2);
            
            $return .= html_writer::tag('span', get_string('participationtime', 'hybridteaching') .
                ': ' . helper::get_hours_format($connectiontime) . ' ' . $participantpercent . '%' );
            $return .= html_writer::empty_tag('br');
            $return .= html_writer::tag('a', 'volver', ['href' => 'attendance.php?id='.$id.'&view=extendedsessionatt&sessionid=' .
            $sessionid.'', 'class' => 'btn btn-primary']);

            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
            $return .= html_writer::tag('form', $attendancetable, array('method' => 'post', 
                'action' => $CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php?view=' . $view . '&sessionid=' . $sessionid ));
            $baseurl = new moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, array('id' => $id, 
                'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid));
            $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
            $return .= $OUTPUT->box_end();
            return $return;
        }
        if ($view == 'extendedstudentatt') {
            $participationrecords = $attendance_controller->hybridteaching_get_students_participation($hybridteachingid);
            $att = $attendance_controller->hybridteaching_get_attendance_from_id($attid);
            $attuser = $attendance_controller->load_sessions_attendant($userid);
            $userpicture = $OUTPUT->user_picture($attuser);
            $params['userid'] = $attuser->id;
            $attendanceassist = $attendance_controller->load_attendance_assistance($page, $perpage, $params,
             $extrasql, $operator, $sort, $dir);
            foreach ($participationrecords as $participation) {
                if ($participation->userid == $userid && !$editing) {
                    $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching', $this->hybridteaching->id, $participation->userid);
                    $attparticipation = $participationrecords[$userid];
                    $attendanceid = $attid;
                    $body = [ 
                        'class' => '',
                        'pfp' => $userpicture,
                        'firstlastname' => $attuser->firstname . ' / ' . $attuser->lastname,
                        'combinedatt' => $attparticipation->vc + $attparticipation->classroom,
                        'grade' =>
                            round($usertotalgrade->items[0]->grades[$participation->userid]->grade, 2) . ' / ' . $this->hybridteaching->grade,
                        'vc' => $attparticipation->vc,
                        'classroom' => $attparticipation->classroom,
                        'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                            'id' => 'attendance-' . $attendanceid,
                            'name' => 'attendance[]',
                            'classes' => 'm-1',
                            'checked' => false,
                            'value' => $attendanceid
                        ]),
                    ];
                    $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                    $params = array('attid' => $attid, 'h' => $hybridteachingid, 'id' => $id,
                        'returnurl' => $returnurl, 'view' => $view, 'userid' => $attuser->id, 'editing' => $editing);

                    $options = $this->get_table_options($body, $params, $url, 0);
                    $class = $options['class'];
                    $options = $options['options'];
                    // Add a row to the table.
                    $row = $this->get_attendance_row($body, $options, $view, $editing);
                    if (!empty($class)) {
                        $row->attributes['class'] = $class;
                    }
                    $table->data[] = $row;
        
                    continue;
                } else if ($editing) {  
                    $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching', $this->hybridteaching->id, $participation->userid);
                    $attuser = $attendance_controller->load_sessions_attendant($participation);
                    $userpicture = $OUTPUT->user_picture($attuser);
                    $attendanceid = $attid;
                    $body = [ 
                        'class' => '',
                        'pfp' => $userpicture,
                        'firstlastname' => $attuser->firstname . ' / ' . $attuser->lastname,
                        'combinedatt' => $participation->vc + $participation->classroom,
                        'grade' =>
                            round($usertotalgrade->items[0]->grades[$participation->userid]->grade, 2) . ' / ' . $this->hybridteaching->grade,
                        'vc' => $participation->vc,
                        'classroom' => $participation->classroom,
                        'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                            'id' => 'attendance-' . $attendanceid,
                            'name' => 'attendance[]',
                            'classes' => 'm-1',
                            'checked' => false,
                            'value' => $attendanceid
                        ]),
                    ];
                    $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                    $params = array('attid' => $attid, 'h' => $hybridteachingid, 'id' => $id,
                        'returnurl' => $returnurl, 'view' => $view, 'userid' => $attuser->id, 'editing' => $editing);
                    $options = $this->get_table_options($body, $params, $url, 0);
                    $class = $options['class'];
                    $options = $options['options'];
                    // Add a row to the table.
                    $row = $this->get_attendance_row($body, $options, $view, $editing);
                    if (!empty($class)) {
                        $row->attributes['class'] = $class;
                    }
                    $table->data[] = $row;
                }
            }
            if (!$participationrecords) {
                if (isset($attendance) && $attendance) {
                    $attendgrade = $grades->calc_att_grade_for($this->hybridteaching,$session->id,$attendance['id']);
                    $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching', $this->hybridteaching->id, $attendance['userid']);
                    foreach ($attuser as $attu) {
                        $body = [ 
                            'class' => '',
                            'pfp' => $userpicture,
                            'firstlastname' => $attuser->firstname . ' / ' . $attuser->lastname,
                            'combinedatt' => $participation->vc + $participation->classroom,
                            'grade' => round($attendgrade,2) . ' / '. 
                            round($usertotalgrade->items[0]->grades[$attendance['userid']]->grade, 2) . ' / ' . $this->hybridteaching->grade,
                            'vc' => $participation->vc,
                            'classroom' => $participation->classroom,
                            'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                                'id' => 'attendance-' . $attendanceid,
                                'name' => 'attendance[]',
                                'classes' => 'm-1',
                                'checked' => false,
                                'value' => $attendanceid
                            ]),
                        ];
                    }
                    $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                        $params = array('attid' => $attid, 'h' => $hybridteachingid, 'id' => $id,
                            'returnurl' => $returnurl, 'view' => $view, 'userid' => $attuser->id, 'editing' => $editing);
    
                        $options = $this->get_table_options($body, $params, $url, $att->sessionid);
                        $class = $options['class'];
                        $options = $options['options'];
                        // Add a row to the table.
                        $row = $this->get_attendance_row($body, $options, $view, $editing);
                        if (!empty($class)) {
                            $row->attributes['class'] = $class;
                        }
                        $table->data[] = $row;
                } 
            }
            $attendancetable = html_writer::table($table);
            if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
                $attendancetable .= $this->get_bulk_options_select($view);
            }
            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
            $return .= html_writer::tag('form', $attendancetable, array('method' => 'post', 
                'action' => $CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php?view=' . $view . '&sessionid=' . $sessionid ));
            $baseurl = new moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, array('id' => $id, 
                'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid));
            $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
            $return .= $OUTPUT->box_end();
            return $return;
        }
        $attendancelist = $attendance_controller->load_attendance($page, $perpage, $params, $extrasql,
        $operator, $sort, $dir, $view, $sessionid);
        $attendanceassist = $attendance_controller->load_attendance_assistance($page, $perpage, $params,
        $extrasql, $operator, $sort, $dir);
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
            $sessionsuser = (object) $attendance_controller->load_sessions_attendant($attendance);
            $attributes = array(
                'type'  => 'checkbox',
                'name'  => $session->name,
                'value' => get_string('active'),
                'class' => 'attendance-validated');

            $attendance['status'] == 1 ? $attributes['checked'] = true : '';
            $view == 'studentattendance' && $userid != $USER->id ? $editing = 1 : false;
            $editing ? true : $attributes['disabled'] = true;
            $submitb = html_writer::empty_tag('input', $attributes);
            $connectiontime = helper::get_hours_format($attendance['connectiontime']);
            empty($connectiontime) ? $connectiontime = self::EMPTY : false;

            if ($view == 'extendedsessionatt') {
                $timelog = $attendance_controller->hybridteaching_get_attendance_entry_end_times($attendance['id']);
                $entrytime = $timelog['entry'];
                $end = $timelog['end'];
                $action = $timelog['lastaction'];
                $action == 1 ? $endtime = '' : $endtime = $end;
            }
            $view == 'studentattendance' ?  $submitb .= '<br>' . $connectiontime : '';
            $userpicture = $OUTPUT->user_picture($sessionsuser);
            $userurl = new moodle_url('/user/view.php', array('id' => $USER->id));
            $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching', $this->hybridteaching->id, $attendance['userid']);
            $attendgrade = $grades->calc_att_grade_for($this->hybridteaching,$session->id,$attendance['id']);
            $attexempt = $sessions_controller->get_session($attendance['sessionid'])->attexempt;
            $body = [
                'class' => '',
                'attendanceid' => $attendance['id'],
                'group' => $session->groupid == 0 ? get_string('commonattendance', 'hybridteaching') :
                     groups_get_group($session->groupid)->name,
                'name' => $session->name,
                'username' => $sessionsuser->firstname . ' ' . $sessionsuser->lastname . ' ' . $userpicture,
                'date' => $date,
                'duration' => !empty($session->duration) ? helper::get_hours_format($session->duration) : self::EMPTY,
                'pfp' => $userpicture,
                'firstlastname' => $sessionsuser->firstname . ' / ' . $sessionsuser->lastname,
                'entrytime' => isset($entrytime) && !empty($entrytime) ? date('H:i:s | d/m/y', $entrytime) : self::EMPTY,
                'leavetime' => isset($endtime) && !empty($endtime) ? date('H:i:s | d/m/y', $endtime): self::EMPTY,
                'permanence' => $connectiontime,
                'attendance' => $attendance['exempt'] ? '<b>' . get_string('exempt', 'hybridteaching') . '<b>' : $submitb,
                'type' => $attendance['type'] == 0 ? get_string('classroom', 'hybridteaching') :
                    get_string('videoconference', 'hybridteaching'),
                'grade' => round($attendgrade,2) . ' / '. 
                    round($usertotalgrade->items[0]->grades[$attendance['userid']]->grade, 2) . ' / ' . $this->hybridteaching->grade,
                'vc' => $attassistance['vc'],
                'classroom' => $attassistance['classroom'],
                'enabled' => !$attendance['visible'] || $attexempt ? 0 : 1,
                'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                    'id' => 'attendance-' . $attendanceid,
                    'name' => 'attendance[]',
                    'classes' => 'm-1',
                    'checked' => false,
                    'value' => $attendanceid
                ]),
            ];
            if (!$body['enabled']) {    
                $body['class'] = 'dimmed_text';
            }
            $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
            $params = array('attid' => $attendanceid, 'h' => $hybridteachingid, 'id' => $id,
             'returnurl' => $returnurl, 'view' => $view, 'userid' => $userid, 'editing' => $editing);

            $options = $this->get_table_options($body, $params, $url, $attendance['sessionid']);
            $class = $options['class'];
            if (!$body['enabled']) {
                $class = 'dimmed_text';
            }
            $options = $options['options'];
            // Add a row to the table.
            $body['chosensession'] = $sessionid;
            $row = $this->get_attendance_row($body, $options, $view, $editing, $session->id);
            if (!empty($class)) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        // add filters
        $optionsform->display();
        if (isset($sessionoptions) && $editing) {
            $sessionoptions->display();
        }

        $attendancetable = html_writer::table($table);
        if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
            $attendancetable .= $this->get_bulk_options_select($view);
        }

        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
        $return .= html_writer::tag('form', $attendancetable, array('method' => 'post', 
            'action' => $CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php?view=' . $view . '&sessionid=' . $sessionid ));
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
                return 'type';
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
                return 'connectiontime';
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
                return 'lastname';
                break;
            case 'strfirstlastname':
                return 'lastname';
                break;
            case 'strentrytime':
                return 'starttime';
                break;
            case 'strleavetime':
                return 'endtime';
                break;
            case 'strpermanence':
                return 'connectiontime';
                break;
            case 'strhour':
                return 'hour';
                break;
            case 'strlogaction':
                return 'action';
                break;
            case 'strlogmark':
                return 'mark';
                break;
            case 'combinedatt':
                return '';
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
                isset($arrayoptions['view']) ? 
                $options .= $arrayoptions['view'] : '';
            } else {
                $options .= $arrayoptions['view'] . $arrayoptions['visible'];
            }
        }
        return ['options' => $options, 'class' => $arrayoptions['class']];
    }

    public function get_table_header($columns, $headers, $editing, $sessionid) {
        global $OUTPUT;
        $header = [];
        if ($headers == 'sessionattendance') {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strgroup'],
                $columns['strname'],
                $columns['strdate'],
                $columns['strduration'],
                $columns['strclassroom'],
                $columns['strvc'],
                $columns['stroptions'],
            ];
        } else if ($headers == 'studentattendance') {
            $header = [
                has_capability('mod/hybridteaching:sessionsfulltable', $this->context) ?
                    $OUTPUT->render($columns['mastercheckbox']) : '',
                $columns['strgroup'],
                $columns['strname'],
                $columns['strdate'],
                $columns['strduration'],
                $columns['strtype'],
                $columns['strattendance'],
                $columns['strgrade'],
                $columns['stroptions'],
            ];
 
        } else if ($headers == 'extendedsessionatt') {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strpfp'],
                $columns['strfirstlastname'],
                $sessionid ? '' : $columns['strname'],
                $columns['strtype'],
                $columns['strentrytime'],
                $columns['strleavetime'],
                $columns['strpermanence'],
                $columns['strattendance'],
                $columns['strgrade'],
                $columns['stroptions'],
            ];
        } else if ($headers == 'attendlog') {
            $header = [
                $columns['strhour'],
                $columns['strlogaction'],
                $columns['strlogmark'],
            ];
        } else {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strpfp'],
                $columns['strfirstlastname'],
                $columns['strcombinedatt'],
                $columns['strclassroom'],
                $columns['strvc'],
                $columns['strgrade'],
                $columns['stroptions'],
            ];
        }
        return $header;
    }

    public function build_options($body, $params, $url, $sessionid) {
        global $OUTPUT;
        $class = '';
        $attid = $params['attid'];
        switch ($params['view']) {
            case 'extendedsessionatt':
                $icon = 'i/log';
                $strview = 'logs';
                break;
            case 'extendedstudentatt':
                $icon = 't/viewdetails';
                $strview = 'info';
                break;
            default:
                $icon = 'i/preview';
                $strview = 'view';
        }
        
        $view = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'view',
         'sessionid' => $sessionid, 'attid' => $attid))), $OUTPUT->pix_icon($icon, get_string($strview),
          'moodle', array('class' => 'iconsmall')),
        );
        if ($params['view'] == 'sessionattendance') {
            if ($body['enabled']) {
                $visible = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'disable', 'attid' => $attid))),
                    $OUTPUT->pix_icon('i/hide', get_string('disable'), 'moodle', array('class' => 'iconsmall', 'attid' => $attid))); 
            } else {
                $visible = html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'enable'))),
                    $OUTPUT->pix_icon('i/show', get_string('enable'), 'moodle', array('class' => 'iconsmall')));
                $class = 'dimmed_text';
                $view = '';
            }
            $options = [
                'view' => $view,
                'visible' => $visible,
                'class' => $class,
            ];
        } else {
            has_capability('mod/hybridteaching:sessionsfulltable', $this->context) ?
            $options = [
                'view' => $view,
                'class' => $class,
            ] :
            $options = ['class' => $class];
        }

        return $options;
    }

    public function get_attendance_row($params, $options, $tableview, $editing, $sessionid = null) {
        global $OUTPUT;
        $type = $this->hybridteaching->typevc;
        $typealias = '';
        $sessions_controller = new sessions_controller($this->hybridteaching);
        if (!empty($type) && has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) {
            $typealias = get_string('attendance', 'hybridteaching');
        }
        $row = '';
        $sesname = '';
        switch($tableview) {
            case 'sessionattendance':
                $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['group'],
                $params['name'], $params['date'], $params['duration'], $params['classroom'], $params['vc'],
                 $options));
                break;
            case 'studentattendance':
                $row = new html_table_row(array(has_capability('mod/hybridteaching:sessionsfulltable', $this->context) ? 
                        $OUTPUT->render($params['checkbox']) : '', $params['group'],
                    $params['name'], $params['date'], $params['duration'], $params['type'],$params['attendance'],
                    $params['grade'], $options));
                break;
            case 'extendedsessionatt':
                if (isset($sessionid) && $params['chosensession'] == 0 ) {
                    $session = $sessions_controller->get_session($sessionid);
                    $sesname = $session->name;
                }
                $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['pfp'],
                    $params['firstlastname'], !empty($sesname) ? $sesname : '', $params['type'], $params['entrytime'], $params['leavetime'],
                    $params['permanence'], $params['attendance'], $params['grade'], $options));
                break;
            case 'attendlog':
                $row = new html_table_row(array($params['hour'], $params['logaction'], $params['mark']));
                break;
            case 'extendedstudentatt':
                $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['pfp'], $params['firstlastname'],
                     $params['combinedatt'], $params['classroom'], $params['vc'], $params['grade'], $options));
                break;
            default:
                $row = new html_table_row(array($OUTPUT->render($params['checkbox']), $params['group'],
                    $params['name'], $params['date'], $params['duration'], $params['type'],$params['attendance'],
                    $params['grade'], $options));
                break;
        }
        return $row;
    }

    public function get_bulk_options_select($view) {
        $selectactionparams = array(
            'id' => 'attendanceid',
            'class' => 'ml-2',
            'data-action' => 'toggle',
            'data-togglegroup' => 'attendance-table',
            'data-toggle' => 'action',
            'disabled' => 'disabled'
        );
        $printbulk = true;
        switch ($view) {
            case 'studentattendance':
                $options = [
                    'bulksetattendance' => get_string('setattendance', 'hybridteaching'),
                    'bulksetexempt' => get_string('setexempt', 'hybridteaching'),
                ];
                break;
            case 'extendedsessionatt':
                $options = [
                    'bulksetattendance' => get_string('setattendance', 'hybridteaching'),
                    'bulksetexempt' => get_string('setexempt', 'hybridteaching'),
                ];
                break;
            case 'sessionattendance':
                $options = [
                    'bulksetsessionexempt' => get_string('setsessionexempt', 'hybridteaching'),
                ];
                break;
            default:
                $printbulk = false;
        }
        if ($printbulk) {
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

    public function print_attendance_bulk_table($attendlist, $view = null) {
        global $OUTPUT, $DB, $PAGE;

        $sessions_controller = new sessions_controller($this->hybridteaching);
        [$insql, $params] = $DB->get_in_or_equal($attendlist, SQL_PARAMS_NAMED, 'id');
        $extrasql = 'ha.id ' . $insql;

        if ($view == 'sessionbulk') {
            $columns = [
                'strname' => get_string('name'),
                'strdate' => get_string('date'),
                'strattendance' => get_string('exempt', 'mod_hybridteaching'),
            ];
        } else {
            $columns = [
                'strpfp' => get_string('picture'),
                'strfirstlastname' => get_string('firstname') . ' / ' . get_string('lastname'),
                'strdate' => get_string('date'),
                'strattendance' => get_string('attendance', 'mod_hybridteaching'),
                'strprevioues' => get_string('prevattend', 'mod_hybridteaching'),
            ];
        }
        $return = $OUTPUT->heading(get_string('withselectedattends', 'mod_hybridteaching'));
        $return .= $OUTPUT->box_start('generalbox');

        $table = new html_table();
        $table->head = $columns;
        $table->colclasses = array('leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'hybridteachingsessions';
        $table->attributes['class'] = 'sessionstable generaltable';
        $table->data = array();

        if ($view == 'sessionbulk') {
            foreach ($attendlist as $sessionid) {
                $session = $sessions_controller->get_session($sessionid);
                $date = $session->starttime;
                $hour = date('H:i', $date);
                $date = date('l, j \d\e F \d\e Y, H:i', $date);

                $body = [
                    'name' => $session->name,
                    'date' => $date,
                    'attendance' => $session->attexempt ? '<b>' . get_string('exempt', 'hybridteaching') . '<b>' : self::EMPTY,
                ];

                // Add a row to the table.
                $row = new html_table_row($body);
                $table->data[] = $row;
            }
            $return .= html_writer::table($table);
            $return .= $OUTPUT->box_end();
            return $return;
        }
        $attcontroller = new attendance_controller($this->hybridteaching);
        $attendlist = $attcontroller->load_attendance(0, 0, $params, $extrasql);
        foreach ($attendlist as $att) {
            $connectiontime = helper::get_hours_format($att['connectiontime']);
            $attuser = $attcontroller->load_sessions_attendant($att);
            $date = $att['starttime'];
            $hour = date('H:i', $date);
            $date = date('l, j \d\e F \d\e Y, H:i', $date);

            $attributes = array(
                'type'  => 'checkbox',
                'class' => 'attendance-validated',
                'disabled' => true);
            $att['status'] == 1 ? $attributes['checked'] = true : false ;
            $submitb = html_writer::empty_tag('input', $attributes);
            if ($view == 'bulksetexempt') {
                $att['exempt'] ? $attendance = '<b>' . get_string('exempt', 'hybridteaching') . '<b>' : $attendance = self::EMPTY;
            } else {
                !empty($connectiontime) ? $attendance = $connectiontime : $attendance = self::EMPTY;
            }
            $body = [
                'pfp' => $OUTPUT->user_picture($attuser),
                'firstlastname' => $attuser->lastname . ' ' . $attuser->firstname,
                'date' => $date,
                'attendance' => $attendance,
                'previous' => $submitb,
            ];

            // Add a row to the table.
            $row = new html_table_row($body);
            $table->data[] = $row;
        }
        $return .= html_writer::table($table);
        $return .= $OUTPUT->box_end();

        return $return;
    }
}