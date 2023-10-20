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

namespace hybridteachvc_bbb;

use mod_bigbluebuttonbn\local\proxy\proxy_base;
use mod_bigbluebuttonbn\local\proxy\curl;

defined('MOODLE_INTERNAL') || die();

class bbbproxy extends proxy_base {
    /**
     * Minimum poll interval for remote bigbluebutton server in seconds.
     */
    const MIN_POLL_INTERVAL = 2;

    /**
     * Default poll interval for remote bigbluebutton server in seconds.
     */
    const DEFAULT_POLL_INTERVAL = 5;

    protected $bbbinstance;

    /*
    **
     * Constructor for the proxy, with url for api
     *
    */
    public function __construct($bbbinstance) {
        $this->bbbinstance = $bbbinstance;  //api with credentials, url...
    }

    /**
     * Create a Meeting
     *
     * @param array $data
     * @param array $metadata
     * @param string|null $presentationname
     * @param string|null $presentationurl
     * @return array
     * @throws bigbluebutton_exception
     */
    public function create_meeting(
        array $data,
        array $metadata,
        ?string $presentationname = null,
        ?string $presentationurl = null
    ): array {
        $createmeetingurl = $this->action_url_config('create', $data, $metadata);

        $curl = new curl();
        if (!is_null($presentationname) && !is_null($presentationurl)) {
            $payload = "<?xml version='1.0' encoding='UTF-8'?><modules><module name='presentation'><document url='" .
                $presentationurl . "' /></module></modules>";

            $xml = $curl->post($createmeetingurl, $payload);
        } else {
            $xml = $curl->get($createmeetingurl);
        }
        self::assert_returned_xml($xml);

        if (empty($xml->meetingID)) {
            throw new bigbluebutton_exception('general_error_cannot_create_meeting');
        }

        if ($xml->hasBeenForciblyEnded === 'true') {
            throw new bigbluebutton_exception('index_error_forciblyended');
        }

        return [
            'returncode' => (string) $xml->returncode,
            'meetingID' => (string) $xml->meetingID,
            'internalMeetingID' => (string) $xml->internalMeetingID,
            'attendeePW' => (string) $xml->attendeePW,
            'moderatorPW' => (string) $xml->moderatorPW,
            'createTime' => (string) $xml->createTime
        ];
    }

    public function end_meeting($meetingid,$modpw){
        $xml = $this->fetch_endpoint_xml_config('end',['meetingID' => $meetingid, 'password' => $modpw]);
        self::assert_returned_xml($xml, ['meetingid' => $meetingid]);
    }

    /**
     * Returns the right URL for the action specified.
     *
     * @param string $action
     * @param array $data
     * @param array $metadata
     * @return string
     */
    protected function action_url_config(string $action = '', array $data = [], array $metadata = []): string {
        $baseurl = $this->sanitized_url_config() . $action . '?';
        $metadata = array_combine(array_map(function($k) {
            return 'meta_' . $k;
        }, array_keys($metadata)), $metadata);

        $params = http_build_query($data + $metadata, '', '&');
        //echo $baseurl.$params.'&checksum=' . sha1($action . $params . $this->sanitized_secret_config());
        return $baseurl . $params . '&checksum=' . sha1($action . $params . $this->sanitized_secret_config());
    }

    /**
     * Makes sure the url used doesn't is in the format required.
     *
     * @return string
     */
    protected function sanitized_url_config() {
        //$serverurl = trim(config::get('server_url'));
        $serverurl = trim($this->bbbinstance->serverurl);
        if (PHPUNIT_TEST) {
            $serverurl = (new moodle_url(TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER))->out(false);
        }
        if (substr($serverurl, -1) == '/') {
            $serverurl = rtrim($serverurl, '/');
        }
        if (substr($serverurl, -4) == '/api') {
            $serverurl = rtrim($serverurl, '/api');
        }
        return $serverurl . '/api/';
    }

    /**
     * Makes sure the shared_secret used doesn't have trailing white characters.
     *
     * @return string
     */
    protected function sanitized_secret_config(): string {
        return trim($this->bbbinstance->sharedsecret);
    }

    /**
     * Builds and returns a url for joining a bigbluebutton meeting.
     *
     * @param string $meetingid
     * @param string $username
     * @param string $pw
     * @param string $logouturl
     * @param string $role
     * @param string|null $configtoken
     * @param int $userid
     * @param string|null $createtime
     *
     * @return string
     */
    public function get_join_url(
        string $meetingid,
        string $username,
        string $logouturl,
        string $role,
        string $configtoken = null,
        int $userid = 0,
        string $createtime = null
    ): string {
        $data = [
            'meetingID' => $meetingid,
            'fullName' => $username,
            //'password' => $pw,   //deprecado, no es necesario ya que se pasa el rol
            'logoutURL' => $logouturl,
            'role' => $role
        ];

        if (!is_null($configtoken)) {
            $data['configToken'] = $configtoken;
        }

        if (!empty($userid)) {
            $data['userID'] = $userid;
            $data['guest'] = "false";
        } else {
            $data['guest'] = "true";
        }

        if (!is_null($createtime)) {
            $data['createTime'] = $createtime;
        }
        $currentlang = current_language();
        if (!empty(trim($currentlang))) {
            $data['userdata-bbb_override_default_locale'] = $currentlang;
        }
        return $this->action_url_config('join', $data);
    }

