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
 * @package    hybridteachvc_bbb
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_bbb;

use mod_bigbluebuttonbn\plugin;
use hybridteachvc_bbb\bbbproxy;

/**
 * Class meeting.
 */
class meeting {
    /** @var object $bbbinstance Api with credentials, url... */
    protected $bbbinstance;

    /**
     * Constructor for the meeting object.
     *
     * @param object $bbbinstance
     */
    public function __construct($bbbinstance) {
        $this->bbbinstance = $bbbinstance;
    }

    /**
     * Create a meeting using the provided session and ht.
     *
     * @param object $session
     * @param object $ht intance
     * @return bbbproxy response
     */
    public function create_meeting ($session, $ht) {
        $bbbproxy = new bbbproxy($this->bbbinstance);
        $bbbproxy->require_working_server();
        $data = $this->create_meeting_data($session, $ht);
        $metadata = $this->create_meeting_metadata($session, $ht);
        $presentations = null;
        if ($ht->sessionscheduling == 1) {
            // Only presentations for sessionscheduling in advanced configurations.
            $presentations = $this->upload_presentation($session, $ht);
        }

        $response = $bbbproxy->create_meeting($data, $metadata, $presentations, $this->bbbinstance);
        return $response;
    }

    /**
     * Upload presentation.
     *
     * @param object $session session instance
     * @param object $ht hybridteaching instance
     * @return array|null the representation of the presentations as array
     */
    public function upload_presentation ($session, $ht) {
        $fs = get_file_storage();
        $cm = get_coursemodule_from_instance('hybridteaching', $ht->id);
        $context = \context_module::instance($cm->id);
        $files = $fs->get_area_files($context->id, 'mod_hybridteaching', 'session', $session->id);
        $presentations = [];

        foreach ($files as $file) {
            if (!empty($file) && $file->get_filename() != '.') {
                $presentations[] = [
                    'name' => $file->get_filename(),
                    'content' => $file->get_content(),
                ];
            }
        }
        if (empty($presentations)) {
            return null;
        }
        return $presentations;
    }

    /**
     * Helper to prepare data used for create meeting.
     * Populate the data from session and ht to BBB meeting.
     * @param object $session session instance
     * @param object $ht hybridteaching instance
     * @return array
     */
    protected function create_meeting_data($session, $ht) {

        $meetingid = self::get_unique_meetingid_seed();

        $url = new \moodle_url('/course/view.php', ['id' => $ht->course]);

        $data = ['meetingID' => $meetingid,
                'name' => \mod_bigbluebuttonbn\plugin::html2text($session->name, 64),
                'logoutURL' => (string) $url,
        ];
        if (!is_null($ht->wellcomemessage) && $ht->wellcomemessage != '') {
            $data['welcome'] = $ht->wellcomemessage;
        }

        /*
        Info: initial states added in BBB:
                            $ht->userslimit
                            $ht->disablecam
                            $ht->disablemic
                            $ht->disableprivatechat
                            $ht->disablepublicchat
                            $ht->disablenote
                            $ht->hideuserlist
                            $ht->blockroomdesign
                            $ht->ignorelocksettings
                            $ht->initialrecord
                            $ht->hiderecordbutton
        */

        // Get instance context.
        $cm = get_coursemodule_from_instance ('hybridteaching', $ht->id);
        $context = \context_module::instance($cm->id);

        $enabledrecording = get_config('hybridteachvc_bbb', 'enabledrecording');
        // Initially, recording is false. If has permissions, change to true.
        $data['record'] = false;
        $data['allowStartStopRecording'] = false;
        if (has_capability('hybridteachvc/bbb:record', $context) &&
            has_capability('mod/hybridteaching:record', $context) && $enabledrecording) {
            $data['record'] = $ht->userecordvc ? 'true' : 'false';
            if ($data['record'] == 'true' ) {
                if ($ht->initialrecord) {
                    $data['autoStartRecording'] = 'true';
                }
                $data['allowStartStopRecording'] = $ht->hiderecordbutton ? 'false' : 'true';
            }
        }

        $data['duration'] = $session->duration;
        $data['muteOnStart'] = 'true';
        if ($ht->userslimit > 0) {
            $data['maxParticipants'] = $ht->userslimit;
        }

        $data['lockSettingsDisableCam'] = $ht->disablecam ? 'true' : 'false';
        $data['lockSettingsDisableMic'] = $ht->disablemic ? 'true' : 'false';
        $data['lockSettingsDisablePrivateChat'] = $ht->disableprivatechat ? 'true' : 'false';
        $data['lockSettingsDisablePublicChat'] = $ht->disablepublicchat ? 'true' : 'false';
        $data['lockSettingsDisableNotes'] = $ht->disablenote ? 'true' : 'false';
        $data['lockSettingsHideUserList'] = $ht->hideuserlist ? 'true' : 'false';
        $data['lockSettingsLockOnJoin'] = $ht->ignorelocksettings ? 'false' : 'true';
        if ($ht->blockroomdesign) {
            $data['disabledFeatures'] = 'layouts';
        }
        if ($ht->reusesession) {
            $data['meetingExpireWhenLastUserLeftInMinutes '] = 0;
        }

        return $data;
    }

