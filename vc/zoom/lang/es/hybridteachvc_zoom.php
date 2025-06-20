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
$string['pluginconfig'] = 'Configuración de zoom híbrido';
$string['pluginnewconfig'] = 'Nueva configuración de zoom híbrido';
$string['zoom'] = 'zoom';
$string['alias'] = 'Zoom';
$string['accountid'] = 'ID cuenta zoom';
$string['clientid'] = 'ID cliente zoom';
$string['clientsecret'] = 'Clave secreta cliente zoom';
$string['emaillicense'] = 'Email de la licencia zoom';
$string['zoomerr_field_missing'] = '{$a} no encontrado';
$string['errorwebservice'] = 'Error de webservice de Zoom: {$a}.';
$string['zoomerr_no_access_token'] = 'No se devolvió ningún token de acceso';
$string['licenses'] = 'Licencias';
$string['downloadrecordszoom'] = 'Descargar grabaciones Zoom';
$string['maxdownloadattempts'] = 'Num. máximo de intentos de descarga de grabaciones';
$string['maxdownloadattempts_help'] = 'Cuando se haya intentado descargar esta cantidad máxima de intentos se detendrán los intentos de descarga';
$string['chatnamefile'] = 'Chat de la reunión';
$string['recordingnotdownload'] = 'No se puede descargar la grabación de courseid {$a->course}: \'{$a->name}\'';
$string['recordingdownloaded'] = 'Grabación descargada correctamente del id de curso {$a->course} para la sesión \'{$a->name}\'';
$string['confignotfound'] = 'Configuración no encontrada con Id {$a->config} para la sesión \'{$a->name}\'';
$string['meetingnotfound'] = 'Meeting no encontrado en zoom del id de curso \'{$a->course}\' para la sesión \'{$a->name}\'';
$string['errorgetmeeting'] = 'Error webservice al intentar obtener meeting de zoom.';
$string['recordingnotfound'] = 'No se encontraron grabaciones para la sesión \'{$a->name}\' del id de curso \'{$a->course}\'';
$string['alreadydownloaded'] = 'Grabación ya descargada previamente del id de curso \'{$a->course}\' para la sesión \'{$a->name}\'. No se descargará de nuevo.';

$string['zoom:view'] = 'Ver Zoom';
$string['zoom:use'] = 'Generar videconferencias Zoom';
$string['zoom:record'] = 'Almacenar grabaciones Zoom';

$string['cachedef_oauth'] = 'Oauth cache';
