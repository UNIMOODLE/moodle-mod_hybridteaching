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
 * @package    hybridteachvc_teams
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class restore_hybridteaching_activity_task
 *
 */
class restore_hybridteachvc_teams_subplugin extends restore_subplugin {
    /**
     * Returns the paths to be handled by the subplugin
     * @return array
     */
    protected function define_session_subplugin_structure() {
        $paths = [];

        $elename = $this->get_namefor('session');
        $elepath = $this->get_pathfor('/hybridteachvc_teams');
        // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);
        return $paths;
    }

    /**
     * Processes one hybridteachvc_teams element
     * @param mixed $data
     */
    public function process_hybridteachvc_teams_session($data) {
        global $DB;

        $data = (object)$data;
        $data->htsession = $this->get_mappingid('hybridteaching_session', $data->htsession);
        $oldid = $data->id;

        $newitemid = $DB->insert_record('hybridteachvc_teams', $data);
        // We map the references of the restored record.
        $this->set_mapping('hybridteachvc_teams', $oldid, $newitemid);
    }
}
