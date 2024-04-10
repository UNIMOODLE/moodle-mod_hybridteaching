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

namespace mod_hybridteaching\local;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use mod_hybridteaching\controller\attendance_controller;
use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\helpers\grades;
use mod_hybridteaching\form\attresume_options_form;
use mod_hybridteaching\form\attuserfilter_options_form;
use mod_hybridteaching\form\attsessions_options_form;
use mod_hybridteaching\form\attendance_options_form;
use mod_hybridteaching\filters\session_filtering;
use mod_hybridteaching\helper;
use html_writer;

require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/gradelib.php');

/**
 * Class attendance_table.
 */
class attendance_table {
    /** @var string Used when there is an empty column */
    const EMPTY = "-";

    /** @var object Hybridteaching object */
    protected $hybridteaching;

    /** @var object Course module */
    protected $cm;

    /** @var object Module context */
    protected $context;

    /**
     * Constructor for the class.
     *
     * @param stdClass $hybridteaching
     */
    public function __construct(stdClass $hybridteaching) {
        $this->hybridteaching = $hybridteaching;
        if (!empty($this->hybridteaching)) {
            $this->cm = get_coursemodule_from_instance('hybridteaching', $this->hybridteaching->id);
            $this->context = \context_module::instance($this->cm->id);
        }
    }

