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
global $CFG;

require_once('common_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');

class sessions_controller extends common_controller {
    const MINUTE_TIMETYPE = 1;
    const HOUR_TIMETYPE = 2;

    /**
     * Constructs a new instance of the class.
     *
     * @param stdClass|null $hybridobject An object of stdClass or null.
     * @param string|null $table The table name or null.
     * @throws Some_Exception_Class Description of exception.
     * @return void
     */
    public function __construct(stdClass $hybridobject = null, string $table = null) {
        parent::__construct($hybridobject, $table);
        if (!empty($hybridobject->typevc)) {
            $this->require_subplugin_session($hybridobject->typevc);
        }
    }

    /**
     * Retrieves a list of sessions for the current hybrid teaching module.
     *
     * @param int $page page number to display.
     * @param int $recordsperpage number of records to display per page.
     * @param array $params optional parameters to filter the query results.
     * @param string $operator comparison operator to use in the WHERE clause.
     * @return array An array of session objects.
     */
    public function load_sessions($page = 0, $recordsperpage = 0, $params = [], $operator = self::OPERATOR_GREATER_THAN) {
        global $DB;
        $where = '';
        $params = array_merge(['hybridteachingid' => $this->hybridobject->id], $params);

        if (!empty($params['starttime'])) {
            $where .= ' AND starttime '.$operator.' :starttime';
        }
        
        $sql = 'SELECT * 
                  FROM {hybridteaching_session} 
                 WHERE hybridteachingid = :hybridteachingid ' . $where . '
                 ORDER BY visible DESC, id';

        $sessions = $DB->get_records_sql($sql, $params, $page, $recordsperpage);
        $sessionsarray = json_decode(json_encode($sessions), true);
        return $sessionsarray;
    }

    /**
     * Creates a session for the hybrid teaching object.
     *
     * @param object $data An object containing the data for the new session.
     *                     It should contain a hybridteachingid, instance,
     *                     duration, and timetype property.
     * @throws Exception If the function cannot create a session.
     * @return void
     */
    public function create_session($data) {
        $data->hybridteachingid = $this->hybridobject->id;
        $data->instance = $this->hybridobject->instance;
        $data->duration = sessions_controller::calculate_duration($data->duration, $data->timetype);
        $data->userecordvc=$this->hybridobject->userecordvc;
        if ($data->userecordvc==1){
            $data->processedrecording=-1;
        }
        if (isset($data->addmultiply)) {
            $this->create_multiple_sessions($data);
        } else {
            $this->create_unique_session($data);
        }
    }

    /**
     * Creates a new session with a unique ID and saves it to the database.
     *
     * @param array $data An array of session data to be saved.
     * @throws Exception If there is an error inserting the session into the database.
     */
    public function create_unique_session($data) {
        global $DB;

        $session = $this->fill_session_data_for_create($data);
        $session->id = $DB->insert_record('hybridteaching_session', $session); 
        if (!empty($session->descrip)) {
            $description = file_save_draft_area_files($session->descrip['itemid'],
            $session->context->id, 'mod_hybridteaching', 'session', $session->id,
                array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
                $session->descrip['text']);
            $DB->set_field('hybridteaching_session', 'description', $description, array('id' => $session->id));
        }
        $session->htsession = $session->id;
        
        if (!empty($this->hybridobject->typevc) && $this->hybridobject->usevideoconference) {
            $subpluginsession = new sessions();
            $subpluginsession->create_unique_session_extended($session);
        }
    }

    /**
     * Create multiple sessions based on given data.
     *
     * @param mixed $data The data to create sessions from.
     * @throws null
     * @return null
     */
    public function create_multiple_sessions($data) {
        global $CFG;

        $startdate = $data->starttime;
        $enddate = $data->sessionenddate + DAYSECS; // Because enddate in 0:0am.

        if ($enddate < $startdate) {
            return null;
        }

        // Getting first day of week.
        $sdate = $startdate;
        $dinfo = usergetdate($sdate);
        if ($CFG->calendar_startwday === '0') { // Week start from sunday.
            $startweek = $startdate - $dinfo['wday'] * DAYSECS; // Call new variable.
        } else {
            $wday = $dinfo['wday'] === 0 ? 7 : $dinfo['wday'];
            $startweek = $startdate - ($wday - 1) * DAYSECS;
        }

        $wdaydesc = array(0 => 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

        list($hour, $minutes) = $this->get_hour_and_minutes($data->starttime);
        while ($sdate < $enddate) {
            if ($sdate < strtotime('+1 week', $startweek)) {
                $dinfo = usergetdate($sdate);
                if (isset($data->sdays) && array_key_exists($wdaydesc[$dinfo['wday']], $data->sdays)) {
                    $data->starttime = make_timestamp($dinfo['year'], $dinfo['mon'], $dinfo['mday'],
                        $hour, $minutes);
                    $this->create_unique_session($data);
                }

                $sdate = strtotime("+1 day", $sdate); // Set start to tomorrow.
            } else {
                $startweek = strtotime("+".$data->period.' weeks', $startweek);
                $sdate = $startweek;
            }
        }
    }

    /**
     * Updates a session in the hybridteaching_session table.
     *
     * @param object $data The session data to update.
     * @throws Exception If there is an error updating the session.
     * @return string Returns an error message if there is an error updating the session, otherwise, returns an empty string.
     */
    public function update_session($data) {
        global $DB, $USER;
        $errormsg = '';
        $session = $this->fill_session_data_for_update($data);
        if (!$DB->update_record('hybridteaching_session', $session)) {
            $errormsg = 'errorupdatesession';
        }

        $sessiontype = $DB->get_field('hybridteaching_session', 'typevc', array('id' => $session->id));
        if (!empty($sessiontype)) {
            $subpluginsession = new sessions();
            $subpluginsession->update_session_extended($session);
        }

        return $errormsg;
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
        $sessiontype = $DB->get_field('hybridteaching_session', 'typevc', ['id' => $id]);
        if (!$DB->delete_records('hybridteaching_session', ['id' => $id])) {
            $errormsg = 'errordeletesession';
        }
        if (!empty($sessiontype)) {
            // Check if the plugin exists
            if (helper::subplugin_instance_exists($this->hybridobject->instance)){
                require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$this->hybridobject->instance.'/classes/sessions.php');
                $subpluginsession = new sessions();
                $subpluginsession->delete_session_extended($id, $this->hybridobject->instance);
            }
        }
        return $errormsg;
    }

