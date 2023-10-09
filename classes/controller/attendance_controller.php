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

/**
 * The students attendance helper.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once('common_controller.php');
require_once('sessions_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helpers/grades.php');
require_once(dirname(__FILE__).'../../../../../config.php');

class attendance_controller extends common_controller {

    const ACTIVE = 1;
    const INACTIVE = 2;
    const EXEMPT = 3;
    const NOTEXEMPT = 4;
    const SESSIONEXEMPT = 5;
    const NOTSESSIONEXEMPT = 6;
    /**
     * Retrieves a list of attendance for the current hybrid teaching module.
     *
     * @param int $page page number to display.
     * @param int $recordsperpage number of records to display per page.
     * @param array $params optional parameters to filter the query results.
     * @param string $operator comparison operator to use in the WHERE clause.
     * @return array An array of session objects.
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
        if (!empty($params['userid'])) {
            if ($params['view'] == 'studentattendance') {
                $userid = $params['userid'];
                $where .= 'AND userid = ' . $userid . ' ';
            }
        }
        if (isset($params['view'])) {
            $params['view'] == 'extendedstudentatt'  ? $altergroupby .= '  ha.userid ': $groupby = ' ha.sessionid ';
            $params['view'] == 'extendedsessionatt' ? $groupby = ' ha.id ' : '';
        } else {
            $groupby = ' ha.id ';
        }

        if (!empty($extraselect)) {
            $where .= " AND $extraselect";
        }

        $sessionid != 0 ? $where .= ' AND sessionid = ' . $sessionid . '' : false;

        !empty($fname) ? $fname = " AND u.firstname like '" . $fname . "%' " : ''; 
        !empty($lname) ? $lname = " AND u.lastname like '" . $lname . "%' " : ''; 

        $sql = 'SELECT ha.id, ha.sessionid, ha.id, hs.name, hs.starttime, hs.duration, hs.typevc, ha.visible, ha.grade,
                            ha.userid, CASE WHEN ha.connectiontime = 0 THEN -1 ELSE ha.type END as type, ha.status,
                            ha.connectiontime, (hs.starttime + hs.duration) endtime, ha.exempt, u.lastname, u.username
                  FROM {hybridteaching_attendance} ha
                  JOIN {hybridteaching_session} hs on (ha.sessionid = hs.id)
                  JOIN {user} u on (u.id = ha.userid)
                 WHERE ha.hybridteachingid = :hybridteachingid ' . $where . ' ' . $fname . $lname . '
              GROUP BY' . $groupby .  $altergroupby . 'ORDER BY ' . $sort . ' ' . $dir . ', visible desc';

        $attendance = $DB->get_records_sql($sql, $params, $page * $recordsperpage, $recordsperpage);
        $attendancearray = json_decode(json_encode($attendance), true);
        return $attendancearray;
    }

    /**
     * Retrieves a list of attendance for the current hybrid teaching module.
     *
     * @param int $page page number to display.
     * @param int $recordsperpage number of records to display per page.
     * @param array $params optional parameters to filter the query results.
     * @param string $operator comparison operator to use in the WHERE clause.
     * @return array An array of session objects.
     */
    public function load_attendance_assistance($page = 0, $recordsperpage = 0, $params = [], $extraselect = '',
          $operator = self::OPERATOR_GREATER_THAN, $sort = 'id', $dir = 'ASC') {
            global $DB;
            $where = '';
            $params = $params + ['hybridteachingid' => $this->hybridobject->id];

            if (!empty($params['starttime'])) {
                $where .= ' AND starttime + duration '.$operator.' :starttime';
            }
            if (!empty($extraselect)) {
                $where .= " AND $extraselect";
            }
            if (!empty($params['userid'])) {
                if ($params['view'] == 'studentattendance') {
                    $userid = $params['userid'];
                    $where .= 'AND userid = ' . $userid . ' ';
                }
            }
            $sql = 'SELECT sessionid,
                            sum(CASE WHEN type THEN 0 ELSE 1 END) AS classroom,
                            sum(CASE WHEN type THEN 1 ELSE 0 END) AS vc
                     FROM {hybridteaching_session} hs
                     JOIN {hybridteaching_attendance} ha ON ha.sessionid = hs.id
                    WHERE ha.hybridteachingid = :hybridteachingid 
                      AND ha.connectiontime != 0 ' . $where . '
                 GROUP BY ha.sessionid';

            $attendance = $DB->get_records_sql($sql, $params, $page * $recordsperpage, $recordsperpage);
            $attendancearray = json_decode(json_encode($attendance), true);
            return $attendancearray;
    }

    /**
     * Retrieves a list of attendance for the current hybrid teaching module.
     *
     * @param int $page page number to display.
     * @param int $recordsperpage number of records to display per page.
     * @param array $params optional parameters to filter the query results.
     * @param string $operator comparison operator to use in the WHERE clause.
     * @return array An array of session objects.
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

            $params = ['hybridteachingid' => $this->hybridobject->id];
            $sql = 'SELECT *
                      FROM {user}
                     WHERE id =' . $userid . '';
           return $attendance = $DB->get_record_sql($sql, $params);
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
    public static function hybridteaching_set_attendance($hybridteaching, $session, int $status = 0, int $atttype = null, $userid = null) {
        global $DB, $USER, $PAGE;

        if (empty($userid)) {
            $userid = $USER->id;
        }
        
        $att = self::hybridteaching_get_attendance($hybridteaching, $session, $userid);
        if ($att) {
            if ($atttype === null) {
                $atttype = $att->type;
            }
        }
        $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        $atttype === null ? $type = $hybridteaching->usevideoconference : $type = $atttype;
        if (!$att) {
            $att = new \StdClass();
            $att->hybridteachingid = $hybridteaching->id;
            $att->sessionid = $session->id;
            $att->userid = $userid;
            $att->status = $status;
            $att->type = $type;
            $att->usermodified = $USER->id;
            $att->timecreated = $timenow;
            $att->timemodified = $timenow;
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
        $att->usermodified = $USER->id;
        $att->timemodified = $timenow;
        $att->connectiontime = $timespent;
        try {
            $DB->update_record('hybridteaching_attendance', $att);

            $event = \mod_hybridteaching\event\attendance_updated::create(array(
                'objectid' => $hybridteaching->id,
                'context' => \context_course::instance($hybridteaching->course),
                'other' => array(
                    'sessid' => $session->id,
                    'userid' => $userid,
                    'attid' => $att->id
                )
            ));

            $event->trigger();

            $grades = new grades();
            $grade = round($grades->calc_att_grade_for($hybridteaching, $session->id, $att->id), 2);
            $DB->set_field('hybridteaching_attendance', 'grade', $grade, ['id' => $att->id]);
            return true;
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    /**
     * Retrieves the attendance record for a specific hybrid teaching session and user.
     *
     * @param object $hybridteaching An object representing the hybrid teaching.
     * @param mixed $session The session ID or session object.
     * @param int|null $userid (Optional) The user ID. Defaults to the current user.
     * @return mixed The attendance record for the specified hybrid teaching session and user.
     */
    public static function hybridteaching_get_attendance($hybridteaching, $session, $userid = null) {
        global $DB, $USER;
        is_integer($session) ? $sessid = $session : $sessid = false;
        if (!$sessid) {
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
     * Counts the number of attendances for a given hybrid teaching object.
     *
     * @param array $params An optional array of parameters to filter the attendance by.
     *                      Defaults to an empty array.
     *                      - hybridteachingid: The ID of the hybrid teaching object to count attendance for.
     * @global moodle_database $DB The Moodle database object.
     * @throws Exception If the database query fails.
     * @return int The number of attendances for the given hybrid teaching object and parameters.
     */
    public function count_attendance($params = [], $operator = self::OPERATOR_GREATER_THAN, $s, $fname, $lname) {
        //
        return count($this->load_attendance(0, 0, $params, '', $operator, 'name', 'asc', '', $s, $fname, $lname));
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
        return count($DB->get_records('hybridteaching_attendance', $params, '', 'id'));
    }


    /**
     * Sets the attendance log for hybrid teaching.
     *
     * @param mixed $hybridteaching description
     * @param mixed $session description
     * @param mixed $time description
     * @param mixed $action description
     * @throws \Throwable description of exception
     * @return mixed
     */
    public static function hybridteaching_set_attendance_log($hybridteaching, $session, $time, $action) {
        global $DB, $USER;

        $att = self::hybridteaching_get_attendance($hybridteaching, $session, $USER->id);
        if (!$att) {
            $att = new \StdClass();
            //Creates an empty attendance, for registering the log, if log isn't valid, delete the attendance.
            $id = $DB->get_field('hybridteaching_attendance', 'id',
                ['id' => self::hybridteaching_set_attendance($hybridteaching, $session, 1, -1)], MUST_EXIST);
            $att->id = $id;
        }
        $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        $log = self::hybridteaching_get_last_attend($att->id, $USER->id);
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
     * Retrieves the attendance logs for a given attendance ID.
     *
     * @param int $attid The ID of the attendance.
     * @throws \Throwable If there is an error accessing the database.
     * @return array An array of attendance log records.
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
    public static function hybridteaching_get_last_attend($attid, $userid)  {
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
            }return 0;
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
    public static function hybridteaching_get_needed_time($timevalue, $timeunit, $sesstime = 0) : int {
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
        return $neededtime;
    }

    /**
     * Generates a string containing session information for printing.
     *
     * @param mixed $session The session object.
     * @return string The string containing session information.
     */
    public static function hybridteaching_print_session_info($session) : String {
        if (!$session) {
            return '<h2>' . get_string('allsessions', 'hybridteaching') . '</h2>';
        }
        $s = '<br>';
        $s .= $session->name;
        $s .= '<br>' . date('l, j \d\e F \d\e Y H:i', $session->starttime).'<br>';
        $session->groupid == 0 ? $s .= get_string('commonattendance', 'hybridteaching') : $s .= groups_get_group($session->groupid)->name;
        $session->typevc != '' ?  $s .= '<br>' . $session->typevc : $s .= '';
        $s .= '<br>' . get_string('sessionstarttime', 'hybridteaching') . ': ' . date('H:i', $session->starttime);
        ($session->starttime + $session->duration) == $session->starttime ?
            $s .= '<br>' . get_string('sessionendtime', 'hybridteaching') . ': ' .  get_string('noduration', 'hybridteaching'):
            $s .= '<br>' . get_string('sessionendtime', 'hybridteaching') . ': ' . date('H:i', $session->starttime + $session->duration);
        $s .= '<br>' . get_string('duration', 'mod_hybridteaching') . ': '; 
        !empty($session->duration) ? $s .= helper::get_hours_format($session->duration) : $s .= ' - ';
        return $s;
    }

    public static function hybridteaching_print_attendance_for_user($hid, $user) : String {
        global $DB, $OUTPUT;

        if (!$hid || !$user) {
            return '<h2>' . get_string('notattendance', 'hybridteaching') . '</h2>';
        }
        $userid = $user->id;
        $grades = new grades();
        $ht = $DB->get_record('hybridteaching', ['id' => $hid], '*');
        $total = $DB->count_records('hybridteaching_attendance', ['hybridteachingid' => $hid, 'userid' => $userid]);
        $notattended = $DB->count_records('hybridteaching_attendance', ['hybridteachingid' => $hid, 'userid' => $userid, 'connectiontime' => 0]);
        $validated = $DB->count_records('hybridteaching_attendance', ['hybridteachingid' => $hid, 'userid' => $userid, 'status' => 1]);
        $exempted = $DB->count_records('hybridteaching_attendance', ['hybridteachingid' => $hid, 'userid' => $userid, 'status' => 3]);
        $finalgrade = $grades->get_instance_grade_for($ht, $userid);
        $s = $OUTPUT->user_picture($user) . $user->lastname . ' ' . $user->firstname . '<br>';
        $s .= get_string('attendedsessions', 'hybridteaching') . ': ';
        $s .= $total - $notattended;
        $s .= '/';
        $s .= $total;
        $s .= '<br>' . get_string('exempt', 'hybridteaching') . ': ' . $exempted;
        $s .= '<br>' . get_string('validatedattendance', 'hybridteaching') . ': ' . $validated . '/' . $total;
        $s .= '<br>' . get_string('finalgrade', 'hybridteaching') . ': ' . round($finalgrade, 2) . '/' . $ht->grade;
        return $s;
    }

    /**
     * Retrieves attendance users in a session.
     *
     * @param int $sessionid The ID of the session.
     * @throws \Throwable When there is an error executing the SQL query.
     * @return array An array of records containing the attendance users.
     */
    public static function hybridteaching_get_attendance_users_in_session($sessionid) {
        global $DB;
        $sql = "SELECT att.userid, att.id, u.firstname, u.lastname
                  FROM {hybridteaching_attendance} att
                  JOIN {hybridteaching_session} s
                    ON (att.sessionid = s.id)
                  JOIN {user} u
                    ON (u.id = att.userid)
                 WHERE s.id = :sessionid
              ORDER BY u.lastname ASC";
        $params = ['sessionid' => $sessionid];
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
            if (!$entrytime = $DB->get_record_sql($sqlentry, $params)->entrytime) {
                $entrytime = 0;
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
     * Retrieves the attendance records for a specific user.
     *
     * @param object $att The attendance object containing the user ID.
     * @throws \Throwable Throws an exception if there is an error with the database query.
     * @return array Returns an array of attendance records for the user.
     */
    public static function hybridteaching_get_instance_users($hid) {
        global $DB;

        if (!$hid) {
            return false;
        }
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname
                  FROM {hybridteaching_attendance} att
                  JOIN {user} u
                    ON (u.id = att.userid)
                 WHERE att.hybridteachingid = :hid
              ORDER BY u.lastname ASC";
        $params = ['hid' => $hid];
        try {
            return $DB->get_records_sql($sql, $params);
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    /**
     * Retrieves the students' participation data for a given hybrid teaching session.
     *
     * @param string $hid (optional) The hybrid teaching session ID. If not provided, the ID of the current hybrid object will be used.
     * @throws \Throwable If there is an error executing the SQL query.
     * @return array Returns an associative array containing the students' participation data. The array is indexed by the user ID and each element contains the total number of participations, the number of participations in classroom sessions, and the number of participations in virtual sessions.
     */
    public static function hybridteaching_get_students_participation($hid = '', $sort = 'id', $dir = 'asc', $fname = '', $lname = '') {
        global $DB;

        if (!$hid) {
            return false;
        }
        $sortd = [0 => 'total', 1 => 'vc' , 2 => 'classroom', 3 => 'lastname'];
        !in_array($sort, $sortd) ? $prefix = 'at.' : $prefix = '';
        !empty($fname) ? $fname = " AND u.firstname like '" . $fname . "%' " : ''; 
        !empty($lname) ? $lname = " AND u.lastname like '" . $lname . "%' " : ''; 
        $sql = "SELECT userid, u.lastname,
                         COUNT(type) AS total,
                      SUM(CASE
                         WHEN type THEN 0
                         ELSE 1
                      END) AS classroom,
                      SUM(CASE
                         WHEN type THEN 1
                         ELSE 0
                      END) AS vc
                  FROM
                       {hybridteaching_attendance} at
                  JOIN
                       {hybridteaching_session} s ON (at.sessionid = s.id)
                  JOIN {user} u ON (u.id = at.userid)
                 WHERE at.hybridteachingid = :hid
                   AND at.connectiontime != 0
                   AND at.visible = 1" .
                    $fname . $lname . "
              GROUP BY at.userid
              ORDER BY " . $prefix . $sort . " " . $dir;
        $param = ['hid' => $hid];
        try {
            return $DB->get_records_sql($sql, $param);
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
            $updateatt = 1;
            $att = $DB->get_record('hybridteaching_attendance', ['id' => $attid]);
            $att->timemodified = $timemodified;
            $att->usermodified = $USER->id; 
            switch ($data->operation) {
                case self::ACTIVE:
                    $att->status == 1 ? $updateatt = 0: $att->status = 1;
                    break;
                case self::INACTIVE:
                    $att->status == 0 ? $updateatt = 0: $att->status = 0;
                    break;
                case self::EXEMPT:
                    $att->exempt == 1 ? $updateatt = 0 : $att->exempt = 1;
                    $att->status = 3;
                    break;
                case self::NOTEXEMPT:
                    $att->exempt == 0 ? $updateatt = 0 : $att->exempt = 0;
                    if ($DB->update_record('hybridteaching_attendance', $att)) {
                        $timeneeded = $DB->get_record('hybridteaching_session', ['id' => $att->sessionid], 'duration')->duration;
                        $timespent = self::get_user_timespent($attid);
                        $att->status = self::verify_user_attendance(0, 0, $attid, $timeneeded, $timespent);
                        $updateatt = 1;
                    }
                    break;
            }
            if ($updateatt) {
                if (!$DB->update_record('hybridteaching_attendance', $att)) {
                    $errormsg = 'errorupdateattendance';
                } else {
                    $course = $DB->get_field('hybridteaching', 'course', ['id' => $att->hybridteachingid]);
                    $event = \mod_hybridteaching\event\attendance_updated::create(array(
                        'objectid' => $att->hybridteachingid,
                        'context' => \context_course::instance($course),
                        'other' => array(
                            'sessid' => $att->sessionid,
                            'userid' => $att->userid,
                            'attid' => $att->id
                        )
                    ));
            
                    $event->trigger();
                }
            }
        }

        return $errormsg;
    }

    public function update_session_exempt($attid, $action) {
        global $DB, $USER;

        $sessid = $DB->get_record('hybridteaching_attendance', ['id' => $attid], 'sessionid', IGNORE_MISSING)->sessionid;
        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid], 'id');
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        $session->attexempt = $action;
        $session->modifiedby = $USER->id;
        $session->timemodified = $timemodified; 
        $DB->update_record('hybridteaching_session', $session);
    }

    public function update_multiple_sessions_exempt($sessionids, $data) {
        global $DB, $USER;
        $errormsg = '';
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        foreach ($sessionids as $sessid) {
            $updateatt = 1;
            $session = $DB->get_record('hybridteaching_session', ['id' => $sessid], '*');
            $session->timemodified = $timemodified;
            $session->modifiedby = $USER->id;
            switch ($data->operation) {
                case self::SESSIONEXEMPT:
                    $session->attexempt == 1 ? $updateatt = 0: $session->attexempt = 1;
                    break;
                case self::NOTSESSIONEXEMPT:
                    $session->attexempt == 0 ? $updateatt = 0: $session->attexempt = 0;
                    break;
            }
            if ($updateatt) {
                if (!$DB->update_record('hybridteaching_session', $session)) {
                    $errormsg = 'errorupdateattendance';
                }
                $attends = $DB->get_records('hybridteaching_attendance', ['sessionid' => $sessid], '', 'id');
                foreach ($attends as $att) {
                    $session->attexempt ? $att->visible = 0 : $att->visible = 1;
                    $DB->update_record('hybridteaching_attendance', $att);
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
    public static function get_user_timespent($attid) : int {
        global $DB;

        $attlogs = $DB->get_records('hybridteaching_attend_log', ['attendanceid' => $attid], 'id asc', '*');
        $timespent = 0;
        foreach($attlogs as $log) {
            $nextlog = next($attlogs);
            if ($log->action) {
                if ($nextlog) {
                    $periodspent = $nextlog->timecreated - $log->timecreated;
                    $timespent += $periodspent;
                }
            }
        }
        return $timespent;
    }

    /**
     * @return int return status to set for the user attendance.
     */
    public static function verify_user_attendance($hybridteaching, $session, $attid, $timeneeded = 0, $timespent = 0) : int  {
        global $DB;

        $timeremaining = $timeneeded - $timespent;
        $att = self::hybridteaching_get_attendance_from_id($attid);
        if ($att->exempt) {
            return 3;
        }
        if ($timeremaining > 0) {
            return 0;
        }
        //con restraso
        if (!self::get_grace_period($hybridteaching, $session, $att->id)) {
            return 2;
        }
        // asistencia completa.
        return 1;
    }

    /**
     * @return bool Returns if the user attendance entry was on time.
     */
    public static function get_grace_period($ht, $session, $attid) : bool {
        global $DB;
        $intime = true;

        $gracetime = self::hybridteaching_get_needed_time($ht->graceperiod, $ht->graceperiodunit);
        if (!$gracetime) {
            return true;
        }
        $graceend = $session->starttime + $gracetime;
        $sql = "SELECT timecreated
                     FROM {hybridteaching_attend_log}
                    WHERE attendanceid = :attid
                 ORDER BY timecreated ASC
                    LIMIT 1";
        $params = ['attid' => $attid];
        $log = $DB->get_record_sql($sql, $params, IGNORE_MISSING);
        $log ? $logtime = $log->timecreated : $logtime = 0;
        $logtime > $graceend ? $intime = false : true;
        return $intime;
    }

}
