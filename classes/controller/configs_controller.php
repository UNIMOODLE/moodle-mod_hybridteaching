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

class configs_controller extends common_controller {
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
     * Loads an config of the class by its ID.
     *
     * @param int $configid The ID of the config to load.
     * @throws Exception If the config cannot be loaded.
     * @return mixed The loaded config data
     *
     */
    public function hybridteaching_load_config($configid) {
        global $DB;
        $configdata = $DB->get_record('hybridteaching_configs', ['id' => $configid]);
        return $configdata;
    }

    /**
     * Create a new config of a hybridteaching plugin.
     *
     * @param mixed $data an object containing the data for the new config
     * @throws Exception if there is an error creating the config
     * @return string a string containing an error message if there is an error, otherwise empty
     */
    public function hybridteaching_create_config($data) {
        global $DB, $USER;
        $plugin = new stdClass();
        require_once('../../'.$this->splugindir.'/'.$this->hybridobject->type.'/version.php');
        $errormsg = '';
        $config = new stdClass();
        $config->configname = $data->configname;
        $config->type = $this->hybridobject->type;
        $config->subplugintype = $this->splugindir;
        $config->version = $plugin->version;
        $config->visible = 1;
        $config->timecreated = time();
        $config->createdby = $USER->id;
        $config->subpluginconfigid = $data->id;
        if (!$DB->insert_record('hybridteaching_configs', $config)) {
            $errormsg = 'errorcreateconfig';
        }
        return $errormsg;
    }

    /**
     * Updates the config of the hybridteaching plugin with new data.
     *
     * @param object $data the data to update the config with
     * @throws Exception if there is an error updating the config
     * @return string an error message if the config failed to update, otherwise null
     */
    public function hybridteaching_update_config($data) {
        global $DB, $USER;
        $errormsg = '';
        $config = new stdClass();
        $config->id = $data->id;
        $config->configname = $data->configname;
        $config->timemodified = time();
        $config->modifiedby = $USER->id;
        if (!$DB->update_record('hybridteaching_configs', $config)) {
            $errormsg = 'errorupdateconfig';
        }
        return $errormsg;
    }

    /**
     * Deletes an config of the hybridteaching module.
     *
     * @param int $configid ID of the config to be deleted.
     * @throws Exception if the config cannot be deleted.
     * @return string Error message, if any.
     */
    public function hybridteaching_delete_config($configid) {
        global $DB;
        $errormsg = '';
        $configid = ['id' => $configid];
        $subpluginconfigid = $DB->get_field('hybridteaching_configs', 'subpluginconfigid', $configid);
        if (!$DB->delete_records('hybridteaching_configs', $configid)) {
            $errormsg = 'errordeleteconfig';
        } else {
            require_once('../../'.$this->splugindir.'/'.$this->hybridobject->type.'/classes/configs.php');
            configs::delete_config($subpluginconfigid);
        }
        return $errormsg;
    }

    /**
     * Retrieves all configs of mod_hybridteaching from the database of existing subplugins.
     *
     * @param mixed|null $params null or an array of options to pass to the function
     * @throws Exception if an error occurs while retrieving configs
     * @return array|null The configs retrieved from the database in an array format
     */
    public function hybridteaching_get_configs($params = null) {
        global $DB;

        $pluginmanager = core_plugin_manager::instance();
        $subplugins = $pluginmanager->get_subplugins_of_plugin('mod_hybridteaching');
        $subtypes = [];
        foreach ($subplugins as $sub) {
            if ($sub->type == $this->splugintype) {
                $subtypes[] = $sub->name;
            }
        }

        //comprobar aquí, además, que la configuracion pertenece a la categoría del curso: 
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
                  FROM {hybridteaching_configs} hi
                 WHERE hi.type $insql $conditions
              ORDER BY visible DESC, sortorder, id";
        $configs = $DB->get_records_sql($sql, $inparams);
        $configsarray = json_decode(json_encode($configs), true);

        return $configsarray;
    }

    /**
     * Returns an array of configs with their names and types in a format suitable for select lists.
     *
     * @return array the config select list
     */
    public function hybridteaching_get_configs_select() {
        $configs = $this->hybridteaching_get_configs(['visible' => 1]);
        $configselect = [];
        foreach ($configs as $config) {
            $configselect[$config['id']."-".$config['type']] = $config['configname']." (".$config['type'].")";
        }
        return $configselect;
    }

    public static function get_subplugin_dir($type) {
        return substr($type, strlen("hybridteach"));
    }
}
