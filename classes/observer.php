<?php

require_once(dirname(__FILE__).'/helpers/grades.php');
require_once(dirname(__FILE__).'/controller/attendance_controller.php');

class mod_hybridteaching_observer {
    public static function session_finished(\mod_hybridteaching\event\session_finished $event) {
        // Revisar si hay students que no tienen attendance en la session (en ese caso crearla)
        // Actualizar la asistencia de los usuarios
        self::update_users_grade($event->objectid, $event->other['sessid']);
    }
    
    public static function session_joined(\mod_hybridteaching\event\session_joined $event) {

    }

    public static function session_added(\mod_hybridteaching\event\session_added $event) {
        if (isset($event->other['multiplesess']) && empty($event->other['multiplesess'])) {
            self::create_empty_attendance($event->objectid, $event->other['sessid']);
        }
        self::update_users_grade($event->objectid);
    }
    
    public static function session_updated(\mod_hybridteaching\event\session_updated $event) {
    
    }
    
    public static function session_deleted(\mod_hybridteaching\event\session_deleted $event) {
        // Delete attendance provisional
        global $DB;
        $DB->delete_records('hybridteaching_attendance', ['sessionid' => $event->other['sessid']]);
        self::update_users_grade($event->objectid);
    }
    
    public static function attendance_updated(\mod_hybridteaching\event\attendance_updated $event) {
        self::update_users_grade($event->objectid, null, array($event->other['userid']));
    }
    
    private static function update_users_grade($objectid, $sessid = null, $userid = null) {
        $grades = new grades();
        $grades->hybridteaching_update_users_grade($objectid, $userid);
        if (!empty($sessid)) {
            $grades->ht_update_users_att_grade($objectid, $sessid, $userid);
        }
    }

    private static function create_empty_attendance($objectid, $sessid) {
        global $DB;
        $session = $DB->get_record('hybridteaching_session', array('id' => $sessid));
        $ht = $DB->get_record('hybridteaching', array('id' => $session->hybridteachingid));
        $coursecontext = context_course::instance($ht->course);
        $usersid = array_keys(get_role_users(5, $coursecontext, false, '', null, false, $session->groupid));
        foreach ($usersid as $userid) {
            attendance_controller::hybridteaching_set_attendance($ht, $session, 0, null, $userid);
        }
        
    }
}
