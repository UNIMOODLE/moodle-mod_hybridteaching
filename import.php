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

require(__DIR__.'/../../config.php');
use mod_hybridteaching\controller\notify_controller;
use mod_hybridteaching\form\sessions_import_form;
use mod_hybridteaching\import\sessions_import;

$id = required_param('id', PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$hybridteaching = $DB->get_record('hybridteaching', ['id' => $cm->instance], '*', MUST_EXIST);

$url = new moodle_url('/mod/hybridteaching/import.php', ['id' => $id]);
$PAGE->set_url($url);

require_capability('mod/hybridteaching:import', $context);

$strimportsessions = get_string('importsessions', 'hybridteaching');

$PAGE->navbar->add($strimportsessions);
$PAGE->set_title("$course->shortname: $strimportsessions");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('admin');

$returnurl = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);

$importform = new sessions_import_form(null, ['id' => $id]);

if ($importform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $importform->get_data()) {
    if ($importform->get_data()->validimport == 1) {
        require_sesskey();
        $text = $importform->get_file_content('sessionsfile');
        $encoding = $data->encoding;
        $delimiter = $data->delimiter_name;
        $importer = new sessions_import($text, $encoding, $delimiter, 0, null, false, $course, $hybridteaching);
        $importer->import($hybridteaching);
        redirect($url);
    } else {
        redirect($url);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('importsessions', 'hybridteaching'));
if ($hybridteaching->sessionscheduling != 1) {
    notify_controller::notify_problem(get_string('error:importnosessionschedule', 'hybridteaching'));
}
notify_controller::show();
$importform->display();
echo $OUTPUT->footer();
