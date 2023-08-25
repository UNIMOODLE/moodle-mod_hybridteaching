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



require_once('../../../../config.php');
require_once('editconfig_form.php');
require_once('../../classes/controller/configs_controller.php');
require_once('classes/configs.php');

$type = optional_param('type', "", PARAM_COMPONENT);
$configid = optional_param('id', 0, PARAM_INT);
$context = context_system::instance();
$return = new moodle_url('/admin/settings.php', array('section' => 'hybridteaching_configstoresettings'));
require_admin();

if (empty($type)) {
    redirect($return);
}

$url = new moodle_url('/mod/hybridteaching/store/youtube/editconfig.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

$hybridconfig = new stdClass();
if (!empty($configid)) {
    $hybridconfig = $DB->get_record('hybridteaching_configs', array('id' => $configid), '*', MUST_EXIST);
} else {
    $hybridconfig->type = $type;
}


$configcontroller = new configs_controller($hybridconfig, 'hybridteachstore');

$config = null;
if (!empty($configid)) {
    $config = $configcontroller->hybridteaching_load_config($configid);
    $youtubeconfig = configs::load_config($config->subpluginconfigid);
    unset($youtubeconfig->id);
    $config = (object) array_merge((array) $config, (array) $youtubeconfig);
}

$mform = new htyoutube_config_edit_form(null, array($config, $type));
$message = '';
$error = '';
if ($mform->is_cancelled()) {
    redirect($return);
} else if ($data = $mform->get_data()) {
    if (!isset($config)) {
        $data->id = configs::create_config($data);
        $error = $configcontroller->hybridteaching_create_config($data, $type);
        empty($error) ? $message = 'createdconfig' : $message = $error;
        $configid=$data->id;
    } else {
        $error = $configcontroller->hybridteaching_update_config($data);
        $configid=configs::update_config($data);
        empty($error) ? $message = 'updatedconfig' : $message = $error;
    }
    $return = new moodle_url('/admin/settings.php', array('section' => 'hybridteaching_configstoresettings',
        'message' => $message));

    //add access code and permissions to youtube
    $url = new moodle_url('./classes/youtubeaccess.php',array('id'=>$configid) );
    redirect($url);        

    //redirect($return);
}

$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_context($context);


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'hybridteaching'));
$mform->display();
echo $OUTPUT->footer();
