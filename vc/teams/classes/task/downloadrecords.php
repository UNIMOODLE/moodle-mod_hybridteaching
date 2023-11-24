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

namespace hybridteachvc_teams\task;

use hybridteachvc_teams\sessions;
use hybridteachvc_teams\teams_handler;

class downloadrecords extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('downloadrecordsteams', 'hybridteachvc_teams');
    }

    public function execute() {

        global $DB, $CFG;

        $sessionconfig = new sessions();

        $sql = 'SELECT hs.id AS hsid, ht.id AS htid, ht.course, ht.config, teams.meetingid, teams.organizer
            FROM {hybridteaching_session} hs
            INNER JOIN {hybridteachvc_teams} teams ON teams.htsession=hs.id
            INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
            WHERE hs.typevc="teams" AND hs.userecordvc=1 AND hs.processedrecording=-1';
        $download = $DB->get_records_sql($sql);

        foreach ($download as $session) {
            $folder = $CFG->dataroot."/repository/hybridteaching/".$session->course;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $folderfile = $folder."/".$session->htid."-".$session->hsid.'-1.mp4';

            $teamsconfig = $sessionconfig->load_teams_config($session->config);
            $teamshandler = new teams_handler($teamsconfig);

            // Call to download recordings.
            $response = $teamshandler->get_meeting_recordings($folderfile, $session->meetingid, $session->organizer);

            // Save recordingid and prepare to upload.
            $sess = $DB->get_record('hybridteaching_session', ['id' => $session->hsid]);
            if ($response != false) {
                $teams = $DB->get_record('hybridteachvc_teams', ['meetingid' => $session->meetingid] );
                $teams->recordingid = $response;
                $DB->update_record('hybridteachvc_teams', $teams);
                $sess->processedrecording = 0;
            } else {
                // Save -2 indicates there are not recording.
                $sess->processedrecording = -2;
            }
            $DB->update_record('hybridteaching_session', $sess);
        }
    }
}
