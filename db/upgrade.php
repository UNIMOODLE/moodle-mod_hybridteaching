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
 * Upgrades the hybridteaching module from a specific version to the current version.
 *
 * @param int $oldversion The old version of the module that is being upgraded from.
 * @return bool True if the upgrade is successful, false otherwise.
 */
function xmldb_hybridteaching_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < '2023031700.21') {
        $table = new xmldb_table('hybridteaching_session');
        $field = new xmldb_field('visiblerecord', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'storagereference');

        // Conditionally launch add field visible.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.21', 'hybridteaching');
    }

    if ($oldversion < '2023031700.22') {

        // Define field caleventid to be added to hybridteaching_session.
        $table = new xmldb_table('hybridteaching_session');
        $field = new xmldb_field('caleventid', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'attexempt');

        // Conditionally launch add field caleventid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.22', 'hybridteaching');
    }

    if ($oldversion < '2023031700.30') {

        // Rename field categories on table hybridteaching_configs to NEWNAMEGOESHERE.
        $table = new xmldb_table('hybridteaching_configs');
        $field = new xmldb_field('category', XMLDB_TYPE_INTEGER, 11, null, null, null, null, 'subpluginconfigid');

        // Launch rename field categories.
        $dbman->rename_field($table, $field, 'categories');

        $field = new xmldb_field('categories', XMLDB_TYPE_TEXT, null, null, null, null, null, 'subpluginconfigid');

        // Launch change of type for field categories.
        $dbman->change_field_type($table, $field);

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.30', 'hybridteaching');
    }

    if ($oldversion < '2023031700.31') {

        // Rename field categories on table hybridteaching_configs to NEWNAMEGOESHERE.
        $table = new xmldb_table('hybridteaching');
        $field = new xmldb_field('undatedsession', XMLDB_TYPE_INTEGER, 1, null, null, null, null, 'sessionscheduling');

        // Launch rename field categories.
        $dbman->rename_field($table, $field, 'reusesession');

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.31', 'hybridteaching');
    }

    if ($oldversion < '2023031700.32') {
        $table = new xmldb_table('hybridteaching_session');
        $field = new xmldb_field('processedrecording', XMLDB_TYPE_INTEGER, 11, null, null, null, null, 'vcreference');

        // Launch change of type for field categories.
        $dbman->change_field_type($table, $field);

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.32', 'hybridteaching');
    }

    if ($oldversion < '2023031700.33') {
        $table = new xmldb_table('hybridteaching_session');
        $field = new xmldb_field('visiblechat', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'visiblerecord');

        // Conditionally launch add field visible.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.33', 'hybridteaching');
    }

    if ($oldversion < '2023031700.40') {
        $table = new xmldb_table('hybridteaching');
        $field = new xmldb_field('wellcomemessage', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'userslimit');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < '2023031700.44') {
        $table = new xmldb_table('hybridteaching');
        $field = new xmldb_field('maxgradeattendanceunit', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'maxgradeattendance');

        // Launch rename field maxgradeattendancemode.
        $dbman->rename_field($table, $field, 'maxgradeattendancemode');

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.44', 'hybridteaching');
    }

    if ($oldversion < '2023031700.45') {
        $table = new xmldb_table('hybridteaching_session');
        $field = new xmldb_field('visibleatt', XMLDB_TYPE_INTEGER, '1', null, null, null, '1', 'visiblechat');

        // Conditionally launch add field visible.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.45', 'hybridteaching');
    }

    return true;
}
