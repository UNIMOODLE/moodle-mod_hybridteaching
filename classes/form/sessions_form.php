<?php

require_once($CFG->libdir.'/formslib.php');

class sessions_form extends moodleform {
    public function definition() {
        global $CFG, $USER, $DB;
        $mform = &$this->_form;

        $course = $this->_customdata['course'];
        $cm = $this->_customdata['cm'];
        $session = $this->_customdata['session'];
        $typevc = $this->_customdata['typevc'];

        $modcontext = context_module::instance($cm->id);
        $defopts = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext);
        $session = file_prepare_standard_editor($session, 'description', 
            $defopts, $modcontext, 'mod_hybridteaching', 'session', $session->sessionid);

        if (empty($session)) {
            $headertitle = get_string('addsession', 'hybridteaching');
        } else {
            $headertitle = get_string('editsession', 'hybridteaching');
        }
        
        $mform->addElement('header', 'general', get_string('addsession', 'hybridteaching'));

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
        $selectgroups = array();
        $selectgroups[0] = get_string('commonsession', 'hybridteaching');
        if ($groupmode == SEPARATEGROUPS || $groupmode == VISIBLEGROUPS) {
            if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $modcontext)) {
                $groups = groups_get_all_groups($course->id, $USER->id, $cm->groupingid);
            } else {
                $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
            }
            if ($groups) {
                foreach ($groups as $group) {
                    $selectgroups[$group->id] = $group->name;
                }
                if ($groupmode == SEPARATEGROUPS) {
                    array_shift($selectgroups);
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

        $mform->addElement('date_time_selector', 'starttime', get_string('sessiondate', 'hybridteaching'));
        $mform->setType('starttime', PARAM_TEXT);

        $duration[] = &$mform->createElement('text', 'duration', get_string('duration', 'hybridteaching'));
        $mform->setType('duration', PARAM_INT);

        $options = array(
            '1' => get_string('minutes'),
            '2' => get_string('hours')
        );
        $duration[] = &$mform->createElement('select', 'timetype', '', $options);
        $mform->setType('timetype', PARAM_INT);
        $mform->addGroup($duration, 'durationgroup', get_string('duration', 'hybridteaching'), array(' '), false);
        $mform->addRule('durationgroup', null, 'required', null, 'client');

        $mform->addElement('editor', 'description', get_string('description'), array('rows' => 1, 'columns' => 80),
                            array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $modcontext));
        $mform->setType('description', PARAM_RAW);
        if (!empty($session)) {
            $mform->setDefault('description', $session->description_editor);
        }

        $mform->addElement('filemanager', 'sessionfiles', get_string('presentationfile', 'hybridteaching'), null);
        $mform->setType('sessionfiles', PARAM_RAW);

        if (empty($session)) {
            // For multiple sessions.
            $mform->addElement('header', 'headeraddmultiplesessions', get_string('addmultiplesessions', 'hybridteaching'));
            /*if (!empty($pluginconfig->multisessionexpanded)) {
                $mform->setExpanded('headeraddmultiplesessions');
            }*/
            $mform->addElement('checkbox', 'addmultiply', '', get_string('repeatasfollows', 'hybridteaching'));
            $mform->addHelpButton('addmultiply', 'createmultiplesessions', 'hybridteaching');

            $sdays = array();
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
                array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), true);
            $mform->disabledIf('sdays', 'addmultiply', 'notchecked');

            $period = array(1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
                21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36);
            $periodgroup = array();
            $periodgroup[] =& $mform->createElement('select', 'period', '', $period, false, true);
            $periodgroup[] =& $mform->createElement('static', 'perioddesc', '', get_string('week'));
            $mform->addGroup($periodgroup, 'periodgroup', get_string('repeatevery', 'hybridteaching'), array(' '), false);
            $mform->disabledIf('periodgroup', 'addmultiply', 'notchecked');

            $mform->addElement('date_selector', 'sessionenddate', get_string('repeatuntil', 'hybridteaching'));
            $mform->disabledIf('sessionenddate', 'addmultiply', 'notchecked');
        }

        $mform->addElement('header', 'headeradotheroptions', get_string('otheroptions', 'hybridteaching'));
        $mform->addElement('checkbox', 'replicatedoc', '', get_string('replicatedoc', 'hybridteaching'));
        $mform->addElement('checkbox', 'caleneventpersession', '', get_string('caleneventpersession', 'hybridteaching'));

        if (empty($session)) {
            $this->add_action_buttons(true, get_string('add'));
        } else {
            $this->add_action_buttons(true, get_string('savechanges'));
        }
        $this->set_data($session);
    }
}


