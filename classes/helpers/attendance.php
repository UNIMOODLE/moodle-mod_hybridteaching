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

namespace mod_hybridteaching\helpers;

use mod_hybridteaching\controller\attendance_controller;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'../../../../../config.php');
require_login();

/**
 * Class attendance.
 */
class attendance {
    /**
     * Calculate session attendance.
     *
     * @param object $ht The HT object.
     * @param int $sessid The session ID.
     * @param int $groupid The group ID.
     *
     * @return array The attendance data including the attendance count, total users, percentage, and a string representation.
     */
    public static function calculate_session_att($ht, $sessid, $groupid) {
        $attendancecontroller = new attendance_controller($ht);
        $attendance = $attendancecontroller->count_sess_attendance(['sessionid' => $sessid, 'status' => 0]);
        $totalusers = roles::count_role_users(5, $ht->coursecontext, false, $groupid);

        if ($totalusers == 0) {
            return 0;
        }

        $result = round(($attendance / $totalusers) * 100, 2);

        $sessattstring = "{$attendance} / {$totalusers} ({$result}%)";

        $data = [
            'attendance' => $attendance,
            'totalusers' => $totalusers,
            'percentage' => $result,
            'sessatt_string' => $sessattstring,
        ];

        return $data;
    }
}
