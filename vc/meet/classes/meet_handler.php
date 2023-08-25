<?php

class meet_handler {
   public $client;

    public function __construct($configmeet) {
        try {         
            $this->createclient($configmeet);
            $this->client->setApprovalPrompt('consent');
            $this->client->setAccessType('offline');     
            $this->client->setAccessToken($configmeet->token);          
            if ($this->client->getAccessToken()) {               
                if($this->client->isAccessTokenExpired()) {    
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $this->saveToken($configmeet);
                }
            }      
        } catch(Google_Service_Exception $e) {
            print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
        }catch (Exception $e) {
            print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
        }
    }

    public function createclient($configmeet) {
        require_once (__DIR__.'/../vendor/autoload.php');

        $this->client = new Google_Client();
        $this->client->setClientId($configmeet->clientid);
        $this->client->setClientSecret($configmeet->clientsecret);
        $this->client->setScopes(Google_Service_Calendar::CALENDAR);
        $this->client->setApplicationName(get_string('pluginname','hybridteaching'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        return $this->client;    
    }

    public function setredirecturi($url) {
        $this->client->setRedirectUri($url);
        return $this->client;
    }

    public function create_meeting_event($meet) {
        global $USER;

        $service = new Google_Service_Calendar($this->client);
        $calendarid = 'primary';
        
        $sessionstart = new \DateTime();
        $sessionstart->setTimestamp($meet->starttime);

        $sessionend = new \DateTime();
        $sessionend->setTimestamp($meet->starttime + $meet->duration);

        $starttime = $sessionstart->format('H:i:s');
        $sdate = $sessionstart->format('Y-m-d');

        $endtime = $sessionend->format('H:i:s');
        $edate = $sessionend->format('Y-m-d');

        $startdatetime = $sdate . 'T' . $starttime;
        $enddatetime = $edate . 'T' . $endtime;

        $timezone = get_user_timezone($USER->timezone);

        $event = new Google_Service_Calendar_Event(array(
            'summary' => $meet->name,
            'description' => $meet->description,
            'start' => array(
                'dateTime' => $startdatetime,
                'timeZone' => $timezone
            ),
            'end' => array(
                'dateTime' => $enddatetime,
                'timeZone' => $timezone
            ),
            'conferenceData' => array(
                'createRequest' => array(
                    'requestID' => 'req_'.time()
                )
            )
        ));

        $conferenceData = new Google\Service\Calendar\ConferenceData();
        $req = new Google\Service\Calendar\CreateConferenceRequest();
        $req->setRequestId("req_".time());
        $conferenceData->setCreateRequest($req);
        $event->setConferenceData($conferenceData);

        $event = $service->events->insert($calendarid, $event, array('conferenceDataVersion' => 1));

        return $event;
    }

    public function saveToken($configmeet) {
        global $DB;
        $configmeet->token = json_encode($this->client->getAccessToken());;
        $DB->update_record('hybridteachstore_youtube_con',$configmeet);
    }
}

