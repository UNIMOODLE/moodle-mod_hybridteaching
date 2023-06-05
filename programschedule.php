<?php

require(__DIR__.'/../../config.php');
require(__DIR__.'/classes/output/sessions_render.php');

// Course module id.
$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/hybridteaching:programschedule', $context);

$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);
$url = new moodle_url('/mod/hybridteaching/programschedule.php', array('id' => $id));
$sessionrender = new hybridteaching_sessions_render($hybridteaching);

$PAGE->set_url($url);
$PAGE->navbar->add(ucfirst(get_string('programschedule', 'hybridteaching')));
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->activityheader->disable();

echo $OUTPUT->header();
echo "<a href='programsessions.php?id=".$id."'  class='btn btn-info' role='button'>Añadir sesión</a>";
echo $sessionrender->print_sessions_programming_table();

echo $OUTPUT->footer();