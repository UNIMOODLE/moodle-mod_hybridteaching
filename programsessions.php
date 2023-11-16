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

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/classes/form/sessions_form.php');
require_once(__DIR__.'/classes/controller/sessions_controller.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$sid = optional_param('s', 0, PARAM_INT);
$h = optional_param('h', 0, PARAM_INT);
$slist = optional_param('l', 2, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/hybridteaching:programschedule', $context);

$url = new moodle_url('/mod/hybridteaching/programsessions.php', ['id' => $id]);

$hybridteaching = $DB->get_record('hybridteaching', ['id' => $cm->instance], '*', MUST_EXIST);

$PAGE->set_url($url);
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('programschedule', 'hybridteaching'));
$PAGE->set_context($context);

$sessionobj = null;
if (!empty($sid)) {
    $sessionobj = $DB->get_record('hybridteaching_session', ['id' => $sid], '*', MUST_EXIST);
    $sessionobj->sessionid = $sessionobj->id;
    unset($sessionobj->id);
    $sessionobj->duration = $sessionobj->duration / MINSECS;
}

$sessioncontroller = new sessions_controller($hybridteaching);
$params = [
    'course' => $course,
    'cm' => $cm,
    'session' => $sessionobj,
    'typevc' => $hybridteaching->typevc,
];
$mform = new sessions_form(null, $params);
$return = new moodle_url('sessions.php?id='.$id.'&l='.$slist);
$message = '';
$error = '';
if ($mform->is_cancelled()) {
    redirect($return);
} else if ($data = $mform->get_data()) {
    if (empty($sid)) {
        $data->context = $context;
        $sessioncontroller->create_session($data);
    } else {
        $data->id = required_param('s', PARAM_INT);
        $data->context = $context;
        $data->config = $hybridteaching->config;
        $sessioncontroller->update_session($data);
    }
    redirect($return);
}

$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_context($context);

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
