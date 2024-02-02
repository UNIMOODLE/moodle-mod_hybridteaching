<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The sessions form
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class session_options_form extends \moodleform {
    public function definition() {
        $mform =& $this->_form;
        $id = $this->_customdata['id'];
        $slist = $this->_customdata['l'];

        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'l');
        $mform->setDefault('id', $id);
        $mform->setDefault('l', $slist);
        $mform->setType('id', PARAM_INT);
        $mform->setType('l', PARAM_INT);
        $mform->addElement('header', 'options', get_string('options', 'hybridteaching'));
        $perpage = [
            0 => get_string('donotusepaging', 'hybridteaching'),
            10 => 10,
            25 => 25,
            50 => 50,
            75 => 75,
            100 => 100,
            250 => 250,
            500 => 500,
            1000 => 1000,
        ];
        $mform->addElement('select', 'perpage', get_string('sesperpage', 'hybridteaching'), $perpage);
        $mform->setDefault('perpage', get_config('hybridteaching', 'resultsperpage'));
    }
}
