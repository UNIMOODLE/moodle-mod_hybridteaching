<?php

require_once($CFG->libdir.'/formslib.php');

class session_add_filter_form extends moodleform {
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
        $replacefiltersbutton = $mform->createElement('submit', 'replacefilters', get_string('replacefilters', 'filters'));
        $addfilterbutton = $mform->createElement('submit', 'addfilter', get_string('addfilter', 'filters'));
        $buttons = array_filter([
            empty($SESSION->session_filtering) ? null : $replacefiltersbutton,
            $addfilterbutton,
        ]);

        $mform->addGroup($buttons);
    }
}

class session_active_filter_form extends moodleform {

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

            $objs = array();
            $objs[] = &$mform->createElement('submit', 'removeselected', get_string('removeselected', 'filters'));
            $objs[] = &$mform->createElement('submit', 'removeall', get_string('removeall', 'filters'));
            $mform->addElement('group', 'actfiltergrp', '', $objs, ' ', false);
        }
    }
}
