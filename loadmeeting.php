<?php 

//aqui lanzar evento de que ha clicado en el botón de entrar a reunión
//require_login

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$url=required_param('url',PARAM_RAW);
$id = required_param('id', PARAM_INT);

$nexturl=base64_decode($url);

require_login($id, true);

redirect($nexturl);