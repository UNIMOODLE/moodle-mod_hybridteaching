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

/**
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot.'/mod/hybridteaching/store/youtube/classes/webservice.php');


class sessions extends sessions_controller {

    public function create_session($data) {
        global $DB;

      
        return $response;
    }
    
    public function update_session($data) {

    }


    public function delete_session($id) {
    }


    public function delete_all_sessions($moduleinstance) {
    }

    function populate_htyoutube_from_response($module, $response) {

    }
}
