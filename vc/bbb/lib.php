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



// Funcion callback:
// funcion para incluir campos de formularios en el form principal.
function hybridteachvc_bbb_addform($mform) {
    /*$salas = get_salas($mform);
    $mform->addElement('select', 'host_id', get_string('licenses', 'hybridteachvc_bbb'), $salas);
    return $mform;
    */
}

/*
// Función para buscar salas (licencias) libres.
function get_salas(){
    global $DB;
    //buscar aqui solo las licencias libres, y de la instancia seleccionada.
    //habrá que relaciona la tabla hybridteachvc_zoom_license con hybridteaching_configs

    //$select_licencias_libres="SELECT id,nombre_sala, email_licencia, rol
    //".$licencias_ocupadas. " ORDER BY nombre_sala";

    $salas=$DB->get_records('hybridteachvc_zoom_license');
    foreach ($salas as $sala){
        $arraySalas[$sala->host_id]=$sala->nombre_sala." - ".$sala->email_licencia;
    }
    return $arraySalas;

}
*/

