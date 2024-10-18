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

namespace mod_hybridteaching\controller;

use mod_hybridteaching\helpers\grades;
use mod_hybridteaching\helper;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'../../../../../config.php');
require_login();

/**
 * Class attendance_controller.
 */
class attendance_controller extends \mod_hybridteaching\controller\common_controller {
    /** @var int The operator to use in bulk updates. Active */
    const ACTIVE = 1;

    /** @var int The operator to use in bulk updates. Inactive */
    const INACTIVE = 2;

    /** @var int The operator to use in bulk updates. Exempt */
    const EXEMPT = 3;

    /** @var int The operator to use in bulk updates. Not exempt */
    const NOTEXEMPT = 4;

    /** @var int The operator to use in bulk updates. Session exempt */
    const SESSIONEXEMPT = 5;

    /** @var int The operator to use in bulk updates. Not session exempt */
    const NOTSESSIONEXEMPT = 6;

    /** @var int The operator to use in bulk updates. Empty */
    const EMPTY = "-";


    /**
     * Load attendance records based on various parameters.
     *
     * @param int $page page number
     * @param int $recordsperpage number of records per page
     * @param array $params additional parameters
     * @param string $extraselect additional select conditions
     * @param string $operator comparison operator
     * @param string $sort sorting column
     * @param string $dir sorting direction
     * @param string $view type of view
     * @param int $sessionid session id
     * @param string $fname first name filter
     * @param string $lname last name filter
     * @return array loaded attendance records
     */
    public function load_attendance($page = 0, $recordsperpage = 0, $params = [], $extraselect = '',
          $operator = self::OPERATOR_GREATER_THAN, $sort = 'hs.name', $dir = 'ASC', $view = '',
          $sessionid = 0, $fname = '', $lname = '') {
        global $DB, $USER;
        $where = '';
        $groupby = '';
        $altergroupby = '';
        $params = $params + ['hybridteachingid' => $this->hybridobject->id];

        if (!empty($params['starttime'])) {
            $where .= ' AND starttime + duration '.$operator.' :starttime';
        }
        if (isset($params['view'])) {
            switch ($params['view']) {
                case 'extendedstudentatt':
                    $altergroupby .= '  ha.userid ';
                    break;
                case 'extendedsessionatt':
                    $groupby = ' ha.id    ';
                    break;
                case 'studentattendance':
                    $groupby = ' ha.sessionid ';
                    !$params['editing'] ? $where .= ' AND visible = 1' : '';
                    break;
                case 'sessionattendance':
                    $groupby = ' ha.id ';
                    break;
                default:
                    throw new \Exception('Invalid view type');
            }
            // $params['view'] == 'extendedstudentatt' ? $altergroupby .= '  ha.userid ' : $groupby = ' ha.sessionid ';
            // $params['view'] == 'extendedsessionatt' ? $groupby = ' ha.id    ' : '';
            // $params['view'] == 'studentattendance' && !$params['editing'] ? $where .= ' AND visible = 1' : '';
        } else {
            $groupby = ' ha.id ';
        }
        if (isset($params['groupid'])) {
            if ($params['view'] != 'studentattendance') {
                $params['groupid'] > 0 ? $where .= ' AND hs.groupid = ' . $params['groupid'] : '';
                $params['groupid'] == 0 ? $where .= ' AND hs.groupid = 0' : '';
            } else {
                $params['groupid'] > 0 ? $where .= ' AND groupid = ' . $params['groupid'] : '';
                $params['groupid'] == 0 ? $where .= ' AND groupid = 0' : '';
            }
        }
        if (!empty($extraselect)) {
            $where .= " AND $extraselect";
        }


        !empty($fname) ? $fname = " AND UPPER(u.firstname) like '" . $fname . "%' " : '';
        !empty($lname) ? $lname = " AND UPPER(u.lastname) like '" . $lname . "%' " : '';
        !isset($params['view']) ? $params['view'] = 'bulkform' : '';

        $secondarysort = '';
        if ($sort === 'connectiontime') {
            $dir == 'ASC' ? $secondarysort .= ', status = ' . HYBRIDTEACHING_ATTSTATUS_VALID
                : $secondarysort .= ', status != ' . HYBRIDTEACHING_ATTSTATUS_VALID;
        }
        if ($sort === 'status') {
            $dir == 'ASC' ? $sort = 'status = ' . HYBRIDTEACHING_ATTSTATUS_VALID
                : $sort = 'status != ' . HYBRIDTEACHING_ATTSTATUS_VALID;
            $dir = '';
        }
        if ($params['view'] == 'sessionattendance') {
            $sessionid != 0 ? $where .= ' AND id = ' . $sessionid . '' : false;
            $sql = 'SELECT hs.id, hs.name, hs.starttime, hs.duration, hs.typevc
                      FROM {hybridteaching_session} hs
                     WHERE hs.hybridteachingid = :hybridteachingid ' . $where . ' ' . '
                  ORDER BY ' . $sort . ' ' . $dir . ', visible desc';
        } else if ($params['view'] == 'studentattendance') {
            $sessionid != 0 ? $where .= ' AND id = ' . $sessionid . '' : false;
            $sql = 'SELECT *
                      FROM {hybridteaching_session}
                     WHERE hybridteachingid = :hybridteachingid ' . $where . '
                  ORDER BY ' . $sort . ' ' . $dir;
        } else {
            $sessionid != 0 ? $where .= ' AND sessionid = ' . $sessionid . '' : false;
            $sql = 'SELECT ha.id, ha.sessionid, hs.name, hs.starttime, hs.duration, hs.typevc, ha.visible, ha.grade,
                            ha.userid, CASE WHEN ha.connectiontime = 0 THEN -1 ELSE ha.type END as type, ha.status,
                            ha.connectiontime, (hs.starttime + hs.duration) endtime, ha.exempt, u.lastname, u.username
                      FROM {hybridteaching_attendance} ha
                      JOIN {hybridteaching_session} hs on (ha.sessionid = hs.id)
                      JOIN {user} u on (u.id = ha.userid)
                     WHERE ha.hybridteachingid = :hybridteachingid ' . $where . ' ' . $fname . $lname . '
                  GROUP BY ha.id,
                           ha.sessionid,
                           hs.name,
                           hs.starttime,
                           hs.duration,
                           hs.typevc,
                           ha.visible,
                           ha.grade,
                           ha.userid,
                           CASE WHEN ha.connectiontime = 0 THEN -1 ELSE ha.type END,
                           ha.status,
                           ha.connectiontime,
                           (hs.starttime + hs.duration),
                           ha.exempt,
                           u.lastname,
                           u.username ' . 'ORDER BY ' . $sort . ' ' . $dir . $secondarysort . ', visible desc';
        }
        $attendance = $DB->get_records_sql($sql, $params, $page * $recordsperpage, $recordsperpage);
        $attendancearray = json_decode(json_encode($attendance), true);
        return $attendancearray;
    }

