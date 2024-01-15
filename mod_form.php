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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_hybridteaching\helpers\roles;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once('classes/controller/configs_controller.php');

/**
 * Module instance settings form.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_hybridteaching_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;

        $PAGE->requires->js_call_amd('mod_hybridteaching/formconfig', 'init');

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'sectiongeneral', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('hybridteachingname', 'hybridteaching'), ['size' => '64']);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Configuración principal de tipo de videoconferencia, registrar asistencias, otros settings de opciones comunes.

        // Registro de asistencia de estudiantes.
        $mform->addElement('advcheckbox', 'useattendance', '', get_string('useattendance', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('useattendance', 1);
        $mform->addHelpButton('useattendance', 'useattendance', 'hybridteaching');

        // Usar acceso por videoconferencia.
        $mform->addElement('advcheckbox', 'usevideoconference', '', get_string('usevideoconference',
            'hybridteaching'), null, [0, 1]);
        $mform->setDefault('usevideoconference', 0);
        $mform->addHelpButton('usevideoconference', 'usevideoconference', 'hybridteaching');

        // TO-DO ISYC: COMPROBAR SI SE PERMITEN GRABACIONES A NIVEL DE SETTINGS.
        // Si se permiten grabaciones.
        $mform->addElement('advcheckbox', 'userecordvc', '', get_string('userecordvc', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('userecordvc', 0);
        $mform->addHelpButton('userecordvc', 'userecordvc', 'hybridteaching');

        $vcconfigscontroller = new configs_controller(null, 'hybridteachvc');
        $configs = $vcconfigscontroller->hybridteaching_get_configs_select($this->_course->category);
        $mform->addElement('select', 'typevc', get_string('typevc', 'hybridteaching'), $configs);

        // NUEVAS SECCIONES PERSONALIZADAS:.

        $mform->addElement('header', 'sectionsessions', get_string('sectionsessions', 'hybridteaching'));

        $mform->addElement('advcheckbox', 'sessionscheduling', '', get_string('sessionscheduling', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('sessionscheduling', 0);
        $mform->addHelpButton('sessionscheduling', 'sessionscheduling', 'hybridteaching');
        if (isset($PAGE->cm->id)) {
            $mform->addElement('hidden', 'cmgroupmode', $groupmode = groups_get_activity_groupmode($PAGE->cm));
            $mform->setType('cmgroupmode', PARAM_INT);
        }

        $mform->addElement('advcheckbox', 'reusesession', '', get_string('undatedsession', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('reusesession', 0);
        $mform->disabledIf('reusesession', 'usevideoconference', 'notchecked');

        $mform->addElement('date_time_selector', 'starttime', get_string('starttime', 'hybridteaching'), ['optional' => true]);

        $duration[] = &$mform->createElement('text', 'duration', get_string('duration', 'hybridteaching'));
        $mform->setType('duration', PARAM_INT);

        $options = [
            '1' => get_string('minutes'),
            '2' => get_string('hours'),
        ];
        $duration[] = &$mform->createElement('select', 'timetype', '', $options);
        $mform->setType('timetype', PARAM_INT);
        $mform->addGroup($duration, 'durationgroup', get_string('duration', 'hybridteaching'), [' '], false);

        $course = $this->_course;
        $context = context_course::instance($course->id);
        $hybridteachingid = empty($this->get_current()->id) ? null : $this->get_current();
        $participantlist = roles::get_participant_list($hybridteachingid, $context);

        // Now add the instance type profiles to the form as a html hidden field.
        $mform->addElement('html', html_writer::div('', 'd-none', [
            'data-participant-data' => json_encode(roles::get_participant_data($context, $hybridteachingid)),
        ]));

        $PAGE->requires->js_call_amd('mod_hybridteaching/modform', 'init');

        $this->hybridteaching_mform_insert_roles_access_mapping($mform, $participantlist);

        $mform->addElement('header', 'sectionsessionaccess', get_string('sectionsessionaccess', 'hybridteaching'));
        // TO-DO ISYC: ESTAS OPCIONES DEPENDEN DE SI LA VC LO PERMITE O NO.
        // HAY QUE REVISAR SI SE PERMITE. SI NO SE PERMITE, HAY QUE DESACTIVAR Y SACAR UN MSJ SEGÚN 1ª PANTALLA DE PAG 12.

        $mform->addElement('advcheckbox', 'waitmoderator', '', get_string('waitmoderator', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('waitmoderator', 0);

        $units = [get_string('hours'),
                get_string('minutes'),
                get_string('seconds'),
        ];
        $mform->addGroup([
                $mform->createElement('text', 'advanceentrycount', '', ['size' => 5]),
                $mform->createElement('select', 'advanceentryunit', '', $units),
                ], 'advanceentry', get_string('advanceentry', 'hybridteaching'), ' ', false);
        $mform->addHelpButton('advanceentry', 'advanceentry', 'hybridteaching');
        $mform->setType('advanceentrycount', PARAM_INT);
        $mform->disabledIf('advanceentry', 'sessionscheduling', 'notchecked');
        $mform->addGroup([
                $mform->createElement('text', 'closedoorscount', '', ['size' => 5]),
                $mform->createElement('select', 'closedoorsunit', '', $units),
                ], 'closedoors', get_string('closedoors', 'hybridteaching'), ' ', false);
        $mform->addHelpButton('closedoors', 'closedoors', 'hybridteaching');
        $mform->setType('closedoorscount', PARAM_INT);

        $mform->addElement('text', 'userslimit', get_string('userslimit', 'hybridteaching'), ['size' => 6]);
        $mform->addHelpButton('userslimit', 'userslimit', 'hybridteaching');
        $mform->setType('userslimit', PARAM_INT);

        $mform->addGroup([
            $mform->createElement('text', 'graceperiod', '', ['size' => 10]),
            $mform->createElement('select', 'graceperiodunit', '', $units),
            ], 'graceperiod', get_string('graceperiod', 'hybridteaching'), ' ', false);
        $mform->addHelpButton('graceperiod', 'graceperiod', 'hybridteaching');
        $mform->setType('graceperiod', PARAM_INT);

        // Sección Opciones de bloqueo iniciales de la videoconferencia.
        $mform->addElement('header', 'sectioninitialstates', get_string('sectioninitialstates', 'hybridteaching'));

        $mform->addElement('advcheckbox', 'disablecam', '', get_string('disablewebcam', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('disablewebcam', 0);

        $mform->addElement('advcheckbox', 'disablemic', '', get_string('disablemicro', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('disablemicro', 0);

        $mform->addElement('advcheckbox', 'disableprivatechat', '',
            get_string('disableprivatechat', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('disableprivatechat', 0);

        $mform->addElement('advcheckbox', 'disablepublicchat', '', get_string('disablepublicchat', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('disablepublicchat', 0);

        $mform->addElement('advcheckbox', 'disablenote', '', get_string('disablesharednotes', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('disablesharednotes', 0);

        $mform->addElement('advcheckbox', 'hideuserlist', '', get_string('hideuserlist', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('hideuserlist', 0);

        $mform->addElement('advcheckbox', 'blockroomdesign', '', get_string('blockroomdesign', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('blockroomdesign', 0);

        $mform->addElement('advcheckbox', 'ignorelocksettings', '',
            get_string('ignorelocksettings', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('ignorelocksettings', 0);

        // Sección Opciones de grabación.
        $mform->addElement('header', 'sectionrecording', get_string('sectionrecording', 'hybridteaching'));
        // TO-DO ISYC: solo visible y activa esta sección si se ha seleccionado Permitir grabaciones de videoconferencia.

        $mform->addElement('advcheckbox', 'initialrecord', '', get_string('initialrecord', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('initialrecord', 0);

        $mform->addElement('advcheckbox', 'hiderecordbutton', '', get_string('hiderecordbutton', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('hiderecordbutton', 0);

        $mform->addElement('advcheckbox', 'showpreviewrecord', '', get_string('showpreviewrecord', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('showpreviewrecord', 0);

        $mform->addElement('advcheckbox', 'downloadrecords', '', get_string('downloadrecords', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('downloadrecords', 0);

        $mform->addElement('header', 'sectionattendance', get_string('sectionattendance', 'hybridteaching'));

        $units = [get_string('hours'),
            get_string('minutes'),
            get_string('seconds'),
            get_string('totalduration', 'hybridteaching'),
        ];
        $mform->addGroup([
            $mform->createElement('text', 'validateattendance', '', ['size' => 5]),
            $mform->createElement('select', 'attendanceunit', '', $units),
            ], 'attendance', get_string('validateattendance', 'hybridteaching'), ' ', false);
        $mform->addHelpButton('attendance', 'attendance', 'hybridteaching');
        $mform->setType('validateattendance', PARAM_INT);

        $mform->addElement('advcheckbox', 'useqr', '', get_string('useqr', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('useqr', 1);
        $mform->disabledif('useqr', 'rotateqr', 'checked');
        $mform->addElement('advcheckbox', 'rotateqr', '', get_string('rotateqr', 'hybridteaching'), null, [0, 1]);
        $mform->setDefault('rotateqr', 0);
        $mform->addElement('hidden', 'rotateqrsecret', '');
        $mform->setDefault('rotateqrsecret', random_string(8));
        $mform->setType('rotateqrsecret', PARAM_TEXT);
        $mform->disabledIf('rotateqrsecret', 'rotateqr', 'unchecked');
        $mform->addElement('text', 'studentpassword', get_string('studentpassword', 'hybridteaching'));
        $mform->setType('studentpassword', PARAM_TEXT);
        $mform->disabledif('studentpassword', 'rotateqr', 'checked');
        $mform->addHelpButton('studentpassword', 'passwordgrp', 'hybridteaching');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();
        $maxgradeattendance[] = &$mform->createElement('text', 'maxgradeattendance',
            get_string('maxgradeattendance', 'hybridteaching'));
        $mform->setType('maxgradeattendance', PARAM_INT);

        $options = [
            '1' => get_string('numsess', 'hybridteaching'),
            '2' => get_string('percennumatt', 'hybridteaching'),
            '3' => get_string('percentotaltime', 'hybridteaching'),
        ];
        $maxgradeattendance[] = &$mform->createElement('select', 'maxgradeattendanceunit', '', $options);
        $mform->setType('maxgradeattendanceunit', PARAM_INT);
        $mform->addGroup($maxgradeattendance, 'maxgradeattendancegroup',
            get_string('maxgradeattendance', 'hybridteaching'), [' '], false);
        $mform->addHelpButton('maxgradeattendancegroup', 'maxgradeattendance', 'hybridteaching');

        $mform->disabledIf('maxgradeattendancegroup', 'grade[modgrade_type]', 'eq', 'none');
        // $mform->setDefault('grade', false);

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();

        // Load default adminsettings.
        $this->apply_admin_defaults();

        // Load group elements config.
        $this->load_admin_settings();
    }


    /**
     * Add elements for setting the custom completion rules.
     *
     * @category completion
     * @return array List of added element names, or names of wrapping group elements.
     */

    public function add_completion_rules() {

        $mform = $this->_form;

        $group = [
            $mform->createElement('checkbox', 'completionattendanceenabled', '',
                get_string('completionattendance', 'hybridteaching')),
            $mform->createElement('text', 'completionattendance', '', ['size' => 5]),
        ];
        $mform->setType('completionattendance', PARAM_INT);
        $mform->addGroup($group, 'completionattendancegroup',
            get_string('completionattendancegroup', 'hybridteaching'), [' '], false);
        $mform->disabledIf('completionattendance', 'completionattendanceenabled', 'notchecked');

        return ['completionattendancegroup'];
    }

    public function completion_rule_enabled($data) {
        return (!empty($data['completionattendanceenabled']) && $data['completionattendance'] != 0);
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked.
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionattendanceenabled) || !$autocompletion) {
                $data->completionattendance = 0;
            }
        }
        return $data;
    }

    public function data_preprocessing(&$defaultvalues) {
        global $DB;

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $defaultvalues['completionattendanceenabled'] = !empty($defaultvalues['completionattendance']) ? 1 : 0;

        if (empty($defaultvalues['completionattendance'])) {
            $defaultvalues['completionattendance'] = 1;
        }

        // Merge typevc and instance: get the correct value at typevc.
        // Ex: 2-bbb or 1-zoom.
        if (!empty($this->_instance)) {
            $content = $DB->get_record('hybridteaching', ['id' => $this->_instance]);
            if ($content && $content->usevideoconference) {
                $typevc = $content->config."-".$content->typevc;
                $defaultvalues['typevc'] = $typevc;
            }
        }
    }



     /**
      * Function for showing the block for setting participant roles.
      *
      * @param MoodleQuickForm $mform
      * @param array $participantlist
      * @return void
      */
    private function hybridteaching_mform_insert_roles_access_mapping(MoodleQuickForm &$mform, array $participantlist): void {
        global $OUTPUT;
        $participantselection = roles::get_participant_selection_data();
        $mform->addElement('header', 'sectionaudience', get_string('sectionaudience', 'hybridteaching'));
        $mform->addElement('hidden', 'participants', json_encode($participantlist));
        $mform->setType('participants', PARAM_TEXT);
        $selectiontype = new single_select(new moodle_url(qualified_me()),
            'hybridteaching_participant_selection_type',
            $participantselection['type_options'],
            $participantselection['type_selected']);
        $selectionparticipants = new single_select(new moodle_url(qualified_me()),
            'hybridteaching_participant_selection',
            $participantselection['options'],
            $participantselection['selected']);
        $action = new single_button(new moodle_url(qualified_me()),
            get_string('mod_form_field_participant_list_action_add', 'hybridteaching'),
            'post',
            false,
            ['name' => 'hybridteaching_participant_selection_add']
        );
        $pformcontext = [
            'selectionType' => $selectiontype->export_for_template($OUTPUT),
            'selectionParticipant' => $selectionparticipants->export_for_template($OUTPUT),
            'action' => $action->export_for_template($OUTPUT),
        ];
        $html = $OUTPUT->render_from_template('mod_hybridteaching/participant_form', $pformcontext);
        $mform->addElement('static', 'static_participant_list',
            get_string('mod_form_field_participant_list', 'hybridteaching'), $html);
    }

    /**
      * Function used for loading admin settings of grouped elements.
      *
      * @return void
      */
    private function load_admin_settings() : void {
        $groupconfigs = [
            'closedoorscount',
            'closedoorsunit',
            'advanceentrycount',
            'advanceentryunit,',
            'graceperiod',
            'graceperiodunit',
            'validateattendance',
            'attendanceunit',
        ];
        foreach ($groupconfigs as $element) {
            if ($elementconfig = get_config('hybridteaching', $element)) {
                $this->_form->setDefault($element, $elementconfig);
            }
            if ($elementlocked = get_config('hybridteaching', $element . '_locked')) {
                $this->_form->addElement('hidden',  $element . '_locked',  true);
                $this->_form->setType($element . '_locked', PARAM_BOOL);
                $this->_form->disabledIf($element, $element . '_locked', 'eq', true);
            }
        }

    }
}
