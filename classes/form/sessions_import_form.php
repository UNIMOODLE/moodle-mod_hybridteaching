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

/**
 * The sessions import form
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/csvlib.class.php');

/**
 * Class sessions_import_form
 */
class sessions_import_form extends \moodleform {
    /**
     * Create all the form elements
     */
    public function definition() {
        global $DB;
        $mform =& $this->_form;
        $data  = $this->_customdata;

        $mform->addElement('header', 'general', get_string('general'));

        $url = new \moodle_url('classes/import/example.csv');
        $link = \html_writer::link($url, 'example.csv');
        $mform->addElement('static', 'examplecsv', get_string('examplecsv', 'hybridteaching'), $link);
        $mform->addHelpButton('examplecsv', 'examplecsv', 'hybridteaching');

        $mform->addElement('filepicker', 'sessionsfile', get_string('importsessions', 'hybridteaching'));
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $choices = \csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'group'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }

        $choices = \core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'group'), $choices);
        $mform->setDefault('encoding', 'UTF-8');
        $hybridteaching = $DB->get_record_sql('SELECT ht.* FROM {hybridteaching} ht
                                                 JOIN {course_modules} cm 
                                                   ON cm.instance=ht.id'
                                                   , ['cm.id' => $data['id']]);
        
        if($hybridteaching->sessionscheduling != '1') {
            $mform->disabledIf('submitbutton', 'id', 'eq', $data['id']);
        }
        $this->add_action_buttons(true, get_string('importsessions', 'hybridteaching'));
        
        $this->set_data($data);
    }
}
