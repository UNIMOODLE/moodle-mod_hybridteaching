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


require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helper.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/notify_controller.php');
require_once($CFG->dirroot . '/lib/grouplib.php');
use mod_hybridteaching\helpers\roles;

$id = optional_param('id', 0, PARAM_INT);
$h = optional_param('h', 0, PARAM_INT);
$err = optional_param('err', 0, PARAM_INT);

if ($id) {
    list ($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
    $hybridteaching = $DB->get_record('hybridteaching', ['id' => $cm->instance], '*', MUST_EXIST);
} else if ($h) {
    $hybridteaching = $DB->get_record('hybridteaching', ['id' => $h], '*', MUST_EXIST);
    list ($course, $cm) = get_course_and_cm_from_instance($h,  'teamsmeeting');
}
if (!isset($cm) || !$cm) {
    throw new moodle_exception('view_error_url_missing_parameters', 'hybridteaching');
}

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);
require_capability('mod/hybridteaching:view', $modulecontext);

global $USER;

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$urlparams = ['id' => $cm->id, 'h' => $hybridteaching->id];
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
$activesession = $session->get_next_session();
$result = [];
$hasvc = !empty($hybridteaching->typevc) ? true : false;
if (!$activesession) {
    $activesession = $session->get_last_session();
}
if (has_capability('mod/hybridteaching:sessionsactions', $modulecontext)) {
    $session->finish_unfinished_sessions($hybridteaching->id);
}
if (!$activesession) {
    if (!$hybridteaching->sessionscheduling && has_capability('mod/hybridteaching:sessionsactions', $modulecontext)) {
        $result['id'] = $id;
        $result['hascreatecapability'] = true;
        $result['isundatedsession'] = true;
        $result['sessionexist'] = false;
        $result['isfinished'] = true;
        $result['hasvc'] = $hasvc;
        $result['target'] = $hasvc ? "_blank" : "_self";
        $result['url'] = base64_encode($url);
    } else {
        echo $OUTPUT->notification(get_string('nosessions', 'hybridteaching', 'info'));
    }
} else {
    if (!has_capability('mod/hybridteaching:sessionsactions', $modulecontext) &&
          !groups_is_member($activesession->groupid, $USER->id) && $activesession->groupid != 0) {
        $access = false;
        echo $OUTPUT->notification(get_string('sessionnoaccess', 'hybridteaching', 'info'));
    } else {
        $timeinit = $activesession->starttime;
        $timeend = $timeinit + $activesession->duration;

        // Closedoors.
        $closedoors = '';
        $isclosedoors = false;
        $closedoorstime = 0;
        if ($hybridteaching->closedoorscount > 0) {
            $isclosedoors = true;
            switch ($hybridteaching->closedoorsunit) {
                case 0:
                    $closedoors = get_string('closedoors_hours', 'hybridteaching', $hybridteaching->closedoorscount);
                    $multiplydoors = HOURSECS;
                    break;
                case 1:
                    $closedoors = get_string('closedoors_minutes', 'hybridteaching', $hybridteaching->closedoorscount);
                    $multiplydoors = MINSECS;
                    break;
                default:
                    $closedoors = get_string('closedoors_seconds', 'hybridteaching', $hybridteaching->closedoorscount);
                    $multiplydoors = 1;
                    break;
            }
            $closedoorstime = $hybridteaching->closedoorscount * $multiplydoors;
        }
        $isundatedsession = false;
        $isprogress = false;
        $isstart = false;
        $isfinished = false;
        $alert = 0;

        $viewupdate = false;
        switch (true) {
            case !$hybridteaching->sessionscheduling:
                $isundatedsession = true;
                $isfinished = $activesession->isfinished;
                if ($isfinished) {
                    if (has_capability('mod/hybridteaching:sessionsactions', $modulecontext)) {
                        $status = get_string('status_undated', 'hybridteaching');
                    } else {
                        $status = get_string('status_undated_wait', 'hybridteaching');
                    }
                } else {
                    $status = get_string('status_start', 'hybridteaching');
                    if (($activesession->starttime < time() && $activesession->isfinished == 0)) {
                        $isprogress = true;
                        $viewupdate = true;
                        $isclosedoors ? $status = get_string('status_progress', 'hybridteaching') : 
                            $status = get_string('status_ready', 'hybridteaching');
                    } else if ($activesession->starttime > time()) {
                        $duration = $activesession->duration;
                        $activesession = $session->get_last_undated_session();
                        if (!empty($activesession)) {
                            $isprogress = true;
                            $viewupdate = true;
                            $timeinit = $activesession->starttime;
                            $isclosedoors ? $status = get_string('status_progress', 'hybridteaching') : 
                                $status = get_string('status_ready', 'hybridteaching');
                        } else {
                            $isstart = true;
                            $isfinished = true;
                            $nextsessduration = $duration;
                        }
                    } else {
                        $isprogress = true;
                            $viewupdate = true;
                            $timeinit = $activesession->starttime;
                            $isclosedoors ? $status = get_string('status_progress', 'hybridteaching') : 
                                $status = get_string('status_ready', 'hybridteaching');
                    }
                }
                $isclosedoors ? $alert = 'alert-warning' : $alert = 'alert-info';
                break;
            case $timeinit < time() && $timeend > time():
                $isclosedoors ? $status = get_string('status_progress', 'hybridteaching') : 
                    $status = get_string('status_ready', 'hybridteaching');
                $isprogress = true;
                $alert = 'alert-warning';
                $viewupdate = true;
                break;
            case $timeinit >= time():
                $status = get_string('status_start', 'hybridteaching');
                $isstart = true;
                $alert = 'alert-info';
                break;
            case $timeend < time():
                $status = get_string('status_finished', 'hybridteaching');
                $isfinished = true;
                $alert = 'alert-danger';
                if (!$session::session_finished_triggered($activesession->id)) {
                    list($course, $cm) = get_course_and_cm_from_instance($hybridteaching->id, 'hybridteaching');
                    $event = \mod_hybridteaching\event\session_finished::create([
                        'objectid' => $hybridteaching->id,
                        'context' => \context_module::instance($cm->id),
                        'other' => [
                            'sessid' => $activesession->id,
                        ],
                    ]);
                    $event->trigger();
                }

                break;
        }
        !isset($status) ? $status = '' : '';

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
        if (($timeinit - $advanceentrytime) < time()) {
            if ($isclosedoors) {
                if (($timeinit + $closedoorstime) > time()) {
                    $canentry = true;
                }
            } else {
                if ($timeend > time()) {
                    $canentry = true;
                } else {
                    if ($isundatedsession && $timeend == $timeinit) $canentry = true;
                }
            }
        }
        $result['hasjoinurlcapability'] = has_capability('mod/hybridteaching:viewjoinurl', $modulecontext);
        if ($hybridteaching->waitmoderator) {
            $role = roles::is_moderator($modulecontext, json_decode($hybridteaching->participants, true), $USER->id);
            $issessionmoderator = ($role || has_capability('mod/hybridteaching:sessionsactions', $modulecontext));
            if (!$issessionmoderator && $canentry ) {
                if (!$session->session_started($activesession)) {
                    // Wait moderator.
                    $result['waitmoderator'] = true;
                    $canentry = false;
                }
            }
        } else if ($canentry && !$session->session_started($activesession)) {
            $role = roles::is_moderator($modulecontext, json_decode($hybridteaching->participants, true), $USER->id);
            $issessionmoderator = ($role || has_capability('mod/hybridteaching:sessionsactions', $modulecontext));
            $result['hasjoinurlcapability'] = $issessionmoderator;
        }
        if (!has_capability('mod/hybridteaching:sessionsactions', $modulecontext) && !$session::get_sessionconfig_exists($activesession)
            && $isundatedsession && $hybridteaching->usevideoconference) {
            $viewupdate = false;
            $canentry = false;
            $status = get_string('status_undated_wait', 'hybridteaching');
        }
        $viewupdate && has_capability('mod/hybridteaching:attendanceregister', $modulecontext) && $hybridteaching->useattendance
            && $canentry ? $PAGE->requires->js_call_amd('mod_hybridteaching/view', 'init', [$activesession->id, $USER->id]) : '';

        $result['isaccess'] = true;
        $result['id'] = $id;
        $result['s'] = !$isfinished ? $activesession->id : 0;
        $result['hascreatecapability'] = has_capability('mod/hybridteaching:sessionsactions', $modulecontext);
        $result['hasvc'] = $hasvc;
        $result['target'] = $hasvc ? "_blank" : "_self";
        $result['onsubmit'] = $result['target'] == "_blank" ? "setTimeout(function() { location.reload(); }, 4000)" : "";
        $result['sessionexist'] = true;
        $result['url'] = base64_encode($url);
        $result['isundatedsession'] = $isundatedsession;
        $result['isprogress'] = $isprogress;
        $result['isstart'] = $isstart;
        $result['isfinished'] = $isfinished;
        $result['starttime'] = userdate($timeinit);
        $result['finished'] = userdate($timeend);
        if (!empty($activesession->duration)) {
            $result['duration'] = helper::get_hours_format($activesession->duration);
        } else {
            if (!empty($nextsessduration)) {
                $result['duration'] = helper::get_hours_format($nextsessduration);
            } else {
                $result['duration'] = 'Sin definir';
            }
        }
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
        $result['admreusesession'] = get_config('hybridteaching', 'reusesession');
        $result['instance'] = $hybridteaching->id;
        $result['useqr'] = $hybridteaching->useqr;
        $result['usepassword'] = $hybridteaching->studentpassword != '';
        $result['rotateqr'] = $hybridteaching->rotateqr;
        $result['useattendance'] = $hybridteaching->useattendance;
    }
}
if ($err == 1) {
    // Code 1: error_unable_join: The meeting does not exist in the vc system, has been deleted, or has not been found.
    $result['message'] = get_string('error_unable_join', 'hybridteaching');
    $result['alert'] = 'alert-danger';
}
notify_controller::show();
echo $renderer->zone_access($result);
echo $OUTPUT->footer();
