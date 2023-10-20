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
require_capability('mod/hybridteaching:view', $modulecontext);

global $USER;

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
$activesession = $session->get_next_session();
$result = [];
$hasvc = !empty($hybridteaching->typevc) ? true : false;
if (!$activesession) {
    $activesession = $session->get_last_session();
}

if (!$activesession) {
    if ($hybridteaching->undatedsession && has_capability('mod/hybridteaching:sessionsactions', $modulecontext)) {
        $result['id'] = $id;
        $result['hascreatecapability'] = true;
        $result['isundatedsession'] = true;
        $result['sessionexist'] = false;
        $result['isfinished'] = true;
        $result['hasvc'] = $hasvc;
        $result['target'] = $hasvc ? "_blank" : "_self";
        $result['url'] = base64_encode($url);
    } else {
        echo $OUTPUT->notification(get_string('nosessions', 'mod_hybridteaching', 'info'));
    }
} else {
    if (!groups_is_member($activesession->groupid, $USER->id) && $activesession->groupid != 0) {
        $access = false;
        echo $OUTPUT->notification(get_string('sessionnoaccess', 'mod_hybridteaching', 'info'));
    } else {
        $date = new DateTime();
        $date->setTimestamp(intval($activesession->starttime));
        $timeinit = $date->getTimestamp();
        $timeend = $timeinit + intval($activesession->duration);

        $isundatedsession = false;
        $isprogress = false;
        $isstart = false;
        $isfinished = false;
        $alert = 0;

        $viewupdate = false;
        switch (true) {
            case $hybridteaching->undatedsession:
                $isundatedsession = true;
                $isfinished = $activesession->isfinished;
                if ($isfinished) {
                    if (has_capability('mod/hybridteaching:sessionsactions', $modulecontext)) {
                        $status = get_string('status_undated', 'mod_hybridteaching');
                    } else {
                        $status = get_string('status_undated_wait', 'mod_hybridteaching');
                    }
                } else {
                    $status = get_string('status_ready', 'mod_hybridteaching');
                    $viewupdate = true;
                }
                $alert = 'alert-info';
                break;
            case $timeinit < time() && $timeend > time():
                $status = get_string('status_progress', 'mod_hybridteaching');
                $isprogress = true;
                $alert = 'alert-warning';
                $viewupdate = true;
                break;
            case $timeinit >= time():
                $status = get_string('status_start', 'mod_hybridteaching');
                $isstart = true;
                $alert = 'alert-info';
                $viewupdate = true;
                break;
            case $timeend < time():
                $status = get_string('status_finished', 'mod_hybridteaching');
                $isfinished = true;
                $alert = 'alert-danger';
                break;
        }
        $viewupdate && has_capability('mod/hybridteaching:attendanceregister', $modulecontext) && $hybridteaching->useattendance
            ? $PAGE->requires->js_call_amd('mod_hybridteaching/view', 'init', [$activesession->id, $USER->id]) : '';
        !isset($status) ? $status = '' : '';

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

        $result['isaccess'] = true;
        $result['id'] = $id;
        $result['s'] = !$isfinished ? $activesession->id : 0;
        $result['hascreatecapability'] = has_capability('mod/hybridteaching:sessionsactions', $modulecontext);
        $result['hasjoinurlcapability'] = has_capability('mod/hybridteaching:viewjoinurl', $modulecontext);
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
        $result['admreusesession'] = get_config('hybridteaching', 'reusesession');
        $result['instance'] = $hybridteaching->id;
        $result['useqr'] = $hybridteaching->useqr;
        $result['usepassword'] = $hybridteaching->studentpassword != '';
        $result['rotateqr'] = $hybridteaching->rotateqr;
        $result['useattendance'] = $hybridteaching->useattendance;
    }
}

echo $renderer->zone_access($result);
echo $OUTPUT->footer();
