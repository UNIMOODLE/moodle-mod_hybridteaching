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

$string['pluginname'] = 'Hybrid teaching';
$string['modulename'] = 'Hybrid teaching';
$string['modulenameu'] = 'Hybrid_teaching';
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
$string['wellcomemessage'] = 'Wellcome message';
$string['wellcomemessage_help'] = 'Welcome message to display when entering the videoconference';

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
$string['attendance_help'] = 'Time the user is required to spent in the session to validate their attendance';

$string['completionattendance'] = 'The user must attend sessions';
$string['completionattendancegroup'] = 'Assistance required';
$string['completiondetail:attendance'] = 'Session attendance: {$a}';

$string['subplugintype_hybridteachvc'] = 'Videoconference type';
$string['subplugintype_hybridteachvc_plural'] = 'Videoconferences types';
$string['hybridteachvc'] = 'Videoconference plugin';
$string['hybridteachvcpluginname'] = 'Videoconference plugin';
$string['headerconfigvc'] = 'Manage videoconference extensions';
$string['videoconferenceplugins'] = 'Videoconference plugins';

$string['subplugintype_hybridteachstore'] = 'Storage type';
$string['subplugintype_hybridteachstore_plural'] = 'Storage types';

$string['view_error_url_missing_parameters'] = 'There are parameters missing in this URL';

$string['programschedule'] = 'Schedule program';
$string['sessions'] = 'Sessions';
$string['import'] = 'Import';
$string['export'] = 'Export';

$string['hybridteaching:addinstance'] = 'Add new hybrid teaching';
$string['hybridteaching:manageactivity'] = 'Manage settings hybridteaching';
$string['hybridteaching:view'] = 'View hybrid teaching';
$string['hybridteaching:viewjoinurl'] = 'View join url';
$string['hybridteaching:programschedule'] = 'View Program schedule';
$string['hybridteaching:sessions'] = 'View sessions';
$string['hybridteaching:attendance'] = 'View attendance';
$string['hybridteaching:import'] = 'Import';
$string['hybridteaching:export'] = 'Export';
$string['hybridteaching:bulksessions'] = 'See multiple session actions select';
$string['hybridteaching:sessionsactions'] = 'See session list actions';
$string['hybridteaching:sessionsfulltable'] = 'Display all fields of session list';
$string['hybridteaching:attendancesactions'] = 'Access the attendance actions';
$string['hybridteaching:attendanceregister'] = 'Register attendance in the session';
$string['hybridteaching:record'] = 'Allow recordings';
$string['hybridteaching:viewrecordings'] = 'View recordings';
$string['hybridteaching:viewchat'] = 'View chats';
$string['hybridteaching:downloadrecordings'] = 'Download recordings';
$string['hybridteaching:viewhiddenitems'] = 'View hidden items';
$string['hybridteaching:viewallsessions'] = 'Allow view all sessions without group filter';

$string['type'] = 'Type';
$string['order'] = 'Order';
$string['hideshow'] = 'Show/Hide';
$string['addsetting'] = 'Add setting';
$string['editconfig'] = 'Edit config';
$string['saveconfig'] = 'Save config';
$string['configgeneralsettings'] = 'Hybrid teaching general settings';
$string['configname'] = 'Config name';
$string['configselect'] = 'Select an config';
$string['generalconfig'] = 'General configuration';
$string['configsconfig'] = 'Manage configs';
$string['configsvcconfig'] = 'Manage videoconference settings';
$string['configsstoreconfig'] = 'Manage storage settings';

$string['errorcreateconfig'] = 'Error creating config';
$string['errorupdateconfig'] = 'Error updateing config';
$string['errordeleteconfig'] = 'Error deleting config';
$string['createdconfig'] = 'config created successfully';
$string['updatedconfig'] = 'config updated successfully';
$string['deletedconfig'] = 'config deleted successfully';
$string['deleteconfirm'] = 'Are you sure you want to delete {$a} config?';

$string['view_error_url_missing_parameters'] = 'There are parameters missing in this URL';

$string['recording'] = 'Recording';
$string['materials'] = 'Materials';
$string['actions'] = 'Actions';
$string['start'] = 'Start';

