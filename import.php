<?php

require(__DIR__.'/../../config.php');
include_once(__DIR__.'/classes/form/sessions_import_form.php');
include_once(__DIR__.'/classes/import/sessions_import.php');
include_once(__DIR__.'/classes/controller/notify_controller.php');

$id = required_param('id', PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$hybridteaching = $DB->get_record('hybridteaching', array('id' => $cm->instance), '*', MUST_EXIST);

$url = new moodle_url('/mod/hybridteaching/import.php', array('id' => $id));
$PAGE->set_url($url);

require_capability('mod/hybridteaching:import', $context);

$strimportsessions = get_string('importsessions', 'hybridteaching');

$PAGE->navbar->add($strimportsessions);
$PAGE->set_title("$course->shortname: $strimportsessions");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('admin');

$returnurl = new moodle_url('/mod/hybridteaching/view.php', array('id'=>$id));

$importform = new sessions_import_form(null, ['id' => $id]);

if ($importform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $importform->get_data()) {
    require_sesskey();
    $text = $importform->get_file_content('sessionsfile');
    $encoding = $data->encoding;
    $delimiter = $data->delimiter_name;
    $importer = new sessions_import($text, $encoding, $delimiter, 0, null, false, $course, $hybridteaching);
    $importer->import($hybridteaching);
    redirect($url);
}


echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('importsessions', 'hybridteaching'));
notify_controller::show();
$importform->display();
echo $OUTPUT->footer();
