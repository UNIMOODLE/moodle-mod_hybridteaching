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
 * @package    hybridteachvc_teams
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_teams;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../vendor/autoload.php');

// Include the Microsoft Graph classes.
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use mod_hybridteaching\helpers\roles;

/**
 * Class teams_handler.
 */
class teams_handler {
    /** @var stdClass $config A config from the teams object. */
    protected $config;

    /**
     * Constructor.
     * @param stdClass $config A config from the teams object.
     *
     * @return void
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Check the accessmethod (app: 0, behalfuser: 1).
     *
     * @return string The token to connect Teams.
     */
    public function refreshtoken() {
        if ($this->config->accessmethod == 0) {
            return $this->accesstokenapp();
        } else {
            return $this->getrefreshtoken();
        }
    }

    /**
     * Get the token to connect Teams in case  accessmethod is app: 0.
     *
     * @return string The token to connect Teams.
     */
    public function getrefreshtoken() {
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

        return $this->config->accesstoken;
    }

    /**
     * Get the token to connect Teams in case  accessmethod is behalfuser: 1.
     *
     * @return string The token to connect Teams.
     */
    public function accesstokenapp() {
        global $DB, $CFG;

        $guzzle = new \GuzzleHttp\Client();

        // Get access token if accessmethod is app.
        $url = 'https://login.microsoftonline.com/' . $this->config->tenantid . '/oauth2/v2.0/token';
        $graphresponse = json_decode($guzzle->post($url, [
            'form_params' => [
                'tenant' => $this->config->tenantid,
                'client_id' => $this->config->clientid,
                'scope' => 'https://graph.microsoft.com/.default',
                'client_secret' => $this->config->clientsecret,
                'grant_type' => 'client_credentials',
                'redirect_uri' => $CFG->wwwroot.'/mod/hybridteaching/vc/teams/classes/teamsacessapp.php',
            ],
        ])->getBody()->getContents());

        $result = json_decode(json_encode($graphresponse), true);

        if (isset($result['access_token'])) {
            $this->config->accesstoken = $result['access_token'];
            $DB->update_record('hybridteachvc_teams_config', $this->config);
            return $this->config->accesstoken;
        }
    }

    /**
     * Create a teams vc
     *
     * @param object $session The session object.
     * @param object $ht The hybridteaching object.
     * @return object The response from api.
     */
    public function createmeeting($session, $ht) {

        $token = $this->refreshtoken();
        $graph = new Graph();
        $graph->setAccessToken($token);

        $allowattendeetoenablecamera = $ht->disablecam ? false : true;
        $allowattendeetoenablemic = $ht->disablemic ? false : true;
        $allowmeetingchat = $ht->disablepublicchat ? 'disabled' : 'enabled';

        // Get instance context.
        $cm = get_coursemodule_from_instance ('hybridteaching', $ht->id);
        $context = \context_module::instance($cm->id);

        $enabledrecording = get_config('hybridteachvc_teams', 'enabledrecording');

        if ($ht->userecordvc && $ht->initialrecord && has_capability('hybridteachvc/teams:record', $context)
            && has_capability('mod/hybridteaching:record', $context) && $enabledrecording) {
            $recordautomatically = true;
        } else {
            $recordautomatically = false;
        }
        $startdatetime = date('Y-m-d\TH:i:s', $session->starttime);
        $enddatetime = date('Y-m-d\TH:i:s', ($session->starttime + $session->duration));

        // Get attendees.
        // Search participants as moderators and as attendee.
        $found = false;
        $moderators = roles::getparticipants($ht, $session->groupid, roles::ROLE_MODERATOR);
        $participants = roles::getparticipants($ht, $session->groupid, roles::ROLE_VIEWER);

        $participantsteams = [];
        $participantscalendar = [];
        foreach ($participants as $participant) {
            $userteams = '';
            try {
                $email = $participant->email;
                $graphresponse = $graph->createRequest('GET', "/users/$email")
                    ->addHeaders(['Content-Type' => 'application/json'])
                    ->setReturnType(Model\User::class)
                    ->execute();
                $userteams = json_decode(json_encode($graphresponse), true);
                $found = true;
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
                    'role' => 'attendee',
                ];
                $participantsteams[] = $user;

                $usercalendar = [
                    'emailAddress' => [
                        'address' => $email,
                        'name' => $userteams['displayName'],
                    ],
                    'type' => 'required',
                ];
                $participantscalendar[] = $usercalendar;

            }
        }

