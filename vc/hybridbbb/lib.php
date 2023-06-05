<?php

defined('MOODLE_INTERNAL') || die();


//funcion callback:
//funcion para incluir campos de formularios en el form principal
function hybridteachingvc_hybridbbb_addform($mform){
    /*$salas = get_salas($mform);  
    $mform->addElement('select', 'host_id', get_string('licenses', 'hybridteachingvc_hybridzoom'), $salas);
    return $mform;   
    */
}

/*
//función para buscar salas (licencias) libres.
function get_salas(){
    global $DB;
    //buscar aqui solo las licencias libres, y de la instancia seleccionada.
    //habrá que relaciona la tabla hybridteaching_zoom_licenses con hybridteaching_instances
    
    //$select_licencias_libres="SELECT id,nombre_sala, email_licencia, rol
    //".$licencias_ocupadas. " ORDER BY nombre_sala";

    $salas=$DB->get_records('hybridteaching_zoom_licenses');
    foreach ($salas as $sala){
        $arraySalas[$sala->host_id]=$sala->nombre_sala." - ".$sala->email_licencia;
    }
    return $arraySalas;

}
*/

