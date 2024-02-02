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

define('NO_OUTPUT_BUFFERING', true);

require('../../../config.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$configid = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$section = optional_param('section', '', PARAM_COMPONENT);

$PAGE->set_url('/mod/hybridteaching/action/config_action.php');
$PAGE->set_context(context_system::instance());

require_admin();
require_sesskey();

$hybridconfig = $DB->get_record('hybridteaching_configs', ['id' => $configid], '*', MUST_EXIST);
$configcontroller = new mod_hybridteaching\controller\configs_controller($hybridconfig,
    'hybridteach'.$hybridconfig->subplugintype);
$configids = $configcontroller->hybridteaching_get_configs();

$sortorder = array_values($configids);

$return = new moodle_url('/admin/settings.php', ['section' => $section]);

if (!array_key_exists($configid, $configids)) {
    redirect($return);
}

switch ($action) {
    case 'disable':
        $configcontroller->enable_data($configid, false, 'hybridteaching_configs');
        break;

    case 'enable':
        $configcontroller->enable_data($configid, true, 'hybridteaching_configs');
        break;

    case 'up':
        $order = array_keys($configids);
        $order = array_flip($order);
        $pos = $order[$configid];
        if ($pos > 0) {
            $switch = $pos - 1;
            $resorted = array_values($configids);
            $temp = $resorted[$pos];
            $resorted[$pos] = $resorted[$switch];
            $resorted[$switch] = $temp;
            foreach ($resorted as $sortorder => $config) {
                if ($config["sortorder"] != $sortorder) {
                    $configcontroller->update_data_sortorder($config["id"], $sortorder, 'hybridteaching_configs');
                }
            }
        }
        break;

    case 'down':
        $order = array_keys($configids);
        $order = array_flip($order);
        $pos = $order[$configid];
        if ($pos < count($configids) - 1) {
            $switch = $pos + 1;
            $resorted = array_values($configids);
            $temp = $resorted[$pos];
            $resorted[$pos] = $resorted[$switch];
            $resorted[$switch] = $temp;
            foreach ($resorted as $sortorder => $config) {
                if ($config["sortorder"] != $sortorder) {
                    $configcontroller->update_data_sortorder($config["id"], $sortorder, 'hybridteaching_configs');
                }
            }
        }
        break;
    case 'delete':
        $configcontroller->hybridteaching_delete_config($configid);
        break;
}

redirect($return);
