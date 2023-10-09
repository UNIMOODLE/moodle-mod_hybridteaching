<?php

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/attendance_controller.php');
$id = optional_param('id', 0, PARAM_INT);
$hybridteaching = $DB->get_record('hybridteaching', array('id' => $id), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('hybridteaching', $hybridteaching->id, 0, false, MUST_EXIST);
$session = new sessions_controller($hybridteaching);
$attendance_controller = new attendance_controller($hybridteaching);
$sessionshow = $session->get_next_session($hybridteaching->id);

$url = new moodle_url('/mod/hybridteaching/view.php', array('id' => $cm->id));
if (!$sessionshow) {
    $sessionshow = $session->get_last_session($hybridteaching->id);
}
//if not use of attendance return to main view.
if ($hybridteaching->useattendance == 0) {
    
    throw new moodle_exception('attendancedisabled', 'hybridteaching', $url);
}


$timestart = $sessionshow->starttime;
$timeend = $timestart + intval($sessionshow->duration);
$validatetime = $hybridteaching->validateattendance;
$validateunit = $hybridteaching->attendanceunit;
$timeparams = array('start' => $timestart, 'end' => $timeend, 'timeneeded' => $validatetime, 'validateunit' => $validateunit);
$action = optional_param('attaction', null, PARAM_INT);
$qrpass = optional_param('qrpass', '', PARAM_TEXT);
$qrpassflag = false;

$atttype = 0;
if ($hybridteaching->rotateqr == 1 && $action == 1) {
    $cookiename = 'hybridteaching'.$hybridteaching->id;
    $secrethash = md5($USER->id.$hybridteaching->rotateqrsecret);
    $url = new moodle_url('/mod/hybridteaching/view.php', array('id' => $cm->id));
    // Check password.
    $sql = 'SELECT * FROM {hybridteaching_session_pwd}'.
        ' WHERE attendanceid = ? AND expirytime > ? ORDER BY expirytime ASC';
    $qrpassdatabase = $DB->get_records_sql($sql, ['attendanceid' => $id, time() - 2], 0, 2);
    foreach ($qrpassdatabase as $qrpasselement) {
        if ($qrpass == $qrpasselement->password) {
            $qrpassflag = true;
        }
    }
    // Used for quick testing to be removed.
    echo '<br> pass:';
    var_dump($qrpass);
    if ($qrpassflag) {
        // Create and store the token.
        setcookie($cookiename, $secrethash, time() + (60 * 5), "/");
        attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1, $atttype);
        if ($notify = attendance_controller::hybridteaching_set_attendance_log($hybridteaching, $sessionshow, $timeparams, $action)) {
            redirect($url ,get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
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
        redirect($url ,$message, null, \core\output\notification::NOTIFY_ERROR);
    }

    //Password access is correct.
} else if (!empty($qrpass) && $hybridteaching->studentpassword == $qrpass) {
    if ($notify = attendance_controller::hybridteaching_set_attendance_log($hybridteaching, $sessionshow, $timeparams, $action)) {
        if ($notify['ntype'] == 'success') { 
            attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1, $atttype);
        }
        redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
    }
    redirect($url ,get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);

    //Use of qr and no password set.
} else if ($hybridteaching->useqr && strlen($hybridteaching->studentpassword == '')) {
    $qrsecret = optional_param('secretqr', null, PARAM_TEXT);
    //End attendance.
    if ($action == 0 ) {
        if ($notify = attendance_controller::hybridteaching_set_attendance_log($hybridteaching, $sessionshow, $timeparams, $action)) {
            if ($notify['ntype'] == 'success') {
                attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1, $atttype);
            }
            redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
        }
    }
    if ($qrsecret != '' && $qrsecret == $hybridteaching->rotateqrsecret) {
        if ($notify = attendance_controller::hybridteaching_set_attendance_log($hybridteaching, $sessionshow, $timeparams, $action)) {
           
            if ($notify['ntype'] == 'success') {
                attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1, $atttype);
            }
            redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
        }
    }
    redirect($url ,get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
} else {
    if ($action != 0 && $qrpass != $hybridteaching->studentpassword) {
        redirect($url, get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_INFO);
    }
    $qrsecret = optional_param('secretqr', null, PARAM_TEXT);
    //Finish attendance.
    if ($qrsecret != '' && $qrsecret == $hybridteaching->rotateqrsecret) {
        if ($notify = attendance_controller::hybridteaching_set_attendance_log($hybridteaching, $sessionshow, $timeparams, $action)) {
            if ($notify['ntype'] == 'success') {
                attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1);
            }
            redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
        }
    }
    if ($notify = attendance_controller::hybridteaching_set_attendance_log($hybridteaching, $sessionshow, $timeparams, $action)) {
        if ($notify['ntype'] == 'success') {
            attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, 1);
        }
        redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']);
    }
}
redirect($url);
