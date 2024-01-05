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


use mod_hybridteaching\plugininfo\hybridteachvc;

define('SESSION_LIST', 1);
define('PROGRAM_SESSION_LIST', 2);

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
            // ...case FEATURE_MOD_PURPOSE
            // MOD_PURPOSE_COMMUNICATION
        case FEATURE_BACKUP_MOODLE2:
            // ...case FEATURE_ADVANCED_GRADING.
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

    $moduleinstance->timecreated = time();
    if ($moduleinstance->usevideoconference == 1) {
        $divide = isset($moduleinstance->typevc) ? explode('-', $moduleinstance->typevc, 2) : [];
        $moduleinstance->config = isset($divide[0]) ? $divide[0] : 0;
        $moduleinstance->typevc = isset($divide[1]) ? $divide[1] : '';
    } else {
        $moduleinstance->config = 0;
        $moduleinstance->typevc = '';
    }

    /*if session recording is used, userecordvc, the recording must be processed with processedrecording*/
    /*processedrecording:
        -1: must process with vc
        0: processed and downloaded with vc, ready to upload to storage
        >1: uploaded to storage
    */
    if ($moduleinstance->userecordvc == 1) {
        $moduleinstance->processedrecording = -1;
    }

    if (empty($moduleinstance->duration) && !$moduleinstance->sessionscheduling) {
        $moduleinstance->starttime = time();
    }

    $moduleinstance->id = $DB->insert_record('hybridteaching', $moduleinstance);
    if (!$moduleinstance->sessionscheduling && !empty($moduleinstance->id)) {
        require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
        $sessioncontroller = new sessions_controller($moduleinstance);
        $moduleinstance->groupid = 0;
        $session = (object) $sessioncontroller->create_session($moduleinstance);

        if (!empty($moduleinstance->typevc) && !empty($moduleinstance->usevideoconference)) {
            $classname = sessions_controller::get_subpluginvc_class($moduleinstance->typevc);
            $sessionvc = new $classname($session->id);
            $sessionvc->create_unique_session_extended($session, $moduleinstance);
        }
    }

    hybridteaching_grade_item_update($moduleinstance);

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
    global $CFG, $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    if ($moduleinstance->usevideoconference == 1) {
        $divide = isset($moduleinstance->typevc) ? explode('-', $moduleinstance->typevc, 2) : [];
        $moduleinstance->config = isset($divide[0]) ? $divide[0] : 0;
        $moduleinstance->typevc = isset($divide[1]) ? $divide[1] : '';
    } else {
        $moduleinstance->config = 0;
        $moduleinstance->typevc = '';
    }

    // If there is not sessionscheduling, change time in the unique session.
    $hadsessionsscheduling = $DB->get_field('hybridteaching', 'sessionscheduling', ['id' => $moduleinstance->id]);
    if ($moduleinstance->sessionscheduling == 0 && $hadsessionsscheduling == 1) {
        require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
        $session = new sessions_controller($moduleinstance);
        $session->update_session($moduleinstance);
    }

    if (!$DB->update_record('hybridteaching', $moduleinstance)) {
        return false;
    }

    hybridteaching_grade_item_update($moduleinstance);

    return true;
}

