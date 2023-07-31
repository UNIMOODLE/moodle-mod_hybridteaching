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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot.'/mod/hybridteaching/vc/bbb/classes/webservice.php');
require_once($CFG->dirroot.'/mod/hybridteaching/vc/bbb/classes/bbbproxy.php');
use mod_bigbluebuttonbn\meeting;
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
        return $DB->get_record('hybridteachvc_bbb', ['htsession' => $htsessionid]);
    }

    public function get_session() {
        return $this->bbbsession;
    }

    /**
     * Creates a new session by calling the Hybrid Web Service's create_meeting function
     * and stores the data returned from it in the hybridteachvc_zoom table if the response
     * is not false.
     *
     * @param mixed $data the data to be passed to the create_meeting function
     * @throws 
     * @return mixed the response from the create_meeting function
     */
    public function create_unique_session_extended($data) {
        global $DB, $USER;

        // As it is a new activity, assign passwords. 
        //process_pre_save:   //mod_helper::process_pre_save($moduleinstance);

//AQUI PONER LO MISMO QUE EN BBB, EN EL MEETING.PHP, EN FUNCION CREATE_MEETING(), ES MEJOR

/*
        $bbb = new \stdClass();
        $bbb->htsession = $data->htsession;
        $bbb->meetingid=meeting::get_unique_meetingid_seed();
        $bbb->moderatorpass = plugin::random_password(12);
        $bbb->viewerpass = plugin::random_password(12, $bbb->moderatorpass);
        [$bbb->guestlinkuid, $bbb->guestpassword] = plugin::generate_guest_meeting_credentials();

        $bbb->timecreated = time();
        $bbb->timemodified = 0;
        $bbb->createdby = $USER->id;

        $bbb->id = $DB->insert_record('hybridteachvc_bbb', $bbb);
        */

        //FALTA COMPLETAR
        return false;
    }
    
    public function update_session_extended($data) {
        /*
        global $DB, $USER;
        $errormsg = '';
        $session = new \stdClass();
        $session->id = $data->id;
        $session->name = $data->name;
        $session->timemodified = time();
        $session->modifiedby = $USER->id;
        if (!$DB->update_record($this->table, $session)) {
            $errormsg = 'errorupdatesession';
        }
        return $errormsg;
        */
    }

    /**
     * Deletes a session and its corresponding BBB meeting via the mod_hybrid_webservice (if exists).
     *
     * @param mixed $id The ID of the session to delete.
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    public function delete_session_extended($htsession, $instanceid) {
        global $DB;

        $bbb = $DB->get_record('hybridteachvc_bbb', ['htsession' => $htsession]);
        $meeting = new meeting($bbb);
        $meeting->end_meeting(meetingid, $bbb->moderatorpass);

        $DB->delete_records('hybridteachvc_bbb', ['htsession' => $htsession]);
    }

    /**
     * Populates a stdClass object with Zoom meeting details from a given response object. 
     *
     * @param object $module The module object containing the Zoom meeting.
     * @param object $response The response object from a Zoom API call.
     * @return object The populated stdClass object with Zoom meeting details.
     */
    function populate_htbbb_from_response($module, $response) {

        //not can populate yet
        
    }

        /**
     * Loads a BBB instance based on the given instance ID.
     *
     * @param int $instanceid The ID of the instance to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The Zoom instance record on success, or false on failure.
     */
    public function load_bbb_instance($instanceid) {
        global $DB;

        $sql = "SELECT bi.serverurl, bi.sharedsecret, bi.pollinterval
                  FROM {hybridteaching_instances} hi
                  JOIN {hybridteachvc_bbb_instance} bi ON bi.id = hi.subplugininstanceid
                 WHERE hi.id = :instanceid AND hi.visible = 1";

        $instance = $DB->get_record_sql($sql, ['instanceid' => $instanceid]);
        return $instance;
    }

    function get_zone_access() {
        //ESTA FUNCION NO NECESITA NINGÚN $hybridteachingid
        //PORQUE YA ESTÁ INICIALIZADA EN EL CONSTRUCTOR CON SU SESSION, 
        //NO ES NECESARIO NINGÚN id DE ACTIVIDAD
        
        //la info ya está cargada del constructor


        //aquÍ solo calcular los datos necesarios de la zona de acceso
        //comprobar si el rol es para iniciar reunión o para entrar a reunión
        //y mandamos la url de acceso (o bien starturl o bien joinurl)
        //starturl o join url, según sea hospedador o participante


        //bbbproxy::require_working_server($vc);        
    
        if ($this->bbbsession) {
            $array = [
                'id' => $this->bbbsession->id,
                'ishost' => true,
                'isaccess' => true,
                'url' => base64_encode($this->bbbsession->starturl),
            ];
            return $array;
        } else {
            return null;
        }
    }
}
