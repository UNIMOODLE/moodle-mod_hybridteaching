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
 * @package    hybridteachvc_teams
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_teams;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');

/**
 * Class sessions.
 */
class sessions {
    /** @var object $teamssession The teams session object. */
    protected $teamssession;

    /**
     * Constructor for the PHP class.
     *
     * @param int $htsessionid session ID
     */
    public function __construct($htsessionid = null) {
        if (!empty($htsessionid)) {
            $this->teamssession = $this->load_session($htsessionid);
        }
    }

    /**
     * Load a session by session ID.
     *
     * @param int $htsessionid session ID
     * @return object
     */
    public function load_session($htsessionid) {
        global $DB;
        $this->teamssession = $DB->get_record('hybridteachvc_teams', ['htsession' => $htsessionid]);
        return $this->teamssession;
    }

    /**
     * Get the session value.
     *
     * @return object
     */
    public function get_session() {
        return $this->teamssession;
    }

    /**
     * Set the session for the PHP function.
     *
     * @param int $htsessionid session ID
     */
    public function set_session($htsessionid) {
        $this->teamssession = $this->load_session($htsessionid);
    }

    /**
     * Creates a new session by calling the Hybrid Web Service's create_meeting function
     * and stores the data returned from it in the hybridteachvc_teams table if the response
     * is not false.
     *
     * @param object $session the data to be passed to the create_meeting function
     * @param object $ht the hybridteaching instance
     * @return mixed the response from the create_meeting function
     */
    public function create_unique_session_extended($session, $ht) {
        global $DB;

        $cm = get_coursemodule_from_instance('hybridteaching', $ht->id);
        $context = \context_module::instance($cm->id);
        if (!has_capability('hybridteachvc/teams:use', $context)) {
            return;
        }

        $teamsconfig = $this->load_teams_config($ht->config);
        if (!$teamsconfig) {
            return false;
        }
        $teams = new teams_handler($teamsconfig);
        if (!get_config('hybridteaching', 'reusesession') && $ht->reusesession == 0) {
            try {
                $response = $teams->createmeeting($session, $ht);
            } catch (\moodle_exception $e) {
                throw $e;
            }
        } else {
            $lastteams = null;
            if (isset($session->vcreference)) {
                $lastteams = $this->get_last_teams_in_hybrid($session->hybridteachingid,
                    $session->groupid, $session->typevc, $session->vcreference,
                    $session->starttime);
            }
            if (!empty($lastteams)) {
                $response = [];
                $response['id'] = $lastteams->meetingid;
                $response['meetingCode'] = $lastteams->meetingcode;
                $response['participants']['organizer']['identity']['user']['id'] = $lastteams->organizer;
                $response['joinUrl'] = $lastteams->joinurl;
            } else {
                try {
                    $response = $teams->createmeeting($session, $ht);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }

        if (isset($response['joinUrl']) && isset($response['meetingCode'])
            && isset($response['participants']['organizer']['identity']['user']['id']) && isset($response['id'])) {
            $teams = new \stdClass();
            $teams->htsession = $session->id;
            $teams->meetingid = $response['id'];
            $teams->meetingcode = $response['meetingCode'];
            $teams->organizer = $response['participants']['organizer']['identity']['user']['id'];
            $teams->joinurl = $response['joinUrl'];

            if (!$teams->id = $DB->insert_record('hybridteachvc_teams', $teams)) {
                return false;

            }
        }
    }

    public function update_session_extended($data) {

    }


    /**
     * Deletes session from the database.
     *
     * @param object $htsession The session object.
     * @param int $configid The ID of the config.
     */
    public function delete_session_extended($htsession, $configid) {
        global $DB;
        $teamsconfig = $this->load_teams_config($configid);
        if (!empty($teamsconfig)) {
            $teams = $DB->get_record('hybridteachvc_teams', ['htsession' => $htsession]);
            if (isset($teams->meetingid)) {
                // If exists meeting, delete it.
                try {
                    $teamshandler = new teams_handler($teamsconfig);
                    $teamshandler->deletemeeting($teams);
                } catch (\Exception $e) {
                    // No action for delete.
                }
            }
        }
        $DB->delete_records('hybridteachvc_teams', ['htsession' => $htsession]);
    }

    /**
     * Loads a Teams config based on the given config ID.
     *
     * @param int $configid The ID of the config to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The Teams config record on success, or false on failure.
     */
    public function load_teams_config($configid) {
        global $DB;

        $sql = 'SELECT tc.*
                  FROM {hybridteaching_configs} hc
                  JOIN {hybridteachvc_teams_config} tc ON tc.id = hc.subpluginconfigid
                 WHERE hc.id = :configid AND hc.visible = 1';

        $config = $DB->get_record_sql($sql, ['configid' => $configid]);
        return $config;
    }

    /**
     * Loads the teams configuration from the session.
     *
     * @return mixed The loaded Teams configuration.
     */
    public function load_teams_config_from_session() {
        global $DB;
        $sql = "SELECT h.config
                FROM {hybridteaching} h
                JOIN {hybridteaching_session} hs ON hs.hybridteachingid = h.id
                WHERE hs.id = :htsession";

        $configpartial = $DB->get_record_sql($sql, ['htsession' => $this->teamssession->htsession]);
        $config = $this->load_teams_config($configpartial->config);
        return $config;
    }

    /**
     * Get zone access.
     *

     * @param bool $userismoderator  Whether or not user is moderator.
     * @return array
     */
    public function get_zone_access($userismoderator = true) {
        if ($this->teamssession) {
            $teamsconfig = $this->load_teams_config_from_session();
            if (!$teamsconfig) {
                // No exists config teams or its hidden.
                return [
                    'returncode' => 'FAILED',
                    'message' => get_string('noconfig', 'hybridteaching'),
                ];
            }

            // Not check if a meeting teams exists, cause in Teams if you cancel it from calendar
            // the meeting still exists and is not deleted.
            $array = [
                'id' => $this->teamssession->id,
                'ishost' => $userismoderator,
                'url' => base64_encode($this->teamssession->joinurl),
            ];
            return $array;
        } else {
            return [
                'returncode' => 'FAILED',
                'message' => get_string('error_unable_join', 'hybridteaching'),
            ];
        }
    }

    /**
     * Get the last teams in hybrid for a given session, group, typevc, vcreference, and starttime.
     *
     * @param int $htid The hybrid teaching ID
     * @param int $groupid The group ID
     * @param string $typevc The type of VC
     * @param int $vcreference The VC reference
     * @param string $starttime The start time
     * @return object The configuration record
     */
    public function get_last_teams_in_hybrid($htid, $groupid, $typevc, $vcreference, $starttime) {
        global $DB;

        $sql = 'SELECT hm.*
                  FROM {hybridteachvc_teams} hm
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
     * Get the chat URL if the user has the capability, otherwise return an empty string.
     *
     * @param object $context The context object.
     * @return string
     */
    public function get_chat_url ($context) {
        if (!has_capability('hybridteachvc/teams:view', $context)) {
            return '';
        }
        if (isset($this->teamssession->chaturl)) {
            return $this->teamssession->chaturl;
        }
        return '';
    }
}
