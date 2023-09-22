<?php 

//aqui lanzar evento de que ha clicado en el botón de entrar a reunión
//require_login

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(__DIR__.'/classes/controller/sessions_controller.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/attendance_controller.php');

$id = required_param('id', PARAM_INT);
$url = optional_param('url', '', PARAM_RAW);
$sid = optional_param('s', 0, PARAM_INT);
$finishsession = optional_param('finishsession', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
$modulecontext = context_module::instance($cm->id);
$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);
$attendance_controller = new attendance_controller($hybridteaching);
if (!empty($sid)) {
    $activesession = $DB->get_record('hybridteaching_session', array('id' => $sid), '*', MUST_EXIST);
    if (!$finishsession) {
        if ($activesession->typevc != $hybridteaching->typevc) {
            $activesession->typevc = $hybridteaching->typevc;
            $DB->update_record('hybridteaching_session', $activesession);
        }

        if ($activesession->starttime == 0) {
            $activesession->starttime = time();
            $DB->update_record('hybridteaching_session', $activesession);
        }

        if ($hybridteaching->config) {         
            sessions_controller::require_subplugin_session($hybridteaching->typevc);
            $classname = sessions_controller::get_subplugin_class($hybridteaching->typevc);
            $sessionvc = new $classname($activesession->id);
        
            if (!empty($sessionvc->get_session())) {
                $resultsaccess = $sessionvc->get_zone_access();
                $url = $resultsaccess['url'];
                $ishost = $resultsaccess['ishost'];
            } else {                 
                //FALTA COMPROBAR SI PUEDE ENTRAR ANTES QUE EL ANFITRION
                if ($activesession && has_capability('mod/hybridteaching:sessionsactions', $modulecontext)) {
                    //previously to create vc session:
                    //if must save recordings, save the id of storage
                    if ($activesession->userecordvc){
                        $activesession->storagereference = sessions_controller::savestorage($activesession);
                    }
                    $sessionvc->create_unique_session_extended($activesession, $hybridteaching);
                }
            }
        }
    } else {
        if (!get_config('hybridteaching', 'reusesession')) {
            $activesession->isfinished = 1;
            $activesession->duration = time() - $activesession->starttime;
            $DB->update_record('hybridteaching_session', $activesession);
            $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);
            $url = base64_encode($url);
        } 
    }
} else {
    if ($hybridteaching->undatedsession) {
        $sessioncontroller = new sessions_controller($hybridteaching);
        $session = new stdClass();
        $session->name = get_string('recurringses', 'hybridteaching') . ' ' . $hybridteaching->name;
        $session->groupid = 0;
        $session->starttime = time();
        $session->duration = 0;
        $session->timetype = 0;
        $session->typevc = $hybridteaching->typevc;
        $session = (object) $sessioncontroller->create_session($session);

        if (!empty($session->typevc)) {
            $classname = sessions_controller::get_subplugin_class($hybridteaching->typevc);
            $sessionvc = new $classname($session->id);
        
            if (!empty($sessionvc->get_session())) {
                $resultsaccess = $sessionvc->get_zone_access();
                $url = $resultsaccess['url'];
                $ishost = $resultsaccess['ishost'];
            } else {                 
                //FALTA COMPROBAR SI PUEDE ENTRAR ANTES QUE EL ANFITRION
                if (has_capability('mod/hybridteaching:sessionsactions', $modulecontext)) {
                    $sessionvc->create_unique_session_extended($session, $hybridteaching);
                    $sessionvc->set_session($session->id);
                    $resultsaccess = $sessionvc->get_zone_access();
                    $url = $resultsaccess['url'];
                    $ishost = $resultsaccess['ishost'];
                }
            }
        }
    }
}

$nexturl = base64_decode($url);

require_login($course->id, true);
if (!$finishsession && !has_capability('mod/hybridteaching:sessionsfulltable', $PAGE->context)) {
    isset($activesession) ? $sessiontype = $activesession : $sessiontype = $session;
    $attaction = optional_param('attaction', 1, PARAM_INT);
        $timeparams = ['start' => $sessiontype->starttime, 'end' => $sessiontype->starttime + $sessiontype->duration,
        'timeneeded' => $hybridteaching->attendanceunit, 'validateunit' => $hybridteaching->attendanceunit];
        if ($hybridteaching->useattendance) {
        $notify = attendance_controller::hybridteaching_set_attendance_log($hybridteaching, $sessiontype, $timeparams, 1);
        if ($notify['ntype'] == 'success') {
            attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessiontype, 1, $attaction);
        }
        $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);
        $notify['ntype'] == 'error' ? redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']) :
            redirect($nexturl);
        }
}
redirect($nexturl);