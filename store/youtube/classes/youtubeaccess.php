<?php

require_once('../../../../../config.php');

defined('MOODLE_INTERNAL') || die();

require_once './../vendor/autoload.php';
require_once('./youtube_handler.php');

$configid = optional_param('id', 0, PARAM_INT);
global $DB;


//ESTO VER SI SE PUEDE CAMBIAR POR EL $SESSION GLOBAL DEL MOODLE.

if (!$configid) {
    $configid=$_SESSION['configid'];
}
$config = $DB->get_record('hybridteachstore_youtube_con', ['id' => $configid]);


// Call set_include_path() as needed to point to your client library.
//set_include_path($_SERVER['DOCUMENT_ROOT'] . '/directory/to/google/api/');

 
/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */

$youtubeclient = new \youtube_handler($config);
$client=$youtubeclient->createclient($config);
$redirect = $CFG->wwwroot.'/mod/hybridteaching/store/youtube/classes/youtubeaccess.php';
$client=$youtubeclient->setredirecturi($redirect);


// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);
 
if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }
 
    $client->authenticate($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
    $_SESSION['configid']=$configid;
}
 
if (isset($_SESSION['token'])) {
    $client->setAccessToken($_SESSION['token']);
  /*  echo '<code>' ; var_dump(json_encode($_SESSION['token']));echo  '</code>';
    echo '<br><br><code>' ; echo json_encode($_SESSION['token']);echo  '</code>';
*/
    if ($config){
        $config->token=json_encode($_SESSION['token']);
        $DB->update_record('hybridteachstore_youtube_con' ,$config);
        unset($_SESSION['token']);
        unset($_SESSION['configid']);
        $return = new moodle_url($CFG->wwwroot. '/admin/settings.php?section=hybridteaching_configstoresettings');
        redirect($return);
    } 
}
$htmlBody = '';
// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) { 
    $_SESSION['token'] = $client->getAccessToken();
    $_SESSION['configid'] = $configid;
} else {
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;
    $_SESSION['configid'] = $configid;
 
    $authUrl = $client->createAuthUrl();

    $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorise access</a> before proceeding.<p>
END;
}
?>



<!doctype html>
<html>
<head>
    <title>Youtube</title>
</head>
<body>
<?php echo $htmlBody?>
</body>
</html>



<!--
/*
require_once($CFG->dirroot . '/mod/hybridteaching/store/youtube/classes/youtube_handler.php');

$configid = optional_param('id', 0, PARAM_INT);
global $DB;

$config = $DB->get_record('hybridteachstore_youtube_con', ['id' => $configid]);

$youtubeclient = new \youtube_handler($config);

$htmlbody=$youtubeclient->createclient($config);

    echo $htmlbody;
*/
-->