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

$string['pluginname'] = 'Teams híbrid';
$string['pluginconfig'] = 'Configuració de Teams híbrid';
$string['pluginnewconfig'] = 'Nova configuració de Teams híbrid';
$string['tenantid'] = 'ID de llogater';
$string['clientid'] = 'ID de client';
$string['clientsecret'] = 'Secret del client';
$string['useremail'] = 'Email de l\'usuari';
$string['teams'] = 'teams';
$string['alias'] = 'Teams';
$string['downloadrecordsteams'] = 'Descarregar enregistraments Teams';
$string['accessmethod'] = 'Mètode d\'accés per a configuració';

$string['teams:view'] = 'Veure Teams';
$string['teams:use'] = 'Generar videoconferències Teams';
$string['teams:record'] = 'Emmagatzemar enregistraments Teams';

$string['recordingnotfound'] = 'No s\'ha trobat l\'enregistrament al curs {$a->course}: \'{$a->name}\'  amb meetingid: {$a->meetingid}';
$string['recordingnotdownload'] = 'No s\'ha pogut descarregar l\'enregistrament al curs {$a->course} : \'{$a->name}\' amb meetingid {$a->meetingid}';
$string['recordingnoexists'] = 'No hi ha gravació al curs  {$a->course} : \'{$a->name}\' amb meetingid {$a->meetingid}';
$string['emailorganizatornotfound'] = 'El correu electrònic de l\'organitzador no s\'ha trobat a l\'organització de Teams seleccionada.';
$string['incorrectconfig'] = 'Configuració o accés erronis a Teams per a la sessió \'{$a->name}\' del curs {$a->course}. Contacteu amb l\'administrador per a la configuració Teams.';
$string['correctdownload'] = 'Descàrrega correcta de l\'enregistrament del curs {$a->course} : \'{$a->name}\' amb meetingid {$a->meetingid}';
