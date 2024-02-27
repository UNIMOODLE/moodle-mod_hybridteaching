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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\helper;

$cid = required_param('cid', PARAM_INT);
$sid = required_param('sid', PARAM_INT);
$id = required_param('id', PARAM_INT);
$download = optional_param('download', 0, PARAM_BOOL);
require_login($cid);

$session = $DB->get_record('hybridteaching_session', ['id' => $sid], '*', MUST_EXIST );

$cm = get_coursemodule_from_instance ('hybridteaching', $session->hybridteachingid);
$context = context_module::instance($cm->id);

$urlrecording = '';

// Get url recording if download is false.
if ($download == false) {
    if (has_capability('mod/hybridteaching:viewrecordings', $context)) {
        if ($session->userecordvc == 1 && $session->processedrecording >= 0) {
            if ($session->storagereference > 0) {
                $classstorage = sessions_controller::get_subpluginstorage_class($session->storagereference);
                $config = helper::subplugin_config_exists($session->storagereference, 'store');
                if ($config && $classstorage) {
                    sessions_controller::require_subplugin_store($classstorage['type']);
                    $classname = $classstorage['classname'];
                    $sessionrecording = new $classname();
                    $urlrecording = $sessionrecording->get_recording($session->processedrecording,
                        $session->storagereference, $session->hybridteachingid, $sid);

                    list($course, $cm) = get_course_and_cm_from_instance($session->hybridteachingid, 'hybridteaching');
                    $event = \mod_hybridteaching\event\session_record_viewed::create([
                        'objectid' => $session->hybridteachingid,
                        'context' => \context_module::instance($cm->id),
                        'other' => [
                            'sessid' => $sid,
                        ],
                    ]);

                    $event->trigger();
                }
            } else if ($session['storagereference'] == -1) {
                // For use case to BBB or a videconference type storage.
                $config = helper::subplugin_config_exists($session['vcreference'], 'vc');
                if ($config) {
                    sessions_controller::require_subplugin_session($session['typevc']);
                    $classname = sessions_controller::get_subpluginvc_class($session['typevc']);
                    $sessionrecording = new $classname($session['id']);
                    $urlrecording = $sessionrecording->get_recording($session['id']);
                }
            }
        }
    }
} else {
    // Download recording if download is true.
    if (has_capability('mod/hybridteaching:downloadrecordings', $context)) {
        if ($session->userecordvc == 1 && $session->processedrecording >= 0) {
            if ($session->storagereference > 0) {
                $ht = $DB->get_record('hybridteaching', ['id' => $session->hybridteachingid], '*', MUST_EXIST );

                // You can download if can manage sessionsfulltable (supposed teacher)
                // or view and downloadrecords in ht (supposed student).
                if ($ht->downloadrecords && has_capability('mod/hybridteaching:view', $context)
                    || has_capability('mod/hybridteaching:sessionsfulltable', $context)) {
                        $classstorage = sessions_controller::get_subpluginstorage_class($session->storagereference);
                        $config = helper::subplugin_config_exists($session->storagereference, 'store');
                    if ($config && $classstorage) {
                        sessions_controller::require_subplugin_store($classstorage['type']);
                        $classname = $classstorage['classname'];
                        $sessionrecording = new $classname();
                        $urlrecording = $sessionrecording->download_recording($session->processedrecording,
                            $session->storagereference, $session->hybridteachingid, $sid);

                        list($course, $cm) = get_course_and_cm_from_instance($session->hybridteachingid, 'hybridteaching');
                        $event = \mod_hybridteaching\event\session_record_downloaded::create([
                            'objectid' => $session->hybridteachingid,
                            'context' => \context_module::instance($cm->id),
                            'other' => [
                                'sessid' => $sid,
                            ],
                        ]);

                        $event->trigger();
                    }
                }
            }
        }
    }
}
if ($urlrecording != '') {
    redirect($urlrecording);
} else {
    redirect (new moodle_url('/mod/hybridteaching/sessions.php', ['id' => $id, 'l' => 1]));
}


