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

use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\controller\attendance_controller;
use mod_hybridteaching\helpers\grades;

/**
 * Class mod_hybridteaching_observer.
 */
class mod_hybridteaching_observer {
    /**
     * Observer for session_finished event
     * Updates the users grade and sets the session state
     *
     * @param \mod_hybridteaching\event\session_finished $event The event to be processed
     */
    public static function session_finished(\mod_hybridteaching\event\session_finished $event) {
        global $DB;
        $userids = $DB->get_records('hybridteaching_attendance', ['sessionid' => $event->other['sessid']], '', 'userid');
        self::update_users_grade($event->objectid, $userids);
        self::update_session_users_grade($event->objectid, $event->other['sessid'], $userids);
        self::set_session_state($event->other['sessid']);
        grades::finish_connectime_logs($event->objectid, $event->other['sessid'], $userids);
    }

    /**
     * Observer for session_joined event.
     *
     * @param \mod_hybridteaching\event\session_joined $event The event to be processed.
     */
    public static function session_joined(\mod_hybridteaching\event\session_joined $event) {
    }

    /**
     * Observer for session_added event
     * Updates the users grade and create empty attendance
     *
     * @param \mod_hybridteaching\event\session_added $event The event to be processed
     */
    public static function session_added(\mod_hybridteaching\event\session_added $event) {
        if (isset($event->other['multiplesess']) && empty($event->other['multiplesess'])) {
            self::create_empty_attendance($event->objectid, $event->other['sessid']);
        }
        self::update_users_grade($event->objectid);
    }

    /**
     * Observer for session_updated event
     * Updates the users grade
     *
     * @param \mod_hybridteaching\event\session_updated $event The event to be processed
     */
    public static function session_updated(\mod_hybridteaching\event\session_updated $event) {
        isset($event->other['userid']) ? $userids  = $event->other['userid'] : $userids = null;
        self::update_users_grade($event->objectid, $userids);
        self::update_session_users_grade($event->objectid, $event->other['sessid'], $userids);
    }

    /**
     * Observer for session_deleted event
     * Updates the users grade
     *
     * @param \mod_hybridteaching\event\session_deleted $event The event to be processed
     */
    public static function session_deleted(\mod_hybridteaching\event\session_deleted $event) {
        global $DB;
        $DB->delete_records('hybridteaching_attendance', ['sessionid' => $event->other['sessid']]);
        self::update_users_grade($event->objectid);
    }

    /**
     * Observer for attendance_updated event
     * Updates the users grade
     *
     * @param \mod_hybridteaching\event\attendance_updated $event The event to be processed
     */
    public static function attendance_updated(\mod_hybridteaching\event\attendance_updated $event) {
        self::update_users_grade($event->objectid, [$event->relateduserid]);
        self::update_session_users_grade($event->objectid, $event->other['sessid'], [$event->relateduserid]);
    }

    /**
     * Observer for course_module_updated event
     * Updates the users grade
     *
     * @param \core\event\course_module_updated $event The event to be processed
     */
    public static function course_module_updated(\core\event\course_module_updated $event) {
        global $DB;
        $htid = $DB->get_field('course_modules', 'instance', ['id' => $event->contextinstanceid]);
        self::update_users_grade($htid);
    }

    /**
     * Update the grade of specified user(s) for a given object.
     *
     * @param int $objectid The hybridteaching ID for which the grade is being updated
     * @param array|null $userids (Optional) The ID(s) of the user(s) for whom the grade is being updated
     */
    private static function update_users_grade($objectid, $userids = null) {
        grades::hybridteaching_update_users_grade($objectid, $userids);
    }

    /**
     * Update the session grade of specified user(s) for a given object.
     *
     * @param int $objectid The hybridteaching ID for which the grade is being updated
     * @param int $sessid The session ID
     * @param array|null $userids (Optional) The ID(s) of the user(s) for whom the grade is being updated
     */
    private static function update_session_users_grade($objectid, $sessid, $userids = null) {
        grades::ht_update_users_att_grade($objectid, $sessid, $userids);
    }

    /**
     * Create empty attendance for a given object and session.
     *
     * @param int $objectid HybridTeaching ID
     * @param int $sessid Session ID
     */
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

    /**
     * Set the session state for the given session ID.
     *
     * @param int $sessid Session ID
     */
    private static function set_session_state($sessid) {
        sessions_controller::set_session_finished($sessid);
    }
}
