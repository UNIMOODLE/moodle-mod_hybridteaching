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
 * @package    hybridteachstore_onedrive
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachstore_onedrive;

// Include the Microsoft Graph classes.
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

/**
 * Class onedrive_handler.
 */
class onedrive_handler {
    /** @var stdClass $config A config from the teams object. */
    protected $config;


    /**
     * Constructor for initializing the class with the provided configuration.
     *
     * @param object $config The configuration for the class
     */
    public function __construct($config) {
        $this->config = $config;  // Api with credentials, url...
    }

    /**
     * Checks the token to connect to Onedrive.
     */
    public function refreshtoken() {
        require_once(__DIR__ . '/../vendor/autoload.php');
        global $DB;

        // Refresh authorization token.
        $guzzle = new \GuzzleHttp\Client();

        $url = 'https://login.microsoftonline.com/' . $this->config->tenantid . '/oauth2/v2.0/token';

        $tokenderefresco = json_decode($guzzle->post($url, [
            'form_params' => [
                'client_id' => $this->config->clientid,
                'client_secret' => $this->config->clientsecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->config->refreshtoken,
            ],
        ])->getBody()->getContents());

        // Save token:.
        $this->config->accesstoken = $tokenderefresco->access_token;
        $this->config->refreshtoken = $tokenderefresco->refresh_token;
        $DB->update_record('hybridteachstore_onedrive_co', $this->config);
    }

    /**
     * Uploads a file to Onedrive using the Graph API.
     *
     * @param object $store Store object
     * @param string $videopath Video path
     * @return mixed
     */
    public function uploadfile($store, $videopath) {
        require_once(__DIR__ . '/../vendor/autoload.php');
        $this->refreshtoken();

        $graph = new Graph();
        $graph->setAccessToken($this->config->accesstoken);

        $htfolderid = 0;

        try {

            // Check the 'hybrid teaching' folder exists in the root of OneDrive.
            $path = "/me/drive/root:/".get_string('hybridteaching', 'hybridteachstore_onedrive');
            $graphresponse = $graph
                ->createRequest("GET", $path)
                ->setReturnType(Model\DriveItem::class)
                ->execute();

                // Get the id of the parent folder 'hybridteaching'.
                $htfolderid = $graphresponse->getId();
        } catch (\Throwable $e) {
            // If the hybridteaching folder does not exist:.
            if ($e->getCode() == 404) {
                // Create root folder called 'hybridteaching'.
                $data = [
                    'name' => 'Hybridteaching',
                    'folder' => ["@odata.type" => "microsoft.graph.folder"],
                    "@microsoft.graph.conflictBehavior" => "rename",
                ];
                $graphresponse = $graph
                    ->createRequest("POST", "/me/drive/root/children")
                    ->attachBody($data)
                    ->setReturnType(Model\DriveItem::class)
                    ->execute();

                    // Get the id of the created parent 'hybridteaching' folder.
                    $htfolderid = $graphresponse->getId();
            }
        }

        $htchildrenid = 0;

        // Check if another folder with the short name of the course exists within hybrid teaching.
        try {
            $path = "/me/drive/root:/".get_string('hybridteaching', 'hybridteachstore_onedrive')."/".$store->shortname;
            $graphresponse = $graph
                ->createRequest("GET", $path)
                ->setReturnType(Model\DriveItem::class)
                ->execute();

                $htchildrenid = $graphresponse->getId();

        } catch (\Throwable $e) {
            // If item folder with course name does not exist:.
            if ($e->getCode() == 404) {
                // Create folder with course name.
                try {
                    $data = [
                      'name' => $store->shortname,
                      'folder' => ["@odata.type" => "microsoft.graph.folder"],
                      "@microsoft.graph.conflictBehavior" => "rename",
                    ];
                    $path = "/me/drive/items/".$htfolderid."/children";
                    $graphresponse = $graph
                        ->createRequest("POST", $path)
                        ->attachBody($data)
                        ->setReturnType(Model\DriveItem::class)
                        ->execute();

                        $htchildrenid = $graphresponse->getId();
                } catch (\Throwable $ee) {
                    echo $ee->getCode(). " - ". $ee->getMessage();
                }

            }
        }

        // Take the video and upload it to onedrive, to the folder with id: htchildrenid.
        $filename = $store->name.".mp4";
        $maxuploadsize = 1024 * 1024 * 4;
        try {
            // If file size is less than 4MB, perform simple upload, otherwise, perform upload with sessions.
            if (filesize($videopath) < $maxuploadsize) {
                $path = "/me/drive/items/".$htchildrenid.":/".$filename.":/content";
                $graphresponse = $graph
                    ->createRequest("PUT", $path)
                    ->setReturnType(Model\DriveItem::class)
                    ->upload($videopath);

                  $element = json_decode(json_encode($graphresponse), true);

            } else {

                $path = "/me/drive/items/".$htchildrenid.":/".$filename.":/createUploadSession";
                $data = [
                  'item' => ["@microsoft.graph.conflictBehavior" => "rename"],
                ];
                $uploadsession = $graph
                    ->createRequest("POST", $path)
                    ->attachBody($data)
                    ->setReturnType(Model\UploadSession::class)
                    ->execute();

                // Upload video:.
                $handle = fopen($videopath, 'rb');
                $filesize = filesize($videopath);
                $chunksize = 1024 * 1024 * 2;
                $prevbytesread = 0;
                while (!feof($handle)) {
                    $bytes = fread($handle, $chunksize);
                    $bytesread = ftell($handle);

                    $graphresponse = $graph
                        ->createRequest("PUT", $uploadsession->getUploadUrl())
                        ->addHeaders([
                            'Connection' => "keep-alive",
                            'Content-Length' => ($bytesread - $prevbytesread),
                            'Content-Range' => "bytes " . $prevbytesread . "-" . ($bytesread - 1) . "/" . $filesize,
                        ])
                        ->setReturnType(Model\UploadSession::class)
                        ->attachBody($bytes)
                        ->execute();

                        $prevbytesread = $bytesread;
                }
            }
            $element = json_decode(json_encode($graphresponse), true);

        } catch (\Throwable $ee) {
            echo $ee->getCode(). " - ". $ee->getMessage();
        }

        $response = [];
        if (isset($element['webUrl'])) {
            $response['weburl'] = $element['webUrl'];
        }
        if (isset($element['@microsoft.graph.downloadUrl'])) {
            $response['downloadurl'] = $element['@microsoft.graph.downloadUrl'];
        }

        return $response;
    }


