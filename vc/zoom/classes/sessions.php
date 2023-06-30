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

require_once($CFG->dirroot.'/mod/hybridteaching/vc/zoom/classes/webservice.php');

class sessions {
    /**
     * Creates a unique session extended for a Zoom meeting instance.
     *
     * @param mixed $data The data to create the meeting.
     * @throws Exception If the meeting creation fails.
     */
    public function create_unique_session_extended($session,$ht) {
        global $DB;

        $zoominstance = $this->load_zoom_instance($ht->instance);
   
        $service = new mod_hybrid_webservice($zoominstance);
        $response = $service->create_meeting($session,$ht);
        if ($response != false) {
            $zoom = $this->populate_htzoom_from_response($session, $response);
            $zoom->id = $DB->insert_record('hybridteachvc_zoom', $zoom);  
        }
    }
    
    /**
     * Updates a Zoom session with new data.
     *
     * @param mixed $data The data to update the Zoom session with.
     * @throws Exception If there is an error updating the Zoom session.
     * @return string An error message if there was an error updating the Zoom session, otherwise null.
     */
    public function update_session_extended($data, $ht) {
        global $DB;
        $errormsg = '';
        $zoominstance = $this->load_zoom_instance($ht->instance);
        $zoom = $DB->get_record('hybridteachvc_zoom', ['htsession' => $data->id]);
        //if is created zoom, change in zoom with webservice
        if ($zoom){
            $zoom = (object) array_merge((array) $zoom, (array) $data);
            $service = new mod_hybrid_webservice($zoominstance);
            $service->update_meeting($zoom,$ht);
        }
        return $errormsg;
    }

    /**
     * Deletes a Zoom session extended and all its associated records from the database.
     *
     * @param mixed $htsession the session identifier
     * @param mixed $instanceid the instance identifier
     * @throws Exception if an error occurs while deleting the session
     */
    public function delete_session_extended($htsession, $instanceid) {
        global $DB;
        $zoominstance = $this->load_zoom_instance($instanceid);
        $service = new mod_hybrid_webservice($zoominstance);        
        $zoom = $DB->get_record('hybridteachvc_zoom', ['htsession' => $htsession]);
        $service->delete_meeting($zoom->meetingid, 0);
        $DB->delete_records('hybridteachvc_zoom', ['htsession' => $htsession]);
    }

    /**
     * Deletes all sessions related to a specific instance of a zoom meeting.
     *
     * @param mixed $htsession The session data to delete.
     * @param mixed $instanceid The instance id to delete sessions for.
     * @throws Exception If there is an error deleting the sessions.
     * @return void
     */
    public function delete_all_sessions_extended($htsession, $instanceid) {
        global $DB;
        $zoominstance = $this->load_zoom_instance($instanceid);
        $service = new mod_hybrid_webservice($zoominstance);   
        $zooms = $DB->get_records('hybridteachvc_zoom', array('htsession' => $htsession));
        foreach ($zooms as $zoom) {
            $service->delete_meeting($zoom->meetingid, 0);
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
    function populate_htzoom_from_response($data, $response) {        
        $newzoom = new stdClass();
        $newzoom->htsession = $data->id;   //session id
        $newzoom->meetingid = $response->id;
        $newzoom->hostid = $response->host_id;
        $newzoom->hostemail = $response->host_email;
        $newzoom->starturl = $response->start_url;
        $newzoom->joinurl = $response->join_url;
        $newzoom->optionhostvideo = $response->settings->host_video;
        $newzoom->optionparticipantsvideo = $response->settings->participant_video;
        $newzoom->existsonzoom = 1;
        
        return $newzoom;
    }

    /**
     * Loads a Zoom instance based on the given instance ID.
     *
     * @param int $instanceid The ID of the instance to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The Zoom instance record on success, or false on failure.
     */
    public function load_zoom_instance($instanceid) {
        global $DB;

        $sql = "SELECT zi.accountid, zi.clientid, zi.clientsecret,
                       zi.emaillicense
                  FROM {hybridteaching_instances} hi
                  JOIN {hybridteachvc_zoom_instance} zi ON zi.id = hi.subplugininstanceid
                 WHERE hi.id = :instanceid AND hi.visible = 1";

        $zoominstance = $DB->get_record_sql($sql, ['instanceid' => $instanceid]);
        return $zoominstance;
    }
}
