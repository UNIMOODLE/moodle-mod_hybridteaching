<?php

namespace hybridteachvc_bbb;

//use mod_bigbluebuttonbn\local\proxy\bigbluebutton_proxy;
use mod_bigbluebuttonbn\plugin;
use hybridteachvc_bbb\bbbproxy;


class meeting  {

    protected $bbbinstance;

    /**
     * Constructor for the meeting object.
     *
     * @param  $data
    */
    public function __construct($bbbinstance) {
        $this->bbbinstance = $bbbinstance;  //api with credentials, url...
    }

    /**
     * Creates a bigbluebutton meeting, send the message to BBB and returns the response in an array.
     *
     * @return array
     */
    public function create_meeting ($session,$ht) {
        $bbbproxy=new bbbproxy($this->bbbinstance);
        $bbbproxy->require_working_server();   
        $data = $this->create_meeting_data($session, $ht);
        $metadata = $this->create_meeting_metadata($session, $ht);
        //$presentation = $this->instance->get_presentation_for_bigbluebutton_upload(); // The URL must contain nonce.
        //$presentationname = $presentation['name'] ?? null;
        //$presentationurl = $presentation['url'] ?? null;
        $presentationname=null;
        $presentationurl=null;
        
        $response = $bbbproxy->create_meeting($data, $metadata, $presentationname, $presentationurl,$this->bbbinstance);
      
        // New recording management: Insert a recordingID that corresponds to the meeting created.
        /*if ($this->instance->is_recorded()) {
            $recording = new recording(0, (object) [
                'courseid' => $this->instance->get_course_id(),
                'bigbluebuttonbnid' => $this->instance->get_instance_id(),
                'recordingid' => $response['internalMeetingID'],
                'groupid' => $this->instance->get_group_id()]
            );
            $recording->create();
        }*/

        return $response;
    }
    /**
     * Helper to prepare data used for create meeting.
     * Populate the data from session and ht to BBB meeting.
     * @todo moderatorPW and attendeePW will be removed from create after release of BBB v2.6.
     *
     * @return array
     */
    protected function create_meeting_data($session, $ht) {

        $meetingid=meeting::get_unique_meetingid_seed();
        $moderatorpass = plugin::random_password(12);
        $viewerpass = plugin::random_password(12, $moderatorpass);


        $data = ['meetingID' =>$meetingid,
                'name' => \mod_bigbluebuttonbn\plugin::html2text($session->name, 64),
                'attendeePW' => $viewerpass,
                'moderatorPW' => $moderatorpass,
            //    'logoutURL' => $this->instance->get_logout_url()->out(false),
        ];

        /*
        info: estados iniciales añadidos en la vc de BBB:
                            $ht->userslimit
                            $ht->disablecam
                            $ht->disablemic
                            $ht->disableprivatechat
                            $ht->disablepublicchat
                            $ht->disablenote
                            $ht->hideuserlist
                            $ht->blockroomdesign
                            $ht->ignorelocksettings
                            $ht->initialrecord
                            $ht->hiderecordbutton
        */

        $data['duration'] = $session->duration;
        $data['record'] = $ht->userecordvc ? 'true' : 'false';
        if ($data['record'] == 'true' ){
            if ($ht->initialrecord) {
                $data['autoStartRecording'] = 'true';    
            }
            $data['allowStartStopRecording'] = $ht->hiderecordbutton ? 'false' : 'true';
        }
        $data['muteOnStart'] = 'true';
        if ($ht->userslimit > 0){
            $data['maxParticipants'] = $ht->userslimit;
        }
        
        $data['lockSettingsDisableCam'] = $ht->disablecam ? 'true' : 'false';
        $data['lockSettingsDisableMic'] = $ht->disablemic ? 'true' : 'false';
        $data['lockSettingsDisablePrivateChat'] = $ht->disableprivatechat ? 'true' : 'false';
        $data['lockSettingsDisablePublicChat'] = $ht->disablepublicchat ? 'true' : 'false';
        $data['lockSettingsDisableNotes'] = $ht->disablenote ? 'true' : 'false';
        $data['lockSettingsHideUserList'] = $ht->hideuserlist ? 'true' : 'false';
        $data['lockSettingsLockOnJoin'] = $ht->ignorelocksettings ? 'false' : 'true';
        if ($ht->blockroomdesign){
            $data['disabledFeatures'] = 'layouts';
        }

        return $data;
    }


