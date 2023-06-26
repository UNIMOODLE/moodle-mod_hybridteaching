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
 * Plugin administration pages are defined here.
 *
 * @package     mod_hybridteaching
 * @category    admin
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_table\dynamic as dynamic_table;

require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/user/lib.php');

class hybridteaching_sessions_render extends \table_sql implements dynamic_table {
    protected $hybridteaching;

    public function __construct($hybridteaching) {
        $this->hybridteaching = $hybridteaching;
    }

    /**
     * Builds the XHTML to display the control
     *
     * @param string $query
     * @return string
     */
    public function print_sessions_table() {
        global $OUTPUT, $DB, $PAGE;

        $id = required_param('id', PARAM_INT);
        $hybridteachingid = optional_param('h', 0, PARAM_INT);    
        $page = optional_param('page', 0, PARAM_INT);
        $perpage = optional_param('perpage', 10, PARAM_INT);    

        // Display strings.
        $strgroup = get_string('group');
        $strtype = get_string('type', 'mod_hybridteaching');
        $strname = get_string('name');
        $strdate = get_string('date');
        $strduration = get_string('duration', 'mod_hybridteaching');
        $strrecording = get_string('recording', 'mod_hybridteaching');
        $strattendance = get_string('attendance', 'mod_hybridteaching');
        $strmaterials = get_string('materials', 'mod_hybridteaching');
        $struninstall = get_string('delete');
        $stroptions = get_string('actions', 'mod_hybridteaching');
        $strdisable = get_string('disable');
        $strenable = get_string('enable');

        $return = $OUTPUT->box_start('generalbox');

        $mastercheckbox = new \core\output\checkbox_toggleall('sessions-table', true, [
            'id' => 'select-all-sessions',
            'name' => 'select-all-sessions',
            'label' => get_string('selectall'),
            'labelclasses' => 'sr-only',
            'classes' => 'm-1',
            'checked' => false,
        ]);

        $table = new html_table();
        $table->head = array($OUTPUT->render($mastercheckbox), $strgroup, $strtype, $strname, $strdate, $strduration, 
            $strrecording, $strattendance, $strmaterials, $stroptions);
        $table->colclasses = array('leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'hybridteachingsessions';
        $table->attributes['class'] = 'sessionstable generaltable';
        $table->data = array();

        $type = $this->hybridteaching->typevc;
        $typealias = '';
        if (!empty($type)) {
            $typealias = get_string('alias', 'hybridteachvc_'.$type);
        }
        $url = new moodle_url('/mod/hybridteaching/classes/action/session_action.php', array('sesskey' => sesskey()));
        $sessioncontroller = new sessions_controller($this->hybridteaching);
        $params = [];
        if ($this->hybridteaching->sessionscheduling) {
            $params = ['starttime' => time()];
        }
        $sessionslist = $sessioncontroller->load_sessions($page, $perpage, $params, sessions_controller::OPERATOR_LESS_THAN);
        $sessionscount = $sessioncontroller->count_sessions($params, sessions_controller::OPERATOR_LESS_THAN);
        $cm = get_coursemodule_from_instance('hybridteaching', $this->hybridteaching->id);
        $returnurl = new moodle_url('/mod/hybridteaching/sessions.php?id='.$cm->id.'&h='.$this->hybridteaching->id);
        foreach ($sessionslist as $session) {
            // Hide/show links.
            $class = '';
            $sessionid = $session['id'];
            $group = $session['groupid'] == 0 ? get_string('commonsession', 'hybridteaching') : groups_get_group($session['groupid']);
            $name = $session['name'];
            $date = $session['starttime'];
            $date = date('l, j \d\e F \d\e Y, H:i', $date);
            $duration = $this->get_hours_format($session['duration']);
            $recordingbutton = html_writer::start_tag('input', ['type' => 'button', 'value' => $strrecording]);
            $attendance = '75/100 (75%)';
            $materials = 'Recursos';
            $enabled = $session['visible'];

            $checkbox = new \core\output\checkbox_toggleall('sessions-table', false, [
                'id' => 'session-' . $sessionid,
                'name' => 'session-' . $sessionid,
                'classes' => 'm-1',
                'checked' => false,
            ]);

            $options = '';
            $hybridteachingid = empty($hybridteaching) ? $cm->instance : $hybridteachingid;
            $params = array('sid' => $sessionid, 'h' => $hybridteachingid, 'id' => $id, 'returnurl' => $returnurl);
            if ($enabled) {
                $options .= html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'disable'))),
                    $OUTPUT->pix_icon('t/hide', $strdisable, 'moodle', array('class' => 'iconsmall')));
            } else {
                $options .= html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'enable'))),
                    $OUTPUT->pix_icon('t/show', $strenable, 'moodle', array('class' => 'iconsmall')));
                $class = 'dimmed_text';
            }

            $options .= html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'delete'))),
                $OUTPUT->pix_icon('t/delete', $struninstall, 'moodle', array('class' => 'iconsmall')),
                array('onclick' => 'if (!confirm("'.get_string('deleteconfirm', 'mod_hybridteaching', $name).'"))
                { return false; }')
            );

            $options .= html_writer::link(new moodle_url(''),
                $OUTPUT->pix_icon('docs', $stroptions, 'moodle', array('class' => 'iconsmall')));

            $options .= html_writer::link(new moodle_url(''),
                $OUTPUT->pix_icon('i/messagecontentvideo', $stroptions, 'moodle', array('class' => 'iconsmall')));
            
            $options .= html_writer::link(new moodle_url(''),
                $OUTPUT->pix_icon('i/unchecked', $stroptions, 'moodle', array('class' => 'iconsmall')));

            // Add a row to the table.
            $row = new html_table_row(array($OUTPUT->render($checkbox), $group, $typealias, $name, $date, $duration, $recordingbutton, $attendance, $materials, $options));
            if (!empty($class)) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }

        $return .= html_writer::table($table);
        $baseurl = new moodle_url('/mod/hybridteaching/sessions.php', array('id' => $id, 'h' => $hybridteachingid, 'perpage' => $perpage));
        $return .= $OUTPUT->paging_bar($sessionscount, $page, $perpage, $baseurl);

        $selectactionparams = array(
            'id' => 'sessionid',
            'class' => 'ml-2',
            'data-action' => 'toggle',
            'data-togglegroup' => 'sessions-table',
            'data-toggle' => 'action',
            'disabled' => 'disabled'
        );

        $label = html_writer::tag('label', get_string("withselectedusers"),
            ['for' => 'sessionid', 'class' => 'col-form-label d-inline']);
        $select = html_writer::select(["In progress"], 'session', '', ['' => 'choosedots'], $selectactionparams);
        $return .= html_writer::tag('div', $label . $select);

        $return .= $OUTPUT->box_end();
        
        return $return;
    }


    public function print_sessions_programming_table() {
        global $OUTPUT, $DB, $PAGE, $COURSE;

        $id = required_param('id', PARAM_INT);
        $hybridteachingid = optional_param('h', 0, PARAM_INT);    
        $page = optional_param('page', 0, PARAM_INT);
        $perpage = optional_param('perpage', 10, PARAM_INT);    

        // Display strings.
        $strdate = get_string('date');
        $strstart = get_string('start', 'mod_hybridteaching');
        $strduration = get_string('duration', 'mod_hybridteaching');
        $strgroup = get_string('group');
        $strname = get_string('name');
        $strmaterials = get_string('materials', 'mod_hybridteaching');
        $stroptions = get_string('actions', 'mod_hybridteaching');
        $struninstall = get_string('delete');
        $strdisable = get_string('disable');
        $strenable = get_string('enable');

        $return = $OUTPUT->box_start('generalbox');

        $mastercheckbox = new \core\output\checkbox_toggleall('sessions-table', true, [
            'id' => 'select-all-sessions',
            'name' => 'select-all-sessions',
            'label' => get_string('selectall'),
            'labelclasses' => 'sr-only',
            'classes' => 'm-1',
            'checked' => false,
        ]);

        $table = new html_table();
        $table->head = array($OUTPUT->render($mastercheckbox), $strdate, $strstart, $strduration, $strgroup, 
            $strname, $strmaterials, $stroptions);
        $table->colclasses = array('leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'hybridteachingsessions';
        $table->attributes['class'] = 'sessionstable generaltable';
        $table->data = array();

        $url = new moodle_url('/mod/hybridteaching/classes/action/session_action.php', array('sesskey' => sesskey()));
        $sessioncontroller = new sessions_controller($this->hybridteaching);
        $params = ['starttime' => time()];
        $sessionslist = $sessioncontroller->load_sessions($page, $perpage, $params);
        $sessionscount = $sessioncontroller->count_sessions($params);
        $cm = get_coursemodule_from_instance('hybridteaching', $this->hybridteaching->id);
        $returnurl = new moodle_url('/mod/hybridteaching/programschedule.php?id='.$cm->id);
        foreach ($sessionslist as $session) {
            // Hide/show links.
            $class = '';
            $sessionid = $session['id'];
            $group = $session['groupid'] == 0 ? get_string('commonsession', 'hybridteaching') : groups_get_group($session['groupid'])->name;
            $name = $session['name'];
            /*$description = file_rewrite_pluginfile_urls($session['description'],
                'pluginfile.php', $this->hybridteaching->context->id, 'mod_hybridteaching', 'session', $sessionid);*/
            $description = $session['description'];
            $date = $session['starttime'];
            $hour = date('H:i', $date);
            $date = date('l, j \d\e F \d\e Y', $date);
            $duration = $this->get_hours_format($session['duration']);
            $materials = 'Recursos';
            $enabled = $session['visible'];

            $checkbox = new \core\output\checkbox_toggleall('sessions-table', false, [
                'id' => 'session-' . $sessionid,
                'name' => 'session-' . $sessionid,
                'classes' => 'm-1',
                'checked' => false,
            ]);

            $options = '';
            $hybridteachingid = empty($hybridteaching) ? $cm->instance : $hybridteachingid;
            $params = array('sid' => $sessionid, 'h' => $hybridteachingid, 'id' => $id, 'returnurl' => $returnurl);
            /*if ($enabled) {
                $options .= html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'disable'))),
                    $OUTPUT->pix_icon('t/hide', $strdisable, 'moodle', array('class' => 'iconsmall')));
            } else {
                $options .= html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'enable'))),
                    $OUTPUT->pix_icon('t/show', $strenable, 'moodle', array('class' => 'iconsmall')));
                $class = 'dimmed_text';
            }*/

            $urledit = '/mod/hybridteaching/programsessions.php?id='.$cm->id .'&s='.$sessionid;
            $options .= html_writer::link(new moodle_url($urledit),
                $OUTPUT->pix_icon('t/edit', $stroptions, 'moodle', array('class' => 'iconsmall')));

            $options .= html_writer::link(new moodle_url(''),
                $OUTPUT->pix_icon('docs', $stroptions, 'moodle', array('class' => 'iconsmall')));

            $options .= html_writer::link(new moodle_url($url, array_merge($params, array('action' => 'delete'))),
                $OUTPUT->pix_icon('t/delete', $struninstall, 'moodle', array('class' => 'iconsmall')),
                array('onclick' => 'if (!confirm("'.get_string('deleteconfirm', 'mod_hybridteaching', $name).'"))
                { return false; }')
            );

            // Add a row to the table.
            $row = new html_table_row(array($OUTPUT->render($checkbox), $date, $hour, $duration, $group, $name, $materials, $options));
            if (!empty($class)) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }

        $return .= html_writer::table($table);
        $baseurl = new moodle_url('/mod/hybridteaching/programschedule.php', array('id' => $id, 'h' => $hybridteachingid, 'perpage' => $perpage));
        $return .= $OUTPUT->paging_bar($sessionscount, $page, $perpage, $baseurl);

        $selectactionparams = array(
            'id' => 'sessionid',
            'class' => 'ml-2',
            'data-action' => 'toggle',
            'data-togglegroup' => 'sessions-table',
            'data-toggle' => 'action',
            'disabled' => 'disabled'
        );

        $label = html_writer::tag('label', get_string("withselectedusers"),
            ['for' => 'sessionid', 'class' => 'col-form-label d-inline']);
        $select = html_writer::select(["In progress"], 'session', '', ['' => 'choosedots'], $selectactionparams);
        $return .= html_writer::tag('div', $label . $select);

        $return .= $OUTPUT->box_end();
        
        return $return;
    }

    public function get_hours_format($secs) {
        $hours = floor($secs / 3600);
        $minutes = floor(($secs - ($hours * 3600)) / 60);
        $formattime = '';
        
        if ($hours > 0) {
            $formattime .= $hours . ' h ';
        }

        if ($minutes > 0) {
            $formattime .=  $minutes . ' min';
        }
        return $formattime;
    }
}
