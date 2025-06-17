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

$string['pluginname'] = 'Zoom hibridoa';
$string['pluginconfig'] = 'Zoom hibridoaren ezarpenak';
$string['pluginnewconfig'] = 'Zoom hibridoaren konfigurazio berria';
$string['zoom'] = 'zoom';
$string['alias'] = 'Zoom';
$string['accountid'] = 'Zoom kontuaren IDa';
$string['clientid'] = 'Zoom bezeroaren IDa';
$string['clientsecret'] = 'Zoom bezeroaren gako sekretua';
$string['emaillicense'] = 'Zoom lizentziaren posta elektronikoa';
$string['zoomerr_field_missing'] = '{$a} ez da aurkitu';
$string['errorwebservice'] = 'Zoom web-zerbitzuaren errorea: {$a}.';
$string['zoomerr_no_access_token'] = 'Ez da sarbide-token itzuli';
$string['licenses'] = 'Lizentziak';
$string['downloadrecordszoom'] = 'Deskargatu Zoom grabazioak';
$string['maxdownloadattempts'] = 'Zenbakia gehienezko grabaketa deskargatzeko saiakerak';
$string['maxdownloadattempts_help'] = 'Gehienezko saiakera kopuru hori deskargatzen saiatzen direnean, deskarga saiakerak gelditu egingo dira';
$string['chatnamefile'] = 'Bilera-txata';
$string['recordingnotdownload'] = 'Ezin da deskargatu courseid grabaketa {$a->course}: \'{$a->name}\'';
$string['recordingdownloaded'] = 'Ondo deskargatu da ikastaroaren id {$a->course} para la sesión \'{$a->name}\'';
$string['confignotfound'] = 'Ez da aurkitu IDarekin konfigurazioa {$a->config} para la sesión \'{$a->name}\'';
$string['meetingnotfound'] = 'Bilera ez da aurkitu ikastaroaren ID zoomean \'{$a->course}\' para la sesión \'{$a->name}\'';
$string['errorgetmeeting'] = 'Web-zerbitzuaren errorea zoom bilera lortzen saiatzean.';
$string['recordingnotfound'] = 'Ez da saiorako grabaziorik aurkitu \'{$a->name}\' ikastaroaren id \'{$a->course}\'';
$string['alreadydownloaded'] = 'Aurretik deskargatutako ikastaroaren IDaren grabazioa \'{$a->course}\' saiorako \'{$a->name}\'. Ez da berriro deskargatuko.';

$string['zoom:view'] = 'Ikusi Zooma';
$string['zoom:use'] = 'Sortu Zoom bideokonferentziak';
$string['zoom:record'] = 'Gorde Zoom grabazioak';
$string['cachedef_oauth'] = 'Oauth cachea';
