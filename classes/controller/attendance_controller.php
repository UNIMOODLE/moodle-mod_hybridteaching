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

class attendance_controller extends common_controller{


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
        global $DB;
        $where = '';
        $extragroupby = '';
        $params = $params + ['hybridteachingid' => $this->hybridobject->id];

        if (!empty($params['starttime'])) {
            $where .= ' AND starttime + duration '.$operator.' :starttime';
        }
        if (!empty($params['userid'])) {
            $userid = $params['userid'];
            $where .= 'AND userid = ' . $userid . '';
        } 
        $view != 'sessionattendance' ? $extragroupby .= ', ha.userid ':
        false;

        if (!empty($extraselect)) {
            $where .= " AND $extraselect";
        }

        $sessionid != 0 ? $where .= ' AND sessionid = ' . $sessionid . '' : false;

        $sql = 'SELECT ha.id, ha.sessionid, ha.id, hs.name, hs.starttime, hs.duration, hs.typevc, ha.visible, ha.grade,
                         ha.userid, ha.type, ha.status
                  FROM {hybridteaching_attendance} ha
                  JOIN {hybridteaching_session} hs on (ha.sessionid = hs.id)
                 WHERE ha.hybridteachingid = :hybridteachingid ' . $where . '
              GROUP BY ha.sessionid, hs.groupid ' . $extragroupby . 'ORDER BY ' . $sort . ' ' . $dir;

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
            $sql = 'SELECT 
                        sessionid,
                        sum(CASE WHEN type THEN 0 ELSE 1 END) AS classroom,
                        sum(CASE WHEN type THEN 1 ELSE 0 END) AS vc
                        FROM {hybridteaching_session} hs
                        JOIN {hybridteaching_attendance} ha ON ha.sessionid = hs.id
                        GROUP BY ha.sessionid';
    
            $attendance = $DB->get_records_sql($sql, $params, $page, $recordsperpage);
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
            $userid = $attendancelist['userid'];
            $params = ['hybridteachingid' => $this->hybridobject->id];
            $sql = 'SELECT 
                            *
                            FROM {user}
                            WHERE id =' . $userid . '';
           return $attendance = $DB->get_record_sql($sql, $params);
            return json_decode(json_encode($attendance), true);;
    }
    public function view_attendance($attendanceid, $moduleid, $sessionid, $viewall, $userid = null) {
        var_dump($attendanceid, $moduleid, $sessionid, $viewall,$userid);
    }

    public static function hybridteaching_set_attendance($hybridteaching, $session, int $status) {
        global $DB, $USER;

        $att = self::hybridteaching_get_attendance($hybridteaching, $session);
        $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        $hybridteaching->usevideoconference == 0 ? $type = 0 : $type = 1;
        if (!$att) {
            $att = new \StdClass();
            $att->hybridteachingid = $hybridteaching->id;
            $att->sessionid = $session->id;
            $att->userid = $USER->id;
            $att->status = $status;
            $att->type = $type;
            $att->usermodified = $USER->id;
            $att->timecreated = $timenow;
            $att->timemodified = $timenow;
            try {
                $DB->insert_record('hybridteaching_attendance', $att);
                return true;
            } catch (\Throwable $dbexception) {
                throw $dbexception;
            }
        }
        $att->status = $status;
        $att->type = $type;
        $att->usermodified = $USER->id;
        $att->timemodified = $timenow;
        try {
            $DB->update_record('hybridteaching_attendance', $att);
            return true;
        } catch (\Throwable $dbexception) {
            throw $dbexception;
        }
    }

    public static function hybridteaching_get_attendance($hybridteaching, $session) {
        global $DB, $USER;
        $sql = "SELECT * FROM {hybridteaching_attendance} WHERE hybridteachingid = :hybridid
                          AND sessionid = :sessid AND userid = :userid";
        $params = ['hybridid' => $hybridteaching->id, 'sessid' => $session->id, 'userid' => $USER->id];
        return $DB->get_record_sql($sql, $params, IGNORE_MISSING);
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
}
