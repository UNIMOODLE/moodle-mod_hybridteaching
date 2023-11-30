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

namespace hybridteachstore_pumukit;

class sessions {
    public function load_config($storagereference) {
        global $DB;

        $sql = "SELECT *
                  FROM {hybridteachstore_pumukit_con} pu
            INNER JOIN {hybridteaching_configs} htc ON htc.subpluginconfigid=pu.id
                 WHERE htc.id=:storagereference";

        $config = $DB->get_record_sql ($sql, ['storagereference' => $storagereference]);
        return $config;
    }

    public function get_recording($processedrecording, $storagereference, $htid, $sid) {
        global $DB;
        $config = $this->load_config($storagereference);

        // $object = $DB->get_record('hybridteachstore_pumukit', ['id' => $processedrecording]);
        $url = "";
        // Aquí lo necesario para poder visualizar el vídeo de pumukit, devolver una url de visualización.
        /*if ($object->weburl) {
            $url = $object->weburl;
        }*/
        return $url;
    }

    public function delete_session_extended($htsession, $configid) {
        global $DB;
        $DB->delete_records('hybridteachstore_pumukit', ['sessionid' => $htsession]);
    }
}