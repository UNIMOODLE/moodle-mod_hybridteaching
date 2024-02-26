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
 * @package    hybridteachvc_bbb
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_bbb;

use hybridteachvc_bbb\bbbproxy;
use hybridteachvc_bbb\meeting;
use mod_hybridteaching\helpers\roles;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');

use mod_bigbluebuttonbn\plugin;

/**
 * Class sessions.
 */
class sessions {
    /** @var object The session object. */
    protected $bbbsession;

    /**
     * Constructor for the class.
     *
     * @param mixed $htsessionid The session ID (optional)
     * @throws None
     * @return None
     */
    public function __construct($htsessionid = null) {
        if (!empty($htsessionid)) {
            $this->set_session($htsessionid);
        }
    }

    /**
     * Loads a session using the provided session ID.
     *
     * @param string $htsessionid The session ID.
     * @return object The loaded session.
     */
    public function load_session($htsessionid) {
        global $DB;
        return $DB->get_record('hybridteachvc_bbb', ['htsession' => $htsessionid]);
    }

    /**
     * Set the session with the given session ID.
     *
     * @param int $htsessionid The session ID to set.
     * @return void
     */
    public function set_session($htsessionid) {
        $this->bbbsession = $this->load_session($htsessionid);
    }

    /**
     * Retrieves the session for the current instance.
     *
     * @return object The session object.
     */
    public function get_session() {
        return $this->bbbsession;
    }

    /**
     * Creates a new session by calling the Hybrid Web Service's create_meeting function
     * and stores the data returned from it in the hybridteachvc_bbb table if the response
     * is not false.
     *
     * @param object $session the data to be passed to the create_meeting function
     * @param object $ht the hybridteaching instance
     * @return bool
     */
    public function create_unique_session_extended($session, $ht) {
        global $DB;

        $cm = get_coursemodule_from_instance('hybridteaching', $ht->id);
        $context = \context_module::instance($cm->id);
        if (!has_capability('hybridteachvc/bbb:use', $context)) {
            return;
        }

        $bbbconfig = $this->load_bbb_config($ht->config);
        if (!$bbbconfig) {
            return false;
        }

        $bbbproxy = new bbbproxy($bbbconfig);
        $meeting = new meeting($bbbconfig);
        $response = $meeting->create_meeting($session, $ht);

        if (!get_config('hybridteaching', 'reusesession') && $ht->reusesession == 0) {
            $response = $meeting->create_meeting($session, $ht);
        } else {
            $bbb = null;
            if (isset($session->vcreference)) {
                $bbb = $this->get_last_bbb_in_hybrid($session->hybridteachingid,
                    $session->groupid, $session->typevc, $session->vcreference,
                    $session->starttime);
            }
            if (!empty($bbb)) {
                $bbbvcexpired = $bbbproxy->is_meeting_running($bbb->meetingid);
                if ($bbbvcexpired) {
                    $response = [];
                    $response['meetingID'] = $bbb->meetingid;
                    $response['createTime'] = $bbb->createtime;
                    $response['returncode'] = 'SUCCESS';
                } else {
                    $response = $meeting->create_meeting($session, $ht);
                }
            } else {
                $response = $meeting->create_meeting($session, $ht);
            }
        }

        if (isset($response['returncode']) && $response['returncode'] == 'SUCCESS') {
            $bbb = $this->populate_htbbb_from_response($session, $response);
            $bbb->id = $DB->insert_record('hybridteachvc_bbb', $bbb);
            $this->bbbsession = $bbb;
            return true;
        } else {
            return false;
        }
    }

    public function update_session_extended($data) {
        // No requires action.
    }

    /**
     * Delete an extended session.
     *
     * @param object $htsession The session object.
     * @param int $configid The config ID.
     */
    public function delete_session_extended($htsession, $configid) {
        global $DB;
        $bbbconfig = $this->load_bbb_config($configid);
        if (!empty($bbbconfig)) {
            $bbb = $DB->get_record('hybridteachvc_bbb', ['htsession' => $htsession]);
            if (isset($bbb->meetingid)) {
                // If exists meeting, delete it.
                $meeting = new meeting($bbbconfig);
                if (isset($meeting)) {
                    $meeting->end_meeting($bbb->meetingid);
                }
            }
        }
        $DB->delete_records('hybridteachvc_bbb', ['htsession' => $htsession]);
    }

    /**
     * Populates a new stdClass object with relevant data from a BBB API response and returns it.
     *
     * @param mixed $data stdClass object containing htsession data
     * @param mixed $response stdClass object containing BBB API response data
     * @return stdClass $newbbb stdClass object containing relevant data
     */
    public function populate_htbbb_from_response($data, $response) {
        $newbbb = new \stdClass();
        $newbbb->htsession = $data->id;   // Session id.
        $newbbb->meetingid = $response['meetingID'];
        $newbbb->createtime = $response['createTime'];

        return $newbbb;
    }

    /**
     * Loads a BBB config based on the given config ID.
     *
     * @param int $configid The ID of the config to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The bbb config record on success, or false on failure.
     */
    public function load_bbb_config($configid) {
        global $DB;

        $sql = "SELECT bi.serverurl, bi.sharedsecret
                  FROM {hybridteaching_configs} hi
                  JOIN {hybridteachvc_bbb_config} bi ON bi.id = hi.subpluginconfigid
                 WHERE hi.id = :configid AND hi.visible = 1";

        $config = $DB->get_record_sql($sql, ['configid' => $configid]);
        return $config;
    }


