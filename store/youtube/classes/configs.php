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

use stdClass;

/**
 * Class configs.
 */
class configs extends \mod_hybridteaching\controller\configs_controller {
    /**
     * Loads a configuration by its ID.
     *
     * @param int $configid Configuration ID.
     * @return object
     */
    public static function load_config($configid) {
        global $DB;
        $data = $DB->get_record('hybridteachstore_youtube_con', ['id' => $configid]);
        return $data;
    }

    /**
     * Create a new configuration record.
     *
     * @param object $data The data to create the configuration record
     * @return int The ID of the newly created configuration record
     */
    public static function create_config($data) {
        global $DB, $USER;
        $records = new stdClass();
        $records->clientid = $data->clientid;
        $records->clientsecret = $data->clientsecret;
        $records->emaillicense = $data->emaillicense;
        $records->timecreated = time();
        $records->createdby = $USER->id;
        $id = $DB->insert_record('hybridteachstore_youtube_con', $records);
        return $id;
    }

    /**
     * Update the configuration with the given data.
     *
     * @param object $data The data to create the configuration record
     * @return int
     */
    public static function update_config($data) {
        global $DB, $USER;
        $records = new stdClass();
        $records->id = $data->subpluginconfigid;
        $records->clientid = $data->clientid;
        $records->clientsecret = $data->clientsecret;
        $records->emaillicense = $data->emaillicense;
        $records->timemodified = time();
        $records->modifiedby = $USER->id;
        $DB->update_record('hybridteachstore_youtube_con', $records);
        return $records->id;
    }

    /**
     * Deletes a configuration from the hybridteachstore_youtube_con table.
     *
     * @param int $configid The ID of the configuration to be deleted.
     */
    public static function delete_config($configid) {
        global $DB;
        $configid = ['id' => $configid];
        $DB->delete_records('hybridteachstore_youtube_con', $configid);
    }
}
