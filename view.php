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
 * Prints an instance of mod_hybridteaching.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



 //use mod_hybridteaching\output\view_page;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

require_once($CFG->dirroot . '/mod/hybridteaching/locallib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$h = optional_param('h', 0, PARAM_INT);

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
    $hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($h) {
    $hybridteaching = $DB->get_record('hybridteaching', array('id' => $h), '*', MUST_EXIST);
    list ($course, $cm) = get_course_and_cm_from_instance($h,  'teamsmeeting');
}
if (!isset($cm) || !$cm) {
    throw new moodle_exception('view_error_url_missing_parameters', 'hybridteaching');
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);

// TO-DO: Parte comentada, pero hay que ver qué hacer con los eventos

/*$event = \mod_hybridteaching\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('hybridteaching', $moduleinstance);
$event->trigger();
*/

// Esto marca como finalizada la actividad al entrar el alumno.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$urlparams = array('id' => $cm->id, 'h' => $hybridteaching->id);
$url = new moodle_url('/mod/hybridteaching/view.php', $urlparams);


$PAGE->set_url($url);
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_activity_record($hybridteaching);

// Output starts.
//$renderer = $PAGE->get_renderer('mod_hybridteaching');
//try {
//    $renderedinfo = $renderer->render(new view_page($moduleinstance));
//} catch (server_not_available_exception $e) {
    //bigbluebutton_proxy::handle_server_not_available($instance);
//}


$renderer = $PAGE->get_renderer('mod_hybridteaching');

echo $OUTPUT->header();

//si existe tipo de videoconferencia:
if ($hybridteaching->typevc){
    require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$hybridteaching->typevc.'/classes/subobject.php');
    require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$hybridteaching->typevc.'/classes/webservice.php');

    $subobject=new subobject($hybridteaching->id);

    //si hay sessiones de vc creadas:  
    //(porque puede ser que sea programación de sesiones con vc y aun no se hayan creado las reuniones)
    if ($subobject->get_sessions()){
            $resultsaccess=$subobject->get_zone_access($hybridteaching->id);
            //dividir la vista en 3 mustaches: zona mensajes, boton reunion, zona grabaciones
            echo $renderer->zone_access($resultsaccess);
            //obtnener aquí las grabaciones
            //echo $renderer->zone_records();
    }
    else {
        //errors: there are not sessions
        $errors = ['error'=> get_string('nosessions','mod_hybridteaching')];
        echo $renderer->zone_errors($errors);
    }

}


echo $OUTPUT->footer();
