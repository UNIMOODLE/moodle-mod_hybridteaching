<?php

require_once('../../../../config.php');

defined('MOODLE_INTERNAL') || die();

require_once './vendor/autoload.php';
require_once('./classes/meet_handler.php');

$configid = optional_param('id', 0, PARAM_INT);
if (!$configid) {
    $configid = $SESSION->configid;
}

$return = new moodle_url($CFG->wwwroot. '/admin/settings.php?section=hybridteaching_configvcsettings');
$subpluginconfigid = $DB->get_field('hybridteaching_configs', 'subpluginconfigid', ['id' => $configid]);
$meetconfig = $DB->get_record('hybridteachvc_meet_config', ['id' => $subpluginconfigid]);

if (empty($meetconfig)) {
    redirect($return, get_string('loggingerrormeet', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
}
 
$meethandler = new meet_handler($meetconfig);
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
 
if (isset($SESSION->token)) {
    $meethandler->client->setAccessToken($SESSION->token); 
    if ($meetconfig) {
        $meetconfig->token = json_encode($SESSION->token);
        $DB->update_record('hybridteachvc_meet_config' , $meetconfig);
        redirect($return, get_string('alreadyloggedmeet', 'hybridteaching'));
    }
}

$htmlBody = '';
// Check to ensure that the access token was successfully acquired.
if ($meethandler->client->getAccessToken()) { 
    $SESSION->token = $meethandler->client->getAccessToken();
} else {
    $state = mt_rand();
    $meethandler->client->setState($state);
    $SESSION->state = $state;
 
    $authUrl = $meethandler->client->createAuthUrl();
    redirect($authUrl);
}
