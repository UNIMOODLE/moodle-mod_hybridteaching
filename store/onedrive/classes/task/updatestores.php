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
 * @package    hybridteachstore_onedrive
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace hybridteachstore_onedrive\task;

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
        return get_string('updatestores', 'hybridteachstore_onedrive');
    }


    /**
     * Get onedrive recordings.
     *
     */
    public function execute() {
        global $DB, $CFG;

        // Sessions to get recordings, with onedrive store method.
        $sql = "SELECT concat(ht.id,'-',hs.id) AS id, ht.id AS htid, ht.course, c.shortname, hs.id AS hsid,
                            hs.name, hs.userecordvc, hs.processedrecording, hs.storagereference, hc.subpluginconfigid
                  FROM {hybridteaching_session} hs
            INNER JOIN {hybridteaching} ht ON ht.id=hs.hybridteachingid
            INNER JOIN {hybridteaching_configs} hc ON hs.storagereference=hc.id
            INNER JOIN {course} c ON c.id=ht.course
                 WHERE hs.userecordvc=1 AND hc.type='onedrive' AND hs.processedrecording=0";

        $storeonedrive = $DB->get_records_sql($sql);

        // Process:
        // 1. upload to onedrive
        // 2. save record as upload to onedrive
        // 3. delete file from moodledata.

        foreach ($storeonedrive as $store) {

            // Read store instance.
            $configonedrive = $DB->get_record('hybridteachstore_onedrive_co', ['id' => $store->subpluginconfigid]);

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
                // Onedrive connect.
                $onedriveclient = new \hybridteachstore_onedrive\onedrive_handler($configonedrive);

                // Upload file.
                $response = $onedriveclient->uploadfile($store, $videopath);

                // If there are correct response.
                if ($response != null) {
                    $onedrive = new  \stdClass();
                    $onedrive->sessionid = $store->hsid;
                    $onedrive->weburl = $store->shortname.'/'.$store->name.'.mp4';
                    $onedrive->downloadurl = $response['downloadurl'];
                    $onedrive->visible = true;
                    $onedrive->timecreated = time();

                    $onedriveid = $DB->insert_record('hybridteachstore_onedrive', $onedrive);
                    // Update _session with onedrive id.
                    $storesession = $DB->get_record('hybridteaching_session', ['id' => $store->hsid]);
                    $storesession->processedrecording = $onedriveid;    // 1;
                    $DB->update_record('hybridteaching_session', $storesession);

                    // Delete video from origin download vc moodledata.
                    unlink ($videopath);
                } else {
                    mtrace(get_string('notuploading', 'hybridteachstore_onedrive').' '.$store->course.": '".$store->name."': ");
                }
            }
        }
    }
}