    /**
     * Create meeting metadata.
     *
     * @param object $session
     * @param object $ht instance
     * @return array
     */
    protected function create_meeting_metadata ($session, $ht) {
        // Create standard metadata.
        $origindata = $this->get_origin_data();
        $metadata = [
            'bbb-origin' => $origindata->origin,
            'bbb-origin-version' => $origindata->originVersion,
            'bbb-origin-server-name' => $origindata->originServerName,
            'bbb-origin-server-common-name' => $origindata->originServerCommonName,
            'bbb-origin-tag' => $origindata->originTag,
            'bbb-context' => $ht->name,
            'bbb-context-id' => $ht->id,
            'bbb-context-name' => trim(html_to_text($ht->name, 0)),
            'bbb-context-label' => trim(html_to_text($ht->name, 0)),
            'bbb-recording-name' => plugin::html2text($ht->name, 64),
            'bbb-recording-description' => plugin::html2text($session->description, 64),
        ];

        return $metadata;
    }

    /**
     * Ends a meeting using the provided meeting ID.
     *
     * @param int $meetingid meet id
     */
    public function end_meeting($meetingid) {
        $bbbproxy = new bbbproxy($this->bbbinstance);
        $bbbproxy->end_meeting($meetingid);
    }

    /**
     * Retrieves the meeting recordings for the given meeting ID.
     *
     * @param int $meetingid The ID of the meeting
     * @return array
     */
    public function get_meeting_recordings($meetingid) {
        $bbbproxy = new bbbproxy($this->bbbinstance);
        return $bbbproxy->get_meeting_recording($meetingid);
    }

    /**
     * Helper function returns a sha1 encoded string that is unique and will be used as a seed for meetingid.
     *
     * @return string
     */
    public static function get_unique_meetingid_seed() {
        global $DB;
        do {
            $encodedseed = sha1(plugin::random_password(12));
            $meetingid = (string) $DB->get_field('hybridteachvc_bbb', 'meetingid', ['meetingid' => $encodedseed]);
        } while ($meetingid == $encodedseed);
        return $encodedseed;
    }

    /**
     * Get information about the origin.
     *
     * @return object
     */
    public function get_origin_data() {
        global $CFG;

        $parsedurl = parse_url($CFG->wwwroot);
        return (object) [
            'origin' => 'Moodle',
            'originVersion' => $CFG->release,
            'originServerName' => $parsedurl['host'],
            'originServerUrl' => $CFG->wwwroot,
            'originServerCommonName' => '',
            'originTag' => sprintf('moodle-mod_bigbluebuttonbn (%s)', get_config('hybridteachvc_bbb', 'version')),
        ];
    }
}
