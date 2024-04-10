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
 * @package    hybridteachvc_zoom
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Hybrid Zoom';
$string['pluginconfig'] = 'Hybrid zoom config';
$string['pluginnewconfig'] = 'New hybrid zoom config';
$string['zoom'] = 'zoom';
$string['alias'] = 'Zoom';
$string['accountid'] = 'Zoom account ID';
$string['clientid'] = 'Zoom client ID';
$string['clientsecret'] = 'Zoom client secret';
$string['emaillicense'] = 'Zoom license email';
$string['zoomerr_field_missing'] = '{$a} not found';
$string['errorwebservice'] = 'Zoom webservice error: {$a}.';
$string['zoomerr_no_access_token'] = 'No access token returned';
$string['licenses'] = 'Licenses';
$string['downloadrecordszoom'] = 'Download Zoom records';
$string['maxdownloadattempts'] = 'Max download attempts for recordings';
$string['maxdownloadattempts_help'] = 'When downloading has been attempted this maximum number of times, attempts to download the recording will stop.';
$string['chatnamefile'] = 'Chat meeting';
$string['recordingnotdownload'] = 'Cannot download recording from courseid {$a->course}: \'{$a->name}\'';
$string['recordingdownloaded'] = 'Recording downloaded correctly from courseid {$a->course} for session \'{$a->name}\'';
$string['confignotfound'] = 'Configuration not found with Id {$a->config} for session \'{$a->name}\'';
$string['meetingnotfound'] = 'Meeting not found in zoom from courseid \'{$a->course}\' for session \'{$a}\'';
$string['errorgetmeeting'] = 'Webservice error when trying to obtain zoom meeting.';
$string['recordingnotfound'] = 'Recording not found for session \'{$a}\' from courseid \'{$a->course}\'';
$string['alreadydownloaded'] = 'Recording already downloaded from courseid \'{$a->course}\' for session \'{$a->name}\'. It will not download again';

$string['zoom:view'] = 'Access Zoom';
$string['zoom:use'] = 'Generate Zoom meetings';
$string['zoom:record'] = 'Store Zoom recordings';
