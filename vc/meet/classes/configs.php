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
 * @package    hybridteachvc_meet
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace hybridteachvc_meet;

use stdClass;

/**
 * Class configs.
 */
class configs extends \mod_hybridteaching\controller\configs_controller {
    /**
     * Load config by config ID.
     *
     * @param int $configid The ID of the configuration to load.
     * @return object The loaded configuration data.
     */
    public static function load_config($configid) {
        global $DB;
        $zoomdata = $DB->get_record('hybridteachvc_meet_config', ['id' => $configid]);
        return $zoomdata;
    }

    /**
     * Creates a new configuration record in the database.
     *
     * @param object $data The data to be used for creating the configuration.
     * @return int The ID of the newly created configuration record.
     */
    public static function create_config($data) {
        global $DB;
        $meetrecords = new stdClass();
        $meetrecords->emailaccount = $data->emailaccount;
        $meetrecords->clientid = $data->clientid;
        $meetrecords->clientsecret = $data->clientsecret;
        $meetrecords->token = 0;
        $meetrecords->eventid = 0;
        $id = $DB->insert_record('hybridteachvc_meet_config', $meetrecords);
        return $id;
    }

    /**
     * Update the configuration with the given data.
     *
     * @param object $data The data to update the configuration
     */
    public static function update_config($data) {
        global $DB;
        $meetrecords = new stdClass();
        $meetrecords->id = $data->subpluginconfigid;
        $meetrecords->emailaccount = $data->emailaccount;
        $meetrecords->clientid = $data->clientid;
        $meetrecords->clientsecret = $data->clientsecret;
        $DB->update_record('hybridteachvc_meet_config', $meetrecords);
    }

    /**
     * Delete a configuration by its ID.
     *
     * @param int $configid The ID of the configuration to delete
     */
    public static function delete_config($configid) {
        global $DB;
        $DB->delete_records('hybridteachvc_meet_config', ['id' => $configid]);
    }
}
