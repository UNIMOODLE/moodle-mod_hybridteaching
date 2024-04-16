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
use mod_hybridteaching\local\attendance_table;
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

$urlview = new moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php',
    ['id' => $moduleid]);

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
        $view == 'sessionattendance' ? $urlview->params(['view' => 'extendedsessionatt', 'sessionid' => $sessionid]) : '';
        $view == 'extendedsessionatt' ? $urlview->params(['view' => 'attendlog', 'userid' => $userid,
            'sessionid' => $sessionid, 'attid' => $attid, ]) : '';
        if ($view == 'studentattendance') {
            $log ? $rview = 'attendlog' : $rview = 'extendedsessionatt';
            $urlview->params(['view' => $rview, 'userid' => $userid, 'attid' => $attid, 'sessionid' => $sessionid]);
        }
        $view == 'extendedstudentatt' ? $urlview->params(['sessionid' => 0, 'view' => 'studentattendance',
            'userid' => $userid, 'attid' => $attid, ]) : '';
        $view == 'studentattsessions' ? $urlview->params(['view' => 'attendlog', 'userid' => $userid, 'attid' => $attid,
            'sessionid' => $sessionid, ]) : '';

        redirect($urlview);

        break;
    case 'userinf':

        $urlview->params(['sessionid' => 0, 'view' => 'studentattendance', 'attid' => $attid,
            'userid' => $attendancecontroller::hybridteaching_get_attendance_from_id($attid)->userid, ]);
        redirect($urlview);
        break;

        // Bulk options.
    case 'bulksetattendance':
        $attendsid = optional_param_array('attendance', '', PARAM_SEQUENCE);
        if ($attendsid) {
            $useratt = array_key_first($attendsid);
            $userid = $attendancecontroller->hybridteaching_get_attendance_from_id($attendsid[$useratt])->userid;
            $view == 'studentattendance' ?
            $urlview->params(['view' => $view, 'userid' => $userid])
            :
            $urlview->params(['view' => $view, 'sessionid' => $sessionid]);

        } else {
            $view == 'studentattendance' ?
            $urlview->params(['view' => $view, 'userid' => $userid])
            :
            $urlview->params(['view' => $view, 'sessionid' => $sessionid]);
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
        $attendancetable = new attendance_table($hybridteaching, $attendslist);
        $mform = ($action === 'bulksetattendance') ? new bulk_set_attendance_form($url, $formparams)
            : false;
        if ($mform->is_cancelled()) {
            redirect($urlview);
        }
        if ($formdata = $mform->get_data()) {
            $attendancecontroller->update_multiple_attendance(explode('_', $formdata->ids), $formdata);
            redirect($urlview);
        }
        print_attendance_action($mform, $attendancetable, 'bulksetattendance', $attendsid, null);
        break;
    case 'bulksetexempt':
        $attendsid = optional_param_array('attendance', '', PARAM_SEQUENCE);
        if ($attendsid) {
            $useratt = array_key_first($attendsid);
            $userid = $attendancecontroller->hybridteaching_get_attendance_from_id($attendsid[$useratt])->userid;
            $view == 'studentattendance' ?
            $urlview->params(['userid' => $userid, 'view' => $view])
            :
            $urlview->params(['sessionid' => $sessionid, 'view' => $view]);

        } else {
            $view == 'studentattendance' ?
            $urlview->params(['view' => $view, 'userid' => $userid])
            :
            $urlview->params(['view' => $view, 'sessionid' => $sessionid]);

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
        $attendancetable = new attendance_table($hybridteaching, $attendslist);
        $mform = ($action === 'bulksetexempt') ? new bulk_set_exempt_form($url, $formparams)
            : false;
        if ($mform->is_cancelled()) {
            redirect($urlview);
        }
        if ($formdata = $mform->get_data()) {
            $attendancecontroller->update_multiple_attendance(explode('_', $formdata->ids), $formdata);
            redirect($urlview);
        }
        print_attendance_action($mform, $attendancetable, 'bulksetexempt', $attendsid, null);
        break;
    case 'bulksetsessionexempt':
        $urlview->params(['view' => $view]);

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
        $attendancetable = new attendance_table($hybridteaching, $sessionslist);
        $mform = ($action === 'bulksetsessionexempt') ? new bulk_set_session_exempt_form($url, $formparams)
            : false;
        if ($mform->is_cancelled()) {
            redirect($urlview);
        }
        if ($formdata = $mform->get_data()) {
            $attendancecontroller->update_multiple_sessions_exempt(explode('_', $formdata->ids), $formdata);
            redirect($urlview);
        }
        print_attendance_action($mform, $attendancetable, 'bulksetsessionexempt', null, $sessionsids);
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

/**
 * Display the print attendance action.
 *
 * @param object $mform mform object
 * @param mod_hybridteaching\local\attendance_table $attendancetable renderer
 * @param string $action bulksetattendance || bulksetexempt || bulksetsessionexempt
 * @param array $attendsid attends ids
 * @param array $sessionsids sessions ids
 */
function print_attendance_action($mform, $attendancetable, $action, $attendsid = null, $sessionsids = null) {
    global $OUTPUT;
    echo $OUTPUT->header();
    switch ($action) {
        case "bulksetattendance":
            $attendancetable->print_attendance_bulk_table($attendsid);
            break;
        case "bulksetexempt":
            $attendancetable->print_attendance_bulk_table($attendsid, 'bulksetexempt');
            break;
        case "bulksetsessionexempt":
            $attendancetable->print_attendance_bulk_table($sessionsids, 'sessionbulk');
            break;
    }
    $mform != null && ($action == "bulksetattendance" || $action == "bulksetexempt" || $action == "bulksetsessionexempt")
        ? $mform->display() : '';
    echo $OUTPUT->footer();
}
