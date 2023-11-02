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

class common_controller {
    const OPERATOR_GREATER_THAN = ">";
    const OPERATOR_LESS_THAN = "<";
    public $hybridobject;

    /**
     * Constructs a new instance of the class.
     *
     * @param stdClass|null $hybridobject The hybrid object to be used
     * @param string|null $table The table to be used
     */
    public function __construct(stdClass $hybridobject = null) {
        $this->hybridobject = $hybridobject;
    }

    /**
     * Returns the number of enabled records in the table.
     *
     * @global moodle_database $DB Moodle database global object.
     * @return int The number of enabled records in the table.
     */
    public function get_enabled_data($table, $params = []) {
        global $DB;
        $params['visible'] = 1;
        $countenabled = $DB->count_records($table, $params);
        return $countenabled;
    }

    /**
     * Updates the visibility and timestamp of a data object in the database.
     *
     * @param mixed $id unique identifier of the data object
     * @param bool $visible determines whether the data object is visible or not
     * @throws Exception if the database update fails
     */
    public function enable_data($id, $visible, $table) {
        global $DB, $USER;
        $object = new stdClass();
        $object->id = $id;
        $object->visible = $visible;
        $object->timemodified = time();
        $object->modifiedby = $USER->id;
        $DB->update_record($table, $object);
    }

    /**
     * Updates the sort order of a data entry.
     *
     * @param int $id The ID of the entry to update.
     * @param int $sortorder The new sort order for the entry.
     * @throws Exception If the database update fails.
     * @return void
     */
    public function update_data_sortorder($id, $sortorder, $table) {
        global $DB;
        $object = new stdClass();
        $object->id = $id;
        $object->sortorder = $sortorder;
        $DB->update_record($table, $object);
    }
}
