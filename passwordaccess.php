<?php
//aqui desde qr / contraseña el usuario, si coincide mandar a meeting si existe.
//+ cumple condiciones

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
if($hybridteaching->useattendance == 0) {
    
    throw new moodle_exception('attendancedisabled', 'hybridteaching', $url);
}
$qrpass = optional_param('qrpass', '', PARAM_TEXT);
$qrpassflag = false;
if ($att = attendance_controller::hybridteaching_get_attendance($hybridteaching, $sessionshow)) {
    if ($hybridteaching->starttime != 0 && $hybridteaching->starttime > $att->timemodified) {
    } else {
    }
}
if ($hybridteaching->rotateqr == 1) {
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
    if ($qrpassflag) {
        // Create and store the token.
        setcookie($cookiename, $secrethash, time() + (60 * 5), "/");
        $status = 1;
        attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, $status);
        redirect($url ,get_string('attendance_registered', 'hybridteaching'), null, \core\output\notification::NOTIFY_SUCCESS);
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
                debugging($message);
            }
        }
        redirect($url ,get_string('qr_expired', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
    }

} else if (!empty($qrpass) && $hybridteaching->studentpassword == $qrpass) {
    $status = 1;
    $qrsecret = optional_param('secretqr', null, PARAM_TEXT);
    if ($qrsecret) {
        if ($qrsecret == $hybridteaching->rotateqrsecret) {
            attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, $status);
            redirect($url ,get_string('attendance_registered', 'hybridteaching'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
        redirect($url ,get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
    }
    attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, $status);
    redirect($url ,get_string('attendance_registered', 'hybridteaching'), null, \core\output\notification::NOTIFY_SUCCESS);
} else if ($hybridteaching->useqr && strlen($hybridteaching->studentpassword == '')) {
    $status = 1;
    $qrsecret = optional_param('secretqr', null, PARAM_TEXT);
    if ($qrsecret != '' && $qrsecret == $hybridteaching->rotateqrsecret) {
        attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessionshow, $status);
        redirect($url ,get_string('attendance_registered', 'hybridteaching'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
    redirect($url ,get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
} else {
    redirect($url ,get_string('incorrect_password', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
}
redirect($url);
