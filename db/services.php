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

defined('MOODLE_INTERNAL') || die();

$functions = [

    'mod_hybridteaching_set_manual_attendance' => [
        'classname' => 'hybridteaching_external',
        'methodname' => 'set_attendance_status',
        'classpath' => '/mod/hybridteaching/externallib.php',
        'description' => 'sets the attendance',
        'type' => 'set',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/hybridteaching:attendancesactions', // Capabilities check in form.
    ],
    'mod_hybridteaching_set_session_exempt' => [
        'classname' => 'hybridteaching_external',
        'methodname' => 'set_session_exempt',
        'classpath' => '/mod/hybridteaching/externallib.php',
        'description' => 'sets a session exempt for attendance grade',
        'type' => 'set',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/hybridteaching:sessionsactions', // Capabilities check in form.
    ],
    'mod_hybridteaching_get_display_actions' => [
        'classname' => 'hybridteaching_external',
        'methodname' => 'get_display_actions',
        'classpath' => '/mod/hybridteaching/externallib.php',
        'description' => 'gets the actions to display for students in the view view',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '', // Capabilities check in form.
    ],
    'mod_hybridteaching_get_modal_text' => [
        'classname' => 'hybridteaching_external',
        'methodname' => 'get_modal_text',
        'classpath' => '/mod/hybridteaching/externallib.php',
        'description' => 'gets modal with the session info',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/hybridteaching:sessionsactions', // Capabilities check in form.
    ],
    'mod_hybridteaching_user_has_recording_capability' => [
        'classname' => 'hybridteaching_external',
        'methodname' => 'get_user_has_recording_capability',
        'classpath' => '/mod/hybridteaching/externallib.php',
        'description' => 'gets if the user can use recodings for the selected vc',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '', // Capabilities check in form.
    ],
    'mod_hybridteaching_disable_attendance_inprogress' => [
        'classname' => 'hybridteaching_external',
        'methodname' => 'disable_attendance_inprogress',
        'classpath' => '/mod/hybridteaching/externallib.php',
        'description' => 'disable the checks if a session is in progress',
        'type' => 'set',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '', // Capabilities check in form.
    ],
];
