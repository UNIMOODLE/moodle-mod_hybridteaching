<?php

require(__DIR__.'/../../config.php');
require(__DIR__.'/classes/output/sessions_render.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$slist = required_param('l', PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);

if ($slist == SESSION_LIST) {
    require_capability('mod/hybridteaching:sessions', $context);
    $PAGE->navbar->add(get_string('sessionsuc', 'hybridteaching'));
} else if ($slist == PROGRAM_SESSION_LIST) {
    require_capability('mod/hybridteaching:programschedule', $context);
    $PAGE->navbar->add(get_string('programscheduleuc', 'hybridteaching'));
}

$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);
$hybridteaching->context = $context;
$url = new moodle_url('/mod/hybridteaching/sessions.php', array('id' => $id, 'l' => $slist));
$sessionrender = new hybridteaching_sessions_render($hybridteaching, $slist);

$PAGE->set_url($url);
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->activityheader->disable();

echo $OUTPUT->header();
if ($slist == SESSION_LIST) {
    echo $OUTPUT->heading(get_string('sessions', 'hybridteaching'));
} else if ($slist == PROGRAM_SESSION_LIST) {
    echo "<a href='programsessions.php?id=".$id."'  class='btn btn-info' role='button'>Añadir sesión</a>";
}
echo $sessionrender->print_sessions_table();

echo $OUTPUT->footer();