$string['sessionfor'] = 'Session for the group';
$string['sessiondate'] = 'Session date';
$string['addsession'] = 'Add session';
$string['allgroups'] = 'All groups';
$string['sessiontypehelp'] = 'You can add sessions for all students or for a group of students. Ability to add different types depends on activity group mode.

* In group mode "No groups" you can add only sessions for all students.
* In group mode "Separate groups" you can add only sessions for a group of students.
* In group mode "Visible groups" you can add both types of sessions.
';
$string['nogroups'] = 'This activity has been set to use groups, but no groups exist in the course.';
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
$string['noconfig'] = 'The selected videoconference configuration does not exist. Contact your administrator';
$string['noconfig_viewer'] = 'There is no videoconference configuration. Contact your teacher.';

$string['status_progress'] = 'The session is in progress';
$string['status_finished'] = 'The session has finished';
$string['status_start'] = 'The session will start soon';
$string['status_ready'] = 'This session is ready. You can join now.';
$string['status_undated'] = 'You can create a new recurring session';
$string['status_undated_wait'] = 'You must wait until a new session starts';

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
$string['closedoorsprev'] = 'This session closed doors ';
$string['closedoorsafter'] = 'from start';
$string['finished'] = 'Last session ended on';

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

$string['updatesessions'] = 'Update sessions';
$string['deletesessions'] = 'Delete sessions';
$string['withselectedsessions'] = 'With selected sessions';
$string['go'] = 'Go';
$string['options'] = 'Options';
$string['sessionsuc'] = 'Sessions';
$string['programscheduleuc'] = 'Program schedule';
$string['nosessionsselected'] = 'No sessions selected';
$string['deletecheckfull'] = 'Are you absolutely sure you want to completely delete the following sessions, including all user data?';
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
$string['editsession'] = 'Edit session';

$string['headerconfigstore'] = 'Manage storage extensions';
$string['storageplugins'] = 'Storage plugins';
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

$string['nostarttime'] = 'Without start time';
$string['noduration'] = 'Without duration';
$string['notypevc'] = 'Without videoconference type';
$string['labeljoinvc'] = 'Access videoconference';
$string['joinvc'] = 'Join videoconference';
$string['createsession'] = 'Create session';
$string['showqr'] = 'Show QR';
$string['canjoin'] = 'You can join the meeting when the teacher has started it';
$string['canattendance'] = 'You will be able to register your attendance when the teacher has started the session';
$string['recurringses'] = 'Recurring session';
$string['finishsession'] = 'Finish session';
$string['sessionnoaccess'] = 'You have no access to this session';
$string['lessamin'] = 'Less than 1 min';

$string['qrcode'] = 'QR code';
$string['useqr'] = 'Include QR use';
$string['rotateqr'] = 'Rotate QR code';
$string['studentpassword'] = 'Student password';
$string['passwordheader'] = 'Use the password listed below to take your attendance';
$string['qrcodeheader'] = 'Scan the QR code below to take your attendance';
$string['qrcodeandpasswordheader'] = 'Scan the QR code below or use the password listed below to take your attendance';
$string['noqrpassworduse'] = 'The use of QR or password to take attendance is disabled';
$string['labelshowqrpassword'] = 'Show password/QR for classroom assistance';
$string['showqrpassword'] = 'Show Password / QR';
$string['qrcodevalidbefore'] = 'QR code valid for:';
$string['qrcodevalidafter'] = 'seconds.';
$string['labelattendwithpassword'] = 'Register attendance in Classroom';
$string['attendwithpassword'] = 'Access password: ';
$string['markattendance'] = 'Sign attendance';
$string['incorrect_password'] = 'Incorrect password entered.';
$string['attendance_registered'] = 'Attendance registered succesfully';
$string['qr_expired'] = 'The QR expired, make sure to read the correct qr';
$string['grade'] = 'Grade';
$string['commonattendance'] = 'All groups';
$string['videoconference'] = 'Vconf';
$string['classroom'] = 'Classroom';

$string['resultsperpage'] = 'Results per page';
$string['sessresultsperpage_desc'] = 'Number of sessions to show per page';
$string['donotusepaging'] = 'Do not use paging';
$string['reusesession'] = 'Reuse external session resources';
$string['reusesession_desc'] = 'If is checked, the session resources will be reused for recurring sessions';

