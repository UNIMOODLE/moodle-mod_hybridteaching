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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    hybridteachstore_pumukit
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Class htpumukit_config_edit_form.
 */
class htpumukit_config_edit_form extends moodleform {
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
        $mform->addElement('hidden', 'categories', '', "id='categories'");
        $mform->addElement('text', 'configname', get_string('configname', 'hybridteaching'));

        $mform->addElement('html', '<button type="button" class="btn btn-outline-primary ml-3" data-toggle="modal"
            data-target="#categoriesmodal">'.get_string('categories').'</button>');

        $mform->setType('id', PARAM_INT);
        $mform->setType('type', PARAM_COMPONENT);
        $mform->setType('subpluginconfigid', PARAM_INT);
        $mform->setType('configname', PARAM_TEXT);
        $mform->setType('categories', PARAM_TEXT);

        $mform->setDefault('type', get_string($type, 'hybridteachstore_'.$type));

        $mform->addRule('configname', null, 'required', null, 'client');
        $mform->addRule('configname', null, 'maxlength', 255, 'client');

        $mform->addElement('header', 'subplugin', get_string('pluginname', 'hybridteachstore_pumukit'));
        $mform->addElement('text', 'url', get_string('url', 'hybridteachstore_pumukit'), ['size' => 40]);
        $mform->addElement('text', 'userpumukit', get_string('user', 'hybridteachstore_pumukit'), ['size' => 40]);
        $mform->addElement('passwordunmask', 'secret', get_string('secret', 'hybridteachstore_pumukit'), ['size' => 40]);

        $mform->setType('url', PARAM_TEXT);
        $mform->setType('userpumukit', PARAM_TEXT);
        $mform->setType('secret', PARAM_TEXT);

        $mform->addRule('url', null, 'required', null, 'client');
        $mform->addRule('url', null, 'maxlength', 700, 'client');
        $mform->addRule('userpumukit', null, 'required', null, 'client');
        $mform->addRule('userpumukit', null, 'maxlength', 255, 'client');
        $mform->addRule('secret', null, 'required', null, 'client');
        $mform->addRule('secret', null, 'maxlength', 255, 'client');

        if (empty($config)) {
            $this->add_action_buttons(true, get_string('addsetting', 'hybridteaching'));
        } else {
            $this->add_action_buttons(true, get_string('saveconfig', 'hybridteaching'));
        }
        $this->set_data($config);
    }
}
