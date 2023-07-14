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
//get next session
$sessionshow = $session->get_next_session($hybridteaching->id);

//if there are not next session, get last session
if (!$sessionshow) {
    $sessionshow = $session->get_last_session($hybridteaching->id);
}
//if no next session neigher last session: notification
if (!$sessionshow){
        //errors: there are not sessions
        echo $OUTPUT->notification(get_string('nosessions','mod_hybridteaching', 'info'));
}
else {
    if (!has_capability ('moodle/site:accessallgroups', $modulecontext)){  

    //ARREGLAR ESTA PARTE        
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
        if ($hybridteaching->instance){
            //comprobar que la instancia en hybridteaching_instances existe y no se ha borrado
            //y que el tipo de vc existe con el pluginmanager
            $config_exists = helper::subplugin_instance_exists($hybridteaching->instance);
            if (!is_object($config_exists) && $config_exists==0){
                echo $OUTPUT->notification(get_string('nosubplugin','mod_hybridteaching', 'warning'));
                $correct=false;
            }
            else if (!is_object($config_exists) && $config_exists==-1){
                echo $OUTPUT->notification(get_string('noinstance','mod_hybridteaching', 'warning'));
                $correct=false;
            }
            else if ($config_exists){
                $correct=true;
            }            
        } else {
            $correct=true;
        }

        if ($correct){
            //calculate results:
            $date = new DateTime();
            $date->setTimestamp(intval($sessionshow->starttime));
            $timeinit=$date->getTimestamp();
            $timeend=$timeinit+intval($sessionshow->duration);

            /*calculate status:*/
            $isundatedsession=false;
            $isprogress=false;
            $isstart=false;
            $isfinished=false;
            $alert = 0;

                /* starttime=0: permite acceso en cualquier momento */
            if ($hybridteaching->starttime==0){
                $status=get_string('status_undated','mod_hybridteaching');
                $isundatedsession=true;
                $alert='alert-info';
            } 
            /* in progress */
            else if ( ($timeinit<time() && $timeend>time())){
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
            $closedoors = '';
            $isclosedoors = false;
            $closedoorstime = 0;
            if ($hybridteaching->closedoorscount > 0) {               
                $isclosedoors = true;
                if ($hybridteaching->closedoorsunit == 0) {
                    $closedoors = get_string('closedoors_hours', 'mod_hybridteaching', $hybridteaching->closedoorscount);
                    $multiplydoors = HOURSECS;
                } else if ($hybridteaching->closedoorsunit == 1) {
                    $closedoors = get_string('closedoors_minutes', 'mod_hybridteaching', $hybridteaching->closedoorscount);
                    $multiplydoors = MINSECS;
                } else {
                    $closedoors = get_string('closedoors_seconds', 'mod_hybridteaching', $hybridteaching->closedoorscount);
                    $multiplydoors = 1;
                }
                $closedoorstime = $hybridteaching->closedoorscount * $multiplydoors;
            }

            //advanceentry (abrir puertas)
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
          
            //if starttime=0, can entry always depends on rol
            if ($hybridteaching->starttime == 0) {
                $canentry = true;
            } else {
                //if have advanceentrytime
                if (($timeinit - $advanceentrytime) < time()) {
                    if ($isclosedoors) {
                        //if have cloors
                        if (($timeinit + $closedoorstime) > time()) {
                            $canentry = true;
                        }
                    } else {
                        if ($timeend > time()) {  //if not closedoors and not finished 
                            $canentry = true;
                        }
                    }
                }
            }
            
            if ($hybridteaching->instance && $config_exists){
                // AQUI CREAR LA VC SI NO EXISTE

                //obtener el tipo de vc dependiendo del campo instance.
                //$subplugin_instance=$DB->get_record('hybridteaching_instances',['id' => $hybridteaching->instance, 'visible' => 1]);
                
                require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$config_exists->type.'/classes/subobject.php');
                require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$config_exists->type.'/classes/sessions.php');

                //aqui crear la vc si no existe ya.
                //1. comprobar si existe la vc en zoom, bbb...
                //2. si no existe, crearla
                    //2.1 primero comprobar si el usuario tiene permisos para crear la videoconferencia,
                        //si hay que esperar al moderador,  si tiene acceso para crear,...
                //3. si existe, obtenerla.


                //el futuro es convertir este subobject en el sessions.php de cada vc. Solo uno, para qué mas.....
                $subobject=new subobject($sessionshow->id);
                //si hay sessiones de vc creadas:  
                //(porque puede ser que sea programación de sesiones con vc y aun no se hayan creado las reuniones)

                //si hay reunión zoom creada, obtenerla. Sino, crearla si tiene permisos
              
                if ($subobject->get_sessions()){
                    $resultsaccess=$subobject->get_zone_access();
                    $result['url'] = $resultsaccess['url'];
                    $result['ishost'] = $resultsaccess['ishost'];
                }
                else {
                    //si hay sesión pero no hay vc, crearla si es el momento y si hay permisos:
                    //IMPORTANTE:
                    //FALTA COMPROBAR SI HAY PERMISOS: SI ES PROFESOR, O SI PUEDE ENTRAR ANTES QUE EL ANFITRION
                    if ($sessionshow && $canentry==true){ 
                        $sessionvc=new sessions();
                        $sessionvc->create_unique_session_extended($sessionshow, $hybridteaching);
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
