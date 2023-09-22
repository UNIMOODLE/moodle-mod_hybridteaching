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
 * Plugin administration pages are defined here.
 *
 * @package     mod_hybridteaching
 * @category    admin
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/hybridteaching/adminlib.php');

$settings = null;
if ($hassiteconfig) {
    $ADMIN->add('modsettings', new admin_category('hybridteaching', new lang_string('pluginname', 'mod_hybridteaching')));
    $generalsettings = new admin_settingpage('hybridteaching_generalsettings',
        new lang_string('generalconfig', 'mod_hybridteaching'));
    $vcsettings = new admin_settingpage('hybridteaching_configvcsettings',
        new lang_string('configsvcconfig', 'mod_hybridteaching'));
    $storesettings = new admin_settingpage('hybridteaching_configstoresettings',
        new lang_string('configsstoreconfig', 'mod_hybridteaching'));

    if ($ADMIN->fulltree) {
        $options = array(
            0 => get_string('donotusepaging', 'mod_hybridteaching'),
            10 => 10,
            25 => 25,
            50 => 50,
            75 => 75,
            100 => 100,
            250 => 250,
            500 => 500,
            1000 => 1000,
        );
    
        $generalsettings->add(new admin_setting_configselect('hybridteaching/resultsperpage',
            get_string('resultsperpage', 'hybridteaching'), get_string('sessresultsperpage_desc', 'hybridteaching'), 25, $options));
    
        $generalsettings->add(new admin_setting_configcheckbox('hybridteaching/reusesession',
            get_string('reusesession', 'hybridteaching'), get_string('reusesession_desc', 'hybridteaching'), 0));

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
