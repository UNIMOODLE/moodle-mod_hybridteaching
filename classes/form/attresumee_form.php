<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The attendance filtering form.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once($CFG->libdir.'/formslib.php');
 require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/attendance_controller.php');

 class attresumee_options_form extends moodleform {
    public function definition() {
        $mform = &$this->_form;
        $mform->_attributes['id'] = 'sessionsform' . substr($mform->_attributes['id'], 6);

        $id = $this->_customdata['id'];
        $hid = $this->_customdata['hid'];
        $cm = get_coursemodule_from_id('hybridteaching', $id,  0,  false,  MUST_EXIST);

        $mform->addElement('hidden', 'id');
        $mform->setDefault('id', $id);
        $mform->setType('id', PARAM_INT);
        
        $attcontroller = new attendance_controller();
        $selecteduser = $this->_customdata['selecteduser'];

        $mform->addElement('header', 'headerusercompletion', get_string('attendanceresumee', 'hybridteaching'));
        $selecteduser = $this->_customdata['selecteduser'];
        $selectedusers = [];
        $husers = $attcontroller::hybridteaching_get_instance_users($hid);
        foreach($husers as $huser) {
            $selectedusers[$huser->id] = $huser->lastname . ' ' . $huser->firstname . '';
        }
        $mform->addElement('select', 'selecteduser', get_string('userfor', 'mod_hybridteaching'), $selectedusers);
        $mform->setDefault('selecteduser', $selecteduser);

        $selecteduser = $attcontroller->load_sessions_attendant($selecteduser);
        $mform->addElement('static', 'attendinfo', '', $attcontroller->hybridteaching_print_attendance_for_user($hid, $selecteduser));
    }
}