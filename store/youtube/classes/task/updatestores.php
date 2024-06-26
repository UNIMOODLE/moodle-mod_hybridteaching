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
 * @package    hybridteachstore_youtube
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachstore_youtube\task;

use hybridteachstore_youtube\youtube_handler;

/**
 * Class updatestores.
 */
class updatestores extends \core\task\scheduled_task {

    /**
     * Returns name of task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('updatestores', 'hybridteachstore_youtube');
    }

    /**
     * Execute the function to obtain sessions from which to download recordings with YouTube storage method,
     * upload the recordings to YouTube, save the records as already uploaded, and delete the processed files.
     */
    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/mod/hybridteaching/store/youtube/classes/youtube_handler.php');
        // Sessions to get
        // 1. Obtain the sessions from which to download recordings, which have the YouTube storage method.
        $sql = "SELECT concat(ht.id,'-',hs.id) AS id, ht.id AS htid, ht.course, hs.id AS hsid, hs.name, hs.userecordvc,";
        $sql .= " hs.processedrecording, hs.storagereference, hc.subpluginconfigid";
        $sql .= " FROM {hybridteaching_session} hs";
        $sql .= " INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid";
        $sql .= " INNER JOIN {hybridteaching_configs} hc ON hs.storagereference=hc.id";
        $sql .= " WHERE hs.userecordvc=1 AND hc.type='youtube' AND hs.processedrecording=0";

        $storesyoutube = $DB->get_records_sql($sql);

        // 3. Upload to youtube.
        // 4. Save the record as already uploaded to youtube.
        // 5. delete the file (already processed) from moodledata.

        foreach ($storesyoutube as $store) {

            // Read the store instance config.
            $configyt = $DB->get_record('hybridteachstore_youtube_con', ['id' => $store->subpluginconfigid]);

            $path = $CFG->dataroot.'/repository/hybridteaching/'.$store->course.'/';

            // Check type mp4 or MP4.
            // Videopath is the path from moodledata.
            $videopath = $path.$store->htid.'-'.$store->hsid.'-1.mp4';
            $exist = false;
            if (!file_exists($videopath)) {
                $videopath = $path.$store->htid.'-'.$store->hsid.'-1.MP4';
                if (file_exists($videopath)) {
                    $exist = true;
                }
            } else {
                $exist = true;
            }

            if ($exist) {
                // Youtube connect.
                $youtubeclient = new youtube_handler($configyt);
                $redirect = $CFG->wwwroot . '/admin/cli/scheduled_task.php';
                $youtubeclient->setredirecturi($redirect);

                // Upload file.
                $youtubecode = $youtubeclient->uploadfile ($store, $videopath);

                // If there has been a correct upload and we have the YouTube code.
                if ($youtubecode != null) {
                    $youtube = new  \stdClass();
                    $youtube->sessionid = $store->hsid;
                    $youtube->code = $youtubecode;
                    $youtube->visible = true;
                    $youtube->timecreated = time();

                    $youtubeid = $DB->insert_record('hybridteachstore_youtube', $youtube);
                    // Update _session, with the youtube id.
                    $storesession = $DB->get_record('hybridteaching_session', ['id' => $store->hsid]);
                    $storesession->processedrecording = $youtubeid;
                    $DB->update_record('hybridteaching_session', $storesession);

                    // Delete video from origin download vc moodledata.
                    unlink ($videopath);
                }
            };
        }
    }
}
