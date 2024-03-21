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
 * @package    hybridteachvc_bbb
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'BigBlueButton híbrido';
$string['pluginconfig'] = 'Configuración de bbb híbrido';
$string['pluginnewconfig'] = 'Nueva configuración de bbb híbrido';
$string['serverurl'] = 'Url del servidor BigBlueButton';
$string['sharedsecret'] = 'Clave secreta de BigBlueButton';
$string['bbb'] = 'bbb';
$string['alias'] = 'BBB';

$string['bbberr_field_missing'] = '{$a} no encontrado';
$string['errorwebservice'] = 'Error de webservice de BBB: {$a}.';
$string['bbberr_no_access_token'] = 'No se encontró token de acceso';

$string['view_error_unable_join_student'] = 'No se pudo conectar al servidor BigBlueButton.';
$string['view_error_unable_join_teacher'] = 'No se pudo conectar al servidor BigBlueBotton. Póngase en contacto con el administrador.';
$string['view_error_unable_join'] = 'Incapaz de unirse a la sesión. Por favor compruebe el servidor añadido en la configuración de BigBlueButton y compruebe que el servidor de BigBlueButton está en funcionamiento.';

$string['downloadrecordsbbb'] = 'Descargar grabaciones BigBlueButton';

$string['bbb:view'] = 'Ver BigBlueButton';
$string['bbb:use'] = 'Generar videconferencias BigBlueButton';
$string['bbb:record'] = 'Almacenar grabaciones BigBlueButton';

$string['recordingnotfound'] = 'No se encontró la grabación en el curso {$a->course}: \'{$a->name}\'';
$string['meetingrunning'] = 'La reunión con meetingID {$a} está en ejecución. Aún no está disponible la grabación.';
$string['unknownerror'] = 'Se produjo un problema con la grabación del meetingID {$a}.';
$string['correctgetrecording'] = 'Grabación obtenida correctamente para la sesión \'{$a->name}\' del curso id {$a->course}';
