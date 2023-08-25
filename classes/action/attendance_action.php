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
$attendanceid = optional_param('attid', 0, PARAM_INT);
$sessionid = optional_param('sessionid', 0, PARAM_INT);
$view = optional_param('view', 'sessionattendance', PARAM_TEXT);
$userid = optional_param('userid', null, PARAM_INT);
$editing = optional_param('editing', 1, PARAM_INT);

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

$return = new moodle_url('/mod/hybridteaching/attendance.php', $returnparams);
$hybridteaching = $DB->get_record('hybridteaching', array('id' => $hybridteachingid), '*', MUST_EXIST);
$attendancecontroller = new attendance_controller($hybridteaching, 'hybridteaching_attendance');
$attendancelist = $attendancecontroller->load_attendance();
$mform = null;

switch ($action) {
    case 'disable':
        $attendancecontroller->enable_data($attendanceid, false, 'hybridteaching_attendance');
        break;
    case 'enable':
        $attendancecontroller->enable_data($attendanceid, true, 'hybridteaching_attendance');
        break;
    case 'view':
        if ($editing == 0) {
            redirect ($return = new moodle_url('/mod/hybridteaching/attendance.php?view=' . 'extendedsessionatt' . '',
            array('id' => $moduleid, 'sessionid' => $sessionid, 'viewall' => 0, 'userid' => $userid)));
        }
        redirect($return = new moodle_url('/mod/hybridteaching/attendance.php?view=' . 'extendedsessionatt' . '',
        array('id' => $moduleid, 'sessionid' => $sessionid, 'viewall' => 1)));
        break;
        //activar desactivar multiple sessiones de attendance.
    default:
        redirect($return);
        break;
}


if (empty($mform)) {
    redirect($return);
}