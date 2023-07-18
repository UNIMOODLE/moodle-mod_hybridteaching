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
    $instancevcsettings = new admin_settingpage('hybridteaching_instancevcsettings',
        new lang_string('instancesvcconfig', 'mod_hybridteaching'));
    $instancestoresettings = new admin_settingpage('hybridteaching_instancestoresettings',
        new lang_string('instancesstoreconfig', 'mod_hybridteaching'));

    if ($ADMIN->fulltree) {
        $instancevcsettings->add(new admin_setting_heading(
            'headerconfigvc',
            get_string('headerconfigvc', 'mod_hybridteaching'),
            ''
        ));

        $instancevcsettings->add(new hybridteaching_admin_plugins_instances(
            'managevideoconferenceplugins',
            get_string('videoconferenceplugins', 'mod_hybridteaching'),
            '',
            '',
            'hybridteachvc'
        ));

        $instancestoresettings->add(new admin_setting_heading(
            'headerconfigstore',
            get_string('headerconfigstore', 'mod_hybridteaching'),
            ''
        ));

        $instancestoresettings->add(new hybridteaching_admin_plugins_instances(
            'managestorageplugins',
            get_string('storageplugins', 'mod_hybridteaching'),
            '',
            '',
            'hybridteachstore'
        ));
    }

    $ADMIN->add('hybridteaching', $generalsettings);
    $ADMIN->add('hybridteaching', $instancevcsettings);
    $ADMIN->add('hybridteaching', $instancestoresettings);
}
