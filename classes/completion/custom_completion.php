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
 * Custom rules completion class used in hybridteching module *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types = 1);

namespace mod_hybridteaching\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for the hybridteaching activity.
 *
 * Class for defining mod_hybridteaching's custom completion rules and fetching the completion statuses
 * of the custom completion rules for a given hybridteaching instance and a user.
 *
 * @package mod_hybridteaching
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $hybridteachingid = $this->cm->instance;

        if (!$hybridteaching = $DB->get_record('hybridteaching', ['id' => $hybridteachingid])) {
            throw new \moodle_exception(get_string('nohid', 'hybridteaching') . $hybridteachingid);
        }
        $status = HYBRIDTEACHING_ATTSTATUS_NOTVALID;
        $attcountparams = ['userid' => $userid, 'hybridteachingid' => $hybridteachingid, 'status' => 1];
        if ($rule == 'completionattendance') {
            $status = $hybridteaching->completionattendance <= $DB->count_records('hybridteaching_attendance', $attcountparams);
        }
        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completionattendance',
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $completionattendance = $this->cm->customdata['customcompletionrules']['completionattendance'] ?? 0;

        return [
            'completionattendance' => get_string('completiondetail:attendance', 'hybridteaching', $completionattendance),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionattendance',
            'completionusegrade',
            'completionpassgrade',
        ];
    }


}
