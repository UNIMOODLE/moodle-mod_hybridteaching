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

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/instances_controller.php');

/**
 * Admin external page that displays a list of the installed submission plugins.
 *
 * @package   mod_hybridteaching
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hybridteaching_admin_plugins_instances extends admin_setting {
    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('managevideoconferenceplugins', get_string('videoconferenceplugins', 'mod_hybridteaching'), '', '');
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
    public function output_html($data, $query='') {
        global $CFG, $OUTPUT, $DB, $PAGE;

        // Display strings.
        $strorder = get_string('order', 'mod_hybridteaching');
        $stroptions = ucfirst(get_string('options'));
        $strstate = get_string('hideshow', 'mod_hybridteaching');
        $struninstall = get_string('delete');
        $strversion = get_string('version');
        $strdisable = get_string('disable');
        $strenable = get_string('enable');
        $strup = get_string('up');
        $strdown = get_string('down');
        $strname = get_string('name');
        $strtype = get_string('type', 'mod_hybridteaching');
        $message = optional_param('message', 0, PARAM_COMPONENT);

        $return = $OUTPUT->box_start('generalbox hybridteachingpluginsinstances');

        $table = new html_table();
        $table->head = array($strname, $strtype, $strversion, $strorder, $stroptions);
        $table->colclasses = array('leftalign', 'leftalign', 'centeralign',
            'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'hybridteachingpluginsinstances';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data = array();

        $updowncount = 1;
        $printed = array();
        $spacer = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'iconsmall'));

        $url = new moodle_url('/mod/hybridteaching/classes/action/instance_action.php', array('sesskey' => sesskey()));
        $instancecontroller = new instances_controller(null, 'hybridteaching_instances');
        $instancelist = $instancecontroller::hybridteaching_get_instances();
        $enabledinstances = $instancecontroller->get_enabled_data('hybridteaching_instances');
        if (!empty($message)) {
            \core\notification::add(get_string($message, 'mod_hybridteaching'), \core\output\notification::NOTIFY_INFO);
        }
        foreach ($instancelist as $instance) {
            // Hide/show links.
            $class = '';
            $enabled = $instance['visible'];
            $instanceid = $instance['id'];
            $instancename = $instance['instancename'];
            $instanceversion = $instance['version'];
            $instancetype = $instance['type'];
            $instancetypealias = get_string($instance['type'].'alias', 'hybridteachingvc_'.$instance['type']);

            // Up/down link (only if enrol is enabled).
            $updown = '';
            if ($enabled) {
                if ($updowncount > 1) {
                    $updown = html_writer::link(new moodle_url($url, array('action' => 'up', 'id' => $instanceid)),
                        $OUTPUT->pix_icon('t/up', $strup, 'moodle', array('class' => 'iconsmall')));
                } else {
                    $updown = $spacer;
                }
                if ($updowncount < $enabledinstances) {
                    $updown .= html_writer::link(new moodle_url($url, array('action' => 'down', 'id' => $instanceid)),
                        $OUTPUT->pix_icon('t/down', $strdown, 'moodle', array('class' => 'iconsmall')));
                } else {
                    $updown .= $spacer;
                }
                ++$updowncount;
            }

            $options = '';
            if ($enabled) {
                $options .= html_writer::link(new moodle_url($url, array('action' => 'disable', 'id' => $instanceid)),
                    $OUTPUT->pix_icon('t/hide', $strdisable, 'moodle', array('class' => 'iconsmall')));
            } else {
                $options .= html_writer::link(new moodle_url($url, array('action' => 'enable', 'id' => $instanceid)),
                    $OUTPUT->pix_icon('t/show', $strenable, 'moodle', array('class' => 'iconsmall')));
                $class = 'dimmed_text';
            }

            $urledit = '/mod/hybridteaching/vc/'.$instancetype.'/editinstance.php?type='.$instancetype.'&id='.$instanceid;
            $options .= html_writer::link(new moodle_url($urledit),
                $OUTPUT->pix_icon('t/edit', $stroptions, 'moodle', array('class' => 'iconsmall')));

            $options .= html_writer::link(new moodle_url($url, array('action' => 'delete', 'id' => $instanceid)),
                $OUTPUT->pix_icon('t/delete', $struninstall, 'moodle', array('class' => 'iconsmall')),
                array('onclick' => 'if (!confirm("'.get_string('deleteconfirm', 'mod_hybridteaching', $instancename).'"))
                { return false; }')
            );

            // Add a row to the table.
            $row = new html_table_row(array($instancename, $instancetypealias, $instanceversion, $updown, $options));
            if (!empty($class)) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }

        $return .= html_writer::table($table);

        $pluginmanager = core_plugin_manager::instance();
        $subplugins = $pluginmanager->get_subplugins_of_plugin('mod_hybridteaching');
        $subpluginsarray = array();
        foreach ($subplugins as $subplugin) {
            $url = new moodle_url('/mod/hybridteaching/vc/'.$subplugin->name.'/editinstance.php?type='.$subplugin->name);
            $link = $url->out();
            $subplugincommonname = get_string($subplugin->name.'alias', 'hybridteachingvc_'.$subplugin->name);
            $subpluginsarray[$link] = $subplugincommonname;
        }
        $selectsubplugins = new url_select($subpluginsarray);
        $selectsubplugins->set_label(get_string('addinstance', 'hybridteaching'));
        echo $OUTPUT->render($selectsubplugins);

        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}

