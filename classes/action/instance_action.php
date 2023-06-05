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
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require('../../../../config.php');
require_once('../../adminlib.php');
require_once('../controller/instances_controller.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$instanceid = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_url('/mod/hybridteaching/classes/action/instance_action.php');
$PAGE->set_context(context_system::instance());

require_admin();
require_sesskey();

$hybridinstance = $DB->get_record('hybridteaching_instances', array('id' => $instanceid), '*', MUST_EXIST);
$table = 'hybridteaching_instances';
$instancecontroller = new instances_controller($hybridinstance, $table);
$instanceids = $instancecontroller::hybridteaching_get_instances();

$sortorder = array_values($instanceids);

$return = new moodle_url('/admin/settings.php', array('section' => 'hybridteaching_instancesettings'));

if (!array_key_exists($instanceid, $instanceids)) {
    redirect($return);
}

switch ($action) {
    case 'disable':
        $instancecontroller->enable_data($instanceid, false);
        break;

    case 'enable':
        $instancecontroller->enable_data($instanceid, true);
        break;

    case 'up':
        $order = array_keys($instanceids);
        $order = array_flip($order);
        $pos = $order[$instanceid];
        if ($pos > 0) {
            $switch = $pos - 1;
            $resorted = array_values($instanceids);
            $temp = $resorted[$pos];
            $resorted[$pos] = $resorted[$switch];
            $resorted[$switch] = $temp;
            // now update db sortorder
            foreach ($resorted as $sortorder => $instance) {
                if ($instance["sortorder"] != $sortorder) {
                    $instancecontroller->update_data_sortorder($instance["id"], $sortorder, 'hybridteaching_instances');
                }
            }
        }
        break;

    case 'down':
        $order = array_keys($instanceids);
        $order = array_flip($order);
        $pos = $order[$instanceid];
        if ($pos < count($instanceids) - 1) {
            $switch = $pos + 1;
            $resorted = array_values($instanceids);
            $temp = $resorted[$pos];
            $resorted[$pos] = $resorted[$switch];
            $resorted[$switch] = $temp;
            // now update db sortorder
            foreach ($resorted as $sortorder => $instance) {
                if ($instance["sortorder"] != $sortorder) {
                    $instancecontroller->update_data_sortorder($instance["id"], $sortorder, 'hybridteaching_instances');
                }
            }
        }
        break;
    case 'delete':
        $instancecontroller->hybridteaching_delete_instance($instanceid);
        break;
}

redirect($return);
