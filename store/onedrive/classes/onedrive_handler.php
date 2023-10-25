<?php
namespace hybridteachstore_onedrive;

// Include the Microsoft Graph classes  
use Microsoft\Graph\Graph;  
use Microsoft\Graph\Model; 
class onedrive_handler{

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
    require_once __DIR__ . '/../vendor/autoload.php'; 
    global $DB;

    //Refresh authorization token

    $guzzle = new \GuzzleHttp\Client();

    $url = 'https://login.microsoftonline.com/' . $this->config->tenantid . '/oauth2/v2.0/token';

    $tokenderefresco = json_decode($guzzle->post($url, [
        'form_params' => [
            'client_id' => $this->config->clientid,
            'client_secret' => $this->config->clientsecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->config->refreshtoken,
            //'scope' => 'offline_access files.readwrite.all',
            //Directory.AccessAsUser.All Directory.ReadWrite.All',
            
        ],
    ])->getBody()->getContents());

    //save token:
    $this->config->accesstoken=$tokenderefresco->access_token;
    $this->config->refreshtoken=$tokenderefresco->refresh_token;
    $DB->update_record('hybridteachstore_onedrive_co',$this->config);
  }

  public function uploadfile($store,$videopath){
      require_once __DIR__ . '/../vendor/autoload.php'; 
      $this->refreshtoken();

      $graph = new Graph();  
      $graph->setAccessToken($this->config->accesstoken);  

      $htfolderid=0;
    
      try{

        //Check the 'hybrid teaching' folder exists in the root of OneDrive.
        $path="/me/drive/root:/".get_string('hybridteaching','hybridteachstore_onedrive');
        $graphresponse = $graph
          ->createRequest("GET", $path)
          ->setReturnType(Model\DriveItem::class)
          ->execute();

          //get the id of the parent folder 'hybridteaching'
          $htfolderid=$graphresponse->getId();

      } catch(\Throwable $e) {
        //if the hybridteaching folder does not exist:
        if ($e->getCode()==404){
          //create root folder called 'hybridteaching'
            $data=[
              'name' => 'Hybridteaching',
              'folder' => array("@odata.type" => "microsoft.graph.folder"),
              "@microsoft.graph.conflictBehavior" => "rename"
            ];
            $graphresponse = $graph
              ->createRequest("POST", "/me/drive/root/children")
              ->attachBody($data)
              ->setReturnType(Model\DriveItem::class)
              ->execute();
            
              //get the id of the created parent 'hybridteaching' folder
              $htfolderid=$graphresponse->getId();
        }
      }

    $htchildrenid=0;
    
    //check if another folder with the short name of the course exists within hybrid teaching.
    try{
      $path="/me/drive/root:/".get_string('hybridteaching','hybridteachstore_onedrive')."/".$store->shortname;
      $graphresponse = $graph
      ->createRequest("GET", $path)
      ->setReturnType(Model\DriveItem::class)
      ->execute();

      $htchildrenid=$graphresponse->getId();

    } catch(\Throwable $e) {
      //if item folder with course name does not exist:
      if ($e->getCode()==404){
        //create folder with course name
        try{
            $data=[
              'name' => $store->shortname,
              'folder' => array("@odata.type" => "microsoft.graph.folder"),
              "@microsoft.graph.conflictBehavior" => "rename"
            ];
            $path="/me/drive/items/".$htfolderid."/children";
            $graphresponse = $graph
              ->createRequest("POST", $path)
              ->attachBody($data)
              ->setReturnType(Model\DriveItem::class)
              ->execute();

              $htchildrenid=$graphresponse->getId();
          }catch(\Throwable $ee){
            echo $ee->getCode(). " - ". $ee->getMessage();
          }

      }
    }

    //take the video and upload it to onedrive, to the folder with id: htchildrenid
    $filename=$store->name.".mp4";
    $maxuploadsize = 1024 * 1024 * 4;
    try{
      //If file size is less than 4MB, perform simple upload, otherwise, perform upload with sessions
      if (filesize($videopath) < $maxuploadsize) {
        $path="/me/drive/items/".$htchildrenid.":/".$filename.":/content";
        $graphresponse = $graph
          ->createRequest("PUT", $path)
          ->setReturnType(Model\DriveItem::class)
          ->upload($videopath);        

        $element = json_decode(json_encode($graphresponse), true);

      }
      else{

        $path="/me/drive/items/".$htchildrenid.":/".$filename.":/createUploadSession";
        $data=[
          'item' => array("@microsoft.graph.conflictBehavior" => "rename"),
        ];
        $uploadsession = $graph
          ->createRequest("POST", $path)
          ->attachBody($data)
          ->setReturnType(Model\UploadSession::class)
          ->execute();

        //upload video:
        $handle = fopen($videopath, 'rb');
        $fileSize = fileSize($videopath);
        $chunkSize = 1024*1024*2;
        $prevBytesRead = 0;
        while (!feof($handle)) {
          $bytes = fread($handle, $chunkSize);
          $bytesRead = ftell($handle);
          //test:
          //echo "<br>prevbytes:".$prevBytesRead." - bytesread:".$bytesRead."<br>";
          $graphresponse = $graph 
              ->createRequest("PUT",$uploadsession->getUploadUrl())
              ->addHeaders([
                  'Connection' => "keep-alive",
                  'Content-Length' => ($bytesRead-$prevBytesRead),
                  'Content-Range' => "bytes " . $prevBytesRead . "-" . ($bytesRead-1) . "/" . $fileSize,
              ])
              ->setReturnType(Model\UploadSession::class)
              ->attachBody($bytes)
              ->execute();

            $prevBytesRead = $bytesRead;
        }
      }
      $element = json_decode(json_encode($graphresponse), true);
          
    }catch(\Throwable $ee){
      echo $ee->getCode(). " - ". $ee->getMessage();
    }

    $response=[];
    if (isset($element['webUrl'])){
      $response['weburl'] =  $element['webUrl'];
    }
    if (isset($element['@microsoft.graph.downloadUrl'])){
      $response['downloadurl'] =  $element['@microsoft.graph.downloadUrl'];
    }

    return $response;

  }


  public function get_urlrecording($processedrecording){    
    global $DB;
    require_once __DIR__ . '/../vendor/autoload.php'; 
    $this->refreshtoken();

    $graph = new Graph();  
    $graph->setAccessToken($this->config->accesstoken);  

    $url = '';

    $record = $DB->get_record('hybridteachstore_onedrive', ['id' => $processedrecording]);
    if (!isset($record->weburl)){
      return $url;
    }  

    try{

      $path='/me/drive/root:/'.get_string('hybridteaching','hybridteachstore_onedrive').'/'.$record->weburl;
      $graphresponse = $graph
      ->createRequest("GET", $path)
      ->setReturnType(Model\DriveItem::class)
      ->execute();

      // Get the id of the video item.
      $htitemid = $graphresponse->getId();

      $path = '/me/drive/items/'.$htitemid.'/preview';
            $graphresponse = $graph
            ->createRequest("POST", $path)
            ->setReturnType(Model\DriveItem::class)
            ->execute();

      $element = json_decode(json_encode($graphresponse), true);

      if (isset($element['getUrl'])){
        $url = $element['getUrl'];
      }

    } catch(\Throwable $e) {

    }

    return $url;

  }
 
}