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

$string['pluginname'] = 'Teams híbrido';
$string['pluginconfig'] = 'Configuración de teams híbrido';
$string['pluginnewconfig'] = 'Nueva configuración de teams híbrido';
$string['tenantid'] = 'Tenant ID';
$string['clientid'] = 'Cliente ID';
$string['clientsecret'] = 'Secreto del cliente';
$string['useremail'] = 'Email del usuario';
$string['teams'] = 'teams';
$string['alias'] = 'Teams';
$string['downloadrecordsteams'] = 'Descargar grabaciones Teams';
$string['accessmethod'] = 'Método de acceso para configuración';

$string['teams:view'] = 'Ver Teams';
$string['teams:use'] = 'Generar videconferencias Teams';
$string['teams:record'] = 'Almacenar grabaciones Teams';

$string['recordingnotfound'] = 'No se encontró la grabación en el curso {$a->course}: \'{$a->name}\'  con meetingid: {$a->meetingid}';
$string['recordingnotdownload'] = 'No se pudo descargar la grabación en el curso {$a->course} : \'{$a->name}\' con meetingid {$a->meetingid}';
$string['recordingnoexists'] = 'No existe grabación en el curso  {$a->course} : \'{$a->name}\' con meetingid {$a->meetingid}';
$string['emailorganizatornotfound'] = 'El email del organizador no se ha encontrado en la organización de Teams seleccionada.';
$string['incorrectconfig'] = 'Configuración o acceso erróneos a Teams para la sesión \'{$a->name}\' del curso {$a->course}. Contacte con el administrador para la configuración Teams.';
$string['correctdownload'] = 'Descarga correcta de la grabación del curso {$a->course} : \'{$a->name}\' con meetingid {$a->meetingid}';
