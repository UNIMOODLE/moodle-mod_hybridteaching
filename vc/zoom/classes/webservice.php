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
 * @package    hybridteachvc_zoom
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace hybridteachvc_zoom;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/hybridteaching/vc/zoom/locallib.php');
require_once($CFG->dirroot.'/lib/filelib.php');

if (!class_exists('Firebase\JWT\JWT')) {
    if (file_exists($CFG->dirroot.'/lib/php-jwt/src/JWT.php')) {
        require_once($CFG->dirroot.'/lib/php-jwt/src/JWT.php');
    } else {
        if (file_exists($CFG->dirroot.'/mod/bigbluebuttonbn/vendor/firebase/php-jwt/src/JWT.php')) {
            require_once($CFG->dirroot.'/mod/bigbluebuttonbn/vendor/firebase/php-jwt/src/JWT.php');
        } else {
            require_once($CFG->dirroot.'/mod/isyczoomav/jwt/JWT.php');
        }
    }
}

define('HTZOOM_API_URL', 'https://api.zoom.us/v2/');

/**
 * Class webservice.
 */
class webservice {

    /**
     * Client ID
     * @var string
     */
    protected $clientid;

    /**
     * Client secret
     * @var string
     */
    protected $clientsecret;

    /**
     * Account ID
     * @var string
     */
    protected $accountid;

    /**
     * email license
     * @var string
     */
    protected $emaillicense;

    /**
     * API base URL.
     * @var string
     */
    protected $apiurl;

    /**
     * List of users
     * @var array
     */
    protected static $userslist;

    /**
     * name group
     * @var string
     */
    protected $namegroup;

    /**
     * The constructor for the webservice class.
     * @param object $zoomconfig The Zoom config object.
     * @throws \moodle_exception Moodle exception is thrown for missing config settings.
     */
    public function __construct($zoomconfig) {
        try {
            $this->accountid = $zoomconfig->accountid;
            $this->clientid = $zoomconfig->clientid;
            $this->clientsecret = $zoomconfig->clientsecret;
            $this->emaillicense = $zoomconfig->emaillicense;

            // Get and remember the API URL.
            $this->apiurl = HTZOOM_API_URL;
        } catch (\moodle_exception $exception) {
            throw new \moodle_exception('errorwebservice', 'htzoom', '', $exception->getMessage());
        }
    }

    /**
     * Makes the call to curl using the specified method, url, and parameter data.
     * This has been moved out of make_call to make unit testing possible.
     *
     * @param \curl $curl The curl object used to make the request.
     * @param string $method The HTTP method to use.
     * @param string $url The URL to append to the API URL
     * @param array|string $data The data to attach to the call.
     * @return stdClass The call's result.
     */
    protected function make_curl_call(&$curl, $method, $url, $data) {
        return $curl->$method($url, $data);
    }

    /**
     * Gets a curl object in order to make API calls. This function was created
     * to enable unit testing for the webservice class.
     * @return curl The curl object used to make the API calls
     */
    protected function get_curl_object() {
        return new \curl();
    }

    /**
     * Makes a REST call.
     *
     * @param string $path The URL to append to the API URL
     * @param array|string $data The data to attach to the call.
     * @param string $method The HTTP method to use.
     * @return stdClass The call's result in JSON format.
     * @throws \moodle_exception Moodle exception is thrown for curl errors.
     */
    protected function make_call($path, $data = [], $method = 'get') {
        $url = $this->apiurl . $path;
        $method = strtolower($method);
        $curl = new \curl();

        if (isset($this->clientid) && isset($this->clientsecret) && isset($this->accountid)) {
            $token = $this->get_access_token();
        }

        $curl->setHeader('Authorization: Bearer ' . $token);
        $curl->setHeader('Accept: application/json');

        if ($method != 'get') {
            $curl->setHeader('Content-Type: application/json');
            $data = is_array($data) ? json_encode($data) : $data;
        }

        $rawresponse = $this->make_curl_call($curl, $method, $url, $data);

        if ($curl->get_errno()) {
            throw new \moodle_exception('errorwebservice', 'htzoom', '', $curl->error);
        }

        $response = json_decode($rawresponse);

        $httpstatus = $curl->get_info()['http_code'];

        if ($httpstatus >= 400) {
            if ($response) {
                throw new \moodle_exception('errorwebservice', 'htzoom', '',
                    get_string('errorwebservice', 'hybridteachvc_zoom', $response->message));
            } else {
                throw new \moodle_exception('errorwebservice', 'htzoom', '', "HTTP Status $httpstatus");
            }
        }

        return $response;
    }


