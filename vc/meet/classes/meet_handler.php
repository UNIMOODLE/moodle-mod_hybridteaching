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

namespace hybridteachvc_meet;

use Google\Client;
use Google\Service\Drive;
class meet_handler {
    public $client;

    public function __construct($configmeet) {
        try {
            $this->createclient($configmeet);
            if (isset($configmeet->token) && $configmeet->token) {
                $this->client->setAccessToken($configmeet->token);
            }

            if ($this->client->getAccessToken() && $this->client->getAccessToken()['access_token'] != 0) {
                $this->client->setApprovalPrompt('consent');
                if ($this->client->isAccessTokenExpired()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $this->save_token($configmeet);
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

    public function createclient($configmeet) {
        require_once(__DIR__.'/../vendor/autoload.php');

        $this->client = new \Google_Client();
        $this->client->setClientId($configmeet->clientid);
        $this->client->setClientSecret($configmeet->clientsecret);
        $this->client->setScopes(\Google_Service_Calendar::CALENDAR);
        $this->client->setApplicationName(get_string('pluginname', 'hybridteaching'));
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

        $service = new \Google_Service_Calendar($this->client);
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

        $event = new \Google_Service_Calendar_Event([
            'summary' => $meet->name,
            'description' => $meet->description,
            'start' => [
                'dateTime' => $startdatetime,
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $enddatetime,
                'timeZone' => $timezone,
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestID' => 'req_'.time(),
                ],
            ],
        ]);

        $conferencedata = new \Google\Service\Calendar\ConferenceData();
        $req = new \Google\Service\Calendar\CreateConferenceRequest();
        $req->setRequestId("req_".time());
        $conferencedata->setCreateRequest($req);
        $event->setConferenceData($conferencedata);

        $event = $service->events->insert($calendarid, $event, ['conferenceDataVersion' => 1]);
        return $event;
    }

    public function update_meeting_event($session, $meetdata) {
        global $USER;

        $service = new \Google_Service_Calendar($this->client);
        $calendarid = 'primary';

        $sessionstart = new \DateTime();
        $sessionstart->setTimestamp($session->starttime);

        $sessionend = new \DateTime();
        $sessionend->setTimestamp($session->starttime + $session->duration);

        $starttime = $sessionstart->format('H:i:s');
        $sdate = $sessionstart->format('Y-m-d');

        $endtime = $sessionend->format('H:i:s');
        $edate = $sessionend->format('Y-m-d');

        $startdatetime = $sdate . 'T' . $starttime;
        $enddatetime = $edate . 'T' . $endtime;

        $timezone = get_user_timezone($USER->timezone);

        $event = new \Google_Service_Calendar_Event([
            'summary' => $session->name,
            'description' => $session->description,
            'start' => [
                'dateTime' => $startdatetime,
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $enddatetime,
                'timeZone' => $timezone,
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestID' => 'req_'.time(),
                ],
            ],
        ]);

        // First retrieve the event from the API.
        $event = $service->events->get('primary', $meetdata->eventid);

        $event->setSummary('Appointment at Somewhere');

        $service->events->update('primary', $event->getId(), $event);

        return $event;
    }

    public function save_token($configmeet) {
        global $DB;
        $configmeet->token = json_encode($this->client->getAccessToken());
        if (!empty($configmeet->id)) {
            $DB->update_record('hybridteachvc_meet_config', $configmeet);
        }
    }

    public function deletemeeting($eventid) {
        $service = new \Google_Service_Calendar($this->client);
        $service->events->delete('primary', $eventid);
    }

    public static function search_files($joincode) {
        try {
            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope(Drive::DRIVE);
            $driveservice = new Drive($client);
            $files = [];
            $pagetoken = null;
            do {
                $response = $driveservice->files->listFiles([
                    'q' => "mimeType='application/vnd.google-apps.file'",
                    'name' => $joincode,
                    'spaces' => 'drive',
                    'pageToken' => $pagetoken,
                    'fields' => 'nextPageToken, files(id, name)',
                ]);
                array_push($files, $response->files);

                $pagetoken = $response->pageToken;
            } while ($pagetoken != null);
            return $files;
        }catch(\Exception $e) {
            echo "Error Message: ".$e;
        }
    }

    public static function download_file($fileid) {
        try {
            $client = new Client();
            $client->useApplicationDefaultCredentials();
            $client->addScope(Drive::DRIVE);
            $driveservice = new Drive($client);
            $response = $driveservice->files->get($fileid, [
                'alt' => 'media', ]);
            $content = $response->getBody()->getContents();
            return $content;
        } catch(\Exception $e) {
            echo "Error Message: ".$e;
        }
    }

    public function addAttachment($calendarservice, $driveservice, $calendarid, $eventid, $fileid) {
        $file = $driveservice->files->get($fileid);
        $event = $calendarservice->events->get($calendarid, $eventid);
        $attachments = $event->attachments;

        $attachments[] = [
          'fileUrl' => $file->alternateLink,
          'mimeType' => $file->mimeType,
          'title' => $file->title,
        ];
        $changes = new \Google_Service_Calendar_Event([
          'attachments' => $attachments,
        ]);

        $calendarservice->events->patch($calendarid, $eventid, $changes, [
          'supportsAttachments' => TRUE,
        ]);
    }
}
