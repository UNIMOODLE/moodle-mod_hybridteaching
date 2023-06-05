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

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot.'/mod/hybridteaching/vc/hybridbbb/classes/webservice.php');
use mod_bigbluebuttonbn\meeting;
use  mod_bigbluebuttonbn\plugin;


class sessions extends sessions_controller {
    /**
     * Creates a new session by calling the Hybrid Web Service's create_meeting function
     * and stores the data returned from it in the hybridteaching_zoom table if the response
     * is not false.
     *
     * @param mixed $data the data to be passed to the create_meeting function
     * @throws 
     * @return mixed the response from the create_meeting function
     */
    public function create_session($data) {
        global $DB, $USER;

        // As it is a new activity, assign passwords. 
        //process_pre_save:   //mod_helper::process_pre_save($moduleinstance);

        $session = new stdClass();

        $session->hybridteachingid=$data->hybridteachingid;
        //$session->meetingid = 0;
        $session->meetingid=meeting::get_unique_meetingid_seed();
        $session->moderatorpass = plugin::random_password(12);
        $session->viewerpass = plugin::random_password(12, $session->moderatorpass);
        [$session->guestlinkuid, $session->guestpassword] = plugin::generate_guest_meeting_credentials();
        $session->starttime=$data->starttime;
        $session->duration=$data->duration;

        $session->timecreated = time();
        $session->timemodified = 0;
        $session->createdby = $USER->id;

        $session->id = $DB->insert_record('hybridteaching_bbb', $session);


        return $session;
    }
    
    public function update_session($data) {
        global $DB, $USER;
        $errormsg = '';
        $session = new stdClass();
        $session->id = $data->id;
        $session->name = $data->name;
        $session->timemodified = time();
        $session->modifiedby = $USER->id;
        if (!$DB->update_record($this->table, $session)) {
            $errormsg = 'errorupdatesession';
        }
        return $errormsg;
    }

    /**
     * Deletes a session and its corresponding Zoom meeting via the mod_hybrid_webservice.
     *
     * @param mixed $id The ID of the session to delete.
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    public function delete_session($id) {
        global $DB;

        $bbb = $DB->get_record('hybridteaching_bbb', array('hybridteachingid' => $id));
        $meeting = new meeting($bbb);
        $meeting->end_meeting(meetingid, $bbb->moderatorpass);



        $service = new mod_hybrid_webservice();        
        $zoom = $DB->get_record('hybridteaching_zoom', array('hybridteachingid' => $id));
        $service->delete_meeting($zoom->meetingid, 0); 
        parent::delete_session($id);
    }

    /**
     * Deletes all Zoom meetings associated with a given hybridteaching instance.
     *
     * @param mixed $moduleinstance the instance of the hybrid teaching
     * @throws Exception if an error occurs while deleting a meeting
     * @return void
     */
    public function delete_all_sessions($moduleinstance) {
        global $DB;
        $service = new mod_hybrid_webservice();        
        $zooms = $DB->get_records('hybridteaching_zoom', array('hybridteachingid' => $moduleinstance->id));
        foreach ($zooms as $zoom) {
            $service->delete_meeting($zoom->meetingid, 0);
        }

        $DB->delete_records('hybridteaching_zoom', array('hybridteachingid' => $moduleinstance->id));
    }

    /**
     * Populates a stdClass object with Zoom meeting details from a given response object. 
     *
     * @param object $module The module object containing the Zoom meeting.
     * @param object $response The response object from a Zoom API call.
     * @return object The populated stdClass object with Zoom meeting details.
     */
    function populate_hybridzoom_from_response($module, $response) {
        global $USER;
        
        $newzoom = new stdClass();
        $newzoom->hybridteachingid = $module->id;
        $newzoom->meetingid = $response->id;
        // Provisional name
        $newzoom->name = $module->name;
        $newzoom->hostid = $response->host_id;
        $newzoom->hostemail = $response->host_email;
        $newzoom->starturl = $response->start_url;
        $newzoom->joinurl = $response->join_url;
        $newzoom->optionhostvideo = $response->settings->host_video;
        $newzoom->optionparticipantsvideo = $response->settings->participant_video;
        $newzoom->existsonzoom = 1;
        $newzoom->visible = 1;
        $newzoom->timecreated = time();
        $newzoom->createdby = $USER->id;

        if (isset($response->start_time)) {
            $newzoom->starttime = strtotime($response->start_time);
        }

        if (isset($response->duration)) {
            $newzoom->duration = $response->duration * 60;
        }
        
        return $newzoom;
    }
}
