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

/**
 * Class sessions_form
 */
class sessions_form extends \moodleform {
    /**
     * Create all the form elements
     */
    public function definition() {
        global $CFG, $USER, $DB;
        $mform = &$this->_form;
        $this->_form->disable_form_change_checker();
        $course = $this->_customdata['course'];
        $cm = $this->_customdata['cm'];
        $session = $this->_customdata['session'];
        !isset($this->_customdata['typevc']) ? $typevc = '' : $typevc = $this->_customdata['typevc'];
        $modcontext = \context_module::instance($cm->id);
        if (!empty($session)) {
            $defopts = ['maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext];
            $session = file_prepare_standard_editor($session, 'description',
                $defopts, $modcontext, 'mod_hybridteaching', 'session', $session->sessionid);
        }

        if (empty($session)) {
            $headertitle = get_string('addsession', 'hybridteaching');
        } else {
            $headertitle = get_string('editsession', 'hybridteaching');
        }
        if (!empty($cm)) {
            $hasgrade = $DB->get_field('hybridteaching', 'grade', [
                'id' => $DB->get_field('course_modules', 'instance', ['id' => $cm->id], IGNORE_MISSING),
            ], IGNORE_MISSING);
        }
        $mform->addElement('header', 'general', $headertitle);

        $mform->addElement('text', 'name', get_string('sessionname', 'hybridteaching'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $cm->id);
        if (!empty($session)) {
            $mform->addElement('hidden', 's');
            $mform->setType('s', PARAM_INT);
            $mform->setDefault('s', $session->sessionid);
        }

        $mform->addElement('hidden', 'typevc', $typevc);
        $mform->setType('typevc', PARAM_ALPHA);

        $groupmode = groups_get_activity_groupmode($cm);
        $selectgroups[0] = get_string('allgroups', 'hybridteaching');
        if ($groupmode == SEPARATEGROUPS || $groupmode == VISIBLEGROUPS) {
            if ($groupmode == SEPARATEGROUPS && !has_capability('mod/hybridteaching:viewallsessions', $modcontext)) {
                $selectgroups = [];
                $groups = groups_get_all_groups($course->id, $USER->id, $cm->groupingid);
            } else {
                $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
            }
            if ($groups) {
                foreach ($groups as $group) {
                    $selectgroups[$group->id] = $group->name;
                }
                $mform->addElement('select', 'groupid', get_string('sessionfor', 'hybridteaching'), $selectgroups);
            } else {
                $mform->addElement('static', 'groupid', get_string('sessionfor', 'hybridteaching'),
                                  get_string('nogroups', 'hybridteaching'));
                if ($groupmode == SEPARATEGROUPS) {
                    return;
                }
            }
        } else {
            $mform->addElement('select', 'groupid', get_string('groups', 'group'), $selectgroups);
        }

        $attributes = ['startyear' => date('Y', time())];
        $mform->addElement('date_time_selector', 'starttime', get_string('sessiondate', 'hybridteaching'), $attributes);
        $mform->setType('starttime', PARAM_TEXT);

        $duration[] = &$mform->createElement('text', 'duration', get_string('duration', 'hybridteaching'));
        $options = [
            HYBRIDTEACHING_DURATION_TIMETYPE_MINUTES => get_string('minutes'),
            HYBRIDTEACHING_DURATION_TIMETYPE_HOURS => get_string('hours'),
        ];
        $duration[] = &$mform->createElement('select', 'timetype', '', $options);
        $mform->addGroup($duration, 'durationgroup', get_string('duration', 'hybridteaching'));
        $mform->addGroupRule('durationgroup', get_string('required'), 'required');
        $mform->setType('durationgroup[duration]', PARAM_RAW);

        if ($hasgrade) {
            $mform->addElement('advcheckbox', 'attexempt', get_string('attexempt', 'hybridteaching'), '', null, [0, 1]);
        }

        $mform->addElement('editor', 'description', get_string('description'), ['rows' => 1, 'columns' => 80],
                            ['maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext]);
        $mform->setType('description', PARAM_RAW);
        if (!empty($session)) {
            $mform->setDefault('description', $session->description_editor);
        }

        $mform->addElement('filemanager', 'sessionfiles', get_string('presentationfile', 'hybridteaching'), null);
        $mform->setType('sessionfiles', PARAM_RAW);

        if (empty($session)) {
            // For multiple sessions.
            $mform->addElement('header', 'headeraddmultiplesessions', get_string('addmultiplesessions', 'hybridteaching'));

            $mform->addElement('checkbox', 'addmultiply', '', get_string('repeatasfollows', 'hybridteaching'));
            $mform->addHelpButton('addmultiply', 'createmultiplesessions', 'hybridteaching');

            $sdays = [];
            if ($CFG->calendar_startwday === '0') { // Week start from sunday.
                $sdays[] =& $mform->createElement('checkbox', 'Sun', '', get_string('sunday', 'calendar'));
            }
            $sdays[] =& $mform->createElement('checkbox', 'Mon', '', get_string('monday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Tue', '', get_string('tuesday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Wed', '', get_string('wednesday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Thu', '', get_string('thursday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Fri', '', get_string('friday', 'calendar'));
            $sdays[] =& $mform->createElement('checkbox', 'Sat', '', get_string('saturday', 'calendar'));
            if ($CFG->calendar_startwday !== '0') { // Week start from sunday.
                $sdays[] =& $mform->createElement('checkbox', 'Sun', '', get_string('sunday', 'calendar'));
            }
            $mform->addGroup($sdays, 'sdays', get_string('repeaton', 'hybridteaching'),
                ['&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'], true);
            $mform->disabledIf('sdays', 'addmultiply', 'notchecked');

            $period = [1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
                21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, ];
            $periodgroup = [];
            $periodgroup[] =& $mform->createElement('select', 'period', '', $period, false, true);
            $periodgroup[] =& $mform->createElement('static', 'perioddesc', '', get_string('week'));
            $mform->addGroup($periodgroup, 'periodgroup', get_string('repeatevery', 'hybridteaching'), [' '], false);
            $mform->disabledIf('periodgroup', 'addmultiply', 'notchecked');

            $mform->addElement('date_selector', 'sessionenddate', get_string('repeatuntil', 'hybridteaching'),
                $attributes);
            $mform->disabledIf('sessionenddate', 'addmultiply', 'notchecked');

            $mform->addElement('header', 'headeradotheroptions', get_string('otheroptions', 'hybridteaching'));
            $mform->addElement('checkbox', 'replicatedoc', '', get_string('replicatedoc', 'hybridteaching'));
            $mform->setDefault('replicatedoc', '1');
            $mform->addElement('checkbox', 'caleneventpersession', '', get_string('caleneventpersession', 'hybridteaching'));
        } else {
            $mform->addElement('checkbox', 'updatecalen', get_string('updatecalen', 'hybridteaching'));
            $mform->setType('updatecalen', PARAM_INT);
        }

        if (empty($session)) {
            $this->add_action_buttons(true, get_string('add'));
        } else {
            $this->add_action_buttons(true, get_string('savechanges'));
        }
        $this->set_data($session);
    }

    /**
     * Form validation
     *
     * @param object $data Form data
     * @param mixed $files
     * @return array Errors
     */
    public function validation($data, $files) {
        // Programsessions duration errors check.
        $errors = [];
        $dataduration = (int) $data['durationgroup']['duration'];
        $dataduration <= 0 ? $errors['durationgroup'] = get_string('invalidduration', 'hybridteaching') . '<br>' : '';
        if ($data['durationgroup']['timetype'] == 2) {
            $sessionduration = $dataduration * 3600;
        } else if ($data['durationgroup']['timetype'] == 1) {
            $sessionduration = $dataduration * 60;
        } else {
            $sessionduration = 0;
            $errors['starttime'] .= get_string('sessionendbeforestart', 'hybridteaching');
        }

        if (isset($data['addmultiply'])) {
            $enddate = date('d-M-Y', $data['sessionenddate']);
            $datenow = date('d-M-Y', time());
            if ($enddate == $datenow || $data['sessionenddate'] < time()) {
                $enddate == $datenow ? $errors['sessionenddate'] = get_string('repeatsessionsamedate', 'hybridteaching') . '<br>' :
                    $errors['sessionenddate'] = get_string('programsessionbeforenow', 'hybridteaching') . '<br>';
            }
            if (!isset($data['sdays'])) {
                $errors['sessionenddate'] .= get_string('daynotselected', 'hybridteaching');
            }
        } else {
            if ($data['starttime'] + $sessionduration < time()) {
                $errors['starttime'] = get_string('sessionendbeforestart', 'hybridteaching');
            }
        }
        return $errors;
    }
}

