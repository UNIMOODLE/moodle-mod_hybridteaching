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
 * The attendance sessions form
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\form;

use mod_hybridteaching\controller\attendance_controller;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');

/**
 * Class attresume_options_form
 */
class attresume_options_form extends \moodleform {
    /**
     * Create all the form elements
     */
    public function definition() {
        global $USER;

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

        $mform->addElement('header', 'headerusercompletion', get_string('attendanceresume', 'hybridteaching'));
        $selecteduser = $this->_customdata['selecteduser'];
        if (has_capability('mod/hybridteaching:sessionsfulltable',
              \context_module::instance($cm->id), $user = $USER->id)) {
            $selectedusers = [];
            $husers = $attcontroller::hybridteaching_get_instance_users($hid);
            foreach ($husers as $huser) {
                $selectedusers[$huser->id] = $huser->lastname . ' ' . $huser->firstname . '';
            }
            $mform->addElement('autocomplete', 'selecteduser', get_string('userfor', 'hybridteaching'), $selectedusers);
            $mform->setDefault('selecteduser', $selecteduser);
        }

        $selecteduser = $attcontroller->load_sessions_attendant($selecteduser);
        $mform->addElement('static', 'attendinfo', '', $attcontroller->hybridteaching_print_attendance_for_user($hid,
            $selecteduser));
    }
}
