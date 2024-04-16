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
 * @package    hybridteachvc_meet
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace hybridteachvc_meet;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once('meet_handler.php');

/**
 * Class sessions.
 */
class sessions {
    /** @var stdClass $meetsession The Meet session. */
    protected $meetsession;

    /**
     * Constructor for the class.
     *
     * @param int $htsessionid session ID
     */
    public function __construct($htsessionid = null) {
        if (!empty($htsessionid)) {
            $this->meetsession = $this->load_session($htsessionid);
        }
    }

    /**
     * Load a session by ID.
     *
     * @param int $htsessionid session ID
     * @return object
     */
    public function load_session($htsessionid) {
        global $DB;
        $this->meetsession = $DB->get_record('hybridteachvc_meet', ['htsession' => $htsessionid]);
        return $this->meetsession;
    }

    /**
     * Get the session.
     *
     * @return meetsession
     */
    public function get_session() {
        return $this->meetsession;
    }

    /**
     * Set the session for the PHP function.
     *
     * @param int $htsessionid session ID
     */
    public function set_session($htsessionid) {
        $this->meetsession = $this->load_session($htsessionid);
    }

    /**
     * Create a unique session extended.
     *
     * @param object $session The session object
     * @param object $ht The hybrid teaching object
     * @return int
     */
    public function create_unique_session_extended($session, $ht) {
        global $DB;

        $cm = get_coursemodule_from_instance('hybridteaching', $ht->id);
        $context = \context_module::instance($cm->id);
        if (!has_capability('hybridteachvc/meet:use', $context)) {
            return;
        }

        $existsmeet = $DB->get_field('hybridteachvc_meet', 'id', ['htsession' => $session->id]);
        if (!$existsmeet) {
            $subpluginconfigid = $DB->get_field('hybridteaching_configs', 'subpluginconfigid', ['id' => $ht->config]);
            $meetconfig = $DB->get_record('hybridteachvc_meet_config', ['id' => $subpluginconfigid]);
            $client = new meet_handler($meetconfig);
            if (!get_config('hybridteaching', 'reusesession')) {
                $event = $client->create_meeting_event($session);
            } else {
                if (isset($session->vcreference)) {
                    $meet = $this->get_last_meet_in_hybrid($session->hybridteachingid,
                        $session->groupid, $session->typevc, $session->vcreference,
                        $session->starttime);
                }
                if (!empty($meet)) {
                    $event = new stdClass();
                    $event->creator = new stdClass();
                    $event->creator->email = $meet->creatoremail;
                    $event->hangoutLink = $meet->joinurl;
                    $eventgoogle = $client->create_meeting_event($session);
                    $event->id = $eventgoogle->id;
                } else {
                    $event = $client->create_meeting_event($session);
                }
            }

            if ($event) {
                $meetsession = new stdClass();
                $meetsession->htsession = $session->id;
                $meetsession->joinurl = $event->hangoutLink;
                $meetsession->creatoremail = $event->creator->email;
                $meetsession->eventid = $event->id;

                if (!$meetsession->id = $DB->insert_record('hybridteachvc_meet', $meetsession)) {
                    $this->meetsession = $meetsession;
                    return false;
                }
            }
        }

        return $meetsession->id;
    }

    /**
     * Update an extended session.
     *
     * @param object $data The data object.
     */
    public function update_session_extended($data) {
        // No requires action.
    }

    /**
     * Deletes an extended session.
     *
     * @param object $htsession
     * @param int $configid
     * @return void
     */
    public function delete_session_extended($htsession, $configid) {
        global $DB;
        $meetconfig = $this->load_meet_config($configid);
        if (!empty($meetconfig)) {
            $meet = $DB->get_record('hybridteachvc_meet', ['htsession' => $htsession]);
            if (isset($meet->eventid)) {
                // If exists meeting, delete it.
                try {
                    $meethandler = new meet_handler($meetconfig);
                    $meethandler->deletemeeting($meet->eventid);
                } catch (\Exception $e) {
                    // No action for delete.
                    return null;
                }
            }
        }
        $DB->delete_records('hybridteachvc_meet', ['htsession' => $htsession]);
    }

    /**
     * Loads a Meet config based on the given config ID.
     *
     * @param int $configid The ID of the config to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The Meet config record on success, or false on failure.
     */
    public function load_meet_config($configid) {
        global $DB;

        $sql = 'SELECT mc.*
                  FROM {hybridteaching_configs} hc
                  JOIN {hybridteachvc_meet_config} mc ON mc.id = hc.subpluginconfigid
                 WHERE hc.id = :configid AND hc.visible = 1';

        $config = $DB->get_record_sql($sql, ['configid' => $configid]);
        return $config;
    }

    /**
     * Loads the Meet configuration from the session.
     *
     * @return mixed The loaded Meet configuration.
     */
    public function load_meet_config_from_session() {
        global $DB;
        $sql = "SELECT h.config
                FROM {hybridteaching} h
                JOIN {hybridteaching_session} hs ON hs.hybridteachingid = h.id
                WHERE hs.id = :htsession";

        $configpartial = $DB->get_record_sql($sql, ['htsession' => $this->meetsession->htsession]);
        $config = $this->load_meet_config($configpartial->config);
        return $config;
    }

    /**
     * Retrieves the zone access information.
     *

     * @param bool $userismoderator  Whether or not user is moderator.
     * @return array The zone access information.
     */
    public function get_zone_access($userismoderator = false) {

        if ($this->meetsession) {
            $meetconfig = $this->load_meet_config_from_session();
            if (!$meetconfig) {
                // No exists config meet or its hidden.
                return [
                    'returncode' => 'FAILED',
                ];
            }

            $array = [
                'id' => $this->meetsession->id,
                'ishost' => $userismoderator,
                'url' => base64_encode($this->meetsession->joinurl),
            ];
            return $array;
        } else {
            return null;
        }
    }

    /**
     * Get the last meeting in a hybrid teaching session.
     *
     * @param int $htid The hybrid teaching ID
     * @param int $groupid The group ID
     * @param string $typevc The type of video conference
     * @param string $vcreference The video conference reference
     * @param int $starttime The start time
     * @return object The configuration record
     */
    public function get_last_meet_in_hybrid($htid, $groupid, $typevc, $vcreference, $starttime) {
        global $DB;

        $sql = 'SELECT hm.*
                  FROM {hybridteachvc_meet} hm
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
     * Get the chat URL for the given context.
     *
     * @param object $context Context object.
     * @return string
     */
    public function get_chat_url($context) {
        if (!has_capability('hybridteachvc/meet:view', $context)) {
            return '';
        }
        return '';
    }
}
