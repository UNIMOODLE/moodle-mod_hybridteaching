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
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

 require_once($CFG->libdir.'/formslib.php');

class htzoom_instance_edit_form extends moodleform {
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        list($instance, $type) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('instancegeneralsettings', 'hybridteaching'));
        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'type');
        $mform->addElement('hidden', 'subplugininstanceid');
        $mform->addElement('text', 'instancename', get_string('instancename', 'hybridteaching'));

        $mform->setType('id', PARAM_INT);
        $mform->setType('type', PARAM_COMPONENT);
        $mform->setType('subplugininstanceid', PARAM_INT);
        $mform->setType('instancename', PARAM_TEXT);

        $mform->setDefault('type', get_string($type, 'hybridteachvc_'.$type));

        $mform->addRule('instancename', null, 'required', null, 'client');
        $mform->addRule('instancename', null, 'maxlength', 255, 'client');

        $mform->addElement('header', 'subplugin', get_string('pluginname', 'hybridteachvc_zoom'));
        $mform->addElement('text', 'accountid', get_string('accountid', 'hybridteachvc_zoom'));
        $mform->addElement('text', 'clientid', get_string('clientid', 'hybridteachvc_zoom'));
        $mform->addElement('text', 'clientsecret', get_string('clientsecret', 'hybridteachvc_zoom'));
        $mform->addElement('text', 'emaillicense', get_string('emaillicense', 'hybridteachvc_zoom'));

        $mform->setType('accountid', PARAM_TEXT);
        $mform->setType('clientid', PARAM_TEXT);
        $mform->setType('clientsecret', PARAM_TEXT);
        $mform->setType('emaillicense', PARAM_TEXT);

        $mform->addRule('accountid', null, 'required', null, 'client');
        $mform->addRule('accountid', null, 'maxlength', 255, 'client');
        $mform->addRule('clientid', null, 'required', null, 'client');
        $mform->addRule('clientid', null, 'maxlength', 255, 'client');
        $mform->addRule('clientsecret', null, 'required', null, 'client');
        $mform->addRule('clientsecret', null, 'maxlength', 255, 'client');
        $mform->addRule('emaillicense', null, 'required', null, 'client');
        $mform->addRule('emaillicense', null, 'maxlength', 255, 'client');

        if (empty($instance)) {
            $this->add_action_buttons(true, get_string('addinstance', 'hybridteaching'));
        } else {
            $this->add_action_buttons(true, get_string('saveinstance', 'hybridteaching'));
        }
        $this->set_data($instance);
    }
}
