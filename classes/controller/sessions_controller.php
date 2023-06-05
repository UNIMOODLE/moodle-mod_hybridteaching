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
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('common_controller.php');

class sessions_controller extends common_controller {
    /**
     * Loads sessions from the database showing a number of records per page.
     *
     * @param int $page The page number to load sessions from. Default is 0.
     * @param int $recordsperpage The number of records to load per page. Default is 0.
     * @return array An array of session records.
     */
    public function load_sessions($page = 0, $recordsperpage = 0, $params = [], $operator = self::OPERATOR_GREATER_THAN) {
        global $DB;
        $where = '';
        $params = array_merge(['hybridteachingid' => $this->hybridobject->id], $params);

        if (!empty($params['starttime'])) {
            $where .= ' AND starttime '.$operator.' :starttime';
        }
        
        $sql = 'SELECT * 
                  FROM {'.$this->table.'} 
                 WHERE hybridteachingid = :hybridteachingid ' . $where . '
                 ORDER BY visible DESC, id';

        $sessions = $DB->get_records_sql($sql, $params, $page, $recordsperpage);
        $sessionsarray = json_decode(json_encode($sessions), true);
        return $sessionsarray;
    }

    /**
     * Deletes a session record from the database.
     *
     * @param int $id The id of the session to be deleted.
     * @return string Error message if deletion fails.
     */
    public function delete_session($id) {
        global $DB;
        $errormsg = '';
        $id = ['id' => $id];
        if (!$DB->delete_records($this->table, $id)) {
            $errormsg = 'errordeletesession';
        }
        return $errormsg;
    }

    /**
     * Retrieve the session data from the database using the given session ID.
     *
     * @param int $sessionid The ID of the session to retrieve.
     * @throws Some_Exception_Class Description of the exception that can be thrown.
     * @return mixed The session data.
     */
    public function get_session($sessionid) {
        global $DB;
        $sessiondata = $DB->get_record($this->table, ['id' => $sessionid]);
        return $sessiondata;
    }

    /**
     * Counts the number of sessions in the database table associated with the HybridTeaching object.
     *
     * @return int The number of sessions.
     */
    public function count_sessions($params = []) {
        global $DB;
        $params = array_merge(['hybridteachingid' => $this->hybridobject->id], $params);
        return $DB->count_records($this->table, $params);
    }
}