    /**
     * A function to get the URL recording.
     *
     * @param int $processedrecording OneDrive instance
     * @throws \Throwable
     * @return string
     */
    public function get_urlrecording($processedrecording) {
        global $DB;
        require_once(__DIR__ . '/../vendor/autoload.php');
        $this->refreshtoken();

        $graph = new Graph();
        $graph->setAccessToken($this->config->accesstoken);

        $url = '';

        $record = $DB->get_record('hybridteachstore_onedrive', ['id' => $processedrecording]);
        if (!isset($record->weburl)) {
            return $url;
        }

        try {

            $path = '/me/drive/root:/'.get_string('hybridteaching', 'hybridteachstore_onedrive').'/'.$record->weburl;
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

            if (isset($element['getUrl'])) {
                $url = $element['getUrl'];
            }

        } catch (\Throwable $e) {
            echo $ee->getCode(). " - ". $ee->getMessage();
        }

        return $url;
    }

    /**
     * Download a recording from the OneDrive using the Microsoft Graph API.
     *
     * @param int $processedrecording The ID of the processed recording.
     * @throws \Throwable
     * @return array|string The download URL of the recording.
     */
    public function downloadrecording($processedrecording) {
        global $DB;
        require_once(__DIR__ . '/../vendor/autoload.php');
        $this->refreshtoken();

        $graph = new Graph();
        $graph->setAccessToken($this->config->accesstoken);

        $recording = $DB->get_record('hybridteachstore_onedrive', ['id' => $processedrecording]);
        if (!isset($recording->weburl) || $recording->weburl == '') {
            return;
        }

        // Download recording.
        try {
            $path = "/me/drive/root:/".get_string('hybridteaching', 'hybridteachstore_onedrive')."/".$recording->weburl;
            $graphresponse = $graph->createRequest("GET", $path)
                ->setReturnType(Model\DriveItem::class)
                ->execute();

            $response = json_decode(json_encode($graphresponse), true);
            if (isset($response['@microsoft.graph.downloadUrl'])) {
                return $response['@microsoft.graph.downloadUrl'];
            }

        } catch (\Throwable $e) {
            return '';
        }
        return '';
    }

    public function deletefile($processedrecording) {
        global $DB;
        require_once(__DIR__ . '/../vendor/autoload.php');
        $this->refreshtoken();

        $graph = new Graph();
        $graph->setAccessToken($this->config->accesstoken);

        $recording = $DB->get_record('hybridteachstore_onedrive', ['id' => $processedrecording]);
        if (!isset($recording->weburl) || $recording->weburl == '') {
            return;
        }

        try {
            $path = "/me/drive/root:/".get_string('hybridteaching', 'hybridteachstore_onedrive')."/".$recording->weburl;
            $graphresponse = $graph->createRequest("GET", $path)
                ->setReturnType(Model\DriveItem::class)
                ->execute();

            $response = json_decode(json_encode($graphresponse), true);
            if (isset($response['@microsoft.graph.downloadUrl'])) {
                return $response['@microsoft.graph.downloadUrl'];
            }

        } catch (\Throwable $e) {
            return '';
        }
    }

}
