<?php

require_once __DIR__ . '/../vendor/autoload.php'; 

// Include the Microsoft Graph classes  
use Microsoft\Graph\Graph;  
use Microsoft\Graph\Model; 
class teams_handler{


  protected $config;

   /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($config) {
        $this->config = $config;  //api with credentials, url...
    }

    public function createclient($config){

      
    }

    public function refreshtoken(){
      global $DB;
      //REFRESCAR TOKEN DE AUTORIZACIÓN

      $guzzle = new \GuzzleHttp\Client();

      $url = 'https://login.microsoftonline.com/' . $this->config->tenantid . '/oauth2/v2.0/token';

      $tokenderefresco = json_decode($guzzle->post($url, [
          'form_params' => [
              'client_id' => $this->config->clientid,
              'grant_type' => 'refresh_token',
              'client_secret' => $this->config->clientsecret,
              'refresh_token' => $this->config->refreshtoken,
          ],
      ])->getBody()->getContents());

      //save token:
      $this->config->accesstoken=$tokenderefresco->access_token;
      $this->config->refreshtoken=$tokenderefresco->refresh_token;
      $DB->update_record('hybridteachvc_teams_config',$this->config);
    }

    public function createmeeting($session,$ht){

        $this->refreshtoken();

        $graph = new Graph();  
        /*echo "<pre>";
        var_dump($this->config);
        echo "</pre>";*/
        $graph->setAccessToken($this->config->accesstoken);  

        $allowAttendeeToEnableCamera = $ht->disablecam ? false : true;
        $allowAttendeeToEnableMic = $ht->disablemic ? false : true;
        $allowMeetingChat = $ht->disablepublicchat ? 'disabled' : 'enabled';
        if ($ht->userecordvc && $ht->initialrecord){
          $recordAutomatically = true;  
        } else {
          $recordAutomatically = false;
        }
        $startDateTime = date('Y-m-d\TH:i:s',$session->starttime);
        $endDateTime = date('Y-m-d\TH:i:s',($session->starttime + $session->duration));
        $startDateTime .= '.00+00:00';
        $endDateTime .= '.00+00:00';

        $data = [
          'subject' => $session->name,
          'startDateTime' => $startDateTime,
          'endDateTime' => $endDateTime,
          //'startDateTime' => '2023-09-08T20:35:00.00+01:00',
          //'endDateTime' => '2023-09-08T21:35:00.00+01:00',
          'allowAttendeeToEnableCamera' => $allowAttendeeToEnableCamera,
          'allowAttendeeToEnableMic' => $allowAttendeeToEnableMic,
          'allowMeetingChat' => $allowMeetingChat,
          'recordAutomatically' => $recordAutomatically,
      ];


try{

        $graphresponse = $graph
        ->createRequest("POST", "/me/onlineMeetings")
        ->attachBody($data)
        ->setReturnType(Model\OnlineMeeting::class)
        ->execute();
        /*
        echo "<pre>";
          var_dump($graphresponse);    
        echo "</pre>";
        */
    }catch (Exception $e) {
        print "Caught Teams service Exception ".$e->getCode(). " message is ".$e->getMessage();
        print "Stack trace is ".$e->getTraceAsString();
    }        
            
    }

}