    /**
     * A function to load attendance assistance.
     *
     * @param array $params optional parameters
     * @param string $extraselect additional select statement
     * @param int $operator comparison operator
     * @return array the loaded attendance assistance
     */
    public function load_attendance_assistance($params = [], $extraselect = '', $operator = self::OPERATOR_GREATER_THAN) {
        global $DB;
        $where = '';
        $params = $params + ['hybridteachingid' => $this->hybridobject->id];

        if (!empty($params['starttime'])) {
            $where .= ' AND starttime + duration '.$operator.' :starttime';
        }
        if (!empty($extraselect)) {
            $where .= " AND $extraselect ";
        }
        if (!empty($params['userid'])) {
            if ($params['view'] == 'studentattendance' || $params['view'] == 'extendedstudentatt') {
                $userid = $params['userid'];
                $where .= 'AND userid = ' . $userid . ' ';
            }
        }
        $sql = 'SELECT sessionid,
                        sum(CASE WHEN ha.type <> 0 THEN 0 ELSE 1 END) AS classroom,
                        sum(CASE WHEN ha.type <> 0 THEN 1 ELSE 0 END) AS vc
                  FROM {hybridteaching_session} hs
                  JOIN {hybridteaching_attendance} ha ON ha.sessionid = hs.id
                 WHERE ha.hybridteachingid = :hybridteachingid
                   AND ha.connectiontime != 0 ' . $where . '
              GROUP BY ha.sessionid';

        $attendance = $DB->get_records_sql($sql, $params);
        $attendancearray = json_decode(json_encode($attendance), true);
        return $attendancearray;
    }


    /**
     * Load sessions for the given attendant.
     *
     * @param object $attendancelist The list of attendance data.
     * @return object The user attendance record.
     */
    public function load_sessions_attendant($attendancelist) {
            global $DB;
        $where = '';
        if (is_array($attendancelist)) {
            $userid = $attendancelist['userid'];
        } else if (is_object($attendancelist)) {
            $userid = $attendancelist->userid;
        } else {
            $userid = $attendancelist;
        }

        return $DB->get_record('user', ['id' => $userid]);
    }

