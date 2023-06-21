<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Handles API calls to Zoom REST API.
 *
 * @package   mod_isyczoomav
 * @copyright 2015 UC Regents
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/hybridteaching/vc/zoom/locallib.php');
require_once($CFG->dirroot.'/lib/filelib.php');

// Some plugins already might include this library, like mod_bigbluebuttonbn.
// Hacky, but need to create whitelist of plugins that might have JWT library.
// NOTE: Remove file_exists checks and the JWT library in mod when versions prior to Moodle 3.7 is no longer supported
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
 * Web service class.
 *
 * @package    mod_isyczoomav
 * @copyright  2015 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_hybrid_webservice {

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
     * host ID
     * @var string
     */
    //protected $hostid;

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
     * @throws moodle_exception Moodle exception is thrown for missing config settings.
     */
    public function __construct($zoominstance) {

        try{
            $this->accountid=$zoominstance->accountid;
            $this->clientid=$zoominstance->clientid;
            $this->clientsecret=$zoominstance->clientsecret;
            //$this->hostid=$zoominstance->hostid;
            $this->emaillicense=$zoominstance->emaillicense;

            /*$this->clientid="baIYr83oQNSCufK4YctaA";
            $this->clientsecret="m08ydwE3yoNDG0dznXHdcfIfRCaEMklE";
            $this->accountid="sav-NZcjSqWvNHcdmP_JMQ";
            */
                      
            // Get and remember the API URL.
            $this->apiurl = HTZOOM_API_URL;
            
        } catch (moodle_exception $exception) {
            throw new moodle_exception('errorwebservice', 'htzoom', '', $exception->getMessage());
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
        return new curl();
    }
    
    /**
     * Makes a REST call.
     *
     * @param string $url The URL to append to the API URL
     * @param array|string $data The data to attach to the call.
     * @param string $method The HTTP method to use.
     * @return stdClass The call's result in JSON format.
     * @throws moodle_exception Moodle exception is thrown for curl errors.
     */
    protected function _make_call($path, $data = array(), $method = 'get') {
        $url = $this->apiurl . $path;
        $method = strtolower($method);
        $curl = new curl();

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
        
        //$response = call_user_func_array(array($curl, $method), array($path, $data));

        if ($curl->get_errno()) {
            throw new moodle_exception('errorwebservice', 'htzoom', '', $curl->error);
        }

        $response = json_decode($rawresponse);

        $httpstatus = $curl->get_info()['http_code'];
        
        if ($httpstatus >= 400) {
            if ($response) {
                throw new moodle_exception('errorwebservice', 'htzoom', '', get_string('errorwebservice', 'hybridteachvc_zoom',$response->message) );
            } else {
                throw new moodle_exception('errorwebservice', 'htzoom', '', "HTTP Status $httpstatus");
            }
        }

        return $response;
    }


    public function _make_call_download($path, $data = array(), $method = 'get') {
 
        $url = $path;
        $method = strtolower($method);
        $curl = new curl();

        if (isset($this->clientid) && isset($this->clientsecret) && isset($this->accountid)) {
            $token = $this->get_access_token();
        }

        $curl->setHeader('Authorization: Bearer ' . $token);
        $curl->setHeader('Accept: application/json');

        if ($method != 'get') {
            //$curl->setHeader('Content-Type: application/json');
            $data = is_array($data) ? json_encode($data) : $data;
        }

        $rawresponse = $this->make_curl_call($curl, $method, $url, $data);
    
        if ($curl->get_errno()) {
            throw new moodle_exception('errorwebservice', 'htzoom', '', $curl->error);
        }

        return $rawresponse;
    }

    /**
     * Makes a paginated REST call.
     * Makes a call like _make_call() but specifically for GETs with paginated results.
     *
     * @param string $url The URL to append to the API URL
     * @param array|string $data The data to attach to the call.
     * @param string $datatoget The name of the array of the data to get.
     * @return array The retrieved data.
     * @see _make_call()
     * @link https://zoom.github.io/api/#list-users
     */
    protected function _make_paginated_call($url, $data = array(), $datatoget = null) {
        $aggregatedata = array();
        $data['page_size'] = HTZOOM_MAX_RECORDS_PER_CALL;
        $reportcheck = explode('/', $url);
        $isreportcall = in_array('report', $reportcheck);
        // The $currentpage call parameter is 1-indexed.
        for ($currentpage = $numpages = 1; $currentpage <= $numpages; $currentpage++) {
            $data['page_number'] = $currentpage;

            $callresult = $this->_make_call($url, $data);

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
            self::$userslist = $this->_make_paginated_call('users', null, 'users');
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
    public function _get_user_settings($userid) {
        return $this->_make_call('users/' . $userid . '/settings');
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
            $founduser = $this->_make_call($url);
        } catch (moodle_exception $error) {
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
            $foundroles = $this->_make_call($url);
        } catch (moodle_exception $error) {
            if (htzoom_is_roles_not_found_error($error->getMessage())) {
                return false;
            } else {
                throw $error;
            }
        }
        return $foundroles;
    }
    
    
    public function get_users(){
        $founduser = false;
        $url = 'users/';

        try {
            $foundusers = $this->_make_call($url);
        } catch (moodle_exception $error) {
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
     * @param stdClass $zoom The zoom meeting to format.
     * @return array The formatted meetings for the meeting.
     * @todo Add functionality for 'alternative_hosts' => $zoom->option_alternative_hosts in $data['settings']
     * @todo Make UCLA data fields and API data fields match?
     */
    protected function _database_to_api($zoom) {
        global $CFG;



        $data = array(
            'topic' => $zoom->name,
            //'settings' => array(
                //'host_video' => (bool) ($zoom->option_host_video),
                //'audio' => $zoom->option_audio
            //)
        );
        if (isset($zoom->intro)) {
            $data['agenda'] = strip_tags($zoom->intro);
        }
        if (isset($CFG->timezone) && !empty($CFG->timezone)) {
            $data['timezone'] = $CFG->timezone;
        } else {
            $data['timezone'] = date_default_timezone_get();
        }


        /*ESTA OPCION DE auto_recording NO FUNCIONA EN LA API CORRECTAMENTE
         SE ACTIVA/DESACTIVA DESDE LA CUENTA, NO DESDE LA REUNION:
         si la cuenta lo tiene activado, se activa
         Si la cuenta lo tiene desactivado, se queda desactivado, 
         no funciona auto_recording
        */
        if (isset($zoom->initialrecord) && $zoom->initialrecord==1){
            $data['auto_recording'] = HTZOOM_RECORDING_CLOUD;
        }
        if (isset($zoom->userecordvc) && $zoom->userecordvc==0) {
            $data['auto_recording'] = HTZOOM_RECORDING_DISABLED;
        }

        if (isset($zoom->webinar) && $zoom->webinar) {
            //opción "Permitir acceso en cualquier momento", depende de variable undatedsession
            $data['type'] = $zoom->undatedsession ? HTZOOM_RECURRING_WEBINAR : HTZOOM_SCHEDULED_WEBINAR;
        } else {
            $data['type'] = $zoom->undatedsession ? HTZOOM_RECURRING_MEETING : HTZOOM_SCHEDULED_MEETING;
        }
        if ($data['type'] == HTZOOM_SCHEDULED_MEETING || $data['type'] == HTZOOM_SCHEDULED_WEBINAR) {
            // Convert timestamp to ISO-8601. The API seems to insist that it end with 'Z' to indicate UTC.
            $data['start_time'] = gmdate('Y-m-d\TH:i:s\Z', $zoom->starttime);
            $data['duration'] = (int) ceil($zoom->duration / 60);
        }

        if (isset($zoom->option_jbh)) {
            $data['settings']['join_before_host'] = ! (bool) ($zoom->waitmoderator);
        }

        if (isset($zoom->disablewebcam)){
            $data['settings']['host_video'] = (bool)!$zoom->disablewebcam;
            $data['settings']['participant_video'] = (bool)!$zoom->disablewebcam;
        }
        /*if (isset($zoom->option_host_video)) {
            $data['settings']['host_video'] =(bool) ($zoom->optionhostvideo);
        }*/
        /*if (isset($zoom->option_participants_video)) {
            $data['settings']['participant_video'] = (bool) ($zoom->option_participants_video);
        }*/

        if (isset($zoom->disablemicro)){
            $data['settings']['mute_upon_entry']=(bool)$zoom->disablemicro;
        }



        //eliminar anfitriones alternativos. Ahora anfitriones alternativos son los profesores del curso, si se añaden
        /*if (isset($zoom->alternative_hosts)) {
            //$data['settings']['alternative_hosts'] = $zoom->alternative_hosts;
        }*/

        
        if (isset($zoom->password)) {
            $data['password'] = $zoom->password;
        }   

        return $data;
    }

    /**
     * Create a meeting/webinar on Zoom.
     * Take a $zoom object as returned from the Moodle form and respond with an object that can be saved to the database.
     *
     * @param stdClass $zoom The meeting to create.
     * @return stdClass The call response.
     */
    public function create_meeting($zoom) {
        if (!isset($zoom->webinar)){
            $zoom->webinar=false;
        }
        $zoom->undatedsession = 0;

        //$url = "users/$this->hostid/" . ($zoom->webinar ? 'webinars' : 'meetings');
        $url = "users/$this->emaillicense/" . ($zoom->webinar ? 'webinars' : 'meetings');

        $response=$this->_make_call($url, $this->_database_to_api($zoom), 'post');
        return $response;

    }

    /**
     * Update a meeting/webinar on Zoom.
     *
     * @param stdClass $zoom The meeting to update.
     * @return void
     */
    public function update_meeting($zoom) {
        if (!isset($zoom->webinar)){
            $zoom->webinar=false;
        }
        $zoom->undatedsession = 0;

        $url = ($zoom->webinar ? 'webinars/' : 'meetings/') . $zoom->meetingid;
        $this->_make_call($url, $this->_database_to_api($zoom), 'patch');
    }

    /**
     * Delete a meeting or webinar on Zoom.
     *
     * @param int $id The meeting_id or webinar_id of the meeting or webinar to delete.
     * @param bool $webinar Whether the meeting or webinar you want to delete is a webinar.
     * @return void
     */
    public function delete_meeting($id, $webinar) {
        $url = ($webinar ? 'webinars/' : 'meetings/') . $id;
        $this->_make_call($url, null, 'delete');
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
            $response = $this->_make_call($url);
        } catch (moodle_exception $error) {
            throw $error;
        }
        return $response;
    }
    
    /**INICIO ISYC:
     *Obtiene las grabaciones de una reunión.
     * 
     * @param int $id The meeting_id of the meeting or webinar to retrieve.
     * @return stdClass Información sobre las grabaciones de un meeting.
     */
    public function get_meeting_recordings($id){
        //$url = 'meetings/'.$id."/recordings";
        $url = 'meetings/'.$id.'/recordings?include_fields=download_access_token';
        
        $response = null;
        try {
            $response = $this->_make_call($url);
        } catch (moodle_exception $error) {
        }
        return $response;
    }
    
    public function get_meeting_recordingsettings($id){
        $url = 'meetings/' . $id . '/recordings/settings';
        $response = null;
        try{
            $response = $this->_make_call($url);
        } catch (moodle_exception $error) {
        }
        return $response;

    }

    public function get_past_meetings_uuid($id){
        $url = '/past_meetings/'.$id.'/instances';
        $response = null;
        try{
            $response = $this->_make_call($url);
        } catch (moodle_exception $error) {
        }
        return $response;
    }
    
    /*
    //pruebas inicio isyc
    public function get_users_recordings($userid){
        $url = 'users/'.$userid."/recordings";
        $response = null;
        try {
            $response = $this->_make_call($url);
        } catch (moodle_exception $error) {
            throw $error;
        }
        return $response;
    }
    //fin pruebas isyc
    */

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
        $data = array('from' => $from, 'to' => $to, 'page_size' => HTZOOM_MAX_RECORDS_PER_CALL);
        return $this->_make_paginated_call($url, $data, 'meetings');
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
        $instances = $this->_make_paginated_call($url, null, ($webinar ? 'webinars' : 'meetings'));
        return $instances;
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
        return $this->_make_paginated_call($url, null, 'registrants');
    }

    /**
     * Get details about a particular webinar UUID/session.
     *
     * @param string $uuid The uuid of the webinar to retrieve.
     * @return stdClass A JSON object with the webinar's details.
     * @link https://zoom.github.io/api/#retrieve-a-webinar
     */
    public function get_metrics_webinar_detail($uuid) {
        return $this->_make_call('webinars/' . $uuid);
    }

    /**
     * Get the participants who attended a meeting
     * @param string $meetinguuid The meeting or webinar's UUID.
     * @param bool $webinar Whether the meeting or webinar whose information you want is a webinar.
     * @return stdClass The meeting report.
     */
    public function get_meeting_participants($meetinguuid, $webinar) {
        return $this->_make_paginated_call('report/' . ($webinar ? 'webinars' : 'meetings') . '/'
                                           . $meetinguuid . '/participants', null, 'participants');
    }

    /**
     * Retrieves ended webinar details report.
     *
     * @param string|int $identifier The webinar ID or webinar UUID. If given webinar ID, Zoom will take the last webinar instance.
     */
    public function get_webinar_details_report($identifier) {
        return $this->_make_call('report/webinars/' . $identifier);
    }

    /**
     * Retrieve the UUIDs of hosts that were active in the last 30 days.
     *
     * @param int $from The time to start the query from, in Unix timestamp format.
     * @param int $to The time to end the query at, in Unix timestamp format.
     * @return array An array of UUIDs.
     */
    public function get_active_hosts_uuids($from, $to) {
        $users = $this->_make_paginated_call('report/users', array('type' => 'active', 'from' => $from, 'to' => $to), 'users');
        $uuids = array();
        foreach ($users as $user) {
            $uuids[] = $user->id;
        }
        return $uuids;
    }


    //crea  un meeting en función de la licencia que ha solicitado el usuario al crear la actividad zoom
    //se modifica zoom->host_id en función del correo de la licencia
    public function create_meeting_calendar($zoom) {

        $zoomuserid = false;
        $service = new mod_hybridteaching_webservice();
        try {
            //obtenemos (con la api) el usuario seleccionado de la licencia
            $zoomuser = $service->get_user($zoom->licencia);
            if ($zoomuser !== false) {
                $zoomuserid = $zoomuser->id;
                //asignamos el id del zoom user seleccionado en la licencia
                $zoom->host_id=$zoomuserid;
            }
            else{
                $zoom->host_id=false;
                return false;
            }
        } catch (moodle_exception $error) {
            throw $error;
        }


        if ($zoom->host_id!=false){ //si hemos obtenido usuario correcto:
            $url = "users/$zoom->host_id/" . (isset($zoom->webinar) && $zoom->webinar ? 'webinars' : 'meetings');

            return $this->_make_call($url, $this->_database_to_api($zoom), 'post');
        }

    }


     /**
     * Get group's list.
     *
     * @return array An array of users.
     * @link https://zoom.github.io/api/groups
     */
    public function get_groups() {
        $grupos = $this->_make_call('groups');
        return $grupos;
    }

    //obtener miembros del grupo guardado en la config
    ///groups/{groupId}/members
    public function get_group_members(){
        $idgroup=get_config('isyczoomav', 'idgroup');
        $groupmembers = $this->_make_paginated_call('groups/'.$idgroup."/members", null, 'members');
        return $groupmembers;
    }
    
    
     /**
     * Returns a server to server oauth access token, good for 1 hour.
     *
     * @throws moodle_exception
     * @return string access token
     */
    public function get_access_token() {
        $token = $this->oauth();
        return $token;
    }
    
    
    /**
     * Stores token and expiration in cache, returns token from OAuth call.
     *
     * @param cache $cache
     * @throws moodle_exception
     * @return string access token
     */
    private function oauth() {
        $curl = new curl();
        $curl->setHeader('Authorization: Basic ' . base64_encode($this->clientid . ':' . $this->clientsecret));
        $curl->setHeader('Content-Type: application/json');

        // Force HTTP/1.1 to avoid HTTP/2 "stream not closed" issue.
        $curl->setopt([
            'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
        ]);

        $timecalled = time();
        $response = $this->make_curl_call($curl, 'post',
            'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=' . $this->accountid, []);

        if ($curl->get_errno()) {
            throw new moodle_exception('errorwebservice', 'htzoom', '', $curl->error);
        }

        $response = json_decode($response);
        if (isset($response->access_token)) {
            $token = $response->access_token;
            return $token;
        } else {
            throw new moodle_exception('errorwebservice', 'htzoom', '', get_string('zoomerr_no_access_token', 'hybridteachvc_zoom'));
        }
    }

}
