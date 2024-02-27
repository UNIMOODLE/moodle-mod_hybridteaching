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
 * @package    hybridteachvc_zoom
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_zoom;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/hybridteaching/vc/zoom/classes/webservice.php');

/**
 * Class sessions.
 */
class sessions {
    /** @var object $zoomsession The current zoom session. */
    protected $zoomsession;

    /**
     * Constructor for the class.
     *
     * @param int $htsessionid The ID of the session.
     */
    public function __construct($htsessionid = null) {
        if (!empty($htsessionid)) {
            $this->set_session($htsessionid);
        }
    }

    /**
     * Load a session by session ID.
     *
     * @param int $htsessionid The ID of the session to load.
     * @return Some_Return_Value
     */
    public function load_session($htsessionid) {
        global $DB;
        $this->zoomsession = $DB->get_record('hybridteachvc_zoom', ['htsession' => $htsessionid]);
        return $this->zoomsession;
    }

    /**
     * Get the current session.
     *
     * @return mixed
     */
    public function get_session() {
        return $this->zoomsession;
    }

    /**
     * Set the session for the PHP function.
     *
     * @param int $htsessionid The ID of the session.
     */
    public function set_session($htsessionid) {
        $this->zoomsession = $this->load_session($htsessionid);
    }

    /**
     * Creates a unique session extended for a Zoom meeting config.
     *
     * @param object $session The data to create the meeting.
     * @param object $ht The hybridteaching object
     * @throws Exception If the meeting creation fails.
     */
    public function create_unique_session_extended($session, $ht) {
        global $DB;

        $cm = get_coursemodule_from_instance('hybridteaching', $ht->id);
        $context = \context_module::instance($cm->id);
        if (!has_capability('hybridteachvc/zoom:use', $context)) {
            return;
        }

        $zoomconfig = $this->load_zoom_config($ht->config);
        if (!$zoomconfig) {
            return false;
        }

        $service = new \hybridteachvc_zoom\webservice($zoomconfig);

        if (!get_config('hybridteaching', 'reusesession') && $ht->reusesession == 0) {
            $response = $service->create_meeting($session, $ht);
        } else {
            $zoom = null;
            if (isset($session->vcreference)) {
                $zoom = $this->get_last_zoom_in_hybrid($session->hybridteachingid,
                    $session->groupid, $session->typevc, $session->vcreference,
                    $session->starttime);
            }
            if (!empty($zoom)) {
                $response = new \stdClass();
                $response->id = $zoom->meetingid;
                $response->host_id = $zoom->hostid;
                $response->host_email = $zoom->hostemail;
                $response->start_url = $zoom->starturl;
                $response->join_url = $zoom->joinurl;
                $response->settings = new \stdClass();
                $response->settings->host_video = $zoom->optionhostvideo;
                $response->settings->participant_video = $zoom->optionparticipantsvideo;
            } else {
                $response = $service->create_meeting($session, $ht);
            }
        }

        if ($response != false) {
            $zoom = $this->populate_htzoom_from_response($session, $response);
            $zoom->id = $DB->insert_record('hybridteachvc_zoom', $zoom);
            $this->zoomsession = $zoom;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates a Zoom session with new data.
     *
     * @param mixed $data The data to update the Zoom session with.
     * @param object $ht The hybridteaching object
     * @throws Exception If there is an error updating the Zoom session.
     * @return string An error message if there was an error updating the Zoom session, otherwise null.
     */
    public function update_session_extended($data, $ht) {
        global $DB;
        $errormsg = '';
        $zoomconfig = $this->load_zoom_config($ht->config);
        $zoom = $DB->get_record('hybridteachvc_zoom', ['htsession' => $data->id]);
        // If is created zoom, change in zoom with webservice.
        if ($zoom) {
            $zoom = (object) array_merge((array) $zoom, (array) $data);
            $service = new \hybridteachvc_zoom\webservice($zoomconfig);
            $service->update_meeting($zoom, $ht);
        }
        return $errormsg;
    }

    /**
     * Deletes a Zoom session extended and all its associated records from the database.
     *
     * @param mixed $htsession the session identifier
     * @param mixed $configid the config identifier
     * @throws Exception if an error occurs while deleting the session
     */
    public function delete_session_extended($htsession, $configid) {
        global $DB;
        $zoomconfig = $this->load_zoom_config($configid);
        if (!empty($zoomconfig)) {
            $zoom = $DB->get_record('hybridteachvc_zoom', ['htsession' => $htsession]);
            if (isset($zoom->meetingid)) {
                // If exists meeting, delete it.
                try {
                    $service = new \hybridteachvc_zoom\webservice($zoomconfig);
                    $service->deletemeeting($zoom->meetingid, 0);
                } catch (\Exception $e) {
                    // No action for delete.
                }
            }
        }
        $DB->delete_records('hybridteachvc_zoom', ['htsession' => $htsession]);
    }

    /**
     * Populates a new stdClass object with relevant data from a Zoom API response and returns it.
     *
     * @param mixed $data stdClass object containing htsession data
     * @param mixed $response stdClass object containing Zoom API response data
     * @return stdClass $newzoom stdClass object containing relevant data
     */
    public function populate_htzoom_from_response($data, $response) {
        $newzoom = new \stdClass();
        $newzoom->htsession = $data->id;
        $newzoom->meetingid = $response->id;
        $newzoom->hostid = $response->host_id;
        $newzoom->hostemail = $response->host_email;
        $newzoom->starturl = $response->start_url;
        $newzoom->joinurl = $response->join_url;
        $newzoom->optionhostvideo = $response->settings->host_video;
        $newzoom->optionparticipantsvideo = $response->settings->participant_video;
        $newzoom->downloadattempts = 0;

        return $newzoom;
    }

    /**
     * Loads a Zoom config based on the given config ID.
     *
     * @param int $configid The ID of the config to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The Zoom config record on success, or false on failure.
     */
    public function load_zoom_config($configid) {
        global $DB;

        $sql = "SELECT zi.accountid, zi.clientid, zi.clientsecret,
                       zi.emaillicense
                  FROM {hybridteaching_configs} hi
                  JOIN {hybridteachvc_zoom_config} zi ON zi.id = hi.subpluginconfigid
                 WHERE hi.id = :configid AND hi.visible = 1";

        $zoomconfig = $DB->get_record_sql($sql, ['configid' => $configid]);
        return $zoomconfig;
    }

    /**
     * Loads the zoom configuration from the session.
     *
     * @return mixed The loaded zoom configuration.
     */
    public function load_zoom_config_from_session() {
        global $DB;
        $sql = "SELECT h.config
                FROM {hybridteaching} h
                JOIN {hybridteaching_session} hs ON hs.hybridteachingid = h.id
                WHERE hs.id = :htsession";

        $configpartial = $DB->get_record_sql($sql, ['htsession' => $this->zoomsession->htsession]);
        $config = $this->load_zoom_config($configpartial->config);
        return $config;
    }

    /**
     * Retrieves the zone access for the current session's Zoom meeting.
     *

     * @param bool $userismoderator  Whether or not user is moderator.
     * @return array Returns an array containing the session ID, host status, access status, and the URL to join the meeting.
     */
    public function get_zone_access($userismoderator = false) {
        if ($this->zoomsession) {
            $zoomconfig = $this->load_zoom_config_from_session();
            if (!$zoomconfig) {
                // No exists config zoom or its hidden.
                return [
                    'returncode' => 'FAILED',
                    'message' => get_string('noconfig', 'hybridteaching'),
                ];
            }
            try {
                $service = new \hybridteachvc_zoom\webservice($zoomconfig);
                $meeting = $service->get_meeting_webinar_info($this->zoomsession->meetingid, 0);


                if (isset($meeting->start_url)) {
                    if ($userismoderator) {
                        $zoomurl = $meeting->start_url;
                    } else {
                        $zoomurl = $meeting->join_url;
                    }

                    $array = [
                        'id' => $this->zoomsession->id,
                        'ishost' => $userismoderator,
                        'url' => base64_encode($zoomurl),
                    ];
                    return $array;
                } else {
                    return [
                        'returncode' => 'FAILED',
                        'message' => get_string('error_unable_join', 'hybridteaching'),
                    ];
                }
            } catch (\Exception $e) {
                // No exists zoom meeting.
                return [
                    'returncode' => 'FAILED',
                    'message' => get_string('error_unable_join', 'hybridteaching'),
                ];
            }
        }
    }

    /**
     * Get the last Zoom in hybrid teaching session based on the provided parameters.
     *
     * @param int $htid
     * @param int $groupid
     * @param string $typevc
     * @param int $vcreference
     * @param string $starttime Start time of the meeting
     * @return object|false $config The Zoom config record on success, or false on failure.
     */
    public function get_last_zoom_in_hybrid($htid, $groupid, $typevc, $vcreference, $starttime) {
        global $DB;

        $sql = 'SELECT hm.*
                  FROM {hybridteachvc_zoom} hm
            INNER JOIN {hybridteaching_session} hs ON hm.htsession = hs.id
                 WHERE hs.hybridteachingid = :htid AND hs.groupid = :groupid
                   AND hs.typevc = :typevc AND hs.vcreference = :vcreference
                   AND hs.starttime < :starttime
              ORDER BY hm.id DESC
                 LIMIT 1';

        $config = $DB->get_record_sql($sql, ['htid' => $htid, 'groupid' => $groupid,
            'typevc' => $typevc, 'vcreference' => $vcreference, 'starttime' => $starttime, ]);
        return $config;
    }

    /**
     * Retrieves the chat URL for the given context.
     *
     * @param object $context The context for which to retrieve the chat URL.
     * @return string The chat URL for the given context.
     */
    public function get_chat_url ($context) {
        if (!has_capability('hybridteachvc/zoom:view', $context)) {
            return '';
        }
        return '';
    }
}