    /**
     * Deletes all sessions associated with the given module instance.
     *
     * @param object $moduleinstance The module instance object
     * @throws Exception If an error occurs while deleting sessions
     */
    public function delete_all_sessions($moduleinstance) {
        global $DB;

        $sessiontype = $DB->get_field('hybridteaching_session', 'typevc', array('id' => $moduleinstance->id));
        if (!empty($sessiontype)) {
            $sessionsht = $DB->get_records('hybridteaching_session', array('hybridteachingid' => $moduleinstance->id), '', 'id');
            // Check if the plugin exists
            if (helper::subplugin_instance_exists($this->hybridobject->instance)){
                require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$moduleinstance->typevc.'/classes/sessions.php');
                $subpluginsession = new sessions();
                foreach ($sessionsht as $session) {
                    $subpluginsession->delete_all_sessions_extended($session, $this->hybridobject->instance);
                }
            }
        }

        $DB->delete_records('hybridteaching_session', array('hybridteachingid' => $moduleinstance->id));
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
        $sessiondata = $DB->get_record('hybridteaching_session', ['id' => $sessionid]);
        return $sessiondata;
    }

    /**
     * Retrieve the next session from the database using the given hybridteaching ID.
     *
     * @param int $htid
     * @throws Some_Exception_Class Description of the exception that can be thrown.
     * @return mixed The next session data.
     */
    public function get_next_session($htid){
        global $DB;
        
        $sql="SELECT * 
            FROM {hybridteaching_session} AS hs
            WHERE hs.hybridteachingid = :id AND (hs.starttime + hs.duration >= UNIX_TIMESTAMP() OR hs.starttime IS NULL) 
            ORDER BY hs.starttime LIMIT 1";
           
        $nextsession = $DB->get_record_sql($sql,['id'=> $htid]);

        /*if there not are next session, get the last session*/
        if (!$nextsession){
            $sql="SELECT * 
                FROM {hybridteaching_session} AS hs
                WHERE hs.hybridteachingid = :id
                ORDER BY hs.starttime DESC LIMIT 1";
            $nextsession = $DB->get_record_sql($sql,['id'=> $htid]);
        }

        return $nextsession;
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
    public function count_sessions($params = [], $operator = self::OPERATOR_GREATER_THAN) {
        return count($this->load_sessions(0, 0, $params, $operator));
    }

    /**
     * Calculates the duration in seconds based on the duration and timetype parameters.
     *
     * @param int $duration The duration in units specified by the timetype parameter.
     * @param int $timetype The type of time unit used to specify the duration (1 for minutes, 2 for hours).
     * @return int The duration in seconds.
     */
    public static function calculate_duration($duration, $timetype) {
        if ($timetype == self::MINUTE_TIMETYPE) {
            return $duration * 60;
        } elseif ($timetype == self::HOUR_TIMETYPE) {
            return $duration * 3600;
        } else {
            // Invalid timetype, return 0
            return 0;
        }
    }

    /**
     * Returns the hour and minutes of a given timestamp.
     *
     * @param int $timestamp Unix timestamp to get hour and minutes from.
     * @return array An array containing the hour and minutes in the format [hour, minutes].
     */
    function get_hour_and_minutes($timestamp) {
        $hour = date('H', $timestamp);
        $minutes = date('i', $timestamp);
        return [$hour, $minutes];
    }

    /**
     * Creates a new session object from the given data object, populating some of the fields
     * with default values. Returns the new session object.
     *
     * @param object $data The data object to create the session from.
     * @return object The new session object.
     */
    public function fill_session_data_for_create($data) {
        global $USER;

        $session = clone $data;
        $description = !empty($data->description) ? $data->description : '';
        $session->description = null;
        $session->descrip = $description;
        $session->visible = 1;
        $session->timecreated = time();
        $session->createdby = $USER->id;
        
        return $session;
    }

    public function fill_session_data_for_update($data) {
        global $USER;
        
        $session = clone $data;
        $session->duration = sessions_controller::calculate_duration($data->duration, $data->timetype);
        $description = !empty($data->description) ? $data->description : '';
        $session->description = null;
        $session->descrip = $description;
        $session->timemodified = time();
        $session->modifiedby = $USER->id;
        
        return $session;
    }

    /**
     * Includes the required subplugin session file for the specified typevc.
     *
     * @param string $typevc the type of VC to require the session file for
     */
    public function require_subplugin_session($typevc) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$typevc.'/classes/sessions.php');
    }
}