    /**
     * Helper for preparing metadata used while creating the meeting.
     *
     * @return array
     */
    protected function create_meeting_metadata ($session, $ht) {
        // Create standard metadata.
        $origindata = $this->get_origin_data();
        $metadata = [
            'bbb-origin' => $origindata->origin,
            'bbb-origin-version' => $origindata->originVersion,
            'bbb-origin-server-name' => $origindata->originServerName,
            'bbb-origin-server-common-name' => $origindata->originServerCommonName,
            'bbb-origin-tag' => $origindata->originTag,
            'bbb-context' => $ht->name,
            'bbb-context-id' => $ht->id,
            'bbb-context-name' => trim(html_to_text($ht->name, 0)),
            'bbb-context-label' => trim(html_to_text($ht->name, 0)),
            'bbb-recording-name' => plugin::html2text($ht->name, 64),
            'bbb-recording-description' => plugin::html2text($session->description,64),
        ];

        //REVISAR: esta parte probablemente se eliminará. 
        //SEGURAMENTE LO HAREMOS CON UN CRON PARA DESCARGAR LAS GRABACIONES ACTIVAS, DE SIMILAR MANERA QUE CON ZOOM.
        //OJO: CON LA API NO HAY OPCIÓN DIRECTA PARA DESCARGAR UNA GRABACIÓN
            //https://www.youtube.com/watch?v=n1NbII0bH40&t=1219s
        //revisar /classes/recordings.php, función sync_pending_recordings_from_server
        
        // Special metadata for recording processing.
        /*
        if ((boolean) config::get('recordingstatus_enabled')) {
            $metadata["bn-recording-status"] = json_encode(
                [
                    'email' => ['"' . fullname($USER) . '" <' . $USER->email . '>'],
                    'context' => $this->instance->get_view_url(),
                ]
            );
        }
        if ((boolean) config::get('recordingready_enabled')) {
            $metadata['bn-recording-ready-url'] = $this->instance->get_record_ready_url()->out(false);
        }
        if ((boolean) config::get('meetingevents_enabled')) {
            $metadata['analytics-callback-url'] = $this->instance->get_meeting_event_notification_url()->out(false);
        }
        */
        return $metadata;
    }


    /**
     * Send an end meeting message to BBB server
    */
    public function end_meeting($meetingid, $moderatorpassword) {
        $bbbproxy=new bbbproxy($this->bbbinstance);
        $bbbproxy->end_meeting($meetingid,$moderatorpassword);

    }


    //SE PODRÍA ADAPTAR ESTO PARA:
    // SI AL HACER get_join_url EN bbbproxy.php NO ESTÁ EL MEET CREADO, ENTONCES SE CREA.
    //HABRÍA QUE ACTUALIZAR/INSERTAR EN mdl_hybridteachvc_bbb EL REGISTRO 
    /**
     * Helper to join a meeting.
     *
     *
     * It will create the meeting if not already created.
     *
     * @param instance $instance
     * @param int $origin
     * @return string
     * @throws meeting_join_exception this is sent if we cannot join (meeting full, user needs to wait...)
     */
    /*public function join_meeting($instance, $origin = logger::ORIGIN_BASE): string {
        // See if the session is in progress.
        $meeting = new meeting($instance);
        // As the meeting doesn't exist, try to create it.
        if (empty($meeting->get_meeting_info(true)->createtime)) {
            $meeting->create_meeting();
        }
        return $meeting->join($origin);
    }*/

    /*
    * Get meeting recordings
    */
    public function get_meeting_recordings($meetingid){
        $bbbproxy=new bbbproxy($this->bbbinstance);
        return $bbbproxy->get_meeting_recording($meetingid);

    }

    /**
     * Helper function returns a sha1 encoded string that is unique and will be used as a seed for meetingid.
     *
     * @return string
     */
    public static function get_unique_meetingid_seed() {
        global $DB;
        do {
            $encodedseed = sha1(plugin::random_password(12));
            $meetingid = (string) $DB->get_field('hybridteachvc_bbb', 'meetingid', ['meetingid' => $encodedseed]);
        } while ($meetingid == $encodedseed);
        return $encodedseed;
    }


    /**
     * Get information about the origin.
     *
     * @return stdClass
     */
    public function get_origin_data()  {
        global $CFG;

        $parsedurl = parse_url($CFG->wwwroot);
        return (object) [
            'origin' => 'Moodle',
            'originVersion' => $CFG->release,
            'originServerName' => $parsedurl['host'],
            'originServerUrl' => $CFG->wwwroot,
            'originServerCommonName' => '',
            'originTag' => sprintf('moodle-mod_bigbluebuttonbn (%s)', get_config('hybridteachvc_bbb', 'version')),
        ];
    }

}
