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
 * @package    hybridteachvc_teams
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Hybrid Teams';
$string['pluginconfig'] = 'Hybrid Teams configuration';
$string['pluginnewconfig'] = 'New hybrid Teams configuration';
$string['tenantid'] = 'ID Tenant';
$string['clientid'] = 'ID Client';
$string['clientsecret'] = 'Secret Client';
$string['useremail'] = 'Email user';
$string['teams'] = 'teams';
$string['alias'] = 'Teams';
$string['downloadrecordsteams'] = 'Download Teams recordings';
$string['accessmethod'] = 'Access method to config';

$string['teams:view'] = 'Access Teams';
$string['teams:use'] = 'Generate Teams meetings';
$string['teams:record'] = 'Store Teams recordings';

$string['recordingnotfound'] = 'Recording not found in courseid {$a->course}: \'{$a->name}\'  with meetingid: {$a->meetingid}';
$string['recordingnotdownload'] = 'Cannot download recording from courseid {$a->course}: \'{$a->name}\'  with meetingid: {$a->meetingid}';
$string['recordingnoexists'] = 'Recording not exists in courseid {$a->course} : \'{$a->name}\' with meetingid {$a->meetingid}';
$string['emailorganizatornotfound'] = 'The organizers email was not found in the selected Teams organization.';
$string['incorrectconfig'] = 'Incorrect configuration or access to Teams for session \'{$a->name}\' from course {$a->course}. Contact your administrator for Teams settings.';
$string['correctdownload'] = 'Successful download of the course recording {$a->course}: \'{$a->name}\' with meetingid {$a->meetingid}';
$string['meetingcreating'] = 'The meeting is being created. Try again in a few seconds.';
