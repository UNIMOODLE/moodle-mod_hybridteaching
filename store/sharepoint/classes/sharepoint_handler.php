<?php
namespace hybridteachstore_sharepoint;

// Include the Microsoft Graph classes  
use Microsoft\Graph\Graph;  
use Microsoft\Graph\Model; 
class sharepoint_handler{

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
              'scope' => 'offline_access user.read https://'.$this->config->subdomain.'sharepoint.com/AllSites.FullControl',
              //'scope' => 'offline_access user.read https://nhb7.sharepoint.com/AllSites.FullControl',
          ],
      ])->getBody()->getContents());

      //save token:
      $this->config->accesstoken=$tokenderefresco->access_token;
      $this->config->refreshtoken=$tokenderefresco->refresh_token;
      $DB->update_record('hybridteachstore_sharepo_con',$this->config);
    }

    public function upload_recordings(){
        require_once __DIR__ . '/../vendor/autoload.php'; 
        $this->refreshtoken();
        //$this->authsharepoint();
        $graph = new Graph();  
        $graph->setAccessToken($this->config->accesstoken);  
        $graphresponse = $graph
          ->createRequest("GET", "/sites/root/lists")
          ->execute();


        //ACCEDER A SHAREPOINT
        $graphresponse = $graph
          //->createRequest("GET", "/sites/root")
          ->createRequest("GET", "/me/Drive")
          //->createRequest("GET", "/me/drive/root/search(q='{s1}')")         
          //->createRequest("GET", "/sites/ca50e87a-5e07-4a3d-b095-e1948d99cc58/drive")
          //->createRequest("GET", "/users/$organizer/onlineMeetings/$meetingid/recordings/")
          //->attachBody($data)
          ->setReturnType(Model\Drive::class)          
          ->execute();        
          

        //lista el contenido de la carpeta raiz
        $graphresponse = $graph
        ->createRequest("GET", "/me/drive/root/children")
        ->setReturnType(Model\DriveItem::class)
        ->execute();
        //->createRequest("GET", "/me/drive/search(q='test')");
        //->setReturnType(Model\DriveItem::class);
        
        //aquí creamos carpeta hybridteaching si no existe. Y dentro de hybridteaching otra carpeta con el id/nombre del curso.

        $graphresponse = $graph
            //->createRequest("GET", "/me/drive/root/search(q='{Hybridteaching}')")
            ->createRequest("GET", "/me/drive/root/")
            ->setReturnType(Model\DriveItem::class)
            ->execute();
        /*echo "<pre>";
        $folderht = json_decode(json_encode($graphresponse), true);
        var_dump($folderht);
        echo "</pre>";*/
        $find=false;
        foreach ($folderht as $folder){
          if ($folder['name']=="Hybridteaching"){
              $find=true;
          }
        }
        
        //si NO existe carpeta 'hybridteaching', crearla
        if ($find==false){
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
        }
    }
 
}