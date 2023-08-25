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
 * The password and qr helper.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once($CFG->libdir.'/formslib.php');
 
 class  attendance_options_form extends moodleform {
    public function definition() {
        $mform = &$this->_form;

        $id = $this->_customdata['id'];
        $cm = get_coursemodule_from_id('hybridteaching', $id,  0,  false,  MUST_EXIST);
        $mform->_attributes['id'] = 'optionsform' . substr($mform->_attributes['id'], 6);

        $mform->addElement('hidden', 'id');
        $mform->setDefault('id', $id);
        $mform->setType('id', PARAM_INT);

        $groupmode = groups_get_activity_groupmode($cm);
        $selectgroups = array();
        $selectgroups[0] = get_string('commonsession', 'hybridteaching');
        $mform->addElement('header', 'headeraddmultiplesessions', get_string('addmultiplesessions', 'hybridteaching'));
        if ($groupmode == SEPARATEGROUPS || $groupmode == VISIBLEGROUPS) {
            if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $modcontext)) {
                $groups = groups_get_all_groups($course->id, $USER->id, $cm->groupingid);
            } else {
                $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
            }
            if ($groups) {
                foreach ($groups as $group) {
                    $selectgroups[$group->id] = $group->name;
                }
                if ($groupmode == SEPARATEGROUPS) {
                    array_shift($selectgroups);
                }
                $mform->addElement('select', 'groupid', get_string('sessionfor', 'hybridteaching'), $selectgroups);
            } else {
                $mform->addElement('static', 'groupid', get_string('sessionfor', 'hybridteaching'),
                                  get_string('nogroups', 'hybridteaching'));
                if ($groupmode == SEPARATEGROUPS) {
                    return;
                }
            }
        } else {
            $mform->addElement('select', 'groupid', get_string('groups', 'group'), $selectgroups);
        }

        $mform->addElement('header', 'options', get_string('options', 'mod_hybridteaching'));
        $perpage = array(
            0 => get_string('donotusepaging', 'attendance'),
            10 => 10,
            25 => 25,
            50 => 50,
            75 => 75,
            100 => 100,
            250 => 250,
            500 => 500,
            1000 => 1000,
        );
        $mform->addElement('select', 'perpage', get_string('sesperpage', 'mod_hybridteaching'), $perpage);
        $mform->setDefault('perpage', get_config('hybridteaching', 'resultsperpage'));
    }
}