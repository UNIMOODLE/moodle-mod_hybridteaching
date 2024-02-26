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

define('NO_OUTPUT_BUFFERING', true);

use mod_hybridteaching\controller\attendance_controller;
use mod_hybridteaching\output\attendance_render;
use mod_hybridteaching\form\bulk_set_attendance_form;
use mod_hybridteaching\form\bulk_set_exempt_form;
use mod_hybridteaching\form\bulk_set_session_exempt_form;


require('../../../config.php');

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$moduleid = required_param('id', PARAM_INT);
$hybridteachingid = required_param('h', PARAM_INT);
$attendanceids = optional_param('attendance[]', 0, PARAM_INT);
$sessionid = optional_param('sessionid', 0, PARAM_INT);
$view = optional_param('view', 'sessionattendance', PARAM_TEXT);
$userid = optional_param('userid', null, PARAM_INT);
$attid = optional_param('attid', 0, PARAM_INT);
$log = optional_param('log', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($moduleid, 'hybridteaching');

$hybridteaching = $DB->get_record('hybridteaching', ['id' => $cm->instance], '*', MUST_EXIST);
$url = new moodle_url('/mod/hybridteaching/action/attendance_action.php');
$PAGE->set_url($url);
$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);

require_login();
require_sesskey();

$returnparams = [
    'id' => $moduleid,
    'view' => $view,
];

$return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php', $returnparams);
$hybridteaching = $DB->get_record('hybridteaching', ['id' => $hybridteachingid], '*', MUST_EXIST);
$attendancecontroller = new attendance_controller($hybridteaching, 'hybridteaching_attendance');
$mform = null;

switch ($action) {
    case 'disable':
        $attendancecontroller->enable_data($attid, false, 'hybridteaching_attendance');
        attendance_controller::update_session_visibility($sessionid, 0);
        break;
    case 'enable':
        $attendancecontroller->enable_data($attid, true, 'hybridteaching_attendance');
        attendance_controller::update_session_visibility($sessionid, 1);
        break;
    case 'view':
        if ($view == 'sessionattendance') {
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'extendedsessionatt']));
        }

        if ($view == 'extendedsessionatt') {
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'attendlog', 'attid' => $attid, 'userid' => $userid]));
        }

        if ($view == 'studentattendance') {
            $log ? $rview = 'attendlog' : $rview = 'extendedsessionatt';
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => $sessionid, 'view' => $rview, 'attid' => $attid, 'userid' => $userid]));
        }

        if ($view == 'extendedstudentatt') {
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => 0, 'view' => 'studentattendance', 'attid' => $attid, 'userid' => $userid]));
        }
        if ($view == 'studentattsessions') {
            redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => $sessionid, 'view' => 'attendlog', 'userid' => $userid, 'attid' => $attid]));
        }
        break;
    case 'userinf':
        redirect($return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
            ['id' => $moduleid, 'sessionid' => 0, 'view' => 'studentattendance', 'attid' => $attid,
            'userid' => $attendancecontroller::hybridteaching_get_attendance_from_id($attid)->userid, ]));
        break;

        // Bulk options.
    case 'bulksetattendance':
        $attendsid = optional_param_array('attendance', '', PARAM_SEQUENCE);
        if ($attendsid) {
            $useratt = array_key_first($attendsid);
            $userid = $attendancecontroller->hybridteaching_get_attendance_from_id($attendsid[$useratt])->userid;
            $view == 'studentattendance' ?
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'userid' => $userid, 'view' => $view])
            :
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => $sessionid, 'view' => $view]);

        } else {
            $view == 'studentattendance' ?
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'userid' => $userid, 'view' => $view])
            :
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => $sessionid, 'view' => $view]);
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
        $attendancerenderer = new attendance_render($hybridteaching, $attendslist);
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
                ['id' => $moduleid, 'userid' => $userid, 'view' => $view])
            :
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => $sessionid, 'view' => $view]);

        } else {
            $view == 'studentattendance' ?
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'userid' => $userid, 'view' => $view])
            :
            $return = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
                ['id' => $moduleid, 'sessionid' => $sessionid, 'view' => $view]);
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
        $attendancerenderer = new attendance_render($hybridteaching, $attendslist);
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
                ['id' => $moduleid, 'view' => $view]);
        $attendsid = optional_param_array('session', '', PARAM_SEQUENCE);
        $ids = optional_param('ids', '', PARAM_ALPHANUMEXT);

        $PAGE->set_title(format_string($hybridteaching->name));
        $PAGE->set_heading(format_string($course->fullname));

        $sessionsids = [];
        if ($attendsid) {
            foreach ($attendsid as $attid) {
                $sessionsids[] = $attid;
            }
        }

        $sessionslist = !empty($sessionsids) ? implode('_', $sessionsids) : '';
        $params = [
            'id' => $moduleid,
            'action' => $action,
        ];

        $formparams = compact('sessionslist', 'cm', 'hybridteaching', 'sessionid', 'view');
        $attendancerenderer = new attendance_render($hybridteaching, $sessionslist);
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

if ($action == 'disable' || $action == 'enable') {
    $users = $DB->get_records('hybridteaching_attendance', ['id' => $attid], '', 'DISTINCT userid');
    list($course, $cm) = get_course_and_cm_from_instance($hybridteachingid, 'hybridteaching');
    foreach ($users as $user) {
        $event = \mod_hybridteaching\event\attendance_updated::create([
            'objectid' => $hybridteachingid,
            'context' => \context_module::instance($cm->id),
            'relateduserid' => $user->userid,
            'other' => [
                'sessid' => $sessionid,
                'attid' => $attid,
            ],
        ]);

        $event->trigger();
    }

}

if (empty($mform)) {
    redirect($return);
}
