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

$string['pluginname'] = 'Hybrid BigBluebutton';
$string['pluginconfig'] = 'Hybrid BBB config';
$string['pluginnewconfig'] = 'New hybrid BBB config';
$string['serverurl'] = 'Url del servidor BigBlueButton';
$string['sharedsecret'] = 'Clave secreta de BigBlueButton';

$string['bbb'] = 'bbb';
$string['alias'] = 'BBB';

$string['bbberr_field_missing'] = '{$a} not found';
$string['errorwebservice'] = 'BBB webservice error: {$a}.';
$string['bbberr_no_access_token'] = 'No access token returned';

$string['view_error_unable_join_student'] = 'Unable to connect to the BigBlueButton server.';
$string['view_error_unable_join_teacher'] = 'Unable to connect to the BigBlueButton server. Please contact an administrator.';
$string['view_error_unable_join'] = 'Unable to join the session. Please check the server added in the BigBlueButton config and check that the BigBlueButton server is up and running.';

$string['downloadrecordsbbb'] = 'Download BigBlueButton records';

$string['bbb:view'] = 'Access BigBlueButton';
$string['bbb:use'] = 'Generate BigBlueButton meetings';
$string['bbb:record'] = 'Store BigBlueButton recordings';

$string['recordingnotfound'] = 'Recording not found in courseid {$a->course}: \'{$a->name}\'';
