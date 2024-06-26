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

use stdClass;

/**
 * Class configs.
 */
class configs extends \mod_hybridteaching\controller\configs_controller {
    /**
     * Load a configuration by its ID.
     *
     * @param int $configid The ID of the configuration to load
     * @return object The loaded configuration data, or false if not found
     */
    public static function load_config($configid) {
        global $DB;
        $data = $DB->get_record('hybridteachstore_onedrive_co', ['id' => $configid]);
        return $data;
    }

    /**
     * Create a config record in the database.
     *
     * @param object $data Data to store
     * @return int
     */
    public static function create_config($data) {
        global $DB, $USER;
        $records = new stdClass();
        $records->tenantid = $data->tenantid;
        $records->clientid = $data->clientid;
        $records->clientsecret = $data->clientsecret;
        $records->subdomain = $data->subdomain;
        $records->useremail = $data->useremail;
        $records->timecreated = time();
        $records->createdby = $USER->id;
        $id = $DB->insert_record('hybridteachstore_onedrive_co', $records);
        return $id;
    }

    /**
     * Updates the config with the provided data.
     *
     * @param object $data The data to update the config with.
     * @return int The ID of the updated record.
     */
    public static function update_config($data) {
        global $DB, $USER;
        $records = new stdClass();
        $records->id = $data->subpluginconfigid;
        $records->tenantid = $data->tenantid;
        $records->clientid = $data->clientid;
        $records->clientsecret = $data->clientsecret;
        $records->subdomain = $data->subdomain;
        $records->useremail = $data->useremail;
        $records->timemodified = time();
        $records->modifiedby = $USER->id;
        $DB->update_record('hybridteachstore_onedrive_co', $records);
        return $records->id;
    }

    /**
     * Delete a configuration from the database by its ID.
     *
     * @param int $configid The ID of the configuration to be deleted
     */
    public static function delete_config($configid) {
        global $DB;
        $configid = ['id' => $configid];
        $DB->delete_records('hybridteachstore_onedrive_co', $configid);
    }
}
