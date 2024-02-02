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
 * @package    hybridteachvc_zoom
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_zoom;

use stdClass;

/**
 * Class configs.
 */
class configs extends \mod_hybridteaching\controller\configs_controller {
    /**
     * Load a specific configuration by ID.
     *
     * @param int $configid The ID of the configuration to load.
     * @return object The configuration data.
     */
    public static function load_config($configid) {
        global $DB;
        $zoomdata = $DB->get_record('hybridteachvc_zoom_config', ['id' => $configid]);
        return $zoomdata;
    }
    
    /**
     * Create a new configuration record in the database.
     *
     * @param object $data 
     * @return int
     */
    public static function create_config($data) {
        global $DB, $USER;
        $zoomrecords = new stdClass();
        $zoomrecords->accountid = $data->accountid;
        $zoomrecords->clientid = $data->clientid;
        $zoomrecords->clientsecret = $data->clientsecret;
        $zoomrecords->emaillicense = $data->emaillicense;
        $zoomrecords->timecreated = time();
        $zoomrecords->createdby = $USER->id;
        $id = $DB->insert_record('hybridteachvc_zoom_config', $zoomrecords);
        return $id;
    }

    /**
     * Updates the configuration data in the database with the provided $data.
     *
     * @param object $data The data to update the configuration with.
     */
    public static function update_config($data) {
        global $DB, $USER;
        $zoomrecords = new stdClass();
        $zoomrecords->id = $data->subpluginconfigid;
        $zoomrecords->accountid = $data->accountid;
        $zoomrecords->clientid = $data->clientid;
        $zoomrecords->clientsecret = $data->clientsecret;
        $zoomrecords->emaillicense = $data->emaillicense;
        $zoomrecords->timemodified = time();
        $zoomrecords->modifiedby = $USER->id;
        $DB->update_record('hybridteachvc_zoom_config', $zoomrecords);
    }

    /**
     * Deletes a configuration by its ID.
     *
     * @param int $configid Configuration ID to delete.
     */
    public static function delete_config($configid) {
        global $DB;
        $configid = ['id' => $configid];
        $DB->delete_records('hybridteachvc_zoom_config', $configid);
    }
}
