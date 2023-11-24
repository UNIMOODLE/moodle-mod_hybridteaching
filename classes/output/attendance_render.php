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

require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/attendance_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/filters/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/attendance_form.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/attsessions_form.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/attresumee_form.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/form/attuserfilter_form.php');
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
        $attid = optional_param('attid', 0, PARAM_INT);
        $selecteduser = optional_param('selecteduser', 0, PARAM_INT);
        $editing = optional_param('editing', 1, PARAM_INT);
        $userid = optional_param('userid', $USER->id, PARAM_INT);
        $fname = optional_param('fname', '', PARAM_TEXT);
        $lname = optional_param('lname', '', PARAM_TEXT);
        $group = optional_param('groupid', -1, PARAM_INT);
        $selectedfilter = optional_param('attfilter', 'nofilter' , PARAM_ALPHANUMEXT);
        $attendancecontroller = new attendance_controller($this->hybridteaching);
        $sessionscontroller = new sessions_controller($this->hybridteaching);
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
            'strlastfirstname' => get_string('lastname') . ' / ' . get_string('firstname'),
            'strentrytime' => get_string('entrytime', 'hybridteaching'),
            'strleavetime' => get_string('leavetime', 'hybridteaching'),
            'strpermanence' => get_string('permanence', 'hybridteaching'),
            'strhour' => get_string('hour', 'hybridteaching'),
            'strlogaction' => get_string('action'),
            'strlogmark' => get_string('marks', 'hybridteaching'),
            'strcombinedatt' => get_string('combinedatt', 'hybridteaching'),
        ];

        $params = [];
        $this->check_attendance_filters();
        $sfiltering = new session_filtering();
        list($extrasql, $params) = $sfiltering->get_sql_filter();
        $params['userid'] = $userid;
        $grades = new grades();
        $viewsexclusion = ['studentattendance', 'attendlog'];
        if (!has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $USER->id, $doanything = true)) {
            if (!in_array($view, $viewsexclusion)) {
                $view = 'studentattendance';
            }
            $editing = 0;
            $params['userid'] = $USER->id;
        }
        $params['editing'] = $editing;
        $params['view'] = $view;
        $selectedfilter != 'nofilter' && !empty($selectedfilter) ? $perpage = 0 : '';
        if ($view == 'attendlog' || $view == 'extendedsessionatt') {
            $selectedsession ? $sessionid = $selectedsession : $sessionid = 0;
            $selecteduser ? $attid = $selecteduser : $selecteduser = $attid;
            $selecteduser ?
                $selectedstudent = $attendancecontroller::hybridteaching_get_attendance_from_id($selecteduser)->userid : '';
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
                    'attendlog' . '&selectedsession=' . $selectedsession . '&attid=' . $selecteduser . '&sort=' . $sort .
                    '&dir=' . $dir . '&groupid=' . $group, $sessoptionsformparams);
            } else {
                $sessionoptions = new attsessions_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                    'extendedsessionatt' . '&selectedsession=' . $selectedsession .
                    '&sort=' . $sort . '&dir=' . $dir . '&fname=' . $fname . '&lname=' . $lname . '&perpage=' .
                    $perpage . '&attfilter=' . $selectedfilter . '&groupid=' . $group, $sessoptionsformparams);
            }
        }
        if ($view == 'studentattendance' && has_capability('mod/hybridteaching:sessionsfulltable',
                $this->context, $user = $USER->id)) {
            $selecteduser ? $params['userid'] = $selecteduser : $selecteduser = $userid;
            $params['userid'] = $selecteduser;
            $attresumee = new attresumee_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                'studentattendance' . '&sessionid=0' . '&userid=' . $userid . '&perpage=' . $perpage . '&sort=' . $sort .
                '&dir=' . $dir, ['id' => $id, 'att' => $attid, 'selecteduser' => $selecteduser, 'hid' => $hybridteachingid]);
        }

        if ($view == 'extendedstudentatt' || $view == 'extendedsessionatt') {
            $attusrfilter = new attuserfilter_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?',
                ['id' => $id, 'att' => $attid, 'fname' => $fname, 'lname' => $lname, 'hid' => $hybridteachingid,
                    'view' => $view, 'sessid' => $selectedsession, 'sort' => $sort, 'dir' => $dir,
                    'perpage' => $perpage, 'attfilter' => $selectedfilter, 'groupid' => $group, ]);
        }
        $view == 'sessionattendance' ? $sortexclusions = ['stroptions', 'strclassroom', 'strvc'] : '';
        $view == 'extendedstudentatt' ? $sortexclusions = ['stroptions', 'strgrade'] : '';
        !isset($sortexclusions) ? $sortexclusions = ['stroptions', 'strlogmark', 'strentrytime', 'strleavetime'] : '';
        foreach ($columns as $key => $column) {
            $columnnames = $this->get_column_name($key, $view);
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
                $view == 'attendlog' ? $columns[$key] = "<a href=\"attendance.php?sort=". $columnnames .
                    "&dir=$columndir&id=$id&view=" . $view . "&sessionid=" . $sessionid . "&attid=" . $selecteduser .
                    "&userid=" . $params['userid'] ."\">". $column ."</a>$columnicon"
                :
                $columns[$key] = "<a href=\"attendance.php?sort=". $columnnames .
                    "&dir=$columndir&id=$id&view=" . $view . "&sessionid=" . $sessionid . "&userid=" . $params['userid'] .
                    "&fname=" . $fname . "&lname=" . $lname . "&perpage=" .
                    $perpage  . '&attfilter=' . $selectedfilter . "\">". $column ."</a>$columnicon";
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
            'perpage' => $perpage,
            'view' => $view,
            'selectedfilter' => $selectedfilter,
            'course' => $COURSE,
            'groupexception' => 0,
        ];
        $params['groupid'] = $group;
        $return = $OUTPUT->box_start('generalbox');

        $table = new html_table();
        $table->head = $this->get_table_header($columns, $view, $sessionid);
        $table->colclasses = ['leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign', ];
        $table->id = 'hybridteachingattendance';
        $table->attributes['class'] = 'attendancetable generaltable';
        $table->data = [];

        $url = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php',
             ['sesskey' => sesskey(), 'view' => $view, 'sessionid' => $sessionid]);

        $operator = $this->get_operator();
        $attendancecount = $attendancecontroller->count_attendance( $fname, $lname, $selectedsession, $params, $operator);
        $returnurl = new moodle_url('/mod/hybridteaching/attendance.php?id='.$this->cm->id.'');
        has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $USER->id) ?
                $returntosession = 1 : $returntosession = 0;
        if ($view == 'attendlog') {
            $att = $attendancecontroller->hybridteaching_get_attendance_from_id($attid);
            $att->sessionid != $sessionid ? $att = $attendancecontroller->hybridteaching_get_attendance($sessionid) : '';
            !$att ? $logs = false :
            $logs = $attendancecontroller->hybridteaching_get_attendance_logs($att->id, $sort, $dir);
            if (!$logs) {
                if (isset($sessionoptions)) {
                    has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $USER->id) ?
                    $sessionoptions->display() : '';
                }
                echo '<br><h2>' . get_string('nologsfound', 'mod_hybridteaching') . '<h2>';
                $return .= html_writer::tag('a', get_string('viewstudentinfo', 'mod_hybridteaching'),
                    ['href' => 'attendance.php?id='.$id.'&view=studentattendance&userid='.
                    $selectedstudent . '', 'class' => 'btn btn-primary', ]);
                $returntosession ?
                $return .= html_writer::tag('a', get_string('viewsessioninfo', 'mod_hybridteaching'),
                    ['href' => 'attendance.php?id='.$id.'&view=extendedsessionatt&sessionid=' .
                $sessionid.'', 'class' => 'btn btn-primary', ]) : '';
                $return .= $OUTPUT->box_end();
                return $return;
            }
            $session = $sessionscontroller->get_session($att->sessionid);
            $unsortedlog = $attendancecontroller->hybridteaching_get_attendance_logs($att->id);
            $firstlog = $logs[array_key_first($unsortedlog)];
            $lastlog = $logs[array_key_last($unsortedlog)];
            foreach ($logs as $log) {
                $mark = '';
                $log->id == $lastlog->id && $log->action == 0 ? $mark = get_string('lastexit', 'hybridteaching') : false;
                $log->id == $firstlog->id ? $mark = get_string('firstentry', 'hybridteaching') : false;
                $body = [
                    'class' => '',
                    'hour' => date('H:i:s | d/m/y', $log->timecreated),
                    'logaction' => $log->action ? get_string('sessionentry', 'hybridteaching') :
                        get_string('sessionexit', 'hybridteaching'), 'mark' => $mark != '' ? $mark : self::EMPTY,
                ];
                $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                $params = ['attid' => $attid, 'h' => $hybridteachingid, 'id' => $id,
                 'returnurl' => $returnurl, 'view' => $view, 'userid' => $userid, ];

                $options = $this->get_table_options($body, $params, $url, $att->sessionid);
                $class = $options['class'];
                $options = $options['options'];
                // Add a row to the table.
                $row = $this->get_attendance_row($body, $options, $view);
                if (!empty($class)) {
                    $row->attributes['class'] = $class;
                }
                $table->data[] = $row;
            }

            $attendancetable = html_writer::table($table);
            if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
                $attendancetable .= $this->get_bulk_options_select($view);
            }
            if (isset($sessionoptions)) {
                has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $USER->id) ?
                    $sessionoptions->display() : '';
            }

            if (!$session->duration) {
                $return .= html_writer::tag('span', get_string('sessiondate', 'hybridteaching') . ' ' .
                    get_string('noduration', 'hybridteaching'));
                $return .= html_writer::empty_tag('br');
                $return .= html_writer::tag('a', get_string('viewstudentinfo', 'mod_hybridteaching'),
                    ['href' => 'attendance.php?id='.$id.'&view=studentattendance&userid='.
                    $selectedstudent. '', 'class' => 'btn btn-primary', ]);
                $returntosession ?
                    $return .= html_writer::tag('a', get_string('viewsessioninfo', 'mod_hybridteaching'),
                        ['href' => 'attendance.php?id='.$id.'&view=extendedsessionatt&sessionid=' .
                    $sessionid.'', 'class' => 'btn btn-primary', ]) : '';
                $return .= $OUTPUT->box_end();
                return $return;
            }
            $connectiontime = $attendancecontroller->hybridteaching_get_attendance($session, $att->userid)->connectiontime;
            $participantpercent = round(($connectiontime / $session->duration) * 100, 2);
            $participantpercent > 100 ? $participantpercent = 100 : '';
            $participantpercent < 0 ? $participantpercent = 0 : ''; 
            $return .= html_writer::tag('span', get_string('participationtime', 'hybridteaching') .
                ': ' . helper::get_hours_format($connectiontime) . ' ' . $participantpercent . '%' );
            $return .= html_writer::empty_tag('br');
            $return .= html_writer::tag('a', get_string('viewstudentinfo', 'mod_hybridteaching'),
                ['href' => 'attendance.php?id='.$id.'&view=studentattendance&userid='.
                $selectedstudent . '', 'class' => 'btn btn-primary', ]);
            $returntosession ?
                $return .= html_writer::tag('a', get_string('viewsessioninfo', 'mod_hybridteaching'),
                    ['href' => 'attendance.php?id='.$id.'&view=extendedsessionatt&sessionid=' .
                $sessionid.'', 'class' => 'btn btn-primary', ]) : '';
            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
            $return .= html_writer::tag('form', $attendancetable, ['method' => 'post',
                'action' => $CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php?view=' . $view .
                '&sessionid=' . $sessionid, ]);
            $baseurl = new moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, ['id' => $id,
                'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid, 'attid' => $selecteduser, ]);
            $return .= $OUTPUT->box_end();
            return $return;
        }
        if ($view == 'extendedstudentatt') {
            $participationrecords = $attendancecontroller->hybridteaching_get_students_participation($hybridteachingid,
                $sort, $dir, $fname, $lname);
            $baseurl = new moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, ['id' => $id,
            'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid, ]);
            $attendancecount = count($participationrecords);
            $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
            $attuser = $attendancecontroller->load_sessions_attendant($userid);
            $userpicture = $OUTPUT->user_picture($attuser);
            $params['userid'] = $attuser->id;
            $attendanceassist = $attendancecontroller->load_attendance_assistance($params, $extrasql, $operator);
            foreach ($participationrecords as $participation) {
                $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching',
                    $this->hybridteaching->id, $participation->userid);
                $attuser = $attendancecontroller->load_sessions_attendant($participation);
                $userpicture = $OUTPUT->user_picture($attuser);
                $attendanceid = $attid;
                $gradevalue = $usertotalgrade->items[0]->grades[$participation->userid]->grade;
                $roundedgrade = ($gradevalue !== null) ? round($gradevalue, 2) : null;
                $body = [
                    'class' => '',
                    'pfp' => $userpicture,
                    'firstlastname' => $attuser->lastname . ' / ' . $attuser->firstname,
                    'combinedatt' => $participation->vc + $participation->classroom,
                    'grade' => ($roundedgrade !== null) ? $roundedgrade . ' / ' . $this->hybridteaching->grade : null,
                    'vc' => $participation->vc,
                    'classroom' => $participation->classroom,
                    'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                        'id' => 'attendance-' . $attendanceid,
                        'name' => 'attendance[]',
                        'classes' => 'm-1',
                        'checked' => false,
                        'value' => $attendanceid,
                    ]),
                ];
                $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                $params = ['attid' => $attid, 'h' => $hybridteachingid, 'id' => $id,
                    'returnurl' => $returnurl, 'view' => $view, 'userid' => $attuser->id, ];
                $options = $this->get_table_options($body, $params, $url, 0);
                $class = $options['class'];
                $options = $options['options'];
                // Add a row to the table.
                $row = $this->get_attendance_row($body, $options, $view);
                if (!empty($class)) {
                    $row->attributes['class'] = $class;
                }
                $table->data[] = $row;
            }

            if (!$participationrecords) {
                if (isset($attendance) && $attendance) {
                    $attendgrade = $grades->calc_att_grade_for($this->hybridteaching, $session->id, $attendance['id']);
                    $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching',
                        $this->hybridteaching->id, $attendance['userid']);
                    foreach ($attuser as $attu) {
                        $body = [
                            'class' => '',
                            'pfp' => $userpicture,
                            'firstlastname' => $attuser->lastname . ' / ' . $attuser->firstname,
                            'combinedatt' => $participation->vc + $participation->classroom,
                            'grade' => round($attendgrade, 2) . ' / '.
                                round($usertotalgrade->items[0]->grades[$attendance['userid']]->grade, 2) .
                                ' / ' . $this->hybridteaching->grade,
                            'vc' => $participation->vc,
                            'classroom' => $participation->classroom,
                            'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                                'id' => 'attendance-' . $attendanceid,
                                'name' => 'attendance[]',
                                'classes' => 'm-1',
                                'checked' => false,
                                'value' => $attendanceid,
                            ]),
                        ];
                    }
                    $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                        $params = ['attid' => $attid, 'h' => $hybridteachingid, 'id' => $id,
                            'returnurl' => $returnurl, 'view' => $view, 'userid' => $attuser->id, ];

                        $options = $this->get_table_options($body, $params, $url, $att->sessionid);
                        $class = $options['class'];
                        $options = $options['options'];
                        // Add a row to the table.
                        $row = $this->get_attendance_row($body, $options, $view);
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
            if (isset($attusrfilter)) {
                $attusrfilter->display();
            }
            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
            $return .= html_writer::tag('form', $attendancetable, ['method' => 'post',
                'action' => $CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php?view=' .
                $view . '&sessionid=' . $sessionid, ]);
            $baseurl = new moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, ['id' => $id,
                'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid, ]);
            $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
            $return .= $OUTPUT->box_end();
            return $return;
        }
        $attendancelist = $attendancecontroller->load_attendance($page, $perpage, $params, $extrasql,
            $operator, $sort, $dir, $view, $sessionid, $fname, $lname);
        $attendanceassist = $attendancecontroller->load_attendance_assistance($params, $extrasql, $operator);
        $view == 'studentattendance' ? $baseurl = new moodle_url('/mod/hybridteaching/attendance.php?view=' .
            $view, ['id' => $id, 'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage,
            'sessionid' => $sessionid, 'userid' => $selecteduser, 'fname' => $fname, 'lname' => $lname,
            'attfilter' => $selectedfilter, ])
        :
        $baseurl = new moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, ['id' => $id,
            'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid, 'fname' => $fname,
            'lname' => $lname, 'attfilter' => $selectedfilter, ]);
            $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
        $groupmode ? $usegroup = 1 : $usegroup = 0;
        if (!$groupmode) {
            $groupmode = $attendancecontroller->attendances_uses_groups($attendancelist);
            if (!$groupmode && ($view == 'studentattendance' || $view == 'sessionattendance')) {
                unset($table->head[1]);
                $optionsformparams['groupexception'] = $groupmode;
            }
        }
        if ($view == 'sessionattendance') {
            foreach ($attendancelist as $sessionlist) {
                $session = $sessionscontroller->get_session($sessionlist['id']);
                $date = $session->starttime;
                $hour = date('H:i', $date);
                $date = date('l, j \d\e F \d\e Y H:i', $date);
                $attassistance = [];
                foreach ($attendanceassist as $attassist) {
                    if ($attassist['sessionid'] == $session->id) {
                        $attassistance = $attassist;
                        continue;
                    }
                }
                $attributes = [
                    'type'  => 'checkbox',
                    'name'  => $session->name,
                    'value' => get_string('active'),
                    'class' => 'attendance-validated', ];
                $editing ? true : $attributes['disabled'] = true;
                $groupbody = '';
                if ($groupmode) {
                    $session->groupid == 0 ? $groupbody = get_string('commonattendance', 'hybridteaching') :
                        $groupbody = groups_get_group($session->groupid)->name;
                }
                if (!empty($session->typevc)) {
                    $typealias = get_string('alias', 'hybridteachvc_'.$session->typevc);
                } else {
                    $typealias = get_string('classroom', 'mod_hybridteaching');
                }
                $body = [
                    'class' => '',
                    'group' => $groupbody,
                    'name' => $session->name,
                    'date' => $date,
                    'duration' => !empty($session->duration) ? helper::get_hours_format($session->duration) : self::EMPTY,
                    'typevc' => $typealias,
                    'vc' => isset($attassistance['vc']) ? $attassistance['vc'] : 0,
                    'classroom' => isset($attassistance['classroom']) ? $attassistance['classroom'] : 0,
                    'attexempt' => $session->attexempt,
                    'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                        'id' => 'session-' . $session->id ,
                        'name' => 'session[]',
                        'classes' => 'm-1',
                        'checked' => false,
                        'value' => $session->id,
                    ]),
                ];
                if ($body['attexempt']) {
                    $body['class'] = 'dimmed_text';
                }
                $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                $params = ['attid' => $session->id, 'h' => $hybridteachingid, 'id' => $id,
                'returnurl' => $returnurl, 'view' => $view, 'userid' => $userid, ];

                $options = $this->get_table_options($body, $params, $url, $session->id);
                $class = $options['class'];
                $options = $options['options'];
                // Add a row to the table.
                if ($attendancecontroller->display_attendance_row($body, $selectedfilter, $view)) {
                    $row = $this->get_attendance_row($body, $options, $view, $session->id);
                    if (!empty($class)) {
                        $row->attributes['class'] = $class;
                    }
                    $table->data[] = $row;
                } else {
                    $table->data[] = [];
                }
            }
            // Add filters.
            if (isset($attresumee)) {
                $attresumee->display();
            }
            $optionsform = new attendance_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                $view . '&sessionid=' . $sessionid . '&userid=' . $selecteduser . '&sort=' . $sort . '&dir=' .
                $dir . '&fname=' . $fname . '&lname=' . $lname . '&groupid=' . $group, $optionsformparams);
            $optionsform->display();
            if (isset($sessionoptions) && $editing) {
                $sessionoptions->display();
            }
            if (isset($attusrfilter)) {
                $attusrfilter->display();
            }
            $attendancetable = html_writer::table($table);
            if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
                $attendancetable .= $this->get_bulk_options_select($view);
            }

            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
            $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
            $return .= html_writer::tag('form', $attendancetable, ['method' => 'post',
                'action' => $CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php?view=' .
                $view . '&sessionid=' . $sessionid, ]);
            $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
            $return .= $OUTPUT->box_end();
            return $return;
        }

        foreach ($attendancelist as $attendance) {
            $session = $sessionscontroller->get_session($attendance['sessionid']);
            $date = $session->starttime;
            $hour = date('H:i', $date);
            $attendanceid = $attendance['id'];
            $date = date('l, j \d\e F \d\e Y H:i', $date);
            $attassistance = [];
            foreach ($attendanceassist as $attassist) {
                if ($attassist['sessionid'] == $attendance['sessionid']) {
                    $attassistance = $attassist;
                    continue;
                }
            }
            $sessionsuser = (object) $attendancecontroller->load_sessions_attendant($attendance);
            $attributes = [
                'type'  => 'checkbox',
                'name'  => $session->name,
                'value' => get_string('active'),
                'class' => 'attendance-validated', ];

            $attendance['status'] == 1 ? $attributes['checked'] = true : '';
            $editing ? true : $attributes['disabled'] = true;
            $submitb = html_writer::empty_tag('input', $attributes);
            $connectiontime = helper::get_hours_format($attendance['connectiontime']);
            empty($connectiontime) ? $connectiontime = self::EMPTY : false;

            if ($view == 'extendedsessionatt') {
                $timelog = $attendancecontroller->hybridteaching_get_attendance_entry_end_times($attendance['id']);
                $entrytime = $timelog['entry'];
                $end = $timelog['end'];
                $action = $timelog['lastaction'];
                $action == 1 ? $endtime = '' : $endtime = $end;
            }
            $view == 'studentattendance' ? $submitb .= '<br>' . $connectiontime : '';
            $attendance['status'] == 2 ? $submitb .= '<br>' . get_string('late', 'hybridteaching') : '';
            $attendance['status'] == 4 ? $submitb .= '<br>' . get_string('earlyleave', 'hybridteaching') : '';
            $userpicture = $OUTPUT->user_picture($sessionsuser);
            $userurl = new moodle_url('/user/view.php', ['id' => $USER->id]);
            $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching',
                $this->hybridteaching->id, $attendance['userid']);
            $attendgrade = $grades->calc_att_grade_for($this->hybridteaching, $session->id, $attendance['id']);
            $attexempt = $sessionscontroller->get_session($attendance['sessionid'])->attexempt;
            $attendance['connectiontime'] == 0 ? $atttype = get_string('noatt', 'hybridteaching') : $atttype = '';
            if (empty($atttype)) {
                $attendance['type'] == 0 ? $atttype = get_string('classroom', 'hybridteaching') :
                    $atttype = get_string('videoconference', 'hybridteaching');
            }
            $groupbody = '';
            if ($groupmode) {
                $session->groupid == 0 ? $groupbody = get_string('commonattendance', 'hybridteaching') :
                    $groupbody = groups_get_group($session->groupid)->name;
            }
            $body = [
                'class' => '',
                'attendanceid' => $attendance['id'],
                'group' => $groupbody,
                'name' => $session->name,
                'username' => $sessionsuser->firstname . ' ' . $sessionsuser->lastname . ' ' . $userpicture,
                'date' => $date,
                'duration' => !empty($session->duration) ? helper::get_hours_format($session->duration) : self::EMPTY,
                'pfp' => $userpicture,
                'firstlastname' => $sessionsuser->lastname . ' / ' . $sessionsuser->firstname,
                'entrytime' => isset($entrytime) && !empty($entrytime) ? date('H:i:s | d/m/y', $entrytime) : self::EMPTY,
                'leavetime' => isset($endtime) && !empty($endtime) ? date('H:i:s | d/m/y', $endtime) : self::EMPTY,
                'permanence' => $connectiontime,
                'attendance' => $attendance['exempt'] ? '<b>' . get_string('exempt', 'hybridteaching') . '<b>' : $submitb,
                'type' => $atttype,
                'grade' => round($attendgrade ?? 0, 2) . ' / ' .
                    round($usertotalgrade->items[0]->grades[$attendance['userid']]->grade ?? 0, 2) .
                    ' / ' . $this->hybridteaching->grade,
                'vc' => isset($attassistance['vc']) ? $attassistance['vc'] : 0,
                'classroom' => isset($attassistance['classroom']) ? $attassistance['classroom'] : 0,
                'enabled' => !$attendance['visible'] || $attexempt ? 0 : 1,
                'status' => $attendance['status'],
                'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                    'id' => 'attendance-' . $attendanceid,
                    'name' => 'attendance[]',
                    'classes' => 'm-1',
                    'checked' => false,
                    'value' => $attendanceid,
                ]),
            ];
            if (!$body['enabled']) {
                $body['class'] = 'dimmed_text';
            }
            $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
            $params = ['attid' => $attendanceid, 'h' => $hybridteachingid, 'id' => $id,
             'returnurl' => $returnurl, 'view' => $view, 'userid' => $userid, ];

            $options = $this->get_table_options($body, $params, $url, $attendance['sessionid']);
            $class = $options['class'];
            if (!$body['enabled']) {
                $class = 'dimmed_text';
            }
            $options = $options['options'];
            // Add a row to the table.
            $body['chosensession'] = $sessionid;
            if ($attendancecontroller->display_attendance_row($body, $selectedfilter, $view)) {
                $row = $this->get_attendance_row($body, $options, $view, $session->id);
                if (!empty($class)) {
                    $row->attributes['class'] = $class;
                }
                $table->data[] = $row;
            } else {
                $table->data[] = [];
            }
        }
        // Add filters.
        if (isset($attresumee)) {
            $attresumee->display();
        }
        $optionsform = new attendance_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
            $view . '&sessionid=' . $sessionid . '&userid=' . $selecteduser . '&sort=' . $sort . '&dir=' .
            $dir . '&fname=' . $fname . '&lname=' . $lname . '&groupid=' . $group, $optionsformparams);
        $optionsform->display();
        if (isset($sessionoptions) && $editing) {
            $sessionoptions->display();
        }
        if (isset($attusrfilter)) {
            $attusrfilter->display();
        }
        $attendancetable = html_writer::table($table);
        if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
            $attendancetable .= $this->get_bulk_options_select($view);
        }

        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
        $return .= html_writer::tag('form', $attendancetable, ['method' => 'post',
            'action' => $CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php?view=' .
            $view . '&sessionid=' . $sessionid, ]);
        $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
        $return .= $OUTPUT->box_end();
        return $return;
    }

    public function get_column_name($column, $view) {
        switch ($column) {
            case 'strgroup':
                return 'groupid';
                break;
            case 'strtype':
                if ($view == 'sessionattendance') {
                    return 'typevc';
                    break;
                }
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
                if ($view == 'studentattendance') {
                    return 'connectiontime';
                    break;
                }
                return 'status';
                break;
            case 'strgrade':
                return 'grade';
                break;
            case 'strusername':
                return 'username';
                break;
            case 'strpfp':
                return 'userid';
                break;
            case 'strlastfirstname':
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
                return 'timecreated';
                break;
            case 'strlogaction':
                return 'action';
                break;
            case 'strcombinedatt':
                return 'total';
                break;
            case 'strclassroom':
                return 'classroom';
                break;
            case 'strvc':
                return 'vc';
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
                isset($arrayoptions['userinf']) ?
                $options .= $arrayoptions['userinf'] : '';
                isset($arrayoptions['log']) ?
                $options .= $arrayoptions['log'] : '';
            } else {
                $options .= $arrayoptions['view'] . $arrayoptions['visible'];
            }
        }
        return ['options' => $options, 'class' => $arrayoptions['class']];
    }

    public function get_table_header($columns, $headers, $sessionid) {
        global $OUTPUT;
        $header = [];
        if ($headers == 'sessionattendance') {
            $header = [
                $OUTPUT->render($columns['mastercheckbox']),
                $columns['strgroup'],
                $columns['strname'],
                $columns['strdate'],
                $columns['strduration'],
                $columns['strtype'],
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
                $columns['strlastfirstname'],
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
                $columns['strlastfirstname'],
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

        $view = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'view',
         'sessionid' => $sessionid, 'attid' => $attid, ])), $OUTPUT->pix_icon($icon, get_string($strview),
          'moodle', ['class' => 'iconsmall']),
        );
        if ($params['view'] == 'sessionattendance') {
            if (!$body['attexempt']) {
                $visible = html_writer::link(new moodle_url($url, array_merge($params,
                    ['action' => 'disable', 'attid' => $attid])),
                    $OUTPUT->pix_icon('i/hide', get_string('disable'), 'moodle', ['class' => 'iconsmall', 'attid' => $attid]));
            } else {
                $visible = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'enable'])),
                    $OUTPUT->pix_icon('i/show', get_string('enable'), 'moodle', ['class' => 'iconsmall']));
                $class = 'dimmed_text';
                $view = '';
            }
            $options = [
                'view' => $view,
                'visible' => $visible,
                'class' => $class,
            ];
        } else if ($params['view'] == 'extendedsessionatt') {
            $userinf = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'userinf', 'attid' => $attid])),
            $OUTPUT->pix_icon('i/user', get_string('user'), 'moodle', ['class' => 'iconsmall', 'attid' => $attid]));
            $options = [
                'view' => $view,
                'userinf' => $userinf,
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
        if ($params['view'] == 'studentattendance') {
            $options['log'] = html_writer::link(new moodle_url($url, array_merge($params, ['action' => 'view',
            'sessionid' => $sessionid, 'attid' => $attid, 'log' => 1, ])), $OUTPUT->pix_icon('i/log', get_string('logs'),
             'moodle', ['class' => 'iconsmall']),
            );
        }
        return $options;
    }

    public function get_attendance_row($params, $options, $tableview, $sessionid = null) {
        global $OUTPUT;
        $type = $this->hybridteaching->typevc;
        $typealias = '';
        $sessionscontroller = new sessions_controller($this->hybridteaching);
        if (!empty($type) && has_capability('mod/hybridteaching:sessionsfulltable', $this->context)) {
            $typealias = get_string('attendance', 'hybridteaching');
        }
        $row = '';
        $sesname = '';
        switch($tableview) {
            case 'sessionattendance':
                if (!$params['group']) {
                    $row = new html_table_row([$OUTPUT->render($params['checkbox']),
                    $params['name'], $params['date'], $params['duration'], $params['typevc'], $params['classroom'], $params['vc'],
                        $options, ]);
                    break;
                }
                $row = new html_table_row([$OUTPUT->render($params['checkbox']), $params['group'],
                $params['name'], $params['date'], $params['duration'], $params['typevc'], $params['classroom'], $params['vc'],
                    $options, ]);
                break;
            case 'studentattendance':
                if (!$params['group']) {
                    $row = new html_table_row([has_capability('mod/hybridteaching:sessionsfulltable', $this->context) ?
                    $OUTPUT->render($params['checkbox']) : '',
                        $params['name'], $params['date'], $params['duration'], $params['type'], $params['attendance'],
                        $params['grade'], $options, ]);
                    break;
                }
                $row = new html_table_row([has_capability('mod/hybridteaching:sessionsfulltable', $this->context) ?
                        $OUTPUT->render($params['checkbox']) : '', $params['group'],
                    $params['name'], $params['date'], $params['duration'], $params['type'], $params['attendance'],
                    $params['grade'], $options, ]);
                break;
            case 'extendedsessionatt':
                if (isset($sessionid) && $params['chosensession'] == 0 ) {
                    $session = $sessionscontroller->get_session($sessionid);
                    $sesname = $session->name;
                }
                $row = new html_table_row([$OUTPUT->render($params['checkbox']), $params['pfp'],
                    $params['firstlastname'], !empty($sesname) ? $sesname : '', $params['type'],
                    $params['entrytime'], $params['leavetime'], $params['permanence'],
                    $params['attendance'], $params['grade'], $options, ]);
                break;
            case 'attendlog':
                $row = new html_table_row([$params['hour'], $params['logaction'], $params['mark']]);
                break;
            case 'extendedstudentatt':
                $row = new html_table_row([$OUTPUT->render($params['checkbox']), $params['pfp'], $params['firstlastname'],
                     $params['combinedatt'], $params['classroom'], $params['vc'], $params['grade'], $options, ]);
                break;
            default:
                $row = new html_table_row([$OUTPUT->render($params['checkbox']), $params['group'],
                    $params['name'], $params['date'], $params['duration'], $params['type'], $params['attendance'],
                    $params['grade'], $options, ]);
                break;
        }
        return $row;
    }

    public function get_bulk_options_select($view) {
        $selectactionparams = [
            'id' => 'attendanceid',
            'class' => 'ml-2',
            'data-action' => 'toggle',
            'data-togglegroup' => 'attendance-table',
            'data-toggle' => 'action',
            'disabled' => 'disabled',
        ];
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
        return [$extrasql, $params];
    }

    public function print_attendance_bulk_table($attendlist, $view = null) {
        global $OUTPUT, $DB, $PAGE;

        $sessionscontroller = new sessions_controller($this->hybridteaching);
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
                'strlastfirstname' => get_string('firstname') . ' / ' . get_string('lastname'),
                'strdate' => get_string('date'),
                'strattendance' => get_string('attendance', 'mod_hybridteaching'),
                'strprevioues' => get_string('prevattend', 'mod_hybridteaching'),
            ];
        }
        $return = $OUTPUT->heading(get_string('withselectedattends', 'mod_hybridteaching'));
        $return .= $OUTPUT->box_start('generalbox');

        $table = new html_table();
        $table->head = $columns;
        $table->colclasses = ['leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign', ];
        $table->id = 'hybridteachingsessions';
        $table->attributes['class'] = 'sessionstable generaltable';
        $table->data = [];

        if ($view == 'sessionbulk') {
            foreach ($attendlist as $sessionid) {
                $session = $sessionscontroller->get_session($sessionid);
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

            $attributes = [
                'type'  => 'checkbox',
                'class' => 'attendance-validated',
                'disabled' => true, ];
            $att['status'] == 1 ? $attributes['checked'] = true : false;
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
