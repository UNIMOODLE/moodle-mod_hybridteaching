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

use stdClass;
use mod_hybridteaching\helper;
use mod_hybridteaching\helpers\calendar_helpers;

/**
 * Class sessions_controller
 */
class sessions_controller extends \mod_hybridteaching\controller\common_controller {
    /** @var int The timetype to use when calculating the duration. Minutes */
    const MINUTE_TIMETYPE = 1;

    /** @var int The timetype to use when calculating the duration. Hours */
    const HOUR_TIMETYPE = 2;

    /** @var int The operation to use when updating the duration. Equal */
    const EQUAL = 1;

    /** @var int The operation to use when updating the duration. Add */
    const ADD = 2;

    /** @var int The operation to use when updating the duration. Reduce */
    const REDUCE = 3;

    /** @var bool Whether the subplugin vc exists. */
    protected $existssubpluginvc = false;

    /** @var bool Whether the subplugin store exists. */
    protected $existssubpluginstore = false;

    /**
     * Constructs a new instance of the class.
     *
     * @param stdClass $hybridobject The hybrid object to initialize the instance with. Defaults to null.
     * @throws Some_Exception_Class A description of the exception that may be thrown.
     * @return void
     */
    public function __construct(stdClass $hybridobject = null) {
        parent::__construct($hybridobject);
        if (!empty($hybridobject->typevc) && helper::subplugin_config_exists($hybridobject->config)) {
            $this->existssubpluginvc = true;
            self::require_subplugin_session($hybridobject->typevc);
        }
    }


    /**
     * Loads sessions from the database based on the given parameters.
     *
     * @param int $page The page number to load.
     * @param int $recordsperpage The number of records to load per page.
     * @param array $params Additional parameters to filter the sessions.
     * @param string $extraselect Additional SQL select statement.
     * @param string $operator The operator to use for the start time filter.
     * @param string $sort The column to sort the sessions by.
     * @param string $dir The direction of the sorting.
     * @return array The loaded sessions as an array.
     */
    public function load_sessions($page = 0, $recordsperpage = 0, $params = [], $extraselect = '',
          $operator = self::OPERATOR_GREATER_THAN, $sort = 'starttime', $dir = 'DESC') {
        global $DB;
        $where = '';
        $params = $params + ['hybridteachingid' => $this->hybridobject->id];

        if (!empty($params['starttime'])) {
            $where .= ' AND starttime '.$operator.' :starttime';
        }

        if (!empty($extraselect)) {
            $where .= " AND $extraselect";
        }

        $sql = 'SELECT *
                  FROM {hybridteaching_session}
                 WHERE hybridteachingid = :hybridteachingid ' . $where . '
              ORDER BY ' . $sort . ' ' . $dir;

        $sessions = $DB->get_records_sql($sql, $params, $page * $recordsperpage, $recordsperpage);
        $sessionsarray = json_decode(json_encode($sessions), true);
        return $sessionsarray;
    }


    /**
     * Creates a session.
     *
     * @param mixed $data The data for creating the session.
     * @throws Some_Exception_Class If an error occurs during session creation.
     * @return mixed The created session.
     */
    public function create_session($data) {
        $session = null;
        $data->hybridteachingid = $this->hybridobject->id;
        $data->config = $this->hybridobject->config;
        if (!empty($data->durationgroup)) {
            $data->duration = self::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);
        } else {
            $data->duration = self::calculate_duration($data->duration,
                $data->timetype);
        }
        $data->userecordvc = $this->hybridobject->userecordvc;
        if ($data->userecordvc == 1) {
            $data->processedrecording = -1;
        }

        $multiplesess = false;
        if (isset($data->addmultiply)) {
            $this->create_multiple_sessions($data);
            $multiplesess = true;
        } else {
            $session = $this->create_unique_session($data);
        }

        $event = \mod_hybridteaching\event\session_added::create([
            'objectid' => $this->hybridobject->id,
            'context' => \context_course::instance($this->hybridobject->course),
            'other' => [
                'multiplesess' => $multiplesess,
                'sessid' => !empty($session) ? $session->id : null,
            ],
        ]);

