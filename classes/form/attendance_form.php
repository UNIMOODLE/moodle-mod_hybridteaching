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
 * The attendance filtering form
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');

class attendance_options_form extends moodleform {
    public function definition() {
        global $USER;
        $mform = &$this->_form;

        $id = $this->_customdata['id'];
        $cm = get_coursemodule_from_id('hybridteaching', $id,  0,  false,  MUST_EXIST);
        $mform->_attributes['id'] = 'optionsform' . substr($mform->_attributes['id'], 6);

        $mform->addElement('hidden', 'id');
        $mform->setDefault('id', $id);
        $mform->setType('id', PARAM_INT);

        $course = $this->_customdata['course'];
        $modcontext = context_module::instance($cm->id);

        $perpageval = $this->_customdata['perpage'];
        $groupmode = groups_get_activity_groupmode($cm);
        $selectgroups = [];
        $selectgroups[-1] = get_string('anygroup', 'hybridteaching');
        $selectgroups[0] = get_string('allgroups', 'hybridteaching');
        $selectfilter = $this->_customdata['selectedfilter'];
        $groupexception = $this->_customdata['groupexception'];
        if (has_capability('mod/hybridteaching:sessionsfulltable', $modcontext)) {
            if ($groupmode == SEPARATEGROUPS || $groupmode == VISIBLEGROUPS || $groupexception) {
                $mform->addElement('header', 'headeraddmultiplesessions', get_string('addmultiplesessions', 'hybridteaching'));
                $groupid = 1;
                if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $modcontext)) {
                    $groups = groups_get_all_groups($course->id, $USER->id, $cm->groupingid);
                } else {
                    $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
                }
                if ($groups) {
                    foreach ($groups as $group) {
                        $selectgroups[$group->id] = $group->name;
                    }
                    $mform->addElement('autocomplete', 'groupid', get_string('sessionfor', 'hybridteaching'), $selectgroups);
                } else {
                    $mform->addElement('static', 'groupid', get_string('sessionfor', 'hybridteaching'),
                                      get_string('nogroups', 'hybridteaching'));
                    if ($groupmode == SEPARATEGROUPS) {
                        return;
                    }
                }
            }
        }

        $mform->addElement('header', 'options', get_string('options', 'mod_hybridteaching'));
        $perpage = [
            0 => get_string('donotusepaging', 'mod_hybridteaching'),
            10 => 10,
            25 => 25,
            50 => 50,
            75 => 75,
            100 => 100,
            250 => 250,
            500 => 500,
            1000 => 1000,
        ];
        $mform->addElement('select', 'perpage', get_string('sesperpage', 'mod_hybridteaching'), $perpage);
        $mform->setDefault('perpage', $perpageval);

        $mform->addElement('header', 'filter', get_string('filter'));
        $view = $this->_customdata['view'];
        switch ($view) {
            case 'sessionattendance':
                $filter = ['nofilter' => get_string('nofilter', 'hybridteaching'), 'att' => get_string('withatt', 'hybridteaching'),
                    'notatt' => get_string('withoutatt', 'hybridteaching'), ];
                break;
            case 'studentattendance':
                $filter = ['nofilter' => get_string('nofilter', 'hybridteaching'), 'att' => get_string('withatt', 'hybridteaching'),
                    'notatt' => get_string('withoutatt', 'hybridteaching'), 'late' => get_string('late', 'hybridteaching'),
                    'leaved' => get_string('earlyleave', 'hybridteaching'), 'vc' => get_string('vc', 'hybridteaching'),
                    'classroom' => get_string('classroom', 'hybridteaching'), ];
                break;
            case 'extendedsessionatt':
                $filter = ['nofilter' => get_string('nofilter', 'hybridteaching'), 'att' => get_string('withatt', 'hybridteaching'),
                    'notatt' => get_string('withoutatt', 'hybridteaching'), 'exempt' => get_string('exempt', 'hybridteaching'),
                    'notexempt' => get_string('notexempt', 'hybridteaching'), 'late' => get_string('late', 'hybridteaching'),
                    'leaved' => get_string('earlyleave', 'hybridteaching'), ];
                break;
            default:
                $filter = ['nofilter' => get_string('nofilter', 'hybridteaching')];
                break;
        }
        $mform->addElement('select', 'attfilter',  get_string('filter'), $filter);
        $selectfilter ? $mform->setDefault('attfilter', $selectfilter) : $mform->setDefault('attfilter', 'nofilter');
    }
}