    /**
     * Get meeting recording by meetingid
     *
     * @param meetingid $meetingid
     */
    public function get_meeting_recording($meetingid){
        $data = [
            'meetingID' => $meetingid, 
//'496909493508781bb144e696513a1b492ef0d940', 
            'state' => 'published,unpublished,processed, processing, deleted',  //comprobar si las processed hay que descargarlas tb o no
        ];
        $recordingurl = $this->action_url_config('getRecordings', $data);
        $recordingid = '';
        $curl = new curl();
        $xml = $curl->get($recordingurl);
        self::assert_returned_xml($xml);           
        if ($xml != null){
            if (isset($xml->recordings->recording->recordID)){
                $recordingid=(string) $xml->recordings->recording->recordID;
            }
        }
        return [
            'returncode' => (string) $xml->returncode,
            'recordingid' => $recordingid,
        ];
    }

     /**
     * Get meeting recording by recordingid
     *
     * @param recordingid $recordingid
     */
    public function get_url_recording_by_recordid($recordingid){
        $data = [
            'recordingID' => $recordingid,
            'state' => 'published,unpublished,processed',
        ];
        $recordingurl= $this->action_url_config('getRecordings', $data);
        
        $curl = new curl();
        $xml = $curl->get($recordingurl);
        self::assert_returned_xml($xml);

        if ($xml != null){
            if (isset($xml->recordings->recording->playback->format->url)){
                $recordingurl=(string) $xml->recordings->recording->playback->format->url;
            }
        }
        return [
            'returncode' => (string) $xml->returncode,
            'recordingid' => $recordingurl,
        ];
    }
    


    /**
     * Ensure that the remote server was contactable.
     *
     * @param instance $instance
     */
    public function require_working_server(): void {
        $version = null;
        try {
            $version = $this->get_server_version_config();
        } catch (server_not_available_exception $e) {
            self::handle_server_not_available();
        }

        if (empty($version)) {
            self::handle_server_not_available();
        }
    }

    /**
     * Perform api request on BBB.
     *
     * @return null|string
     */
    public function get_server_version_config(): ?string {
        $xml = $this->fetch_endpoint_xml_config('');
        if (!$xml || $xml->returncode != 'SUCCESS') {
            return null;
        }
    
        if (!isset($xml->version)) {
            return null;
        }
    
        $serverversion = (string) $xml->version;
        return (double) $serverversion;
    }

    /**
     * Fetch the XML from an endpoint and test for success.
     *
     * If the result could not be loaded, or the returncode was not 'SUCCESS', a null value is returned.
     *
     * @param string $action
     * @param array $data
     * @param array $metadata
     * @return null|bool|SimpleXMLElement
     */
    protected function fetch_endpoint_xml_config(
        string $action,
        array $data = [],
        array $metadata = []
    ) {
        if (PHPUNIT_TEST && !defined('TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER')) {
            return true; // In case we still use fetch and mock server is not defined, this prevents
            // an error. This can happen if a function from lib.php is called in test from other modules
            // for example.
        }
        $curl = new curl();
        return $curl->get($this->action_url_config($action, $data, $metadata));
    }

    /**
     * Handle the server not being available.
     */
    public static function handle_server_not_available(): void {
        \core\notification::add(
            self::get_server_not_available_message(),
            \core\notification::ERROR
        );
        //redirect(self::get_server_not_available_url($instance));
    }

    /**
     * Get message when server not available
     *
     * @return string
     */
    public static function get_server_not_available_message(): string {
        global $USER;

//AÑADIR AQUI EL MENSAJE DEPENDIENDO DEL ROL DENTRO DE LA INSTANCIA
// UN MENSAJE DISTINTO SI ES ADMIN, SI ES MODERADOR O SI ES ESTUDIANTE

        if (is_siteadmin($USER->id)) {
            return get_string('view_error_unable_join', 'mod_bigbluebuttonbn');
        /*} else if ($USER->is_moderator()) {
            return get_string('view_error_unable_join_teacher', 'mod_bigbluebuttonbn');
        */            
        } else {
            return get_string('view_error_unable_join_student', 'mod_bigbluebuttonbn');
        }
    }

    /**
     * Get URL to the page displaying that the server is not available
     *
     * @param instance $instance
     * @return string
     */
    public static function get_server_not_available_url($instance): string {

//AÑADIR AQUI EL MENSAJE DEPENDIENDO DEL ROL DENTRO DE LA INSTANCIA
// UN MENSAJE DISTINTO SI ES ADMIN, SI ES MODERADOR O SI ES ESTUDIANTE

        global $USER;
        if (is_siteadmin($USER->id)) {
            return new moodle_url('/admin/settings.php', ['section' => 'modsettingbigbluebuttonbn']);
        /*} else if ($instance->is_moderator()) {
            return new moodle_url('/course/view.php', ['id' => $instance->get_course_id()]);
        */
        } else {
            return new moodle_url('/course/view.php', ['id' => $instance->get_course_id()]);
        }
    }

}





