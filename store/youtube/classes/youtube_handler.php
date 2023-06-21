<?php


class youtube_handler{

   protected $client;

    public function __construct($configyt){
        global $CFG;
        require_once (__DIR__.'../../vendor/autoload.php');
        //aqui leer de la bbdd del subplugin, para leer el reg.

        $scope = array('https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtubepartner');

        try{         
            $this->client = new Google_Client();
            $this->client->setApplicationName(get_string('pluginname','hybridteaching'));
            $this->client->setClientId($configyt->clientid);
            
            $this->client->setScopes($scope);
            $this->client->setClientSecret($configyt->clientsecret);

            $redirect = $CFG->wwwroot . '/admin/cli/scheduled_task.php';
            //$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'], FILTER_SANITIZE_URL);
            $this->client->setRedirectUri($redirect);
            $this->client->setApprovalPrompt('consent');
            //$this->client->setIncludeGrantedScopes(true);   // incremental auth
            $this->client->setAccessType('offline');     
            $this->client->setAccessToken($configyt->token);          
            //comprobar si hay que actualizar el acceso con el refreshtoken
            if ($this->client->getAccessToken()) {               
                if($this->client->isAccessTokenExpired()) {    
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    //guardar el token obtenido para no tener que solicitarlo al usuario en ningún momento
                    $this->saveToken($configyt);
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

    public function uploadfile($store,$videoPath){
        require_once (__DIR__.'../../vendor/autoload.php');
        try{         
                $youtube = new Google_Service_YouTube($this->client);
                // Create a snipet with title, description, tags and category id
              
                $snippet = new Google_Service_YouTube_VideoSnippet();
                $snippet->setTitle($store->name);
                //ver aquí si se puede poner alguna descripción personalizada.
                $snippet->setDescription($store->name);

                // Create a video status with privacy status. Options are "public", "private" and "unlisted".
                $status = new Google_Service_YouTube_VideoStatus();
                $status->setPrivacyStatus('private');
                // Create a YouTube video with snippet and status
                $video = new Google_Service_YouTube_Video();
                $video->setSnippet($snippet);
                $video->setStatus($status);               
                // Size of each chunk of data in bytes. Setting it higher leads faster upload (less chunks,
                // for reliable connections). Setting it lower leads better recovery (fine-grained chunks)
                $chunkSizeBytes = 1 * 1024 * 1024;  
                // Setting the defer flag to true tells the client to return a request which can be called
                // with ->execute(); instead of making the API call immediately.           
                $this->client->setDefer(true);
           
                // Create a request for the API's videos.insert method to create and upload the video.
                $insertRequest = $youtube->videos->insert("status,snippet", $video);
               
                // Create a MediaFileUpload object for resumable uploads.
                $media = new Google_Http_MediaFileUpload(
                    $this->client,
                    $insertRequest,
                    'video/*',
                    null,
                    true,
                    $chunkSizeBytes
                );
                $media->setFileSize(filesize($videoPath));

            // Read the media file and upload it chunk by chunk.
            $status = false;
            $handle = fopen($videoPath, "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }
            fclose($handle);

            // If you want to make other calls after the file upload, set setDefer back to false
            $this->client->setDefer(false);

            if ($status->status['uploadStatus'] == 'uploaded') {
                return $status->id;  //code del vídeo
            }
            else {
                return null;   //¿subida incorrecta? Comprobar valores posibles de uploadStatus
            }
        } catch(Google_Service_Exception $e) {
            print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
        } catch (Exception $e) {
            print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
        }

    }

    public function saveToken($configyt){
        global $DB;

        $configyt->token = json_encode($this->client->getAccessToken());;
        $DB->update_record('hybridteachstore_youtube_ins',$configyt);
    }

    

    
}