/**
 * Removes an instance of the mod_hybridteaching from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function hybridteaching_delete_instance($id) {
    global $CFG, $DB;

    $existshybrid = $DB->get_record('hybridteaching', ['id' => $id]);
    if (!$existshybrid) {
        return false;
    }

    require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
    $sessions = new sessions_controller($existshybrid);
    $sessions->delete_all_sessions();

    $attendance = $DB->get_records('hybridteaching_attendance', ['hybridteachingid' => $id]);
    foreach ($attendance as $att) {
        $DB->delete_records('hybridteaching_attend_log', ['attendanceid' => $att->id]);
    }

    $DB->delete_records('hybridteaching_attendance', ['hybridteachingid' => $id]);
    $DB->delete_records('hybridteaching', ['id' => $id]);

    // TO-DO: repasar estas funciones para ver cómo incluirlas
    // hybridteaching_calendar_item_delete($hybridteaching);.
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
    return [];
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
function mod_hybridteaching_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = []) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);
    if (!has_capability('mod/hybridteaching:view', $context)) {
        return false;
    }

    if ($filearea !== 'session' && $filearea !== 'chats') {
        return false;
    }

    $itemid = (int)array_shift($args);
    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/mod_hybridteaching/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
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
        // ...$result->customdata['customcompletionrules']['completioncalculation'] = $hybridteaching->completioncalculation;
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

    if ($scaleid && $DB->record_exists('hybridteaching', ['grade' => -$scaleid])) {
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
function hybridteaching_grade_item_update($hybridteaching, $grades = null) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $params = ['itemname' => $hybridteaching->name, 'idnumber' => $hybridteaching->cmidnumber];

    if ($hybridteaching->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $hybridteaching->grade;
        $params['grademin'] = 0;
    } else if ($hybridteaching->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$hybridteaching->grade;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/hybridteaching', $hybridteaching->course, 'mod',
        'hybridteaching', $hybridteaching->id, 0, $grades, $params);
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
        $hybridteaching->id, 0, null, ['deleted' => 1]);
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

    $hassessionsscheduling = $DB->get_field('hybridteaching', 'sessionscheduling', ['id' => $cm->instance]);
    if (has_capability('mod/hybridteaching:sessions', $context)) {
        $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/sessions.php', ['id' => $cm->id, 'l' => SESSION_LIST]),
                    'title' => get_string('sessions', 'hybridteaching'), ];
    }

    if (has_capability('mod/hybridteaching:attendance', $context)) {
        $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/attendance.php', ['id' => $cm->id]),
                    'title' => get_string('attendance', 'hybridteaching'), ];
    }

    if (has_capability('mod/hybridteaching:programschedule', $context)) {
        // Mostrar solo pestaña de "Programación de sesiones" solo si esta VC
        // ...tiene marcada la opción de "usar programación de sesiones".
        $hassessionsscheduling = $DB->get_field('hybridteaching', 'sessionscheduling', ['id' => $cm->instance]);
        if ($hassessionsscheduling) {
            $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/sessions.php', ['id' => $cm->id, 'l' => PROGRAM_SESSION_LIST]),
                        'title' => get_string('programschedule', 'hybridteaching'), ];
        }
    }

    if (has_capability('mod/hybridteaching:import', $context)) {
        $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/import.php', ['id' => $cm->id]),
                    'title' => get_string('import', 'hybridteaching'), ];
    }

    if (has_capability('mod/hybridteaching:export', $context)) {
        $nodes[] = ['url' => new moodle_url('/mod/hybridteaching/export.php', ['id' => $cm->id]),
                    'title' => get_string('export', 'hybridteaching'), ];
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

function hybrid_build_category_array($category) {
    $categoryarray = [
        'id' => $category->id,
        'name' => format_text($category->name),
        'categories' => [],
    ];

    $subcategories = $category->get_children();
    foreach ($subcategories as $subcategory) {
        $subcategoryarray = hybrid_build_category_array($subcategory);
        if (!empty($subcategoryarray)) {
            $categoryarray['categories'][] = $subcategoryarray;
        }
    }

    $categoryarray = array_filter($categoryarray, function($array) {
        return !empty($array);
    });

    return $categoryarray;
}

function hybrid_build_output_categories($arraycategories, $categoryid = 0) {
    $output = "";
    foreach ($arraycategories as $key => $category) {
        $output .= html_writer::start_tag("li", [
            "id" => "listitem-category-" . $category["id"],
            "class" => "listitem listitem-category list-group-item list-group-item-action collapsed",
        ]);
        $output .= html_writer::start_div("", ["class" => "category-listing-header d-flex"]);
        $output .= html_writer::start_div("", ["class" => "custom-control custom-checkbox mr-1"]);
        $output .= html_writer::tag("input", "", [
            "id" => "checkboxcategory-" . $category["id"],
            "type" => "checkbox", "class" => "custom-control-input",
            "data-parent" => "#category-listing-content-" . $categoryid,
        ]);
        $output .= html_writer::tag(
            "label", "",
            ["class" => "custom-control-label", "for" => "checkboxcategory-" . $category["id"]]
        );
        $output .= html_writer::end_div();// ... .custom-checkbox
        $output .= html_writer::start_div("", [
            "class" => "d-flex px-0", "data-toggle" => "collapse",
            "data-target" => "#category-listing-content-" . $category["id"],
            "aria-controls" => "category-listing-content-" . $category["id"],
        ]);
        $output .= html_writer::start_div("", ["class" => "categoryname d-flex align-items-center"]);
        $output .= $category["name"];
        if (!empty($category["categories"])) {
            $output .= html_writer::tag("i", "", ["class" => "fa fa-angle-down ml-2"]);
        }
        $output .= html_writer::end_div();// ....categoryname
        $output .= html_writer::end_div();// ... .data-toggle
        $output .= html_writer::end_div();// ... .d-flex
        $output .= html_writer::start_tag("ul", [
            "id" => "category-listing-content-" . $category["id"],
            "class" => "collapse", "data-parent" => "#category-listing-content-" . $categoryid,
        ]);
        if (!empty($category["categories"])) {
            $output .= hybrid_build_output_categories($category["categories"], $category["id"]);
        }
        $output .= html_writer::end_tag("ul");// ... #category-listing-content-x
        $output .= html_writer::end_tag("li");// ... .listitem.listitem-category.list-group-item
    }
    return $output;
}

function hybrid_get_categories_for_modal() {
    $categoriesall = core_course_category::top()->get_children();
    $categoryarray = [];
    foreach ($categoriesall as $cat) {
        $categoryarray[] = hybrid_build_category_array($cat);
    }

    if (!empty($categoryarray)) {
        $outputcategories = html_writer::start_div("", ["class" => "course-category-listing"]);
        $outputcategories .= html_writer::start_div("", ["class" => "header-listing"]);
        $outputcategories .= html_writer::start_div("", ["class" => "d-flex"]);
        $outputcategories .= html_writer::start_div("", ["class" => "custom-control custom-checkbox mr-1"]);
        $outputcategories .= html_writer::tag(
            "input", "", ["id" => "course-category-select-all", "type" => "checkbox", "class" => "custom-control-input"]
        );
        $outputcategories .= html_writer::tag("label", "", ["class" => "custom-control-label", "for" => "course-category-select-all"]);
        $outputcategories .= html_writer::end_div(); // ... .custom-checkbox
        $outputcategories .= html_writer::start_div("", ["class" => "col px-0 d-flex"]);
        $outputcategories .= html_writer::start_div("", ["class" => "header-categoryname"]);
        $outputcategories .= get_string('name', 'core');
        $outputcategories .= html_writer::end_div(); // ...... .header-categoryname
        $outputcategories .= html_writer::end_div(); // ... .col
        $outputcategories .= html_writer::end_div(); // ... .d-flex
        $outputcategories .= html_writer::end_div(); // ... .header-listing
        $outputcategories .= html_writer::start_div("", ["class" => "category-listing"]);
        $outputcategories .= html_writer::start_tag("ul", ["id" => "category-listing-content-0", "class" => "m-0 pl-0"]);
        $outputcategories .= hybrid_build_output_categories($categoryarray);
        $outputcategories .= html_writer::end_tag("ul"); // ... #category-listing-content-0
        $outputcategories .= html_writer::end_div(); // ... .category-listing
        $outputcategories .= html_writer::end_div(); // ... .course-category-listing

        $templatecontext['output_categoriescourses'] = $outputcategories;
    }

    return $templatecontext;
}