    /**
     * Make a call to download data from a specified path using the given method.
     *
     * @param string $path The path from which to download data
     * @param array $data An optional array of data to be sent with the request
     * @param string $method An optional string representing the HTTP method to be used
     * @throws \moodle_exception If an error occurs during the web service call
     * @return mixed The raw response from the server
     */
    public function make_call_download($path, $data = [], $method = 'get') {

        $url = $path;
        $method = strtolower($method);
        $curl = new \curl();

        if (isset($this->clientid) && isset($this->clientsecret) && isset($this->accountid)) {
            $token = $this->get_access_token();
        }

        $curl->setHeader('Authorization: Bearer ' . $token);
        $curl->setHeader('Accept: application/json');

        if ($method != 'get') {
            $data = is_array($data) ? json_encode($data) : $data;
        }

        $rawresponse = $this->make_curl_call($curl, $method, $url, $data);

        if ($curl->get_errno()) {
            throw new \moodle_exception('errorwebservice', 'htzoom', '', $curl->error);
        }

        return $rawresponse;
    }

    /**
     * Makes a paginated REST call.
     * Makes a call like make_call() but specifically for GETs with paginated results.
     *
     * @param string $url The URL to append to the API URL
     * @param array|string $data The data to attach to the call.
     * @param string $datatoget The name of the array of the data to get.
     * @return array The retrieved data.
     * @see make_call()
     * @link https://zoom.github.io/api/#list-users
     */
    protected function make_paginated_call($url, $data = [], $datatoget = null) {
        $aggregatedata = [];
        $data['page_size'] = HTZOOM_MAX_RECORDS_PER_CALL;
        $reportcheck = explode('/', $url);
        $isreportcall = in_array('report', $reportcheck);
        // The $currentpage call parameter is 1-indexed.
        for ($currentpage = $numpages = 1; $currentpage <= $numpages; $currentpage++) {
            $data['page_number'] = $currentpage;

            $callresult = $this->make_call($url, $data);

            if ($callresult) {
                $aggregatedata = array_merge($aggregatedata, $callresult->$datatoget);
                // Note how continually updating $numpages accomodates for the edge case that users are added in between calls.
                $numpages = $callresult->page_count;
            }
        }

        return $aggregatedata;
    }

    /**
     * Get users list.
     *
     * @return array An array of users.
     * @link https://zoom.github.io/api/#list-users
     */
    public function list_users() {
        if (empty(self::$userslist)) {
            self::$userslist = $this->make_paginated_call('users', null, 'users');
        }
        return self::$userslist;
    }

    /**
     * Gets a user's settings.
     *
     * @param string $userid The user's ID.
     * @return stdClass The call's result in JSON format.
     * @link https://zoom.github.io/api/#retrieve-a-users-settings
     */
    public function get_user_settings($userid) {
        return $this->make_call('users/' . $userid . '/settings');
    }

    /**
     * Gets a user.
     *
     * @param string|int $identifier The user's email or the user's ID per Zoom API.
     * @return stdClass|false If user is found, returns the User object. Otherwise, returns false.
     * @link https://zoom.github.io/api/#users
     */
    public function get_user($identifier) {
        $founduser = false;

        $url = 'users/' . $identifier;

        try {
            $founduser = $this->make_call($url);
        } catch (\moodle_exception $error) {
            if (htzoom_is_user_not_found_error($error->getMessage())) {
                return false;
            } else {
                throw $error;
            }
        }

        return $founduser;
    }

    /**
     * Gets roles
     *
     * @return stdClass|false If roles is found, returns the roles object. Otherwise, returns false.
     * @link https://zoom.github.io/api/#users
     */
    public function get_roles() {
        $founduser = false;
        $url = 'roles/';

        try {
            $foundroles = $this->make_call($url);
        } catch (\moodle_exception $error) {
            if (htzoom_is_roles_not_found_error($error->getMessage())) {
                return false;
            } else {
                throw $error;
            }
        }
        return $foundroles;
    }

