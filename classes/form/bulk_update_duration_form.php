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

class bulk_update_duration_form extends \moodleform {
    public function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];
        $ids = $this->_customdata['sesslist'];
        $hybridteaching = $this->_customdata['hybridteaching'];
        $slist = $this->_customdata['slist'];

        $mform->addElement('header', 'general', get_string('updatesesduration', 'hybridteaching'));
        $options = [
            '1' => get_string('seton', 'hybridteaching'),
            '2' => get_string('extend', 'hybridteaching'),
            '3' => get_string('reduce', 'hybridteaching'),
        ];
        $mform->addElement('select', 'operation', get_string('updateduration', 'hybridteaching'), $options);
        $mform->setType('operation', PARAM_INT);
        $duration[] = &$mform->createElement('text', 'duration', get_string('duration', 'hybridteaching'));
        $mform->setType('duration', PARAM_INT);

        $options = [
            '1' => get_string('minutes'),
            '2' => get_string('hours'),
        ];
        $duration[] = &$mform->createElement('select', 'timetype', '', $options);
        $mform->setType('timetype', PARAM_INT);
        $mform->addGroup($duration, 'durationgroup', get_string('time'), [' '], false);

        $mform->addElement('hidden', 'action', 'bulkupdateduration');
        $mform->setType('action', PARAM_INT);

        $mform->addElement('checkbox', 'updatecalen', get_string('updatecalen', 'hybridteaching'));
        $mform->setType('updatecalen', PARAM_INT);
        addhiddens($mform, $ids, $cm, $hybridteaching, $slist);
        $submitstring = get_string('updatesessions', 'hybridteaching');
        $this->add_action_buttons(true, $submitstring);
    }
}

function addhiddens($mform, $ids, $cm, $hybridteaching, $slist) {
    $mform->addElement('hidden', 'ids', $ids);
    $mform->setType('ids', PARAM_ALPHANUMEXT);
    $mform->addElement('hidden', 'id', $cm->id);
    $mform->setType('id', PARAM_INT);
    $mform->addElement('hidden', 'h', $hybridteaching->id);
    $mform->setType('h', PARAM_INT);
    $mform->addElement('hidden', 'l', $slist);
    $mform->setType('l', PARAM_INT);
}
