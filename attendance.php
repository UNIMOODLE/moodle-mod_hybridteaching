<?php

require(__DIR__.'/../../config.php');

// Course module id.
$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/hybridteaching:attendance', $context);

$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);

$url = new moodle_url('/mod/hybridteaching/attendance.php', array('id' => $id));

$PAGE->set_url($url);
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->activityheader->disable();


echo $OUTPUT->header();

echo $OUTPUT->footer();