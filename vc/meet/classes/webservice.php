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

use html_writer;

class webservice {

    /**
     * OAuth 2 client
     * @var \core\oauth2\client
     */
    private $client = null;

    /**
     * OAuth 2 Issuer
     * @var \core\oauth2\issuer
     */
    private $issuer = null;

    /** @var bool informs if the client is enabled */
    public $enabled = true;

    /**
     * Additional scopes required for drive.
     */
    const SCOPES = 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/calendar.events';

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        try {
            $test = get_config('googlemeet', 'issuerid');
            $this->issuer = \core\oauth2\api::get_issuer(get_config('googlemeet', 'issuerid'));
        } catch (\dml_missing_record_exception $e) {
            $this->enabled = false;
        }

        if ($this->issuer && (!$this->issuer->get('enabled') || $this->issuer->get('id') == 0)) {
            $this->enabled = false;
        }

        $client = $this->get_user_oauth_client();
        if ($this->enabled && $client->get_login_url()->get_host() !== 'accounts.google.com') {
            throw new \moodle_exception('invalidissuerid', 'googlemeet');
        }
    }

    /**
     * Get a cached user authenticated oauth client.
     *
     * @return \core\oauth2\client
     */
    protected function get_user_oauth_client() {
        if ($this->client) {
            return $this->client;
        }

        $returnurl = new \moodle_url('/mod/hybridteaching/vc/meet/callback.php');
        $returnurl->param('callback', 'yes');
        $returnurl->param('sesskey', sesskey());

        $this->client = self::get_user_client($this->issuer, $returnurl, self::SCOPES, true);

        return $this->client;
    }

    public static function get_user_client($issuer, $currenturl, $additionalscopes = '',
        $autorefresh = false) {
        $class = self::get_client_classname(null);
        $client = new $class($issuer, $currenturl, $additionalscopes, false, $autorefresh);
        return $client;
    }

    protected static function get_client_classname(?string $type): string {
        $classname = 'core\\oauth2\\client';

        if (!empty($type)) {
            $typeclassname = 'core\\oauth2\\client\\' . $type;
            if (class_exists($typeclassname)) {
                $classname = $typeclassname;
            }
        }

        return $classname;
    }


    /**
     * Print the login in a popup.
     *
     * @param array|null $attr Custom attributes to be applied to popup div.
     *
     * @return string HTML code
     */
    public function print_login_popup($attr = null) {
        global $OUTPUT;

        $client = $this->get_user_oauth_client();
        $url = new \moodle_url($client->get_login_url());
        $state = $url->get_param('state') . '&reloadparent=true';
        $url->param('state', $state);

        return html_writer::div('
            <button class="btn btn-primary" onClick="javascript:window.open(\''.$client->get_login_url().'\',
                \'Login\',\'height=600,width=599,top=0,left=0,menubar=0,location=0,directories=0,fullscreen=0\'
            ); return false">'.get_string('logintoaccount', 'googlemeet').'</button>', 'mt-2');

    }

    /**
     * Print user info.
     *
     * @param string|null $scope 'calendar' or 'drive' Defines which link will be used.
     *
     * @return string HTML code
     */
    public function print_user_info($scope = null) {
        global $OUTPUT, $PAGE;

        if (!$this->check_login()) {
            return '';
        }

        $userauth = $this->get_user_oauth_client();
        $userinfo = $userauth->get_userinfo();

        $username = $userinfo['username'];
        $name = $userinfo['firstname'].' '.$userinfo['lastname'];
        $userpicture = base64_encode($userinfo['picture']);

        $userurl = '#';
        if ($scope == 'calendar') {
            $userurl = new \moodle_url('https://calendar.google.com/');
        }
        if ($scope == 'drive') {
            $userurl = new \moodle_url('https://drive.google.com/');
        }

        $logouturl = new \moodle_url($PAGE->url);
        $logouturl->param('logout', true);

        $img = html_writer::img('data:image/jpeg;base64,'.$userpicture, '');
        $out = html_writer::start_div('', ['id' => 'googlemeet_auth-info']);
        $out .= html_writer::link($userurl, $img,
            ['id' => 'googlemeet_picture-user', 'target' => '_blank', 'title' => get_string('manage', 'googlemeet')]
        );
        $out .= html_writer::start_div('', ['id' => 'googlemeet_user-name']);
        $out .= html_writer::span(get_string('loggedinaccount', 'googlemeet'), '');
        $out .= html_writer::span($name);
        $out .= html_writer::span($username);
        $out .= html_writer::end_div();
        $out .= html_writer::link($logouturl,
            $OUTPUT->pix_icon('logout', '', 'googlemeet', ['class' => 'm-0']),
            ['class' => 'btn btn-secondary btn-sm', 'title' => get_string('logout', 'googlemeet')]
        );

        $out .= html_writer::end_div();

        return $out;
    }

    /**
     * Checks whether the user is authenticate or not.
     *
     * @return bool true when logged in.
     */
    public function check_login() {
        $client = $this->get_user_oauth_client();
        return $client->is_logged_in();
    }

    /**
     * Logout.
     *
     * @return void
     */
    public function logout() {
        global $PAGE;

        if ($this->check_login()) {
            $url = new \moodle_url($PAGE->url);
            $client = $this->get_user_oauth_client();
            $client->log_out();
            $js = <<<EOD
                <html>
                <head>
                    <script type="text/javascript">
                        window.location = '{$url}'.replaceAll('&amp;','&')
                    </script>
                </head>
                <body></body>
                </html>
            EOD;
            die($js);
        }
    }

    /**
     * Store the access token.
     *
     * @return void
     */
    public function callback() {
        $client = $this->get_user_oauth_client();
        // This will upgrade to an access token if we have an authorization code and save the access token in the session.
        $client->is_logged_in();
    }

    /**
     * Create a meeting event in Google Calendar
     *
     * @param object $googlemeet An object from the form.
     *
     * @return object Google Calendar event
     */
    public function create_meeting_event($meet) {
        global $USER;

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
        $recurrence = '';

        $eventrawpost = [
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
        ];

        $service = new rest($this->get_user_oauth_client());

        $eventparams = [
            'calendarid' => $calendarid,
        ];

        $eventresponse = helper::request($service, 'insertevent', $eventparams, json_encode($eventrawpost));

        $conferenceparams = [
            'calendarid' => $calendarid,
            'eventid' => $eventresponse->id,
            'conferenceDataVersion' => 1,
        ];

        $conferencerawpost = [
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => $eventresponse->id,
                ],
            ],
        ];

        $conferenceresponse = helper::request($service, 'createconference', $conferenceparams, json_encode($conferencerawpost));

        return $conferenceresponse;

    }

    /**
     * Get recordings from Google Drive and sync with database.
     *
     * @return void
     */
    public function syncrecordings($googlemeet) {
        global $PAGE;

        if ($this->check_login()) {
            $service = new rest($this->get_user_oauth_client());

            $folderparams = [
                'q' => 'name = "Meet Recordings" and
                        trashed = false and
                        mimeType = "application/vnd.google-apps.folder" and
                        "me" in owners',
                'pageSize' => 1000,
                'fields' => 'nextPageToken, files(id,owners)',
            ];

            $folderresponse = helper::request($service, 'list', $folderparams, false);

            $folders = $folderresponse->files;
            $parents = '';
            for ($i = 0; $i < count($folders); $i++) {
                $parents .= 'parents="'.$folders[$i]->id.'"';
                if ($i + 1 < count($folders)) {
                    $parents .= ' or ';
                }
            }

            $meetingcode = substr($googlemeet->url, 24, 12);
            $name = $googlemeet->name;
            $recordingparams = [
                'q' => '('.$parents.') and
                        trashed = false and
                        mimeType = "video/mp4" and
                        "me" in owners and
                        (name contains "'.$meetingcode.'" or name contains "'.$name.'")',
                'pageSize' => 1000,
                'fields' => 'files(id,name,permissionIds,createdTime,videoMediaMetadata,webViewLink)',
            ];

            $recordingresponse = helper::request($service, 'list', $recordingparams, false);

            $recordings = $recordingresponse->files;

            if ($recordings && count($recordings) > 0) {
                for ($i = 0; $i < count($recordings); $i++) {
                    $recording = $recordings[$i];

                    // If the recording has already been processed.
                    if (isset($recording->videoMediaMetadata)) {
                        if (!in_array('anyoneWithLink', $recording->permissionIds)) {
                            $permissionparams = [
                                'fileid' => $recording->id,
                                'fields' => 'id',
                            ];
                            $permissionrawpost = [
                                "role" => "reader",
                                "type" => "anyone",
                            ];
                            helper::request($service, 'create_permission', $permissionparams, json_encode($permissionrawpost));
                        }

                        // Format it into a human-readable time.
                        $duration = $this->formatseconds((int)$recording->videoMediaMetadata->durationMillis);

                        $createdtime = new \DateTime($recording->createdTime);

                        $recordings[$i]->recordingId = $recording->id;
                        $recordings[$i]->duration = $duration;
                        $recordings[$i]->createdTime = $createdtime->getTimestamp();

                        unset($recordings[$i]->id);
                        unset($recordings[$i]->permissionIds);
                        unset($recordings[$i]->videoMediaMetadata);
                    } else {
                        $recordings[$i]->unprocessed = true;
                    }
                }

                sync_recordings($googlemeet->id, $recordings);
            }

            $url = new \moodle_url($PAGE->url);
            $js = <<<EOD
                <html>
                <head>
                    <script type="text/javascript">
                        window.location = '{$url}'.replaceAll('&amp;','&')
                    </script>
                </head>
                <body></body>
                </html>
            EOD;
            die($js);
        }
    }

    /**
     * Create a meeting event in Google Calendar
     *
     * @param int $milli The time in milliseconds.
     *
     * @return string The formatted time
     */
    protected function formatseconds($milli=0) {
        $secs = $milli / 1000;

        if ($secs < MINSECS) {
            return '0:'. str_pad(floor($secs), 2, "0", STR_PAD_LEFT);
        } else if ($secs >= MINSECS && $secs < HOURSECS) {
            return floor($secs / MINSECS) .':'. str_pad(floor($secs % MINSECS), 2, "0", STR_PAD_LEFT);
        } else {
            return floor($secs / HOURSECS) .':'.
                str_pad(floor(($secs % HOURSECS) / MINSECS), 2, "0", STR_PAD_LEFT) .':'.
                str_pad(floor(($secs % HOURSECS) % MINSECS), 2, "0", STR_PAD_LEFT);
        }
    }

    /**
     * Get the email of the logged in Google account
     *
     * @return string The email
     */
    public function get_email() {
        if (!$this->check_login()) {
            return '';
        }

        $userauth = $this->get_user_oauth_client()->get_userinfo();
        return $userauth['username'];
    }

}
