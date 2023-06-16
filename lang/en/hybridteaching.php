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

/**
 * Plugin strings are defined here.
 *
 * @package     mod_hybridteaching
 * @category    string
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Hybrid teaching';
$string['modulename'] = 'Hybrid teaching';
$string['modulenameplural'] = 'Hybrid teaching';
$string['hybridteachingname'] = 'Name';
$string['pluginadministration'] = 'Hybridteaching administration';

$string['sectionsessions'] = 'Sessions timing';
$string['sectionaudience'] = 'Access and role of participants';
$string['sectionsessionaccess'] = 'Session access';
$string['sectioninitialstates'] = 'VideoConference Initial States';
$string['sectionrecording'] = 'Recording options';
$string['sectionattendance'] = 'Atendance record';

$string['sessionscheduling'] = 'Use sessions scheduling';
$string['undatedsession'] = 'Reuse internal resource';
$string['starttime'] = 'Session start';
$string['duration'] = 'Duration';

$string['useattendance'] = 'Use student attendance record';
$string['useattendance_help'] = 'Activate student attendance registration, and consequently attendance-based grades';
$string['usevideoconference'] = 'Use video conferencing access';
$string['usevideoconference_help'] = 'Activate the use of videoconference';
$string['typevc'] = 'Videoconference type';
$string['userecordvc'] = 'Allow videoconference recordings';
$string['userecordvc_help'] = 'Allow recordings in the videoconference';

$string['waitmoderator'] = 'Wait for moderator';
$string['advanceentry'] = 'Advance entry';
$string['advanceentry_help'] = 'How long before the meeting starts the Join button is displayed.';
$string['closedoors'] = 'Close doors';
$string['closedoors_help'] = 'After this time, students cannot join.';
$string['userslimit'] = 'User limit';
$string['userslimit_help'] = 'Only applicable to observers, not moderators.';

$string['disablewebcam'] = 'Disable webcams';
$string['disablemicro'] = 'Disable microphones';
$string['disableprivatechat'] = 'Disable private chat';
$string['disablepublicchat'] = 'Disable public chat';
$string['disablesharednotes'] = 'Disable shared notes';
$string['hideuserlist'] = 'Hide user list';
$string['blockroomdesign'] = 'Block room design';
$string['ignorelocksettings'] = 'Ignore lock settings';

$string['initialrecord'] = 'Record everything from start';
$string['hiderecordbutton'] = 'Hide record button';
$string['showpreviewrecord'] = 'Show recording preview';
$string['downloadrecords'] = 'Students can download recordings';

$string['validateattendance'] = 'Stay to validate assistance';
$string['totalduration'] = '% total length';
$string['attendance'] = 'Attendance';
$string['attendance_help'] = 'Help';

$string['completionattendance'] = 'The user must attend sessions';
$string['completionattendancegroup'] = 'Assistance required';
$string['completiondetail:attendance'] = 'Session attendance: {$a}';

$string['subplugintype_hybridteachvc'] = 'Videoconference type';
$string['subplugintype_hybridteachvc_plural'] = 'Videoconferences types';
$string['hybridteachvc'] = 'Videoconference plugin';
$string['hybridteachvcpluginname'] = 'Videoconference plugin';
$string['headerconfig'] = 'Manage video conference extensions';
$string['videoconferenceplugins'] = 'Video conference plugins';

$string['view_error_url_missing_parameters'] = 'There are parameters missing in this URL';

$string['programschedule'] = 'Schedule program';
$string['sessions'] = 'Sessions';
$string['attendance'] = 'Attendance';
$string['import'] = 'Import';
$string['export'] = 'Export';

$string['hybridteaching:addinstance'] = 'Add new hybrid teaching';
$string['hybridteaching:view'] = 'View hybrid teaching';
$string['hybridteaching:viewjoinurl'] = 'View join url';
$string['hybridteaching:programschedule'] = 'View Program schedule';
$string['hybridteaching:sessions'] = 'View sessions';
$string['hybridteaching:attendance'] = 'View attendance';
$string['hybridteaching:import'] = 'Import';
$string['hybridteaching:export'] = 'Export';
$string['type'] = 'Type';
$string['order'] = 'Order';
$string['hideshow'] = 'Show/Hide';
$string['addinstance'] = 'Add instance';
$string['editinstance'] = 'Edit instance';
$string['saveinstance'] = 'Save instance';
$string['instancegeneralsettings'] = 'Hybrid teaching general settings';
$string['instancename'] = 'Instance name';
$string['instanceselect'] = 'Select an instance';
$string['generalconfig'] = 'General configuration';
$string['instancesconfig'] = 'Manage instances';

$string['errorcreateinstance'] = 'Error creating instance';
$string['errorupdateinstance'] = 'Error updateing instance';
$string['errordeleteinstance'] = 'Error deleting instance';
$string['createdinstance'] = 'Instance created successfully';
$string['updatedinstance'] = 'Instance updated successfully';
$string['deletedinstance'] = 'Instance deleted successfully';
$string['deleteconfirm'] = 'Are you sure you want to delete {$a} instance?';

$string['view_error_url_missing_parameters'] = 'There are parameters missing in this URL';

$string['recording'] = 'Recording';
$string['materials'] = 'Materials';
$string['actions'] = 'Actions';
$string['start'] = 'Start';

$string['sessionfor'] = 'Session for the group';
$string['sessiondate'] = 'Session date';
$string['addsession'] = 'Add session';
$string['commonsession'] = 'All groups';
$string['sessiontypehelp'] = 'You can add sessions for all students or for a group of students. Ability to add different types depends on activity group mode.

* In group mode "No groups" you can add only sessions for all students.
* In group mode "Separate groups" you can add only sessions for a group of students.
* In group mode "Visible groups" you can add both types of sessions.
';
$string['nogroups'] = 'This activity has been set to use groups, but no groups exist in the course.';
$string['addsession'] = 'Add session';
$string['addsession'] = 'Add session';
$string['presentationfile'] = 'Presentation file';
$string['replicatedoc'] = 'Replicate file to all sessions';
$string['caleneventpersession'] = 'Create one calendar event per session';
$string['addmultiplesessions'] = 'Multiple sessions';
$string['repeatasfollows'] = 'Repeat the session above as follows';
$string['createmultiplesessions'] = 'Create multiple sessions';
$string['createmultiplesessions_help'] = 'This function allows you to create multiple sessions in one simple step.
The sessions begin on the date of the base session and continue until the \'repeat until\' date.

  * <strong>Repeat on</strong>: Select the days of the week when your class will meet (for example, Monday/Wednesday/Friday).
  * <strong>Repeat every</strong>: This allows for a frequency setting. If your class will meet every week, select 1; if it will meet every other week, select 2; every 3rd week, select 3, etc.
  * <strong>Repeat until</strong>: Select the last day of class (the last day you want to take attendance).
';
$string['repeaton'] = 'Repeat on';
$string['repeatevery'] = 'Repeat every';
$string['repeatuntil'] = 'Repeat until';
$string['otheroptions'] = 'Other options';
$string['sessionname'] = 'Session name';

$string['nosessions'] = 'There are not sessions';

