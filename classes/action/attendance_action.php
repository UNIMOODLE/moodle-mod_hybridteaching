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
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require('../../../../config.php');
require_once('../controller/attendance_controller.php');
require('../output/attendance_render.php');
require_once('../form/attendance_form.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$moduleid = required_param('id', PARAM_INT);
$hybridteachingid = required_param('h', PARAM_INT);
$attendanceids = optional_param('attendance[]', 0, PARAM_INT);
$sessionid = optional_param('sessionid', 0, PARAM_INT);
$view = optional_param('view', 'sessionattendance', PARAM_TEXT);
$userid = optional_param('userid', null, PARAM_INT);
$editing = optional_param('editing', 1, PARAM_INT);
$attid = optional_param('attid', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($moduleid, 'hybridteaching');

$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);
$url = new moodle_url('/mod/hybridteaching/classes/action/attendance_action.php');
$PAGE->set_url($url);
$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);

//require_sesskey();
require_login();

$returnparams = [
    'id' => $moduleid,
    'view' => $view,
];

$return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php', $returnparams);
$hybridteaching = $DB->get_record('hybridteaching', array('id' => $hybridteachingid), '*', MUST_EXIST);
$attendancecontroller = new attendance_controller($hybridteaching, 'hybridteaching_attendance');
$attendancelist = $attendancecontroller->load_attendance();
$mform = null;

