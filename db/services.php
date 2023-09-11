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

/**
 * mod_hybridteahcing web services defintions for AJAX
 *
 * @package   mod_hybridteaching
 * @copyright   2023 isyc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array (

    'mod_hybridteaching_set_manual_attendance' => array(
        'classname' => 'hybridteaching_external',
        'methodname' => 'set_attendance_status',
        'classpath' => '/mod/hybridteaching/externallib.php',
        'description' => 'sets the attendance',
        'type' => 'set',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '', //capabilities check in form
    ),
);
