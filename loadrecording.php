<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(__DIR__.'/classes/controller/sessions_controller.php');

$cid = required_param('cid', PARAM_INT);
$sid = required_param('sid', PARAM_INT);
$id = required_param('id', PARAM_INT);
require_login($cid);

$session = $DB->get_record('hybridteaching_session', ['id' => $sid], '*', MUST_EXIST );

$cm = get_coursemodule_from_instance ('hybridteaching', $session->hybridteachingid);
$context = context_module::instance($cm->id);
$urlrecording = '';
if (has_capability('mod/hybridteaching:viewrecordings', $context)) {
    if ($session->userecordvc == 1 && $session->processedrecording >= 0) {
        if ($session->storagereference > 0) {
            $classstorage = sessions_controller::get_subpluginstorage_class($session->storagereference);
            $config = helper::subplugin_config_exists($session->storagereference, 'store');
            if ($config && $classstorage) {
                sessions_controller::require_subplugin_store($classstorage['type']);
                $classname = $classstorage['classname'];
                $sessionrecording = new $classname();
                $urlrecording = $sessionrecording->get_recording ($session->processedrecording, $session->storagereference, $session->hybridteachingid, $sid);     
            }
        } else if ($session['storagereference'] == -1) {
        // For use case to BBB or a videconference type storage.
            $config = helper::subplugin_config_exists($session['vcreference'], 'vc');
            if ($config) {
                sessions_controller::require_subplugin_session($session['typevc']);
                $classname = sessions_controller::get_subplugin_class($session['typevc']);
                $sessionrecording = new $classname($session['id']);
                $urlrecording = $sessionrecording->get_recording($session['id']);
            }
        }

        if ($urlrecording != '') {
            redirect($urlrecording);
        }
        else {
            redirect (new moodle_url('/mod/hybridteaching/sessions.php', ['id' => $id, 'l' => 1]));
        }

    }
}




