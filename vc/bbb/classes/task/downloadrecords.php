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

namespace hybridteachvc_bbb\task;

use hybridteachvc_bbb\sessions;
use hybridteachvc_bbb\meeting;



class downloadrecords extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('downloadrecordsbbb', 'hybridteachvc_bbb');
    }

    public function execute() {
        global $DB, $CFG;

        $sessionconfig = new sessions();

        $sql = 'SELECT hs.id AS hsid, ht.id AS htid, ht.course, ht.config, bbb.meetingid
            FROM {hybridteaching_session} hs
            INNER JOIN {hybridteachvc_bbb} bbb ON bbb.htsession=hs.id
            INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
            WHERE hs.typevc="bbb" AND hs.userecordvc=1 AND hs.processedrecording=-1';
        $download = $DB->get_records_sql($sql);

        foreach ($download as $session) {
            $folder = $CFG->dataroot."/repository/hybridteaching/".$session->course;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            // $folder_file=$folder."/".$session->htid."-".$session->hsid.'.mp4';

            $bbbconfig = $sessionconfig->load_bbb_config($session->config);
            $meeting = new meeting($bbbconfig);
            $response = $meeting->get_meeting_recordings($session->meetingid);

            if ($response['returncode'] == 'SUCCESS') {
                if ($response['recordingid'] != '') {
                    $bbb = $DB->get_record('hybridteachvc_bbb', ['meetingid' => $session->meetingid] );
                    // $bbb->recordingurl = $response['recordingurl'];
                    $bbb->recordingid = $response['recordingid'];
                    $DB->update_record('hybridteachvc_bbb', $bbb);
                    // Save processedrecording in hybridteaching_session=0: ready to upload to store.
                    $session = $DB->get_record('hybridteaching_session', ['id' => $session->hsid]);
                    $session->processedrecording = 0;
                    $session->storagereference = -1;
                    $DB->update_record('hybridteaching_session', $session);
                }

            }
        }
    }
}
