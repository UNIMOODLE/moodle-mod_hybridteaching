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

use core\oauth2\rest;

// Meeting types.
define('HTZOOM_INSTANT_MEETING', 1);
define('HTZOOM_SCHEDULED_MEETING', 2);
define('HTZOOM_RECURRING_MEETING', 3);
define('HTZOOM_SCHEDULED_WEBINAR', 5);
define('HTZOOM_RECURRING_WEBINAR', 6);
define('HTZOOM_RECURRING_MEETING_FIXEDTIME', 8);
// Number of meetings per page from zoom's get user report.
define('HTZOOM_DEFAULT_RECORDS_PER_CALL', 30);
define('HTZOOM_MAX_RECORDS_PER_CALL', 300);

// Recording types.
define('HTZOOM_RECORDING_CLOUD', 'cloud');
define('HTZOOM_RECORDING_DISABLED', 'none');

/**
 * Check if the error indicates that a user is not found or does not belong to the current account.
 *
 * @param string $error
 * @return bool
 */
function htzoom_is_user_not_found_error($error) {
    return strpos($error, 'not exist') !== false || strpos($error, 'not belong to this account') !== false
        || strpos($error, 'not found on this account') !== false;
}

/**
 * Check if the error indicates that roles is not found.
 *
 * @param string $error
 * @return bool
 */
function htzoom_is_roles_not_found_error($error) {
    return strpos($error, 'invalid access') !== false;
}

/**
 * Check if the error indicates that users is not found.
 *
 * @param string $error
 * @return bool
 */
function htzoom_is_users_not_found_error($error) {
    return strpos($error, 'invalid access') !== false;
}
