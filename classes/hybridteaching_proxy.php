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
class hybridteaching_proxy {
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
