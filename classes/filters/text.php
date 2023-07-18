<?php

require_once($CFG->dirroot.'/mod/hybridteaching/classes/filters/lib.php');

class session_filter_text extends session_filter_type {
    /** @var string */
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

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function session_filter_text($name, $label, $advanced, $field) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($name, $label, $advanced, $field);
    }

    /**
     * Returns an array of comparison operators
     * @return array of comparison operators
     */
    public function get_operators() {
        return array(0 => get_string('contains', 'filters'),
                     1 => get_string('doesnotcontain', 'filters'),
                     2 => get_string('isequalto', 'filters'),
                     3 => get_string('startswith', 'filters'),
                     4 => get_string('endswith', 'filters'),
                     5 => get_string('isempty', 'filters'));
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    public function setup_form(&$mform) {
        $objs = array();
        $objs['select'] = $mform->createElement('select', $this->_name.'_op', null, $this->get_operators());
        $objs['text'] = $mform->createElement('text', $this->_name, null);
        $objs['select']->setLabel(get_string('limiterfor', 'filters', $this->_label));
        $objs['text']->setLabel(get_string('valuefor', 'filters', $this->_label));
        $grp =& $mform->addElement('group', $this->_name.'_grp', $this->_label, $objs, '', false);
        $mform->setType($this->_name, PARAM_RAW);
        $mform->disabledIf($this->_name, $this->_name.'_op', 'eq', 5);
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
        $field    = $this->_name;
        $operator = $field.'_op';

        if (property_exists($formdata, $operator)) {
            if ($formdata->$operator != 5 && $formdata->$field == '') {
                // No data - no change except for empty filter.
                return false;
            }
            // If field value is set then use it, else it's null.
            $fieldvalue = null;
            if (isset($formdata->$field)) {
                $fieldvalue = $formdata->$field;

                // If we aren't doing a whitespace comparison, an exact match, trim will give us a better result set.
                $trimmed = trim($fieldvalue);
                if ($trimmed !== '' && $formdata->$operator != 2) {
                    $fieldvalue = $trimmed;
                }
            }
            return array('operator' => (int)$formdata->$operator, 'value' => $fieldvalue);
        }

        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array sql string and $params
     */
    public function get_sql_filter($data) {
        global $DB;
        static $counter = 0;
        $name = 'ex_text'.$counter++;

        $operator = $data['operator'];
        $value    = $data['value'];
        $field    = $this->_field;

        $params = array();

        if ($operator != 5 && $value === '') {
            return '';
        }

        switch($operator) {
            case 0: // Contains.
                $res = $DB->sql_like($field, ":$name", false, false);
                $params[$name] = "%$value%";
                break;
            case 1: // Does not contain.
                $res = $DB->sql_like($field, ":$name", false, false, true);
                $params[$name] = "%$value%";
                break;
            case 2: // Equal to.
                $res = $DB->sql_like($field, ":$name", false, false);
                $params[$name] = "$value";
                break;
            case 3: // Starts with.
                $res = $DB->sql_like($field, ":$name", false, false);
                $params[$name] = "$value%";
                break;
            case 4: // Ends with.
                $res = $DB->sql_like($field, ":$name", false, false);
                $params[$name] = "%$value";
                break;
            case 5: // Empty.
                $res = "$field = :$name";
                $params[$name] = '';
                break;
            default:
                return '';
        }
        return array($res, $params);
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    public function get_label($data) {
        $operator  = $data['operator'];
        $value     = $data['value'];
        $operators = $this->get_operators();

        $a = new stdClass();
        $a->label    = $this->_label;
        $a->value    = '"'.s($value).'"';
        $a->operator = $operators[$operator];

        switch ($operator) {
            case 0: // Contains.
            case 1: // Doesn't contain.
            case 2: // Equal to.
            case 3: // Starts with.
            case 4: // Ends with.
                return get_string('textlabel', 'filters', $a);
            case 5: // Empty.
                return get_string('textlabelnovalue', 'filters', $a);
        }

        return '';
    }
}
