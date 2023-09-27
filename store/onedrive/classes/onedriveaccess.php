<?php

require_once('../../../../../config.php');

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../vendor/autoload.php'; 

$configid = optional_param('id', 0, PARAM_INT);
global $DB;
if (!$configid) {
    $configid=$_SESSION['configid'];
}
$config = $DB->get_record('hybridteachstore_onedrive_co', ['id' => $configid]);

//VER SI SE PUEDE CAMBIAR POR EL $SESSION GLOBAL DEL MOODLE.
if (!isset($_GET["code"]) && !isset($_GET["error"]) ){   

    $_SESSION['configid']=$configid;

    $url = 'https://login.microsoftonline.com/' . $config->tenantid. '/oauth2/v2.0/authorize';

    $redirect = $CFG->wwwroot.'/mod/hybridteaching/store/onedrive/classes/onedriveaccess.php';
    $params="client_id=".$config->clientid;
    $params.="&scope=offline_access+files.readwrite.all";
    
    $params.="&response_type=code&approval_prompt=auto&redirect_uri=".$redirect;
    $auth_redirect_url = $url. '?' . $params;
    
    header('Location: ' . $auth_redirect_url);
}
elseif (isset($_GET["error"])){
    echo "Error handler activated:\n\n";
    var_dump($_GET);  //Debug print 
}else if (isset($_GET["code"])){
     //obtener tokens y finalizar la parte de autenticación
     $guzzle = new \GuzzleHttp\Client();

     $url = 'https://login.microsoftonline.com/' . $config->tenantid . '/oauth2/v2.0/token';
     
     $token = json_decode($guzzle->post($url, [
         'form_params' => [
             'client_id' => $config->clientid,
             'client_secret' => $config->clientsecret,
             'grant_type' => 'authorization_code',
             'redirect_uri' => $CFG->wwwroot.'/mod/hybridteaching/store/onedrive/classes/onedriveaccess.php',
             'code' => $_GET["code"],
             //'scope' => 'offline_access files.readwrite.all'
         ],
     ])->getBody()->getContents());

     //AQUI GUARDAR ACCESS_TOKEN Y REFRESH_TOKEN
     if (isset($token->access_token) && isset($token->refresh_token)){
        $config->accesstoken=$token->access_token;
        $config->refreshtoken=$token->refresh_token;
        $DB->update_record('hybridteachstore_onedrive_co' ,$config);

        unset($_SESSION['configid']);
        
        $return = new moodle_url($CFG->wwwroot. '/admin/settings.php?section=hybridteaching_configstoresettings');
        redirect($return);
    }
    else {
        echo "Error";
    }
    
     
}
