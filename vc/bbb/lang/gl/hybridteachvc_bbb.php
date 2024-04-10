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
$string['pluginconfig'] = 'Configuración híbrida BBB';
$string['pluginnewconfig'] = 'Nova configuración híbrida BBB';
$string['serverurl'] = 'URL do servidor de BigBlueButton';
$string['sharedsecret'] = 'Chave secreta BigBlueButton';
$string['bbb'] = 'bbb';
$string['alias'] = 'BBB';

$string['bbberr_field_missing'] = 'Non se atopou {$a}';
$string['errorwebservice'] = 'Erro do servizo web BBB: {$a}.';
$string['bbberr_no_access_token'] = 'Non se atopou ningún token de acceso';

$string['view_error_unable_join_student'] = 'Non se puido conectar ao servidor BigBlueButton.';
$string['view_error_unable_join_teacher'] = 'Non se puido conectar ao servidor BigBlueBotton. Contacte co administrador.';
$string['view_error_unable_join'] = 'Non se puido unir á sesión. Comprobe o servidor engadido na configuración de BigBlueButton e verifique que o servidor de BigBlueButton estea en funcionamento.';

$string['downloadrecordsbbb'] = 'Descargar gravacións de BigBlueButton';

$string['bbb:view'] = 'Ver BigBlueButton';
$string['bbb:use'] = 'Xera videoconferencias BigBlueButton';
$string['bbb:record'] = 'Almacena gravacións de BigBlueButton';

$string['recordingnotfound'] = 'Non se atopou a gravación no curso {$a->course}: \'{$a->name}\'';
$string['meetingrunning'] = 'La reunión con meetingID {$a} está en execución. A gravación aínda non está dispoñible.';
$string['unknownerror'] = 'Produciuse un problema ao gravar o identificador da reunión {$a}.';
$string['correctgetrecording'] = 'Gravación obtida con éxito para a sesión \'{$a->name}\' co identificador de sesión {$a->sessionid} do curso ID {$a->course}';
