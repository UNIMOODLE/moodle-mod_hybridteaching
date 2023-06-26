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
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/lib/grouplib.php');

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

global $USER;

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

$renderer = $PAGE->get_renderer('mod_hybridteaching');

echo $OUTPUT->header();

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');

$session = new sessions_controller($hybridteaching);
$sessionshow = $session->get_next_session($hybridteaching->id);
  
//si existe tipo de videoconferencia:
if (!$sessionshow) {
    //errors: there are not sessions
    echo $OUTPUT->notification(get_string('nosessions','mod_hybridteaching', 'info'));
}
else {

    if (!has_capability ('moodle/site:accessallgroups', $modulecontext)){  
        //comprobar también que el usuario tiene permisos para acceder, que pertenezca al grupo, que puede acceder, bla bla,....    
        //comprobar que el usuario pertenece al grupo y si tiene permisos
        $groupmode = groups_get_activity_groupmode($cm);
        //si el usuario es miembro del grupo, puede ver sesión
        if (!groups_is_member ($sessionshow->groupid,$USER->id)){
            $access = false;
        }
        //errors: no access to groups
        echo $OUTPUT->notification(get_string('nosessions','mod_hybridteaching', 'info'));
    }   
    else {

        $result=[];
        $correct=false;
        if ($sessionshow->typevc){
            //comprobar que la instancia en hybridteaching_instances existe y no se ha borrado
            //y que el tipo de vc existe con el pluginmanager
            $config_exists = helper::subplugin_instance_exists($hybridteaching->instance);
            if ($config_exists==0){
                echo $OUTPUT->notification(get_string('nosubplugin','mod_hybridteaching', 'warning'));
                $correct=false;
            }
            else if ($config_exists==-1){
                echo $OUTPUT->notification(get_string('noinstance','mod_hybridteaching', 'warning'));
                $correct=false;
            }
            else if ($config_exists==1){
                require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$hybridteaching->typevc.'/classes/subobject.php');
                $subobject=new subobject($sessionshow->id);
        
                //si hay sessiones de vc creadas:  
                //(porque puede ser que sea programación de sesiones con vc y aun no se hayan creado las reuniones)

                if ($subobject->get_sessions()){
                    $resultsaccess=$subobject->get_zone_access();
                    $result['url'] = $resultsaccess['url'];
                    $result['ishost'] = $resultsaccess['ishost'];
                    /*$result[]=[
                        'url' => $resultsaccess['url'],
                        'ishost' => $resultsaccess['ishost'],
                    ];*/
                }
                $correct=true;
            }            
        } else {
            $correct=true;
        }

        //si existe vc y 
        if ($correct){
            //calcular resultados a mostrar:
            $date = new DateTime();
            $date->setTimestamp(intval($sessionshow->starttime));
            $timeinit=$date->getTimestamp();
            $timeend=$timeinit+intval($sessionshow->duration);

            /*calculate status:*/
            /*in progress*/
            $isprogress=false;
            $isstart=false;
            $isfinished=false;
            $alert = 0;

            if ($timeinit<time() && $timeend>time()){
                $status=get_string('status_progress','mod_hybridteaching');
                $isprogress=true;
                $alert='alert-warning';
            }
            /*will start*/
            else if ($timeinit>=time()){
                $status=get_string('status_start','mod_hybridteaching');
                $isstart=true;
                $alert='alert-info';
            }
            /*finished*/
            else if ($timeend<time()){
                $status=get_string('status_finished','mod_hybridteaching');
                $isfinished=true;
                $alert='alert-danger';
            }
        
            //closedoors
            $closedoors='';
            $isclosedoors=false;
            $closedoorstime=0;
            if ($hybridteaching->closedoorscount>0){               
                $isclosedoors=true;
                if ($hybridteaching->closedoorsunit==0){
                    $closedoors = get_string('closedoors_hours', 'mod_hybridteaching',$hybridteaching->closedoorscount);
                    $multiplydoors=3600;
                } else if ($hybridteaching->closedoorsunit==1){
                    $closedoors = get_string('closedoors_minutes', 'mod_hybridteaching',$hybridteaching->closedoorscount);
                    $multiplydoors=60;
                } else {
                    $closedoors = get_string('closedoors_seconds', 'mod_hybridteaching',$hybridteaching->closedoorscount);
                    $multiplydoors=1;
                }
                $closedoorstime=$hybridteaching->closedoorscount*$multiplydoors;
            }

            //advanceentry (abrir puertas)
            $advanceentrytime=0;
            $isadvanceentry=false;
            if ($hybridteaching->advanceentrycount>0){
                $isadvanceentry=true;
                if ($hybridteaching->advanceentryunit==0){
                    $multiplyadvance=3600;
                } else if ($hybridteaching->advanceentryunit==1){
                    $multiplyadvance=60;
                } else{
                    $multiplyadvance=1;
                }
                $advanceentrytime=$hybridteaching->advanceentrycount*$multiplyadvance;
            }

            $canentry=false;
            if (($timeinit-$advanceentrytime)<time() && ($timeinit+$closedoorstime)>time()){
                $canentry=true;
            }
            $result['isaccess'] = true;
            $result['id'] = $hybridteaching->course;
            $result['isprogress'] = $isprogress;
            $result['isstart'] = $isstart;
            $result['isfinished'] = $isfinished;
            $result['starttime'] = userdate($timeinit);
            $result['finished'] = userdate($timeend);
            $result['duration'] = helper::get_hours_format($sessionshow->duration);
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
