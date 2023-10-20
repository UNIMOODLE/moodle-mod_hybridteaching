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

/**
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_bbb;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use hybridteachvc_bbb\bbbproxy;
use hybridteachvc_bbb\meeting;
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');

use mod_bigbluebuttonbn\plugin;

class sessions {
    protected $bbbsession;

    public function __construct($htsessionid = null) {
        if (!empty($htsessionid)) {
            $this->bbbsession = $this->load_session($htsessionid);
        }
    }

    public function load_session($htsessionid) {
        global $DB;
        $this->bbbsession = $DB->get_record('hybridteachvc_bbb', ['htsession' => $htsessionid]);
        return $this->bbbsession;
    }

    public function set_session($htsessionid) {
        $this->bbbsession = $this->load_session($htsessionid);
    }

    public function get_session() {
        return $this->bbbsession;
    }

    /**
     * Creates a new session by calling the Hybrid Web Service's create_meeting function
     * and stores the data returned from it in the hybridteachvc_bbb table if the response
     * is not false.
     *
     * @param mixed $session the data to be passed to the create_meeting function
     * @throws 
     * @return mixed the response from the create_meeting function
     */
    public function create_unique_session_extended($session, $ht) {
        global $DB;

        $bbbconfig = $this->load_bbb_config($ht->config);

        $meeting = new meeting($bbbconfig);
        $response = $meeting->create_meeting($session,$ht);

        if (isset($response['returncode']) && $response['returncode']=='SUCCESS'){
            $bbb = $this->populate_htbbb_from_response($session,$response);
            $bbb->id = $DB->insert_record('hybridteachvc_bbb', $bbb);        
            return true;
        }
        else {
            return false;
        }
    }
    
    public function update_session_extended($data) {
        //no requires action 
    }

    /**
     * Deletes a session and its corresponding BBB meeting via the webservice (if exists).
     *
     * @param mixed $id The ID of the session to delete.
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    public function delete_session_extended($htsession, $configid) {
        global $DB;
        $bbbconfig = $this->load_bbb_config($configid);      
        $bbb = $DB->get_record('hybridteachvc_bbb', ['htsession' => $htsession]);
        $meeting = new meeting($bbbconfig);
        $meeting->end_meeting($bbb->meetingid, $bbb->moderatorpass);

        $DB->delete_records('hybridteachvc_bbb', ['htsession' => $htsession]);
    }

    /**
     * Populates a new stdClass object with relevant data from a BBB API response and returns it.
     *
     * @param mixed $data stdClass object containing htsession data
     * @param mixed $response stdClass object containing BBB API response data
     * @return stdClass $newbbb stdClass object containing relevant data
     */
    public function populate_htbbb_from_response($data, $response) {        
        $newbbb = new \stdClass();
        $newbbb->htsession = $data->id;   //session id
        $newbbb->meetingid = $response['meetingID'];
        $newbbb->moderatorpass = $response['moderatorPW'];
        $newbbb->viewerpass = $response['attendeePW'];
        $newbbb->createtime = $response['createTime'];

        return $newbbb;
    }

        /**
     * Loads a BBB config based on the given config ID.
     *
     * @param int $configid The ID of the config to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The Zoom config record on success, or false on failure.
     */
    public function load_bbb_config($configid) {
        global $DB;

        $sql = "SELECT bi.serverurl, bi.sharedsecret, bi.pollinterval
                  FROM {hybridteaching_configs} hi
                  JOIN {hybridteachvc_bbb_config} bi ON bi.id = hi.subpluginconfigid
                 WHERE hi.id = :configid AND hi.visible = 1";

        $config = $DB->get_record_sql($sql, ['configid' => $configid]);
        return $config;
    }

    //carga la configuración de isntancia desde el htsession
    // (ya inicializado con el constructor)

    public function load_bbb_config_from_session(){
        global $DB;
        $sql = "SELECT h.config
                FROM mdl_hybridteaching h
                JOIN mdl_hybridteaching_session hs ON hs.hybridteachingid=h.id
                WHERE hs.id=:htsession";

        $configpartial = $DB->get_record_sql($sql, ['htsession' => $this->bbbsession->htsession]);
        $config = $this->load_bbb_config($configpartial->config);
        return $config;

    }

    public function get_zone_access() {
        //ESTA FUNCION NO NECESITA NINGÚN $hybridteachingid
        //PORQUE YA ESTÁ INICIALIZADA EN EL CONSTRUCTOR CON SU SESSION, 
        //NO ES NECESARIO NINGÚN id DE ACTIVIDAD     
        //la info ya está cargada del constructor

        //aquÍ solo calcular los datos necesarios de la zona de acceso
        //comprobar si el rol es para iniciar reunión o para entrar a reunión
        //y mandamos la url de acceso (o bien starturl o bien joinurl)
        //starturl o join url, según sea hospedador o participante


            global $USER;

            if ($this->bbbsession) {
                $bbbconfig = $this->load_bbb_config_from_session();
                $bbbproxy=new bbbproxy($bbbconfig);

                //COMO BBB NO TIENE SALA DE ESPERA AL MODERADOR, HAY QUE SIMULARLA EN BBB:

                //comprobar aquí si es moderator o viewer.
                //Si es admin o moderador, poder entrar.
                //Si es viewer, comprobar si está activa la opción de waitmoderator. 
                    //Si es así, sacar un msj de "esperando al moderador". 
                    //Si no, poder entrar.

                $url=$bbbproxy->get_join_url(
                    $this->bbbsession->meetingid,
                    $USER->username,
                    'https://www.urldelogout',   ///comprobar
                    'MODERATOR',     //aqui VIEWER or MODERATOR. Según sea admin o moderator, o viewer
                    null, //un token
                    $USER->id,
                    $this->bbbsession->createtime  
                );

                $array = [
                    'id' => $this->bbbsession->id,
                    'ishost' => true,
                    'isaccess' => true,
                    'url' => base64_encode($url),
                ];
                return $array;
            } else {
                return null;
            }
    }


    public function get_recording (){
        $bbbconfig = $this->load_bbb_config_from_session();
        $bbbproxy=new bbbproxy($bbbconfig);

        $url='';
        $response=$bbbproxy->get_url_recording_by_recordid($this->bbbsession->recordingid);
        if ($response['returncode'] == 'SUCCESS') {
            if ($response['recordingid']!='') {
                $url = $response['recordingid'];
            }
        }
        return $url;
    }
}
