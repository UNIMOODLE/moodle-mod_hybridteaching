<?php 

//aqui lanzar evento de que ha clicado en el botón de entrar a reunión
//require_login

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$url=required_param('url',PARAM_RAW);
$id = required_param('id', PARAM_INT);
//$type=required_param('type',PARAM_RAW);

$nexturl=base64_decode($url);

global $DB;
$course=$DB->get_record_sql("SELECT ht.course FROM {hybridteaching} AS ht
        INNER JOIN {hybridteaching_session} AS hs ON hs.hybridteachingid=ht.id
        INNER JOIN {hybridteachvc_zoom} AS zoom ON zoom.htsession=hs.id
        WHERE zoom.id=:id"
        ,array('id'=>$id));   

require_login($course->course, true);

redirect($nexturl);