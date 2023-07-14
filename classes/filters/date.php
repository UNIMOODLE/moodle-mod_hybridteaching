<?php

require_once($CFG->dirroot.'/mod/hybridteaching/classes/filters/lib.php');

class session_filter_date extends session_filter_type {
    /**
     * the fields available for comparisson
     * @var string
     */
    public $_field;

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     * @param string $field session table filed name
     */
    public function __construct($name, $label, $advanced, $field) {
        parent::__construct($name, $label, $advanced);
        $this->_field = $field;
    }

    public function session_filter_date($name, $label, $advanced, $field) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($name, $label, $advanced, $field);
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    public function setup_form(&$mform) {
        $objs = array();

        $objs[] = $mform->createElement('static', $this->_name.'_s1', null,
            html_writer::start_tag('div', array('class' => 'w-100 d-flex align-items-center')));
        $objs[] = $mform->createElement('static', $this->_name.'_s2', null,
            html_writer::tag('div', get_string('isafter', 'filters'), array('class' => 'mr-2')));
        $objs[] = $mform->createElement('date_time_selector', $this->_name.'_sdt', null, array('optional' => true));
        $objs[] = $mform->createElement('static', $this->_name.'_s3', null, html_writer::end_tag('div'));
        $objs[] = $mform->createElement('static', $this->_name.'_s4', null,
            html_writer::start_tag('div', array('class' => 'w-100 d-flex align-items-center')));
        $objs[] = $mform->createElement('static', $this->_name.'_s5', null,
            html_writer::tag('div', get_string('isbefore', 'filters'), array('class' => 'mr-2')));
        $objs[] = $mform->createElement('date_time_selector', $this->_name.'_edt', null, array('optional' => true));
        $objs[] = $mform->createElement('static', $this->_name.'_s6', null, html_writer::end_tag('div'));

        $grp =& $mform->addElement('group', $this->_name.'_grp', $this->_label, $objs, '', false);

        if ($this->_advanced) {
            $mform->setAdvanced($this->_name.'_grp');
        }
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    public function check_data($formdata) {
        $sdt = $this->_name.'_sdt';
        $edt = $this->_name.'_edt';

        if (!$formdata->$sdt and !$formdata->$edt) {
            return false;
        }

        $data = array();
        $data['after'] = $formdata->$sdt;
        $data['before'] = $formdata->$edt;

        return $data;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array sql string and $params
     */
    public function get_sql_filter($data) {
        $after  = (int)$data['after'];
        $before = (int)$data['before'];

        $field  = $this->_field;

        if (empty($after) and empty($before)) {
            return array('', array());
        }

        $res = " $field >= 0 ";

        if ($after) {
            $res .= " AND $field >= $after";
        }

        if ($before) {
            $res .= " AND $field <= $before";
        }
        return array($res, array());
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    public function get_label($data) {
        $after  = $data['after'];
        $before = $data['before'];
        $field  = $this->_field;

        $a = new stdClass();
        $a->label  = $this->_label;
        $a->after  = userdate($after);
        $a->before = userdate($before);

        if ($after and $before) {
            return get_string('datelabelisbetween', 'filters', $a);
        } else if ($after) {
            return get_string('datelabelisafter', 'filters', $a);
        } else if ($before) {
            return get_string('datelabelisbefore', 'filters', $a);
        }
        return '';
    }
}
