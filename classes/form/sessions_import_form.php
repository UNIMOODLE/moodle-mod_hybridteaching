<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/csvlib.class.php');

class sessions_import_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;
        $data  = $this->_customdata;

        $mform->addElement('header', 'general', get_string('general'));

        $url = new moodle_url('classes/import/example.csv');
        $link = html_writer::link($url, 'example.csv');
        $mform->addElement('static', 'examplecsv', get_string('examplecsv', 'hybridteaching'), $link);
        $mform->addHelpButton('examplecsv', 'examplecsv', 'hybridteaching');

        $mform->addElement('filepicker', 'sessionsfile', get_string('importsessions', 'hybridteaching'));
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'group'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }

        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'group'), $choices);
        $mform->setDefault('encoding', 'UTF-8');
        $this->add_action_buttons(true, get_string('importsessions', 'hybridteaching'));

        $this->set_data($data);
    }
}
