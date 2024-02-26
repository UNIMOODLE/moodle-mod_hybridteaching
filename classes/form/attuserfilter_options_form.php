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
 * The attendance user filtering form
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\form;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/attendance_controller.php');

/**
 * Class attuserfilter_options_form
 */
class attuserfilter_options_form extends \moodleform {
    /**
     * Create all the form elements
     */
    public function definition() {
        global $OUTPUT, $CFG;

        $mform = &$this->_form;
        $mform->_attributes['id'] = 'attuserfilter' . substr($mform->_attributes['id'], 6);

        $id = $this->_customdata['id'];
        $hid = $this->_customdata['hid'];
        $cm = get_coursemodule_from_id('hybridteaching', $id,  0,  false,  MUST_EXIST);

        $mform->addElement('hidden', 'id');
        $mform->setDefault('id', $id);
        $mform->setType('id', PARAM_INT);

        $fname = $this->_customdata['fname'];
        $lname = $this->_customdata['lname'];
        $view = $this->_customdata['view'];
        $sort = $this->_customdata['sort'];
        $dir = $this->_customdata['dir'];
        $groupid = $this->_customdata['groupid'];
        $attid = $this->_customdata['att'];

        $perpage = $this->_customdata['perpage'];
        $selectedsession = $this->_customdata['sessid'];
        $attfilter = $this->_customdata['attfilter'];

        $url = new \moodle_url($CFG->wwwroot . '/mod/hybridteaching/attendance.php?view=' .
            $view . '&sessionid=' . $selectedsession . '&sort=' . $sort . '&dir=' . $dir .
            '&perpage=' . $perpage . '&attfilter=' . $attfilter . '&groupid=' . $groupid,
            ['id' => $id, 'att' => $attid, 'fname' => $fname, 'lname' => $lname]);

        $mform->addElement('header', 'userfilter', get_string('user'));
        $mform->addElement('static', 'fnameselect', get_string('firstname'), $OUTPUT->initials_bar($fname, 'firstinitial',
            '', 'fname', new \moodle_url($url)));
        $mform->addElement('static', 'lnameselect', get_string('lastname'), $OUTPUT->initials_bar($lname, 'lastinitial',
            '', 'lname', new \moodle_url($url)));
    }
}