        $found = false;
        $moderatorsteams = [];
        $moderatorscalendar = [];
        $organizatorteams = [];
        foreach ($moderators as $moderator) {
            $userteams = '';
            try {
                $email = $moderator->email;
                $graphresponse = $graph->createRequest('GET', "/users/$email")
                    ->addHeaders(['Content-Type' => 'application/json'])
                    ->setReturnType(Model\User::class)
                    ->execute();
                $userteams = json_decode(json_encode($graphresponse), true);
                $found = true;
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
                $moderatorsteams[] = $user;
                $organizatorteams = $userteams;

                $usercalendar = [
                    'emailAddress' => [
                        'address' => $email,
                        'name' => $userteams['displayName'],
                    ],
                    'type' => 'required',
                ];
                $moderatorscalendar[] = $usercalendar;
            }
        }

        $attendees = array_merge ($moderatorsteams, $participantsteams);
        $attendeescalendar = array_merge ($moderatorscalendar, $participantscalendar);

        $data = [
            'subject' => $session->name,
            'start' => [
                'dateTime' => $startdatetime,
                'timeZone' => "Europe/Paris",
            ],
            'end' => [
                'dateTime' => $enddatetime,
                'timeZone' => "Europe/Paris",
            ],

            'location' => [
                'displayName' => $session->name,
            ],

            'allowNewTimeProposals' => false,
            'isOnlineMeeting' => true,
            'onlineMeetingProvider' => 'teamsForBusiness',
            'attendees' => $attendeescalendar,
        ];

        // Get the user based on accessmethod: teams id organizer.
        $organiz = '';
        if (isset($organizatorteams['id'])) {
            $organiz = $organizatorteams['id'];
        } else {
            throw new \moodle_exception (get_string('emailorganizatornotfound','hybridteachvc_teams'), 'Teams');
        }
        $urlrequest = $this->geturlrequest($organiz);

        // Create event in calendar.
        try {
            $graphresponse = $graph
                ->createRequest("POST", $urlrequest."/events")
                ->attachBody($data)
                ->setReturnType(Model\OnlineMeeting::class)
                ->execute();

        } catch (\Exception $e) {
            throw new \moodle_exception ($e->getMessage(), 'Teams');
        }

        $result = json_decode(json_encode($graphresponse), true);
        $joinurl = $result['onlineMeeting']['joinUrl'];

        // Get the event meeting.
        $graphresponse = $graph
            ->createRequest("GET", $urlrequest.'/onlineMeetings?$filter'."=JoinWebUrl%20eq%20'".$joinurl."'")
            ->attachBody($data)
            ->setReturnType(Model\OnlineMeeting::class)
            ->execute();

        $result = json_decode(json_encode($graphresponse), true);

        $meetingid = $result[0]['id'];

        // Change info meeting with options from hybridteaching form.
        $data = [
            'allowAttendeeToEnableCamera' => $allowattendeetoenablecamera,
            'allowAttendeeToEnableMic' => $allowattendeetoenablemic,
            'allowMeetingChat' => $allowmeetingchat,
            'recordAutomatically' => $recordautomatically,
            'lobbyBypassSettings' => [
                'scope' => 'invited',
                'isDialInBypassEnabled' => false,
            ],
        ];

        $allowrecording = false;
        if (has_capability('hybridteachvc/teams:record', $context)
            && has_capability('mod/hybridteaching:record', $context) && $enabledrecording) {
            $allowrecording = true;
        }
        $data['allowRecording'] = $allowrecording;

        if (!empty($participants)) {
            $data['allowedPresenters'] = 'roleIsPresenter';
            $data['participants'] = [
                    'attendees' => $attendees,
            ];
        }

