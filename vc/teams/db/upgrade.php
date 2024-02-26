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
 * @package    hybridteachvc_teams
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute mod_hybridteaching upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_hybridteachvc_teams_upgrade($oldversion) {
    global $DB, $CFG;
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < '2023081600.06') {

        // Define field category to be added to hybridteaching_configs.
        $table = new xmldb_table('hybridteachvc_teams');
        $field = new xmldb_field('recordingid', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'joinurl');

        // Conditionally launch add field category.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < '2023081600.08') {
        // Define field category to be added to hybridteaching_configs.
        $table = new xmldb_table('hybridteachvc_teams_config');
        $field = new xmldb_field('accessmethod', XMLDB_TYPE_INTEGER, '11', null, 1, null, 1, 'id');

        // Conditionally launch add field category.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < '2023081600.09') {
        // Define field category to be added to hybridteaching_configs.
        $table = new xmldb_table('hybridteachvc_teams');
        $field = new xmldb_field('chaturl', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'recordingid');

        // Conditionally launch add field category.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}

