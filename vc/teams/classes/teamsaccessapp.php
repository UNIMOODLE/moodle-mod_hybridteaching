<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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

require_once('../../../../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../vendor/autoload.php');

// Include the Microsoft Graph classes.

$configid = optional_param('id', 0, PARAM_INT);

global $DB, $CFG;
if (!$configid) {
    $configid = $_SESSION['configid'];
}
$config = $DB->get_record('hybridteachvc_teams_config', ['id' => $configid]);

$guzzle = new \GuzzleHttp\Client();

if (!isset($_GET['admin_consent'])) {
    $_SESSION['configid'] = $configid;

    $redirect = $CFG->wwwroot.'/mod/hybridteaching/vc/teams/classes/teamsaccessapp.php';
    $url = 'https://login.microsoftonline.com/common/adminconsent?client_id=' . $config->clientid;
    $url .= '&redirect_uri='.$redirect;

    $authredirecturl = $url;
    header('Location: '.$authredirecturl);

} else if ($_GET['admin_consent']) {
    $url = 'https://login.microsoftonline.com/' . $config->tenantid . '/oauth2/v2.0/token';
    $graphresponse = json_decode($guzzle->post($url, [
        'form_params' => [
            'tenant' => $config->tenantid,
            'client_id' => $config->clientid,
            'scope' => 'https://graph.microsoft.com/.default',
            'client_secret' => $config->clientsecret,
            'grant_type' => 'client_credentials',
            'redirect_uri' => $CFG->wwwroot.'/mod/hybridteaching/vc/teams/classes/teamsacessapp.php',
        ],
    ])->getBody()->getContents());

    $result = json_decode(json_encode($graphresponse), true);
    if (isset($result['access_token'])) {
        $config->accesstoken = $result['access_token'];
        $DB->update_record('hybridteachvc_teams_config', $config);
        unset($_SESSION['configid']);
        $return = new moodle_url($CFG->wwwroot. '/admin/settings.php?section=hybridteaching_configvcsettings');
        redirect($return);
    }  if (isset($token->error) && isset($token->error_description)) {
        echo "Error activated:\n";
        echo $token->error_description;
    } else {
        echo "Error";
    }
}
