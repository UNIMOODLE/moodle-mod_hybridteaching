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
require_once('editinstance_form.php');
require_once('../../classes/controller/instances_controller.php');
require_once('classes/instances.php');

$type = optional_param('type', "", PARAM_COMPONENT);
$instanceid = optional_param('id', 0, PARAM_INT);
$context = context_system::instance();
$return = new moodle_url('/admin/settings.php', array('section' => 'hybridteaching_instancestoresettings'));
require_admin();

if (empty($type)) {
    redirect($return);
}

$url = new moodle_url('/mod/hybridteaching/store/youtube/editinstance.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

$hybridinstance = new stdClass();
if (!empty($instanceid)) {
    $hybridinstance = $DB->get_record('hybridteaching_instances', array('id' => $instanceid), '*', MUST_EXIST);
} else {
    $hybridinstance->type = $type;
}


$instancecontroller = new instances_controller($hybridinstance, 'hybridteachstore');

$instance = null;
if (!empty($instanceid)) {
    $instance = $instancecontroller->hybridteaching_load_instance($instanceid);
    $youtubeinstance = instances::load_instance($instance->subplugininstanceid);
    unset($youtubeinstance->id);
    $instance = (object) array_merge((array) $instance, (array) $youtubeinstance);
}

$mform = new htyoutube_instance_edit_form(null, array($instance, $type));
$message = '';
$error = '';
if ($mform->is_cancelled()) {
    redirect($return);
} else if ($data = $mform->get_data()) {
    if (!isset($instance)) {
        $data->id = instances::create_instance($data);
        $error = $instancecontroller->hybridteaching_create_instance($data, $type);
        empty($error) ? $message = 'createdinstance' : $message = $error;
    } else {
        $error = $instancecontroller->hybridteaching_update_instance($data);
        instances::update_instance($data);
        empty($error) ? $message = 'updatedinstance' : $message = $error;
    }
    $return = new moodle_url('/admin/settings.php', array('section' => 'hybridteaching_instancestoresettings',
        'message' => $message));

    redirect($return);
}

$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_context($context);


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'hybridteaching'));
$mform->display();
echo $OUTPUT->footer();
