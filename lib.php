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
 * Library of interface functions and constants.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function hybridteaching_supports($feature) {
    switch ($feature) {
        case FEATURE_IDNUMBER:
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
        //case FEATURE_ADVANCED_GRADING:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_hybridteaching into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_hybridteaching_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function hybridteaching_add_instance($moduleinstance, $mform = null) {
    global $CFG, $DB;

    //dividir el select typevc en instance y typevc
    $moduleinstance->timecreated = time();
    $divide = explode('-', $moduleinstance->typevc, 2);
    $moduleinstance->instance = $divide[0];
    $moduleinstance->typevc = $divide[1];

    

    // CASOS:
    // 1. SI OPCIÓN MARCADA DE "USAR PROGRAMACION DE SESIONES" => crear dentro las vc, no aquí
    // 2. SI OPCIÓN "USAR PROGRAMACION DE SESIONES" NO MARCADA 
    //     Y OPCION "PERMITR ACCESO EN CUALQUIER MOMENTO" NO MARCADA   => crear aqui la vc con una fecha asignada ya
    //
    //    pero si OPCIÓN "PERMITIR ACCESO EN CUALQUIER MOMENTO" SÍ MARCADA => crear aqui la vc sin fecha, como reunión recurrente

    $moduleinstance->hybridteachingid = $DB->insert_record('hybridteaching', $moduleinstance);
    
    //opción "Usar acceso por videoconferencia"
    if ($moduleinstance->usevideoconference){
        //opción "Usar programación de sesiones" está sin marcar  
        if (!$moduleinstance->sessionscheduling) {
            if ($moduleinstance->typevc != ''){
                require_once($CFG->dirroot.'/mod/hybridteaching/vc/'.$moduleinstance->typevc.'/classes/sessions.php');      

                $sessions = new sessions();
                $result = $sessions->create_session($moduleinstance);
            }
        }
    }
    $moduleinstance->id=$moduleinstance->hybridteachingid;

    //TO-DO: repasar estas funciones para ver cómo incluirlas
    //hybridteaching_calendar_item_update($hybridteaching);
    //hybridteaching_grade_item_update($moduleinstance);

    return $moduleinstance->id;
}

/**
 * Updates an instance of the mod_hybridteaching in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_hybridteaching_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function hybridteaching_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

//TO-DO: repasar estas funciones para ver cómo incluirlas
    //hybridteaching_calendar_item_update($hybridteaching);
    //hybridteaching_grade_item_update($moduleinstance);

    return $DB->update_record('hybridteaching', $moduleinstance);
    //llamar aqui al subplugin si hubiera que actualizar algo del subplugin(fecha, horario,...)
}

/**
 * Removes an instance of the mod_hybridteaching from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function hybridteaching_delete_instance($id) {
    global $CFG, $DB;

    $existshybrid = $DB->get_record('hybridteaching', array('id' => $id));
    if (!$existshybrid) {
        return false;
    }
    
    //opción "Usar programación de sesiones" está sin marcar
    if (!$existshybrid->sessionscheduling && $existshybrid->typevc != ''){
        require_once($CFG->dirroot.'/mod/hybridteaching/vc/hybrid'.$existshybrid->typevc.'/classes/sessions.php');    
        $sessions = new sessions();
        $sessions->delete_all_sessions($existshybrid);
    }

    $DB->delete_records('hybridteaching', array('id' => $id));

    //TO-DO: repasar estas funciones para ver cómo incluirlas
    //hybridteaching_calendar_item_delete($hybridteaching);
    hybridteaching_grade_item_delete($existshybrid);

    return true;
}


/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@see file_browser::get_file_info_context_module()}.
 *
 * @package     mod_hybridteaching
 * @category    files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return string[].
 */
function hybridteaching_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for mod_hybridteaching file areas.
 *
 * @package     mod_hybridteaching
 * @category    files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info Instance or null if not found.
 */
function hybridteaching_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the mod_hybridteaching file areas.
 *
 * @package     mod_hybridteaching
 * @category    files
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The mod_hybridteaching's context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 */
function hybridteaching_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    send_file_not_found();
}


/**
 * Add a get_coursemodule_info function in case any forum type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function hybridteaching_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    

    $fields = 'id, name, intro, introformat, completionattendance';
    if (!$hybridteaching = $DB->get_record('hybridteaching', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $hybridteaching->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('hybridteaching', $hybridteaching, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        //$result->customdata['customcompletionrules']['completioncalculation'] = $hybridteaching->completioncalculation;
        $result->customdata['customcompletionrules']['completionattendance'] = $hybridteaching->completionattendance;
    }

    return $result;
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_hybridteaching_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (empty($cm->customdata['customcompletionrules']) || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionattendance':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionattendancedesc', 'forum', $val);
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}

/**
 * Checks if scale is being used by any instance of hybridteaching
 *
 * This is used to find out if scale used anywhere
 * @param int $scaleid
 * @return boolean True if the scale is used by any hybridteaching
 */
