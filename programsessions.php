<?php

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/classes/form/addsessions_form.php');
require_once(__DIR__.'/classes/controller/sessions_controller.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$sid = optional_param('s', 0, PARAM_INT);
$h = optional_param('h', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/hybridteaching:programschedule', $context);

$url = new moodle_url('/mod/hybridteaching/programsessions.php', array('id' => $id));

$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);

$PAGE->set_url($url);
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('programschedule', 'hybridteaching'));
$PAGE->set_context($context);

$sessionobj = null;
if (!empty($sid)) {
    $sessionobj = $DB->get_record('hybridteaching_session', array('id' => $sid), '*', MUST_EXIST);
    $sessionobj->sessionid = $sessionobj->id;
    unset($sessionobj->id);
    $sessionobj->duration = $sessionobj->duration / 60;
}

$sessioncontroller = new sessions_controller($hybridteaching);
$params = [
    'course' => $course,
    'cm' => $cm,
    'session' => $sessionobj,
    'typevc' => $hybridteaching->typevc,
];
$mform = new addsessions_form(null, $params);
$return = new moodle_url('programschedule.php?id='.$id);
$message = '';
$error = '';
if ($mform->is_cancelled()) {
    redirect($return);
} else if ($data = $mform->get_data()) {
    if (empty($sid)) {
        $data->context = $context;
        $sessioncontroller->create_session($data);
    } else {
        $data->id = required_param('s', PARAM_INT);
        $data->instance = $hybridteaching->instance;
        $sessioncontroller->update_session($data);
    }
    redirect($return);
}

$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($SITE->fullname);
$PAGE->set_context($context);

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();