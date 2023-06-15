<?php

defined('MOODLE_INTERNAL') || die();


//funcion callback:
//funcion para incluir campos de formularios en el form principal
function hybridteachvc_bbb_addform($mform){
    /*$salas = get_salas($mform);  
    $mform->addElement('select', 'host_id', get_string('licenses', 'hybridteachvc_bbb'), $salas);
    return $mform;   
    */
}

/*
//función para buscar salas (licencias) libres.
function get_salas(){
    global $DB;
    //buscar aqui solo las licencias libres, y de la instancia seleccionada.
    //habrá que relaciona la tabla hybridteachvc_zoom_license con hybridteaching_instances
    
    //$select_licencias_libres="SELECT id,nombre_sala, email_licencia, rol
    //".$licencias_ocupadas. " ORDER BY nombre_sala";

    $salas=$DB->get_records('hybridteachvc_zoom_license');
    foreach ($salas as $sala){
        $arraySalas[$sala->host_id]=$sala->nombre_sala." - ".$sala->email_licencia;
    }
    return $arraySalas;

}
*/

