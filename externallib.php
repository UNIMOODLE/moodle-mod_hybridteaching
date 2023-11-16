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
            ["attid" => new external_value(PARAM_INT, "attid"),
                    'status' => new external_value(PARAM_INT, "status"), ]
        );
    }

    public static function set_attendance_status($attid, $status) {
        global $DB, $CFG, $USER;
        $params = self::validate_parameters(
            self::set_attendance_status_parameters(),
                ["attid" => $attid, 'status' => $status]
        );

        $attendance = $DB->get_record('hybridteaching_attendance', ['id' => $attid]);
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        if ($attendance) {
            $attendance->status = $status;
            $attendance->usermodified = $USER->id;
            $attendance->timemodified = $timemodified;
            $DB->update_record('hybridteaching_attendance', $attendance);

            $course = $DB->get_field('hybridteaching', 'course', ['id' => $attendance->hybridteachingid]);
            $event = \mod_hybridteaching\event\attendance_updated::create([
                'objectid' => $attendance->hybridteachingid,
                'context' => \context_course::instance($course),
                'other' => [
                    'sessid' => $attendance->sessionid,
                    'userid' => $attendance->userid,
                    'attid' => $attendance->id,
                ],
            ]);

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
            ["sessid" => new external_value(PARAM_INT, "sessid"),
                    'attexempt' => new external_value(PARAM_INT, "attexempt"), ]
        );
    }


    public static function set_session_exempt($sessid, $attexempt) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::set_session_exempt_parameters(),
                ["sessid" => $sessid, 'attexempt' => $attexempt]
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
            ["sessionid" => new external_value(PARAM_INT, "sessionid"),
                    'userid' => new external_value(PARAM_INT, "userid"), ]
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


    public static function get_modal_text_parameters() {
        return new external_function_parameters(
            ["sessid" => new external_value(PARAM_INT, "sessid"),]
        );
    }


    public static function get_modal_text($sessid) {
        global $DB;
        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid]);
        $passstring = get_string('passstring', 'hybridteaching');
        $joinurlstring = get_string('joinurl', 'hybridteaching');
        $unknown = get_string('unknown', 'hybridteaching');
        $sesspass = $DB->get_field('hybridteaching', 'studentpassword', ['id' => $session->hybridteachingid]);
        $joinurl = null;

        if (!empty($session->typevc)) {
            if ($session->typevc == 'bbb') {
                $classname = $session->typevc;
                $subpluginsession = new $classname();
                //$joinurl = $subpluginsession->get_join_url($session->id, $session->config);
            } else {
                $joinurl = $DB->get_field('hybridteachvc_'.$session->typevc, 'joinurl', ['htsession' => $session->id]);
            }
        }

        $modalinfo = [
            'sessname' => $session->name, 
            'sesspass' => !empty($sesspass) ? $passstring . $sesspass : $passstring . $unknown,
            'sessurl' => !empty($joinurl) ? $joinurlstring . html_writer::link($joinurl, $joinurl) : $joinurlstring . $unknown,
        ];
        return json_encode($modalinfo);
    }

    public static function get_modal_text_returns() {
        // TODO.
    }
}