$string['allsessions'] = 'Global - all sessions';
$string['entrytime'] = 'Entry';
$string['leavetime'] = 'Leave';
$string['permanence'] = 'Permanence';

$string['passwordgrp'] = 'Student password';
$string['passwordgrp_help'] = 'If set students will be required to enter this password so they can set their attendance in the session.

  * If empty, no password is required.
  * If use of rotating QR code, the password will also be rotating';

$string['maxgradeattendance'] = 'Attendance for maximum score';
$string['maxgradeattendance_help'] = 'Calculation mode

  * Number of sessions given by attended
  * % number of attendances out of the total sessions accessible
  * % time attended over the nominal total of accessible sessions

';
$string['numsess'] = 'Nº sessions';
$string['percennumatt'] = '% nº attendance';
$string['percentotaltime'] = '% total time';
$string['percentage'] = 'Percentaje';

$string['eventsessionadded'] = 'Session added';
$string['eventsessionviewed'] = 'Session viewed';
$string['eventsessionupdated'] = 'Session updated';
$string['eventsessionrecordviewed'] = 'Session record viewed';
$string['eventsessionrecorddownloaded'] = 'Session record downloaded';
$string['eventsessionmngviewed'] = 'Session manage viewed';
$string['eventsessionjoined'] = 'Session joined';
$string['eventsessioninfoviewed'] = 'Session info viewed';
$string['eventsessionfinished'] = 'Session finished';
$string['eventsessiondeleted'] = 'Session deleted';
$string['eventattviewed'] = 'Attendance viewed';
$string['eventattupdated'] = 'Attendance updated';
$string['eventattmngviewed'] = 'Attendance manage viewed';

$string['gradenoun'] = 'Grade';
$string['gradenoun_help'] = 'Session grade / Total activity grade / Max activity grade';
$string['finishattend'] = 'Finish attendance';
$string['bad_neededtime'] = 'The time for completing the attendance is lower than the session time';
$string['attnotfound'] = 'Attendance id error contact an administrator';
$string['entryregistered'] = 'Attendance entry registered succesfully';
$string['exitregistered'] = 'Attendance exit registered succesfully';
$string['alreadyregistered'] = 'Attendance already registered, if you are having trouble entering the session, try to exit the session and enter again';
$string['exitingleavedsession'] = 'Attendance exit already registered';
$string['entryneededtoexit'] = 'Trying to exit without entering the session, you must register your attendance first';
$string['marks'] = 'Marks';
$string['hour'] = 'Hour';
$string['firstentry'] = 'Marks the first session entry';
$string['sessionentry'] = 'Enters the session';
$string['sessionexit'] = 'Exits the session';
$string['lastexit'] = 'Marks the last session exit';
$string['sessionstarttime'] = 'Session start';
$string['sessionendtime'] = 'Session end';
$string['participant'] = 'Participant';
$string['userfor'] = 'Attendance for user:';
$string['combinedatt'] = 'Total attendance registered';
$string['withselectedattends'] = 'With the selected attends';
$string['prevattend'] = 'Attendance';
$string['setattendance'] = 'Modify attendance';
$string['setexempt'] = 'Modify exempt';
$string['setsessionexempt'] = 'Modify use of session in grade inclusion';
$string['activeattendance'] = 'Set attendance';
$string['inactiveattendance'] = 'Remove attendance';
$string['updateattendance'] = 'Update attendance';
$string['attnotforgrade'] = '(Session not used for grades)';
$string['exempt'] = 'Exempt';
$string['exemptattendance'] = 'Exempt attendance for grades';
$string['notexemptattendance'] = 'Use attendance for grades';
$string['exemptsessionattendance'] = 'Exempt session use for attendance';
$string['notexemptsessionattendance'] = 'Use session for attendance ';
$string['exemptuser'] = 'User exempted in this session';
$string['sessionsattendance'] = 'Sessions attendance';
$string['studentsattendance'] = 'Students attendance';

