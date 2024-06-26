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
 * @author     UNIMOODLE Group (Coordinator] <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_bbb\task;

use hybridteachvc_bbb\sessions;
use hybridteachvc_bbb\meeting;

/**
 * Class downloadrecords.
 */
class downloadrecords extends \core\task\scheduled_task {
    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('downloadrecordsbbb', 'hybridteachvc_bbb');
    }

    /**
     * Executes the function to process recording for hybrid teaching sessions in BigBlueButton.
     */
    public function execute() {
        global $DB, $CFG;

        $enabledrecording = get_config('hybridteachvc_bbb', 'enabledrecording');
        if (!$enabledrecording) {
            mtrace(get_string('recordingdisabled', 'hybridteaching'));
            return;
        }

        $sessionconfig = new sessions();

        $sql = "SELECT hs.id AS hsid, ht.id AS htid, ht.course, ht.config, hs.name, bbb.meetingid
                  FROM {hybridteaching_session} hs
            INNER JOIN {hybridteachvc_bbb} bbb ON bbb.htsession = hs.id
            INNER JOIN {hybridteaching} ht ON ht.id = hs.hybridteachingid
                 WHERE hs.typevc = 'bbb' AND hs.userecordvc = 1 AND hs.processedrecording = -1";
        $download = $DB->get_records_sql($sql);

        foreach ($download as $session) {
            $folder = $CFG->dataroot."/repository/hybridteaching/".$session->course;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $bbbconfig = $sessionconfig->load_bbb_config($session->config);
            if ($bbbconfig == false) {
                continue;
            }
            $meeting = new meeting($bbbconfig);
            $response = $meeting->get_meeting_recordings($session->meetingid);

            if (isset($response['returncode'])) {

                if ($response['returncode'] == 'meetingrunning') {
                    // If is meeting running, still cannot get recordingid.
                    mtrace(get_string('meetingrunning', 'hybridteachvc_bbb', $session->meetingid));

                } else if ($response['returncode'] == 'SUCCESS') {
                    $sessionresult = $DB->get_record('hybridteaching_session', ['id' => $session->hsid]);
                    if ($response['recordingid'] != '') {
                        $bbb = $DB->get_record('hybridteachvc_bbb',
                            ['meetingid' => $session->meetingid, 'htsession' => $session->hsid]);
                        $bbb->recordingid = $response['recordingid'];
                        $DB->update_record('hybridteachvc_bbb', $bbb);
                        // Save processedrecording in hybridteaching_session=0: ready to upload to store.
                        $sessionresult->processedrecording = 0;
                        $sessionresult->storagereference = -1;
                        $DB->update_record('hybridteaching_session', $sessionresult);
                        mtrace(get_string('correctgetrecording', 'hybridteachvc_bbb',
                            ['name' => $session->name, 'sessionid' => $session->hsid, 'course' => $session->course]));
                    } else {
                        // If there are not recordingid, then the recording is not found or there are no recording.
                        $sessionresult->processedrecording = -2;
                        $sessionresult->storagereference = -1;
                        $DB->update_record('hybridteaching_session', $sessionresult);
                        mtrace(get_string('recordingnotfound', 'hybridteachvc_bbb',
                            ['course' => $session->course, 'name' => $session->name]));
                    }
                } else {
                    mtrace(get_string('recordingnotfound', 'hybridteachvc_bbb',
                        ['course' => $session->course, 'name' => $session->name]));
                }
            } else {
                mtrace(get_string('unknownerror', 'hybridteachvc_bbb', $session->meetingid));
            }
        }
    }
}
