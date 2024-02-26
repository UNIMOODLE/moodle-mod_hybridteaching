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

require_once('../../../../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../vendor/autoload.php');

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

$configid = optional_param('id', 0, PARAM_INT);

global $DB;
if (!$configid) {
    $configid = $_SESSION['configid'];
}
$config = $DB->get_record('hybridteachvc_teams_config', ['id' => $configid]);

if (!isset($_GET["code"]) && !isset($_GET["error"])) {
    $_SESSION['configid'] = $configid;

    $guzzle = new \GuzzleHttp\Client();

    $url = 'https://login.microsoftonline.com/' . $config->tenantid. '/oauth2/v2.0/authorize';
    $redirect = $CFG->wwwroot.'/mod/hybridteaching/vc/teams/classes/teamsaccess.php';
    $scopes = "scope=offline_access+User.Read+OnlineMeetings.ReadWrite+Calendars.ReadWrite";
    $params = "client_id=".$config->clientid."&".$scopes."&response_type=code&prompt=login&redirect_uri=";
    $params .= $redirect;
    $params .= "&login_hint=".$config->useremail;
    $authredirecturl = $url.'?'.$params;

    header('Location: '.$authredirecturl);
} else if (isset($_GET["error"])) {
    echo "Error activated:\n\n";
    if (isset($_GET["error_description"])) {
        echo $_GET["error_description"];
    } else {
        var_dump($_GET);  // Debug print.
    }
} else if (isset($_GET["code"])) {

    // Get tokens and end authentication.
     $guzzle = new \GuzzleHttp\Client();

     $url = 'https://login.microsoftonline.com/' . $config->tenantid . '/oauth2/v2.0/token';

     $token = json_decode($guzzle->post($url, [
         'form_params' => [
             'client_id' => $config->clientid,
             'client_secret' => $config->clientsecret,
             'grant_type' => 'authorization_code',
             'redirect_uri' => $CFG->wwwroot.'/mod/hybridteaching/vc/teams/classes/teamsaccess.php',
             'code' => $_GET["code"],
             'scope' => 'offline_access User.Read OnlineMeetings.ReadWrite Calendars.ReadWrite',
         ],
     ])->getBody()->getContents());

     // Save accesstoken and refreshtoken.
    if (isset($token->access_token) && isset($token->refresh_token)) {
        $config->accesstoken = $token->access_token;
        $config->refreshtoken = $token->refresh_token;
        $DB->update_record('hybridteachvc_teams_config', $config);

        unset($_SESSION['configid']);

        $return = new moodle_url($CFG->wwwroot. '/admin/settings.php?section=hybridteaching_configvcsettings');
        redirect($return);
    } if (isset($token->error) && isset($token->error_description)) {
        echo "Error activated:\n";
        echo $token->error_description;
    } else {
        echo "Error";
    }
}
