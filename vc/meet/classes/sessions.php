<?php

//use mod_isycgooglemeetav\webservice;

//use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

namespace hybridteachvc_meet;

use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;

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
            $client = new \meet_handler($meetconfig);
            $event = $client->create_meeting_event($session);

            if ($event) {
                $meetsession = new stdClass();
                $meetsession->htsession = $session->id;
                $meetsession->url = $event->hangoutLink;
                $meetsession->creatoremail = $event->creator->email;
                $meetsession->eventid = $event->id;
        
                if (!$meetsession->id = $DB->insert_record('hybridteachvc_meet', $meetsession)) {
                    return false;
                }
            }
        }

        return $meetsession->id;
    }
    
    public function update_session_extended($data, $ht) {
        // hacer update de meet
        /*global $DB;
        $dataupdated = $DB->update_record('hybridteachvc_meet', $data);

        helper::create_calendar_event($data);
        $meet = $DB->get_record('hybridteachvc_meet', ['htsession' => $data->id]);

        $event = $service->events->get('primary', 'eventId');

        $event->setSummary('Appointment at Somewhere');

        $updatedEvent = $service->events->update('primary', $event->getId(), $event);

        return $errormsg;*/
    }

    public function delete_session_extended($id) {
        global $DB;

        $exists = $DB->get_record('hybridteachvc_meet', array('id' => $id));
        if (!$exists) {
            return false;
        }

        $DB->delete_records('hybridteachvc_meet', array('id' => $id));

        return true;
    }

    function get_zone_access() {
        if ($this->meetsession) {
            $array = [
                'id' => $this->meetsession->id,
                'ishost' => true,
                'isaccess' => true,
                'url' => base64_encode($this->meetsession->url),
            ];
            return $array;
        } else {
            return null;
        }
    }

}
