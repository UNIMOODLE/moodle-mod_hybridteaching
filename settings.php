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
            0 => get_string('donotusepaging', 'hybridteaching'),
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

        $vcsettings->add(new admin_setting_heading(
            'headerconfigvc',
            get_string('headerconfigvc', 'hybridteaching'),
            ''
        ));

        $timeunits = [new lang_string('hours'), new lang_string('minutes'), new lang_string('seconds')];
        $name = new lang_string('defaultsettings', 'mod_hybridteaching');
        $description = new lang_string('defaultsettings_help', 'hybridteaching');
        $generalsettings->add(new admin_setting_heading('defaultsettings', $name, $description));

        $showdescription = new admin_setting_configcheckbox('hybridteaching/showdescription',
            new lang_string('showdescription'),
            new lang_string('showdescription_help'), 1);
        $showdescription->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($showdescription);

        $useattendance = new admin_setting_configcheckbox('hybridteaching/useattendance',
            new lang_string('useattendance', 'hybridteaching'),
            new lang_string('useattendance_help', 'hybridteaching'), 1);
        $useattendance->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($useattendance);

        $usevideoconference = new admin_setting_configcheckbox('hybridteaching/usevideoconference',
            new lang_string('usevideoconference', 'hybridteaching'),
            new lang_string('usevideoconference_help', 'hybridteaching'), 1);
        $usevideoconference->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($usevideoconference);

        $userecordvc = new admin_setting_configcheckbox('hybridteaching/userecordvc',
            new lang_string('userecordvc', 'hybridteaching'),
            new lang_string('userecordvc_help', 'hybridteaching'), 1);
        $generalsettings->add($userecordvc);
        $userecordvc->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $userecordvc->add_dependent_on(new lang_string('usevideoconference', 'hybridteaching'));

        $name = new lang_string('sessionssettings', 'mod_hybridteaching');
        $description = new lang_string('sessionssettings_help', 'hybridteaching');
        $generalsettings->add(new admin_setting_heading('sessionssettings', $name, $description));

        $sessionscheduling = (new admin_setting_configcheckbox('hybridteaching/sessionscheduling',
            new lang_string('sessionscheduling', 'hybridteaching'),
            new lang_string('sessionscheduling_desc', 'hybridteaching'), 0));
        $sessionscheduling->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($sessionscheduling);

        $reusesession = (new admin_setting_configcheckbox('hybridteaching/reusesession',
            new lang_string('reusesession', 'hybridteaching'), new lang_string('reusesession_desc', 'hybridteaching'), 0));
        $reusesession->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($reusesession);

        $advanceentrycount = (new admin_setting_configtext('hybridteaching/advanceentrycount',
            new lang_string('advanceentrycount', 'hybridteaching'),
            new lang_string('advanceentrycount_help', 'hybridteaching'), 0, PARAM_INT));
        $advanceentrycount->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $advanceentrycount->add_dependent_on(new lang_string('sessionscheduling', 'hybridteaching'));

        $advanceentryunit = (new admin_setting_configselect('hybridteaching/advanceentryunit',
            new lang_string('advanceentryunit', 'hybridteaching'),
            new lang_string('advanceentryunit_help', 'hybridteaching'), 1, $timeunits));
        $advanceentryunit->set_locked_flag_options(admin_setting_flag::ENABLED, false);

        $generalsettings->add($advanceentrycount);
        $generalsettings->add($advanceentryunit);

        $closedoorscount = (new admin_setting_configtext('hybridteaching/closedoorscount',
            new lang_string('closedoorscount', 'hybridteaching'),
            new lang_string('closedoorscount_help', 'hybridteaching'), 0, PARAM_INT));
        $closedoorscount->set_locked_flag_options(admin_setting_flag::ENABLED, false);

        $closedoorsunit = (new admin_setting_configselect('hybridteaching/closedoorsunit',
            new lang_string('closedoorsunit', 'hybridteaching'),
            new lang_string('closedoorsunit_help', 'hybridteaching'), 1, $timeunits));
        $closedoorsunit->set_locked_flag_options(admin_setting_flag::ENABLED, false);

        $generalsettings->add($closedoorscount);
        $generalsettings->add($closedoorsunit);

        $waitmoderator = (new admin_setting_configcheckbox('hybridteaching/waitmoderator',
            new lang_string('waitmoderator', 'hybridteaching'),
            new lang_string('waitmoderator_desc', 'hybridteaching'), 0));
        $waitmoderator->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($waitmoderator);

        $userslimit = new admin_setting_configtext('hybridteaching/userslimit',
            new lang_string('userslimit', 'hybridteaching'),
            new lang_string('userslimit_desc', 'hybridteaching'), 300, PARAM_INT);
        $userslimit->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($userslimit);

        $graceperiod = (new admin_setting_configtext('hybridteaching/graceperiod',
            new lang_string('graceperiod', 'hybridteaching'),
            new lang_string('graceperiod_help', 'hybridteaching'), 0, PARAM_INT));
        $graceperiod->set_locked_flag_options(admin_setting_flag::ENABLED, false);

        $graceperiodunit = (new admin_setting_configselect('hybridteaching/graceperiodunit',
            new lang_string('graceperiodunit', 'hybridteaching'),
            new lang_string('graceperiodunit_help', 'hybridteaching'), 1, $timeunits));
        $graceperiodunit->set_locked_flag_options(admin_setting_flag::ENABLED, false);

        $generalsettings->add($graceperiod);
        $generalsettings->add($graceperiodunit);

        $name = new lang_string('attendancesettings', 'mod_hybridteaching');
        $description = new lang_string('attendancesettings_help', 'hybridteaching');
        $generalsettings->add(new admin_setting_heading('attendancesettings', $name, $description));

        $validateattendance = (new admin_setting_configtext('hybridteaching/validateattendance',
            new lang_string('validateattendance', 'hybridteaching'),
            new lang_string('validateattendance_help', 'hybridteaching'), 0, PARAM_INT));
        $validateattendance->set_locked_flag_options(admin_setting_flag::ENABLED, false);

        $attendanceunit = (new admin_setting_configselect('hybridteaching/attendanceunit',
            new lang_string('attendanceunit', 'hybridteaching'),
            new lang_string('attendanceunit_help', 'hybridteaching'), 1, [
                0 => 'hours', 1 => 'minutes', 2 => 'seconds', 3 => '%',
            ]));
        $attendanceunit->set_locked_flag_options(admin_setting_flag::ENABLED, false);

        $generalsettings->add($validateattendance);
        $generalsettings->add($attendanceunit);

        $useqr = (new admin_setting_configcheckbox('hybridteaching/useqr',
            new lang_string('useqr', 'hybridteaching'),
            new lang_string('useqr_desc', 'hybridteaching'), 0));
        $useqr->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($useqr);

        $rotateqr = (new admin_setting_configcheckbox('hybridteaching/rotateqr',
            new lang_string('rotateqr', 'hybridteaching'),
            new lang_string('rotateqr_desc', 'hybridteaching'), 0));
        $rotateqr->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($rotateqr);

        $studentpassword = (new admin_setting_configpasswordunmask('hybridteaching/studentpassword',
            new lang_string('studentpassword', 'hybridteaching'),
            new lang_string('studentpassword_desc', 'hybridteaching'), PARAM_TEXT));
        $studentpassword->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($studentpassword);

        $qrupdatetime = (new admin_setting_configtext('hybridteaching/qrupdatetime',
        new lang_string('qrupdatetime', 'hybridteaching'),
        new lang_string('qrupdatetime_help', 'hybridteaching'), 15, PARAM_INT));
        $qrupdatetime->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $generalsettings->add($qrupdatetime);

        $vcsettings->add(new hybridteaching_admin_plugins_configs(
            'managevideoconferenceplugins',
            get_string('videoconferenceplugins', 'hybridteaching'),
            '',
            '',
            'hybridteachvc'
        ));

        $storesettings->add(new admin_setting_heading(
            'headerconfigstore',
            get_string('headerconfigstore', 'hybridteaching'),
            ''
        ));

        $storesettings->add(new hybridteaching_admin_plugins_configs(
            'managestorageplugins',
            get_string('storageplugins', 'hybridteaching'),
            '',
            '',
            'hybridteachstore'
        ));
    }

    $ADMIN->add('hybridteaching', $generalsettings);
    $ADMIN->add('hybridteaching', $vcsettings);
    $ADMIN->add('hybridteaching', $storesettings);

    $categoryvc = new admin_category('hybridteachvcplugins',
    new lang_string('subplugintype_hybridteachvc_plural', 'hybridteaching'), !$module->is_enabled());
    $ADMIN->add('hybridteaching', $categoryvc);

    $categorystore = new admin_category('hybridteachstoreplugins',
    new lang_string('subplugintype_hybridteachstore_plural', 'hybridteaching'), !$module->is_enabled());
    $ADMIN->add('hybridteaching', $categorystore);

    foreach (core_plugin_manager::instance()->get_plugins_of_type('hybridteachvc') as $plugin) {
        $plugin->load_settings($ADMIN, 'hybridteachvcplugins', $hassiteconfig);
    }

    foreach (core_plugin_manager::instance()->get_plugins_of_type('hybridteachstore') as $plugin) {
        $plugin->load_settings($ADMIN, 'hybridteachstoreplugins', $hassiteconfig);
    }
}
