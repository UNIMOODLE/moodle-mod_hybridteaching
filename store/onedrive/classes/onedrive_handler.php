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
        
        
        /*
        //obtener sitios sharepoint y drive
        $graphresponse = $graph
          ->createRequest("GET", "/sites/root/lists")
          ->setReturnType(Model\Drive::class)          
          ->execute();

          echo "<br>Drive:<br><pre>";
          var_dump($graphresponse);
          echo "</pre>";*/
          
        /*
        //LISTA LA UNIDAD ONEDRIVE
        $graphresponse = $graph
          //->createRequest("GET", "/sites/root")
          ->createRequest("GET", "/me/Drive")
          //->createRequest("GET", "/me/drive/root/search(q='{s1}')")         
          //->createRequest("GET", "/sites/ca50e87a-5e07-4a3d-b095-e1948d99cc58/drive")
          //->createRequest("GET", "/users/$organizer/onlineMeetings/$meetingid/recordings/")
          //->attachBody($data)
          ->setReturnType(Model\Drive::class)          
          ->execute();        
echo "<br>Drive:<br><pre>";
          var_dump($graphresponse);
          echo "</pre>";
        */  

        
      /*
        //SE PODRÍA SUSTITUIR LA BUSQUEDA DE CARPETA HT POR ESTO:
        //Aquí creamos dentro de hybridteaching otra carpeta con el id/nombre del curso.
        $path="/me/drive/root:/".get_string('hybridteaching','hybridteachstore_onedrive');
        $graphresponse = $graph
        ->createRequest("GET", $path)
        //->setReturnType(Model\DriveItem::class)
        ->execute();
      */

      /*
        //lista el contenido de la carpeta raiz
        $graphresponse = $graph
        ->createRequest("GET", "/me/drive/root/children")
        ->setReturnType(Model\DriveItem::class)
        ->execute();
        //->createRequest("GET", "/me/drive/search(q='test')");
        //->setReturnType(Model\DriveItem::class);
        
        echo "<br><br><br>Drive content:<br><pre>";
        var_dump($graphresponse);
        echo "</pre>";
        $find=false;
        foreach ($graphresponse as $element){
          if (!strcmp(strtolower($element->getName()), strtolower(get_string('hybridteaching','hybridteachstore_onedrive')))){
              $find=true;
          } 
        }

        //aquí creamos carpeta hybridteaching si no existe. Y dentro de hybridteaching otra carpeta con el id/nombre del curso.
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
        }*/

      $htfolderid=0;

      
      try{
        //Aquí comprobar que existe la carpeta 'hybridteaching' en la raiz de onedrive.
        $path="/me/drive/root:/".get_string('hybridteaching','hybridteachstore_onedrive');
        $graphresponse = $graph
          ->createRequest("GET", $path)
          ->setReturnType(Model\DriveItem::class)
          ->execute();

          /*
          echo "<br>CARPETA HYBRIDTEACHING:<br><pre>";
          var_dump($graphresponse);
          echo "</pre>";
          */

          //obtener el id de la carpeta padre 'hybridteaching'
          $htfolderid=$graphresponse->getId();

      } catch(\Throwable $e) {
        //si no existe la carpeta hybridteaching:
        if ($e->getCode()==404){
          //crear carpeta en raiz llamada 'hybridteaching'
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
            
              //obtener el id de la carpeta padre 'hybridteaching' creada
              $htfolderid=$graphresponse->getId();
        }
      }



      $htchildrenid=0;
      
      //comprobar si existe dentro de hybridteaching otra carpeta con el nombre corto del curso.
      try{
        $path="/me/drive/root:/".get_string('hybridteaching','hybridteachstore_onedrive')."/".$store->shortname;
        $graphresponse = $graph
        ->createRequest("GET", $path)
        ->setReturnType(Model\DriveItem::class)
        ->execute();

        $htchildrenid=$graphresponse->getId();

      } catch(\Throwable $e) {
        //si no existe item carpeta con nombre de curso::
        if ($e->getCode()==404){
          //crear carpeta con nombre de curso
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
                echo "<br>CARPETA HIJA CURSO creada:". $htchildrenid."<br>";

            }catch(\Throwable $ee){
              echo $ee->getCode(). " - ". $ee->getMessage();
            }

        }
      }

      //aqui, coger el vídeo y subirlo a onedrive, a la carpeta con id: htchildrenid
      $filename=$store->name.".mp4";
      $maxuploadsize = 1024 * 1024 * 4;

      //si tamaño de fichero es menor de 4MB, realizar subida simple, sino, realizar subida con sesiones
      if (filesize($videopath) < $maxuploadsize) {

        $path="/me/drive/items/".$htchildrenid.":/".$filename.":/content";
        $graphresponse = $graph
          ->createRequest("PUT", $path)
          ->upload($videopath);        
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
    }
 
}