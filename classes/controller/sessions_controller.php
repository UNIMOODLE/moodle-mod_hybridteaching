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
    const EQUAL = 1;
    const ADD = 2;
    const REDUCE = 3;
    protected $existssubplugin = false;

    /**
     * Constructs a new instance of the class.
     *
     * @param stdClass|null $hybridobject An object of stdClass or null.
     * @param string|null $table The table name or null.
     * @throws Some_Exception_Class Description of exception.
     * @return void
     */
    public function __construct(stdClass $hybridobject = null) {
        parent::__construct($hybridobject);
        if (!empty($hybridobject->typevc) && helper::subplugin_config_exists($hybridobject->config)) {
            $this->existssubplugin = true;
            self::require_subplugin_session($hybridobject->typevc);
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
    public function load_sessions($page = 0, $recordsperpage = 0, $params = [], $extraselect = '',
          $operator = self::OPERATOR_GREATER_THAN, $sort = 'starttime', $dir = 'ASC') {
        global $DB;
        $where = '';
        $params = $params + ['hybridteachingid' => $this->hybridobject->id];

        if (!empty($params['starttime'])) {
            $where .= ' AND starttime + duration '.$operator.' :starttime';
        }

        if (!empty($extraselect)) {
            $where .= " AND $extraselect";
        }

        $sql = 'SELECT *
                  FROM {hybridteaching_session}
                 WHERE hybridteachingid = :hybridteachingid ' . $where . '
              ORDER BY visible DESC, ' . $sort . ' ' . $dir;

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
        $session = null;
        $data->hybridteachingid = $this->hybridobject->id;
        $data->config = $this->hybridobject->config;
        $data->duration = self::calculate_duration($data->duration, $data->timetype);
        $data->userecordvc = $this->hybridobject->userecordvc;
        if ($data->userecordvc == 1) {
            $data->processedrecording =- 1;
        }

        $multiplesess = false;
        if (isset($data->addmultiply)) {
            $this->create_multiple_sessions($data);
            $multiplesess = true;
        } else {
            $session = $this->create_unique_session($data);
        }

        $event = \mod_hybridteaching\event\session_added::create(array(
            'objectid' => $this->hybridobject->id,
            'context' => \context_course::instance($this->hybridobject->course),
            'other' => array(
                'multiplesess' => $multiplesess,
                'sessid' => !empty($session) ? $session->id : null
            )
        ));

        $event->trigger();

        return $session;
    }

    /**
     * Creates a new session with a unique ID and saves it to the database.
     *
     * @param array $data An array of session data to be saved.
     * @throws Exception If there is an error inserting the session into the database.
     */
    public function create_unique_session($session) {
        global $DB;

        $session = $this->fill_session_data_for_create($session);
        $session->id = $DB->insert_record('hybridteaching_session', $session);
        if (!empty($session->description)) {
            $description = file_save_draft_area_files($session->descriptionitemid,
            $session->context->id, 'mod_hybridteaching', 'session', $session->id,
                array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
                $session->description);
            $DB->set_field('hybridteaching_session', 'description', $description, array('id' => $session->id));
        }
        $session->htsession = $session->id;

        return $session;
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
        global $DB;
        $errormsg = '';
        $session = $this->fill_session_data_for_update($data);
        $session->duration = self::calculate_duration($data->duration, $data->timetype);
        if (!$DB->update_record('hybridteaching_session', $session)) {
            $errormsg = 'errorupdatesession';
        }

        $session = $DB->get_record('hybridteaching_session', ['id' => $session->id]);
        if (!empty($session->typevc)) {
            $classname = $this->get_subplugin_class($session->typevc);
            $subpluginsession = new $classname();
            $subpluginsession->update_session_extended($session, $this->hybridobject);
        }

        return $errormsg;
    }

    /**
     * Updates multiple sessions.
     *
     * @param array $sessids The array of session IDs.
     * @param mixed $data The data to be updated.
     * @return string The error message, if any.
     */
    public function update_multiple_session($sessids, $data) {
        global $DB;
        $errormsg = '';

        foreach ($sessids as $sessid) {
            $session = $DB->get_record('hybridteaching_session', ['id' => $sessid]); 
            if (!empty($data->duration) && !empty($data->timetype)) {
                switch ($data->operation) {
                    case self::EQUAL:
                        $session->duration = self::calculate_duration($data->duration, $data->timetype);
                        break;
                    case self::ADD:
                        $session->duration = $session->duration +
                            self::calculate_duration($data->duration, $data->timetype);
                        break;
                    case self::REDUCE:
                        $session->duration = $session->duration -
                            self::calculate_duration($data->duration, $data->timetype);
                        break;
                }
            } 
            
            if (!empty($data->starttime) && !empty($data->timetype)) {
                switch ($data->operation) {
                    case self::REDUCE:
                        $session->starttime = $session->starttime -
                            self::calculate_duration($data->starttime, $data->timetype);
                        break;
                    case self::ADD:
                        $session->starttime = $session->starttime +
                            self::calculate_duration($data->starttime, $data->timetype);
                        break;
                }
            }

            if (!$DB->update_record('hybridteaching_session', $session)) {
                $errormsg = 'errorupdatesession';
            }

            if (!empty($session->typevc)) {
                $classname = $this->get_subplugin_class($session->typevc);
                $subpluginsession = new $classname();             
                $subpluginsession->update_session_extended($session, $this->hybridobject);
            }
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
        $sessiondata = $this->get_session($id);
        if (time() >= $sessiondata->starttime && time() < ($sessiondata->starttime + $sessiondata->duration)) {
            $$errormsg = 'errordsinprogress';
        } else {
            if (!$DB->delete_records('hybridteaching_session', ['id' => $id])) {
                $errormsg = 'errordeletesession';
            }
            if (!empty($sessiondata->typevc) && $this->existssubplugin) {
                $classname = $this->get_subplugin_class($sessiondata->typevc);
                $subpluginsession = new $classname();
                $subpluginsession->delete_session_extended($id, $this->hybridobject->config);
            }
        }

        $event = \mod_hybridteaching\event\session_deleted::create(array(
            'objectid' => $this->hybridobject->id,
            'context' => \context_course::instance($this->hybridobject->course),
            'other' => array(
                'sessid' => $id
            )
        ));

        $event->trigger();
        
        return $errormsg;
    }

    /**
     * Deletes all sessions associated with the given module instance.
     *
     * @param object $moduleinstance The module instance object
     * @throws Exception If an error occurs while deleting sessions
     */
    public function delete_all_sessions() {
        global $DB;

        $sessiontype = $DB->get_field('hybridteaching_session', 'typevc', array('id' => $this->hybridobject->id));
        if (!empty($sessiontype) && $this->existssubplugin) {
            $sessionsht = $DB->get_records('hybridteaching_session', 
                array('hybridteachingid' => $this->hybridobject->id), '', 'id');
            $classname = $this->get_subplugin_class($sessiontype);
            $subpluginsession = new $classname();
            foreach ($sessionsht as $session) {
                $subpluginsession->delete_session_extended($session, $this->hybridobject->config);
            }
        }

        $DB->delete_records('hybridteaching_session', array('hybridteachingid' => $this->hybridobject->id));
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
    public function get_next_session() {
        global $DB;

        $datefilter = "";
        if (empty($this->hybridobject->undatedsession)) {
            $datefilter  = " AND (hs.starttime + hs.duration >= UNIX_TIMESTAMP() OR hs.starttime IS NULL)";
        }

        $sql = "SELECT *
                  FROM {hybridteaching_session} AS hs
                 WHERE hs.hybridteachingid = :id 
                   $datefilter
                   AND (hs.isfinished = 0 OR hs.isfinished IS NULL)
              ORDER BY hs.starttime LIMIT 1";

        $nextsession = $DB->get_record_sql($sql, ['id' => $this->hybridobject->id]);

        return $nextsession;
    }

    /**
     * Retrieves the last session from the hybrid teaching session table based on the provided hybrid teaching ID.
     *
     * @param int $htid The ID of the hybrid teaching.
     * @global object $DB The global database object.
     * @throws None
     * @return mixed The last session record from the hybrid teaching session table.
     */
    public function get_last_session() {
        global $DB;

        $sql = "SELECT *
                  FROM {hybridteaching_session} AS hs
                 WHERE hs.hybridteachingid = :id 
                   AND hs.starttime < UNIX_TIMESTAMP()
                   AND hs.isfinished = 1
              ORDER BY hs.starttime DESC LIMIT 1";
        $lastsession = $DB->get_record_sql($sql, ['id' => $this->hybridobject->id]);

        return $lastsession;
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
        return count($this->load_sessions(0, 0, $params, '', $operator));
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
            return $duration * MINSECS;
        } else if ($timetype == self::HOUR_TIMETYPE) {
            return $duration * HOURSECS;
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
    public function get_hour_and_minutes($timestamp) {
        $hour = date('H', $timestamp);
        $minutes = date('i', $timestamp);
        return [$hour, $minutes];
    }

    /**
     * Fills the session data with the provided data.
     *
     * @param mixed $data The data to fill the session with.
     * @return mixed The session object with the filled data.
     */
    public function fill_session_data($data) {
        $session = clone $data;
        if (!empty($data->description["text"])) {
            $session->descriptionitemid = $data->description['itemid'];
            $session->description = $data->description['text'];
            $session->descriptionformat = $data->description['format'];
        } else {
            $session->description = '';
            $session->descriptionformat = 0;
        }
        
        return $session;
    }

    /**
     * Fills session data for create.
     *
     * @param mixed $data The data to fill the session with.
     * @return mixed The filled session.
     */
    public function fill_session_data_for_create($data) {
        global $USER;

        $session = $this->fill_session_data($data);
        $session->visible = 1;
        $session->timecreated = time();
        $session->createdby = $USER->id;
        
        return $session;
    }

    /**
     * Fills the session data for update.
     *
     * @param mixed $data The data to fill the session with.
     * @global object $USER The global user object.
     * @return object The updated session object.
     */
    public function fill_session_data_for_update($data) {
        global $USER;

        $session = $this->fill_session_data($data);
        $session->timemodified = time();
        $session->modifiedby = $USER->id;

        return $session;
    }

    /**
     * Includes the required subplugin session file for the specified typevc.
     *
     * @param string $typevc the type of VC to require the session file for
     */
    public static function require_subplugin_session($typevc) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$typevc.'/classes/sessions.php');
    }

    public static function get_subplugin_class($typevc) {
        $classname = '\hybridteachvc_' . $typevc . '\sessions';
        return $classname;
    }

    /**
    * Save session storage config id
    *
    * @param object The session object to calculate to save.
    */
    public static function savestorage($session){

        //FALTA CALCULAR CON LA SELECCION DE CURSOS DE LA CONFIGURACIÓN

        //calculate storage
        global $DB;
        $sql= 'SELECT id FROM {hybridteaching_configs}
            WHERE subplugintype=:type AND visible=1
            ORDER BY sortorder, id
            LIMIT 1';
        $record=$DB->get_record_sql($sql,['type' => 'storage']);

        //save storage in session
        if ($record){
            $session->storagereference=$record->id;
            $DB->update_record('hybridteaching_session',$session);
        }
    }
}
