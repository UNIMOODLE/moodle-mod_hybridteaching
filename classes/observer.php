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

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/helpers/grades.php');
require_once(dirname(__FILE__).'/controller/attendance_controller.php');
require_once(dirname(__FILE__).'/controller/sessions_controller.php');

class mod_hybridteaching_observer {
    public static function session_finished(\mod_hybridteaching\event\session_finished $event) {
        self::update_users_grade($event->objectid, $event->other['sessid']);
        self::set_session_state($event->other['sessid']);
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
        global $DB;
        $DB->delete_records('hybridteaching_attendance', ['sessionid' => $event->other['sessid']]);
        self::update_users_grade($event->objectid);
    }

    public static function attendance_updated(\mod_hybridteaching\event\attendance_updated $event) {
        self::update_users_grade($event->objectid, null, [$event->other['userid']]);
    }

    public static function course_module_updated(\core\event\course_module_updated $event) {
        global $DB;
        $htid = $DB->get_field('course_modules', 'instance', ['id' => $event->contextinstanceid]);
        self::update_users_grade($htid);
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
        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid]);
        $ht = $DB->get_record('hybridteaching', ['id' => $session->hybridteachingid]);
        $coursecontext = context_course::instance($ht->course);
        $usersid = array_keys(get_role_users(5, $coursecontext, false, '', null, false, $session->groupid));
        foreach ($usersid as $userid) {
            attendance_controller::hybridteaching_set_attendance($ht, $session, 0, null, $userid);
        }
    }

    private static function set_session_state($sessid) {
        sessions_controller::set_session_finished($sessid);
    }
}
