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

/**
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class configs extends configs_controller {
    public static function load_config($configid) {
        global $DB;
        $zoomdata = $DB->get_record('hybridteachvc_meet_config', ['id' => $configid]);
        return $zoomdata;
    }

    public static function create_config($data) {
        global $DB;
        $meetrecords = new stdClass();
        $meetrecords->emailaccount = $data->emailaccount;
        $meetrecords->clientid = $data->clientid;
        $meetrecords->clientsecret = $data->clientsecret;
        $id = $DB->insert_record('hybridteachvc_meet_config', $meetrecords);
        return $id;
    }

    public static function update_config($data) {
        global $DB;
        $meetrecords = new stdClass();
        $meetrecords->id = $data->subpluginconfigid;
        $meetrecords->emailaccount = $data->emailaccount;
        $meetrecords->clientid = $data->clientid;
        $meetrecords->clientsecret = $data->clientsecret;
        $DB->update_record('hybridteachvc_meet_config', $meetrecords);
    }

    public static function delete_config($configid) {
        global $DB;
        $configid = ['id' => $configid];
        $DB->delete_records('hybridteachvc_meet_config', $configid);
    }
}
