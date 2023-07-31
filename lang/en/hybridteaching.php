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
$string['usevideoconference'] = 'Use videoconferencing access';
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
$string['headerconfigvc'] = 'Manage videoconference extensions';
$string['videoconferenceplugins'] = 'Videoconference plugins';

$string['view_error_url_missing_parameters'] = 'There are parameters missing in this URL';

$string['programschedule'] = 'Schedule program';
$string['sessions'] = 'Sessions';
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
$string['addsetting'] = 'Add setting';
$string['editinstance'] = 'Edit instance';
$string['saveinstance'] = 'Save instance';
$string['instancegeneralsettings'] = 'Hybrid teaching general settings';
$string['instancename'] = 'Instance name';
$string['instanceselect'] = 'Select an instance';
$string['generalconfig'] = 'General configuration';
$string['instancesconfig'] = 'Manage instances';
$string['instancesvcconfig'] = 'Manage videoconference settings';
$string['instancesstoreconfig'] = 'Manage storage settings';

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
$string['nogroup'] = 'Next session is not for your group';
$string['nosubplugin'] = 'Incorrect type of videoconference. El tipo de videoconferencia es incorrecto. Contact your administrator';
$string['noinstance'] = 'The selected videoconference configuration does not exist. Contact your administrator';

$string['status_progress'] = 'In progress';
$string['status_finished'] ='Has finished';
$string['status_start'] = 'will start soon';
$string['status_undated'] = 'This videoconference room is ready. You can join the meeting now.';

$string['closedoors_hours'] = ' {$a} hours from start';
$string['closedoors_minutes'] = ' {$a} minutes from start';
$string['closedoors_seconds'] = ' {$a} seconds from start';

$string['sessionstart'] = 'The next session will start on';
$string['estimatedduration'] = 'Estimated duration:';
$string['advanceentry'] = 'Advance entry:';
$string['closedoors'] = 'Close doors:';
$string['status'] = 'Status';
$string['inprogress'] = 'In progress';
$string['started'] = 'Started on';
$string['closedoorsnext'] = 'Close doors after';
$string['closedoorsnext2'] = 'from start';
$string['closedoorsprev'] = 'This session closed doors to';
$string['finished'] = 'This session ended on';

$string['mod_form_field_participant_list_action_add'] = 'Add';
$string['mod_form_field_participant_list'] = 'Assignee';
$string['mod_form_field_participant_list_type_all'] = 'All users enrolled';
$string['mod_form_field_participant_list_type_role'] = 'Role';
$string['mod_form_field_participant_list_type_user'] = 'User';
$string['mod_form_field_participant_list_type_owner'] = 'Owner';
$string['mod_form_field_participant_list_text_as'] = 'joins session as';
$string['mod_form_field_participant_list_action_add'] = 'Add';
$string['mod_form_field_participant_list_action_remove'] = 'Remove';
$string['mod_form_field_participant_role_moderator'] = 'Moderator';
$string['mod_form_field_participant_role_viewer'] = 'Viewer';


$string['equalto'] = 'Equal to';
$string['morethan'] = 'More than';
$string['lessthan'] = 'Less than';
$string['options'] = 'Options';
$string['sesperpage'] = 'Sessions per page';

$string['equalto'] = 'Equal to';
$string['morethan'] = 'More than';
$string['lessthan'] = 'Less than';
$string['options'] = 'Options';
$string['sesperpage'] = 'Sessions per page';
$string['hybridteaching:bulksessions'] = 'See multiple session actions select';
$string['updatesessions'] = 'Update sessions';
$string['deletesessions'] = 'Delete sessions';
$string['withselectedsessions'] = 'With selected sessions';
$string['go'] = 'Go';
$string['options'] = 'Options';
$string['sessionsuc'] = 'Sessions';
$string['programscheduleuc'] = 'Program schedule';
$string['nosessionsselected'] = 'No sessions selected';
$string['deletecheckfull'] = 'Are you absolutely sure you want to completely delete the {$a}, including all user data?';
$string['sessiondeleted'] = 'Session successfully deleted';
$string['strftimedmyhm'] = '%d %b %Y %I.%M%p';
$string['extend'] = 'Extend';
$string['reduce'] = 'Reduce';
$string['seton'] = 'Set on';
$string['updatesesduration'] = 'Modify session duration';
$string['updatesesstarttime'] = 'Modify session start time';
$string['updateduration'] = 'Modify duration';
$string['advance'] = 'Advance';
$string['delayin'] = 'Delay in';
$string['updatestarttime'] = 'Modify start time';
$string['hybridteaching:sessionsactions'] = 'See session list actions';
$string['hybridteaching:sessionsfulltable'] = 'Display all fields of session list';

$string['headerconfigstore'] = 'Manage storage extensions';
$string['storageplugins'] = 'Storage plugins';
$string['editsession'] = 'Edit session';
$string['importsessions'] = 'Import sessions';
$string['invalidimportfile'] = 'File format is invalid.';
$string['processingfile'] = 'Processing file...';
$string['sessionsgenerated'] = '{$a} sessions were successfully generated';

$string['error:importsessionname'] = 'Invalid session name! Skipping line {$a}.';
$string['error:importsessionstarttime'] = 'Invalid session start time! Skipping line {$a}.';
$string['error:importsessionduration'] = 'Invalid session duration! Skipping line {$a}.';
$string['formaterror:importsessionstarttime'] = 'Invalid format for session start time! Skipping line {$a}.';
$string['formaterror:importsessionduration'] = 'Invalid format for session duration! Skipping line {$a}.';
$string['error:sessionunknowngroup'] = 'Unknown group name: {$a}.';
$string['examplecsv'] = 'Example text file';
$string['examplecsv_help'] = 'Sessions may be imported via CSV, Excel or ODP. The format of the file should be as follows:

  * Each line of the file contains one record
  * Each record is a series of data separated by the selected separator
  * The first record contains a list of fieldnames defining the format of the rest of the file
  * Required fieldname is name, starttime and duration
  * Optional fieldnames are groups and description';