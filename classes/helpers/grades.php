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

namespace mod_hybridteaching\helpers;

use mod_hybridteaching\controller\attendance_controller;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'../../../../../config.php');
require_login();
require_once($CFG->libdir . '/gradelib.php');

/**
 * Class grades.
 */
class grades {
    /** @var int Option one to calculate the activity grade. Use the number of attended sessions. */
    const ATTENDED_SESSIONS = 1;

    /** @var int Option two to calculate the activity grade. use the percentage of attended sessions */
    const PERCENTAGE_ATTENDED_SESSIONS = 2;

    /** @var int Option three to calculate the activity grade. Use the percentage of total time attended sessions. */
    const PERCENTAGE_TOTAL_TIME_ATTENDED_SESSIONS = 3;

    /**
     * Updates the grades of users in the hybrid teaching module.
     *
     * @param int $htid The ID of the hybrid teaching module.
     * @param array $userids An array of user IDs to update the grades for. If empty, all enrolled users will be updated.
     * @return bool|int Returns false if the grade or max grade attendance is empty,
     *                  otherwise it returns true or false based on the success of grade update.
     */
    public static function hybridteaching_update_users_grade($htid, $userids = []) {
        global $DB;

        $ht = $DB->get_record('hybridteaching', ['id' => $htid]);
        $cmid = get_coursemodule_from_instance('hybridteaching', $htid);
        if (empty($ht->grade) || empty($ht->maxgradeattendance) || !$cmid) {
            return false;
        }

        list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'hybridteaching');

        !is_array($userids) && !is_null($userids) ? $userids = [0 => $userids] : '';
        if (empty($userids)) {
            $context = \context_module::instance($cm->id);
            $userids = array_keys(get_enrolled_users($context, '', 0, 'u.id'));
        }

        $grades = [];
        $completion = new \completion_info($course);
        foreach ($userids as $userid) {
            is_object($userid) ? $userid = $userid->userid : '';
            $grades[$userid] = new \stdClass();
            $grades[$userid]->userid = $userid;

            if (self::has_taken_attendance($ht->id, $userid)) {
                $grades[$userid]->rawgrade = self::get_instance_grade_for($ht, $userid);
                if (isset($grades[$userid]->rawrgrade)) {
                    $grades[$userid]->rawrgrade < 0 ? $grades[$userid]->rawrgrade = 0 : '';
                }
                if ($completion->is_enabled($cm) && $ht->completionattendance) {
                    $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
                }
            } else {
                $grades[$userid]->rawgrade = null;
            }
        }

