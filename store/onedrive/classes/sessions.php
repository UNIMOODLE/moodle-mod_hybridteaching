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

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Class sessions.
 */
class sessions {
    /**
     * Load configuration based on the storage reference.
     *
     * @param int $storagereference Storage reference
     * @return $config
     */
    public function load_config($storagereference) {
        global $DB;

        $sql = "SELECT *
        FROM {hybridteachstore_onedrive_co} od
        INNER JOIN {hybridteaching_configs} htc ON htc.subpluginconfigid=od.id
            WHERE htc.id=:storagereference AND htc.visible=1";

        $config = $DB->get_record_sql ($sql, ['storagereference' => $storagereference]);
        return $config;
    }

    /**
     * Get the recording URL for a processed recording.
     *
     * @param int $processedrecording Processed recording check
     * @param int $storagereference Storage reference
     * @param int $htid Hybridteaching ID
     * @param int $sid Session ID
     * @return string
     */
    public function get_recording($processedrecording, $storagereference,  $htid, $sid) {
        $config = $this->load_config($storagereference);
        if (!$config) {
            return '';
        }
        $recording = new \hybridteachstore_onedrive\onedrive_handler($config);
        $url = $recording->get_urlrecording ($processedrecording);
        return $url;
    }

    /**
     * Download a recording from a storage reference.
     *
     * @param int $processedrecording Processed recording check
     * @param int $storagereference Storage reference
     * @param int $htid Hybridteaching ID
     * @param int $sid Session ID
     * @return string
     */
    public function download_recording($processedrecording, $storagereference, $htid, $sid) {
        global $DB;
        $config = $this->load_config($storagereference);
        if (!$config) {
            return '';
        }

        $recording = new \hybridteachstore_onedrive\onedrive_handler($config);
        $url = $recording->downloadrecording ($processedrecording);
        return $url;
    }

    /**
     * Delete a recording from a storage reference.
     *
     * @param int $htsession Session ID
     * @param int $configid Hybridteaching config ID
     * @return void
     */
    public function delete_session_extended($htsession, $config) {
        global $DB;

        // Read the store instance config.
        $configod = $DB->get_record('hybridteachstore_onedrive_co', ['id' => $config->subpluginconfigid]);
        $videoweburl = $DB->get_field('hybridteachstore_onedrive', 'weburl', ['sessionid' => $htsession]);
        if (isset($videoweburl) && $videoweburl != '' && isset($configod)) {
            // Only delete video from onedrive if there are not same video with another session, for example, restored from backup.
            $sql = "SELECT *
                FROM {hybridteachstore_onedrive} od
                WHERE od.weburl LIKE :videoweburl";
    
            $othersession = $DB->get_records_sql ($sql, ['videoweburl' => $videoweburl]);
            if (count($othersession) > 1) {
                // No delete if there are other restores.            
            } else {
                // Delete video in onedrive.
                $onedriveclient = new onedrive_handler($configod);             
                $onedriveclient->deletefile($videoweburl);
            }
        }

        $DB->delete_records('hybridteachstore_onedrive', ['sessionid' => $htsession]);
    }
}