class bulk_set_attendance_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];
        $ids = $this->_customdata['attendslist'];
        $sessionid = $this->_customdata['sessionid'];
        $hybridteaching = $this->_customdata['hybridteaching'];
        $view = $this->_customdata['view'];
        $userid = $this->_customdata['userid'];

        $mform->addElement('header', 'general', get_string('setattendance', 'hybridteaching'));
        $options = [
            '1' => get_string('activeattendance', 'hybridteaching'),
            '2' => get_string('inactiveattendance', 'hybridteaching'),
        ];
        $mform->addElement('select', 'operation', get_string('updateduration', 'hybridteaching'), $options);
        $mform->setType('operation', PARAM_INT);

        $mform->addElement('hidden', 'action', 'bulksetattendance');
        $mform->setType('action', PARAM_INT);

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        addhiddens($mform, $ids, $cm, $hybridteaching, $sessionid, $view);
        $submitstring = get_string('updateattendance', 'hybridteaching');
        $this->add_action_buttons(true, $submitstring);
    }
}

class bulk_set_exempt_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];
        $ids = $this->_customdata['attendslist'];
        $sessionid = $this->_customdata['sessionid'];
        $hybridteaching = $this->_customdata['hybridteaching'];
        $view = $this->_customdata['view'];
        $userid = $this->_customdata['userid'];

        $mform->addElement('header', 'general', get_string('setattendance', 'hybridteaching'));
        $options = [
            '3' => get_string('exemptattendance', 'hybridteaching'),
            '4' => get_string('notexemptattendance', 'hybridteaching'),
        ];
        $mform->addElement('select', 'operation', get_string('updateduration', 'hybridteaching'), $options);
        $mform->setType('operation', PARAM_INT);

        $mform->addElement('hidden', 'action', 'bulksetexempt');
        $mform->setType('action', PARAM_INT);

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        addhiddens($mform, $ids, $cm, $hybridteaching, $sessionid, $view);
        $submitstring = get_string('updateattendance', 'hybridteaching');
        $this->add_action_buttons(true, $submitstring);
    }
}

class bulk_set_session_exempt_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];
        $ids = $this->_customdata['sessionslist'];
        $sessionid = $this->_customdata['sessionid'];
        $hybridteaching = $this->_customdata['hybridteaching'];
        $view = $this->_customdata['view'];

        $mform->addElement('header', 'general', get_string('setattendance', 'hybridteaching'));
        $options = [
            '5' => get_string('exemptsessionattendance', 'hybridteaching'),
            '6' => get_string('notexemptsessionattendance', 'hybridteaching'),
        ];
        $mform->addElement('select', 'operation', get_string('updateduration', 'hybridteaching'), $options);
        $mform->setType('operation', PARAM_INT);

        $mform->addElement('hidden', 'action', 'bulksetsessionexempt');
        $mform->setType('action', PARAM_INT);

        addhiddens($mform, $ids, $cm, $hybridteaching, $sessionid, $view);
        $submitstring = get_string('updateattendance', 'hybridteaching');
        $this->add_action_buttons(true, $submitstring);
    }
}

function addhiddens($mform, $ids, $cm, $hybridteaching, $sessionid, $view) {
    $mform->addElement('hidden', 'ids', $ids);
    $mform->setType('ids', PARAM_ALPHANUMEXT);
    $mform->addElement('hidden', 'id', $cm->id);
    $mform->setType('id', PARAM_INT);
    $mform->addElement('hidden', 'h', $hybridteaching->id);
    $mform->setType('h', PARAM_INT);
    $mform->addElement('hidden', 'sessionid', $sessionid);
    $mform->setType('sessionid', PARAM_INT);
    $mform->addElement('hidden', 'view', $view);
    $mform->setType('view', PARAM_TEXT);
}
