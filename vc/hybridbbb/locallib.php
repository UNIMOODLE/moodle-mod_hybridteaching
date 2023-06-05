<?php

global $CFG;

use mod_bigbluebuttonbn\local\helpers\mod_helper;
//use mod_bigbluebuttonbn\meeting;
use mod_bigbluebuttonbn\plugin;

class VcInstance {

    function add_instance($moduleinstance){
        global $CFG, $DB;

        
        
        // As it is a new activity, assign passwords. 
        //process_pre_save:   //mod_helper::process_pre_save($moduleinstance);
        $moduleinstance->meetingid = 0;
        $moduleinstance->timecreated = time();
        $moduleinstance->timemodified = 0;
        $moduleinstance->moderatorpass = plugin::random_password(12);
        $moduleinstance->viewerpass = plugin::random_password(12, $moduleinstance->moderatorpass);

        $moduleinstance->meetingid=$this->get_unique_meetingid_seed();
        [$moduleinstance->guestlinkuid, $moduleinstance->guestpassword] =plugin::generate_guest_meeting_credentials();

        /*echo "moderatorpass:".$moduleinstance->moderatorpass;
        echo "<br>viewerpass:".$moduleinstance->moderatorpass;
        echo "<br>meetingid:".$moduleinstance->meetingid;
        echo "<br>guestlinkuid:".$moduleinstance->guestlinkuid;
        echo "<br>guestpassword:".$moduleinstance->guestpassword;
        */

        $moduleinstance->id = $DB->insert_record('hybridteaching_bbb', $moduleinstance);


        //----------------------------------


    
        //llamada a la bbdd (inserción en bbdd)
        $result=0;
        if ($response!=false){
            //grabar registro de vc             
            $result=$this->insert($moduleinstance,$response);
            $result=$response;
        }
        else {
            $result=false;
        }
        return $result;
    
    }

    function insert($moduleinstance, $response){
        global $DB;
        $zoom=$this->populate_hybridzoom_from_response($moduleinstance,$response);
        $zoom->id = $DB->insert_record('hybridteaching_zoom',$zoom);     
    }

    function delete_instance($moduleinstance){
        global $CFG, $DB;

        

        //llamada al webservice (eliminación en zoom)
        require_once($CFG->dirroot.'/mod/hybridteaching/vc/hybridzoom/classes/webservice.php');
        $service = new mod_hybrid_webservice();        

        $zooms=$DB->get_records('hybridteaching_zoom',array('hybridteachingid'=>$moduleinstance->id));
        foreach ($zooms as $zoom){
            $response=$service->delete_meeting($zoom->meetingid, 0); //0 indica que es meetings y no es webinar    
        }

        //esto borra todos los registros de vez del hybridzoom
        $DB->delete_records('hybridteaching_zoom',array('hybridteachingid'=>$moduleinstance->id));
                   
        return $result;
    }


    function populate_hybridzoom_from_response($module,$response){

        $newzoom = clone $module;

        $newzoom->hybridteachingid=$module->id;
        $newzoom->meetingid = $response->id;
        $newzoom->hostid = $response->host_id;
        $newzoom->hostemail = $response->host_email;
        $newzoom->starturl = $response->start_url;
        $newzoom->joinurl = $response->join_url;
        $newzoom->optionhostvideo = $response->settings->host_video;
        $newzoom->optionparticipantsvideo = $response->settings->participant_video;
        $newzoom->existsonzoom = 1;
        if (isset($response->start_time)) {
            $newzoom->starttime = strtotime($response->start_time);
        }
        if (isset($response->duration)) {
            $newzoom->duration = $response->duration * 60;
        }
        
        return $newzoom;
    }


    public static function get_unique_meetingid_seed() {
        global $DB;
        do {
            $encodedseed = sha1(plugin::random_password(12));
            $meetingid = (string) $DB->get_field('hybridteaching_bbb', 'meetingid', ['meetingid' => $encodedseed]);
        } while ($meetingid == $encodedseed);
        return $encodedseed;
    }

}