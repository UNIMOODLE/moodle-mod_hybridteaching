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

namespace mod_hybridteaching\filters;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class session_add_filter_form extends \moodleform {
    public function definition() {
        global $SESSION;

        $mform       =& $this->_form;
        $fields      = $this->_customdata['fields'];
        $extraparams = $this->_customdata['extraparams'];

        $mform->addElement('header', 'newfilter', get_string('newfilter', 'filters'));

        foreach ($fields as $ft) {
            $ft->setup_form($mform);
        }

        // In case we wasnt to track some page params.
        if ($extraparams) {
            foreach ($extraparams as $key => $value) {
                $mform->addElement('hidden', $key, $value);
                $mform->setType($key, PARAM_RAW);
            }
        }

        // Add buttons.
        $replacefiltersbutton = $mform->createElement('submit', 'replacefilters', get_string('replacefilters', 'filters'),
            null, '', ['class' => 'btn btn-secondary']);
        $addfilterbutton = $mform->createElement('submit', 'addfilter', get_string('addfilter', 'filters'), null,
            '', ['class' => 'btn btn-secondary']);
        $buttons = array_filter([
            empty($SESSION->session_filtering) ? null : $replacefiltersbutton,
            $addfilterbutton,
        ]);

        $mform->addGroup($buttons);
    }
}
