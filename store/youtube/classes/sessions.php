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
 * @package    hybridteachstore_youtube
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachstore_youtube;

/**
 * Class sessions.
 */
class sessions {
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
        global $DB;
        $object = $DB->get_record('hybridteachstore_youtube', ['id' => $processedrecording]);
        $url = "";
        if ($object) {
            $url = "https://www.youtube.com/watch?v=".$object->code;
        }
        return $url;
    }

    /**
     * Delete a session extended.
     *
     * @param int $htsession Session ID
     * @param object $config Config object
     */
    public function delete_session_extended($htsession, $config) {
        global $DB;

        // Read the store instance config.
        $configyt = $DB->get_record('hybridteachstore_youtube_con', ['id' => $config->subpluginconfigid]);
        $videocode = $DB->get_field('hybridteachstore_youtube', 'code', ['sessionid' => $htsession]);
        if (isset($videocode) && isset($configyt)) {
            // Delete video in youtube.
            $youtubeclient = new youtube_handler($configyt);
            $youtubeclient->deletefile($videocode);

        }
        $DB->delete_records('hybridteachstore_youtube', ['sessionid' => $htsession]);
    }
}
