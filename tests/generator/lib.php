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
 * mod_hybridteaching data generator.
 *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * mod_hybridteaching data generator class.
 *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_hybridteaching_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        $defaultquizsettings = array(
            
            'introformat' => 1,
            'useattendance' => 1,
            'usevideoconference' => 1,
            'userecordvc' => 1,
            'typevc' => 1,
            'config' => 0,
            'sessionscheduling' => 0,
            'reusesession' => 0,
            'starttime' => time(),
            'duration' => 0,
            'timezone' => null,
            'grade' => 100,
            'maxgradeattendance' => 0,
            'maxgradeattendanceunit' => 1,
            'participants' => "[{'selectiontype':'all','selectionid':'all','role':'viewer'},{'selectiontype':'user','selectionid':'2','role':'moderator'}]",
            'waitmoderator' => 1,
            'advanceentrycount' => 0,
            'advanceentryunit' => 1,
            'closedoorscount' => 0,
            'cloosedoorsunit' => 1,
            'userslimit' => 300,
            'graceperiod' => 0,
            'graceperiodunit' => 1,
            'wellcomemessage' => "",
            'disablecam' => 0,
            'disablemic' => 0,
            'disableprivatechat' => 0,
            'disablepublicchat' => 0,
            'disablenote' => 0,
            'hideuserlist' => 0,
            'blockroomdesign' => 0,
            'ignorelocksettings' => 0,
            'initialrecord' => 0, 
            'hiderecordbutton' => 0,
            'showpreviewrecord' => 0,
            'downloadrecords' => 0,
            'validateattendance' => 0,
            'attendanceunit' => 1,
            'completionattendance' => 0,
            'useqr' => 0,
            'rotateqr' => 0,
            'rotateqrsecret' => null,
            'studentpassword' => "text",
            'usercreator' => null,
            'timecreated' => time(), 
            'timemodified' => null,

            // To config help
            'type' => null
        );

        foreach ($defaultquizsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }

}
