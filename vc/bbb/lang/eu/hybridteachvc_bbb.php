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

$string['pluginname'] = 'BigBlueButton hibridoa';
$string['pluginconfig'] = 'BBB konfigurazio hibridoa';
$string['pluginnewconfig'] = 'BBB konfigurazio hibrido berria';
$string['serverurl'] = 'BigBlueButton zerbitzariaren URLa';
$string['sharedsecret'] = 'BigBlueButton gako sekretua';
$string['bbb'] = 'bbb';
$string['alias'] = 'BBB';

$string['bbberr_field_missing'] = '{$a} ez da aurkitu';
$string['errorwebservice'] = 'BBB web-zerbitzuaren errorea: {$a}.';
$string['bbberr_no_access_token'] = 'Ez da sarbide-token aurkitu';

$string['view_error_unable_join_student'] = 'Ezin izan da BigBlueButton zerbitzarira konektatu.';
$string['view_error_unable_join_teacher'] = 'Ezin izan da BigBlueBotton zerbitzarira konektatu. Jarri harremanetan administratzailearekin.';
$string['view_error_unable_join'] = 'Ezin da saioan sartu. Mesedez, egiaztatu BigBlueButton konfigurazioan gehitutako zerbitzaria eta egiaztatu BigBlueButton zerbitzaria martxan dagoela.';

$string['downloadrecordsbbb'] = 'Deskargatu BigBlueButton grabazioak';

$string['bbb:view'] = 'Ikusi BigBlueButton';
$string['bbb:use'] = 'Sortu BigBlueButton bideokonferentziak';
$string['bbb:record'] = 'Gorde BigBlueButton-en grabazioak';

$string['recordingnotfound'] = 'Grabaketa ez da aurkitu ikastaroan {$a->course}: \'{$a->name}\'';
$string['meetingrunning'] = 'meetingID-ekin bilera {$a} martxan dago. Grabaketa oraindik ez dago erabilgarri.';
$string['unknownerror'] = 'Arazo bat izan da bileraren IDa grabatzean {$a}.';
$string['correctgetrecording'] = 'Saiorako arrakastaz lortutako grabazioa \'{$a->name}\' {$a->sessionid} ikastaroaren id {$a->course}';
