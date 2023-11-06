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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/hybridteaching/adminlib.php');

$settings = null;
if ($hassiteconfig) {
    $ADMIN->add('modsettings', new admin_category('hybridteaching', new lang_string('pluginname', 'mod_hybridteaching')));
    $generalsettings = new admin_settingpage($section,
        new lang_string('generalconfig', 'mod_hybridteaching'));
    $vcsettings = new admin_settingpage('hybridteaching_configvcsettings',
        new lang_string('configsvcconfig', 'mod_hybridteaching'));
    $storesettings = new admin_settingpage('hybridteaching_configstoresettings',
        new lang_string('configsstoreconfig', 'mod_hybridteaching'));

    if ($ADMIN->fulltree) {
        $options = [
            0 => get_string('donotusepaging', 'mod_hybridteaching'),
            10 => 10,
            25 => 25,
            50 => 50,
            75 => 75,
            100 => 100,
            250 => 250,
            500 => 500,
            1000 => 1000,
        ];

        $generalsettings->add(new admin_setting_configselect('hybridteaching/resultsperpage',
            get_string('resultsperpage', 'hybridteaching'), get_string('sessresultsperpage_desc', 'hybridteaching'), 25, $options));

        $generalsettings->add(new admin_setting_configcheckbox('hybridteaching/reusesession',
            get_string('reusesession', 'hybridteaching'), get_string('reusesession_desc', 'hybridteaching'), 0));

        $generalsettings->add(new admin_setting_configcheckbox('hybridteaching/configsubcategories',
            get_string('configsubcategories', 'hybridteaching'), get_string('configsubcategories_desc', 'hybridteaching'), 0));

        $vcsettings->add(new admin_setting_heading(
            'headerconfigvc',
            get_string('headerconfigvc', 'mod_hybridteaching'),
            ''
        ));

        $vcsettings->add(new hybridteaching_admin_plugins_configs(
            'managevideoconferenceplugins',
            get_string('videoconferenceplugins', 'mod_hybridteaching'),
            '',
            '',
            'hybridteachvc'
        ));

        $storesettings->add(new admin_setting_heading(
            'headerconfigstore',
            get_string('headerconfigstore', 'mod_hybridteaching'),
            ''
        ));

        $storesettings->add(new hybridteaching_admin_plugins_configs(
            'managestorageplugins',
            get_string('storageplugins', 'mod_hybridteaching'),
            '',
            '',
            'hybridteachstore'
        ));
    }

    $ADMIN->add('hybridteaching', $generalsettings);
    $ADMIN->add('hybridteaching', $vcsettings);
    $ADMIN->add('hybridteaching', $storesettings);
}
