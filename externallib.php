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

/**
 * hybridteaching external API
 *
 * @package    mod_hybridteaching
 * @copyright  2023 isyc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once("$CFG->libdir/externallib.php");

/**
 * hybridteaching external functions
 *
 * @package    mod_hybridteaching
 * @category   external
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 4.1
 */
class hybridteaching_external extends external_api {

    /**
     * Checks that the cmid passed is of type int.
     *
     * @return validate function.
     */
    public static function set_attendance_status_parameters() {
        return new external_function_parameters(
            array("attid" => new external_value(PARAM_INT, "attid"),
                    'status' => new external_value(PARAM_INT, "status"))
        );
    }

    public static function set_attendance_status($attid, $status) {
        global $DB, $CFG, $USER;
        $params = self::validate_parameters(
            self::set_attendance_status_parameters(),
                array("attid" => $attid, 'status' => $status)
        );

        $attendance = $DB->get_record('hybridteaching_attendance', ['id' => $attid]);
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        if ($attendance) {
            $attendance->status = $status;
            $attendance->usermodified = $USER->id;
            $attendance->timemodified = $timemodified;  
            $DB->update_record('hybridteaching_attendance', $attendance);

            $course = $DB->get_field('hybridteaching', 'course', ['id' => $attendance->hybridteachingid]);
            $event = \mod_hybridteaching\event\attendance_updated::create(array(
                'objectid' => $attendance->hybridteachingid,
                'context' => \context_course::instance($course),
                'other' => array(
                    'sessid' => $attendance->sessionid,
                    'userid' => $attendance->userid,
                    'attid' => $attendance->id
                )
            ));
    
            $event->trigger();
        }
        return json_encode($attendance);
    }

    /**
     * info about the returned object
     */
    public static function set_attendance_status_returns() {
        // TODO.
    }

    public static function set_session_exempt_parameters() {
        return new external_function_parameters(
            array("sessid" => new external_value(PARAM_INT, "sessid"),
                    'attexempt' => new external_value(PARAM_INT, "attexempt"))
        );
    }


    public static function set_session_exempt($sessid, $attexempt) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::set_session_exempt_parameters(),
                array("sessid" => $sessid, 'attexempt' => $attexempt)
        );

        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid]);
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        if ($session) {
            $session->attexempt = $attexempt;
            $session->modifiedby = $USER->id;
            $session->timemodified = $timemodified;  
            $DB->update_record('hybridteaching_session', $session);
        }
        return json_encode($session);
    }

    public static function set_session_exempt_returns() {
        // TODO.
    }

    public static function get_display_actions_parameters() {
        return new external_function_parameters(
            array("sessionid" => new external_value(PARAM_INT, "sessionid"),
                    'userid' => new external_value(PARAM_INT, "userid"))
        );
    }


    public static function get_display_actions($sessionid, $userid) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::get_display_actions_parameters(), ["sessionid" => $sessionid, 'userid' => $userid]
        );
        $actions = [];
        $att = $DB->get_record('hybridteaching_attendance', ['sessionid' => $sessionid, 'userid' => $userid], '*', IGNORE_MISSING);
        $att ? $log = $DB->get_records('hybridteaching_attend_log', ['attendanceid' => $att->id],
            'timecreated DESC', 'action', 0, 1, IGNORE_MISSING) : $log = false;
        if ($att && $log && !empty($log[1])) {
            $actions['buttons'] = 'exit';
            return json_encode($actions);
        }
        $actions['buttons'] = 'enter';
        is_siteadmin($USER) ? $actions['admin'] = true : $actions['admin'] = false;
        return json_encode($actions);
    }

    public static function get_display_actions_returns() {
        // TODO.
    }
}