    /**
     * Retrieves the list of users from the API.
     *
     * @return mixed The list of users or false if not found
     * @throws \moodle_exception When an error occurs
     */
    public function get_users() {
        $founduser = false;
        $url = 'users/';

        try {
            $foundusers = $this->make_call($url);
        } catch (\moodle_exception $error) {
            if (htzoom_is_users_not_found_error($error->getMessage())) {
                return false;
            } else {
                throw $error;
            }
        }
        return $foundusers;
    }

    /**
     * Converts a zoom object from database format to API format.
     *
     * The database and the API use different fields and formats for the same information. This function changes the
     * database fields to the appropriate API request fields.
     *
     * @param object $zoom The zoom meeting to format.
     * @param object $ht The zoom meeting to format.
     * @return array The formatted meetings for the meeting.
     */
    protected function database_to_api($zoom, $ht) {
        global $CFG;

        $data = [
            'topic' => $zoom->name,
        ];
        if (isset($CFG->timezone) && !empty($CFG->timezone)) {
            $data['timezone'] = $CFG->timezone;
        } else {
            $data['timezone'] = date_default_timezone_get();
        }

        // Get instance context.
        $cm = get_coursemodule_from_instance ('hybridteaching', $ht->id);
        $context = \context_module::instance($cm->id);

        /* This auto_recording option does not work in the api correctly,
            since it is activated/deactivated from the account and not from the meeting.
            If the account has it activated, it is activated
            If the account has it deactivated, it remains deactivated.
        */
        $enabledrecording = get_config('enabledrecording', 'hybridteachvc_zoom');
        if ($ht->userecordvc && has_capability('hybridteachvc/zoom:record', $context)
            && has_capability('mod/hybridteaching:record', $context) && $enabledrecording) {
            if (isset($ht->initialrecord) && $ht->initialrecord == 1) {
                $data['auto_recording'] = HTZOOM_RECORDING_CLOUD;
            }
        } else {
            $data['auto_recording'] = HTZOOM_RECORDING_DISABLED;
        }

        $data['type'] = $ht->reusesession ? HTZOOM_RECURRING_MEETING : HTZOOM_SCHEDULED_MEETING;

        if ($data['type'] == HTZOOM_SCHEDULED_MEETING) {
            // Convert timestamp to ISO-8601. The API seems to insist that it end with 'Z' to indicate UTC.
            $data['start_time'] = gmdate('Y-m-d\TH:i:s\Z', $zoom->starttime);
            $data['duration'] = (int) ceil($zoom->duration / 60);
        }

        if (isset($ht->waitmoderator)) {
            $data['settings']['join_before_host'] = ! (bool) ($ht->waitmoderator);
        }

        if (isset($ht->disablecam)) {
            $data['settings']['host_video'] = (bool)!$ht->disablecam;
            $data['settings']['participant_video'] = (bool)!$ht->disablecam;
        }

        if (isset($ht->disablemic)) {
            $data['settings']['mute_upon_entry'] = (bool)$ht->disablemic;
        }

        return $data;
    }

    /**
     * Create a meeting/webinar on Zoom.
     * Take a $zoom object as returned from the Moodle form and respond with an object that can be saved to the database.
     *
     * @param object $zoom The meeting to create.
     * @param object $ht The hybridteaching object
     * @return object The call response.
     */
    public function create_meeting($zoom, $ht) {
        $zoom->undatedsession = $ht->sessionscheduling ? 0 : 1;
        $url = 'users/'.$this->emaillicense.'/meetings';
        $response = $this->make_call($url, $this->database_to_api($zoom, $ht), 'post');
        return $response;
    }

    /**
     * Update a meeting/webinar on Zoom.
     *
     * @param object $zoom The meeting to update.
     * @param object $ht The hybridteaching object
     * @return void
     */
    public function update_meeting($zoom, $ht) {
        $zoom->undatedsession = $ht->sessionscheduling ? 0 : 1;
        $url = 'meetings/' . $zoom->meetingid;
        $this->make_call($url, $this->database_to_api($zoom, $ht), 'patch');
    }

    /**
     * Delete a meeting or webinar on Zoom.
     *
     * @param int $id The meeting_id or webinar_id of the meeting or webinar to delete.
     * @param bool $webinar Whether the meeting or webinar you want to delete is a webinar.
     * @return void
     */
    public function deletemeeting($id, $webinar) {
        $url = ($webinar ? 'webinars/' : 'meetings/') . $id;
        try {
            $this->make_call($url, null, 'delete');
        } catch (\Exception $e) {
            // No action for delete.
            return null;
        }

    }

