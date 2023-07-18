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
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('common_controller.php');

class instances_controller extends common_controller {
    protected $splugindir;
    protected $splugintype;

    public function __construct(stdClass $hybridobject = null, $splugintype) {
        parent::__construct($hybridobject);
        $this->splugintype = $splugintype;
        if (!empty($this->hybridobject->subplugintype)) {
            $this->splugindir = $this->hybridobject->subplugintype;
        } else {
            $this->splugindir = $this->get_subplugin_dir($splugintype);
        }
    }

    /**
     * Loads an instance of the class by its ID.
     *
     * @param int $instanceid The ID of the instance to load.
     * @throws Exception If the instance cannot be loaded.
     * @return mixed The loaded instance data
     *
     */
    public function hybridteaching_load_instance($instanceid) {
        global $DB;
        $instancedata = $DB->get_record('hybridteaching_instances', ['id' => $instanceid]);
        return $instancedata;
    }

    /**
     * Create a new instance of a hybridteaching plugin.
     *
     * @param mixed $data an object containing the data for the new instance
     * @throws Exception if there is an error creating the instance
     * @return string a string containing an error message if there is an error, otherwise empty
     */
    public function hybridteaching_create_instance($data) {
        global $DB, $USER;
        $plugin = new stdClass();
        require_once('../../'.$this->splugindir.'/'.$this->hybridobject->type.'/version.php');
        $errormsg = '';
        $instance = new stdClass();
        $instance->instancename = $data->instancename;
        $instance->type = $this->hybridobject->type;
        $instance->subplugintype = $this->splugindir;
        $instance->version = $plugin->version;
        $instance->visible = 1;
        $instance->timecreated = time();
        $instance->createdby = $USER->id;
        $instance->subplugininstanceid = $data->id;
        if (!$DB->insert_record('hybridteaching_instances', $instance)) {
            $errormsg = 'errorcreateinstance';
        }
        return $errormsg;
    }

    /**
     * Updates the instance of the hybridteaching plugin with new data.
     *
     * @param object $data the data to update the instance with
     * @throws Exception if there is an error updating the instance
     * @return string an error message if the instance failed to update, otherwise null
     */
    public function hybridteaching_update_instance($data) {
        global $DB, $USER;
        $errormsg = '';
        $instance = new stdClass();
        $instance->id = $data->id;
        $instance->instancename = $data->instancename;
        $instance->timemodified = time();
        $instance->modifiedby = $USER->id;
        if (!$DB->update_record('hybridteaching_instances', $instance)) {
            $errormsg = 'errorupdateinstance';
        }
        return $errormsg;
    }

    /**
     * Deletes an instance of the hybridteaching module.
     *
     * @param int $instanceid ID of the instance to be deleted.
     * @throws Exception if the instance cannot be deleted.
     * @return string Error message, if any.
     */
    public function hybridteaching_delete_instance($instanceid) {
        global $DB;
        $errormsg = '';
        $instanceid = ['id' => $instanceid];
        $subplugininstanceid = $DB->get_field('hybridteaching_instances', 'subplugininstanceid', $instanceid);
        if (!$DB->delete_records('hybridteaching_instances', $instanceid)) {
            $errormsg = 'errordeleteinstance';
        } else {
            require_once('../../'.$this->splugindir.'/'.$this->hybridobject->type.'/classes/instances.php');
            instances::delete_instance($subplugininstanceid);
        }
        return $errormsg;
    }

    /**
     * Retrieves all instances of mod_hybridteaching from the database of existing subplugins.
     *
     * @param mixed|null $params null or an array of options to pass to the function
     * @throws Exception if an error occurs while retrieving instances
     * @return array|null The instances retrieved from the database in an array format
     */
    public function hybridteaching_get_instances($params = null) {
        global $DB;

        $pluginmanager = core_plugin_manager::instance();
        $subplugins = $pluginmanager->get_subplugins_of_plugin('mod_hybridteaching');
        $subtypes = [];
        foreach ($subplugins as $sub) {
            if ($sub->type == $this->splugintype) {
                $subtypes[] = $sub->name;
            }
        }

        //comprobar aquí, además, que la instancia pertenece a la categoría del curso: 
        //parámetro que se recibe del $courseid
        $conditions = '';
        if (isset($params['visible'])) {
            $conditions = ' AND hi.visible = ' . $params['visible'];
        }

        $inparams = [];
        $insql = '';
        if (!empty($subtypes)) {
            [$insql, $inparams] = $DB->get_in_or_equal($subtypes);
        }

        $sql = "SELECT *
                  FROM {hybridteaching_instances} hi
                 WHERE hi.type $insql $conditions
              ORDER BY visible DESC, sortorder, id";
        $instances = $DB->get_records_sql($sql, $inparams);
        $instancesarray = json_decode(json_encode($instances), true);

        return $instancesarray;
    }

    /**
     * Returns an array of instances with their names and types in a format suitable for select lists.
     *
     * @return array the instance select list
     */
    public function hybridteaching_get_instances_select() {
        $instances = $this->hybridteaching_get_instances(['visible' => 1]);
        $instanceselect = [];
        foreach ($instances as $instance) {
            $instanceselect[$instance['id']."-".$instance['type']] = $instance['instancename']." (".$instance['type'].")";
        }
        return $instanceselect;
    }

    public static function get_subplugin_dir($type) {
        return substr($type, strlen("hybridteach"));
    }
}
