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
 * @package    hybridteachstore_youtube
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace hybridteachstore_youtube;
require_once('../../../../../config.php');

defined('MOODLE_INTERNAL') || die();

require_once('./../vendor/autoload.php');

$configid = optional_param('id', 0, PARAM_INT);
global $DB;

require_login();

if (!$configid) {
    $configid = $_SESSION['configid'];
}
$config = $DB->get_record('hybridteachstore_youtube_con', ['id' => $configid]);

/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */

$youtubeclient = new youtube_handler($config);
$client = $youtubeclient->createclient($config);
$redirect = $CFG->wwwroot.'/mod/hybridteaching/store/youtube/classes/youtubeaccess.php';
$client = $youtubeclient->setredirecturi($redirect);
$client->setApprovalPrompt('force');

// Define an object that will be used to make all API requests.
$youtube = new \Google_Service_YouTube($client);

if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }

    $client->authenticate($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
    $_SESSION['configid'] = $configid;
}

if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
    if ($config) {
        $config->token = json_encode($_SESSION['token']);
        $DB->update_record('hybridteachstore_youtube_con', $config);
        unset($_SESSION['token']);
        unset($_SESSION['configid']);
        $return = new \moodle_url('/admin/settings.php?section=hybridteaching_configstoresettings');
        redirect($return);
    }
}
$htmlbody = '';
// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
    $_SESSION['token'] = $client->getAccessToken();
    $_SESSION['configid'] = $configid;
} else {
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;
    $_SESSION['configid'] = $configid;

    $authurl = $client->createauthurl();

    $htmlbody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authurl">authorise access</a> before proceeding.<p>
END;
}
?>

<!doctype html>
<html>
<head>
    <title>Youtube</title>
</head>
<body>
<?php echo $htmlbody?>
</body>
</html>
