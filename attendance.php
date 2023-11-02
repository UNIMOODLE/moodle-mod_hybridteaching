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


require(__DIR__.'/../../config.php');
require(__DIR__.'/classes/output/attendance_render.php');
global $USER;
// Course module id.
$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/hybridteaching:attendance', $context);

$hybridteaching = $DB->get_record('hybridteaching', ['id' => $cm->instance], '*', MUST_EXIST);
$hybridteaching->context = $context;
$url = new moodle_url('/mod/hybridteaching/attendance.php', ['id' => $id, 'editing' => 1]);
$attendancerender = new hybridteaching_attendance_render($hybridteaching);

$PAGE->set_url($url);
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->activityheader->disable();

$user = $USER->id;
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('attendance', 'hybridteaching'));

if (has_capability('mod/hybridteaching:sessionsfulltable', $context, $user, $doanything = true)) {
    echo "<a href='attendance.php?id=".$id."&view=extendedstudentatt'  class='btn btn-info' role='button'>" .
        get_string('studentsattendance', 'hybridteaching') . "</a>";
    echo "<a href='attendance.php?id=".$id."&view=sessionattendance'  class='btn btn-info' role='button'>" .
        get_string('sessionsattendance', 'hybridteaching') . "</a>";
}
echo $attendancerender->print_attendance_table();

echo $OUTPUT->footer();
