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

namespace mod_hybridteaching\filters;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Class session_active_filter_form
 */
class session_active_filter_form extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        global $SESSION; // This is very hacky :-(.

        $mform       =& $this->_form;
        $fields      = $this->_customdata['fields'];
        $extraparams = $this->_customdata['extraparams'];

        if (!empty($SESSION->session_filtering)) {
            // Add controls for each active filter in the active filters group.
            $mform->addElement('header', 'actfilterhdr', get_string('actfilterhdr', 'filters'));

            foreach ($SESSION->session_filtering as $fname => $datas) {
                if (!array_key_exists($fname, $fields)) {
                    continue; // Filter not used.
                }
                $field = $fields[$fname];
                foreach ($datas as $i => $data) {
                    $description = $field->get_label($data);
                    $mform->addElement('checkbox', 'filter['.$fname.']['.$i.']', null, $description);
                }
            }

            if ($extraparams) {
                foreach ($extraparams as $key => $value) {
                    $mform->addElement('hidden', $key, $value);
                    $mform->setType($key, PARAM_RAW);
                }
            }

            $objs = [];
            $objs[] = &$mform->createElement('submit', 'removeselected', get_string('removeselected', 'filters'));
            $objs[] = &$mform->createElement('submit', 'removeall', get_string('removeall', 'filters'));
            $mform->addElement('group', 'actfiltergrp', '', $objs, ' ', false);
        }
    }
}
