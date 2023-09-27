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
        $data = $DB->get_record('hybridteachstore_onedrive_co', ['id' => $configid]);
        return $data;
    }
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
        $id=$DB->insert_record('hybridteachstore_onedrive_co', $records);
        return $id;
    }

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

    public static function delete_config($configid) {
        global $DB;
        $configid = ['id' => $configid];
        $DB->delete_records('hybridteachstore_onedrive_co', $configid);
    }
}
