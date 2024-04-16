<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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

/**
 * Class restore_hybridteaching_activity_structure_step
 *
 * This class is for restoring the structure of a hybrid teaching activity.
 */
class restore_hybridteaching_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure for the restore.
     *
     * @return datatype
     */
    protected function define_structure() {
        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('hybridteaching', '/activity/hybridteaching');
        $session = new restore_path_element('hybridteaching_session', '/activity/hybridteaching/sessions/session');
        $paths[] = $session;
        $paths[] = new restore_path_element('hybridteaching_attendance',
            '/activity/hybridteaching/sessions/session/attendances/attendance');
        $paths[] = new restore_path_element('hybridteaching_attend_log',
            '/activity/hybridteaching/sessions/session/attendances/attendance/logs/log');

        // Support 2 types of subplugins.
        $this->add_subplugin_structure('hybridteachvc', $session);
        //$this->add_subplugin_structure('hybridteachstore', $session);

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }


    /**
     * Process the hybridteaching data and insert a new record.
     *
     * @param object $data The data to be processed
     */
    protected function process_hybridteaching($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        if (!isset($data->intro)) {
            $data->intro = '';
            $data->introformat = FORMAT_HTML;
        }
        // Insert the hybridteaching record.
        $newitemid = $DB->insert_record('hybridteaching', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process hybrid teaching session.
     *
     * @param mixed $data
     */
    protected function process_hybridteaching_session($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->hybridteachingid = $this->get_new_parentid('hybridteaching');
        $data->storagereference = null;

        $newitemid = $DB->insert_record('hybridteaching_session', $data);
        $this->set_mapping('hybridteaching_session', $oldid, $newitemid);
    }

    /**
     * Process hybrid teaching attendance.
     *
     * @param mixed $data
     */
    protected function process_hybridteaching_attendance($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->hybridteachingid = $this->get_new_parentid('hybridteaching');
        $data->sessionid = $this->get_new_parentid('hybridteaching_session');

        $newitemid = $DB->insert_record('hybridteaching_attendance', $data);
        $this->set_mapping('hybridteaching_attendance', $oldid, $newitemid);
    }

    /**
     * Process the hybridteaching attend log.
     *
     * @param mixed $data
     */
    protected function process_hybridteaching_attend_log($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->attendanceid = $this->get_new_parentid('hybridteaching_attendance');

        $newitemid = $DB->insert_record('hybridteaching_attend_log', $data);
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder).
        $this->set_mapping('hybridteaching_attend_log', $oldid, $newitemid, true);
    }

    /**
     * Process to execute after restore.
     *
     */
    protected function after_execute() {
        // Add hybridteaching related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_hybridteaching', 'intro', null);
    }
}