    /**
     * Builds the XHTML to display the control
     *
     * @return string
     */
    public function print_attendance_table() {
        global $OUTPUT, $DB, $PAGE, $USER, $CFG, $COURSE;

        $PAGE->requires->js_call_amd('mod_hybridteaching/attendance', 'init');
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
            'strduration' => get_string('duration', 'hybridteaching'),
            'strtype' => get_string('type', 'hybridteaching'),
            'strattendance' => get_string('attendance', 'hybridteaching'),
            'strgrade' => get_string('gradenoun', 'hybridteaching') . $helpatt,
            'stroptions' => get_string('actions', 'hybridteaching'),
            'strvc' => get_string('videoconference', 'hybridteaching'),
            'strclassroom' => get_string('classroom', 'hybridteaching'),
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
                list($course, $cm) = get_course_and_cm_from_instance($this->hybridteaching->id, 'hybridteaching');
                $event = \mod_hybridteaching\event\attendance_manage_viewed::create([
                    'objectid' => $this->hybridteaching->id,
                    'context' => \context_module::instance($cm->id),
                    'other' => [
                        'sessid' => $sessionid,
                    ],
                ]);

                $event->trigger();

                $sessionoptions = new attsessions_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                    'extendedsessionatt' . '&selectedsession=' . $selectedsession .
                    '&sort=' . $sort . '&dir=' . $dir . '&fname=' . $fname . '&lname=' . $lname . '&perpage=' .
                    $perpage . '&attfilter=' . $selectedfilter . '&groupid=' . $group, $sessoptionsformparams);
            }
        }

        if ($view == 'studentattendance') {
            $selecteduser ? $params['userid'] = $selecteduser : $selecteduser = $userid;
            $params['userid'] = $selecteduser;
            $attresume = new attresume_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                'studentattendance' . '&sessionid=0' . '&userid=' . $userid . '&perpage=' . $perpage . '&sort=' . $sort .
                '&dir=' . $dir, ['id' => $id, 'att' => $attid, 'selecteduser' => $selecteduser, 'hid' => $hybridteachingid]);
        }

        if ($view == 'extendedsessionatt') {
            $attusrfilter = new attuserfilter_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?',
                ['id' => $id, 'att' => $attid, 'fname' => $fname, 'lname' => $lname, 'hid' => $hybridteachingid,
                    'view' => $view, 'sessid' => $selectedsession, 'sort' => $sort, 'dir' => $dir,
                    'perpage' => $perpage, 'attfilter' => $selectedfilter, 'groupid' => $group, ]);
        }
        $view == 'sessionattendance' ? $sortexclusions = ['stroptions', 'strclassroom', 'strvc'] : '';
        $view == 'extendedstudentatt' ? $sortexclusions = ['stroptions', 'strgrade', 'strvc', 'strclassroom', 'strcombinedatt', 'strpfp'] : '';
        $view == 'studentattendance' ? $sortexclusions = ['strtype', 'strattendance', 'strgrade', 'stroptions'] : '';
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

        $table = new \html_table();
        $table->head = $this->get_table_header($columns, $view, $sessionid);
        $table->colclasses = ['leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign', ];
        $table->id = 'hybridteachingattendance';
        $table->attributes['class'] = 'attendancetable generaltable';
        $table->data = [];

        $url = new \moodle_url($CFG->wwwroot . '/mod/hybridteaching/action/attendance_action.php',
             ['sesskey' => sesskey(), 'view' => $view, 'sessionid' => $sessionid]);

        $operator = $this->get_operator();
        $attendancecount = 0;
        $returnurl = new \moodle_url('/mod/hybridteaching/attendance.php?id=' . $this->cm->id . '&view=' . $view . '');
        if ($view == 'attendlog') {
            $buildparams = ['attid' => $attid, 'h' => $hybridteachingid, 'id' => $id,
            'returnurl' => $returnurl, 'view' => $view, 'userid' => $userid, ];
            $params['id'] = $id;
            $logparams = [
                'attid' => $attid,
                'sessionid' => $sessionid,
                'sort' => $sort,
                'dir' => $dir,
                'url' => $url,
                'buildparams' => $buildparams,
                'table' => $table,
                'return' => $return,
                'selectedstudent' => $selectedstudent,
                'sessionoptions' => $sessionoptions,
                'params' => $params, ];
            $return = $this->print_attendance_log_table($logparams);
            if ($return) {
                return $return;
            }
        }
        if ($view == 'extendedstudentatt') {
            $extendedstudentsparams = [
                'hybridteachingid' => $hybridteachingid,
                'id' => $id,
                'sort' => $sort,
                'dir' => $dir,
                'page' => $page,
                'perpage' => $perpage,
                'sessionid' => $sessionid,
                'fname' => $fname,
                'lname' => $lname,
                'userid' => $userid,
                'return' => $return,
                'extrasql' => $extrasql,
                'operator' => $operator,
                'attid' => $attid,
                'url' => $url,
                'table' => $table,
                'returnurl' => $returnurl,
                'selectedfilter' => $selectedfilter,
                'group' => $group, ];
            $return = $this->print_attendance_extendedstudent_table($extendedstudentsparams);
            if ($return) {
                return $return;
            }
        }
        if ($view == 'studentattendance') {
            $studentattsessparams = [
                'hybridteachingid' => $hybridteachingid,
                'id' => $id,
                'page' => $page,
                'perpage' => $perpage,
                'params' => $params,
                'extrasql' => $extrasql,
                'operator' => $operator,
                'sessionid' => $sessionid,
                'userid' => $userid,
                'return' => $return,
                'returnurl' => $returnurl,
                'url' => $url,
                'groupmode' => $groupmode,
                'table' => $table,
                'optionsformparams' => $optionsformparams,
                'attresume' => $attresume,
                'selecteduser' => $selecteduser,
            ];
            $return = $this->print_student_sessions_table($studentattsessparams);
            return $return;
        }
        if (has_capability('mod/hybridteaching:sessions', $this->context, $userid) && !$editing) {
            $view == 'studentattendance' ?
            $return .= "<a href='attendance.php?id=".$id."&view=studentattsessions'  class='btn btn-info mr-3' role='button'>" .
                get_string('allsessions', 'hybridteaching') . "</a>" : '';
        }
        $attendancelist = [];
        if ($view != 'attendlog') {
            $sort = optional_param('sort', 'starttime', PARAM_ALPHANUMEXT);
            $dir = optional_param('dir', 'DESC', PARAM_ALPHA);
            $attendancelist = $attendancecontroller->load_attendance($page, $perpage, $params, $extrasql,
                $operator, $sort, $dir, $view, $sessionid, $fname, $lname);
            $attendanceassist = $attendancecontroller->load_attendance_assistance($params, $extrasql, $operator);
            $attendancecount = $attendancecontroller->count_attendance( $fname, $lname, $selectedsession, $params, $operator);
        }
        $view == 'studentattendance' ? $baseurl = new \moodle_url('/mod/hybridteaching/attendance.php?view=' .
            $view, ['id' => $id, 'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage,
            'sessionid' => $sessionid, 'userid' => $selecteduser, 'fname' => $fname, 'lname' => $lname,
            'attfilter' => $selectedfilter, ])
        :
        $baseurl = new \moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, ['id' => $id,
            'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid, 'fname' => $fname,
            'lname' => $lname, 'attfilter' => $selectedfilter, ]);
            $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
        if (!$groupmode) {
            $groupmode = $attendancecontroller->attendances_uses_groups($attendancelist);
            if (!$groupmode && ($view == 'sessionattendance')) {
                unset($table->head[1]);
                $optionsformparams['groupexception'] = $groupmode;
            }
        }
        if ($view == 'sessionattendance') {
            list($course, $cm) = get_course_and_cm_from_instance($this->hybridteaching->id, 'hybridteaching');
            $event = \mod_hybridteaching\event\attendance_viewed::create([
                'objectid' => $this->hybridteaching->id,
                'context' => \context_module::instance($cm->id),
            ]);

            $event->trigger();

            $optionsform = new attendance_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
                $view . '&sessionid=' . $sessionid . '&userid=' . $selecteduser . '&sort=' . $sort . '&dir=' .
                $dir . '&fname=' . $fname . '&lname=' . $lname . '&groupid=' . $group, $optionsformparams);
            $attsessionsparams = [
                'attendancelist' => $attendancelist,
                'attendanceassist' => $attendanceassist,
                'editing' => $editing,
                'groupmode' => $groupmode,
                'id' => $id,
                'returnurl' => $returnurl,
                'baseurl' => $baseurl,
                'url' => $url,
                'userid' => $userid,
                'selectedfilter' => $selectedfilter,
                'table' => $table,
                'return' => $return,
                'optionsform' => $optionsform,
                'page' => $page,
                'perpage' => $perpage,
                'attendancecount' => $attendancecount,
                'sessionid' => $sessionid, ];
            $return = $this->print_attendance_sessions_table($attsessionsparams);
            if ($return) {
                return $return;
            }
        }

        foreach ($attendancelist as $attendance) {
            $session = $sessionscontroller->get_session($attendance['sessionid']);
            $date = $session->starttime;
            $hour = date('H:i', $date);
            $attendanceid = $attendance['id'];
            $date = date('l, j F Y H:i', $date);
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
            $userurl = new \moodle_url('/user/view.php', ['id' => $USER->id]);
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
                'visible' => $attendance['visible'],
                'enabled' => !$attendance['visible'] || $attexempt ? HYBRIDTEACHING_NOT_EXEMPT : HYBRIDTEACHING_EXEMPT,
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
        if ($view !== 'attendlog') {
            if (isset($attresume)) {
                $attresume->display();
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
        }
        $attendancetable = html_writer::table($table);
        if (has_capability('mod/hybridteaching:bulksessions', $this->context)) {
            $attendancetable .= $this->get_bulk_options_select($view);
        }
        if ($view == 'attendlog' && !$editing) {
            $return .= "<a href='attendance.php?id=".$id."&view=studentattendance'  class='btn btn-info mr-3' role='button'>" .
                get_string('studentsattendance', 'hybridteaching') . "</a>";
        }
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
        $paramsurl = ['view' => $view, 'sessionid' => $sessionid, 'sesskey' => sesskey()];
        $return .= html_writer::tag('form', $attendancetable, ['method' => 'post',
            'action' => new \moodle_url($CFG->wwwroot . '/mod/hybridteaching/action/attendance_action.php', $paramsurl), ]);
        $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
        $return .= $OUTPUT->box_end();
        return $return;
    }

    /**
     * Get the corresponding database column name for a given column and view.
     *
     * @param string $column The column name
     * @param string $view The view name
     * @return string The corresponding database column name
     */
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

    /**
     * Get the table options based on the provided body, params, url, and urledit.
     *
     * @param array $body Array with all the body parameters
     * @param array $params The params for build the options column
     * @param \moodle_url $url The url parameter
     * @param int $sessionid Session id
     * @return array
     */
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

    /**
     * Gets the table header based on the given columns, headers, and session ID.
     *
     * @param array $columns The columns for the table header.
     * @param string $headers The type of headers to generate.
     * @param int $sessionid The session ID.
     * @return array The generated table header.
     */
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
                $columns['strgroup'],
                $columns['strname'],
                $columns['strdate'],
                $columns['strduration'],
                $columns['strtype'],
                $columns['strattendance'],
                $columns['strgrade'],
                $columns['stroptions'],
            ];

        } else if ($headers == 'studentattsessions') {
            $header = [
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
                $columns['strpfp'],
                $columns['strlastfirstname'],
                $columns['strcombinedatt'],
                $columns['strclassroom'],
                $columns['strvc'],
                $columns['strgrade'],
                $columns['stroptions'],
            ];
        }
        if (!$this->hybridteaching->grade) {
            if (array_search($columns['strgrade'], $header)) {
                unset($header[array_search($columns['strgrade'], $header)]);
            }
        }
        return $header;
    }

    /**
     * Builds options for the given parameters and returns an array of options.
     *
     * @param array $body Array with all the body parameters
     * @param array $params The params for build the options column
     * @param \moodle_url $url The url parameter
     * @param int $sessionid Session id
     * @return array The array of options
     */
    public function build_options($body, $params, $url, $sessionid) {
        global $OUTPUT;
        $class = '';
        isset($params['attid']) ? $attid = $params['attid'] : $attid = 0;
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

        $view = html_writer::link(new \moodle_url($url, array_merge($params, ['action' => 'view',
          'sessionid' => $sessionid, 'attid' => $attid, ])), $OUTPUT->pix_icon($icon, get_string($strview),
          'moodle', ['class' => 'iconsmall']),
        );
        if ($params['view'] == 'sessionattendance') {
            if ($body['visible']) {
                $visible = html_writer::link(new \moodle_url($url, array_merge($params,
                    ['action' => 'disable', 'attid' => $attid, 'sessionid' => $sessionid])),
                    $OUTPUT->pix_icon('i/hide', get_string('disable'), 'moodle', ['class' => 'iconsmall', 'attid' => $attid]));
            } else {
                $visible = html_writer::link(new \moodle_url($url, array_merge($params,
                    ['action' => 'enable', 'sessionid' => $sessionid])),
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
            $userinf = html_writer::link(new \moodle_url($url, array_merge($params, ['action' => 'userinf', 'attid' => $attid])),
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
        if ($params['view'] == 'studentattendance' || $params['view'] == 'studentattsessions') {
            $options['log'] = html_writer::link(new \moodle_url($url, array_merge($params, ['action' => 'view',
            'sessionid' => $sessionid, 'attid' => $attid, 'log' => 1, ])), $OUTPUT->pix_icon('i/log', get_string('logs'),
             'moodle', ['class' => 'iconsmall']),
            );
        }
        return $options;
    }

    /**
     * Get the attendance row for the given parameters and options.
     *
     * @param array $params The parameters for the attendance row
     * @param array $options The options for the attendance row
     * @param string $tableview The view of the table
     * @param mixed|null $sessionid The session id, defaults to null
     * @return \html_table_row The attendance row
     */
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
                    $row = ([$OUTPUT->render($params['checkbox']),
                    $params['name'], $params['date'], $params['duration'], $params['typevc'], $params['classroom'], $params['vc'],
                        $options, ]);
                    break;
                }
                $row = ([$OUTPUT->render($params['checkbox']), $params['group'],
                $params['name'], $params['date'], $params['duration'], $params['typevc'], $params['classroom'], $params['vc'],
                    $options, ]);
                break;
            case 'studentattendance':
                if (!$params['group']) {
                        $row = ([$params['name'], $params['date'], $params['duration'], $params['type'], $params['attendance'],
                        $params['grade'], $options, ]);
                    break;
                }
                $row = ([$params['name'], $params['date'], $params['duration'], $params['type'], $params['attendance'],
                    $params['grade'], $options, ]);
                break;
            case 'extendedsessionatt':
                if (isset($sessionid) && $params['chosensession'] == 0 ) {
                    $session = $sessionscontroller->get_session($sessionid);
                    $sesname = $session->name;
                }
                $row = ([$OUTPUT->render($params['checkbox']), $params['pfp'],
                    $params['firstlastname'], !empty($sesname) ? $sesname : '', $params['type'],
                    $params['entrytime'], $params['leavetime'], $params['permanence'],
                    $params['attendance'], $params['grade'], $options, ]);
                break;
            case 'attendlog':
                $row = ([$params['hour'], $params['logaction'], $params['mark']]);
                break;
            case 'extendedstudentatt':
                $row = ([$params['pfp'], $params['firstlastname'],
                     $params['combinedatt'], $params['classroom'], $params['vc'], $params['grade'], $options, ]);
                break;
            default:
                $row = ([$OUTPUT->render($params['checkbox']), $params['group'],
                    $params['name'], $params['date'], $params['duration'], $params['type'], $params['attendance'],
                    $params['grade'], $options, ]);
                break;
        }
        if (isset($params['grade']) && !$this->hybridteaching->grade) {
            unset($row[array_search($params['grade'], $row)]);
        }
        $row = new \html_table_row($row);
        return $row;
    }

    /**
     * Get bulk options select based on the view.
     *
     * @param string $view The view of the table
     * @return string
     */
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

    /**
     * Get the operator for the attendance controller.
     *
     * @return string $operator The operator for the attendance controller
     */
    public function get_operator() {
        $operator = attendance_controller::OPERATOR_GREATER_THAN;
        return $operator;
    }

    /**
     * Check attendance filters and unset filtering if 'sort' parameter is not set.
     */
    public function check_attendance_filters() {
        global $SESSION;
        $sort = optional_param('sort', '', PARAM_ALPHANUMEXT);
        if (empty($sort)) {
            unset($SESSION->attendance_filtering);
        }
    }

    /**
     * Get the group filter for the current user in the context of the given course module.
     *
     * @return array An array containing the SQL condition and parameters for filtering by group.
     */
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

    /**
     * Print the attendance bulk table.
     *
     * @param array $attendlist The list of attendance items
     * @param string $view The view parameter (optional)
     * @return string
     */
    public function print_attendance_bulk_table($attendlist, $view = null) {
        global $OUTPUT, $DB, $PAGE;

        $sessionscontroller = new sessions_controller($this->hybridteaching);
        [$insql, $params] = $DB->get_in_or_equal($attendlist, SQL_PARAMS_NAMED, 'id');
        $extrasql = 'ha.id ' . $insql;

        if ($view == 'sessionbulk') {
            $columns = [
                'strname' => get_string('name'),
                'strdate' => get_string('date'),
                'strattendance' => get_string('exempt', 'hybridteaching'),
            ];
        } else {
            $columns = [
                'strpfp' => get_string('picture'),
                'strlastfirstname' => get_string('firstname') . ' / ' . get_string('lastname'),
                'strdate' => get_string('date'),
                'strattendance' => get_string('attendance', 'hybridteaching'),
                'strprevioues' => get_string('prevattend', 'hybridteaching'),
            ];
        }
        $return = $OUTPUT->heading(get_string('withselectedattends', 'hybridteaching'));
        $return .= $OUTPUT->box_start('generalbox');

        $table = new \html_table();
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
                $date = date('l, j F Y, H:i', $date);

                $body = [
                    'name' => $session->name,
                    'date' => $date,
                    'attendance' => $session->attexempt ? '<b>' . get_string('exempt', 'hybridteaching') . '<b>' : self::EMPTY,
                ];

                // Add a row to the table.
                $row = new \html_table_row($body);
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
            $date = date('l, j F Y, H:i', $date);

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
            $row = new \html_table_row($body);
            $table->data[] = $row;
        }
        $return .= html_writer::table($table);
        $return .= $OUTPUT->box_end();

        return $return;
    }

    /**
     * Print attendance log table.
     *
     * @param array $logparams Params to make the log attendance table
     * @param string $view The view name
     * @return string
     */
    protected function print_attendance_log_table($logparams, $view = 'attendlog') {
        global $OUTPUT, $USER;

        $attid = $logparams['attid'];
        $sessionid = $logparams['sessionid'];
        $sort = $logparams['sort'];
        $dir = $logparams['dir'];
        $url = $logparams['url'];
        $buildparams = $logparams['buildparams'];
        $table = $logparams['table'];
        $return = $logparams['return'];
        $selectedstudent = $logparams['selectedstudent'];
        $sessionoptions = $logparams['sessionoptions'];
        $params = $logparams['params'];

        $attendancecontroller = new attendance_controller($this->hybridteaching);
        $sessionscontroller = new sessions_controller($this->hybridteaching);
        has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $USER->id) ?
            $returntosession = 1 : $returntosession = 0;
        $att = $attendancecontroller->hybridteaching_get_attendance_from_id($attid);
        $att->sessionid != $sessionid ? $att = $attendancecontroller->hybridteaching_get_attendance($sessionid) : '';
        !$att ? $logs = false :
        $logs = $attendancecontroller->hybridteaching_get_attendance_logs($att->id, $sort, $dir);
        if (!$logs) {
            if (isset($sessionoptions)) {
                has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $USER->id) ?
                $sessionoptions->display() : '';
            }
            echo '<br><h2>' . get_string('nologsfound', 'hybridteaching') . '<h2>';
            $return .= html_writer::tag('a', get_string('viewstudentinfo', 'hybridteaching'),
                ['href' => 'attendance.php?id='.$params['id'].'&view=studentattendance&userid='.
                $selectedstudent . '', 'class' => 'btn btn-primary', ]);
            $returntosession ?
            $return .= html_writer::tag('a', get_string('viewsessioninfo', 'hybridteaching'),
                ['href' => 'attendance.php?id='.$params['id'].'&view=extendedsessionatt&sessionid=' .
            $sessionid.'', 'class' => 'btn btn-primary', ]) : '';
            $return .= $OUTPUT->box_end();
            return $return;
        }
        $session = $sessionscontroller->get_session($att->sessionid);
        $usortedlogs = $attendancecontroller->hybridteaching_get_attendance_logs($att->id);
        $firstlog = $logs[array_key_first($usortedlogs)];
        $lastlog = $logs[array_key_last($usortedlogs)];
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
        if (isset($sessionoptions)) {
            has_capability('mod/hybridteaching:sessionsfulltable', $this->context, $user = $USER->id) ?
                $sessionoptions->display() : '';
        }

    }

    /**
     * Print attendance extended student table.
     *
     * @param array $extendedstudentsparams The parameters for extended students table
     * @param string $view The view type
     * @return string
     */
    protected function print_attendance_extendedstudent_table($extendedstudentsparams, $view = 'extendedstudentatt') {
        global $OUTPUT, $USER, $COURSE, $CFG;

        $hybridteachingid = $extendedstudentsparams['hybridteachingid'];
        $id = $extendedstudentsparams['id'];
        $sort = $extendedstudentsparams['sort'];
        $dir = $extendedstudentsparams['dir'];
        $page = $extendedstudentsparams['page'];
        $perpage = $extendedstudentsparams['perpage'];
        $sessionid = $extendedstudentsparams['sessionid'];
        $fname = $extendedstudentsparams['fname'];
        $lname = $extendedstudentsparams['lname'];
        $userid = $extendedstudentsparams['userid'];
        $return = $extendedstudentsparams['return'];
        $extrasql = $extendedstudentsparams['extrasql'];
        $operator = $extendedstudentsparams['operator'];
        $attid = $extendedstudentsparams['attid'];
        $url = $extendedstudentsparams['url'];
        $table = $extendedstudentsparams['table'];
        $returnurl = $extendedstudentsparams['returnurl'];
        $selectedfilter = $extendedstudentsparams['selectedfilter'];
        $group = $extendedstudentsparams['group'];

        $attendancecontroller = new attendance_controller($this->hybridteaching);
        $sessionscontroller = new sessions_controller($this->hybridteaching);
        $participationrecords = $attendancecontroller->hybridteaching_get_students_participation($hybridteachingid,
            $sort, $dir, $fname, $lname);
        $baseurl = new \moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, ['id' => $id,
        'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid, ]);
        $attendancecount = count($participationrecords);
        $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
        $attuser = $attendancecontroller->load_sessions_attendant($userid);
        $userpicture = $OUTPUT->user_picture($attuser);
        $params['view'] = $view;
        foreach ($participationrecords as $participation) {
            $params['userid'] = $participation->userid;
            $attendanceassist = $attendancecontroller->get_student_participation($hybridteachingid, $params['userid']);
            $attendanceassist ? $participation->vc = $attendanceassist->vc : $participation->vc = 0;
            $attendanceassist ? $participation->classroom = $attendanceassist->classroom : $participation->classroom = 0;
            $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching',
                $this->hybridteaching->id, $participation->userid);
            $attuser = $attendancecontroller->load_sessions_attendant($participation);
            $userpicture = $OUTPUT->user_picture($attuser);
            $attendanceid = $attid;
            $gradevalue = 0;
            if (isset($usertotalgrade->items[0])) {
                $gradevalue = $usertotalgrade->items[0]->grades[$participation->userid]->grade;
            }
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
                'view' => $view, 'userid' => $attuser->id, ];
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
                    'view' => $view, 'userid' => $attuser->id, ];

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
        $attusrfilter = new attuserfilter_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?',
        ['id' => $id, 'att' => $attid, 'fname' => $fname, 'lname' => $lname, 'hid' => $hybridteachingid,
            'view' => $view, 'sessid' => $sessionid, 'sort' => $sort, 'dir' => $dir,
            'perpage' => $perpage, 'attfilter' => $selectedfilter, 'groupid' => $group, ]);
        if (isset($attusrfilter)) {
            $attusrfilter->display();
        }
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
        $paramsurl = ['view' => $view, 'sessionid' => $sessionid, 'sesskey' => sesskey()];
        $return .= html_writer::tag('form', $attendancetable, ['method' => 'post',
            'action' => new \moodle_url($CFG->wwwroot . '/mod/hybridteaching/action/attendance_action.php', $paramsurl), ]);
        $baseurl = new \moodle_url('/mod/hybridteaching/attendance.php?view=' . $view, ['id' => $id,
            'sort' => $sort, 'dir' => $dir, 'perpage' => $perpage, 'sessionid' => $sessionid, ]);
        $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
        $return .= $OUTPUT->box_end();
        return $return;
    }

    /**
     * Function for printing the attendance sessions table.
     *
     * @param array $attsessionsparams The parameters for the attendance sessions table
     * @param string $view The view of the attendance table
     * @return string
     */
    protected function print_attendance_sessions_table($attsessionsparams, $view = 'sessionattendance') {
        global $OUTPUT, $CFG, $USER;

        $attendancelist = $attsessionsparams['attendancelist'];
        $attendanceassist = $attsessionsparams['attendanceassist'];
        $editing = $attsessionsparams['editing'];
        $groupmode = $attsessionsparams['groupmode'];
        $id = $attsessionsparams['id'];
        $returnurl = $attsessionsparams['returnurl'];
        $baseurl = $attsessionsparams['baseurl'];
        $url = $attsessionsparams['url'];
        $userid = $attsessionsparams['userid'];
        $selectedfilter = $attsessionsparams['selectedfilter'];
        $table = $attsessionsparams['table'];
        $return = $attsessionsparams['return'];
        $optionsform = $attsessionsparams['optionsform'];
        $page = $attsessionsparams['page'];
        $perpage = $attsessionsparams['perpage'];
        $attendancecount = $attsessionsparams['attendancecount'];
        $sessionid = $attsessionsparams['sessionid'];

        $attendancecontroller = new attendance_controller($this->hybridteaching);
        $sessionscontroller = new sessions_controller($this->hybridteaching);
        $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
        foreach ($attendancelist as $sessionlist) {
            $session = $sessionscontroller->get_session($sessionlist['id']);
            $date = $session->starttime;
            $hour = date('H:i', $date);
            $date = date('l, j F Y H:i', $date);
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
                $typealias = get_string('classroom', 'hybridteaching');
            }
            $body = [
                'class' => '',
                'group' => $groupbody,
                'name' => $session->attexempt ? $session->name . ' ' .
                    get_string('attnotforgrade', 'hybridteaching') : $session->name,
                'date' => $date,
                'duration' => !empty($session->duration) ? helper::get_hours_format($session->duration) : self::EMPTY,
                'typevc' => $typealias,
                'vc' => isset($attassistance['vc']) ? $attassistance['vc'] : 0,
                'classroom' => isset($attassistance['classroom']) ? $attassistance['classroom'] : 0,
                'attexempt' => $session->attexempt,
                'visible' => $session->visibleatt,
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
            $params = ['attid' => $session->id, 'h' => $hybridteachingid, 'id' => $id,
                'view' => $view, 'userid' => $userid, ];

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
        if (isset($attresume)) {
            $attresume->display();
        }
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
        $paramsurl = ['view' => $view, 'sessionid' => $sessionid, 'sesskey' => sesskey()];
        $return .= html_writer::tag('form', $attendancetable, ['method' => 'post',
            'action' => new \moodle_url($CFG->wwwroot . '/mod/hybridteaching/action/attendance_action.php', $paramsurl), ]);
        $return .= $OUTPUT->paging_bar($attendancecount, $page, $perpage, $baseurl);
        $return .= $OUTPUT->box_end();
        return $return;
    }

    /**
     * Print student sessions table.
     *
     * @param array $studentattsessparams The parameters for the student sessions table
     * @param string $view The view of the student sessions table
     * @return string
     */
    protected function print_student_sessions_table($studentattsessparams, $view = 'studentattendance') {
        global $CFG, $OUTPUT, $USER, $COURSE;

        $hybridteachingid = $studentattsessparams['hybridteachingid'];
        $id = $studentattsessparams['id'];
        $page = $studentattsessparams['page'];
        $perpage = $studentattsessparams['perpage'];
        $params = $studentattsessparams['params'];
        $extrasql = $studentattsessparams['extrasql'];
        $operator = $studentattsessparams['operator'];
        $sessionid = $studentattsessparams['sessionid'];
        $userid = $studentattsessparams['userid'];
        $return = $studentattsessparams['return'];
        $returnurl = $studentattsessparams['returnurl'];
        $url = $studentattsessparams['url'];
        $groupmode = $studentattsessparams['groupmode'];
        $table = $studentattsessparams['table'];
        $optionsformparams = $studentattsessparams['optionsformparams'];
        $attresume = $studentattsessparams['attresume'];
        $selecteduser = $studentattsessparams['selecteduser'];

        $attendancecontroller = new attendance_controller($this->hybridteaching);
        $sessionscontroller = new sessions_controller($this->hybridteaching);
        $grades = new grades();
        $sort = optional_param('sort', 'starttime', PARAM_ALPHANUMEXT);
        $dir = optional_param('dir', 'DESC', PARAM_ALPHA);
        $returnurl = new \moodle_url('/mod/hybridteaching/attendance.php?id=' . $this->cm->id . '&view='. $view .
            '&page=' . $page . '&perpage=' . $perpage . '&sort=' . $sort . '&dir=' . $dir . '&userid=' . $userid . '');
        $sessionslist = $attendancecontroller->load_attendance($page, $perpage, $params, $extrasql,
            $operator, $sort, $dir, $view, $sessionid);
        $sessionsuser = (object) $USER;
        $userpicture = $OUTPUT->user_picture($sessionsuser);
        $userurl = new \moodle_url('/user/view.php', ['id' => $USER->id]);
        foreach ($sessionslist as $session) {
            $date = $session['starttime'];
            $hour = date('H:i', $date);
            $date = date('l, j F Y H:i', $date);
            $attributes = [
                'type'  => 'checkbox',
                'name'  => $session['name'],
                'value' => get_string('active'),
                'class' => 'attendance-validated', ];
            $attendance = $attendancecontroller->get_session_attendance($session['id'], $selecteduser);
            $params['editing'] ? true : $attributes['disabled'] = true;
            $usertotalgrade = grade_get_grades($COURSE->id, 'mod', 'hybridteaching', $this->hybridteaching->id, $selecteduser);
            $attexempt = $session['attexempt'];
            $groupbody = '';
            if ($groupmode) {
                $session['groupid'] == 0 ? $groupbody = get_string('commonattendance', 'hybridteaching') :
                    $groupbody = groups_get_group($session['groupid'])->name;
            }
            if ($attendance) {
                $attendanceid = $attendance->id;
                $attendance->status == HYBRIDTEACHING_ATTSTATUS_VALID ? $attributes['checked'] = true : '';
                $connectiontime = helper::get_hours_format($attendance->connectiontime);
                empty($connectiontime) ? $connectiontime = self::EMPTY : false;
                $submitb = html_writer::empty_tag('input', $attributes);
                $submitb .= '<br>' . $connectiontime;
                $attendance->status == 2 ? $submitb .= '<br>' . get_string('late', 'hybridteaching') : '';
                $attendance->status == 3 ? $submitb = get_string('exempt', 'hybridteaching') : '';
                $attendance->status == 4 ? $submitb .= '<br>' . get_string('earlyleave', 'hybridteaching') : '';
                $attendgrade = $grades->calc_att_grade_for($this->hybridteaching, $session['id'], $attendance->id);
                $attendance->connectiontime == 0 ? $atttype = get_string('noatt', 'hybridteaching') : $atttype = '';
                if (empty($atttype)) {
                    $attendance->type == 0 ? $atttype = get_string('classroom', 'hybridteaching') :
                        $atttype = get_string('videoconference', 'hybridteaching');
                }

                $body = [
                    'class' => '',
                    'attendanceid' => $attendance->id,
                    'group' => $groupbody,
                    'name' => $session['name'],
                    'date' => $date,
                    'duration' => !empty($session['duration']) ? helper::get_hours_format($session['duration']) : self::EMPTY,
                    'firstlastname' => $sessionsuser->lastname . ' / ' . $sessionsuser->firstname,
                    'attendance' => $session['attexempt'] ? '<b>' . get_string('exempt', 'hybridteaching') . '<b>' : $submitb,
                    'type' => $atttype,
                    'grade' => round($attendgrade ?? 0, 2) . ' / ' .
                        round($usertotalgrade->items[0]->grades[$attendance->userid]->grade ?? 0, 2) .
                        ' / ' . $this->hybridteaching->grade,
                    'enabled' => !$attendance->visible || $attexempt ? HYBRIDTEACHING_NOT_EXEMPT : HYBRIDTEACHING_EXEMPT,
                    'visible' => $attendance->visible,
                    'status' => $attendance->status,
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
                $tableparams = ['h' => $hybridteachingid, 'id' => $id, 'sessionid' => $session['id'],
                    'view' => $view, 'userid' => $userid, 'attid' => $attendance->id, ];
                $options = $this->get_table_options($body, $tableparams, $url, $session['id']);
                $class = $options['class'];
                if (!$body['enabled']) {
                    $class = 'dimmed_text';
                }
                if (!$body['visible']) {
                    !$params['editing'] ? $class .= ' hidden' : $body['name'] .= ' '
                        . get_string('hiddenuserattendance', 'hybridteaching');
                }
                $options = $options['options'];
                // Add a row to the table.
                $body['chosensession'] = $session['id'];
                $row = $this->get_attendance_row($body, $options, $view, $session['id']);
                if (!empty($class)) {
                    $row->attributes['class'] = $class;
                }
                $table->data[] = $row;
            } else {
                $attendgrade = 0;
                $body = [
                    'class' => '',
                    'sessionid' => $session['id'],
                    'group' => $groupbody,
                    'name' => $session['name'],
                    'date' => $date,
                    'duration' => !empty($session['duration']) ? helper::get_hours_format($session['duration']) : self::EMPTY,
                    'firstlastname' => $sessionsuser->lastname . ' / ' . $sessionsuser->firstname,
                    'attendance' => $attexempt ? '<b>' . get_string('exempt', 'hybridteaching') . '<b>' :
                        get_string('noatt', 'hybridteaching'),
                    'type' => isset($atttype) ? get_string('eventsessionfinished', 'hybridteaching') :
                        get_string('sessiontobecreated', 'hybridteaching'),
                    'grade' => round($attendgrade ?? 0, 2) . ' / ' .
                        round($usertotalgrade->items[0]->grades[$selecteduser]->grade ?? 0, 2) .
                        ' / ' . $this->hybridteaching->grade,
                    'enabled' => !$session['visible'] || $attexempt ? HYBRIDTEACHING_NOT_EXEMPT : HYBRIDTEACHING_EXEMPT,
                    'visible' => $session['visible'],
                    'checkbox' => new \core\output\checkbox_toggleall('attendance-table', false, [
                        'id' => 'session-' . $sessionid,
                        'name' => 'session[]',
                        'classes' => 'm-1',
                        'checked' => false,
                        'value' => $sessionid,
                    ]),
                ];
                if (!$body['enabled']) {
                    $body['class'] = 'dimmed_text';
                }
                $hybridteachingid = empty($hybridteachingid) ? $this->cm->instance : $hybridteachingid;
                $tableparams = ['h' => $hybridteachingid, 'id' => $id,
                    'view' => $view, 'userid' => $userid, ];

                $options = $this->get_table_options($body, $tableparams, $url, $sessionid);
                $class = $options['class'];
                if (!$body['enabled']) {
                    $class = 'dimmed_text';
                }
                if (!$body['visible']) {
                    !$params['editing'] ? $class .= ' hidden' : $body['name'] .= ' '
                        . get_string('hiddenuserattendance', 'hybridteaching');
                }
                $options = null;
                // Add a row to the table.
                $body['chosensession'] = $sessionid;
                $row = $this->get_attendance_row($body, $options, $view, $session['id']);
                if (!empty($class)) {
                    $row->attributes['class'] = $class;
                }
                $table->data[] = $row;
            }

        }
        if (isset($attresume)) {
            $attresume->display();
        }
        $optionsform = new attendance_options_form($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
            $view . '&sessionid=' . $sessionid . '&userid=' . $userid . '&sort=' . $sort . '&dir=' .
            $dir . '&groupid=' . $groupmode, $optionsformparams);
        $optionsform->display();
        if (!$groupmode) {
            $groupmode = $attendancecontroller->attendances_uses_groups($sessionslist);
            if (!$groupmode) {
                unset($table->head[0]);
                $optionsformparams['groupexception'] = $groupmode;
            }
        }
        $params['editing'] ? $countparams = ['hybridteachingid' => $hybridteachingid] :
            $countparams = ['hybridteachingid' => $hybridteachingid, 'visible' => 1];
        $countsessions = $sessionscontroller->count_sessions($countparams);
        $attendancetable = html_writer::table($table);
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'h', 'value' => $hybridteachingid]);
        $attendancetable .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $id]);
        $return .= $OUTPUT->paging_bar($countsessions, $page, $perpage, $returnurl);
        $return .= html_writer::tag('form', $attendancetable, ['method' => 'post',
        'action' => $CFG->wwwroot . '/mod/hybridteaching/classes/action/attendance_action.php?view=' .
        $view . '&sessionid=' . $sessionid, ]);
        $return .= $OUTPUT->paging_bar($countsessions, $page, $perpage, $returnurl);
        $return .= $OUTPUT->box_end();
        return $return;
    }
}
