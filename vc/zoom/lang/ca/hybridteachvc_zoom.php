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

$string['pluginname'] = 'Zoom híbrid';
$string['pluginconfig'] = 'Configuració de zoom híbrid';
$string['pluginnewconfig'] = 'Nova configuració de zoom híbrid';
$string['zoom'] = 'zoom';
$string['alias'] = 'Zoom';
$string['accountid'] = 'ID compte zoom';
$string['clientid'] = 'ID client zoom';
$string['clientsecret'] = 'Clau secreta client zoom';
$string['emaillicense'] = 'Email de la llicència zoom';
$string['zoomerr_field_missing'] = '{$a} no trobat';
$string['errorwebservice'] = 'Error de webservice de Zoom: {$a}.';
$string['zoomerr_no_access_token'] = 'No s\'ha tornat cap token d\'accés';
$string['licenses'] = 'Llicències';
$string['downloadrecordszoom'] = 'Descarregar enregistraments Zoom';
$string['maxdownloadattempts'] = 'Número. màxim d\'intents de descàrrega d\'enregistraments';
$string['maxdownloadattempts_help'] = 'Quan s\'hagi intentat descarregar aquesta quantitat màxima d\'intents s\'aturaran els intents de desacàrrega';
$string['chatnamefile'] = 'Xat de la reunió';
$string['recordingnotdownload'] = 'No es pot descarregar la gravació de courseid {$a->course}: \'{$a->name}\'';
$string['recordingdownloaded'] = 'Grabació descarregada correctament de l\'identificador de curs {$a->course} para la sesión \'{$a->name}\'';
$string['confignotfound'] = 'Configuració no trobada amb l\'identificador {$a->config} per a la sessió \'{$a->name}\'';
$string['meetingnotfound'] = 'Meeting no trobat en zoom del id de curs \'{$a->course}\' per a la sessió \'{$a->name}\'';
$string['errorgetmeeting'] = 'Error webservice al intentar obtenir meeting de zoom.';
$string['recordingnotfound'] = 'No he trobat enregistraments per a la sessió \'{$a->name}\' de l\'identificador del curs \'{$a->course}\'';
$string['alreadydownloaded'] = 'Heu descarregat prèviament l\'identificador del curs \'{$a->course}\' per a la sessió \'{$a->name}\'. No es pot descarregar de nou.';

$string['zoom:view'] = 'Veure Zoom';
$string['zoom:use'] = 'Generar videoconferències Zoom';
$string['zoom:record'] = 'Almacenar gravacions Zoom';
