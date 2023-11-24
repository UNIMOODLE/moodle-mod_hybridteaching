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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/configs_controller.php');

class hybridteaching_admin_plugins_configs extends admin_setting {
    protected $splugintype;
    protected $splugindir;
    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $splugintype) {
        $this->nosave = true;
        $this->splugindir = configs_controller::get_subplugin_dir($splugintype);
        $this->splugintype = $splugintype;

        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Always returns '', does not write anything
     *
     * @param mixed $data
     * @return string Always returns ''
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Builds the XHTML to display the control
     *
     * @param string $data Unused
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $CFG, $OUTPUT, $DB, $PAGE;

        // Display strings.
        $strorder = get_string('order', 'mod_hybridteaching');
        $stroptions = get_string('options', 'mod_hybridteaching');
        $strstate = get_string('hideshow', 'mod_hybridteaching');
        $struninstall = get_string('delete');
        $strversion = get_string('version');
        $strdisable = get_string('disable');
        $strenable = get_string('enable');
        $strup = get_string('up');
        $strdown = get_string('down');
        $strname = get_string('name');
        $strtype = get_string('type', 'mod_hybridteaching');
        $strcategories = get_string('categories');
        $section = optional_param('section', '', PARAM_COMPONENT);
        $message = optional_param('message', 0, PARAM_COMPONENT);

        $return = $OUTPUT->box_start('generalbox hybridteachingpluginsconfigs');

        $table = new html_table();
        $table->head = [$strname, $strtype, $strcategories, $strversion, $strorder, $stroptions];
        $table->colclasses = ['leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign', ];
        $table->id = 'hybridteachingpluginsconfigs';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data = [];

        $updowncount = 1;
        $printed = [];
        $spacer = $OUTPUT->pix_icon('spacer', '', 'moodle', ['class' => 'iconsmall']);

        $url = new moodle_url('/mod/hybridteaching/classes/action/config_action.php',
            ['sesskey' => sesskey(), 'section' => $section]);
        $configcontroller = new configs_controller(null, $this->splugintype);
        $configlist = $configcontroller->hybridteaching_get_configs();
        $enabledconfigs = $configcontroller->get_enabled_data('hybridteaching_configs', ['subplugintype' => $this->splugindir]);
        if (!empty($message)) {
            \core\notification::add(get_string($message, 'mod_hybridteaching'), \core\output\notification::NOTIFY_INFO);
        }
        foreach ($configlist as $config) {
            // Hide/show links.
            $class = '';
            $enabled = $config['visible'];
            $configid = $config['id'];
            $configname = $config['configname'];
            $categoryname = !empty($config['category']) ? $DB->get_field('course_categories',
                'name', ['id' => $config['category']]) : get_string('all');
            $configcategories = $categoryname;
            $configversion = $config['version'];
            $configtype = $config['type'];
            $configtypealias = get_string('alias', $this->splugintype.'_'.$config['type']);

            // Up/down link (only if enrol is enabled).
            $updown = '';
            if ($enabled) {
                if ($updowncount > 1) {
                    $updown = html_writer::link(new moodle_url($url, ['action' => 'up', 'id' => $configid]),
                        $OUTPUT->pix_icon('t/up', $strup, 'moodle', ['class' => 'iconsmall']));
                } else {
                    $updown = $spacer;
                }
                if ($updowncount < $enabledconfigs) {
                    $updown .= html_writer::link(new moodle_url($url, ['action' => 'down', 'id' => $configid]),
                        $OUTPUT->pix_icon('t/down', $strdown, 'moodle', ['class' => 'iconsmall']));
                } else {
                    $updown .= $spacer;
                }
                ++$updowncount;
            }

            $options = '';
            if ($enabled) {
                $options .= html_writer::link(new moodle_url($url, ['action' => 'disable', 'id' => $configid]),
                    $OUTPUT->pix_icon('t/hide', $strdisable, 'moodle', ['class' => 'iconsmall']));
            } else {
                $options .= html_writer::link(new moodle_url($url, ['action' => 'enable', 'id' => $configid]),
                    $OUTPUT->pix_icon('t/show', $strenable, 'moodle', ['class' => 'iconsmall']));
                $class = 'dimmed_text';
            }

            $urledit = '/mod/hybridteaching/'. $this->splugindir .'/'.$configtype.
                '/editconfig.php?type='.$configtype.'&id='.$configid;
            $options .= html_writer::link(new moodle_url($urledit),
                $OUTPUT->pix_icon('t/edit', $stroptions, 'moodle', ['class' => 'iconsmall']));

            $htmodules = $DB->get_fieldset_select('hybridteaching', 'name', 'config = :config',
                ['config' => $configid], IGNORE_MISSING);
            $stringtodelete = !empty($htmodules) ?
                get_string('deletewithhybridmods', 'mod_hybridteaching', implode(', ', $htmodules)) :
                get_string('deleteconfirm', 'mod_hybridteaching', $configname);
            $options .= html_writer::link(new moodle_url($url, ['action' => 'delete', 'id' => $configid]),
                $OUTPUT->pix_icon('t/delete', $struninstall, 'moodle', ['class' => 'iconsmall']),
                ['onclick' => 'if (!confirm("'.$stringtodelete.'"))
                { return false; }', ]
            );

            // Add a row to the table.
            $row = new html_table_row([$configname, $configtypealias, $configcategories, $configversion, $updown, $options]);
            if (!empty($class)) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }

        $return .= html_writer::table($table);
        if (!empty($section)) {
            $selectsubplugins = $this->create_subplugin_select();
            echo $OUTPUT->render($selectsubplugins);
        }

        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }

    public function create_subplugin_select() {
        $subpluginsarray = [];
        $pluginmanager = core_plugin_manager::instance();
        $subplugins = $pluginmanager->get_subplugins_of_plugin('mod_hybridteaching');
        foreach ($subplugins as $subplugin) {
            if ($subplugin->type == $this->splugintype) {
                $url = new moodle_url('/mod/hybridteaching/'. $this->splugindir .'/'.$subplugin->name.
                    '/editconfig.php?type='.$subplugin->name);
                $link = $url->out();
                $subplugincommonname = get_string('alias', $this->splugintype.'_'.$subplugin->name);
                $subpluginsarray[$link] = $subplugincommonname;
            }
        }

        $selectsubplugins = new url_select($subpluginsarray);
        $selectsubplugins->set_label(get_string('addsetting', 'hybridteaching'));

        return $selectsubplugins;
    }
}
