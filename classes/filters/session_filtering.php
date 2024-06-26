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

namespace mod_hybridteaching\filters;

/**
 * Class session_filtering
 */
class session_filtering {
    /** @var array Fields to filter */
    public $_fields;

    /** @var object Instance of add form */
    public $_addform;

    /** @var object Instance of active filter form */
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
        $slist = optional_param('l', 1, PARAM_INT);

        if (!isset($SESSION->session_filtering)) {
            $SESSION->session_filtering = [];
        }

        if (empty($fieldnames)) {
            $fieldnames = ['groupid' => 0, 'starttime' => 1, 'duration' => 1];
        }

        $this->_fields  = [];

        foreach ($fieldnames as $fieldname => $advanced) {
            if ($field = $this->get_field($fieldname, $advanced)) {
                $this->_fields[$fieldname] = $field;
            }
        }

        $extraparams = ['id' => $id, 'l' => $slist];
        // Fist the new filter form.
        $this->_addform = new session_add_filter_form($baseurl,
            ['fields' => $this->_fields, 'extraparams' => $extraparams]);
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
                    $SESSION->session_filtering[$fname] = [];
                }
                $SESSION->session_filtering[$fname][] = $data;
            }
        }

        // Now the active filters.
        $this->_activeform = new session_active_filter_form($baseurl,
            ['fields' => $this->_fields, 'extraparams' => $extraparams]);
        if ($activedata = $this->_activeform->get_data()) {
            if (!empty($activedata->removeall)) {
                $SESSION->session_filtering = [];

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
            $this->_addform = new session_add_filter_form($baseurl,
                ['fields' => $this->_fields, 'extraparams' => $extraparams]);
            $this->_activeform = new session_active_filter_form($baseurl,
                ['fields' => $this->_fields, 'extraparams' => $extraparams]);
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
                $context = \context_module::instance($cm->id);
                if (has_capability('mod/hybridteaching:sessionsfulltable', $context)) {
                    $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
                } else if ($groupmode == VISIBLEGROUPS) {
                    $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
                } else if ($groupmode == SEPARATEGROUPS) {
                    $groups = groups_get_all_groups($course->id, $USER->id, $cm->groupingid);
                }
                $choices = [];
                if (has_capability('mod/hybridteaching:sessionsfulltable', $context) || $groupmode != SEPARATEGROUPS) {
                    $choices[0] = get_string('allgroups', 'hybridteaching');
                }
                if (!empty($groups)) {
                    foreach ($groups as $group) {
                        $choices[$group->id] = $group->name;
                    }
                }
                return new session_filter_select('groupid', get_string('groups'), $advanced, 'groupid', $choices);
            case 'starttime':
                return new session_filter_date('starttime', get_string('date'), $advanced, 'starttime');
            case 'duration':
                return new session_filter_duration('duration', get_string('duration', 'hybridteaching'),
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

        $sqls = [];
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
            return ['', []];
        } else {
            $sqls = implode(' AND ', $sqls);
            return [$sqls, $params];
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

