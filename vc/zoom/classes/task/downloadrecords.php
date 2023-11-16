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

namespace hybridteachvc_zoom\task;

use hybridteachvc_zoom\sessions;
use hybridteachvc_zoom\webservice;

class downloadrecords extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('downloadrecordszoom', 'hybridteachvc_zoom');
    }

    public function execute() {
        global $DB, $CFG;

        $sessionconfig = new sessions();

        $sql = 'SELECT hs.id AS hsid,
                       ht.id AS htid,
                       ht.course,
                       ht.config,
                       hs.starttime,
                       zoom.meetingid
                  FROM {hybridteaching_session} hs
            INNER JOIN {hybridteachvc_zoom} zoom ON zoom.htsession = hs.id
            INNER JOIN {hybridteaching} ht ON ht.id = hs.hybridteachingid
                 WHERE hs.typevc = "zoom"
                   AND hs.userecordvc = 1
                   AND hs.processedrecording =- 1';
        $download = $DB->get_records_sql($sql);

        foreach ($download as $session) {
            $folder = $CFG->dataroot."/repository/hybridteaching/".$session->course;
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $folderfile = $folder."/".$session->htid."-".$session->hsid;

            $zoomconfig = $sessionconfig->load_zoom_config($session->config);
            $service = new webservice($zoomconfig);
            $response=null;
            try {
                $response = $service->get_meeting_recordings($session->meetingid);
            } catch (\Exception $e) {

                // Si la sesión es del día anterior y ha fallado, no hay grabación.
                /* REVISAR ESTA PARTE PARA GUARDAR EL -2, PARA DIFERENCIAR CUANDO LA
                 -1: dejar el -1 cuando LA SESION NO ESTÉ PROCESADA POR ZOOM AUN PARA DESCARGAR
                 -2:   O CUANDO ESTÉ PROCESADA Y NO HAYA GRABACIÓN.*/
                if ($session->starttime < (time()+90000)) {
                    // Save -2 indicates there are not recording.
                    $session = $DB->get_record('hybridteaching_session', ['id' => $session->hsid]);
                    $session->processedrecording = -2;
                    $DB->update_record('hybridteaching_session', $session);

                    $response = false;
                }
            }

            if ($response != false) {
                $count = 1;
                foreach ($response->recording_files as $file) {
                    if (($file->file_type == 'MP4')) {
                        $folderfile = $folderfile."-".$count.'.'.$file->file_type;
                        $filesize = @filesize($folderfile);
                        if ((!file_exists($folderfile)) || (file_exists($folderfile) && ($file->filesize != $filesize))) {
                            $response = $service->_make_call_download($file->download_url);

                            $file = fopen($folderfile, "w+");
                            fputs($file, $response);
                            fclose($file);

                            // Save processedrecording in hybridteaching_session=0: ready to upload to store.
                            $session = $DB->get_record('hybridteaching_session', ['id' => $session->hsid]);
                            $session->processedrecording = 0;
                            $DB->update_record('hybridteaching_session', $session);
                        }
                    }
                }
            }
        }
    }
}