function hybridteaching_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('hybridteaching', array('grade'=>-$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Create grade item for given hybridteaching.
 *
 * @param stdClass $hybridteaching record with extra cmidnumber
 * @param array $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function hybridteaching_grade_item_update($hybridteaching, $grades=null) {

//TO-DO: REVISAR ESTA FUNCION PARA VER QUE HACE INSERTANDO CALIFICACIONES    
    
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = array('itemname'=>$hybridteaching->name, 'idnumber'=>$hybridteaching->cmidnumber);

    if ($hybridteaching->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $hybridteaching->grade;
        $params['grademin']  = 0;
    } else if ($hybridteaching->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$hybridteaching->grade;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/hybridteaching', $hybridteaching->course, 'mod', 'hybridteaching', $hybridteaching->id, 0, $grades, $params);
}

/**
 * Update activity grades.
 *
 * @param stdClass $hybridteaching database record
 * @param int $userid specific user only, 0 means all
 * @param bool $nullifnone - not used
 */
function hybridteaching_update_grades($hybridteaching, $userid=0, $nullifnone=true) {

// TO-DO: REVISAR ESTA FUNCION PARA VER QUE HACE
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    if ($asshybridteachingign->grade == 0) {
        hybridteaching_grade_item_update($hybridteaching);

    } else if ($grades = hybridteaching_get_user_grades($hybridteaching, $userid)) {
        foreach ($grades as $k => $v) {
            if ($v->rawgrade == -1) {
                $grades[$k]->rawgrade = null;
            }
        }
        hybridteaching_grade_item_update($hybridteaching, $grades);

    } else {
        hybridteaching_grade_item_update($hybridteaching);
    }
}


/**
 * Return grade for given user or all users.
 *
 * @param stdClass $hybridteaching record of assign with an additional cmidnumber
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function hybridteaching_get_user_grades($hybridteaching, $userid=0) {
    global $CFG;

    require_once($CFG->dirroot . '/mod/hybridteaching/locallib.php');

    $cm = get_coursemodule_from_instance('hybridteaching', $hybridteaching->id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $hybrid = new hybridteaching($context, null, null);
    $hybrid->set_instance($hybridteaching);
    return $hybrid->get_user_grades_for_gradebook($userid);
}

/**
 * Delete grade item for given hybridteaching instance
 *
 * @param stdClass $hybridteaching instance object
 * @return grade_item
 */
function hybridteaching_grade_item_delete($hybridteaching) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/hybridteaching', $hybridteaching->course, 'mod', 'hybridteaching',
            $hybridteaching->id, 0, null, array('deleted' => 1));
}

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param navigation_node $hybridteachingnode The node to add module settings to
 */
function hybridteaching_extend_settings_navigation(settings_navigation $settingsnav,
    navigation_node $hybridteachingnode) {
    global $DB;

    $context = $settingsnav->get_page()->cm->context;
    $cm = $settingsnav->get_page()->cm;
    $nodes = [];

    $hassessionsscheduling=$DB->get_field('hybridteaching','sessionscheduling',array('id' => $cm->instance));
    if (has_capability('mod/hybridteaching:sessions', $context)) {
        $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/sessions.php', ['id' => $cm->id, 'h' => $cm->instance]),
                    'title' => get_string('sessions', 'hybridteaching')];
    }

    if (has_capability('mod/hybridteaching:attendance', $context)) {
        $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/attendance.php', ['id' => $cm->id]),
                    'title' => get_string('attendance', 'hybridteaching')];
    }


    if (has_capability('mod/hybridteaching:programschedule', $context)) {
        //mostrar solo pestaña de "Programación de sesiones" solo si esta VC tiene marcada la opción de "usar programación de sesiones"
        $hassessionsscheduling=$DB->get_field('hybridteaching','sessionscheduling',array('id' => $cm->instance));
        if ($hassessionsscheduling){
            $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/programschedule.php', ['id' => $cm->id, 'h' => $cm->instance]),
                        'title' => get_string('programschedule', 'hybridteaching')];
        }
    }

    if (has_capability('mod/hybridteaching:import', $context)) {
        $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/import.php', ['id' => $cm->id]),
                    'title' => get_string('import', 'hybridteaching')];
    }

    if (has_capability('mod/hybridteaching:export', $context)) {
        $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/export.php', ['id' => $cm->id]),
                    'title' => get_string('export', 'hybridteaching')];
    }

    foreach ($nodes as $node) {
        $settingsnode = navigation_node::create($node['title'], $node['url'], navigation_node::TYPE_SETTING);
        if (isset($settingsnode)) {
            if (!empty($node->more)) {
                $settingsnode->set_force_into_more_menu(true);
            }
            $hybridteachingnode->add_node($settingsnode);
        }
    }
}