class bulk_update_duration_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];
        $ids = $this->_customdata['sesslist'];
        $hybridteaching = $this->_customdata['hybridteaching'];
        $slist = $this->_customdata['slist'];

        $mform->addElement('header', 'general', get_string('updatesesduration', 'hybridteaching'));
        $options = array(
            '1' => get_string('seton', 'hybridteaching'),
            '2' => get_string('extend', 'hybridteaching'),
            '3' => get_string('reduce', 'hybridteaching')
        );
        $mform->addElement('select', 'operation', get_string('updateduration', 'hybridteaching'), $options);
        $mform->setType('operation', PARAM_INT);
        $duration[] = &$mform->createElement('text', 'duration', get_string('duration', 'hybridteaching'));
        $mform->setType('duration', PARAM_INT);

        $options = array(
            '1' => get_string('minutes'),
            '2' => get_string('hours')
        );
        $duration[] = &$mform->createElement('select', 'timetype', '', $options);
        $mform->setType('timetype', PARAM_INT);
        $mform->addGroup($duration, 'durationgroup', get_string('time'), array(' '), false);

        $mform->addElement('hidden', 'action', 'bulkupdateduration');
        $mform->setType('action', PARAM_INT);
        addhiddens($mform, $ids, $cm, $hybridteaching, $slist);
        $submitstring = get_string('updatesessions', 'hybridteaching');
        $this->add_action_buttons(true, $submitstring);
    }
}


class bulk_update_starttime_form extends moodleform {
    public function definition() {
        $mform =& $this->_form;
        $cm = $this->_customdata['cm'];
        $ids = $this->_customdata['sesslist'];
        $hybridteaching = $this->_customdata['hybridteaching'];
        $slist = $this->_customdata['slist'];

        $mform->addElement('header', 'general', get_string('updatesesstarttime', 'hybridteaching'));
        $options = array(
            '3' => get_string('advance', 'hybridteaching'),
            '2' => get_string('delayin', 'hybridteaching')
        );
        $mform->addElement('select', 'operation', get_string('updatestarttime', 'hybridteaching'), $options);
        $mform->setType('operation', PARAM_INT);
        $starttime[] = &$mform->createElement('text', 'starttime', get_string('starttime', 'hybridteaching'));
        $mform->setType('starttime', PARAM_INT);

        $options = array(
            '1' => get_string('minutes'),
            '2' => get_string('hours')
        );
        $starttime[] = &$mform->createElement('select', 'timetype', '', $options);
        $mform->setType('timetype', PARAM_INT);
        $mform->addGroup($starttime, 'starttimegroup', get_string('time'), array(' '), false);

        $mform->addElement('hidden', 'action', 'bulkupdatestarttime');
        $mform->setType('action', PARAM_INT);
        addhiddens($mform, $ids, $cm, $hybridteaching, $slist);
        $submitstring = get_string('updatesessions', 'hybridteaching');
        $this->add_action_buttons(true, $submitstring);
    }
}

class session_options_form extends moodleform {
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
        $mform->addElement('header', 'options', get_string('options', 'mod_hybridteaching'));
        $perpage = [
            '5' => 5,
            '10' => 10,
            '20' => 20,
            '50' => 50,
            '100' => 100
        ];
        $mform->addElement('select', 'perpage', get_string('sesperpage', 'mod_hybridteaching'), $perpage);
        $mform->setDefault('perpage', 10);
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
