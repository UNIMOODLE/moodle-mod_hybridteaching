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
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once(dirname(__FILE__).'../../../../../config.php');

class attendance_controller extends common_controller {

    const ACTIVE = 1;
    const INACTIVE = 2;
    const EXENT = 1;
    const NOTEXENT = 2;

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
          $operator = self::OPERATOR_GREATER_THAN, $sort = 'hs.name', $dir = 'ASC', $view = '', $sessionid = 0) {
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
            if ($params['view'] != 'extendedsessionatt' && $params['view'] != 'sessionattendance') {
                //$where .= 'AND ha.visible = 1 ';
             }
        } 
        if (isset($params['view'])) {
            $params['view'] == 'extendedstudentatt'  ? $altergroupby .= '  ha.userid ': $groupby = ' ha.sessionid ';
            $params['view'] == 'extendedsessionatt' ? $groupby = ' ha.id ' : '';
        } else {
            if (!empty($view)) {
                $view == 'extendedstudentatt' ? $altergroupby .= '  ha.userid ': $groupby = ' ha.sessionid ';
                $view == 'extendedsessionatt' ? $groupby = ' ha.id ' : '';
            } else {
                $groupby = ' ha.id ';
            }
        }
        
        if (!empty($extraselect)) {
            $where .= " AND $extraselect";
        }

        $sessionid != 0 ? $where .= ' AND sessionid = ' . $sessionid . '' : false;

        $sql = 'SELECT ha.id, ha.sessionid, ha.id, hs.name, hs.starttime, hs.duration, hs.typevc, ha.visible, ha.grade,
                            ha.userid, ha.type, ha.status, ha.connectiontime, (hs.starttime + hs.duration) endtime,
                            ha.exempt, u.lastname, u.username
                  FROM {hybridteaching_attendance} ha
                  JOIN {hybridteaching_session} hs on (ha.sessionid = hs.id)
                  JOIN {user} u on (u.id = ha.userid)
                 WHERE ha.hybridteachingid = :hybridteachingid ' . $where . '
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
                    WHERE ha.hybridteachingid = :hybridteachingid ' . $where . '
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

    public static function hybridteaching_set_attendance($hybridteaching, $session, int $status = 0, int $atttype = null, $userid = null) {
        global $DB, $USER;

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $att = self::hybridteaching_get_attendance($hybridteaching, $session);
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
        echo '<br>';
        $status = self::verify_user_attendance($att->id, $neededtime, $timespent);

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

            return true;
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    public static function hybridteaching_get_attendance($hybridteaching, $session, $userid = null) {
        global $DB, $USER;

        if (empty($userid)) {
            $userid = $USER->id;
        }
        is_integer($session) ? $sessid = $session : $sessid = false;
        if (!$sessid) {
            is_array($session) ? $sessid = $session['id'] : $sessid = $session->id;
        }
        $sql = "SELECT *
                  FROM {hybridteaching_attendance}
                 WHERE hybridteachingid = :hybridid
                   AND sessionid = :sessid
                   AND userid = :userid";

        $params = ['hybridid' => $hybridteaching->id, 'sessid' => $sessid,
             'userid' => $userid];
        return $DB->get_record_sql($sql, $params, IGNORE_MISSING);
    }

    public static function hybridteaching_get_attendance_from_id($attid = 1) {
        global $DB;
        return $DB->get_record('hybridteaching_attendance', ['id' => $attid], '*', MUST_EXIST);
    }

    /**
     * Counts the number of sessions for a given hybrid teaching object.
     *
     * @param array $params An optional array of parameters to filter the sessions by.
     *                      Defaults to an empty array.
     *                      - hybridteachingid: The ID of the hybrid teaching object to count sessions for.
     * @global moodle_database $DB The Moodle database object.
     * @throws Exception If the database query fails.
     * @return int The number of sessions for the given hybrid teaching object and parameters.
     */
    public function count_attendance($params = [], $operator = self::OPERATOR_GREATER_THAN) {
        return count($this->load_attendance(0, 0, $params, '', $operator));
    }

    public function count_sess_attendance($params = []) {
        global $DB;
        return count($DB->get_records('hybridteaching_attendance', $params, '', 'id'));
    }


    public static function hybridteaching_set_attendance_log($hybridteaching, $session, $time, $action) {
        global $DB, $USER;

        $att = self::hybridteaching_get_attendance($hybridteaching, $session);
        if (!$att) {
            //Creates an empty attendance, for registering the log, if log isn't valid, delete the attendance.
            $att = self::hybridteaching_get_attendance_from_id(
                self::hybridteaching_set_attendance($hybridteaching, $session, 1, -1) //The just created attendance id.
            );
        }
        $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();

        $start = $time['start'];$end = $time['end'];$timeneeded = $time['timeneeded'];$validateunit = $time['validateunit'];
        $sesstime = $end - $start;
        $neededtime = self::hybridteaching_get_needed_time($timeneeded, $validateunit);
        if ($neededtime > $sesstime) {
            return $notify = ['gstring' => 'bad_neededtime', 'ntype' => \core\output\notification::NOTIFY_ERROR];
        }
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

    public static function hybridteaching_get_attendance_logs($attid) {
        global $DB;
        $sql = "SELECT al.*
                  FROM {hybridteaching_attend_log} al
                  JOIN {hybridteaching_attendance} att
                    ON (al.attendanceid = att.id)
                 WHERE att.id = :attid
              ORDER BY al.timecreated ASC";
        $params = ['attid' => $attid];
        try {
            return $DB->get_records_sql($sql, $params);
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

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
    public static function hybridteaching_get_needed_time($timevalue, $timeunit) : int {

        switch ($timeunit) {
            case 3:
                $neededtime = $timevalue / 100;
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

    public static function hybridteaching_print_session_info($session) : String {
        if (!$session) {
            return '<h2>' . get_string('allsessions', 'hybridteaching') . '</h2>';
        }
        $s = '<br>';
        $s .= get_string('sessionsuc', 'hybridteaching') . ' ' . $session->name;
        $s .= '<br>' . date('l, j \d\e F \d\e Y H:i', $session->starttime).'<br>';
        $session->groupid == 0 ? $s .= get_string('commonattendance', 'hybridteaching') : $s .= groups_get_group($session->groupid)->name;
        $session->typevc != '' ?  $s .= '<br>' . $session->typevc : $s .= '';
        $s .= '<br>' . get_string('sessionstarttime', 'hybridteaching') . ' ' . date('H:i', $session->starttime);
        ($session->starttime + $session->duration) == $session->starttime ?
            $s .= '<br>' . get_string('sessionendtime', 'hybridteaching') . ' ' .  get_string('noduration', 'hybridteaching'):
            $s .= '<br>' . get_string('sessionendtime', 'hybridteaching') . ' ' . date('H:i', $session->starttime + $session->duration);
        $s .= '<br>' . get_string('duration', 'mod_hybridteaching') . ' '; 
        !empty($session->duration) ? $s .= helper::get_hours_format($session->duration) : $s .= ' - ';
        return $s;
    }

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
            $entrytime = '';
            $endtime = '';
            $action = '';
            $entryrecord = $DB->get_record_sql($sqlentry, $params);
            if ($entryrecord !== false) {
                $entrytime = $entryrecord->entrytime;
            }
            
            // Query for $endlog
            $endrecord = $DB->get_record_sql($sqlend, $params);
            if ($endrecord !== false) {
                $endtime = $endrecord->endtime;
                $action = $endrecord->action;
            }
            return ['entry' => $entrytime, 'end' => $endtime, 'lastaction' => $action];
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    public static function hybridteaching_get_user_attends($att) {
        global $DB;
        $sql = "SELECT att.*
                  FROM {hybridteaching_attendance} att
                 WHERE att.userid = :userid
              ORDER BY att.id ASC";
        $params = ['userid' => $att->userid];
        try {
            return $DB->get_records_sql($sql, $params);
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    public static function hybridteaching_get_students_participation($hid = '') {
        global $DB;

        !$hid ? $hid = $this->hybridobject->id : '' ;
        $sql = "SELECT userid,
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
                       mdl_hybridteaching_attendance at
                         JOIN
                       mdl_hybridteaching_session s ON (at.sessionid = s.id)
                 WHERE at.hybridteachingid = :hid
                   AND at.visible = 1
              GROUP BY at.userid";
        $param = ['hid' => $hid];
        try {
            return $DB->get_records_sql($sql, $param);
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

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

    public function visible_sessions($attid, $action) {
        global $DB, $USER;

        $sessid = $DB->get_record('hybridteaching_attendance', ['id' => $attid], 'sessionid', IGNORE_MISSING)->sessionid;
        $sessions = $DB->get_records_sql("SELECT id FROM {hybridteaching_attendance} WHERE sessionid = ?", ['sessionid' => $sessid]);
        $timemodified = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        foreach ($sessions as $session) {
            $session->visible = $action;
            $session->usermodified = $USER->id;
            $session->timemodified = $timemodified; 
            $DB->update_record('hybridteaching_attendance', $session);
        }
    }

    public static function get_user_timespent($attid) : int {
        global $DB;

        $attlogs = $DB->get_records('hybridteaching_attend_log', ['attendanceid' => $attid], 'id asc', '*');
        echo '<br>';
        $timespent = 0;
        foreach($attlogs as $log) {
            $nextlog = next($attlogs);
            echo '<br>';
            if ($log->action) {
                if ($nextlog) {
                    echo '<br>' . $log->id . ' ' . $nextlog->id . ' <br>';
                    echo '<br>' . $log->timecreated . ' ' . $nextlog->timecreated . ' <br>';
                    $periodspent = $nextlog->timecreated - $log->timecreated;
                    echo '<br>segundos usados: ' . $periodspent . '<br>';
                    $timespent += $periodspent;
                }
            }
        }
        echo '<br><b>Tiempo total: ' . $timespent . ' | ' . helper::get_hours_format($timespent) . '</b><br>';
        return $timespent;
    }

    /**
     * @return int return status to set for the user attendance.
     */
    public static function verify_user_attendance($attid, $timeneeded, $timespent) :mixed  {
        global $DB;

        $timeremaining = $timeneeded - $timespent;
        $att = self::hybridteaching_get_attendance_from_id($attid);
        if ($att->exempt) {
            return 3;
        }
        var_dump($timeremaining);
        if ($timeremaining > 0) {
            return 0;
        }
        //con restraso

        // return 2;

        // asistencia completa 

        return 1;
    }
    
}
