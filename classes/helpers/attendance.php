<?php 

use mod_hybridteaching\helpers\roles;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'../../../../../config.php');
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
            'sessatt_string' => $sessattstring
        ];

        return $data;
    }
}
