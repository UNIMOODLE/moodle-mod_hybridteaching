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


defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once("$CFG->libdir/externallib.php");

use mod_hybridteaching\controller\sessions_controller;

use mod_hybridteaching\helper;


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
                    'status' => new external_value(PARAM_INT, "status"),
                    'id' => new external_value(PARAM_TEXT, "id"), ]
        );
    }

    /**
     * Sets the attendance status for a given attendance ID.
     *
     * @param int $attid The ID of the attendance.
     * @param string $status The status to set for the attendance.
     * @param string $id The hybridteaching course module id.
     * @return string The attendance record in JSON format.
     */
    public static function set_attendance_status($attid, $status, $id) {
        global $DB, $CFG, $USER;
        $params = self::validate_parameters(
            self::set_attendance_status_parameters(),
                ["attid" => $attid, 'status' => $status, 'id' => $id]
        );

        $attendance = $DB->get_record('hybridteaching_attendance', ['id' => $attid], 'id, hybridteachingid, sessionid, userid');

        // Check permissions.
        $coursemodule = get_coursemodule_from_instance('hybridteaching', $attendance->hybridteachingid);
        $context = context_module::instance($coursemodule->id);
        require_capability('mod/hybridteaching:manageactivity', $context);

        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        if ($attendance) {
            $attendance->status = $status;
            $attendance->usermodified = $USER->id;
            $attendance->timemodified = $timemodified;
            $session = $DB->get_record('hybridteaching_session', ['id' => $attendance->sessionid]);
            $status ? $attendance->connectiontime = $session->duration : $attendance->connectiontime = 0;
            $DB->update_record('hybridteaching_attendance', $attendance);

            list($course, $cm) = get_course_and_cm_from_instance($attendance->hybridteachingid, 'hybridteaching');
            $event = \mod_hybridteaching\event\attendance_updated::create([
                'objectid' => $attendance->hybridteachingid,
                'context' => \context_module::instance($cm->id),
                'relateduserid' => $attendance->userid,
                'other' => [
                    'sessid' => $attendance->sessionid,
                    'attid' => $attendance->id,
                ],
            ]);

            $event->trigger();

            // Update completion state.
            $hybridteaching = $DB->get_record('hybridteaching', ['id' => $attendance->hybridteachingid]);
            !$cm ? $cm = get_coursemodule_from_id('hybridteaching', $id,  0,  false,  MUST_EXIST) : '';

            $completion = new completion_info($course);
            if ($completion->is_enabled($cm) && $hybridteaching->completionattendance) {
                $status ? $completion->update_state($cm, COMPLETION_COMPLETE, $attendance->userid) :
                    $completion->update_state($cm, COMPLETION_INCOMPLETE, $attendance->userid);
            }
        }
        return $attendance;
    }

    /**
     * info about the returned object
     */
    public static function set_attendance_status_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'Attendance id'),
                'hybridteachingid' => new external_value(PARAM_INT, 'Hybridteaching id'),
                'sessionid' => new external_value(PARAM_INT, 'Session id'),
                'userid' => new external_value(PARAM_INT, 'User id'),
                'status' => new external_value(PARAM_INT, 'Attendance status'),
                'usermodified' => new external_value(PARAM_INT, 'User that modified the attendance'),
                'timemodified' => new external_value(PARAM_INT, 'When the attendance was modified'),
                'connectiontime' => new external_value(PARAM_INT, 'The user connectiontime'),
            ]
        );
    }

    /**
     * Set session exempt parameters.
     *
     * @return external_function_parameters
     */
    public static function set_session_exempt_parameters() {
        return new external_function_parameters(
            ["sessid" => new external_value(PARAM_INT, "sessid"),
                    'attexempt' => new external_value(PARAM_INT, "attexempt"), ]
        );
    }


    /**
     * Sets the exemption status for a session.
     *
     * @param mixed $sessid The ID of the session.
     * @param mixed $attexempt The exemption status to set.
     * @return string The JSON-encoded session object.
     */
    public static function set_session_exempt($sessid, $attexempt) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::set_session_exempt_parameters(),
                ["sessid" => $sessid, 'attexempt' => $attexempt]
        );

        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid], 'id, hybridteachingid');

        // Check permissions.
        $coursemodule = get_coursemodule_from_instance('hybridteaching', $session->hybridteachingid);
        $context = context_module::instance($coursemodule->id);
        require_capability('mod/hybridteaching:sessionsfulltable', $context);

        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        if ($session) {
            $session->attexempt = $attexempt;
            $session->modifiedby = $USER->id;
            $session->timemodified = $timemodified;
            $DB->update_record('hybridteaching_session', $session);

            list($course, $cm) = get_course_and_cm_from_instance($session->hybridteachingid, 'hybridteaching');
            $attends = $DB->get_records('hybridteaching_attendance', ['sessionid' => $sessid], '', 'id, userid');
            foreach ($attends as $att) {
                $event = \mod_hybridteaching\event\session_updated::create([
                    'objectid' => $session->hybridteachingid,
                    'context' => \context_module::instance($cm->id),
                    'other' => [
                        'sessid' => $sessid,
                        'userid' => $att->userid,
                        'attid' => $att->id,
                    ],
                ]);

                $event->trigger();
            }

        }
        return $session;
    }

    /**
     * info about the returned object
     */
    public static function set_session_exempt_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'Session id'),
                'hybridteachingid' => new external_value(PARAM_INT, 'Hybridteaching id'),
                'attexempt' => new external_value(PARAM_INT, 'Attendance exempt for session'),
                'modifiedby' => new external_value(PARAM_INT, 'User that modified the attendance'),
                'timemodified' => new external_value(PARAM_INT, 'When the attendance was modified'),
            ]
        );
    }

    /**
     * Retrieve the parameters for the display actions.
     *
     * @return external_function_parameters The parameters for the display actions.
     */
    public static function get_display_actions_parameters() {
        return new external_function_parameters(
            ["sessionid" => new external_value(PARAM_INT, "sessionid"),
                    'userid' => new external_value(PARAM_INT, "userid"), ]
        );
    }


    /**
     * Retrieves the display actions for a given session and user.
     *
     * @param string $sessionid The ID of the session.
     * @param int $userid The ID of the user.
     * @return string The JSON-encoded display actions.
     */
    public static function get_display_actions($sessionid, $userid) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::get_display_actions_parameters(), ["sessionid" => $sessionid, 'userid' => $userid]
        );
        $actions = [];
        is_siteadmin($USER) ? $actions['admin'] = true : $actions['admin'] = false;

        $att = $DB->get_record('hybridteaching_attendance', ['sessionid' => $sessionid, 'userid' => $userid], '*', IGNORE_MISSING);
        if (!$att) {
            return json_encode($actions);
        }
        $activesession = $DB->get_record('hybridteaching_session', ['id' => $sessionid], '*', IGNORE_MISSING);
        if ($activesession) {
            $sessioncontroller = new sessions_controller();
            if ($sessioncontroller->session_started($activesession)) {
                $actions['buttons'] = 'enter';
            }
        }
        return json_encode($actions);
    }

    /**
     * Returns the display actions.
     *
     */
    public static function get_display_actions_returns() {
        return new external_value(PARAM_RAW, 'json describing the available actions for the user');
    }


    /**
     * Get the parameters for the `get_modal_text` function.
     *
     * @return external_function_parameters The parameters for the function.
     */
    public static function get_modal_text_parameters() {
        return new external_function_parameters(
            ["sessid" => new external_value(PARAM_INT, "sessid")]
        );
    }


    /**
     * Retrieves the modal text for a given session ID.
     *
     * @param int $sessid The ID of the session.
     * @return string The JSON-encoded modal information.
     */
    public static function get_modal_text($sessid) {
        global $DB, $USER;
        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid]);
        $passstring = get_string('passstring', 'hybridteaching');
        $joinurlstring = get_string('joinurl', 'hybridteaching');
        $unknown = get_string('unknown', 'hybridteaching');
        $sesspass = $DB->get_field('hybridteaching', 'studentpassword', ['id' => $session->hybridteachingid]);
        $joinurl = null;

        // Check permissions.
        $coursemodule = get_coursemodule_from_instance('hybridteaching', $session->hybridteachingid);
        $context = context_module::instance($coursemodule->id);
        require_capability('mod/hybridteaching:sessionsfulltable', $context);

        if (!empty($session->typevc)) {
            if (helper::subplugin_config_exists($session->vcreference, 'vc')) {
                if ($session->typevc == 'bbb') {
                    $sessioncontroller = new sessions_controller();
                    $classname = $sessioncontroller->get_subpluginvc_class($session->typevc);
                    $subpluginsession = new $classname($session->id);
                    $joinurl = $subpluginsession->get_join_url($session);
                } else {
                    $joinurl = $DB->get_field('hybridteachvc_'.$session->typevc, 'joinurl', ['htsession' => $session->id]);
                }
                $joinurl = html_writer::link($joinurl, $joinurl);
            } else {
                $joinurl = get_string('lostconfig', 'hybridteaching');
            }
        }

        $modalinfo = [
            'sessname' => $session->name,
            'sesspass' => !empty($sesspass) ? $passstring . $sesspass : $passstring . $unknown,
            'sessurl' => !empty($joinurl) ? $joinurlstring . $joinurl : $joinurlstring . $unknown,
        ];

        list($course, $cm) = get_course_and_cm_from_instance($session->hybridteachingid, 'hybridteaching');
        $event = \mod_hybridteaching\event\session_info_viewed::create([
            'objectid' => $session->hybridteachingid,
            'context' => \context_module::instance($cm->id),
            'other' => [
                'sessid' => !empty($session) ? $session->id : null,
            ],
        ]);

        $event->trigger();

        return json_encode($modalinfo);
    }

    /**
     * Info about the returned object
     */
    public static function get_modal_text_returns() {
        return new external_value(PARAM_RAW, 'json with the modal info diplayed for the user');
    }

    /**
     * Get the parameters for the `get_user_has_recording_capability` function.
     *
     * @return external_function_parameters The parameters for the function.
     */
    public static function get_user_has_recording_capability_parameters() {
        return new external_function_parameters(
            ["typevc" => new external_value(PARAM_TEXT, "typevc"),
                "pagecontext" => new external_value(PARAM_INT, "pagecontext"), ]
        );
    }



    /**
     * Get user recording capability.
     *
     * @param string $typevc description
     * @param object $pagecontext description
     * @return string The JSON-encoded capability information.
     */
    public static function get_user_has_recording_capability($typevc, $pagecontext) {
        global $DB, $USER;

        $params = self::validate_parameters(
            self::get_user_has_recording_capability_parameters(), ["typevc" => $typevc, 'pagecontext' => $pagecontext]
        );
        if (empty($typevc)) {
            return json_encode('notypevc');
        }
        $context = context::instance_by_id($pagecontext, MUST_EXIST);
        $capability = 'hybridteachvc/' . $typevc . ':record';

        return json_encode(has_capability($capability, $context, $USER, true));
    }

    /**
     * Info about the returned object
     */
    public static function get_user_has_recording_capability_returns() {
        return new external_value(PARAM_RAW, 'true, or a string indicating the user recording capabilities.');
    }


    /**
     * Get the parameters for the `disable_attendance_inprogress` function.
     *
     * @return external_function_parameters The parameters for the function.
     */
    public static function disable_attendance_inprogress_parameters() {
        return new external_function_parameters(
            ["hybridteachingid" => new external_value(PARAM_INT, "hybridteachingid"),
                "sessionid" => new external_value(PARAM_INT, "sessionid"), ]
        );
    }

    /**
     * Info about the returned object
     */
    public static function disable_attendance_inprogress_returns() {
        return new external_value(PARAM_RAW, 'returns mixed array with attendance and sessions data');
    }

    /**
     * Get sessions in progress by cmid.
     *
     * @param int $cmid coursemodule id
     * @param int $sessionid session id
     * @return string json-encoded information of session attendance in progress
     */
    public static function disable_attendance_inprogress($id, $sessionid = -1) {
        global $DB, $USER;
        list ($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');

        // Check permissions.
        $context = context_module::instance($id);
        require_capability('mod/hybridteaching:sessionsfulltable', $context);

        $params = self::validate_parameters(
            self::disable_attendance_inprogress_parameters(), ["hybridteachingid" => $cm->instance,
                "sessionid" => $sessionid, ]
        );
        $attstudentsrecords = "";
        if ($sessionid != -1) {
            $sqlattstudents = 'SELECT hta.id
                                 FROM {hybridteaching_attendance} hta
                                 JOIN {hybridteaching_session} hts
                                   ON hts.id = hta.sessionid
                                  AND hts.starttime < :timenow
                                  AND hts.starttime + hts.duration > :timenow2';
            $attstudentsparam = ['timenow' => time(), 'timenow2' => time()];
            $attstudentsrecords = $DB->get_records_sql($sqlattstudents, $attstudentsparam);
        }
        $attsessionrecords = sessions_controller::get_sessions_in_progress($cm->instance);
        $datarecords = [$attsessionrecords, $attstudentsrecords];
        return json_encode($datarecords);
    }

}
