<?php


$key = file_get_contents('the_key.txt');
 
require_once './vendor/autoload.php';
//set_include_path($_SERVER['DOCUMENT_ROOT'] . '/path-to-your-director/');
//require_once 'Google/Client.php';
//require_once 'Google/Service/YouTube.php';
 
$application_name = 'XXXXXX'; 
$client_secret = 'XXXXXXX';
$client_id = 'XXXXXXX.apps.googleusercontent.com';
$scope = array('https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtubepartner');
         
$application_name = 'hybridteaching test 2'; 
$client_secret = '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';
$client_id = '488899869922-ba6u36vv735a0e1b12oe0qusk1v61pjl.apps.googleusercontent.com';
$scope = array('https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube', 'https://www.googleapis.com/auth/youtubepartner');
         
$videoPath = "C:/Users/PC-39/Downloads/road_video_example.mp4";
$videoTitle = "A tutorial video";
$videoDescription = "A video tutorial on how to upload to YouTube";
$videoCategory = "22";
$videoTags = array("youtube", "tutorial");

require(__DIR__.'/../../../../config.php');
require_once(__DIR__.'/../../lib.php');
global $DB;

$configyt=$DB->get_record('hybridteachstore_youtube_con', array('emaillicense'=>'mcalvo@isyc.com'));
if (!$configyt) {
    return;
}
try{
    // Client init
    $client = new Google_Client();
    $client->setApplicationName($configyt->accountid);
    $client->setClientId($configyt->clientid);
    $client->setAccessType('offline');

    $client->setScopes($scope);
    $client->setClientSecret($configyt->clientsecret);
    $redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
    $client->setRedirectUri($redirect);
    $client->setApprovalPrompt('consent');
    //$client->setIncludeGrantedScopes(true);   // incremental auth

    $client->setAccessToken($configyt->token);


    
    $refreshToken = $client->getRefreshToken();
    //echo $refreshToken;


    if ($client->getAccessToken()) {

        /**
         * Check to see if our access token has expired. If so, get a new one and save it to file for future use.
         */
       

        if($client->isAccessTokenExpired()) {
            //$newToken = $client->getAccessToken();
            //$newToken = json_decode($client->getAccessToken());
            //$client->refreshToken($newToken->refresh_token);
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            //file_put_contents('the_key.txt', $client->getAccessToken());
        }

echo "<br><br>";        
var_dump($client->getAccessToken());
echo "<br><br>";   

        $youtube = new Google_Service_YouTube($client);
 


        // Create a snipet with title, description, tags and category id
        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($videoTitle);
        $snippet->setDescription($videoDescription);
        $snippet->setCategoryId($videoCategory);
        $snippet->setTags($videoTags);
 
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
        $client->setDefer(true);
 
        // Create a request for the API's videos.insert method to create and upload the video.
        $insertRequest = $youtube->videos->insert("status,snippet", $video);
      
 
        // Create a MediaFileUpload object for resumable uploads.
        $media = new Google_Http_MediaFileUpload(
            $client,
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

   
        //Video has successfully been upload, now lets perform some cleanup functions for this video
        if ($status->status['uploadStatus'] == 'uploaded') {
            // Actions to perform for a successful upload
            // $uploaded_video_id = $status['id'];
            echo "<br>";
            var_dump($status);
            echo "<br>FINALIZADO";
        }
 
        // If you want to make other calls after the file upload, set setDefer back to false
        $client->setDefer(true);

    } else{
        // @TODO Log error
        echo 'Problems creating the client';
    }
 
} catch(Google_Service_Exception $e) {
    print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
    print "Stack trace is ".$e->getTraceAsString();
}catch (Exception $e) {
    print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
    print "Stack trace is ".$e->getTraceAsString();
}
