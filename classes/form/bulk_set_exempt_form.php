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
 * The attendance filtering form
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\form;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

/**
 * Class bulk_set_exempt_form
 */
class bulk_set_exempt_form extends \moodleform {
    /**
     * Create all the form elements
     */
    public function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];
        $ids = $this->_customdata['attendslist'];
        $sessionid = $this->_customdata['sessionid'];
        $hybridteaching = $this->_customdata['hybridteaching'];
        $view = $this->_customdata['view'];
        $userid = $this->_customdata['userid'];

        $mform->addElement('header', 'general', get_string('setattendance', 'hybridteaching'));
        $options = [
            HYBRIDTEACHING_BULK_EXEMPT_ATTENDANCE => get_string('exemptattendance', 'hybridteaching'),
            HYBRIDTEACHING_BULK_NOT_EXEMPT_ATTENDANCE => get_string('notexemptattendance', 'hybridteaching'),
        ];

        $mform->addElement('select', 'operation', get_string('setexempt', 'hybridteaching'), $options);
        $mform->setType('operation', PARAM_INT);

        $mform->addElement('hidden', 'action', 'bulksetexempt');
        $mform->setType('action', PARAM_INT);

        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_INT);

        addhiddens($mform, $ids, $cm, $hybridteaching, $sessionid, $view);
        $submitstring = get_string('updateattendance', 'hybridteaching');
        $this->add_action_buttons(true, $submitstring);
    }
}

/**
 * Add hidden elements to the form.
 *
 * @param object $mform mform object
 * @param array $ids Array of selected ids
 * @param object $cm Course Module object
 * @param object $hybridteaching Hybridteaching object
 * @param int $sessionid Session id
 * @param string $view The selected view
 */
function addhiddens($mform, $ids, $cm, $hybridteaching, $sessionid, $view) {
    $mform->addElement('hidden', 'ids', $ids);
    $mform->setType('ids', PARAM_ALPHANUMEXT);
    $mform->addElement('hidden', 'id', $cm->id);
    $mform->setType('id', PARAM_INT);
    $mform->addElement('hidden', 'h', $hybridteaching->id);
    $mform->setType('h', PARAM_INT);
    $mform->addElement('hidden', 'sessionid', $sessionid);
    $mform->setType('sessionid', PARAM_INT);
    $mform->addElement('hidden', 'view', $view);
    $mform->setType('view', PARAM_TEXT);
}
