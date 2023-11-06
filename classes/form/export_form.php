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
 * The export form
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_hybridteaching\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');

class export_form extends \moodleform {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        global $USER, $DB, $PAGE, $CFG;
        $mform    =& $this->_form;
        $course        = $this->_customdata['course'];
        $cm            = $this->_customdata['cm'];
        $modcontext    = $this->_customdata['modcontext'];

        $mform->addElement('header', 'general', get_string('export', 'attendance'));

        $groupmode = groups_get_activity_groupmode($cm, $course);
        $groups = groups_get_activity_allowed_groups($cm, $USER->id);
        if ($groupmode == VISIBLEGROUPS || has_capability('moodle/site:accessallgroups', $modcontext)) {
            $grouplist[0] = get_string('allparticipants');
        }
        if ($groups) {
            foreach ($groups as $group) {
                $grouplist[$group->id] = $group->name;
            }
        }

        // Restrict the export to the selected users.
        $userfieldsapi = \core_user\fields::for_name();
        $namefields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

        $allusers = get_enrolled_users($modcontext, 'mod/hybridteaching:attendanceregister', 0, 'u.id,'.$namefields);
        $userlist = [];
        foreach ($allusers as $user) {
            $userlist[$user->id] = fullname($user);
        }
        unset($allusers);
        if (empty($userlist)) {
            $mform->addElement('static', 'nousers', '', get_string('noattendanceusers', 'attendance'));
            return;
        }

        $mform->addElement('select', 'group', get_string('group'), $grouplist);

        $mform->setType('id', PARAM_INT);

        $mform->addElement('checkbox', 'includeallsessions', get_string('includeall', 'attendance'), get_string('yes'));
        $mform->setDefault('includeallsessions', true);
        $mform->addElement('date_selector', 'sessionstartdate', get_string('startofperiod', 'attendance'));
        $mform->setDefault('sessionstartdate', $course->startdate);
        $mform->disabledIf('sessionstartdate', 'includeallsessions', 'checked');
        $mform->addElement('date_selector', 'sessionenddate', get_string('endofperiod', 'attendance'));
        $mform->disabledIf('sessionenddate', 'includeallsessions', 'checked');

        $formatoptions = ['excel' => get_string('downloadexcel', 'attendance'),
                               'ooo' => get_string('downloadooo', 'attendance'),
                               'text' => get_string('downloadtext', 'attendance'), ];
        $mform->addElement('select', 'format', get_string('format'), $formatoptions);

        $submitstring = get_string('ok');
        $this->add_action_buttons(false, $submitstring);

        $mform->addElement('hidden', 'id', $cm->id);
        $mform->addElement('hidden', 'coursename', $course->fullname);
        $mform->setType('coursename', PARAM_TEXT);
    }
}