    /**
     * Sets the attendance for a hybrid teaching session.
     *
     * @param mixed $hybridteaching The hybrid teaching object.
     * @param mixed $session The session object.
     * @param int $status The attendance status. Default is 0.
     * @param int|null $atttype The attendance type. Default is null.
     * @param mixed|null $userid The user ID. Default is null.
     * @throws \Throwable When there is an error inserting or updating the attendance record.
     * @return mixed The attendance record ID if inserted, true if updated.
     */
    public static function hybridteaching_set_attendance($hybridteaching, $session,
            int $status = HYBRIDTEACHING_ATTSTATUS_NOTVALID,
            int $atttype = null, $userid = null) {
        global $DB, $USER, $PAGE;

        if (!empty($session) && is_number($session)) {
            $session = $DB->get_record('hybridteaching_session', ['id' => $session]);
        }

        if (empty($hybridteaching) || empty($session)) {
            return false;
        }

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $att = self::hybridteaching_get_attendance($session, $userid);
        if ($att) {
            if ($atttype === null) {
                $atttype = $att->type;
            }
        }

        if (is_int($session)) {
            $session = $DB->get_record('hybridteaching_session', ['id' => $session]);
        }
        $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        $atttype === null ? $type = $hybridteaching->usevideoconference : $type = $atttype;
        if (!$att) {
            $att = new \StdClass();
            $att->hybridteachingid = $hybridteaching->id;
            $att->sessionid = $session->id;
            $att->userid = $userid;
            $att->status = $status;
            $att->usermodified = $USER->id;
            $att->exempt = HYBRIDTEACHING_NOT_EXEMPT;
            $att->connectiontime = 0;
            $att->timecreated = $timenow;
            $att->timemodified = 0;
            try {
                $attend = $DB->insert_record('hybridteaching_attendance', $att);
                return $attend;
            } catch (\Throwable $dbexception) {
                throw $dbexception;
            }
        }
        $timespent = self::get_user_timespent($att->id);
        $neededtime = self::hybridteaching_get_needed_time($hybridteaching->validateattendance,
            $hybridteaching->attendanceunit, $session->duration);
        $status = self::verify_user_attendance($hybridteaching, $session, $att->id, $neededtime, $timespent);
        $att->type = $type;
        $att->status = $status;
        $att->exempt = HYBRIDTEACHING_NOT_EXEMPT;
        $att->connectiontime = 0;
        $att->usermodified = $USER->id;
        $att->timemodified = $timenow;
        $att->connectiontime = $timespent;
        try {
            $DB->update_record('hybridteaching_attendance', $att);
            list($course, $cm) = get_course_and_cm_from_instance($att->hybridteachingid, 'hybridteaching');
            $event = \mod_hybridteaching\event\attendance_updated::create([
                'objectid' => $att->hybridteachingid,
                'context' => \context_module::instance($cm->id),
                'relateduserid' => $att->userid,
                'other' => [
                    'sessid' => $att->sessionid,
                    'attid' => $att->id,
                ],
            ]);

            $event->trigger();
            return true;
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    /**
     * Get the attendance record for a given session and optional user.
     *
     * @param mixed $session The session ID or session object
     * @param int|null $userid The user ID (optional)
     * @return object|null The attendance record or null if not found
     */
    public static function hybridteaching_get_attendance($session, $userid = null) {
        global $DB;
        is_integer($session) ? $sessid = $session : $sessid = false;
        if (!$sessid && !empty($session)) {
            is_array($session) ? $sessid = $session['id'] : $sessid = $session->id;
        }
        $params = ['sessionid' => $sessid];
        !empty($userid) ? $params['userid'] = $userid : '';
        return $DB->get_record('hybridteaching_attendance', $params, '*', 1, IGNORE_MISSING);
    }

    /**
     * Retrieves the attendance record from the 'hybridteaching_attendance' table based on the given ID.
     *
     * @param int $attid The ID of the attendance record to retrieve. Default value is 1.
     * @throws Exception If the attendance record does not exist.
     * @return mixed The attendance record as an object.
     */
    public static function hybridteaching_get_attendance_from_id($attid = 1) {
        global $DB;
        if (!$attid) {
            return false;
        }
        return $DB->get_record('hybridteaching_attendance', ['id' => $attid], '*', MUST_EXIST);
    }

    /**
     * Count the attendance based on the given parameters.
     *
     * @param string $fname First name
     * @param string $lname Last name
     * @param string $view Type of view
     * @param array $params An array of parameters to filter the attendance records.
     * @param string $operator Operator to use for filtering the attendance records.
     * @return int
     */
    public function count_attendance($fname, $lname, $view, $params = [], $operator = self::OPERATOR_GREATER_THAN) {
        return count($this->load_attendance(0, 0, $params, '', $operator, 'name', 'asc', '', $view, $fname, $lname));
    }

    /**
     * Counts the attendance of sessions based on the given parameters.
     *
     * @param array $params An array of parameters to filter the attendance records.
     * @throws None
     * @return int The number of attendance records.
     */
    public function count_sess_attendance($params = []) {
        global $DB;
        $sql = 'SELECT *
                  FROM {hybridteaching_attendance}
                 WHERE sessionid = :sessionid
                   AND status != :status';
        return count($DB->get_records_sql($sql, $params));
    }

    /**
     * Set attendance log for hybrid teaching.
     *
     * @param object $hybridteaching Hybridteaching object
     * @param int $sessionid Session id
     * @param int $action Action type
     * @param int $userid User ID
     * @param bool $event Event flag
     * @return mixed
     */
    public static function hybridteaching_set_attendance_log($hybridteaching, $sessionid, $action, $userid = 0, $event = false) {
        global $DB, $USER;
        !$userid ? $userid = $USER->id : '';
        $att = self::hybridteaching_get_attendance($sessionid, $userid);
        $notify = '';
        if (!$att) {
            $att = new \StdClass();
            // Creates an empty attendance, for registering the log, if log isn't valid, delete the attendance.
            $id = $DB->get_field('hybridteaching_attendance', 'id',
                ['id' => self::hybridteaching_set_attendance($hybridteaching, $sessionid, 1, -1)], MUST_EXIST);
            $att->id = $id;
        }
        $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        if ($event) {
            $sessiontime = $DB->get_record('hybridteaching_session', ['id' => $sessionid], 'starttime, duration');
            $timenow = $sessiontime->starttime + $sessiontime->duration;
        }

        $log = self::hybridteaching_get_last_attend($att->id, $userid);
        if ($log) {
            if ($log->action == 1 && $action == 0) {
                $log = new \StdClass();
                $log->action = $action;
                $log->attendanceid = $att->id;
                $log->timecreated = $timenow;
                $log->usermodified = $USER->id;
                try {
                    $DB->insert_record('hybridteaching_attend_log', $log);
                    return $notify = ['gstring' => 'exitregistered', 'ntype' => \core\output\notification::NOTIFY_SUCCESS];
                } catch (\Throwable $dbexception) {
                    throw $dbexception;
                }
            } else if ($log->action == 0 && $action == 0) {
                return $notify = ['gstring' => 'exitingleavedsession', 'ntype' => \core\output\notification::NOTIFY_ERROR];
            } else if ($log->action == 0 && $action == 1) {
                $log = new \StdClass();
                $log->action = $action;
                $log->attendanceid = $att->id;
                $log->timecreated = $timenow;
                $log->usermodified = $USER->id;
                try {
                    $DB->insert_record('hybridteaching_attend_log', $log);
                    return $notify = ['gstring' => 'entryregistered', 'ntype' => \core\output\notification::NOTIFY_SUCCESS];
                } catch (\Throwable $dbexception) {
                    throw $dbexception;
                }
            } else {
                return $notify = ['gstring' => 'alreadyregistered', 'ntype' => \core\output\notification::NOTIFY_ERROR];
            }
            return $notify;
        }
        if ($action == 0) {
            return $notify = ['gstring' => 'entryneededtoexit', 'ntype' => \core\output\notification::NOTIFY_SUCCESS];
        } else {
            $log = new \StdClass();
            $log->action = $action;
            $log->attendanceid = $att->id;
            $log->timecreated = $timenow;
            $log->usermodified = $USER->id;
            try {
                $DB->insert_record('hybridteaching_attend_log', $log);
                return $notify = ['gstring' => 'entryregistered', 'ntype' => \core\output\notification::NOTIFY_SUCCESS];
            } catch (\Throwable $dbexception) {
                throw $dbexception;
            }
        }
    }

    /**
     * Get attendance logs for a specific attendance ID.
     *
     * @param int $attid The attendance ID
     * @param string $sort (Optional) The column to sort by (default is 'timecreated')
     * @param string $dir (Optional) The direction to sort in (default is 'ASC')
     * @throws \Throwable description of exception
     * @return array|null The attendance logs or null if the attendance ID is not provided
     */
    public static function hybridteaching_get_attendance_logs($attid, $sort = 'timecreated', $dir = 'ASC') {
        global $DB;
        if (!$attid) {
            return false;
        }
        $sql = "SELECT al.*
                  FROM {hybridteaching_attend_log} al
                  JOIN {hybridteaching_attendance} att
                    ON (al.attendanceid = att.id)
                 WHERE att.id = :attid
              ORDER BY al." . $sort . " " . $dir;
        $params = ['attid' => $attid];
        try {
            return $DB->get_records_sql($sql, $params);
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    /**
     * Retrieves the last attendance log for a given attendance ID and user ID.
     *
     * @param int $attid The ID of the attendance.
     * @param int $userid The ID of the user.
     * @throws \Throwable When there is an error retrieving the attendance log.
     * @return mixed The last attendance log record or 0 if not found.
     */
    public static function hybridteaching_get_last_attend($attid, $userid) {
        global $DB;
        $sql = "SELECT al.id, al.action
                  FROM {hybridteaching_attend_log} al
                  JOIN {hybridteaching_attendance} att
                    ON (al.attendanceid = att.id)
                 WHERE att.id = :attid
                   AND att.userid = :userid
              ORDER BY al.timecreated DESC
                 LIMIT 1";
        $params = ['attid' => $attid, 'userid' => $userid];
        try {
            $r = $DB->get_record_sql($sql, $params);
            if ($r) {
                return $r;
            }

            return 0;
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    /**
     * Calculates the needed time based on the given time value and unit.
     *
     * @param int $timevalue The value of time.
     * @param int $timeunit The unit of time (0: hours, 1: minutes, 2: seconds, 3: percentage).
     * @param int $sesstime The session time (default: 0).
     * @return int The calculated needed time.
     */
    public static function hybridteaching_get_needed_time($timevalue, $timeunit, $sesstime = 0): int {
        switch ($timeunit) {
            case 3:
                $neededtime = $sesstime * ($timevalue / 100);
                break;
            case 2:
                $neededtime = $timevalue;
                break;
            case 1:
                $neededtime = $timevalue * 60;
                break;
            case 0:
                $neededtime = $timevalue * 3600;
                break;
        }
        return (int) $neededtime;
    }

    /**
     * Generates a string containing session information for printing.
     *
     * @param mixed $session The session object.
     * @return string The string containing session information.
     */
    public static function hybridteaching_print_session_info($session): String {
        if (!$session) {
            return '<h2>' . get_string('allsessions', 'hybridteaching') . '</h2>';
        }
        $s = '<br>';
        $s .= get_string('name') . ': ' . $session->name;
        $s .= '<br>' . get_string('date') . ': ' . date('l, j F Y H:i', $session->starttime).'<br>';
        $s .= get_string('group') . ': ';
        $session->groupid == 0 ? $s .= get_string('commonattendance', 'hybridteaching') :
            $s .= groups_get_group($session->groupid)->name;
        $s .= '<br>' . get_string('typevc', 'hybridteaching') . ': ';
        $session->typevc != '' ? $s .= $session->typevc : $s .= get_string('novc', 'hybridteaching');
        $s .= '<br>' . get_string('sessionstarttime', 'hybridteaching') . ': ' . date('H:i:s', $session->starttime);
        ($session->starttime + $session->duration) == $session->starttime ?
            $s .= '<br>' . get_string('sessionendtime', 'hybridteaching') . ': ' .  get_string('noduration', 'hybridteaching')
            :
            $s .= '<br>' . get_string('sessionendtime', 'hybridteaching') . ': ' .
                date('H:i:s', $session->starttime + $session->duration);
        $s .= '<br>' . get_string('duration', 'hybridteaching') . ': ';
        !empty($session->duration) ? $s .= helper::get_hours_format($session->duration) : $s .= ' - ';
        return $s;
    }

    /**
     * Print attendance for a user in the hybrid teaching system.
     *
     * @param int $hid The hybrid teaching ID
     * @param object $user The user object
     * @return String The attendance information for the user
     */
    public static function hybridteaching_print_attendance_for_user($hid, $user): String {
        global $DB, $OUTPUT;

        if (!$hid || !$user) {
            return '<h2>' . get_string('notattendance', 'hybridteaching') . '</h2>';
        }
        $userid = $user->id;
        $grades = new grades();
        $ht = $DB->get_record('hybridteaching', ['id' => $hid], '*');
        $total = $DB->count_records('hybridteaching_attendance', ['hybridteachingid' => $hid, 'userid' => $userid]);
        $notattended = $DB->count_records('hybridteaching_attendance', ['hybridteachingid' => $hid, 'userid' => $userid,
            'connectiontime' => 0, ]);
        $validated = $DB->count_records('hybridteaching_attendance', ['hybridteachingid' => $hid, 'userid' => $userid,
            'status' => 1, ]);
        $exempted = $DB->count_records('hybridteaching_attendance', ['hybridteachingid' => $hid, 'userid' => $userid,
            'status' => 3, ]);
        $finalgrade = $grades->get_instance_grade_for($ht, $userid);
        $s = $OUTPUT->user_picture($user) . $user->lastname . ' ' . $user->firstname . '<br>';
        $s .= get_string('attendedsessions', 'hybridteaching') . ': ';
        $s .= $total - $notattended;
        $s .= '/';
        $s .= $total;
        $s .= '<br>' . get_string('exempt', 'hybridteaching') . ': ' . $exempted;
        $s .= '<br>' . get_string('validatedattendance', 'hybridteaching') . ': ' . $validated . '/' . $total;
        if ($ht->grade) {
            $s .= '<br>' . get_string('finalgrade', 'hybridteaching') . ': ' . round($finalgrade, 2) . '/' . $ht->grade;
        } else {
            $s .= '<br>' . get_string('nograde');
        }
        return $s;
    }


    /**
     * Get attendance users in session.
     *
     * @param int $sessionid Session ID
     * @param int $hid Hybridteaching ID
     * @throws \Throwable Database exception
     * @return object
     */
    public static function hybridteaching_get_attendance_users_in_session($sessionid, $hid) {
        global $DB;
        $sql = "SELECT att.userid, att.id, u.firstname, u.lastname
                  FROM {hybridteaching_attendance} att
                  JOIN {hybridteaching_session} s
                    ON (att.sessionid = s.id)
                  JOIN {user} u
                    ON (u.id = att.userid)
                 WHERE s.id = :sessionid
                   AND att.hybridteachingid = :hid
              ORDER BY u.lastname ASC";
        $params = ['sessionid' => $sessionid, 'hid' => $hid];
        try {
            return $DB->get_records_sql($sql, $params);
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    /**
     * Retrieves the entry and end times for a given attendance ID.
     *
     * @param int $attendanceid The ID of the attendance.
     * @throws \Throwable If there is an error with the database query.
     * @return array An associative array containing the entry time, end time, and last action.
     */
    public static function hybridteaching_get_attendance_entry_end_times($attendanceid) {
        global $DB;
        $sqlentry = "SELECT al.timecreated as entrytime
                       FROM {hybridteaching_attend_log} al
                      WHERE al.attendanceid = :attid
                   ORDER BY al.timecreated ASC
                      LIMIT 1";
        $sqlend = "SELECT al.timecreated as endtime, al.action
                     FROM {hybridteaching_attend_log} al
                    WHERE al.attendanceid = :attid
                 ORDER BY al.timecreated DESC
                    LIMIT 1";
        $params = ['attid' => $attendanceid];
        try {
            if (!$DB->get_record_sql($sqlentry, $params)) {
                $entrytime = 0;
            } else {
                $entrytime = $DB->get_record_sql($sqlentry, $params)->entrytime;
            }
            if ($endlog = $DB->get_record_sql($sqlend, $params)) {
                $endtime = $endlog->endtime;
                $action = $endlog->action;
            } else {
                $endtime = 0;
                $action = 0;
            }

            return ['entry' => $entrytime, 'end' => $endtime, 'lastaction' => $action];
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }


    /**
     * Get instance users
     *
     * @param int $hid Hybridteaching ID
     * @throws \Throwable Database exception
     * @return mixed
     */
    public static function hybridteaching_get_instance_users($hid) {
        global $DB;

        if (!$hid) {
            return false;
        }
        list($course, $cm) = get_course_and_cm_from_instance($hid, 'hybridteaching');
        return get_enrolled_users(\context_module::instance($cm->id), 'mod/hybridteaching:attendanceregister',
            0,  $userfields = 'u.id, u.firstname, u.lastname',  $orderby = '', 0, 0);
    }


    /**
     * Retrieves students' participation data based on certain criteria.
     *
     * @param string $hid The hybrid teaching ID
     * @param string $sort The field to sort by
     * @param string $dir The sort direction
     * @param string $fname The first name to filter by
     * @param string $lname The last name to filter by
     * @throws \Throwable Database exception
     * @return object The student's participation data
     */
    public static function hybridteaching_get_students_participation($hid = '', $sort = 'id', $dir = 'asc',
            $fname = '', $lname = '') {
        global $DB;

        if (!$hid) {
            return false;
        }
        $params = [];
        $sortd = [0 => 'total', 1 => 'vc' , 2 => 'classroom', 3 => 'lastname'];
        !in_array($sort, $sortd) ? $prefix = 'u.' : $prefix = '';
        !empty($fname) ? $fname = " AND UPPER(u.firstname) like '" . $fname . "%' " : '';
        !empty($lname) ? $lname = " AND UPPER(u.lastname) like '" . $lname . "%' " : '';
        $users = self::hybridteaching_get_instance_users($hid);
        [$insql, $inparams] = $DB->get_in_or_equal($users, SQL_PARAMS_NAMED, 'id');
        foreach ($inparams as $key => $values) {
            $params[$key] = $values->id;
        }
        $sql = "SELECT u.id AS userid, u.lastname
                  FROM {user} u
             LEFT JOIN {hybridteaching_attendance} ha ON  u.id = ha.userid
                 WHERE u.id " . $insql . " " . $fname . $lname .
              " GROUP BY ha.userid, u.lastname, u.id
              ORDER BY " . $prefix . $sort . " " . $dir;
        try {
            return $DB->get_records_sql($sql, $params);
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    /**
     * Get student participation.
     *
     * @param string $hid The hybrid teaching ID
     * @param string $userid The user ID
     * @throws \Throwable Database exception
     * @return object The student's participation data
     */
    public static function get_student_participation($hid = '', $userid = 0) {
        global $DB;

        if (!$hid || !$userid) {
            return false;
        }
        $sql = "SELECT ha.userid,
                         COUNT(type) AS total,
                      SUM(CASE
                         WHEN type <> 0 THEN 0
                         ELSE 1
                      END) AS classroom,
                      SUM(CASE
                         WHEN type <> 0 THEN 1
                         ELSE 0
                      END) AS vc
                  FROM
                       {hybridteaching_attendance} ha
                  JOIN
                       {hybridteaching_session} s ON (ha.sessionid = s.id)
                 WHERE ha.hybridteachingid = :hid
                   AND (ha.connectiontime != 0 OR ha.status = 1)
                   AND ha.visible = 1
                   AND ha.userid = :userid
              GROUP BY ha.userid";

        try {
            return $DB->get_record_sql($sql, ['hid' => $hid, 'userid' => $userid]);
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    /**
     * Updates multiple attendance records.
     *
     * @param array $attids The array of attendance IDs.
     * @param mixed $data The data used to update the attendance records.
     * @throws Some_Exception_Class Exception thrown if there is an error updating the attendance records.
     * @return string The error message, if any.
     */
    public function update_multiple_attendance($attids, $data) {
        global $DB, $USER;
        $errormsg = '';
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        foreach ($attids as $attid) {
            $updateatt = true;
            $att = $DB->get_record('hybridteaching_attendance', ['id' => $attid]);
            $session = $DB->get_record('hybridteaching_session', ['id' => $att->sessionid]);
            $att->timemodified = $timemodified;
            $att->usermodified = $USER->id;
            switch ($data->operation) {
                case self::ACTIVE:
                    $att->status == HYBRIDTEACHING_ATTSTATUS_VALID || $att->exempt == HYBRIDTEACHING_EXEMPT ?
                        $updateatt = false : $att->status = HYBRIDTEACHING_ATTSTATUS_VALID;
                    $att->connectiontime = $session->duration;
                    break;
                case self::INACTIVE:
                    $att->status == HYBRIDTEACHING_ATTSTATUS_NOTVALID || $att->exempt == HYBRIDTEACHING_EXEMPT ?
                        $updateatt = false : $att->status = HYBRIDTEACHING_ATTSTATUS_NOTVALID;
                    $att->connectiontime = 0;
                    break;
                case self::EXEMPT:
                    $att->exempt == HYBRIDTEACHING_EXEMPT ?
                        $updateatt = false : $att->exempt = HYBRIDTEACHING_EXEMPT;
                    $att->status = HYBRIDTEACHING_ATTSTATUS_EXEMPT;
                    break;
                case self::NOTEXEMPT:
                    $att->exempt == HYBRIDTEACHING_NOT_EXEMPT ?
                        $updateatt = false : $att->exempt = HYBRIDTEACHING_NOT_EXEMPT;
                    if ($DB->update_record('hybridteaching_attendance', $att)) {
                        $hybridteaching = $DB->get_record('hybridteaching', ['id' => $att->hybridteachingid]);
                        $timeneeded = self::hybridteaching_get_needed_time($hybridteaching->validateattendance,
                            $hybridteaching->attendanceunit, $session->duration);
                        $timespent = self::get_user_timespent($attid);
                        $att->status = self::verify_user_attendance($hybridteaching->id, $session, $attid, $timeneeded, $timespent);
                        $updateatt = true;
                    }
                    break;
            }
            if ($updateatt) {
                if (!$DB->update_record('hybridteaching_attendance', $att)) {
                    $errormsg = 'errorupdateattendance';
                } else {
                    list($course, $cm) = get_course_and_cm_from_instance($att->hybridteachingid, 'hybridteaching');
                    $event = \mod_hybridteaching\event\attendance_updated::create([
                        'objectid' => $att->hybridteachingid,
                        'context' => \context_module::instance($cm->id),
                        'relateduserid' => $att->userid,
                        'other' => [
                            'sessid' => $att->sessionid,
                            'attid' => $att->id,
                        ],
                    ]);

                    $event->trigger();
                }
            }
        }

        return $errormsg;
    }

    /**
     * Update session visibility.
     *
     * @param int $sessid The session ID
     * @param int $action The action to be taken
     */
    public static function update_session_visibility($sessid, $action) {
        global $DB, $USER;

        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid], 'id, hybridteachingid');
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        $session->visibleatt = $action;
        $session->modifiedby = $USER->id;
        $session->timemodified = $timemodified;
        if ($DB->update_record('hybridteaching_session', $session)) {
            $atts = $DB->get_records('hybridteaching_attendance', ['sessionid' => $sessid]);
            foreach ($atts as $att) {
                $att->visible = $action;
                $DB->update_record('hybridteaching_attendance', $att);
            }
        }
    }

    /**
     * Update multiple sessions exempt status.
     *
     * @param array $sessionids The array of session ids to be updated
     * @param object $data The data to be used for the update
     * @return string Error message if any
     */
    public function update_multiple_sessions_exempt($sessionids, $data) {
        global $DB, $USER;
        $errormsg = '';
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        foreach ($sessionids as $sessid) {
            $updateatt = true;
            $session = $DB->get_record('hybridteaching_session', ['id' => $sessid], '*');
            $session->timemodified = $timemodified;
            $session->modifiedby = $USER->id;
            switch ($data->operation) {
                case self::SESSIONEXEMPT:
                    $session->attexempt == HYBRIDTEACHING_EXEMPT ? $updateatt = false :
                        $session->attexempt = HYBRIDTEACHING_EXEMPT;
                    break;
                case self::NOTSESSIONEXEMPT:
                    $session->attexempt == HYBRIDTEACHING_NOT_EXEMPT ? $updateatt = false :
                        $session->attexempt = HYBRIDTEACHING_NOT_EXEMPT;
                    break;
            }
            if ($updateatt) {
                if (!$DB->update_record('hybridteaching_session', $session)) {
                    $errormsg = 'errorupdateattendance';
                } else {
                    $attends = $DB->get_records('hybridteaching_attendance', ['sessionid' => $sessid], '', 'id, userid');
                    list($course, $cm) = get_course_and_cm_from_instance($session->hybridteachingid, 'hybridteaching');
                    foreach ($attends as $att) {
                        $session->attexempt ? $att->visible = 0 : $att->visible = 1;
                        $DB->update_record('hybridteaching_attendance', $att);
                        $event = \mod_hybridteaching\event\session_updated::create([
                            'objectid' => $this->hybridobject->id,
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
            }
        }

        return $errormsg;
    }

    /**
     * Retrieves the total time spent by a user in the attendance log.
     *
     * @param int $attid The ID of the attendance record.
     * @return int The total time spent by the user.
     */
    public static function get_user_timespent($attid): int {
        global $DB;

        $attlogs = $DB->get_records('hybridteaching_attend_log', ['attendanceid' => $attid], 'id asc', '*');
        $timespent = 0;
        foreach ($attlogs as $log) {
            $nextlog = next($attlogs);
            if ($log->action && $nextlog) {
                $periodspent = $nextlog->timecreated - $log->timecreated;
                $timespent += $periodspent;
            }
        }
        return $timespent;
    }


    /**
     * Verify user attendance for the hybrid teaching session.
     *
     * @param object $hybridteaching Hybridteaching object
     * @param object $session Session object
     * @param int $attid Attendance ID
     * @param int $timeneeded The time needed by the user.
     * @param int $timespent The total time spent by the user.
     * @return int
     */
    public static function verify_user_attendance($hybridteaching, $session, $attid, $timeneeded = 0, $timespent = 0): int {
        global $DB;
        $session && is_int($session) ? $session = $DB->get_record('hybridteaching_session',
            ['id' => $session], '*', IGNORE_MISSING) : '';
        $timeremaining = $timeneeded - $timespent;
        $att = self::hybridteaching_get_attendance_from_id($attid);
        empty($session) ? $attexempt = HYBRIDTEACHING_NOT_EXEMPT : $attexempt = $session->attexempt;
        // Exempt.
        if ($att->exempt || $attexempt) {
            return HYBRIDTEACHING_ATTSTATUS_EXEMPT;
        }

        if ($timespent == 0) {
            return HYBRIDTEACHING_ATTSTATUS_NOTVALID;
        }

        if ($timeremaining > 0 || $timeneeded == 0) {
            // Late arrive.
            if (!self::user_attendance_in_grace_period($hybridteaching, $session, $att->id)) {
                return HYBRIDTEACHING_ATTSTATUS_LATE;
            }
            // Early leave.
            if (self::user_attendance_early_leave($session, $att)) {
                return HYBRIDTEACHING_ATTSTATUS_EARLYLEAVE;
            }

            if ($timeneeded < $timespent) {
                return HYBRIDTEACHING_ATTSTATUS_VALID;
            }
            // Not valid attendance.
            return HYBRIDTEACHING_ATTSTATUS_NOTVALID;
        }
        // Late arrive.
        if (!self::user_attendance_in_grace_period($hybridteaching, $session, $att->id)) {
            return HYBRIDTEACHING_ATTSTATUS_LATE;
        }
        // Valid attendance.
        return HYBRIDTEACHING_ATTSTATUS_VALID;
    }


    /**
     * Check if the user attendance is within the grace period.
     *
     * @param object $ht Hybridteaching object
     * @param object $session Session object
     * @param int $attid Attendance ID
     * @return bool
     */
    public static function user_attendance_in_grace_period($ht, $session, $attid): bool {
        global $DB;
        $intime = true;
        empty($ht) || (!isset($ht->graceperiod) || !isset($ht->graceperiodunit)) ? $gracetime = 0 :
        $gracetime = self::hybridteaching_get_needed_time($ht->graceperiod, $ht->graceperiodunit);
        if (!$gracetime) {
            return true;
        }
        $logtime = 0;
        if (!isset($session->starttime)) {
            $starttime = $DB->get_field('hybridteaching_session', 'starttime', ['id' => $session], IGNORE_MISSING);
            $graceend = $starttime + $gracetime;
        } else {
            $graceend = $session->starttime + $gracetime;
        }
        $log = $DB->get_records('hybridteaching_attend_log', ['attendanceid' => $attid],
            'timecreated ASC', 'attendanceid, timecreated', 0, 1, IGNORE_MISSING);
        if (isset($log[$attid])) {
            $log[$attid] ? $logtime = $log[$attid]->timecreated : '';
        }
        $logtime > $graceend ? $intime = false : '';
        return $intime;
    }

    /**
     * Used for defining the user attendance as earlyleave.
     *
     * @param object $session The session used.
     * @param object $att The attendance used.
     * @return bool The user did or didn't leavedearly.
     */
    public static function user_attendance_early_leave($session, $att): bool {
        global $DB;

        $leavedearly = false;
        is_int($session) ? $session = $DB->get_record('hybridteaching_session', ['id' => $session]) : '';
        if (!$session) {
            return false;
        }
        if ($session->isfinished && $att->connectiontime) {
            $logs = self::hybridteaching_get_attendance_entry_end_times($att->id);
            $logs['lastaction'] == 0 && $logs['end'] < ($session->starttime + $session->duration) ? $leavedearly = true : false;
        }
        return($leavedearly);
    }

    /**
     * Applies the chosen filter to the tables row.
     *
     * @param array $bparams The body params used for filtering.
     * @param string $filter The chosen filter
     * @param string $view The attendance view.
     * @return bool Hide the table row or not.
     */
    public static function display_attendance_row($bparams, $filter, $view): bool {
        $displayrow = true;
        switch($filter) {
            case 'nofilter':
                break;
            case 'att':
                if ($view == 'sessionattendance') {
                    ($bparams['vc'] + $bparams['classroom']) == 0 ? $displayrow = false : '';
                }
                if ($view == 'extendedsessionatt' || $view == 'studentattendance') {
                    $bparams['permanence'] == self::EMPTY ? $displayrow = false : '';
                }
                break;
            case 'notatt':
                if ($view == 'sessionattendance') {
                    ($bparams['vc'] + $bparams['classroom']) != 0 ? $displayrow = false : '';
                }
                if ($view == 'extendedsessionatt'|| $view == 'studentattendance') {
                    $bparams['permanence'] > 0 ? $displayrow = false : '';
                }
                break;
            case 'exempt':
                $bparams['status'] != 3 ? $displayrow = false : '';
                break;
            case 'notexempt':
                $bparams['status'] == 3 ? $displayrow = false : '';
                break;
            case 'late':
                $bparams['status'] != 2 ? $displayrow = false : '';
                break;
            case 'leaved':
                $bparams['status'] != 4 ? $displayrow = false : '';
                break;
            case 'classroom':
                !$bparams['classroom'] ? $displayrow = false : '';
                break;
            case 'vc':
                !$bparams['vc'] ? $displayrow = false : '';
                break;
            default:
                break;
        }
        return $displayrow;
    }

    /**
     * Determines if a user belongs to a session group.
     *
     * @param int $userid The ID of the user.
     * @param int $sessid The ID of the session.
     * @return bool Whether the user belongs to the session group.
     */
    public static function user_belongs_in_session_group($userid, $sessid): bool {
        global $DB;

        $useringroup = false;
        $sessgroup = $DB->get_field('hybridteaching_session', 'groupid', ['id' => $sessid], IGNORE_MISSING);
        if ($sessgroup) {
            $DB->get_record('groups_members', ['groupid' => $sessgroup, 'userid' => $userid],
                IGNORE_MISSING) ? $useringroup = true : '';
        } else {
            $useringroup = true;
        }
        return $useringroup;
    }

    /**
     * Calculates the number of attendances that uses groups.
     *
     * @param array $attendances An array of attendance data.
     * @return int The number of attendances that uses groups.
     */
    public function attendances_uses_groups($attendances): int {
        global $DB;

        $usesattendance = 0;
        foreach ($attendances as $att) {
            if (!$usesattendance) {
                isset($att['sessionid']) ? $sessiongroup = $DB->get_field('hybridteaching_session',
                    'groupid', ['id' => $att['sessionid']], IGNORE_MISSING) :
                $sessiongroup = $DB->get_field('hybridteaching_session', 'groupid', ['id' => $att['id']], IGNORE_MISSING);
                $sessiongroup ? $usesattendance = 1 : '';
            }
        }
        return $usesattendance;
    }

    /**
     * A description of the entire PHP function.
     *
     * @param int $sessionid Session ID
     * @param int $userid User ID
     * @return object
     */
    public function get_session_attendance($sessionid, $userid) {
        global $DB;

        return $DB->get_record('hybridteaching_attendance', ['sessionid' => $sessionid,
            'userid' => $userid, ], '*', IGNORE_MISSING);
    }

    /**
     * Checks if a user can join a session
     *
     * @param int $sessionid Session ID
     * @param int $userid User ID
     * @return object
     */
    public static function can_user_join($sessionid, $userid) {
        global $DB;

        $shouldjoin = true;
        $attid = $DB->get_field('hybridteaching_attendance', 'id', ['sessionid' => $sessionid, 'userid' => $userid]);
        if ($attid) {
            $logaction = self::hybridteaching_get_last_attend($attid, $userid);
            if (is_object($logaction) && $logaction->action == 0) {
                $shouldjoin = true;
            }else if (is_object($logaction) && $logaction->action == 1) {
                $shouldjoin = false;
            } else if (is_number($logaction) && $logaction == 0) {
                // No logs, can join.
                $shouldjoin = true;
            }
        }
        return $shouldjoin;
    }
}
