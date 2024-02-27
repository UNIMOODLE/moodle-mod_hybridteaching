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
 * @package    hybridteachstore_onedrive
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../../config.php');

defined('MOODLE_INTERNAL') || die();
require_login();
require_once(__DIR__ . '/../vendor/autoload.php');

$configid = optional_param('id', 0, PARAM_INT);
global $DB;
if (!$configid) {
    $configid = $_SESSION['configid'];
}
$config = $DB->get_record('hybridteachstore_onedrive_co', ['id' => $configid]);

// VER SI SE PUEDE CAMBIAR POR EL $SESSION GLOBAL DEL MOODLE.
if (!isset($_GET["code"]) && !isset($_GET["error"]) ) {

    $_SESSION['configid'] = $configid;

    $url = 'https://login.microsoftonline.com/' . $config->tenantid. '/oauth2/v2.0/authorize';

    $redirect = $CFG->wwwroot.'/mod/hybridteaching/store/onedrive/classes/onedriveaccess.php';
    $params = "client_id=".$config->clientid;
    $params .= "&scope=offline_access+files.readwrite.all";

    $params .= "&response_type=code&approval_prompt=auto&redirect_uri=".$redirect;
    $authredirecturl = $url. '?' . $params;

    header('Location: ' . $authredirecturl);
} else if (isset($_GET["error"])) {
    echo "Error handler activated:\n\n";
    var_dump($_GET);  // Debug print.
} else if (isset($_GET["code"])) {
     // Obtener tokens y finalizar la parte de autenticación.
     $guzzle = new \GuzzleHttp\Client();

     $url = 'https://login.microsoftonline.com/' . $config->tenantid . '/oauth2/v2.0/token';

     $token = json_decode($guzzle->post($url, [
         'form_params' => [
             'client_id' => $config->clientid,
             'client_secret' => $config->clientsecret,
             'grant_type' => 'authorization_code',
             'redirect_uri' => $CFG->wwwroot.'/mod/hybridteaching/store/onedrive/classes/onedriveaccess.php',
             'code' => $_GET["code"],
         ],
     ])->getBody()->getContents());

     // AQUI GUARDAR ACCESS_TOKEN Y REFRESH_TOKEN.
    if (isset($token->access_token) && isset($token->refresh_token)) {
        $config->accesstoken = $token->access_token;
        $config->refreshtoken = $token->refresh_token;
        $DB->update_record('hybridteachstore_onedrive_co', $config);

        unset($_SESSION['configid']);

        $return = new moodle_url($CFG->wwwroot. '/admin/settings.php?section=hybridteaching_configstoresettings');
        redirect($return);
    } else {
        echo "Error";
    }


}
