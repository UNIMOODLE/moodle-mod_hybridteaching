<?php



require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/classes/form/addsessions_form.php');

// Course module id.
$id = required_param('id', PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/hybridteaching:programschedule', $context);

$url = new moodle_url('/mod/hybridteaching/programsessions.php', array('id' => $id));
$sid = optional_param('sid', 0, PARAM_INT);

$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);

$PAGE->set_url($url);
$PAGE->set_title(format_string($hybridteaching->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(ucfirst(get_string('programschedule', 'hybridteaching')));
$PAGE->set_context($context);

$return = new moodle_url('programschedule.php');
$sessionobj = null;
$type = $hybridteaching->typevc;
$typealias = get_string($type.'alias', 'hybridteachingvc_'.$type);
$table = 'hybridteaching_'.lcfirst($typealias);
if (!empty($sid)) {
    $sessionobj = $DB->get_record($table, array('id' => $sid), '*', MUST_EXIST);
}

require_once(__DIR__.'/vc/'.$type.'/classes/sessions.php');
$sessioncontroller = new sessions($sessionobj, $table);
$params = [
    'course' => $course,
    'cm' => $cm,
    'session' => $sessionobj,
    'type' => $type,
];
$mform = new addsessions_form(null, $params);
$message = '';
$error = '';
if ($mform->is_cancelled()) {
    redirect($return);
} else if ($data = $mform->get_data()) {
    if (empty($sessionobj)) {
        $data->id = $sessioncontroller->create_session($data);
    } else {
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