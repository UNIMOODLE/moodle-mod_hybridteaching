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
 * @package    hybridteachvc_zoom
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachvc_zoom\task;

use hybridteachvc_zoom\sessions;
use hybridteachvc_zoom\webservice;

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
        return get_string('downloadrecordszoom', 'hybridteachvc_zoom');
    }

    /**
     * Executes the function to download and process Zoom recordings for hybrid teaching sessions.
     */
    public function execute() {
        global $DB, $CFG;

        $enabledrecording = get_config('hybridteachvc_zoom', 'enabledrecording');
        if (!$enabledrecording) {
            mtrace(get_string('recordingdisabled', 'hybridteaching'));
            return;
        }

        $sessionconfig = new sessions();

        $sql = "SELECT hs.id AS hsid,
                       ht.id AS htid,
                       ht.course,
                       ht.config,
                       hs.starttime,
                       hs.name,
                       zoom.id AS zoomid,
                       zoom.meetingid,
                       zoom.downloadattempts
                  FROM {hybridteaching_session} hs
            INNER JOIN {hybridteachvc_zoom} zoom ON zoom.htsession = hs.id
            INNER JOIN {hybridteaching} ht ON ht.id = hs.hybridteachingid
                 WHERE hs.typevc = 'zoom'
                   AND hs.userecordvc = 1
                   AND hs.processedrecording = -1";
        $download = $DB->get_records_sql($sql);

        foreach ($download as $session) {
            $folder = $CFG->dataroot."/repository/hybridteaching/".$session->course;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $folderfile = $folder."/".$session->htid."-".$session->hsid;

            // Add attempt to download recording.
            $zoom = $DB->get_record ('hybridteachvc_zoom', ['id' => $session->zoomid]);
            $zoom->downloadattempts ++;
            $DB->update_record('hybridteachvc_zoom', $zoom);

            // Calculate maxdownloadattempts.
            $maxdownloadattempts = get_config('hybridteaching', 'downloadattempts');
            if (!is_numeric($maxdownloadattempts) || $maxdownloadattempts <= 0) {
                $maxdownloadattempts = 5;
            }
            if ($session->downloadattempts > $maxdownloadattempts) {
                // Save -2 indicates there are not recording, or there are maxdownloadattempts.
                $sessionupdate = $DB->get_record('hybridteaching_session', ['id' => $session->hsid]);
                $sessionupdate->processedrecording = -2;
                $DB->update_record('hybridteaching_session', $sessionupdate);
            }

            // Connect to API to download recording.
            $zoomconfig = $sessionconfig->load_zoom_config($session->config);
            if ($zoomconfig == false) {
                mtrace(get_string('confignotfound', 'hybridteachvc_zoom',
                    ['config' => $session->config, 'name' => $session->name]));
                continue;
            }
            $service = new webservice($zoomconfig);
            $response = null;
            try {
                $response = $service->get_past_meetings_uuid($session->meetingid);
            } catch (\Exception $e) {
                $response = false;
                mtrace(get_string('errorgetmeeting', 'hybridteachvc_zoom', $session->name));
            }
            if ($response->meetings) {
                foreach ($response->meetings as $meet) {
                    if (!$DB->record_exists('hybridteachvc_zoom_records',
                        ['uuid' => $meet->uuid, 'meetingid' => $session->meetingid])) {
                        // Download recording from uuid.
                        $responserecording = null;
                        try {
                            $responserecording = $service->get_meeting_recordings($meet->uuid);
                        } catch (\Exception $e) {
                            mtrace(get_string('errorgetmeeting', 'hybridteachvc_zoom', $session->name));
                        }
                        $isdownloaded = self::downloadfiles ($responserecording, $folderfile, $service, $session);
                        if ($isdownloaded) {
                            $details = new \stdClass();
                            $details->htsession = $session->hsid;
                            $details->meetingid = $session->meetingid;
                            $details->uuid = $meet->uuid;
                            $DB->insert_record('hybridteachvc_zoom_records', $details);
                            mtrace(get_string('recordingdownloaded', 'hybridteachvc_zoom',
                                ['course' => $session->course, 'name' => $session->name]));
                        }
                    } else {
                        mtrace(get_string('alreadydownloaded', 'hybridteachvc_zoom',
                            ['course' => $session->course, 'name' => $session->name]));
                    }
                }
            } else {
                mtrace(get_string('meetingnotfound', 'hybridteachvc_zoom',
                    ['course' => $session->course, 'name' => $session->name]));
            }
        }
    }

    /**
     * Download files from the response and save them in the specified folder.
     *
     * @param mixed $response The response object containing recording files
     * @param string $folderfile The folder where the files will be saved
     * @param mixed $service The service object used for making calls
     * @param object $session The session object for the recording
     * @return bool Whether any files were downloaded
     */
    public static function downloadfiles($response, $folderfile, $service, $session) {
        global $DB;

        $isdownloaded = false;
        if (isset($response->recording_files) && count($response->recording_files) <= 0) {
            mtrace(get_string('recordingnotfound', 'hybridteachvc_zoom', ['course' => $session->course, 'name' => $session->name]));
            return $isdownloaded;
        }

        foreach ($response->recording_files as $file) {
            if ((strtolower($file->file_type) == 'mp4')) {
                $folderfilerecording = $folderfile.'-1.'.strtolower($file->file_type);
                $filesize = @filesize($folderfile);
                if ((!file_exists($folderfilerecording)) || (file_exists($folderfilerecording) &&
                    ($file->file_size != $filesize))) {
                    try {
                        $responsefile = $service->_make_call_download($file->download_url);
                    } catch (\Exception $e) {
                        mtrace($e->getMessage());
                    }
                    if ($responsefile) {
                        $filerecording = fopen($folderfilerecording, "w+");
                        fputs($filerecording, (string)$responsefile);
                        fclose($filerecording);

                        // Save processedrecording in hybridteaching_session=0: ready to upload to store.
                        $sessionprocessed = $DB->get_record('hybridteaching_session', ['id' => $session->hsid]);
                        $sessionprocessed->processedrecording = 0;
                        $DB->update_record('hybridteaching_session', $sessionprocessed);
                        $isdownloaded = true;
                    } else {
                        mtrace(get_string('recordingnotdownload', 'hybridteachvc_zoom',
                            ['course' => $session->course, 'name' => $session->name]));
                    }
                }
            }
            if (strtolower($file->file_type) == "chat") {
                $responsechat = $service->_make_call_download($file->download_url);
                $suffix = '-chat.txt';
                $folderfilechat = $folderfile.$suffix;
                $filechat = fopen($folderfilechat, "w+");
                fputs($filechat, (string) $responsechat);
                fclose($filechat);

                // Get instance context.
                $cm = get_coursemodule_from_instance('hybridteaching', $session->htid);
                $context = \context_module::instance($cm->id);

                // Save .txt in session filestorage.
                $filename = get_string('chatnamefile', 'hybridteachvc_zoom').' '.$session->name;
                $fileinfo = [
                    'contextid' => $context->id,
                    'component' => 'mod_hybridteaching',
                    'filearea'  => 'chats',
                    'itemid'    => $session->hsid,
                    'filepath'  => '/',
                    'filename'  => $filename.'.txt',
                ];

                $fs = get_file_storage();

                // Checking name.
                $files = $fs->get_area_files($context->id, 'mod_hybridteaching', 'chats', $session->hsid);
                foreach ($files as $file) {
                    if ($file->get_filename() == $fileinfo['filename']) {
                        // Change name chat.
                        $fileinfo['filename'] = $filename.' ('.count($files).').txt';
                    }
                }
                $fs->create_file_from_pathname($fileinfo, $folderfilechat);

                // Delete chat file from origin download vc moodledata.
                unlink ($folderfilechat);
            }
        }
        return $isdownloaded;
    }
}
