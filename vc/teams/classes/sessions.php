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

namespace hybridteachvc_teams;

defined('MOODLE_INTERNAL') || die();

global $CFG;
use hybridteachingvc_teams;
require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
//require_once($CFG->dirroot.'/mod/hybridteaching/vc/teams/classes/teams_handler.php');

class sessions {
    protected $teamssession;

    public function __construct($htsessionid = null) {
        if (!empty($htsessionid)) {
            $this->teamssession = $this->load_session($htsessionid);
        }
    }

    public function load_session($htsessionid) {
        global $DB;
        $this->teamssession = $DB->get_record('hybridteachvc_teams', ['htsession' => $htsessionid]);
        return $this->teamssession;
    }

    public function get_session() {
        return $this->teamssession;
    }

    public function set_session($htsessionid) {
        $this->teamssession = $this->load_session($htsessionid);
    }

    /**
     * Creates a new session by calling the Hybrid Web Service's create_meeting function
     * and stores the data returned from it in the hybridteachvc_teams table if the response
     * is not false.
     *
     * @param mixed $session the data to be passed to the create_meeting function
     * @throws 
     * @return mixed the response from the create_meeting function
     */
    public function create_unique_session_extended($session, $ht) {
        global $DB;

        $teamsconfig = $this->load_teams_config($ht->config);
        if ($teamsconfig){
            $teams = new teams_handler($teamsconfig);
            $response = $teams->createmeeting($session,$ht);

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
        else {
            //ARREGLAR ESTE MENSAJE
            return "error, no existe la configuración";
        }
    }
    
    public function update_session_extended($data) {
        
    }

    /**
     * Deletes a session and its corresponding meeting via the webservice (if exists).
     *
     * @param mixed $id The ID of the session to delete.
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    public function delete_session_extended($htsession, $configid) {
        global $DB;
        $teamsconfig = $this->load_teams_config($configid);      
        $teams = $DB->get_record('hybridteachvc_teams', ['htsession' => $htsession]);

        if ($teams){
            //si existe ya la reunión teams, eliminarla
            $teams_handler = new \teams_handler($teamsconfig);
            $teams_handler->deletemeeting($teams->meetingid);

            $DB->delete_records('hybridteachvc_teams', ['htsession' => $htsession]);
        }


    }

    /**
     * Populates a new stdClass object with relevant data from a BBB API response and returns it.
     *
     * @param mixed $data stdClass object containing htsession data
     * @param mixed $response stdClass object containing BBB API response data
     * @return stdClass $newbbb stdClass object containing relevant data
     */
    public function populate_htteams_from_response($data, $response) {        
        $newteams = new \stdClass();
        /*$newteams->htsession = $data->id;   //session id
        $newteams->meetingid = $response['meetingID'];
        //.....
        $newteams->createtime = $response['createTime'];
        */
        return $newteams;

    }

    /**
     * Loads a BBB config based on the given config ID.
     *
     * @param int $configid The ID of the config to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The Zoom config record on success, or false on failure.
     */
    public function load_teams_config($configid) {
        global $DB;

        $sql = "SELECT tc.*
                  FROM {hybridteaching_configs} hc
                  JOIN {hybridteachvc_teams_config} tc ON tc.id = hc.subpluginconfigid
                 WHERE hc.id = :configid AND hc.visible = 1";

        $config = $DB->get_record_sql($sql, ['configid' => $configid]);
        return $config;
    }

    function get_zone_access() {
        if ($this->teamssession) {
            $array = [
                'id' => $this->teamssession->id,
                'ishost' => true,
                'isaccess' => true,
                'url' => base64_encode($this->teamssession->joinurl),
            ];
            return $array;
        } else {
            return null;
        }
    }
}