$string['graceperiod'] = 'Grace period';
$string['graceperiod_help'] = 'Time the user has before the attendance is set as late arrival';
$string['session'] = 'Session';
$string['participationtime'] = 'Participation time';
$string['noattendanceregister'] = 'Cant register attendance in the session';
$string['attexempt'] = 'Grade exempt';
$string['noatt'] = 'No attendance registered';
$string['attendanceresume'] = 'Attendance resume';
$string['attendedsessions'] = 'Attended sessions';
$string['validatedattendance'] = 'Valid attendances';
$string['finalgrade'] = 'Final grade';
$string['late'] = 'Late arrival';
$string['earlyleave'] = 'Leaved early';
$string['withatt'] = 'With attendance';
$string['withoutatt'] = 'Without attendance';
$string['notexempt'] = 'Not exempt';
$string['nofilter'] = 'No filter';
$string['vc'] = 'Videoconference';

$string['watchrecording'] = 'Watch recording';
$string['norecording'] = 'No recording';

$string['entersession'] = 'You can enter the session to register your attendance';
$string['exitsession'] = 'Your attendance was registered sucesfully, remember to end your attendance when exiting the session';
$string['novc'] = 'No use of vc in this session';
$string['viewstudentinfo'] = 'Student attendance view';
$string['viewsessioninfo'] = 'Session attendance view';
$string['nologsfound'] = 'No logs found for the user in this session';
$string['takensessions'] = 'Taken sessions';
$string['selectedsessions'] = 'Selected sessions';
$string['anygroup'] = 'Any group';
$string['withoutgroup'] = 'Without group';
$string['unknown'] = 'Undefined';
$string['noattendanceusers'] = 'It is not possible to export any data as there are no students enrolled in the course.';
$string['downloadexcel'] = 'Download in Excel format';
$string['downloadooo'] = 'Download in OpenOffice format';
$string['downloadtext'] = 'Download in text format';
$string['startofperiod'] = 'Start of period';
$string['endofperiod'] = 'End of period';
$string['includeall'] = 'Select all sessions';
$string['joinurl'] = 'Join url: ';
$string['passstring'] = 'Password: ';
$string['vcconfigremoved'] = 'The videoconference config for this module was removed by an administrator';
$string['hiderecords'] = 'Hide recordings';
$string['visiblerecords'] = 'Show recordings';

$string['error:deleteinprogress'] = 'You can not delete an in progress session';
$string['deletewithhybridmods'] = 'This config is used in the following hybridteaching modules: {$a}. Are you sure you want to delete it?';
$string['lostconfig'] = 'This session config has been deleted by an administrator';
$string['noinitialstateconfig'] = 'This meeting has no initial status to be configured';
$string['cantfinishunstarted'] = 'Can not finish a session before the real start time';

$string['error_unable_join'] = 'Cannot connect. The meeting could not be found or has been deleted. Contact your teacher or administrator.';
$string['sessionscheduling_help'] = 'If group mode is forced on course, the sessions are forced to use scheduling.

If it isn\'t forced, not using sesions schedulling, forces groups to be disabled for the module.';

$string['error:importsessiontimetype'] = 'Invalid session duration type! Skipping line {$a}.';

$string['invalidduration'] = 'Invalid duration';
$string['chaturlmeeting'] = 'Chat meeting';
$string['notesurlmeeting'] = 'Notes meeting';

$string['sessionendbeforestart'] = 'This session would end before the start date, change its duration or start date';
$string['repeatsessionsamedate'] = 'The session repeat date end can not be set as today';
$string['programsessionbeforenow'] = 'The repeat session date can not be set before today';
$string['daynotselected'] = 'A day must be selected for repeating sessions';

$string['norecordingmoderation'] = 'You do not have access to alow recordings on this vc type';
$string['chats'] = 'Chats';

$string['enabled'] = 'Enabled';
$string['enabled_help'] = 'If the option is activated, this extension will be enabled';
$string['disabled'] = 'Disabled';
$string['savechanges'] = 'Save changes';
$string['cancel'] = 'Cancel';
$string['categoryselect'] = 'Custom selection';

