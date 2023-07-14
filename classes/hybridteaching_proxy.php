<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_hybridteaching;

use stdClass;

/**
 * The hybridteaching proxy class.
 *
 * This class acts as a proxy between Moodle and the hybridteaching API server,
 * and handles all requests relating to the server and meetings.
 *
 * @package   mod_hybridteaching
 */
class hybridteaching_proxy{
    /**
     * Helper for getting the owner userid of a hybridteaching instance.
     *
     * @param stdClass $hybridteaching hybridteaching instance
     * @return int ownerid (a valid user id or null if not registered/found)
     */
    public static function get_instance_ownerid(stdClass $hybridteaching): int {
        global $DB;
        $filters = [
            'id' => $hybridteaching->id,
        ];

        return (int) $DB->get_field('hybridteaching', 'usercreator', $filters);
    }
}