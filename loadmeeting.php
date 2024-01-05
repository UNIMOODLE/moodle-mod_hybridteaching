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

use mod_hybridteaching\helpers\roles;
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(__DIR__.'/classes/controller/sessions_controller.php');
require_once(__DIR__.'/classes/controller/notify_controller.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/attendance_controller.php');

$id = required_param('id', PARAM_INT);
$url = optional_param('url', '', PARAM_RAW);
$sid = optional_param('s', 0, PARAM_INT);
$finishsession = optional_param('finishsession', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
$modulecontext = context_module::instance($cm->id);
$hybridteaching = $DB->get_record('hybridteaching', ['id' => $cm->instance], '*', MUST_EXIST);
$attendancecontroller = new attendance_controller($hybridteaching);
if (!empty($sid)) {
    $activesession = $DB->get_record('hybridteaching_session', ['id' => $sid], '*', MUST_EXIST);
    if (!$finishsession) {
        if ($activesession->typevc != $hybridteaching->typevc) {
            $activesession->typevc = $hybridteaching->typevc;
            $DB->update_record('hybridteaching_session', $activesession);
        }
        if ($activesession->vcreference != $hybridteaching->config) {
            $activesession->vcreference = $hybridteaching->config;
            $DB->update_record('hybridteaching_session', $activesession);
        }
        if ($activesession->starttime == 0) {
            $activesession->starttime = time();
            $DB->update_record('hybridteaching_session', $activesession);
        }

        if ($hybridteaching->config) {
            sessions_controller::require_subplugin_session($hybridteaching->typevc);
            $classname = sessions_controller::get_subpluginvc_class($hybridteaching->typevc);
            $sessionvc = new $classname($activesession->id);

            if (!empty($sessionvc->get_session())) {
                $resultsaccess = $sessionvc->get_zone_access();
                if ($resultsaccess == null ||
                    (isset($resultsaccess['returncode']) && $resultsaccess['returncode'] == 'FAILED') ) {
                        $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id, 'err' => 1]);
                        redirect($url);
                }
                $url = $resultsaccess['url'];
                $ishost = $resultsaccess['ishost'];
                if ($activesession->isfinished) {
                    $regex = '/=[A-Za-z0-9]+/i';
                    preg_match($regex, base64_decode($url), $match);
                    $meetingid = substr($match[0], 1);
                    if ($DB->get_record('hybridteachvc_' . $activesession->typevc,
                          ['meetingid' => $meetingid], 'id', IGNORE_MISSING)) {
                        $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);
                        redirect($url, get_string('finished', 'hybridteaching') . date(' d M Y H:i:s',
                            $activesession->starttime + $activesession->duration), null, 'error');
                    }
                }
            } else {
                $sessioncontroller = new sessions_controller($hybridteaching);
                $role = false;
                if ($hybridteaching->waitmoderator || !empty($hybridteaching->participants)) {
                    $role = roles::is_moderator($modulecontext, json_decode($hybridteaching->participants, true), $USER->id);
                }
                $issessionmoderator = ($role || has_capability('mod/hybridteaching:sessionsactions', $modulecontext));
                if ($activesession && $issessionmoderator) {
                    // If must save recordings, save the id of storage.
                    if ($activesession->userecordvc) {
                        $activesession->storagereference = sessions_controller::savestorage($activesession,
                            $hybridteaching->course);
                    }
                    if (!$sessioncontroller->get_sessionconfig_exists($activesession)) {
                        $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);
                        redirect($url, get_string('vcconfigremoved', 'hybridteaching'), null, 'error');
                    }
                    strlen($activesession->name) <= 1 ? $activesession->name .= '-' : '';
                    $sessionvc->create_unique_session_extended($activesession, $hybridteaching);
                    $sessionvc->set_session($activesession->id);
                    $resultsaccess = $sessionvc->get_zone_access();
                    if ($resultsaccess == null ||
                        (isset($resultsaccess['returncode']) && $resultsaccess['returncode'] == 'FAILED') ) {
                            $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id, 'err' => 1]);
                            redirect($url);
                    }
                    $url = $resultsaccess['url'];
                    $ishost = $resultsaccess['ishost'];
                }
            }
        }
    } else {
        if (time() > $activesession->starttime) {
            $activesession->isfinished = 1;
            $activesession->duration = time() - $activesession->starttime;
            $DB->update_record('hybridteaching_session', $activesession);
            $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);
            $url = base64_encode($url);

            list($course, $cm) = get_course_and_cm_from_instance($hybridteaching->id, 'hybridteaching');
            $event = \mod_hybridteaching\event\session_finished::create([
                'objectid' => $hybridteaching->id,
                'context' => \context_module::instance($cm->id),
                'other' => [
                    'sessid' => $activesession->id,
                ],
            ]);

            $event->trigger();
        } else {
            notify_controller::notify_problem(get_string('cantfinishunstarted', 'hybridteaching'));
        }
    }
} else {
    if (!$hybridteaching->sessionscheduling) {
        $sessioncontroller = new sessions_controller($hybridteaching);
        $session = new stdClass();
        $session->name = get_string('recurringses', 'hybridteaching') . ' ' . $hybridteaching->name;
        $session->groupid = 0;
        $session->starttime = time();
        $session->durationgroup['duration'] = 0;
        $session->durationgroup['timetype'] = 0;
        $session->typevc = $hybridteaching->typevc;
        $session->vcreference = $hybridteaching->config;
        $session = (object) $sessioncontroller->create_session($session);

        if (!empty($session->typevc)) {
            $classname = sessions_controller::get_subpluginvc_class($hybridteaching->typevc);
            $sessionvc = new $classname($session->id);
            if (!empty($sessionvc->get_session())) {
                if (!$sessioncontroller->get_sessionconfig_exists($session)) {
                    $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);
                    redirect($url, get_string('vcconfigremoved', 'hybridteaching'), null, 'error');
                }
                $resultsaccess = $sessionvc->get_zone_access();
                if ($resultsaccess == null ||
                    (isset($resultsaccess['returncode']) && $resultsaccess['returncode'] == 'FAILED') ) {
                        $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id, 'err' => 1]);
                        redirect($url);
                }
                $url = $resultsaccess['url'];
                $ishost = $resultsaccess['ishost'];
            } else {
                if (has_capability('mod/hybridteaching:sessionsactions', $modulecontext)) {
                    if ($activesession = $DB->get_record('hybridteaching_session', ['id' => $session->id], '*', MUST_EXIST)) {
                        // If must save recordings, save the id of storage.
                        if ($activesession->userecordvc) {
                            $activesession->storagereference = sessions_controller::savestorage($activesession,
                                $hybridteaching->course);
                        }
                    }
                    if (!$sessioncontroller->get_sessionconfig_exists($session)) {
                        $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);
                        redirect($url, get_string('vcconfigremoved', 'hybridteaching'), null, 'error');
                    }
                    $sessionvc->create_unique_session_extended($session, $hybridteaching);
                    $sessionvc->set_session($session->id);
                    $resultsaccess = $sessionvc->get_zone_access();
                    if ($resultsaccess == null ||
                        (isset($resultsaccess['returncode']) && $resultsaccess['returncode'] == 'FAILED') ) {
                            $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id, 'err' => 1]);
                            redirect($url);
                    }
                    $url = $resultsaccess['url'];
                    $ishost = $resultsaccess['ishost'];
                }
            }
        }
    }
}

$nexturl = base64_decode($url);

$event = \mod_hybridteaching\event\session_joined::create([
    'objectid' => $hybridteaching->id,
    'context' => $modulecontext,
]);

$event->trigger();

require_login($course->id, true);
if (!$finishsession && !has_capability('mod/hybridteaching:sessionsfulltable', $PAGE->context)) {
    isset($activesession) ? $sessiontype = $activesession : $sessiontype = $session;
    $attaction = optional_param('attaction', 1, PARAM_INT);
    if ($hybridteaching->useattendance) {
        $notify = attendance_controller::hybridteaching_set_attendance_log($hybridteaching, $sessiontype, 1);
        if ($notify['ntype'] == 'success') {
            attendance_controller::hybridteaching_set_attendance($hybridteaching, $sessiontype, 1, $attaction);
        }
        $url = new moodle_url('/mod/hybridteaching/view.php', ['id' => $id]);
        $notify['ntype'] == 'error' ? redirect($url, get_string($notify['gstring'], 'hybridteaching'), null, $notify['ntype']) :
        redirect($nexturl);
    }
}
redirect($nexturl);
