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

require(__DIR__.'/../../config.php');
require_login();
require_once(__DIR__.'/lib.php');
use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\controller\attendance_controller;

global $USER;
$id = optional_param('id', 0, PARAM_INT);
$hybridteaching = $DB->get_record('hybridteaching', ['id' => $id], '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('hybridteaching', $hybridteaching->id, 0, false, MUST_EXIST);
$session = new sessions_controller($hybridteaching);
$attendancecontroller = new attendance_controller($hybridteaching);
$sessionshow = $session->get_next_session($cm);

$url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $cm->id]);
if (!$sessionshow) {
    $sessionshow = $session->get_last_session($cm);
}
// If not use of attendance return to main view.
if (!$hybridteaching->useattendance) {
    throw new moodle_exception('attendancedisabled', 'hybridteaching', $url);
}
$context = context_module::instance($cm->id);
if (!has_capability('mod/hybridteaching:attendanceregister', $context, $USER->id)) {
    throw new moodle_exception('noattendanceregister', 'hybridteaching', $url);
}
$timestart = $sessionshow->starttime;
$timeend = $timestart + intval($sessionshow->duration);
$validatetime = $hybridteaching->validateattendance;
$validateunit = $hybridteaching->attendanceunit;
$action = optional_param('attaction', null, PARAM_INT);
$qrpass = optional_param('qrpass', '', PARAM_TEXT);
$qrpassflag = false;

$atttype = 0;
if ($hybridteaching->rotateqr == 1 && $action == 1 && !$hybridteaching->studentpassword) {
    $cookiename = 'hybridteaching'.$hybridteaching->id;
    $secrethash = md5($USER->id.$hybridteaching->rotateqrsecret);
    $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $cm->id]);
    // Check password.
    $sql = 'SELECT * FROM {hybridteaching_session_pwd}'.
        ' WHERE attendanceid = ? AND expirytime > ? ORDER BY expirytime ASC';
    $qrpassdatabase = $DB->get_records_sql($sql, ['attendanceid' => $id, time() - 2], 0, 2);
    foreach ($qrpassdatabase as $qrpasselement) {
        if ($qrpass == $qrpasselement->password) {
            $qrpassflag = true;
        }
    }

    if ($qrpassflag) {
        // Create and store the token.
        setcookie($cookiename, $secrethash, time() + (60 * 5), "/");
        attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1, $atttype);
        if ($notify = $attendancecontroller::hybridteaching_set_attendance_log($hybridteaching, (int) $sessionshow->id, $action)) {
            redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
        }
    } else {
        // Flag error.
        if (debugging('', DEBUG_DEVELOPER)) {
            // Get qr code record.
            $rec = $DB->get_record('hybridteaching_session_pwd', ['attendanceid' => $id, 'password' => $qrpass]);
            if (empty($rec)) {
                    debugging('QR password not found');
                    var_dump($rec);
            } else {
                $format = get_string('strftimedatetimeaccurate', 'core_langconfig');
                $message = "The current server time is: " . userdate(time(), $format). " and this QR Code expired at: ".
                            userdate($rec->expirytime + 2, $format);
            }
        }
        redirect($url, $message, null, \core\output\notification::NOTIFY_ERROR);
    }

    // Password access is correct.
} else if (!empty($qrpass) && $hybridteaching->studentpassword == $qrpass) {
    if ($notify = $attendancecontroller::hybridteaching_set_attendance_log($hybridteaching, (int) $sessionshow->id, $action)) {
        if ($notify['ntype'] == 'success') {
            $attendancecontroller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1, $atttype);
        }
        redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
    }
    redirect($url, get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);

    // Use of qr and no password set.
} else if ($hybridteaching->useqr && strlen($hybridteaching->studentpassword == '')) {
    $qrsecret = optional_param('secretqr', null, PARAM_TEXT);
    // End attendance.
    if ($action == 0 ) {
        if ($notify = $attendancecontroller::hybridteaching_set_attendance_log($hybridteaching, (int) $sessionshow->id, $action)) {
            if ($notify['ntype'] == 'success') {
                $attendancecontroller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1, $atttype);
            }
            redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
        }
    }
    if ($qrsecret != '' && $qrsecret == $hybridteaching->rotateqrsecret) {
        if ($notify = $attendancecontroller::hybridteaching_set_attendance_log($hybridteaching, (int) $sessionshow->id, $action)) {
            if ($notify['ntype'] == 'success') {
                $attendancecontroller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1, $atttype);
            }
            redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
        }
    }
    redirect($url, get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
} else {
    if ($action != 0 && $qrpass != $hybridteaching->studentpassword) {
        redirect($url, get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
    }
    $qrsecret = optional_param('secretqr', null, PARAM_TEXT);
    // Finish attendance.
    if ($qrsecret != '' && $qrsecret == $hybridteaching->rotateqrsecret) {
        if ($notify = $attendancecontroller::hybridteaching_set_attendance_log($hybridteaching, (int) $sessionshow->id, $action)) {
            if ($notify['ntype'] == 'success') {
                $attendancecontroller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1);
            }
            redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
        }
    }
    if ($notify = $attendancecontroller::hybridteaching_set_attendance_log($hybridteaching, (int) $sessionshow->id, $action)) {
        if ($notify['ntype'] == 'success') {
            $attendancecontroller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1);
        }
        redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
    }
}
redirect($url);
