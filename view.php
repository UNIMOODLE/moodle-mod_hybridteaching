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

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot . '/lib/grouplib.php');

$id = optional_param('id', 0, PARAM_INT);
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

global $USER;

// TO-DO: Eventos

/*$event = \mod_hybridteaching\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('hybridteaching', $moduleinstance);
$event->trigger();
*/

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$urlparams = array('id' => $cm->id, 'h' => $hybridteaching->id);
$url = new moodle_url('/mod/hybridteaching/view.php', $urlparams);

$PAGE->set_url($url);
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
$PAGE->set_activity_record($hybridteaching);

$renderer = $PAGE->get_renderer('mod_hybridteaching');

echo $OUTPUT->header();

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');

$session = new sessions_controller($hybridteaching);
$activesession = $session->get_next_session($hybridteaching->id);

if (!$activesession) {
    $activesession = $session->get_last_session($hybridteaching->id);
}

if (!$activesession) {
    echo $OUTPUT->notification(get_string('nosessions', 'mod_hybridteaching', 'info'));
} else {
    if (!has_capability('moodle/site:accessallgroups', $modulecontext)) {  
        if (!groups_is_member($activesession->groupid, $USER->id)){
            $access = false;
        }
        echo $OUTPUT->notification(get_string('nosessions', 'mod_hybridteaching', 'info'));
    } else {
        $result = [];
        $hasvcinstance = false;
        if ($hybridteaching->instance) {
            $instance_found = helper::subplugin_instance_exists($hybridteaching->instance);
            if (!is_object($instance_found)) {
                if ($instance_found == 0) {
                    echo $OUTPUT->notification(get_string('nosubplugin', 'mod_hybridteaching', 'warning'));
                } elseif ($instance_found == -1) {
                    echo $OUTPUT->notification(get_string('noinstance', 'mod_hybridteaching', 'warning'));
                }
            } else {
                $hasvcinstance = true;
            }
        }
        
        if ($hasvcinstance) {
            $date = new DateTime();
            $date->setTimestamp(intval($activesession->starttime));
            $timeinit = $date->getTimestamp();
            $timeend = $timeinit + intval($activesession->duration);

            $isundatedsession = false;
            $isprogress = false;
            $isstart = false;
            $isfinished = false;
            $alert = 0;

            switch (true) {
                case $hybridteaching->starttime == 0:
                    $status = get_string('status_undated', 'mod_hybridteaching');
                    $isundatedsession = true;
                    $alert = 'alert-info';
                    break;
                case $timeinit < time() && $timeend > time():
                    $status = get_string('status_progress', 'mod_hybridteaching');
                    $isprogress = true;
                    $alert = 'alert-warning';
                    break;
                case $timeinit >= time():
                    $status = get_string('status_start', 'mod_hybridteaching');
                    $isstart = true;
                    $alert = 'alert-info';
                    break;
                case $timeend < time():
                    $status = get_string('status_finished', 'mod_hybridteaching');
                    $isfinished = true;
                    $alert = 'alert-danger';
                    break;
            }
        
            //closedoors
            $closedoors = '';
            $isclosedoors = false;
            $closedoorstime = 0;
            if ($hybridteaching->closedoorscount > 0) {
                $isclosedoors = true;
                switch ($hybridteaching->closedoorsunit) {
                    case 0:
                        $closedoors = get_string('closedoors_hours', 'mod_hybridteaching', $hybridteaching->closedoorscount);
                        $multiplydoors = HOURSECS;
                        break;
                    case 1:
                        $closedoors = get_string('closedoors_minutes', 'mod_hybridteaching', $hybridteaching->closedoorscount);
                        $multiplydoors = MINSECS;
                        break;
                    default:
                        $closedoors = get_string('closedoors_seconds', 'mod_hybridteaching', $hybridteaching->closedoorscount);
                        $multiplydoors = 1;
                        break;
                }
                $closedoorstime = $hybridteaching->closedoorscount * $multiplydoors;
            }

            $advanceentrytime = 0;
            $isadvanceentry = false;
            if ($hybridteaching->advanceentrycount > 0) {
                $isadvanceentry = true;
                if ($hybridteaching->advanceentryunit == 0) {
                    $multiplyadvance = HOURSECS;
                } else if ($hybridteaching->advanceentryunit == 1) {
                    $multiplyadvance = MINSECS;
                } else {
                    $multiplyadvance = 1;
                }
                $advanceentrytime = $hybridteaching->advanceentrycount * $multiplyadvance;
            }

            $canentry = false;
            if ($isundatedsession) {
                $canentry = true;
            } else {
                if (($timeinit - $advanceentrytime) < time()) {
                    if ($isclosedoors) {
                        if (($timeinit + $closedoorstime) > time()) {
                            $canentry = true;
                        }
                    } else {
                        if ($timeend > time()) {
                            $canentry = true;
                        }
                    }
                }
            }

            //createok: variable creada temporalmente para testeos. 
            //Eliminar/modificar cuando esté disponible el resto de subplugins de videoconferencia
            $result['createok']=true;

            if ($hybridteaching->instance && $instance_found) {         
                sessions_controller::require_subplugin_session($hybridteaching->typevc);
                $classname = sessions_controller::get_subplugin_class($hybridteaching->typevc);
                $sessionvc = new $classname($activesession->id);

                if (!empty($sessionvc->get_session())) {
                    $resultsaccess = $sessionvc->get_zone_access();
                    $result['url'] = $resultsaccess['url'];
                    $result['ishost'] = $resultsaccess['ishost'];
                } else {                 
                    //FALTA COMPROBAR SI HAY PERMISOS: SI ES PROFESOR, O SI PUEDE ENTRAR ANTES QUE EL ANFITRION
                    if ($activesession && $canentry == true) { 
                        $result['createok']=$sessionvc->create_unique_session_extended($activesession, $hybridteaching);
                    }
                }
            }

            $result['isaccess'] = true;
            $result['id'] = $hybridteaching->course;
            $result['isundatedsession'] = $isundatedsession;
            $result['isprogress'] = $isprogress;
            $result['isstart'] = $isstart;
            $result['isfinished'] = $isfinished;
            $result['starttime'] = userdate($timeinit);
            $result['finished'] = userdate($timeend);
            $result['duration'] = helper::get_hours_format($activesession->duration);
            $result['status'] = $status;
            $result['isadvanceentry'] = $isadvanceentry;
            $result['advanceentrytime'] = helper::get_hours_format($advanceentrytime);
            $result['isclosedoors'] = $isclosedoors;
            $result['closedoors'] = $closedoors;
            $result['closedoorstime'] = helper::get_hours_format($closedoorstime);
            $result['closedoors'] = $closedoors;
            $result['canentry'] = $canentry;
            $result['message'] = $status;
            $result['closebutton'] = 0;
            $result['alert'] = $alert;

            //render the view
            echo $renderer->zone_access($result);
        }
    }
}

echo $OUTPUT->footer();
