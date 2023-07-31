<?php

require_once($CFG->dirroot.'/mod/hybridteaching/classes/filters/text.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/filters/duration.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/filters/date.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/filters/select.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/filters/session_filter_forms.php');

class session_filtering {
    public $_fields;
    public $_addform;
    public $_activeform;

    /**
     * Contructor
     * @param array $fieldnames array of visible session fields
     * @param string $baseurl base url used for submission/return, null if the same of current page
     * @param array $extraparams extra page parameters
     */
    public function __construct($fieldnames = null, $baseurl = null, $extraparams = null) {
        global $SESSION;

        $id = required_param('id', PARAM_INT);
        $slist = required_param('l', PARAM_INT);

        if (!isset($SESSION->session_filtering)) {
            $SESSION->session_filtering = array();
        }

        if (empty($fieldnames)) {
            $fieldnames = array('groupid' => 0, 'starttime' => 1, 'duration' => 1);
        }

        $this->_fields  = array();

        foreach ($fieldnames as $fieldname => $advanced) {
            if ($field = $this->get_field($fieldname, $advanced)) {
                $this->_fields[$fieldname] = $field;
            }
        }

        $extraparams = ['id' => $id, 'l' => $slist];
        // Fist the new filter form.
        $this->_addform = new session_add_filter_form($baseurl,
            array('fields' => $this->_fields, 'extraparams' => $extraparams));
        if ($adddata = $this->_addform->get_data()) {
            // Clear previous filters.
            if (!empty($adddata->replacefilters)) {
                $SESSION->session_filtering = [];
            }

            // Add new filters.
            foreach ($this->_fields as $fname => $field) {
                $data = $field->check_data($adddata);
                if ($data === false) {
                    continue; // Nothing new.
                }
                if (!array_key_exists($fname, $SESSION->session_filtering)) {
                    $SESSION->session_filtering[$fname] = array();
                }
                $SESSION->session_filtering[$fname][] = $data;
            }
        }

        // Now the active filters.
        $this->_activeform = new session_active_filter_form($baseurl,
            array('fields' => $this->_fields, 'extraparams' => $extraparams));
        if ($activedata = $this->_activeform->get_data()) {
            if (!empty($activedata->removeall)) {
                $SESSION->session_filtering = array();

            } else if (!empty($activedata->removeselected) && !empty($activedata->filter)) {
                foreach ($activedata->filter as $fname => $instances) {
                    foreach ($instances as $i => $val) {
                        if (empty($val)) {
                            continue;
                        }
                        unset($SESSION->session_filtering[$fname][$i]);
                    }
                    if (empty($SESSION->session_filtering[$fname])) {
                        unset($SESSION->session_filtering[$fname]);
                    }
                }
            }
        }

        // Rebuild the forms if filters data was processed.
        if ($adddata || $activedata) {
            $_POST = []; // Reset submitted data.
            $this->_addform = new session_add_filter_form($baseurl, ['fields' => $this->_fields, 'extraparams' => $extraparams]);
            $this->_activeform = new session_active_filter_form($baseurl, ['fields' => $this->_fields, 'extraparams' => $extraparams]);
        }
    }

    /**
     * Creates known session filter if present
     * @param string $fieldname
     * @param boolean $advanced
     * @return object filter
     */
    public function get_field($fieldname, $advanced) {
        global $USER;
        $id = required_param('id', PARAM_INT);

        switch ($fieldname) {
            case 'groupid':
                list($course, $cm) = get_course_and_cm_from_cmid($id, 'hybridteaching');
                $groupmode = groups_get_activity_groupmode($cm);
                $context = context_module::instance($cm->id);
                if (has_capability('mod/hybridteaching:sessionsfulltable', $context)) {
                    $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
                } else if ($groupmode == VISIBLEGROUPS) {
                    $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
                } else if ($groupmode == SEPARATEGROUPS) {
                    $groups = groups_get_all_groups($course->id, $USER->id, $cm->groupingid);
                }
                $choices = array();
                $choices[0] = get_string('commonsession', 'hybridteaching');
                foreach ($groups as $group) {
                    $choices[$group->id] = $group->name;
                }
                return new session_filter_select('groupid', get_string('groups'), $advanced, 'groupid', $choices);
            case 'starttime': 
                return new session_filter_date('starttime', get_string('date'), $advanced, 'starttime');
            case 'duration': 
                return new session_filter_duration('duration', get_string('duration', 'mod_hybridteaching'),
                    $advanced, 'duration');
            default:
                return null;
        }
    }

    /**
     * Returns sql where statement based on active session filters
     * @param string $extra sql
     * @param array $params named params (recommended prefix ex)
     * @return array sql string and $params
     */
    public function get_sql_filter($extra='', array $params=null) {
        global $SESSION;

        $sqls = array();
        if ($extra != '') {
            $sqls[] = $extra;
        }
        $params = (array)$params;

        if (!empty($SESSION->session_filtering)) {
            foreach ($SESSION->session_filtering as $fname => $datas) {
                if (!array_key_exists($fname, $this->_fields)) {
                    continue; // Filter not used.
                }
                $field = $this->_fields[$fname];
                foreach ($datas as $i => $data) {
                    list($s, $p) = $field->get_sql_filter($data);
                    $sqls[] = $s;
                    $params = $params + $p;
                }
            }
        }

        if (empty($sqls)) {
            return array('', array());
        } else {
            $sqls = implode(' AND ', $sqls);
            return array($sqls, $params);
        }
    }

    /**
     * Print the add filter form.
     */
    public function display_add() {
        $this->_addform->display();
    }

    /**
     * Print the active filter form.
     */
    public function display_active() {
        $this->_activeform->display();
    }

}

class session_filter_type {
    /**
     * The name of this filter instance.
     * @var string
     */
    public $_name;

    /**
     * The label of this filter instance.
     * @var string
     */
    public $_label;

    /**
     * Advanced form element flag
     * @var bool
     */
    public $_advanced;

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    public function __construct($name, $label, $advanced) {
        $this->_name     = $name;
        $this->_label    = $label;
        $this->_advanced = $advanced;
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function session_filter_type($name, $label, $advanced) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($name, $label, $advanced);
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return string the filtering condition or null if the filter is disabled
     */
    public function get_sql_filter($data) {
        throw new \moodle_exception('mustbeoveride', 'debug', '', 'get_sql_filter');
    }

    /**
     * Retrieves data from the form data
     * @param stdClass $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    public function check_data($formdata) {
        throw new \moodle_exception('mustbeoveride', 'debug', '', 'check_data');
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param moodleform $mform a MoodleForm object to setup
     */
    public function setup_form(&$mform) {
        throw new \moodle_exception('mustbeoveride', 'debug', '', 'setup_form');
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    public function get_label($data) {
        throw new \moodle_exception('mustbeoveride', 'debug', '', 'get_label');
    }
}
