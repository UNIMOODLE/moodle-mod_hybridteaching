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
$string['pluginconfig'] = 'Configuración de Teams híbrido';
$string['pluginnewconfig'] = 'Nova configuración de Teams híbrido';
$string['tenantid'] = 'ID do inquilino';
$string['clientid'] = 'ID do cliente';
$string['clientsecret'] = 'Segredo do cliente';
$string['useremail'] = 'Correo electrónico do usuario';
$string['teams'] = 'teams';
$string['alias'] = 'Teams';
$string['downloadrecordsteams'] = 'Descargar gravacións Teams';
$string['accessmethod'] = 'Método de acceso á configuración';

$string['teams:view'] = 'Ver Teams';
$string['teams:use'] = 'Xerar videoconferencias de Teams';
$string['teams:record'] = 'Almacenar as gravacións de Teams';

$string['recordingnotfound'] = 'Non se atopou a gravación no curso {$a->course}: \'{$a->name}\'  con meetingid: {$a->meetingid}';
$string['recordingnotdownload'] = 'Non se puido descargar a gravación do curso {$a->course} : \'{$a->name}\' con meetingid {$a->meetingid}';
$string['recordingnoexists'] = 'Non hai gravación no curso {$a->course} : \'{$a->name}\' con meetingid {$a->meetingid}';
$string['emailorganizatornotfound'] = 'Non se atopou o correo electrónico do organizador na organización de Teams seleccionada.';
$string['incorrectconfig'] = 'Configuración incorrecta de Teams ou acceso á sesión \'{$a->name}\' do curso {$a->course}. Contacta co teu administrador para a configuración de Teams.';
$string['correctdownload'] = 'Descarga exitosa da gravación do curso {$a->course} : \'{$a->name}\' con meetingid {$a->meetingid}';