    /**
     * Get a meeting or webinar's information from Zoom.
     *
     * @param int $id The meeting_id or webinar_id of the meeting or webinar to retrieve.
     * @param bool $webinar Whether the meeting or webinar whose information you want is a webinar.
     * @return stdClass The meeting's or webinar's information.
     */
    public function get_meeting_webinar_info($id, $webinar) {
        $url = ($webinar ? 'webinars/' : 'meetings/') . $id;
        $response = null;
        try {
            $response = $this->make_call($url);
        } catch (\moodle_exception $error) {
            throw $error;
        }
        return $response;
    }

    /**
     * Retrieves the meeting recordings for a given ID.
     *
     * @param int $id The ID of the meeting.
     * @throws \moodle_exception If an error occurs during the API call.
     * @return mixed The response from the API call.
     */
    public function get_meeting_recordings($id) {
        $url = 'meetings/'.$id.'/recordings?include_fields=download_access_token';

        $response = null;
        try {
            $response = $this->make_call($url);
        } catch (\moodle_exception $error) {
            throw $error;
        }
        return $response;
    }

    /**
     * Retrieves the recording settings for a specific meeting.
     *
     * @param int $id The ID of the meeting.
     * @throws \moodle_exception If an error occurs while making the API call.
     * @return mixed The response from the API call.
     */
    public function get_meeting_recordingsettings($id) {
        $url = 'meetings/' . $id . '/recordings/settings';
        $response = null;
        try {
            $response = $this->make_call($url);
        } catch (\moodle_exception $error) {
            throw $error;
        }
        return $response;

    }

    /**
     * Retrieves the UUID of past meetings.
     *
     * @param int $id The ID of the meeting to retrieve UUID for.
     * @throws \moodle_exception If an error occurs while making the API call.
     * @return mixed The response from the API call.
     */
    public function get_past_meetings_uuid($id) {
        $url = '/past_meetings/'.$id.'/instances';
        $response = null;
        try {
            $response = $this->make_call($url);
        } catch (\moodle_exception $error) {
            throw $error;
        }
        return $response;
    }

    /**
     * Retrieve ended meetings report for a specified user and period. Handles multiple pages.
     *
     * @param int $userid Id of user of interest
     * @param string $from Start date of period in the form YYYY-MM-DD
     * @param string $to End date of period in the form YYYY-MM-DD
     * @return array The retrieved meetings.
     * @link https://zoom.github.io/api/#retrieve-meetings-report
     */
    public function get_user_report($userid, $from, $to) {
        $url = 'report/users/' . $userid . '/meetings';
        $data = [
            'from' => $from,
            'to' => $to,
            'page_size' => HTZOOM_MAX_RECORDS_PER_CALL,
        ];
        return $this->make_paginated_call($url, $data, 'meetings');
    }

    /**
     * List all meeting or webinar information for a user.
     *
     * @param string $userid The user whose meetings or webinars to retrieve.
     * @param boolean $webinar Whether to list meetings or to list webinars.
     * @return array An array of meeting information.
     * @link https://zoom.github.io/api/#list-webinars
     * @link https://zoom.github.io/api/#list-meetings
     */
    public function list_meetings($userid, $webinar) {
        $url = 'users/' . $userid . ($webinar ? '/webinars' : '/meetings');
        $configs = $this->make_paginated_call($url, null, ($webinar ? 'webinars' : 'meetings'));
        return $configs;
    }

    /**
     * Get attendees for a particular UUID ("session") of a webinar.
     *
     * @param string $uuid The UUID of the webinar session to retrieve.
     * @return array The attendees.
     * @link https://zoom.github.io/api/#list-a-webinars-registrants
     */
    public function list_webinar_attendees($uuid) {
        $url = 'webinars/' . $uuid . '/registrants';
        return $this->make_paginated_call($url, null, 'registrants');
    }

    /**
     * Get details about a particular webinar UUID/session.
     *
     * @param string $uuid The uuid of the webinar to retrieve.
     * @return stdClass A JSON object with the webinar's details.
     * @link https://zoom.github.io/api/#retrieve-a-webinar
     */
    public function get_metrics_webinar_detail($uuid) {
        return $this->make_call('webinars/' . $uuid);
    }

