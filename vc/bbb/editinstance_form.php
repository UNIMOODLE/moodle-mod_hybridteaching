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

class htbbb_instance_edit_form extends moodleform {
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {

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

        $mform->addElement('header', 'subplugin', get_string('pluginname', 'hybridteachvc_bbb'));
        $mform->addElement('text', 'serverurl', get_string('serverurl', 'hybridteachvc_bbb'));
        $mform->addElement('text', 'sharedsecret', get_string('sharedsecret', 'hybridteachvc_bbb'));
        $mform->addElement('text', 'pollinterval', get_string('pollinterval', 'hybridteachvc_bbb'));

        $mform->setType('serverurl', PARAM_TEXT);
        $mform->setType('sharedsecret', PARAM_TEXT);
        $mform->setType('pollinterval', PARAM_TEXT);

        $mform->addRule('serverurl', null, 'required', null, 'client');
        $mform->addRule('sharedsecret', null, 'required', null, 'client');

        
        if (empty($instance)) {
            $this->add_action_buttons(true, get_string('addsetting', 'hybridteaching'));
        } else {
            $this->add_action_buttons(true, get_string('saveinstance', 'hybridteaching'));
        }
        $this->set_data($instance);
    }
}
