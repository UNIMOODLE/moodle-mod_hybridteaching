<?php

require(__DIR__.'/../../config.php');
include_once(__DIR__.'/classes/controller/notify_controller.php');

$id = required_param('id', PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);

$url = new moodle_url('/mod/hybridteaching/export.php', array('id' => $id));
$PAGE->set_url($url);

require_capability('mod/hybridteaching:export', $context);

$strexport = get_string('export', 'hybridteaching');

$PAGE->navbar->add($strexport);
$PAGE->set_title("$course->shortname: $strexport");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('admin');

$returnurl = new moodle_url('/mod/hybridteaching/view.php', array('id'=>$id));

$formparams = array('course' => $course, 'cm' => $cm, 'modcontext' => $context);
$exportform = new mod_hybridteaching\form\export_form(null, $formparams);

if ($exportform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $exportform->get_data()) {
    require_sesskey();
    $data->context = $context;
    $exporter = new mod_hybridteaching\helpers\export($data);
    $exporter->export($hybridteaching);
    redirect($url);
}


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('export', 'hybridteaching'));
notify_controller::show();
$exportform->display();
echo $OUTPUT->footer();