        try {
            $graphresponse = $graph
                ->createRequest("PATCH", $urlrequest."/onlineMeetings/$meetingid")
                ->attachBody($data)
                ->setReturnType(Model\OnlineMeeting::class)
                ->execute();
        } catch (\Exception $e) {
            throw new \moodle_exception ($e->getMessage(), 'Teams');
        }

        $result = json_decode(json_encode($graphresponse), true);
        return $result;
    }

    /**
     * Get the first part of url to call api Teams
     *
     * @param string $organizer
     * @return string The first part of url
     */
    public function geturlrequest($organizer) {
        // Accesstoken: app: 0, behalfuser: 1.
        $urlrequest = '';
        if ($this->config->accessmethod == 0) {
            $urlrequest = '/users'.'/'.$organizer;
        } else {
            $urlrequest = '/me';
        }
        return $urlrequest;
    }

    /**
     * Delete a meeting
     *
     * @param string $teams The team object.
     * @return void
     */
    public function deletemeeting ($teams) {

        $token = $this->refreshtoken();
        $graph = new Graph();
        $graph->setAccessToken($token);

        $urlrequest = $this->geturlrequest ($teams->organizer);

        try {
            $graphresponse = $graph
                ->createRequest("DELETE", $urlrequest.'/onlineMeetings/'.$teams->meetingid)
                ->setReturnType(Model\OnlineMeeting::class)
                ->execute();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Get the meeting recordings.
     *
     * @param string $folderfile Folder where to download the recording
     * @param int $meetingid The meeting id to download the recording
     * @param int $organizerid The id  of the organizer to download the recording from
     * @param int $course The ID of the course
     * @param string $name The name of the meeting
     * @return string $recordingid The id of the recording downloaded
     */
    public function get_meeting_recordings ($folderfile, $meetingid, $organizerid, $course, $name) {
        global $DB;
        $token = $this->refreshtoken();
        $graph = new Graph();
        $graph->setAccessToken($token);
        $urlrequest = $this->geturlrequest ($organizerid);

        $recordingid = 0;
        try {
            $graphresponse = $graph
                ->setApiVersion("beta")
                ->createRequest("GET", $urlrequest."/onlineMeetings/$meetingid/recordings")
                ->setReturnType(Model\RecordingInfo::class)
                ->execute();
        } catch (\Exception $e) {
            mtrace(get_string('recordingnotfound', 'hybridteachvc_teams',
                [
                    'course' => $course,
                    'name' => $name,
                    'meetingid' => $meetingid,
                ]));
            return;
        }
        $result = json_decode(json_encode($graphresponse), true);

        if (empty($result)) {
            mtrace(get_string('recordingnoexists', 'hybridteachvc_teams',
            [
                'course' => $course,
                'name' => $name,
                'meetingid' => $meetingid,
            ]));
        }
        $count = 0;

        foreach ($result as $rec) {
            if (isset($rec['id'])) {
                // This $download is for various recordings in the same meetingid: for reuse the url meeting.
                $recorders = $DB->get_records ('hybridteachvc_teams', ['meetingid' => $meetingid], '', 'id, recordingid');
                $download = true;
                foreach ($recorders as $recorder) {
                    // If is the same recordingid, is already download. So, dont download.
                    if ($rec['id'] == $recorder->recordingid) {
                        $download = false;
                        break;
                    }
                }
                if ($download) {
                    $count++;
                    $pathfile = $folderfile.'-'.$count.'.mp4';
                    $recordingid = $rec['id'];
                    try {
                        // Connect to recordingContentUrl to download.
                        $graphresponse = $graph
                            ->setApiVersion("beta")
                            ->createRequest("GET", $urlrequest."/onlineMeetings/$meetingid/recordings/$recordingid/content")
                            ->download($pathfile);
                    } catch (\Exception $e) {
                        mtrace(get_string('recordingnotdownload', 'hybridteachvc_teams',
                            [
                                'course' => $course,
                                'name' => $name,
                                'meetingid' => $meetingid,
                            ]));
                    }

                    $result = json_decode(json_encode($graphresponse), true);
                    break;
                }
            }
        }

        return $recordingid;
    }

    /**
     * Get the chat url of the meeting.
     *
     * @param int $meetingid The meeting id to get the chat url.
     * @param int $organizerid The id  of the organizer to get the chat url meeting.
     * @return string $urlchat The url of the chat meetingo.
     */
    public function getchatmeetingurl ($meetingid, $organizerid) {
        $token = $this->refreshtoken();
        $graph = new Graph();
        $graph->setAccessToken($token);
        $urlrequest = $this->geturlrequest ($organizerid);

        // Get meeting chat info.
        try {
            $graphresponse = $graph
                ->createRequest("GET", $urlrequest."/onlineMeetings/$meetingid")
                ->setReturnType(Model\MeetingInfo::class)
                ->execute();
        } catch (\Exception $e) {
            print "\nNot exists meetingid: ".$meetingid."<br>";
            return;
        }
        $result = json_decode(json_encode($graphresponse), true);

        $chatid = '';
        if (isset($result['chatInfo']['threadId'])) {
            $chatid = $result['chatInfo']['threadId'];
        }

        if ($chatid != '') {
            // Get the url from chat.
            try {
                $graphresponse = $graph
                    ->createRequest("GET", "/chats/".$chatid)
                    ->setReturnType(Model\ChatInfo::class)
                    ->execute();
            } catch (\Exception $e) {
                print "\nChat does not exist from meetingid: ".$meetingid."<br>";
                return;
            }

            $result = json_decode(json_encode($graphresponse), true);
            if (isset($result['webUrl'])) {
                return $result['webUrl'];
            }
            return '';
        }
    }

    /**
     * Get meeting info.
     *
     * @param int $meetingid The meeting id to get the chat url.
     * @param int $organizerid The id  of the organizer to get the chat url meeting.
     * @return mix $infomeeting The url of the chat meetingo.
     */
    public function getmeetinginfo ($meetingid, $organizerid) {
        $token = $this->refreshtoken();
        $graph = new Graph();
        $graph->setAccessToken($token);
        $urlrequest = $this->geturlrequest ($organizerid);

        // Get meeting chat info.
        try {
            $graphresponse = $graph
                ->createRequest("GET", $urlrequest."/onlineMeetings/$meetingid")
                ->setReturnType(Model\MeetingInfo::class)
                ->execute();
        } catch (\Exception $e) {
            print "\nNot exists meetingid: ".$meetingid."<br>";
            return null;
        }
        $result = json_decode(json_encode($graphresponse), true);
        return $result;
    }

    /**
     * Get meeting transcripts.
     *
     * @param string $folderfile Folder file path
     * @param int $meetingid Meeting id
     * @param int $organizerid Organizer id
     * @throws \Exception
     * @return mixed
     */
    public function get_meeting_transcripts ($folderfile, $meetingid, $organizerid) {
        $token = $this->refreshtoken();
        $graph = new Graph();
        $graph->setAccessToken($token);
        $urlrequest = $this->geturlrequest ($organizerid);

        $transcriptid = 0;
        try {
            $graphresponse = $graph
                ->setApiVersion("beta")
                ->createRequest("GET", $urlrequest."/onlineMeetings/$meetingid/transcripts")
                ->execute();
        } catch (\Exception $e) {
            print "\Transcript does not exist from meetingid: ".$meetingid."<br>";
            return;
        }
        $result = json_decode(json_encode($graphresponse), true);

        $count = 0;
        foreach ($result as $transcript) {
            if (isset($transcript['id'])) {
                $count++;
                $pathfile = $folderfile.'-'.$count.'.txt';
                $transcriptid = $transcript['id'];
                try {
                    // Connect to recordingContentUrl to download.
                    $graphresponse = $graph
                        ->setApiVersion("beta")
                        ->createRequest("GET", $urlrequest."/onlineMeetings/$meetingid/transcripts/$transcriptid/content")
                        ->download($pathfile);
                } catch (\Exception $e) {
                    print "\nCan't download transcript from meetingid: ".$meetingid."<br>";
                }
                $result = json_decode(json_encode($graphresponse), true);
            }
        }
        return $transcriptid;
    }
}