        return grade_update('mod/hybridteaching', $course->id, 'mod', 'hybridteaching', $ht->id, 0, $grades);
    }

    /**
     * Checks if attendance has been taken for a user in a hybrid teaching session.
     *
     * @param int $htid The hybrid teaching ID.
     * @param int $userid The user ID.
     * @param int|null $sessid The session ID (optional).
     * @return bool Returns true if attendance has been taken, false otherwise.
     */
    public static function has_taken_attendance($htid, $userid, $sessid = null) {
        global $DB;
        $params = ['userid' => $userid, 'hybridteachingid' => $htid];
        !empty($sessid) ? $params['sessionid'] = $sessid : null;
        return $DB->record_exists('hybridteaching_attendance', $params);
    }

    /**
     * Retrieves the instance grade for a given $ht and $userid.
     *
     * @param mixed $ht The instance of the class.
     * @param mixed $userid The user ID.
     * @return mixed The instance grade.
     */
    public static function get_instance_grade_for($ht, $userid) {
        switch ($ht->maxgradeattendancemode) {
            case self::ATTENDED_SESSIONS:
                return self::calc_grade_by_attended_sess($ht, $userid);
                break;
            case self::PERCENTAGE_ATTENDED_SESSIONS:
                return self::calc_grade_by_attended_sess_per($ht, $userid);
                break;
            case self::PERCENTAGE_TOTAL_TIME_ATTENDED_SESSIONS:
                return self::calc_grade_by_attended_sess_time_per($ht, $userid);
                break;
        }
    }

    /**
     * Calculates the grade for a user based on their attendance in a session.
     *
     * @param object $ht The session object containing the maximum grade for attendance.
     * @param int $userid The ID of the user.
     * @return float The calculated grade for the user.
     */
    public static function calc_grade_by_attended_sess($ht, $userid) {
        $usergrade = 0;
        $attendancetomaxgrade = $ht->maxgradeattendance;
        $userattendance = self::count_user_att($ht, $userid);

        if ($userattendance->totalattended >= $attendancetomaxgrade) {
            $usergrade = $ht->grade;
        } else {
            $usergrade = ($userattendance->totalattended / $attendancetomaxgrade) * $ht->grade;
        }

        return $usergrade;
    }

    /**
     * Calculates the grade for a user based on the attended sessions percentage.
     *
     * @param object $ht The hybrid teaching object.
     * @param int $userid The user ID.
     * @return float The calculated user grade.
     */
    public static function calc_grade_by_attended_sess_per($ht, $userid) {
        global $DB;

        $usergrade = 0;
        $maxgradeper = $ht->maxgradeattendance / 100;
        $attendancetomaxgrade = round($DB->count_records('hybridteaching_session',
            ['hybridteachingid' => $ht->id, 'attexempt' => HYBRIDTEACHING_NOT_EXEMPT,]) * $maxgradeper);
        $userattendance = self::count_user_att($ht, $userid);

        if ($userattendance->totalattended >= $attendancetomaxgrade) {
            $usergrade = $ht->grade;
        } else {
            $usergrade = ($userattendance->totalattended / $attendancetomaxgrade) * $ht->grade;
        }

        return $usergrade;
    }

    /**
     * Calculate the grade for a user based on their attended session time percentage.
     *
     * @param object $ht The hybridteaching object.
     * @param int $userid The ID of the user.
     * @throws Exception If there is an error in the database query.
     * @return int The calculated grade for the user.
     */
    public static function calc_grade_by_attended_sess_time_per($ht, $userid) {
        global $DB;

        $usergrade = 0;
        $maxgradeper = $ht->maxgradeattendance / 100;

        $sql = "SELECT SUM(duration) as totalduration
                  FROM {hybridteaching_session} hs
                 WHERE hs.hybridteachingid = :hybridteachingid
                   AND hs.attexempt = :attexempt";

        $params = [
            'hybridteachingid' => $ht->id,
            'attexempt' => HYBRIDTEACHING_NOT_EXEMPT,
        ];

        $attendancetomaxgrade = round($DB->get_record_sql($sql, $params)->totalduration * $maxgradeper);

        $sql = "SELECT SUM(connectiontime) as totalconnectime
                  FROM {hybridteaching_attendance} ha
            INNER JOIN {hybridteaching_session} hs ON ha.sessionid = hs.id
                 WHERE ha.userid = :userid
                   AND ha.hybridteachingid = :hybridteachingid
                   AND (ha.status = :status OR ha.status = :status2)
                   AND hs.attexempt = :attexempt";

        $params = [
            'userid' => $userid,
            'hybridteachingid' => $ht->id,
            'status' => HYBRIDTEACHING_ATTSTATUS_VALID,
            'status2' => HYBRIDTEACHING_ATTSTATUS_LATE,
            'attexempt' => HYBRIDTEACHING_NOT_EXEMPT,
        ];

        $userattendancetime = $DB->get_record_sql($sql, $params);

        if ($userattendancetime->totalconnectime >= $attendancetomaxgrade) {
            $usergrade = $ht->grade;
        } else {
            $usergrade = ($userattendancetime->totalconnectime / $attendancetomaxgrade) * $ht->grade;
        }

        return $usergrade;
    }

    /**
     * Counts the number of attendance records for a given user and hybrid teaching.
     *
     * @param mixed $ht The hybrid teaching object.
     * @param int $userid The user ID.
     * @throws Exception If there is an error in the SQL query.
     * @return object The total number of attendance records.
     */
    public static function count_user_att($ht, $userid) {
        global $DB;

        $sql = "SELECT COUNT(ha.id) as totalattended
                  FROM {hybridteaching_attendance} ha
            INNER JOIN {hybridteaching_session} hs ON ha.sessionid = hs.id
                 WHERE ha.userid = :userid
                   AND ha.hybridteachingid = :hybridteachingid
                   AND (ha.status = :status OR ha.status = :status2)
                   AND hs.attexempt = :attexempt";

        $params = [
            'userid' => $userid,
            'hybridteachingid' => $ht->id,
            'status' => HYBRIDTEACHING_ATTSTATUS_VALID,
            'status2' => HYBRIDTEACHING_ATTSTATUS_LATE,
            'attexempt' => HYBRIDTEACHING_NOT_EXEMPT,
        ];

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Update the attendance grades for a given hybrid teaching session and users.
     *
     * @param int $htid The ID of the hybrid teaching session.
     * @param int $sessid The ID of the session.
     * @param array $userids An array of user IDs. Defaults to an empty array.
     * @throws Exception If the hybrid teaching session does not have a grade.
     * @return bool Whether the attendance grades were successfully updated.
     */
    public static function ht_update_users_att_grade($htid, $sessid, $userids = []) {
        global $DB;

        $ht = $DB->get_record('hybridteaching', ['id' => $htid]);
        $cmid = get_coursemodule_from_instance('hybridteaching', $htid);
        if (empty($ht->grade)) {
            return false;
        }

        list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'hybridteaching');

        !is_array($userids) ? $userids = [0 => $userids] : '';
        if (empty($userids)) {
            $context = \context_module::instance($cm->id);
            $userids = array_keys(get_enrolled_users($context, '', 0, 'u.id'));
        }

        foreach ($userids as $userid) {
            is_object($userid) ? $userid = $userid->userid : '';
            if (self::has_taken_attendance($ht->id, $userid, $sessid) && !self::is_session_exempt($sessid)) {
                if ($att = self::has_valid_attendance($ht->id, $sessid, $userid)) {
                    $att->grade = self::calc_att_grade_for($ht, $sessid, $att->id);
                    $DB->set_field('hybridteaching_attendance', 'grade', $att->grade, ['id' => $att->id]);
                }
            }
        }
    }

    /**
     * Checks if the attendance for a given hybrid teaching session and user is valid.
     *
     * @param int $htid The hybrid teaching ID.
     * @param int $sessid The session ID.
     * @param int $userid The user ID.
     * @return mixed|null Returns the attendance record if it exists, otherwise null.
     */
    public static function has_valid_attendance($htid, $sessid, $userid) {
        global $DB;
        return $DB->get_record('hybridteaching_attendance', [
            'hybridteachingid' => $htid,
            'sessionid' => $sessid,
            'userid' => $userid,
        ]);
    }

    /**
     * Calculates the attendance grade for a given hybrid teaching session.
     *
     * @param mixed $ht The hybrid teaching object.
     * @param int $sessid The session ID.
     * @param int $attid The attendance ID.
     * @throws Exception If unable to fetch required data from the database.
     * @return int The attendance grade for the session.
     */
    public static function calc_att_grade_for($ht, $sessid, $attid) {
        global $DB;
        $usersessgrade = 0;
        $userconnectime = $DB->get_field('hybridteaching_attendance', 'connectiontime', ['id' => $attid]);
        $sessduration = $DB->get_field('hybridteaching_session', 'duration', ['id' => $sessid]);
        if ($userconnectime >= $sessduration) {
            $usersessgrade = $ht->grade;
        } else {
            if ($sessduration) {
                $usersessgrade = ($userconnectime / $sessduration) * $ht->grade;
            } else {
                $usersessgrade = 0;
            }
        }
        $usersessgrade < 0 ? $usersessgrade = 0 : '';
        return $usersessgrade;
    }

    /**
     * Determines if a session is exempt from attendance.
     *
     * @param int $sessid The ID of the session to check.
     * @return bool Returns true if the session is exempt, false otherwise.
     */
    public static function is_session_exempt($sessid) {
        global $DB;
        return $DB->record_exists('hybridteaching_session', ['id' => $sessid, 'attexempt' => HYBRIDTEACHING_EXEMPT]);
    }

    /**
     * Finish connectime logs for a specific hybrid teaching session.
     *
     * @param int $htid The ID of the hybrid teaching session.
     * @param int $sessid The ID of the session.
     * @param array $userids An array of user IDs.
     * @return bool Returns false if the hybrid teaching session does not have a grade.
     */
    public static function finish_connectime_logs($htid, $sessid, $userids = []) {
        global $DB;

        $ht = $DB->get_record('hybridteaching', ['id' => $htid]);
        $cmid = get_coursemodule_from_instance('hybridteaching', $htid);
        if (empty($ht->grade)) {
            return false;
        }

        list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'hybridteaching');

        !is_array($userids) ? $userids = [0 => $userids] : '';
        if (empty($userids)) {
            $context = \context_module::instance($cm->id);
            $userids = array_keys(get_enrolled_users($context, '', 0, 'u.id'));
        }

        $attcontroller = new attendance_controller($ht);
        foreach ($userids as $userid) {
            is_object($userid) ? $userid = $userid->userid : '';
            if (self::has_taken_attendance($ht->id, $userid, $sessid) && !self::is_session_exempt($sessid)) {
                if ($att = self::has_valid_attendance($ht->id, $sessid, $userid)) {
                    $getlastattend = $attcontroller::hybridteaching_get_last_attend($att->id, $userid);
                    if (is_object($getlastattend) && $getlastattend->action == 1) {
                        $notify = $attcontroller::hybridteaching_set_attendance_log($ht, (int)$sessid, 0, $userid, $event = true);
                        $notify['ntype'] == 'success' ?
                            attendance_controller::hybridteaching_set_attendance($ht, (int)$sessid, 1, null, $userid)
                            : '';
                    } else {
                        attendance_controller::hybridteaching_set_attendance($ht, (int)$sessid, 0, null, $userid);
                    }
                }
            }
        }
    }
}
