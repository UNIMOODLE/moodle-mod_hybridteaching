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

$string['pluginname'] = 'BigBlueButton híbrid';
$string['pluginconfig'] = 'Configuració de BBB híbrid';
$string['pluginnewconfig'] = 'Nova configuració de BBB híbrid';
$string['serverurl'] = 'Url del servidor BigBlueButton';
$string['sharedsecret'] = 'Clau secreta de BigBlueButton';
$string['bbb'] = 'bbb';
$string['alias'] = 'BBB';

$string['bbberr_field_missing'] = '{$a} no trobat';
$string['errorwebservice'] = 'Error de webservice de BBB: {$a}.';
$string['bbberr_no_access_token'] = 'No s\'ha trobat el token d\'accés';

$string['view_error_unable_join_student'] = 'No s\'ha pogut connectar al servidor BigBlueButton.';
$string['view_error_unable_join_teacher'] = 'No s\'ha pogut connectar al servidor BigBlueBotton. Poseu-vos en contacte amb l\'administrador.';
$string['view_error_unable_join'] = 'Incapaç d\'unir-se a la sessió. Comproveu el servidor afegit a la configuració de BigBlueButton i comproveu que el servidor de BigBlueButton està en funcionament.';

$string['downloadrecordsbbb'] = 'Descarregar enregistraments BigBlueButton';

$string['bbb:view'] = 'Veure BigBlueButton';
$string['bbb:use'] = 'Generar videoconferències BigBlueButton';
$string['bbb:record'] = 'Emmagatzemar enregistraments BigBlueButton';

$string['recordingnotfound'] = 'No s\'ha trobat l\'enregistrament al curs {$a->course}: \'{$a->name}\'';
$string['meetingrunning'] = 'La reunió amb meetingID {$a} està en execució. L\'enregistrament encara no està disponible.';
$string['unknownerror'] = 'S\'ha produït un problema amb l\'enregistrament del meetingID {$a}.';
$string['correctgetrecording'] = 'Enregistrament obtingut correctament per a la sessió \'{$a->name}\' amb sessionid {$a->sessionid} del curs id {$a->course}';
