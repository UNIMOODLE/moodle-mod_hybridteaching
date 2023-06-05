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
    $instancesettings = new admin_settingpage('hybridteaching_instancesettings',
        new lang_string('instancesconfig', 'mod_hybridteaching'));

    if ($ADMIN->fulltree) {
        $instancesettings->add(new admin_setting_heading(
            'headerconfig',
            get_string('headerconfig', 'mod_hybridteaching'),
            ""
        ));

        $instancesettings->add(new hybridteaching_admin_plugins_instances(
            'videoconferenceplugins',
            get_string('videoconferenceplugins', 'mod_hybridteaching'),
            ""
        ));
    }

    $ADMIN->add('hybridteaching', $generalsettings);
    $ADMIN->add('hybridteaching', $instancesettings);
}
