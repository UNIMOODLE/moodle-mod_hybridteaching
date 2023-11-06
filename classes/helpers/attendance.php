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
 * The attendance helper class
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use mod_hybridteaching\helpers\roles;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'../../../../../config.php');
require_login();
require_once(dirname(__FILE__).'/../controller/attendance_controller.php');

class attendance {
    /**
     * Calculate session attendance.
     *
     * @param $ht The HT object.
     * @param $sessid The session ID.
     * @param $groupid The group ID.
     *
     * @return array The attendance data including the attendance count, total users, percentage, and a string representation.
     */
    public static function calculate_session_att($ht, $sessid, $groupid) {
        $attendancecontroller = new attendance_controller($ht);
        $attendance = $attendancecontroller->count_sess_attendance(['sessionid' => $sessid, 'status' => 1]);
        $totalusers = roles::count_role_users(5, $ht->coursecontext, false, $groupid);

        if ($totalusers == 0) {
            return 0;
        }

        $result = ($attendance / $totalusers) * 100;

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
