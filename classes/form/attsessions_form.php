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
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/attendance_controller.php');

class attsessions_options_form extends moodleform {
    public function definition() {
        global $CFG, $USER, $DB;
        $mform = &$this->_form;
        $mform->_attributes['id'] = 'sessionsform' . substr($mform->_attributes['id'], 6);
        $id = $this->_customdata['id'];
        $cm = get_coursemodule_from_id('hybridteaching', $id,  0,  false,  MUST_EXIST);
        $hid = $this->_customdata['hid'];
        $hybridteaching = $DB->get_record('hybridteaching', ['id' => $hid], '*', MUST_EXIST);
        $selectedsession = $this->_customdata['selectedsession'];
        $selectedsession ? $sessionid = $selectedsession : $sessionid = $this->_customdata['sessionid'];
        $sessioncontroller = new sessions_controller($hybridteaching);
        $session = $sessioncontroller->get_session($sessionid);
        $sessions = $sessioncontroller->load_sessions();
        $view = $this->_customdata['view'];
        $attcontroller = new attendance_controller();
        $sessioninfo = $attcontroller->hybridteaching_print_session_info($session);
        $mform->addElement('hidden', 'id');
        $mform->setDefault('id', $id);
        $mform->setType('id', PARAM_INT);

        $selectedsessions = [];
        if ($view == 'attendlog') {
            $selecteduser = $this->_customdata['selecteduser'];
            $mform->addElement('header', 'participant', get_string('participant', 'mod_hybridteaching'));
            $selectedusers = [];
            $sessionusers = $attcontroller->hybridteaching_get_attendance_users_in_session($sessionid);
            if ($sessionusers) {
                foreach ($sessionusers as $sesuser) {
                    $useratt = $DB->get_record('hybridteaching_attendance', ['userid' => $sesuser->userid,
                        'sessionid' => $sessionid, ], 'id, exempt', IGNORE_MISSING);
                    if ($useratt->exempt) {
                        $selectedusers[$useratt->id] = $sesuser->lastname . ' ' . $sesuser->firstname .
                            ' (' . get_string('exemptuser', 'hybridteaching') . ')';
                    } else {
                        $selectedusers[$useratt->id] = $sesuser->lastname . ' ' . $sesuser->firstname;
                    }
                }
                $mform->addElement('select', 'selecteduser', get_string('userfor', 'mod_hybridteaching'), $selectedusers);
                $mform->setDefault('selecteduser', $selecteduser);
            }
            $mform->addElement('static',  'description',  get_string('session', 'hybridteaching'), $sessioninfo);
        } else {
            $selectedsessions[0] = get_string('allsessions', 'hybridteaching');
        }

        $mform->addElement('header', 'sessions', get_string('sessions', 'mod_hybridteaching'));
        foreach ($sessions as $sess) {
            if ($att = $attcontroller->hybridteaching_get_attendance($sess)) {
                $att->visible && !$sess['attexempt'] ? $visible = '' : $visible = get_string('attnotforgrade', 'hybridteaching');
                $selectedsessions[$sess['id']] = $sess['name'] . ' | ' . date('l, j \d\e F \d\e Y H:i', $sess['starttime']) .
                    ' ' . $visible;
            }
        }
        $mform->addElement('autocomplete', 'selectedsession', get_string('sessionfor', 'mod_hybridteaching'), $selectedsessions);
        if ($sessionid) {
            $selectedsessions[$sessionid] = $session->name;
            $mform->setDefault('selectedsession', $sessionid);
        } else {
            $mform->setDefault('selectedsession', 0);
        }
        $view == 'extendedsessionatt' ? $mform->addElement('static',  'description',
            get_string('session', 'hybridteaching'), $sessioninfo) : '';
    }
}
