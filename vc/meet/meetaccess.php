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
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../../../config.php');
require_once('./vendor/autoload.php');

require_login();

$configid = optional_param('id', 0, PARAM_INT);
if (!$configid) {
    $configid = $SESSION->configid;
}

$return = new moodle_url($CFG->wwwroot. '/admin/settings.php?section=hybridteaching_configvcsettings');
$subpluginconfigid = $DB->get_field('hybridteaching_configs', 'subpluginconfigid', ['id' => $configid]);
$meetconfig = $DB->get_record('hybridteachvc_meet_config', ['id' => $subpluginconfigid]);

if (empty($meetconfig)) {
    redirect($return, get_string('loggingerrormeet', 'hybridteachvc_meet'), null, \core\output\notification::NOTIFY_ERROR);
}

$meethandler = new \hybridteachvc_meet\meet_handler($meetconfig);
$redirect = $CFG->wwwroot.'/mod/hybridteaching/vc/meet/meetaccess.php';
$meethandler->setredirecturi($redirect);
$meetconnect = new Google_Service_Calendar($meethandler->client);
$SESSION->configid = $configid;

if (isset($_GET['code'])) {
    if (strval($SESSION->state) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }

    $meethandler->client->authenticate($_GET['code']);
    $SESSION->token = $meethandler->client->getAccessToken();
}

if (isset($SESSION->token) && $SESSION->token['access_token'] != 0) {
    $meethandler->client->setAccessToken($SESSION->token);
    if ($meetconfig) {
        $meetconfig->token = json_encode($SESSION->token);
        $DB->update_record('hybridteachvc_meet_config' , $meetconfig);
        redirect($return, get_string('alreadyloggedmeet', 'hybridteachvc_meet'));
    }
}

$htmblody = '';
// Check to ensure that the access token was successfully acquired.
if ($meethandler->client->getAccessToken() && $meethandler->client->getAccessToken()['access_token'] != 0) {
    $SESSION->token = $meethandler->client->getAccessToken();
} else {
    $state = mt_rand();
    $meethandler->client->setState($state);
    $SESSION->state = $state;

    $authurl = $meethandler->client->createAuthUrl();
    redirect($authurl);
}
