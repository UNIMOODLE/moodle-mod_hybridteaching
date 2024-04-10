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
 * @package    hybridteachvc_meet
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_meet\task;

use hybridteachvc_meet\sessions;
use hybridteachvc_meet\meet_handler;

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
        return get_string('downloadrecordsmeet', 'hybridteachvc_meet');
    }

    /**
     * Executes the function to process and save recordings and chats from the hybridteaching_session table.
     */
    public function execute() {

        global $DB, $CFG;

        $enabledrecording = get_config('hybridteachvc_meet', 'enabledrecording');
        if (!$enabledrecording) {
            mtrace(get_string('recordingdisabled', 'hybridteaching'));
            return;
        }

        $sessionconfig = new sessions();

        $sql = "SELECT hs.id AS hsid, hs.name, ht.id AS htid, ht.course, ht.config, meet.joinurl
                  FROM {hybridteaching_session} hs
            INNER JOIN {hybridteachvc_meet} meet ON meet.htsession = hs.id
            INNER JOIN {hybridteaching} ht ON ht.id = hs.hybridteachingid
                 WHERE hs.typevc = 'meet' AND hs.userecordvc = 1 AND hs.processedrecording = -1";
        $download = $DB->get_records_sql($sql);

        foreach ($download as $session) {
            $folder = $CFG->dataroot."/repository/hybridteaching/".$session->course;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $meetconfig = $sessionconfig->load_meet_config($session->config);
            if ($meetconfig == false) {
                continue;
            }
            $meethandler = new meet_handler($meetconfig);

            $searchrecordings = $meethandler->search_recordings($session);
            $filenum = 1;
            foreach ($searchrecordings as $files) {
                foreach ($files as $file) {
                    $folderfile = $folder."/".$session->htid."-".$session->hsid.'-'.$filenum.'.mp4';
                    $content = $meethandler->download_file($file->id);
                    $sess = $DB->get_record('hybridteaching_session', ['id' => $session->hsid]);
                    if (!empty($content)) {
                        $sess->processedrecording = 0;
                        if (file_put_contents($folderfile, $content) !== false) {
                            $meethandler->delete_file($file->id);
                        }
                    } else {
                        // Save -2 indicates there are not recording.
                        $sess->processedrecording = -2;
                    }
                    $DB->update_record('hybridteaching_session', $sess);
                    $filenum++;
                }
            }

            $searchchats = $meethandler->search_chats($session);
            $chatnum = 1;
            foreach ($searchchats as $chats) {
                foreach ($chats as $chat) {
                    $folderfile = $folder."/".$session->htid."-".$session->hsid.'-'.$chatnum.'-chat.txt';
                    $content = $meethandler->download_file($chat->id);
                    if (!empty($content)) {
                        if (file_put_contents($folderfile, $content) !== false) {
                            $meethandler->delete_file($chat->id);
                        }

                        // Get instance context.
                        $cm = get_coursemodule_from_instance('hybridteaching', $session->htid);
                        $context = \context_module::instance($cm->id);

                        // Save .txt in session filestorage.
                        $filename = get_string('chatnamefile', 'hybridteachvc_meet').'_'.$session->name.$chatnum;
                        $fileinfo = [
                            'contextid' => $context->id,
                            'component' => 'mod_hybridteaching',
                            'filearea'  => 'chats',
                            'itemid'    => $session->hsid,
                            'filepath'  => '/',
                            'filename'  => $filename.'.txt',
                        ];

                        $fs = get_file_storage();
                        $fs->create_file_from_pathname($fileinfo, $folderfile);

                        // Delete chat file from origin download vc moodledata.
                        unlink($folderfile);
                    }
                    $chatnum++;
                }
            }
        }
    }
}
