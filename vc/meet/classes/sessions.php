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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace hybridteachvc_meet;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once('meet_handler.php');

class sessions {
    protected $meetsession;

    public function __construct($htsessionid = null) {
        if (!empty($htsessionid)) {
            $this->meetsession = $this->load_session($htsessionid);
        }
    }

    public function load_session($htsessionid) {
        global $DB;
        $this->meetsession = $DB->get_record('hybridteachvc_meet', ['htsession' => $htsessionid]);
        return $this->meetsession;
    }

    public function get_session() {
        return $this->meetsession;
    }

    public function set_session($htsessionid) {
        $this->meetsession = $this->load_session($htsessionid);
    }

    public function create_unique_session_extended($session, $ht) {
        global $DB;

        $existsmeet = $DB->get_field('hybridteachvc_meet', 'id', ['htsession' => $session->id]);
        if (!$existsmeet) {
            $subpluginconfigid = $DB->get_field('hybridteaching_configs', 'subpluginconfigid', ['id' => $ht->config]);
            $meetconfig = $DB->get_record('hybridteachvc_meet_config', ['id' => $subpluginconfigid]);
            $client = new meet_handler($meetconfig);
            $event = $client->create_meeting_event($session);

            if ($event) {
                $meetsession = new stdClass();
                $meetsession->htsession = $session->id;
                $meetsession->joinurl = $event->hangoutLink;
                $meetsession->creatoremail = $event->creator->email;
                $meetsession->eventid = $event->id;

                if (!$meetsession->id = $DB->insert_record('hybridteachvc_meet', $meetsession)) {
                    return false;
                }
            }
        }

        return $meetsession->id;
    }

    public function update_session_extended($session, $ht) {
        global $DB;

        /*$existsmeet = $DB->get_field('hybridteachvc_meet', 'id', ['htsession' => $session->id]);
        if ($existsmeet) {
            $subpluginconfigid = $DB->get_field('hybridteaching_configs', 'subpluginconfigid', ['id' => $ht->config]);
            $meetconfig = $DB->get_record('hybridteachvc_meet_config', ['id' => $subpluginconfigid]);
            $client = new meet_handler($meetconfig);
            $client->update_meeting_event($session, $existsmeet);
        }*/
    }

    public function delete_session_extended($htsession, $configid) {
        global $DB;
        $meetconfig = $this->load_meet_config($configid);
        if (!empty($meetconfig)) {
            $meet = $DB->get_record('hybridteachvc_meet', ['htsession' => $htsession]);
            if (isset($meet->eventid)) {
                try {
                    $meethandler = new meet_handler($meetconfig);
                    $meethandler->deletemeeting($meet->eventid);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            }
        }
        $DB->delete_records('hybridteachvc_meet', ['htsession' => $htsession]);
    }

    /**
     * Loads a Meet config based on the given config ID.
     *
     * @param int $configid The ID of the config to load.
     * @throws Exception If the SQL query fails.
     * @return stdClass|false The Meet config record on success, or false on failure.
     */
    public function load_meet_config($configid) {
        global $DB;

        $sql = 'SELECT mc.*
                  FROM {hybridteaching_configs} hc
                  JOIN {hybridteachvc_meet_config} mc ON mc.id = hc.subpluginconfigid
                 WHERE hc.id = :configid AND hc.visible = 1';

        $config = $DB->get_record_sql($sql, ['configid' => $configid]);
        return $config;
    }


    public function get_zone_access() {
        if ($this->meetsession) {
            $array = [
                'id' => $this->meetsession->id,
                'ishost' => true,
                'isaccess' => true,
                'url' => base64_encode($this->meetsession->joinurl),
            ];
            return $array;
        } else {
            return null;
        }
    }

}