$string['defaultsettings'] = 'Default hybridteaching general settings';
$string['defaultsettings_help'] = 'These settings define the general default configuration on hybridteaching modules';
$string['sessionscheduling_desc'] = 'If active, forces the sessions to be creatied using the schedule program ';
$string['waitmoderator_desc'] = 'Users must wait to enter a videoconference session until a moderator starts it';
$string['useattendance_help'] = 'Adds the use of the student attendance in the hybridteaching sessions';
$string['usevideoconference_help'] = 'Adds the posibility for users to join the sessions using videoconference systems';
$string['userecord_help'] = 'Adds the posibility for sessions to be recorded and saved';
$string['sessionssettings'] = 'Default hybridteaching sessions settings';
$string['sessionssettings_help'] = 'These settings define the sessions default configuration on hybridteaching modules';
$string['userslimit_desc'] = 'This setting defines the total amount of users not moderators that can join the session';
$string['attendancesettings'] = 'Default hybridteaching attendance settings';
$string['attendancesettings_help'] = 'These settings define the attendance default configuration on hybridteaching modules';
$string['useqr_desc'] = 'Adds the posibility to users so they can register their attendance using a qr';
$string['rotateqr_desc'] = 'Forces the use of a rotating qr for users registering their attendance';
$string['studentpassword_desc'] = 'Password users must use when registering their attendance';
$string['hidechats'] = 'Hide chats';
$string['visiblechats'] = 'Show chats';

$string['advanceentrycount'] = 'Advance entry time';
$string['advanceentrycount_help'] = 'Advance entry time for the session';
$string['advanceentryunit'] = 'Advance entry unit';
$string['advanceentryunit_help'] = 'Advance entry time unit';
$string['closedoorscount'] = 'Close doors time';
$string['closedoorscount_help'] = 'Time value the users have before the session can\'t be accesed';
$string['closedoorsunit'] = 'Close doors unit';
$string['closedoorsunit_help'] = 'Closedoors time unit';
$string['validateattendance_help'] = 'Time the users need to be in the session for it to get validated';
$string['attendanceunit'] = 'Validate attendance unit';
$string['attendanceunit_help'] = 'Validate attendance time unit';
$string['graceperiod_help'] = 'Time since session start, where new user attendance, will not be valid for session grades';
$string['graceperiodunit'] = 'Grace period unit';
$string['graceperiodunit_help'] = 'Grace period time unit';
$string['updatecalen'] = 'Update calendar event';

$string['sessiontobecreated'] = 'Session to be created';
$string['recordingdisabled'] = 'Recordings are not activated. Download not allowed.';
$string['cannotmanageactivity'] = 'You don\'t have permissions to update {$a}';

$string['nouseconfig'] = 'This config does not apply for {$a} videoconferences.';
$string['hybridteaching:createsessions'] = 'Allow create sessions';

$string['bulkhide'] = 'Show/Hide sessions';
$string['bulkhidechats'] = 'Show/Hide chats';
$string['bulkhiderecordings'] = 'Show/Hide recordings';

$string['bulkhidetext'] = 'Are you sure you want to show/hide the following sessions?';
$string['bulkhidechatstext'] = 'Are you sure you want to show/hide the following chats?';
$string['bulkhiderecordingstext'] = 'Are you sure you want to show/hide the following recordings?';

$string['bulkhidesuccess'] = 'Sessions successfully hidden';
$string['bulkhidechatssuccess'] = 'Chats successfully hidden';
$string['bulkhiderecordingssuccess'] = 'Recordings successfully hidden';

$string['hiddenuserattendance'] = '(Hidden session for the user)';
$string['cantcreatevc'] = 'You cannot join the videoconference: you do not have sufficient permissions or you must wait for the moderator.';
$string['sessionperformed'] = '{$a} sessions already done (access in the Sessions tab).';

$string['qrupdatetime'] = 'Qr/Password rotating period';
$string['qrupdatetime_help'] = 'The period time in seconds, the qr will stay until a new one is generated, same as password if it applies';
$string['rotateqr_help'] = 'Forces the QR and password to rotate according to the defined time

  * Disables the password field if active';


$string['bulkhideatt'] = 'Show/Hide session attendance';
$string['bulkhideatttext'] = 'Are you sure you want to show/hide the following session attendance?';
$string['bulkhideattsuccess'] = 'Session attendance successfully hidden';
$string['hideatt'] = 'Hide session attendance';
$string['visibleatt'] = 'Show session attendance';
$string['updatefinished'] = 'Finish sessions that have finished by time';

// New strings.

$string['error:importnosessionschedule'] = 'Session scheduling is disabled. Change it to import.';
$string['info:sessioninprogress'] = 'Some sessions are currently in progress. Any actions are limited.';