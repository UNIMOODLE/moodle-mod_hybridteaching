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

 namespace hybridteachstore_youtube;

defined('MOODLE_INTERNAL') || die();

global $CFG;


class sessions  {

    public function get_recording($processedrecording, $storagereference, $htid, $sid){
        global $DB;
        $object = $DB->get_record('hybridteachstore_youtube', ['id' => $processedrecording]);
        $url="";
        if ($object) {
            $url="https://www.youtube.com/watch?v=".$object->code;
        }
        return $url;
    }

}