    /**
     * Loads the BBB configuration from the session.
     *
     * @return mixed The loaded BBB configuration.
     */
    public function load_bbb_config_from_session() {
        global $DB;
        $sql = "SELECT h.config
                FROM {hybridteaching} h
                JOIN {hybridteaching_session} hs ON hs.hybridteachingid = h.id
                WHERE hs.id = :htsession";

        $configpartial = $DB->get_record_sql($sql, ['htsession' => $this->bbbsession->htsession]);
        $config = $this->load_bbb_config($configpartial->config);
        return $config;
    }

    /**
     * Get the zone access for the user.
     *
     * This function calculates the necessary data for the access zone,
     * checks if the role is for starting a meeting or joining a meeting,
     * and returns the access URL (either starturl or joinurl) based on the role.
     *
     * @param bool $userismoderator  Whether or not user is moderator.
     * @return array|null Returns an array with the zone access information or null if there is no session available.
     */
    public function get_zone_access($userismoderator = false) {
        global $USER;

        if ($this->bbbsession) {
            $bbbconfig = $this->load_bbb_config_from_session();
            if (!$bbbconfig) {
                // No exists config bbb or its hidden.
                return [
                    'returncode' => 'FAILED',
                    'message' => get_string('noconfig', 'hybridteaching'),
                ];
            }

            $bbbproxy = new bbbproxy($bbbconfig);

            if ($userismoderator) {
                $role = 'MODERATOR';
            } else {
                $role = 'VIEWER';
            }

            $url = $bbbproxy->get_join_url(
                $this->bbbsession->meetingid,
                $USER->username,
                $role,
                null, // A token.
                $USER->id,
                $this->bbbsession->createtime
            );

            $array = [
                'id' => $this->bbbsession->id,
                'ishost' => true,
                'url' => base64_encode($url),
            ];
            $meetinginfo = $bbbproxy->get_meeting_info($this->bbbsession->meetingid);
            if (isset ($meetinginfo['returncode']) && $meetinginfo['returncode'] == 'FAILED') {
                return $meetinginfo;
            }
            return $array;
        } else {
            return [
                'returncode' => 'FAILED',
                'message' => get_string('error_unable_join', 'hybridteaching'),
            ];
        }
    }

    /**
     * Get the recording URL.
     *
     * @param object $context The context object.
     * @return string The URL of the recording.
     */
    public function get_recording($context) {
        if (!has_capability('hybridteachvc/bbb:view', $context)) {
            return;
        }

        $bbbconfig = $this->load_bbb_config_from_session();
        if (!$bbbconfig) {
            return;
        }

        $bbbproxy = new bbbproxy($bbbconfig);
        $response = $bbbproxy->get_url_recording_by_recordid($this->bbbsession->recordingid);
        if ($response['returncode'] == 'SUCCESS') {
            return $response;
        }
        return;
    }

    /**
     * Retrieves the role of the user in a meeting.
     *
     * @param object $session The session object for the meeting.
     * @return string The role of the user in the meeting ('VIEWER' or 'MODERATOR').
     */
    public static function get_user_meeting_role($session) : String {
        global $DB, $USER;

        $meetingrole = 'VIEWER';
        $hybridteaching = $DB->get_record('hybridteaching', ['id' => $DB->get_field('hybridteaching_session', 'hybridteachingid',
            ['id' => $session->htsession], IGNORE_MISSING), ], 'id, course, participants', IGNORE_MISSING);
        list($course, $cm) = get_course_and_cm_from_instance($hybridteaching->id, 'hybridteaching');
        $context = \context_module::instance($cm->id);
        $role = roles::is_moderator($context, json_decode($hybridteaching->participants, true), $USER->id);
        if ($role) {
            $meetingrole = 'MODERATOR';
        }
        return $meetingrole;
    }

    /**
     * Retrieves the join URL for a BigBlueButton session.
     *
     * @param object $session The session object.
     * @throws Exception If the join URL cannot be retrieved.
     * @return string The join URL for the session.
     */
    public function get_join_url($session) {
        global $DB, $USER;

        $bbbconfig = $this->load_bbb_config_from_session();
        $bbbproxy = new bbbproxy($bbbconfig);

        $bbbsess = $DB->get_record('hybridteachvc_bbb', ['htsession' => $session->id]);
        $role = self::get_user_meeting_role($bbbsess);
        $joinurl = $bbbproxy->get_join_url($bbbsess->meetingid, $USER->username, $role);

        return $joinurl;
    }

    /**
     * Get the last BBB in hybrid teaching session based on the provided parameters.
     *
     * @param int $htid The hybrid teaching ID
     * @param int $groupid The group ID
     * @param string $typevc The type of video conference
     * @param int $vcreference The video conference reference
     * @param string $starttime The start time
     * @return object The last BBB in the hybrid teaching session
     */
    public function get_last_bbb_in_hybrid($htid, $groupid, $typevc, $vcreference, $starttime) {
        global $DB;

        $sql = 'SELECT hm.*
                  FROM {hybridteachvc_bbb} hm
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
     * @param object $context
     */
    public function get_chat_url ($context) {
        if (!has_capability('hybridteachvc/bbb:view', $context)) {
            return '';
        }
        return '';
    }
}