        $event->trigger();

        return $session;
    }


    /**
     * Create a unique session.
     *
     * @param mixed $session The session data.
     * @param int $countsess The count of sessions.
     * @return mixed The created session.
     */
    public function create_unique_session($session, $countsess = 0) {
        global $DB;

        $session = $this->fill_session_data_for_create($session);
        $session->id = $DB->insert_record('hybridteaching_session', $session);
        if (!empty($session->description)) {
            $description = file_save_draft_area_files($session->descriptionitemid,
                $session->context->id, 'mod_hybridteaching', 'session', $session->id,
                ['subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0],
                $session->description);
            $DB->set_field('hybridteaching_session', 'description', $description, ['id' => $session->id]);
        }

        if (!empty($session->sessionfiles)) {
            if (isset($session->replicatedoc) && $session->replicatedoc == 1) {
                file_save_draft_area_files($session->sessionfiles,
                $session->context->id, 'mod_hybridteaching', 'session', $session->id,
                ['subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0]);
            } else {
                if ($countsess == 0) {
                    file_save_draft_area_files($session->sessionfiles,
                    $session->context->id, 'mod_hybridteaching', 'session', $session->id,
                    ['subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0]);
                }
            }

        }
        $session->htsession = $session->id;
        $session->caleventid = 0;
        if (isset($session->caleneventpersession) && $session->caleneventpersession == 1) {
            calendar_helpers::hybridteaching_create_calendar_event($session);
        } else {
            if ($countsess == 0) {
                calendar_helpers::hybridteaching_create_calendar_event($session);
            }
        }

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

        $wdaydesc = [0 => 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        list($hour, $minutes) = $this->get_hour_and_minutes($data->starttime);
        $countsess = 0;
        while ($sdate < $enddate) {
            if ($sdate < strtotime('+1 week', $startweek)) {
                $dinfo = usergetdate($sdate);
                if (isset($data->sdays) && array_key_exists($wdaydesc[$dinfo['wday']], $data->sdays)) {
                    $data->starttime = make_timestamp($dinfo['year'], $dinfo['mon'], $dinfo['mday'],
                        $hour, $minutes);
                    $this->create_unique_session($data, $countsess);
                    $countsess++;
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
        $session->duration = self::calculate_duration($session->duration,
            $session->timetype);
        if (!$DB->update_record('hybridteaching_session', $session)) {
            $errormsg = 'errorupdatesession';
        }

        if (isset($data->updatecalen) && $data->updatecalen == 1) {
            calendar_helpers::hybridteaching_update_calendar_event($session);
        }

        $session = $DB->get_record('hybridteaching_session', ['id' => $session->id]);
        if (!empty($session->typevc)) {
            $classname = $this->get_subpluginvc_class($session->typevc);
            $subpluginsession = new $classname();
            $subpluginsession->update_session_extended($session, $this->hybridobject);
        }

        list($course, $cm) = get_course_and_cm_from_instance($session->hybridteachingid, 'hybridteaching');
        $event = \mod_hybridteaching\event\session_updated::create([
            'objectid' => $this->hybridobject->id,
            'context' => \context_module::instance($cm->id),
            'other' => [
                'sessid' => !empty($session) ? $session->id : null,
            ],
        ]);

        $event->trigger();

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
        if (isset($data->durationgroup)) {
            $data->duration = $data->durationgroup['duration'];
            $data->timetype = $data->durationgroup['timetype'];
        }

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
                        $reducedduration = $session->duration - self::calculate_duration($data->duration, $data->timetype);
                        $reducedduration > 0 ? $session->duration = $reducedduration : '';
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

            if (isset($data->updatecalen) && $data->updatecalen == 1) {
                calendar_helpers::hybridteaching_update_calendar_event($session);
            }

            list($course, $cm) = get_course_and_cm_from_instance($this->hybridobject->id, 'hybridteaching');
            $event = \mod_hybridteaching\event\session_updated::create([
                'objectid' => $this->hybridobject->id,
                'context' => \context_module::instance($cm->id),
                'other' => [
                    'sessid' => !empty($session) ? $session->id : null,
                ],
            ]);

            $event->trigger();

            if (!empty($session->typevc)) {
                $classname = $this->get_subpluginvc_class($session->typevc);
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
        list($course, $cm) = get_course_and_cm_from_instance($this->hybridobject->id, 'hybridteaching');
        $context = \context_module::instance($cm->id);
        if (time() >= $sessiondata->starttime && time() < ($sessiondata->starttime + $sessiondata->duration)) {
            $errormsg = 'errordsinprogress';
        } else {
            calendar_helpers::hybridteaching_delete_calendar_events($id);

            if (!$DB->delete_records('hybridteaching_session', ['id' => $id])) {
                $errormsg = 'errordeletesession';
            }
            if (!empty($sessiondata->typevc) && $this->existssubpluginvc) {
                $classname = $this->get_subpluginvc_class($sessiondata->typevc);
                $subpluginsession = new $classname();
                $subpluginsession->delete_session_extended($id, $this->hybridobject->config);
            }

            // Check if exists storage subplugin.
            $this->existssubpluginstore = helper::subplugin_config_exists($sessiondata->storagereference, 'store');
            if (!empty($sessiondata->storagereference) && $this->existssubpluginstore) {
                $pluginstoreconfig = $DB->get_record('hybridteaching_configs', ['id' => $sessiondata->storagereference]);
                $classname = $this->get_subpluginstore_class($pluginstoreconfig->type);
                $subpluginsession = new $classname();
                $subpluginsession->delete_session_extended($id, $pluginstoreconfig);
            }

            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'mod_hybridteaching', 'session', $sessiondata->id);
            $fs->delete_area_files($context->id, 'mod_hybridteaching', 'chats', $sessiondata->id);
        }

        $event = \mod_hybridteaching\event\session_deleted::create([
            'objectid' => $this->hybridobject->id,
            'context' => $context,
            'other' => [
                'sessid' => $id,
            ],
        ]);

        $event->trigger();

        return $errormsg;
    }


    /**
     * Deletes all sessions associated with the hybrid teaching object.
     *
     */
    public function delete_all_sessions() {
        global $DB;

        $sessions = $DB->get_records('hybridteaching_session', ['hybridteachingid' => $this->hybridobject->id]);
        foreach ($sessions as $session) {
            $this->delete_session($session->id);
        }

        // Delete attendances, logs and session_pwd, if exists.
        $attendances = $DB->get_records('hybridteaching_attendance', ['hybridteachingid' => $this->hybridobject->id], 'id', 'id');
        foreach ($attendances as $att) {
            $DB->delete_records('hybridteaching_attend_log', ['attendanceid' => $att->id]);
            $DB->delete_records('hybridteaching_session_pwd', ['attendanceid' => $att->id]);
        }
        $DB->delete_records('hybridteaching_attendance', ['hybridteachingid' => $this->hybridobject->id]);
    }

    /**
     * Retrieve the session data from the database using the given session ID.
     *
     * @param int $sessionid The ID of the session to retrieve.
     * @return mixed The session data.
     */
    public function get_session($sessionid) {
        global $DB;
        $sessiondata = $DB->get_record('hybridteaching_session', ['id' => $sessionid]);
        return $sessiondata;
    }


    /**
     * Retrieves the next session for a hybrid teaching object.
     *
     * @return mixed The next session record or null if no session is found.
     */
    public function get_next_session() {
        global $DB;
        $time = time();

        $datefilter = "";
        $datefilter  = " AND (hs.starttime + hs.duration >= :time OR hs.starttime IS NULL)";

        $sql = "SELECT *
                  FROM {hybridteaching_session} AS hs
                 WHERE hs.hybridteachingid = :id
                       $datefilter
                   AND (hs.isfinished = 0 OR hs.isfinished IS NULL)
              ORDER BY ABS(:time2 - hs.starttime) ASC
                 LIMIT 1";

        $nextsession = $DB->get_record_sql($sql, ['id' => $this->hybridobject->id, 'time' => $time, 'time2' => $time]);

        return $nextsession;
    }


    /**
     * Retrieves the last session for the specified hybrid teaching ID.
     *
     * @return mixed The last session record or null if not found.
     */
    public function get_last_session() {
        global $DB;
        $time = time();

        $sql = "SELECT *
                  FROM {hybridteaching_session} hs
                 WHERE hs.hybridteachingid = :id
                   AND hs.starttime < :time
              ORDER BY hs.starttime DESC LIMIT 1";
        $lastsession = $DB->get_record_sql($sql, ['id' => $this->hybridobject->id, 'time' => $time]);

        return $lastsession;
    }



    /**
     * Counts the number of sessions based on the given parameters.
     *
     * @param array $params an array of parameters to filter the sessions
     * @param string $operator the operator to use for filtering the sessions (default: self::OPERATOR_GREATER_THAN)
     * @return int the number of sessions that match the given parameters
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
            // Invalid timetype, return 0.
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
        if (isset($data->durationgroup['duration']) && isset($data->durationgroup['timetype'])) {
            !empty($session->duration) ? $session->duration :
                $session->duration = $data->durationgroup['duration'];
            !empty($session->timetype) ? $session->timetype :
                $session->timetype = $data->durationgroup['timetype'];
        }

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
     * @return object The updated session object.
     */
    public function fill_session_data_for_update($data) {
        global $USER, $DB;

        $session = $this->fill_session_data($data);
        if (!empty($data->sessionfiles)) {
            file_save_draft_area_files($data->sessionfiles,
                $data->context->id, 'mod_hybridteaching', 'session', $data->id,
                ['subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0]);
        }
        $session->timemodified = time();
        $session->caleventid = $DB->get_field('hybridteaching_session', 'caleventid', ['id' => $session->id]);
        $session->hybridteachingid = $DB->get_field('hybridteaching_session', 'hybridteachingid', ['id' => $session->id]);
        $session->modifiedby = $USER->id;

        return $session;
    }


    /**
     * A function that requires a subplugin session.
     *
     * @param string $type The type of subplugin.
     * @return void
     */
    public static function require_subplugin_session($type) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$type.'/classes/sessions.php');
    }

    /**
     * Get the subplugin class based on the given type.
     *
     * @param string $type The type of the subplugin.
     * @return string The fully qualified namespace of the subplugin class.
     */
    public static function get_subpluginvc_class($type) {
        $classname = '\hybridteachvc_' . $type . '\sessions';
        return $classname;
    }

    /**
     * Retrieves the class name for a given subplugin store type.
     *
     * @param string $type The type of subplugin store.
     * @return string The class name for the specified subplugin store type.
     */
    public static function get_subpluginstore_class($type) {
        $classname = '\hybridteachstore_' . $type . '\sessions';
        return $classname;
    }


    /**
     * Require a subplugin store.
     *
     * @param string $type The type of the subplugin store.
     */
    public static function require_subplugin_store($type) {
        global $CFG;
        require_once($CFG->dirroot.'/mod/hybridteaching/store/'.$type.'/classes/sessions.php');
    }


    /**
     * Calculate storage, based on the course category configured in storage settings.
     *
     * @param mixed $session The session parameter.
     * @param mixed $courseid The course ID parameter.
     * @return void
     */
    public static function savestorage($session, $courseid) {
        // Calculate storage, based on the course category configured in storage settings.
        global $DB;

        $sql = "SELECT path
                  FROM {course_categories} cc
                  JOIN {course} c ON c.category = cc.id
                 WHERE c.id = ?";
        $path = $DB->get_record_sql ($sql, [$courseid]);
        $path = $path->path;

        $patharray = explode ('/', $path);
        $patharray = array_reverse($patharray);

        $store = 0;
        foreach ($patharray as $element) {
            if ($element != '') {
                // Check that there is some direct configuration for the element category.
                // Otherwise, check another level in the array.
                $params = [
                    'visible' => 1,
                    'subplugintype' => 'store',
                    'category' => $element,
                ];

                $categoriescond = configs_controller::get_categories_conditions($params);
                $incategories = '';
                if (!empty($categoriescond)) {
                    $incategories = $categoriescond['conditions'];
                    $categoriesparams = $categoriescond['inparams'];
                }

                if (!empty($categoriesparams)) {
                    unset($params['category']);
                    $params = array_merge($params, $categoriesparams);
                }

                $sql = "SELECT hi.*
                            FROM {hybridteaching_configs} hi
                            WHERE hi.visible = ? AND hi.subplugintype = ? $incategories
                        ORDER BY hi.visible DESC, hi.sortorder, hi.id";
                $config = $DB->get_records_sql($sql, $params);
                if ($config) {
                    // Convert in the first element from array.
                    $config = reset($config);
                    if ($config) {
                        $store = $config->id;
                        break;
                    }
                }
            }
        }

        // If no category has been assigned directly, check if there are any category with "All".
        if ($store == 0) {
            $select = "" . $DB->sql_like('subplugintype', ':subplugintype') . " AND categories = :categories
                AND visible = :visible";
            $params = [
                'subplugintype' => 'store',
                'categories' => 0,
                'visible' => 1,
            ];
            $config = $DB->get_records_select('hybridteaching_configs', $select, $params, 'sortorder', 'id', 0, 1);
            if ($config) {
                $config = reset ($config);
                if ($config) {
                    $store = $config->id;
                }
            }
        }

        // Save storage in session.
        if ($store) {
            $session->storagereference = $store;
            $DB->update_record('hybridteaching_session', $session);
        }
    }


    /**
     * Retrieves the subplugin storage class based on the given storage reference.
     *
     * @param mixed $storagereference The reference to the storage.
     * @return array|null An array containing the classname and type of the subplugin storage, or null if not found.
     */
    public static function get_subpluginstorage_class($storagereference) {
        global $DB;
        $sql = "SELECT type FROM {hybridteaching_configs}
            WHERE id = :storagereference AND subplugintype = 'store'";
        $object = $DB->get_record_sql($sql, ['storagereference' => $storagereference]);

        if ($object->type) {
            $classname = '\hybridteachstore_' . $object->type . '\sessions';
            $result = [
                'classname' => $classname,
                'type' => $object->type,
            ];
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Sets the isfinished property of a session to 1 in the hybridteaching_session table.
     *
     * @param int $sessid The ID of the session to update.
     */
    public static function set_session_finished($sessid) {
        global $DB;
        $session = new stdClass();
        $session->id = $sessid;
        $session->isfinished = 1;
        $DB->update_record('hybridteaching_session', $session);
    }

    /**
     * Retrieves the last undated session for the given hybrid teaching ID.
     *
     * @return mixed The undated session record or null if not found.
     */
    public function get_last_undated_session() {
        global $DB;
        $time = time();

        $sql = "SELECT *
                  FROM {hybridteaching_session} hs
                 WHERE hs.hybridteachingid = :id
                   AND (hs.isfinished = 0 OR hs.isfinished IS NULL)
                   AND hs.duration = 0
              ORDER BY ABS(:time - hs.starttime) ASC
                 LIMIT 1";

        $undatedsession = $DB->get_record_sql($sql, ['id' => $this->hybridobject->id, 'time' => $time]);
        return $undatedsession;
    }

    /**
     * Sets the visibility of a record in the hybridteaching_session table.
     *
     * @param int $sessid The ID of the session.
     * @throws -
     * @return void
     */
    public static function set_record_visibility($sessid) {
        global $DB;

        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid]);
        if ($session->visiblerecord == 1) {
            $session->visiblerecord = 0;
            $DB->update_record('hybridteaching_session', $session);
        } else {
            $session->visiblerecord = 1;
            $DB->update_record('hybridteaching_session', $session);
        }
    }

    /**
     * Set the chat visibility for a given session ID.
     *
     * @param int $sessid The session ID
     */
    public static function set_chat_visibility($sessid) {
        global $DB;

        $session = $DB->get_record('hybridteaching_session', ['id' => $sessid]);
        if ($session->visiblechat == 1) {
            $session->visiblechat = 0;
            $DB->update_record('hybridteaching_session', $session);
        } else {
            $session->visiblechat = 1;
            $DB->update_record('hybridteaching_session', $session);
        }
    }

    /**
     * Check if a session configuration exists.
     *
     * @param object $session The session object.
     * @return bool Whether the session configuration exists or not.
     */
    public static function get_sessionconfig_exists($session): bool {
        global $DB;

        $configexists = true;
        if (empty($session->vcreference) ||
              !$DB->get_record('hybridteaching_configs', ['id' => $session->vcreference], '*', IGNORE_MISSING)) {
            $configexists = false;
        }
        return $configexists;
    }

    /**
     * Checks if the session has started.
     *
     * @param mixed $session The session object.
     * @return bool Returns true if the session has started, false otherwise.
     */
    public function session_started($session): bool {
        global $DB;

        $sessionvcstarted = false;
        if (empty($session->typevc)) {
            return true;
        }
        $sessionvctable = 'hybridteachvc_' . $session->typevc;
        if ($DB->get_record($sessionvctable, ['htsession' => $session->id], '*', IGNORE_MISSING)) {
            $sessionvcstarted = true;
        }

        return $sessionvcstarted;
    }

    /**
     * Finish unfinished sessions for a given hybrid teaching ID.
     *
     * @param int $hid The hybrid teaching ID.
     * @throws Some_Exception_Class If an error occurs while finishing the sessions.
     * @return void
     */
    public function finish_unfinished_sessions($hid): void {
        global $DB;

        $sql = 'SELECT *
                  FROM {hybridteaching_session}
                 WHERE hybridteachingid = ?
                   AND isfinished = ?
                   AND starttime + duration < ?
                   AND duration > 0';
        $sessions = $DB->get_records_sql($sql, [$hid, 0, time()]);
        if ($sessions) {
            list($course, $cm) = get_course_and_cm_from_instance($hid, 'hybridteaching');
            foreach ($sessions as $session) {
                $event = \mod_hybridteaching\event\session_finished::create([
                    'objectid' => $hid,
                    'context' => \context_module::instance($cm->id),
                    'other' => [
                        'sessid' => $session->id,
                    ],
                ]);
                $event->trigger();
            }
        }
    }

    /**
     * Get the number of sessions performed for a given hybrid teaching ID.
     *
     * @param int $id the hybrid teaching ID
     * @return int the number of sessions performed
     */
    public static function get_sessions_performed($id) {
        global $DB;
        $sql = "SELECT count(id)
                  FROM {hybridteaching_session}
                 WHERE hybridteachingid = :htid
                   AND isfinished = 1
                   AND (starttime  + duration) < :now";

        $sessionperformed = $DB->count_records_sql($sql, ['htid' => $id, 'now' => time()]);
        return $sessionperformed;
    }


    /**
     * Get the sessions id currently in progress for a given hybrid teaching ID.
     *
     * @param int $id the hybrid teaching id
     * @return array id number of sessions in progress
     */
    public static function get_sessions_in_progress($id) {
        global $DB;
        $sql = 'SELECT hts.id
                  FROM {hybridteaching_session} hts
                 WHERE hts.hybridteachingid = ?
                   AND hts.starttime < ?
                   AND hts.starttime + hts.duration > ?';
        $sessionprogressparam = [$id, time(), time()];
        $sessionsinprogress = $DB->get_records_sql($sql, $sessionprogressparam);
        return $sessionsinprogress;
    }
}

