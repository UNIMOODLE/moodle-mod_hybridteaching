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

define('NO_OUTPUT_BUFFERING', true);

require_once('../../../../config.php');
require_once('../controller/sessions_controller.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/notify_controller.php');
require_once('../output/sessions_render.php');
require_once('../form/sessions_form.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$moduleid = required_param('id', PARAM_INT);
$hybridteachingid = required_param('h', PARAM_INT);
$sesionid = optional_param('sid', 0, PARAM_INT);
$slist = optional_param('l', 1, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($moduleid, 'hybridteaching');

$hybridteaching = $DB->get_record('hybridteaching', ['id' => $cm->instance], '*', MUST_EXIST);
$url = new moodle_url('/mod/hybridteaching/classes/action/session_action.php');
$PAGE->set_url($url);
$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);

require_login();

$returnparams = [
    'id' => $moduleid,
    'l' => $slist,
];

$return = new moodle_url('/mod/hybridteaching/sessions.php', $returnparams);
$hybridteaching = $DB->get_record('hybridteaching', ['id' => $hybridteachingid], '*', MUST_EXIST);
$sessioncontroller = new sessions_controller($hybridteaching, 'hybridteaching_session');
$sessionslist = $sessioncontroller->load_sessions();
$mform = null;
if ($sesionid) {
    $session = $DB->get_record('hybridteaching_session', ['id' => $sesionid], '*', MUST_EXIST);
}

switch ($action) {
    case 'disable':
        $sessioncontroller->enable_data($sesionid, false, 'hybridteaching_session');
        break;
    case 'enable':
        $sessioncontroller->enable_data($sesionid, true, 'hybridteaching_session');
        break;
    case 'delete':
        if ($session->isfinished ||(time() < $session->starttime
              || time() > ($session->starttime + $session->duration))) {
            $sessioncontroller->delete_session($sesionid, $hybridteachingid);
        } else {
            notify_controller::notify_problem(get_string('error:deleteinprogress', 'hybridteaching'));
        }
        break;
    case 'visiblerecord':
        $sessioncontroller->set_record_visibility($sesionid);
        break;
    case 'bulkupdateduration':
    case 'bulkupdatestarttime':
        $sessid = optional_param_array('session', '', PARAM_SEQUENCE);
        $ids = optional_param('ids', '', PARAM_ALPHANUMEXT);

        $PAGE->set_title(format_string($hybridteaching->name));
        $PAGE->set_heading(format_string($course->fullname));

        $sesslist = !empty($sessid) ? implode('_', $sessid) : '';
        $params = [
            'id' => $moduleid,
            'action' => $action,
        ];

        $formparams = compact('sesslist', 'cm', 'hybridteaching', 'slist');
        $sessionrender = new hybridteaching_sessions_render($hybridteaching, $slist);
        $mform = ($action === 'bulkupdateduration') ? new bulk_update_duration_form($url, $formparams)
            : new bulk_update_starttime_form($url, $formparams);

        if ($mform->is_cancelled()) {
            redirect($return);
        }

        if ($formdata = $mform->get_data()) {
            $sessioncontroller->update_multiple_session(explode('_', $formdata->ids), $formdata);
            redirect($return);
        }

        if ($slist === '') {
            throw new moodle_exception('nosessionsselected', 'mod_hybridteaching', $return);
        }

        echo $OUTPUT->header();
        echo $sessionrender->print_sessions_bulk_table($sessid);
        echo $mform->display();
        echo $OUTPUT->footer();
        break;
    case 'bulkdelete':
        $confirm = optional_param('confirm', null, PARAM_INT);
        $message = get_string('deletecheckfull', 'hybridteaching', get_string('sessions', 'hybridteaching'));

        if (isset($confirm) && confirm_sesskey()) {
            $sessionsids = required_param('session', PARAM_ALPHANUMEXT);
            $sessionsids = explode('_', $sessionsids);
            foreach ($sessionsids as $sessid) {
                $session = $DB->get_record('hybridteaching_session', ['id' => $sessid], '*', MUST_EXIST);
                if ($session->isfinished || (time() < $session->starttime
                    || time() > ($session->starttime + $session->duration))) {
                    $sessioncontroller->delete_session($sessid);
                } else {
                    notify_controller::notify_problem(get_string('error:deleteinprogress', 'hybridteaching'));
                }
            }
            redirect($return, get_string('sessiondeleted', 'hybridteaching'));
        }
        $sessid = optional_param_array('session', '', PARAM_SEQUENCE);
        if (empty($sessid)) {
            throw new moodle_exception('nosessionsselected', 'mod_hybridteaching', $return);
        }

        list($extrasql, $params) = $DB->get_in_or_equal($sessid, SQL_PARAMS_NAMED, 'id');
        $extrasql = 'id ' . $extrasql;
        $sessionsinfo = $sessioncontroller->load_sessions(0, 0, $params, $extrasql);

        $message .= html_writer::empty_tag('br');
        foreach ($sessionsinfo as $sessinfo) {
            $message .= html_writer::empty_tag('br');
            $message .= userdate($sessinfo['starttime'], get_string('strftimedmyhm', 'hybridteaching'));
            $message .= html_writer::empty_tag('br');
            $message .= $sessinfo['name'];
        }

        $sessionsids = implode('_', $sessid);
        $params = ['action' => 'bulkdelete', 'session' => $sessionsids,
                        'confirm' => 1, 'sesskey' => sesskey(), 'id' => $moduleid,
                        'l' => $slist, 'h' => $hybridteachingid, ];

        $url = new moodle_url('/mod/hybridteaching/classes/action/session_action.php', $params);
        echo $OUTPUT->header();
        echo $OUTPUT->confirm($message, $url, $return);
        echo $OUTPUT->footer();
        break;
    default:
        redirect($return);
        break;
}


if (empty($mform)) {
    redirect($return);
}
