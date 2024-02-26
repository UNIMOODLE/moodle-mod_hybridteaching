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

/**
 * Class backup_hybridteaching_activity_structure_step
 *
 * This class is for backuping the structure of a hybrid teaching activity.
 */
class backup_hybridteaching_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the complete hybridteaching structure for backup, with file and id annotations.
     *
     * @return object
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $hybridteaching = new backup_nested_element('hybridteaching', ['id'], [
            'course', 'name', 'intro', 'introformat', 'useattendance',
            'usevideoconference', 'userecordvc', 'typevc', 'config', 'sessionscheduling',
            'reusesession', 'starttime', 'duration', 'timezone', 'grade', 'maxgradeattendance',
            'maxgradeattendancemode', 'participants', 'waitmoderator', 'advanceentrycount',
            'advanceentryunit', 'closedoorscount', 'closedoorsunit', 'userslimit',
            'graceperiod', 'graceperiodunit', 'disablecam', 'disablemic', 'disableprivatechat',
            'disablepublicchat', 'disablenote', 'hideuserslist', 'blockroomdesign',
            'ignorelocksettings', 'initialrecord', 'hiderecordbutton', 'showpreviewrecord',
            'downloadrecord', 'validateattendance', 'attendanceunit', 'completionattendance',
            'useqr', 'rotateqr', 'studentpassword', 'usercreator', 'timecreated', 'timemodified', ]);

        $sessions = new backup_nested_element('sessions');
        $session = new backup_nested_element('session', ['id'], [
            'hybridteachingid', 'name', 'description', 'descriptionformat', 'groupid', 'starttime', 'duration',
            'sessionfile', 'typevc', 'userecordvc', 'vcreference', 'processedrecording', 'storagereference',
            'isfinished', 'attexempt', 'visible', 'timecreated', 'timemodified', 'createdby', 'modifiedby', ]);

        $attendances = new backup_nested_element('attendances');
        $attendance = new backup_nested_element('attendance', ['id'], [
            'hybridteachingid', 'sessionid', 'userid', 'connectiontime', 'exempt', 'status',
            'type', 'grade', 'visible', 'usermodified' , 'timecreated', 'timemodified', ]);

        $logs = new backup_nested_element('logs');

        $log = new backup_nested_element('log', ['id'], [
            'attendanceid', 'action', 'usermodified', 'timecreated', ]);

        $passwords = new backup_nested_element('passwords');

        $password = new backup_nested_element('password', ['id'], [
            'attendanceid', 'password', 'expirytime', ]);

        // Build the tree.
        $hybridteaching->add_child($sessions);
        $sessions->add_child($session);
        $session->add_child($attendances);
        $attendances->add_child($attendance);
        $attendance->add_child($logs);
        $logs->add_child($log);

        // Define sources.
        $hybridteaching->set_source_table('hybridteaching', ['id' => backup::VAR_ACTIVITYID]);

        $session->set_source_table('hybridteaching_session', ['hybridteachingid' => backup::VAR_PARENTID], 'id ASC');
        $attendance->set_source_table('hybridteaching_attendance', ['sessionid' => backup::VAR_PARENTID]);
        $log->set_source_table('hybridteaching_attend_log', ['attendanceid' => backup::VAR_PARENTID], 'id ASC');
        // This source definition only happen if we are including user info witch in our case we dont.

        // Skip group overrides if not including groups.
        $groupinfo = $this->get_setting_value('groups');
        if (!$groupinfo) {
            $overrideparams['groupid'] = backup_helper::is_sqlparam(null);
        }

        // Define id annotations.
        $attendance->annotate_ids('user', 'userid');

        // Define file annotations.
        $hybridteaching->annotate_files('mod_hybridteaching', 'intro', null);

        // Return the root element (hybridteaching), wrapped into standard activity structure.
        return $this->prepare_activity_structure($hybridteaching);
    }
}
