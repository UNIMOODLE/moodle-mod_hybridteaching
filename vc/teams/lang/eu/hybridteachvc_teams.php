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

$string['pluginname'] = 'Teams hibridoa';
$string['pluginconfig'] = 'Talde hibridoen konfigurazioa';
$string['pluginnewconfig'] = 'Talde hibridoen konfigurazio berria';
$string['tenantid'] = 'Maizterren IDa';
$string['clientid'] = 'Bezeroaren IDa';
$string['clientsecret'] = 'Bezeroaren sekretua';
$string['useremail'] = 'Erabiltzailearen posta elektronikoa';
$string['teams'] = 'teams';
$string['alias'] = 'Teams';
$string['downloadrecordsteams'] = 'Deskargatu Teams grabazioak';
$string['accessmethod'] = 'Konfigurazioa sartzeko metodoa';

$string['teams:view'] = 'Ikusi taldeak';
$string['teams:use'] = 'Sortu Teams bideokonferentziak';
$string['teams:record'] = 'Gorde Teams grabazioak';

$string['recordingnotfound'] = 'Grabaketa ez da aurkitu ikastaroan {$a->course}: \'{$a->name}\'  meetingid-arekin: {$a->meetingid}';
$string['recordingnotdownload'] = 'Ezin izan da ikastaroan grabazioa deskargatu {$a->course} : \'{$a->name}\' meetingid-arekin {$a->meetingid}';
$string['recordingnoexists'] = 'Ikastaroan ez dago grabaketarik {$a->course} : \'{$a->name}\' meetingid-arekin {$a->meetingid}';
$string['emailorganizatornotfound'] = 'Antolatzailearen helbide elektronikoa ez da aurkitu hautatutako Teams erakundean.';
$string['incorrectconfig'] = 'Taldeen konfigurazioa edo saiorako sarbidea okerra \'{$a->name}\' ikastaroaren {$a->course}. Jarri harremanetan administratzailearekin Teams ezarpenetarako.';
$string['correctdownload'] = 'Ikastaroaren grabazioa deskargatu da {$a->course} : \'{$a->name}\' meetingid-arekin {$a->meetingid}';
$string['meetingcreating'] = 'Bilera sortzen ari da. Saiatu berriro segundo gutxi barru.';
