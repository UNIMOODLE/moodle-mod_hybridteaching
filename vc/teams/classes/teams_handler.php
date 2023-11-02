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

namespace hybridteachvc_teams;

require_once(__DIR__ . '/../vendor/autoload.php');

// Include the Microsoft Graph classes.
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
class teams_handler {

    protected $config;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($config) {
        $this->config = $config;
    }

    public function refreshtoken() {
        global $DB;
        // Refresh authorization token.

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

        // Save token.
        $this->config->accesstoken = $tokenderefresco->access_token;
        $this->config->refreshtoken = $tokenderefresco->refresh_token;
        $DB->update_record('hybridteachvc_teams_config', $this->config);
    }

    public function createmeeting($session, $ht) {
        global $DB;

        $this->refreshtoken();

        $graph = new Graph();
        $graph->setAccessToken($this->config->accesstoken);

        $allowattendeetoenablecamera = $ht->disablecam ? false : true;
        $allowattendeetoenablemic = $ht->disablemic ? false : true;
        $allowmeetingchat = $ht->disablepublicchat ? 'disabled' : 'enabled';
        if ($ht->userecordvc && $ht->initialrecord) {
            $recordautomatically = true;
        } else {
            $recordautomatically = false;
        }
        $startdatetime = date('Y-m-d\TH:i:s', $session->starttime);
        $enddatetime = date('Y-m-d\TH:i:s', ($session->starttime + $session->duration));

        // Create an event calendar as online meeting.

        $data = [
          'subject' => $session->name,
          'start' => [
              'dateTime' => $startdatetime, // "2023-10-24T16:00:00",
              'timeZone' => "Europe/Paris",
          ],
          'end' => [
              'dateTime' => $enddatetime, // "2023-10-24T18:00:00",
              'timeZone' => "Europe/Paris",
          ],

          'location' => [
            'displayName' => $session->name,
          ],

          'allowNewTimeProposals' => false,
          'isOnlineMeeting' => true,
          'onlineMeetingProvider' => 'teamsForBusiness',
          'hideAttendees' => true, // Each attendee only sees themselves in the meeting request and meeting Tracking list.
          'isOrganizer' => true,
        ];

        // Create event in calendar.
        try {
            $graphresponse = $graph
                ->createRequest("POST", "/me/events")
                ->attachBody($data)
                ->setReturnType(Model\OnlineMeeting::class)
                ->execute();

        } catch (\Exception $e) {
            print "Caught Teams service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
            return;
        }

        $result = json_decode(json_encode($graphresponse), true);

        $joinurl = $result['onlineMeeting']['joinUrl'];

        // Get the event meeting.
        $graphresponse = $graph
            ->createRequest("GET", '/me/onlineMeetings?$filter'."=JoinWebUrl%20eq%20'".$joinurl."'")
            ->attachBody($data)
            ->setReturnType(Model\OnlineMeeting::class)
            ->execute();

        $result = json_decode(json_encode($graphresponse), true);

        $meetingid = $result[0]['id'];

        // Search participants as presenters.
        $participants = [];
        $htparticipants = json_decode($ht->participants);
        $found = true;
        foreach ($htparticipants as $participant) {
            if ($participant->role == 'moderator') {
                if ($participant->selectiontype == 'user' && is_numeric($participant->selectionid)) {
                    $usermoodle = $DB->get_record('user', ['id' => $participant->selectionid]);

                    $userteams = '';
                    try {
                        $email = $usermoodle->email;
                        $graphresponse = $graph->createRequest('GET', "/users/$email")
                            ->addHeaders(['Content-Type' => 'application/json'])
                            ->setReturnType(Model\User::class)
                            ->execute();
                        $userteams = json_decode(json_encode($graphresponse), true);
                    } catch (\Exception $e) {
                        $found = false;
                    }

                    if ($found && isset($userteams['id'])) {
                        $user = [
                            'identity' => [
                                'user' => [
                                  'id' => $userteams['id'],
                                  'displayName' => $userteams['displayName'],
                                ],
                            ],
                            'upn' => $email,
                            'role' => 'presenter',
                        ];
                        $participants[] = $user;
                    }
                }
            }
        }

        // Change info meeting with options from hybridteaching form.
        $data = [
            'allowAttendeeToEnableCamera' => $allowattendeetoenablecamera,
            'allowAttendeeToEnableMic' => $allowattendeetoenablemic,
            'allowMeetingChat' => $allowmeetingchat,
            'recordAutomatically' => $recordautomatically,
            // 'lobbyBypassScope' => 'organization',
        ];
        if (!empty($participants)) {
            $data[] = [
                'allowedPresenters' => 'roleIsPresenter',
            ];
            $data[] = [
                'participants' => [
                    'attendees' => $participants,
                ],
            ];
        }
        try {
            $graphresponse = $graph
                ->createRequest("PATCH", "/me/onlineMeetings/$meetingid")
                ->attachBody($data)
                ->setReturnType(Model\OnlineMeeting::class)
                ->execute();
        } catch (\Exception $e) {
            print "Caught Teams service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
            return;
        }
        $result = json_decode(json_encode($graphresponse), true);

        /*
        // Old code.

        $startdatetime .= '.00+00:00';
        $enddatetime .= '.00+00:00';

        $data = [
            'subject' => $session->name,
            'startDateTime' => $startdatetime,
            'endDateTime' => $enddatetime,
            // 'startDateTime' => '2023-09-08T20:35:00.00+01:00',
            // 'endDateTime' => '2023-09-08T21:35:00.00+01:00',
            'allowAttendeeToEnableCamera' => $allowattendeetoenablecamera,
            'allowAttendeeToEnableMic' => $allowattendeetoenablemic,
            'allowMeetingChat' => $allowmeetingchat,
            'recordAutomatically' => $recordautomatically,
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
        $result = json_decode(json_encode($graphresponse), true);*/

        return $result;
    }

    public function deletemeeting ($meetingid) {
        $this->refreshtoken();

        $graph = new Graph();
        $graph->setAccessToken($this->config->accesstoken);

        $data = [
            'meetingId' => $meetingid,
        ];

        try {
            $graphresponse = $graph
                ->createRequest("DELETE", "/me/onlineMeetings")
                ->attachBody($data)
                ->setReturnType(Model\OnlineMeeting::class)
                ->execute();
        } catch (Exception $e) {
            print "Caught Teams service Exception ".$e->getCode(). " message is ".$e->getMessage();
            print "Stack trace is ".$e->getTraceAsString();
            return;
        }
    }

    public function get_meeting_recordings ($folderfile, $meetingid, $organizerid) {
        $this->refreshtoken();
        $graph = new Graph();
        $graph->setAccessToken($this->config->accesstoken);

        $recordingid = 0;
        try {
            $graphresponse = $graph
                ->setApiVersion("beta")
                ->createRequest("GET", "/me/onlineMeetings/$meetingid/recordings")
                ->setReturnType(Model\RecordingInfo::class)
                ->execute();
            $result = json_decode(json_encode($graphresponse), true);

            if (isset($result[0])) {
                $recordingid = $result[0]['id'];

                // Connect to recordingContentUrl to download.
                $graphresponse = $graph
                    ->setApiVersion("beta")
                    ->createRequest("GET", "/me/onlineMeetings/$meetingid/recordings/$recordingid/content")
                    ->download($folderfile);
                    $result = json_decode(json_encode($graphresponse), true);
            }
        } catch (\Exception $e) {
            print "\nCan't download record from meetingid: ".$meetingid."<br>";
        }
        return $recordingid;
    }
}
