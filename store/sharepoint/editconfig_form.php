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

class htsharepoint_config_edit_form extends moodleform {
    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        list($config, $type) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('configgeneralsettings', 'hybridteaching'));
        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'type');
        $mform->addElement('hidden', 'subpluginconfigid');
        $mform->addElement('text', 'configname', get_string('configname', 'hybridteaching'));

        $mform->setType('id', PARAM_INT);
        $mform->setType('type', PARAM_COMPONENT);
        $mform->setType('subpluginconfigid', PARAM_INT);
        $mform->setType('configname', PARAM_TEXT);

        $mform->setDefault('type', get_string($type, 'hybridteachstore_'.$type));

        $mform->addRule('configname', null, 'required', null, 'client');
        $mform->addRule('configname', null, 'maxlength', 255, 'client');

        $mform->addElement('header', 'subplugin', get_string('pluginname', 'hybridteachstore_sharepoint'));
        $mform->addElement('text', 'tenantid', get_string('tenantid', 'hybridteachstore_sharepoint'));
        $mform->addElement('text', 'clientid', get_string('clientid', 'hybridteachstore_sharepoint'));
        $mform->addElement('text', 'clientsecret', get_string('clientsecret', 'hybridteachstore_sharepoint'));
        $mform->addElement('text', 'subdomain', get_string('subdomain', 'hybridteachstore_sharepoint'));
        $mform->addElement('text', 'useremail', get_string('useremail', 'hybridteachstore_sharepoint'));
        
        $mform->setType('tenantid', PARAM_TEXT);
        $mform->setType('clientid', PARAM_TEXT);
        $mform->setType('clientsecret', PARAM_TEXT);
        $mform->setType('subdomain', PARAM_TEXT);
        $mform->setType('useremail', PARAM_TEXT);
        

        $mform->addRule('tenantid', null, 'required', null, 'client');
        $mform->addRule('clientid', null, 'required', null, 'client');
        $mform->addRule('clientsecret', null, 'required', null, 'client');
        $mform->addRule('subdomain', null, 'required', null, 'client');
        $mform->addRule('useremail', null, 'required', null, 'client');
        
        if (empty($config)) {
            $this->add_action_buttons(true, get_string('addsetting', 'hybridteaching'));
        } else {
            $this->add_action_buttons(true, get_string('saveconfig', 'hybridteaching'));
        }
        $this->set_data($config);
    }
}
