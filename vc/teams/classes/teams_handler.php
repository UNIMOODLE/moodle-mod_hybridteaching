<?php
namespace hybridteachvc_teams;

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
      }catch (Exception $e) {
          print "Caught Teams service Exception ".$e->getCode(). " message is ".$e->getMessage();
          print "Stack trace is ".$e->getTraceAsString();
      }     
      $result = json_decode(json_encode($graphresponse), true);

      return $result;
    }   
    
    
    public function deletemeeting($meetingid){
      $this->refreshtoken();

      $graph = new Graph();  
      $graph->setAccessToken($this->config->accesstoken);  

      $data = [
        'meetingId' => $meetingid,
      ];

      try{
      /*$graphServiceClient = new GraphServiceClient($$this->config->accesstoken, 'OnlineMeetings.ReadWrite');
        $graphServiceClient = $graphServiceClient->me()->onlineMeetings()
        ->byOnlineMeetingId($meetingid)->delete()->wait();
*/
        $graphresponse = $graph
          ->createRequest("DELETE", "/me/onlineMeetings")
          ->attachBody($data)
          ->setReturnType(Model\OnlineMeeting::class)
          ->execute();
      }catch (Exception $e) {
          print "Caught Teams service Exception ".$e->getCode(). " message is ".$e->getMessage();
          print "Stack trace is ".$e->getTraceAsString();
          exit;
      }   

    }

    public function get_meeting_recordings($meetingid, $organizer){

        $this->refreshtoken();
        $graph = new Graph();  
        $graph->setAccessToken($this->config->accesstoken);  
        $meetingid="MSo4ZjY0Y2U3My03NjVkLTRhMzUtOTgyMS1jNDdhY2RmNDNlZjMqMCoqMTk6bWVldGluZ19aalF6TVdKbU1XUXRORFZtWVMwME1qVmxMVGczWkRFdFlURTFPVGcyTTJGbU1HRmxAdGhyZWFkLnYy";
        $data = [
          'meetingId' => $meetingid,
        ];
try{
  
  /*
  //https://learn.microsoft.com/en-us/answers/questions/1000346/need-to-fetch-the-download-the-meeting-recording-f  
  Úselo GET /chats/{chat-id}/messages/{message-id}para obtener callRecordingDisplayName.
  A continuación, utilice GET /users/{user-id}/drive/root:/Recordings/{callRecordingDisplayName}:/contentpara descargar las grabaciones de la reunión.

  //https://learn.microsoft.com/en-us/graph/api/onlinemeeting-list-recordings?view=graph-rest-beta&tabs=http
  //https://devblogs.microsoft.com/microsoft365dev/microsoft-teams-recording-and-transcript-apis-billing-in-public-preview/


  //https://learn.microsoft.com/en-us/microsoftteams/platform/graph-api/meeting-transcripts/fetch-id
  */

        $graphresponse = $graph
        ->setApiVersion("beta")
          //->createRequest("GET", "/me/onlineMeetings/$meetingid/recordings")
          ->createRequest("GET", "/users/$organizer/onlineMeetings/$meetingid/recordings/")
          //->attachBody($data)
          //->setReturnType(Model\OnlineMeeting::class)
          ->execute();

        var_dump($graphresponse);
      }catch (Exception $e) {
        print "Caught Teams service Exception ".$e->getCode(). " message is ".$e->getMessage();
        print "Stack trace is ".$e->getTraceAsString();

      }   
      $result = json_decode(json_encode($graphresponse), true);
      return $result;
    }
 
}