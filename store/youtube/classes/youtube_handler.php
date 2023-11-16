<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachstore_youtube;

class youtube_handler {

    protected $client;

    public function __construct($configyt) {
        global $CFG;
        require_once(__DIR__.'../../vendor/autoload.php');

        try {

            $this->createclient($configyt);

            $this->client->setApprovalPrompt('force');

            if (isset($configyt->token) && $configyt->token) {
                $this->client->setAccessToken($configyt->token);
            }

            // Comprobar si hay que actualizar el acceso con el refreshtoken.
            if ($this->client->getAccessToken()) {
                if ($this->client->isAccessTokenExpired()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    // Guardar el token obtenido para no tener que solicitarlo al usuario en ningún momento.
                    $this->savetoken($configyt);
                }
            }
        } catch (\Google_Service_Exception $e) {
            print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
        }catch (\Exception $e) {
            print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
        }
    }

    public function setredirecturi($url) {
        // This uri must be in youtube config api: console.cloud.google.com.
        $this->client->setRedirectUri($url);
        return $this->client;
    }

    public function createclient($configyt) {
        global $CFG;
        require_once(__DIR__.'../../vendor/autoload.php');

        $this->client = new \Google_Client();

        $this->client->setClientId($configyt->clientid);
        $this->client->setClientSecret($configyt->clientsecret);
        $this->client->setScopes('https://www.googleapis.com/auth/youtube');
        $this->client->setApplicationName(get_string('pluginname', 'hybridteaching'));
        $this->client->setAccessType('offline');

        return $this->client;
    }

    public function uploadfile($store, $videopath) {
        require_once(__DIR__.'../../vendor/autoload.php');
        try {
                $youtube = new \Google_Service_YouTube($this->client);
                // Create a snipet with title, description, tags and category id.

                $snippet = new \Google_Service_YouTube_VideoSnippet();
                $snippet->setTitle($store->name);
                // Ver aquí si se puede poner alguna descripción personalizada.
                $snippet->setDescription($store->name);

                // Create a video status with privacy status. Options are "public", "private" and "unlisted".
                $status = new \Google_Service_YouTube_VideoStatus();
                $status->setPrivacyStatus('unlisted');
                // Create a YouTube video with snippet and status.
                $video = new \Google_Service_YouTube_Video();
                $video->setSnippet($snippet);
                $video->setStatus($status);
                // Size of each chunk of data in bytes. Setting it higher leads faster upload (less chunks,
                // for reliable connections). Setting it lower leads better recovery (fine-grained chunks).
                $chunksizebytes = 1 * 1024 * 1024;
                // Setting the defer flag to true tells the client to return a request which can be called
                // with ->execute(); instead of making the API call immediately.
                $this->client->setDefer(true);

                // Create a request for the API's videos.insert method to create and upload the video.
                $insertrequest = $youtube->videos->insert("status,snippet", $video);

                // Create a MediaFileUpload object for resumable uploads.
                $media = new \Google_Http_MediaFileUpload(
                    $this->client,
                    $insertrequest,
                    'video/*',
                    null,
                    true,
                    $chunksizebytes
                );
                $media->setFileSize(filesize($videopath));

            // Read the media file and upload it chunk by chunk.
            $status = false;
            $handle = fopen($videopath, "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunksizebytes);
                $status = $media->nextChunk($chunk);
            }
            fclose($handle);

            // If you want to make other calls after the file upload, set setDefer back to false.
            $this->client->setDefer(false);

            if ($status->status['uploadStatus'] == 'uploaded') {
                return $status->id;  // Video code.
            } else {
                return null;   // Incorrect upload? Check posible valor at uploadStatus.
            }
        } catch (\Google_Service_Exception $e) {
            print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
        } catch (\Exception $e) {
            print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
        }

    }

    public function savetoken ($configyt) {
        global $DB;

        $configyt->token = json_encode($this->client->getAccessToken());;
        $DB->update_record('hybridteachstore_youtube_con', $configyt);
    }

}
