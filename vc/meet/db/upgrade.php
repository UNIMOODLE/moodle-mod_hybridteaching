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
 * @package    hybridteachvc_meet
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute hybridteachvc_meet upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_hybridteachvc_meet_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < '2023031700.24') {
        // Changing type of field token on table hybridteachvc_meet_config to text.
        $table = new xmldb_table('hybridteachvc_meet_config');
        $field = new xmldb_field('token', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'clientsecret');

        // Launch change of type for field token.
        $dbman->change_field_type($table, $field);

        // Meet savepoint reached.
        upgrade_plugin_savepoint(true, '2023033100.07', 'hybridteachvc', 'meet');
    }

    if ($oldversion < '2023031700.33') {
        $table = new xmldb_table('hybridteachvc_meet');
        $field = new xmldb_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'htsession');

        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'joinurl');

            // Meet savepoint reached.
            upgrade_plugin_savepoint(true, '2023031700.33', 'hybridteachvc', 'meet');
        }
    }

    if ($oldversion < '2023033100.04') {

        // Define field eventid to be added to hybridteachvc_meet.
        $table = new xmldb_table('hybridteachvc_meet');
        $field = new xmldb_field('eventid', XMLDB_TYPE_CHAR, '255', null, null, null, '0', 'creatoremail');

        // Conditionally launch add field eventid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Meet savepoint reached.
        upgrade_plugin_savepoint(true, '2023033100.04', 'hybridteachvc', 'meet');
    }

    return true;
}
