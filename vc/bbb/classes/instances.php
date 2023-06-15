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

class instances {
    public static function load_instance($instanceid) {
        global $DB;
        $bbbdata = $DB->get_record('hybridteachvc_bbb_instance', ['id' => $instanceid]);
        return $bbbdata;
    }
    public static function create_instance($data) {
        global $DB, $USER;
        $records = new stdClass();
        $records->serverurl = $data->serverurl;
        $records->sharedsecret = $data->sharedsecret;
        $records->pollinterval = $data->pollinterval;
        $records->timecreated = time();
        $records->createdby = $USER->id;
        $id=$DB->insert_record('hybridteachvc_bbb_instance', $records);
        return $id;
    }

    public static function update_instance($data) {
        global $DB, $USER;
        $records = new stdClass();
        $records->id = $data->subplugininstanceid;
        $records->serverurl = $data->serverurl;
        $records->sharedsecret = $data->sharedsecret;
        $records->pollinterval = $data->pollinterval;
        $records->timemodified = time();
        $records->modifiedby = $USER->id;
        $DB->update_record('hybridteachvc_bbb_instance', $records);
    }

    public static function delete_instance($instanceid) {
        global $DB;
        $instanceid = ['id' => $instanceid];
        $DB->delete_records('hybridteachvc_bbb_instance', $instanceid);
    }
}
