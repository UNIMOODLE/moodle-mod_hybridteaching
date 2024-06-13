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
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    hybridteachstore_pumukit
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute hybridteachstore_pumukit upgrade from the given old version.
 *
 * @param int $oldversion
 *
 * @return bool
 */
function xmldb_hybridteachstore_pumukit_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024061100) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('hybridteachstore_pumukit_con');
        // Rename field user on table hybridteachstore_pumukit_con to userpumukit.
        $field = new xmldb_field('user', XMLDB_TYPE_CHAR, 255, null, true, null, null, 'url');

        // Launch rename field applicationurl.
        $dbman->rename_field($table, $field, 'userpumukit');

        // Qlowcode savepoint reached.
        upgrade_plugin_savepoint(true, 2024061100, 'qtype', 'qlowcode');
    }

    return true;
}
