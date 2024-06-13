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
 * @package    hybridteachstore_pumukit
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachstore_pumukit;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir .'/filelib.php');

/**
 * Class pumukit_handler.
 */
class pumukit_handler {
    /** @var stdClass $config A config from the pumukit object. */
    private $config;
    /** @var stdClass $curl A curl from make connections. */
    private $curl;

    /**
     * Constructor for initializing the class with the provided configuration.
     *
     * @param object $config The configuration for the class
     */
    public function __construct($config) {
        $this->config = $config;  // Api with credentials, url...
        $user = $this->config->userpumukit;
        $pass = $this->config->secret;

        $this->curl = new \curl();
        $this->curl->setopt(
            [
                'CURLOPT_TIMEOUT' => 120,
                'CURLOPT_CONNECTTIMEOUT' => 120,
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_USERPWD' => "$user:$pass",
            ]
        );
    }

    /**
     * Uploads a file to Pumukit using the API.
     *
     * @param string $videofile Video path
     * @param string $coursename Video path
     * @param string $filename Video path
     * @return mixed
     */
    public function uploadfile($videofile, $coursename, $filename) {
        $result = null;

        $url = $this->config->url . "/api/ingest/addMediaPackage";

        $params["flavor"] = "presenter/source";
        $params["BODY"] = new \CURLFile($videofile);
        $params["seriesTitle"] = $coursename;
        $params["title"] = $filename;

        $response = $this->curl->post($url, $params);
        $info = $this->curl->get_info();

        if ($curlerrno = $this->curl->get_errno()) {
            // CURL connection error.
            mtrace("Unexpected response from server, CURL error number: $curlerrno");
            return $result;
        } else if ($info['http_code'] != 200) {
            // Unexpected error from server.
            mtrace('Unexpected response HTTP code:' . $info['http_code']);
            return $result;
        }

        $xml = simplexml_load_string($response);
        if ($xml) {
            $objjsondocument = json_encode($xml);
            $arroutput = json_decode($objjsondocument, true);
            $result = $arroutput["@attributes"]["id"];
        }
        mtrace($result);

        return $result;
    }

    /**
     * Download a recording from the Pumukit using the API.
     *
     * @param int $processedrecording The ID of the processed recording.
     * @throws \Throwable
     * @return array|string The download URL of the recording.
     */
    public function downloadrecording($processedrecording) {
        global $DB;

        $result = null;

        $url = $this->config->url . "/api/ingest/getDownloadTrack";

        $record = $DB->get_record('hybridteachstore_pumukit', ['id' => $processedrecording]);
        if (!isset($record->code)) {
            return $result;
        }

        $params["mediaPackage"] = '<mediapackage id="'.$record->code.'"></mediapackage>';

        $response = $this->curl->post($url, $params);

        $info = $this->curl->get_info();

        if ($curlerrno = $this->curl->get_errno()) {
            // CURL connection error.
            mtrace("Unexpected response from server, CURL error number: $curlerrno");
            return $result;
        } else if ($info['http_code'] != 200) {
            // Unexpected error from server.
            mtrace('Unexpected response HTTP code:' . $info['http_code']);
            return $result;
        }

        return $response;
    }

    /**
     * Get the url recording from the Pumukit using the API.
     *
     * @param int $processedrecording The ID of the processed recording.
     * @return array|string The download URL of the recording.
     */
    public function get_urlrecording($processedrecording) {
        global $DB;

        $result = null;

        $url = $this->config->url . "/api/ingest/getLinkTrack";

        $record = $DB->get_record('hybridteachstore_pumukit', ['id' => $processedrecording]);
        if (!isset($record->code)) {
            return $result;
        }

        $params["mediaPackage"] = '<mediapackage id="'.$record->code.'"></mediapackage>';

        $response = $this->curl->post($url, $params);

        $info = $this->curl->get_info();

        if ($curlerrno = $this->curl->get_errno()) {
            // CURL connection error.
            mtrace("Unexpected response from server, CURL error number: $curlerrno");
            return $result;
        } else if ($info['http_code'] != 200) {
            // Unexpected error from server.
            mtrace('Unexpected response HTTP code:' . $info['http_code']);
            return $result;
        }

        return $response;
    }

    /**
     * Delete recording file from the Pumukit using the API.
     *
     * @param int $videoweburl The weburl.
     */
    public function deletefile($processedrecording) {
        global $DB;

        $result = null;

        $url = $this->config->url . "/api/ingest/deleteTrack";

        $record = $DB->get_record('hybridteachstore_pumukit', ['id' => $processedrecording]);
        if (!isset($record->code)) {
            return $result;
        }

        $params["mediaPackage"] = '<mediapackage id="'.$record->code.'"></mediapackage>';

        $response = $this->curl->post($url, $params);

        $info = $this->curl->get_info();

        if ($curlerrno = $this->curl->get_errno()) {
            // CURL connection error.
            mtrace("Unexpected response from server, CURL error number: $curlerrno");
            return $result;
        } else if ($info['http_code'] != 200) {
            // Unexpected error from server.
            mtrace('Unexpected response HTTP code:' . $info['http_code']);
            return $result;
        }

        return $response;
    }
}
