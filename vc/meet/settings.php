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

defined('MOODLE_INTERNAL') || die();

$item = new admin_setting_configcheckbox('hybridteachvc_meet/enabled',
    new lang_string('enabled', 'hybridteaching'),
    new lang_string('enabled_help', 'hybridteaching'), 1);

$item->set_updatedcallback(function () {
    global $DB;
    if (get_config('hybridteachvc_meet', 'enabled') == false) {
        $sql = "UPDATE {hybridteaching_configs} SET visible=0 WHERE type='meet'";
        $DB->execute($sql);
    }
});

$settings->add($item);

$settings->add(new admin_setting_configcheckbox('hybridteachvc_meet/enabledrecording',
    get_string('userecordvc', 'hybridteaching'),
    get_string('userecordvc_help', 'hybridteaching'), 1));
