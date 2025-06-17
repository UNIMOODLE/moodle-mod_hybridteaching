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

$string['pluginname'] = 'Zoom híbrido';
$string['pluginconfig'] = 'Configuración do zoom híbrido';
$string['pluginnewconfig'] = 'Nova configuración de zoom híbrido';
$string['zoom'] = 'zoom';
$string['alias'] = 'Zoom';
$string['accountid'] = 'ID conta zoom';
$string['clientid'] = 'ID cliente zoom';
$string['clientsecret'] = 'Chave secreta cliente de zoom';
$string['emaillicense'] = 'Correo electrónico de licenza de zoom';
$string['zoomerr_field_missing'] = '{$a} non atopado';
$string['errorwebservice'] = 'Erro do servizo web de zoom: {$a}.';
$string['zoomerr_no_access_token'] = 'Erro do servizo web de zoom';
$string['licenses'] = 'Licenzas';
$string['downloadrecordszoom'] = 'Descargar gravacións de zoom';
$string['maxdownloadattempts'] = 'Número máximo de intentos de descarga de gravacións';
$string['maxdownloadattempts_help'] = 'Cando se intentou descargar este número máximo de intentos, os intentos de descarga pararanse';
$string['chatnamefile'] = 'Chat de reunión';
$string['recordingnotdownload'] = 'Non se puido descargar a gravación do cursoid {$a->course}: \'{$a->name}\'';
$string['recordingdownloaded'] = 'A gravación descargada con éxito, por suposto, a identificación do curso {$a->course} para la sesión \'{$a->name}\'';
$string['confignotfound'] = 'Non se atopou a configuración con Id {$a->config} para a sesión \'{$a->name}\'';
$string['meetingnotfound'] = 'Non se atopou a reunión no zoom do ID do curso \'{$a->course}\' para a sesión \'{$a->name}\'';
$string['errorgetmeeting'] = 'Erro no servizo web ao tentar obter a reunión de zoom.';
$string['recordingnotfound'] = 'Non se atoparon gravacións para a sesión \'{$a->name}\' do ID do curso \'{$a->course}\'';
$string['alreadydownloaded'] = 'GGravación descargada previamente do ID do curso \'{$a->course}\' para a sesión \'{$a->name}\'. Non se descargará de novo.';

$string['zoom:view'] = 'Ver Zoom';
$string['zoom:use'] = 'Xera videoconferencias Zoom';
$string['zoom:record'] = 'Almacenar gravacións de Zoom';
$string['cachedef_oauth'] = 'Oauth cache';
