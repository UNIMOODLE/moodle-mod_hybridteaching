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
 * @package    hybridteachvc_zoom
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_hybridteaching\controller\configs_controller;
use hybridteachvc_zoom\configs;

require_once('../../../../config.php');
require_once('editconfig_form.php');
require_once('../../lib.php');
$PAGE->requires->js_call_amd('mod_hybridteaching/categoriesmodal', 'init');

$type = optional_param('type', "", PARAM_COMPONENT);
$configid = optional_param('id', 0, PARAM_INT);
$context = context_system::instance();
$return = new moodle_url('/admin/settings.php', ['section' => 'hybridteaching_configvcsettings']);
require_admin();

if (empty($type)) {
    redirect($return);
}

$url = new moodle_url('/mod/hybridteaching/vc/zoom/editconfig.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

$hybridconfig = new stdClass();
if (!empty($configid)) {
    $hybridconfig = $DB->get_record('hybridteaching_configs', ['id' => $configid], '*', MUST_EXIST);
} else {
    $hybridconfig->type = $type;
}


$configcontroller = new configs_controller($hybridconfig, 'hybridteachvc');

$config = null;
if (!empty($configid)) {
    $config = $configcontroller->hybridteaching_load_config($configid);
    $zoomconfig = configs::load_config($config->subpluginconfigid);
    unset($zoomconfig->id);
    $config = (object) array_merge((array) $config, (array) $zoomconfig);
}

$mform = new htzoom_config_edit_form(null, [$config, $type]);
$message = '';
$error = '';
if ($mform->is_cancelled()) {
    redirect($return);
} else if ($data = $mform->get_data()) {
    if (!isset($config)) {
        $data->id = configs::create_config($data);
        $error = $configcontroller->hybridteaching_create_config($data, $type);
        empty($error) ? $message = 'createdconfig' : $message = $error;
    } else {
        $error = $configcontroller->hybridteaching_update_config($data);
        configs::update_config($data);
        empty($error) ? $message = 'updatedconfig' : $message = $error;
    }
    $return = new moodle_url('/admin/settings.php',
        ['section' => 'hybridteaching_configvcsettings', 'message' => $message]);

    redirect($return);
}

$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_context($context);


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'hybridteaching'));
$mform->display();
$templatecontext = hybrid_get_categories_for_modal();
echo $OUTPUT->render_from_template('mod_hybridteaching/categoriesmodal', $templatecontext);
echo $OUTPUT->footer();
