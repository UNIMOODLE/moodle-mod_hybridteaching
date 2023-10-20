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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_hybridteaching
 * @category    upgrade
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute mod_hybridteaching upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */

function xmldb_hybridteaching_upgrade($oldversion) { 
    global $DB, $CFG;
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < '2023031700.16') {

        // Define field category to be added to hybridteaching_configs.
        $table = new xmldb_table('hybridteaching_configs');
        $field = new xmldb_field('category', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'subpluginconfigid');

        // Conditionally launch add field category.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('hybridteaching_session');

        if ($oldversion < 2023031700.16) {
            $field = new xmldb_field('vcreference', XMLDB_TYPE_INTEGER, '11', null, null, null, null, 'userecordvc');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Hybridteaching savepoint reached.
        upgrade_mod_savepoint(true, '2023031700.16', 'hybridteaching');
    }

    return true;
}

