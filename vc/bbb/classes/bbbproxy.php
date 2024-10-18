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
 * @package    hybridteachvc_bbb
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_bbb;

use mod_bigbluebuttonbn\local\proxy\proxy_base;
use mod_bigbluebuttonbn\local\proxy\curl;

/**
 * Class bbbproxy.
 */
class bbbproxy extends proxy_base {
    /**
     * Minimum poll interval for remote bigbluebutton server in seconds.
     */
    const MIN_POLL_INTERVAL = 2;

    /**
     * Default poll interval for remote bigbluebutton server in seconds.
     */
    const DEFAULT_POLL_INTERVAL = 5;

    /** @var object $bbbinstance Api with credentials, url... */
    protected $bbbinstance;

    /**
     * Constructor for the class.
     *
     * @param object $bbbinstance
     */
    public function __construct($bbbinstance) {
        $this->bbbinstance = $bbbinstance;
    }

    /**
     * Create a Meeting
     *
     * @param array $data
     * @param array $metadata
     * @param array|null $presentations
     * @return array
     * @throws bigbluebutton_exception
     */
    public function create_meeting(
        array $data,
        array $metadata,
        array $presentations = null
    ): array {
        $createmeetingurl = $this->action_url_config('create', $data, $metadata);

        $curl = new curl();
        if ($presentations != null) {
            $payload = "<?xml version='1.0' encoding='UTF-8'?><modules><module name='presentation'>";
            foreach ($presentations as $presentation) {
                if (isset($presentation['name']) && isset($presentation['content'])
                    && !is_null($presentation['name']) && !is_null($presentation['content'])) {
                        $payload .= "<document name='".$presentation['name']."'>";
                        $payload .= base64_encode($presentation['content']);
                        $payload .= "</document>";
                }
            }
            $payload .= "</module></modules>";
            $xml = $curl->post($createmeetingurl, $payload);
        } else {
            $xml = $curl->get($createmeetingurl);
        }

        if ($xml->returncode[0] == 'FAILED') {
            return ['message' => $xml->returncode];
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
            'createTime' => (string) $xml->createTime,
        ];
    }

    /**
     * End a meeting.
     *
     * @param string $meetingid
     */
    public function end_meeting($meetingid) {
        try {
            $xml = $this->fetch_endpoint_xml_config('end', ['meetingID' => $meetingid]);
            self::assert_returned_xml($xml, ['meetingid' => $meetingid]);
        } catch (\Exception $e) {
            // No action for endmeeting.
            return null;
        }
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
        return $baseurl . $params . '&checksum=' . sha1($action . $params . $this->sanitized_secret_config());
    }

    /**
     * Makes sure the url used doesn't is in the format required.
     *
     * @return string
     */
    protected function sanitized_url_config() {
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
        string $role,
        string $configtoken = null,
        int $userid = 0,
        string $createtime = null
    ): string {
        $data = [
            'meetingID' => $meetingid,
            'fullName' => $username,
            'role' => $role,
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
    public function get_meeting_recording($meetingid) {
        $data = [
            'meetingID' => $meetingid,
            'state' => 'published, unpublished, processed, processing, deleted',
        ];

        if ($this->is_meeting_running ($meetingid)) {
            return [
                'returncode' => 'meetingrunning',
            ];
        }

        $recordingurl = $this->action_url_config('getRecordings', $data);
        $recordingid = '';
        $curl = new curl();
        $xml = $curl->get($recordingurl);
        self::assert_returned_xml($xml);
        if ($xml != null) {
            if (isset($xml->recordings->recording->recordID)) {
                $recordingid = (string) $xml->recordings->recording->recordID;
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
    public function get_url_recording_by_recordid($recordingid) {
        $data = [
            'recordID' => $recordingid,
            'state' => 'published,unpublished,processed',
        ];
        $recording = $this->action_url_config('getRecordings', $data);
        $curl = new curl();
        $xml = $curl->get($recording);
        self::assert_returned_xml($xml);

        $recordingurl = '';
        $notes = '';
        if ($xml != null) {
            if (isset($xml->recordings->recording->playback->format)) {
                if (isset($xml->recordings->recording->playback->format[0])) {
                    foreach ($xml->recordings->recording->playback->format as $object) {
                        if (isset($object->type) && ($object->type == 'presentation' || $object->type == 'video') ) {
                            $recordingurl = $object->url;
                        }
                        if (isset($object->type) && $object->type == 'notes') {
                            $notes = $object->url;
                        }

                    }
                } else {
                    if (isset($xml->recordings->recording->playback->format->url)) {
                        $recordingurl = (string) $xml->recordings->recording->playback->format->url;
                    }
                }
            }
        }

        return [
            'returncode' => (string) $xml->returncode,
            'recording' => $recordingurl,
            'materials' => $notes,
        ];
    }

    /**
     * Ensure that the remote server was contactable.
     *
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
    }

    /**
     * Get message when server not available
     *
     * @return string
     */
    public static function get_server_not_available_message(): string {
        global $USER;
        if (is_siteadmin($USER->id)) {
            return get_string('view_error_unable_join', 'mod_bigbluebuttonbn');
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
        global $USER;
        if (is_siteadmin($USER->id)) {
            return new moodle_url('/admin/settings.php', ['section' => 'modsettingbigbluebuttonbn']);
        } else {
            return new moodle_url('/course/view.php', ['id' => $instance->get_course_id()]);
        }
    }

    /**
     * Determines if a meeting is currently running.
     *
     * @param int $meetingid The ID of the meeting.
     * @return bool Returns true if the meeting is running, false otherwise.
     */
    public function is_meeting_running($meetingid) {
        $action = 'isMeetingRunning';
        $params = http_build_query(['meetingID' => $meetingid], '', '&');
        $url = $this->sanitized_url_config() . $action .  '?meetingID=' . $meetingid .
            '&checksum=' . sha1($action . $params . $this->sanitized_secret_config());

        // Request HTTP.
        $response = file_get_contents($url);

        // Parse XML.
        $xml = simplexml_load_string($response);

        if ($xml->returncode == 'SUCCESS' && $xml->running == 'true') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get a meeting info and if the meeting exists.
     *
     * @param int $meetingid The ID of the meeting.
     * @return mixed Returns info meeting if meeting exists, array with otherwise.
     */
    public function get_meeting_info($meetingid) {
        $data = [
            'meetingID' => $meetingid,
        ];
        $url = $this->action_url_config('getMeetingInfo', $data);
        $curl = new curl();
        $xml = $curl->get($url);
        if ($xml->returncode == 'FAILED') {
            return [
                'returncode' => 'FAILED',
            ];
        } else {
            return $xml;
        }
    }
}
