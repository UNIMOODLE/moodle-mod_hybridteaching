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


use hybridteachvc_meet\webservice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class htmeet_config_edit_form extends moodleform {
    public function definition() {
        //$config = get_config('googlemeet');
        $mform = $this->_form;
        list($config, $type) = $this->_customdata;
        $client = new webservice();

        $logout = optional_param('logout', 0, PARAM_BOOL);
        if ($logout) {
            $client->logout();
        }

        /*if (empty($this->current->instance)) {
            $clientislogged = optional_param('client_islogged', false, PARAM_BOOL);

            // Was logged in before submitting the form and the google session expired after submitting the form.
            if ($clientislogged && !$client->check_login()) {
                $mform->addElement('html', html_writer::div(get_string('sessionexpired', 'googlemeet') .
                    $client->print_login_popup(), 'mdl-align alert alert-danger googlemeet_loginbutton'
                ));

                // Whether the customer is enabled and if not logged in to the Google account.
            } else if ($client->enabled && !$client->check_login()) {
                $mform->addElement('html', html_writer::div(get_string('logintoyourgoogleaccount', 'googlemeet') .
                    $client->print_login_popup(), 'mdl-align alert alert-info googlemeet_loginbutton'
                ));
            }

            // If is logged in, shows Google account information.
            if ($client->check_login()) {
                $mform->addElement('html', $client->print_user_info('calendar'));
                $mform->addElement('hidden', 'client_islogged', true);
            }

        } else {
            $mform->addElement('hidden', 'client_islogged', false);
        }*/
        $mform->setType('client_islogged', PARAM_BOOL);

        $mform->addElement('header', 'header', get_string('configgeneralsettings', 'hybridteaching'));
        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'type');
        $mform->addElement('hidden', 'subpluginconfigid');
        $mform->addElement('text', 'configname', get_string('configname', 'hybridteaching'));
        $categories = core_course_category::get_all();
        $options[0] = get_string('all');
        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }

        $mform->addElement('select', 'category', get_string('categories'), $options);

        $mform->setType('id', PARAM_INT);
        $mform->setType('type', PARAM_COMPONENT);
        $mform->setType('subpluginconfigid', PARAM_INT);
        $mform->setType('configname', PARAM_TEXT);
        $mform->setType('category', PARAM_INT);

        $mform->setDefault('type', get_string($type, 'hybridteachvc_'.$type));

        $mform->addRule('configname', null, 'required', null, 'client');
        $mform->addRule('configname', null, 'maxlength', 255, 'client');

        $mform->addElement('header', 'subplugin', get_string('pluginname', 'hybridteachvc_meet'));
        $mform->addElement('text', 'emailaccount', get_string('emailaccount', 'hybridteachvc_meet'));
        $mform->addElement('text', 'clientid', get_string('clientid', 'hybridteachvc_meet'));
        $mform->addElement('text', 'clientsecret', get_string('clientsecret', 'hybridteachvc_meet'));

        $mform->setType('emailaccount', PARAM_TEXT);
        $mform->setType('clientid', PARAM_TEXT);
        $mform->setType('clientsecret', PARAM_TEXT);

        $mform->addRule('emailaccount', null, 'required', null, 'client');
        $mform->addRule('emailaccount', null, 'maxlength', 255, 'client');
        $mform->addRule('clientid', null, 'required', null, 'client');
        $mform->addRule('clientid', null, 'maxlength', 255, 'client');
        $mform->addRule('clientsecret', null, 'required', null, 'client');
        $mform->addRule('clientsecret', null, 'maxlength', 255, 'client');

        if (empty($config)) {
            $this->add_action_buttons(true, get_string('addsetting', 'hybridteaching'));
        } else {
            $this->add_action_buttons(true, get_string('saveconfig', 'hybridteaching'));
        }
        $this->set_data($config);
    }
}