    /**
     * Get the participants who attended a meeting
     * @param string $meetinguuid The meeting or webinar's UUID.
     * @param bool $webinar Whether the meeting or webinar whose information you want is a webinar.
     * @return stdClass The meeting report.
     */
    public function get_meeting_participants($meetinguuid, $webinar) {
        return $this->make_paginated_call('report/' . ($webinar ? 'webinars' : 'meetings') . '/'
                                           . $meetinguuid . '/participants', null, 'participants');
    }

    /**
     * Retrieves ended webinar details report.
     *
     * @param string|int $identifier The webinar ID or webinar UUID. If given webinar ID, Zoom will take the last webinar config.
     */
    public function get_webinar_details_report($identifier) {
        return $this->make_call('report/webinars/' . $identifier);
    }

    /**
     * Retrieve the UUIDs of hosts that were active in the last 30 days.
     *
     * @param int $from The time to start the query from, in Unix timestamp format.
     * @param int $to The time to end the query at, in Unix timestamp format.
     * @return array An array of UUIDs.
     */
    public function get_active_hosts_uuids($from, $to) {
        $users = $this->make_paginated_call('report/users', ['type' => 'active', 'from' => $from, 'to' => $to], 'users');
        $uuids = [];
        foreach ($users as $user) {
            $uuids[] = $user->id;
        }
        return $uuids;
    }


    /**
     * Creates a meeting calendar.
     *
     * @param object $ht The hybridteaching instance.
     * @param object $zoom The zoom meeting.
     * @throws \moodle_exception The exception thrown.
     * @return mixed
     */
    public function create_meeting_calendar($ht, $zoom) {

        $zoomuserid = false;
        $service = new webservice($zoom);
        try {
            $zoomuser = $service->get_user($zoom->licencia);
            if ($zoomuser !== false) {
                $zoomuserid = $zoomuser->id;
                $zoom->host_id = $zoomuserid;
            } else {
                $zoom->host_id = false;
                return false;
            }
        } catch (\moodle_exception $error) {
            throw $error;
        }

        if ($zoom->host_id != false) {
            $url = "users/$zoom->host_id/" . (isset($zoom->webinar) && $zoom->webinar ? 'webinars' : 'meetings');
            return $this->make_call($url, $this->database_to_api($zoom, $ht), 'post');
        }
    }

    /**
     * Get group's list.
     *
     * @return array An array of users.
     * @link https://zoom.github.io/api/groups
     */
    public function get_groups() {
        $grupos = $this->make_call('groups');
        return $grupos;
    }


    /**
     * Retrieves the members of a group.
     *
     * @throws Some_Exception_Class If an error occurs while making the API call.
     * @return array The array of group members.
     */
    public function get_group_members() {
        $idgroup = get_config('isyczoomav', 'idgroup');
        $groupmembers = $this->make_paginated_call('groups/'.$idgroup."/members", null, 'members');
        return $groupmembers;
    }

    /**
     * Returns a server to server oauth access token, good for 1 hour.
     *
     * @throws \moodle_exception
     * @return string access token
     */
    public function get_access_token() {
        $token = $this->oauth();
        return $token;
    }

    /**
     * Stores token and expiration in cache, returns token from OAuth call.
     *
     * @throws \moodle_exception
     * @return string access token
     */
    private function oauth() {
        $curl = new \curl();
        $curl->setHeader('Authorization: Basic ' . base64_encode($this->clientid . ':' . $this->clientsecret));
        $curl->setHeader('Content-Type: application/json');

        // Force HTTP/1.1 to avoid HTTP/2 "stream not closed" issue.
        $curl->setopt([
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
        ]);

        $response = $this->make_curl_call($curl, 'post',
            'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=' . $this->accountid, []);

        if ($curl->get_errno()) {
            throw new \moodle_exception('errorwebservice', 'htzoom', '', $curl->error);
        }

        $response = json_decode($response);
        if (isset($response->access_token)) {
            $token = $response->access_token;
            return $token;
        } else {
            throw new \moodle_exception('errorwebservice', 'htzoom', '',
                get_string('zoomerr_no_access_token', 'hybridteachvc_zoom'));
        }
    }

}
