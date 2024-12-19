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

/**
 * Class sessions.
 */
class sessions {
    /**
     * Load a configuration from the database based on the given storage reference.
     *
     * @param int $storagereference Storage reference
     * @return object|false
     */
    public function load_config($storagereference) {
        global $DB;

        $sql = "SELECT *
                  FROM {hybridteachstore_pumukit_con} pu
            INNER JOIN {hybridteaching_configs} htc ON htc.subpluginconfigid=pu.id
                 WHERE htc.id=:storagereference";

        $config = $DB->get_record_sql($sql, ['storagereference' => $storagereference]);
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
    public function get_recording($processedrecording, $storagereference, $htid, $sid) {
        $config = $this->load_config($storagereference);
        $pumukitclient = new pumukit_handler($config);
        return $pumukitclient->get_urlrecording($processedrecording);
    }

    /**
     * Deletes extended session for a specific config ID.
     *
     * @param int $htsession Session ID
     * @param object $config Config
     */
    public function delete_session_extended($htsession, $config) {
        global $DB;

        $pumukitconf = $this->load_config($config->id);
        $code = $DB->get_field('hybridteachstore_pumukit', 'code', ['sessionid' => $htsession]);

        if (isset($pumukitconf) && isset($code)) {
            // Only delete video from pumukit if there are not same video with another session, for example, restored from backup.
            $sql = "SELECT *
                FROM {hybridteachstore_pumukit} pk
                WHERE pumukit LIKE :code";
            $othersession = $DB->get_records_sql($sql, ['code' => $code]);
            if (count($othersession) <= 1) {
                // Delete video in pumukit.
                // No delete if there are other restores.
                $rows = $DB->get_records('hybridteachstore_pumukit', ['sessionid' => $htsession]);
                $pumukitclient = new pumukit_handler($pumukitconf);
                foreach ($rows as $row) {
                    $pumukitclient->deletefile($row->id);
                }
            }
        }

        $DB->delete_records('hybridteachstore_pumukit', ['sessionid' => $htsession]);
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
        $config = $this->load_config($storagereference);
        $pumukitclient = new pumukit_handler($config);
        $url = $pumukitclient->downloadrecording($processedrecording);
        return $url;
    }
}
