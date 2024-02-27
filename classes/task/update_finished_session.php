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

namespace mod_hybridteaching\task;

use mod_hybridteaching\controller\sessions_controller;

defined('MOODLE_INTERNAL') || die();

/**
 * Class update_finished.
 */
class update_finished_session extends \core\task\scheduled_task {
    
    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('updatefinished', 'hybridteaching');
    }

    /**
     * Execte the task.
     */
    public function execute() {
        global $DB;

        $sql = 'SELECT *
                  FROM {hybridteaching_session}
                 WHERE starttime + duration < ?
                   AND isfinished = 0
                   AND duration != 0';

        $sessions = $DB->get_recordset_sql($sql, [time()]);
        if (!empty($sessions)) {
            foreach ($sessions as $session) {
                if (sessions_controller::hybridteaching_exist($session->hybridteachingid)) {
                    list($course, $cm) = get_course_and_cm_from_instance($session->hybridteachingid, 'hybridteaching');
                    $event = \mod_hybridteaching\event\session_finished::create([
                        'objectid' => $session->hybridteachingid,
                        'context' => \context_module::instance($cm->id),
                        'other' => [
                            'sessid' => $session->id,
                        ],
                    ]);
                    $event->trigger();
                }
            }
        }
    }
}