switch ($action) {
    case 'disable':
        $attendancecontroller->enable_data($attid, false, 'hybridteaching_attendance');
        $attendancecontroller->update_session_exempt($attid, 1);
        break;
    case 'enable':
        $attendancecontroller->enable_data($attid, true, 'hybridteaching_attendance');
        $attendancecontroller->update_session_exempt($attid, 0);
        break;
    case 'view':
        if ($view == 'sessionattendance') {
            $editing == 0 ? redirect ($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'extendedsessionatt', 'userid' => $userid))) 
            :
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'extendedsessionatt', 'editing' => 1)));
        }

        if ($view == 'extendedsessionatt') {
            $editing == 0 ? redirect ($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'attendlog', 'userid' => $userid, 'attid' => $attid))) 
            :
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'attendlog', 'attid' => $attid, 'editing' => 1)));
        }

        if ($view == 'studentattendance') {
            $editing == 0 ? redirect ($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'extendedsessionatt',
                 'userid' => $userid, 'attid' => $attid))) 
            :
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'extendedsessionatt', 'attid' => $attid, 'editing' => 1)));
        }

        if ($view == 'extendedstudentatt') {
            $editing == 0 ? redirect ($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => 0, 'view' => 'studentattendance',
                 'userid' => $userid, 'attid' => $attid))) 
            :
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => 0, 'view' => 'studentattendance', 'attid' => $attid,
                 'userid' => $userid, 'editing' => 0)));
        }
        break;
        //activar desactivar multiple sessiones de attendance.
    case 'bulksetattendance':
        $attendsid = optional_param_array('attendance', '', PARAM_SEQUENCE);
        if ($attendsid) {
            $useratt = array_key_first($attendsid);
            $userid = $attendancecontroller->hybridteaching_get_attendance_from_id($attendsid[$useratt])->userid;
            $view == 'studentattendance' ?
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'userid' => $userid, 'view' => $view, 'editing' => 1))
            :
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => $view, 'editing' => 1));
    
        } else {
            $view == 'studentattendance' ?
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'userid' => $userid, 'view' => $view, 'editing' => 1))
            :
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => $view, 'editing' => 1));
        }

        $ids = optional_param('ids', '', PARAM_ALPHANUMEXT);

        $PAGE->set_title(format_string($hybridteaching->name));
        $PAGE->set_heading(format_string($course->fullname));

        $attendslist = !empty($attendsid) ? implode('_', $attendsid) : '';
        $params = [
            'id' => $moduleid,
            'action' => $action,
        ];
        $formparams = compact('attendslist', 'cm', 'hybridteaching', 'sessionid', 'view', 'userid');
        $attendancerenderer = new hybridteaching_attendance_render($hybridteaching, $attendslist);
        $mform = ($action === 'bulksetattendance') ? new bulk_set_attendance_form($url, $formparams) 
            : false;
        if ($mform->is_cancelled()) {
            redirect($return);
        }
        if ($formdata = $mform->get_data()) {
            $attendancecontroller->update_multiple_attendance(explode('_', $formdata->ids), $formdata);
            redirect($return);
        }

        echo $OUTPUT->header();
        echo $attendancerenderer->print_attendance_bulk_table($attendsid);
        echo $mform->display();
        echo $OUTPUT->footer();
        break;
    case 'bulksetexempt':
        $attendsid = optional_param_array('attendance', '', PARAM_SEQUENCE);
        if ($attendsid) {
            $useratt = array_key_first($attendsid);
            $userid = $attendancecontroller->hybridteaching_get_attendance_from_id($attendsid[$useratt])->userid;
            $view == 'studentattendance' ?
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'userid' => $userid, 'view' => $view, 'editing' => 1))
            :
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => $view, 'editing' => 1));
    
        } else {
            $view == 'studentattendance' ?
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'userid' => $userid, 'view' => $view, 'editing' => 1))
            :
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'sessionid' => $sessionid, 'view' => $view, 'editing' => 1));
        }
        $ids = optional_param('ids', '', PARAM_ALPHANUMEXT);
        $PAGE->set_title(format_string($hybridteaching->name));
        $PAGE->set_heading(format_string($course->fullname));

        $attendslist = !empty($attendsid) ? implode('_', $attendsid) : '';
        $params = [
            'id' => $moduleid,
            'action' => $action,
        ];
        $formparams = compact('attendslist', 'cm', 'hybridteaching', 'sessionid', 'view', 'userid');
        $attendancerenderer = new hybridteaching_attendance_render($hybridteaching, $attendslist);
        $mform = ($action === 'bulksetexempt') ? new bulk_set_exempt_form($url, $formparams)
            : false;
        if ($mform->is_cancelled()) {
            redirect($return);
        }
        if ($formdata = $mform->get_data()) {
            $attendancecontroller->update_multiple_attendance(explode('_', $formdata->ids), $formdata);
            redirect($return);
        }

        echo $OUTPUT->header();
        echo $attendancerenderer->print_attendance_bulk_table($attendsid, 'bulksetexempt');
        echo $mform->display();
        echo $OUTPUT->footer();
        break;
    case 'bulksetsessionexempt':
        $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                array('id' => $moduleid, 'view' => $view, 'editing' => 1));
        $attendsid = optional_param_array('attendance', '', PARAM_SEQUENCE);
        $ids = optional_param('ids', '', PARAM_ALPHANUMEXT);

        $PAGE->set_title(format_string($hybridteaching->name));
        $PAGE->set_heading(format_string($course->fullname));

        $sessionsids = [];
        if ($attendsid) {
            foreach ($attendsid as $attid) {
                $sessionid = $attendancecontroller->hybridteaching_get_attendance_from_id($attid)->sessionid;
                $sessionsids[] = $sessionid;
            }
        }
        $sessionslist = !empty($sessionsids) ? implode('_', $sessionsids) : '';
        $params = [
            'id' => $moduleid,
            'action' => $action,
        ];

        $formparams = compact('sessionslist', 'cm', 'hybridteaching', 'sessionid', 'view');
        $attendancerenderer = new hybridteaching_attendance_render($hybridteaching, $sessionslist);
        $mform = ($action === 'bulksetsessionexempt') ? new bulk_set_session_exempt_form($url, $formparams)
            : false;
        if ($mform->is_cancelled()) {
            redirect($return);
        }
        if ($formdata = $mform->get_data()) {
            $attendancecontroller->update_multiple_sessions_exempt(explode('_', $formdata->ids), $formdata);
            redirect($return);
        }

        echo $OUTPUT->header();
        echo $attendancerenderer->print_attendance_bulk_table($sessionsids, 'sessionbulk');
        echo $mform->display();
        echo $OUTPUT->footer();
        break;
    default:
        redirect($return);
        break;
}

if (empty($mform)) {
    redirect($return);
}