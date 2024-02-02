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


namespace mod_hybridteaching\controller;

use stdClass;

class configs_controller extends \mod_hybridteaching\controller\common_controller {
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
        if (!empty($data->categories)) {
            $categories = explode(",", $data->categories);
            $config->categories = json_encode($categories);
        } else {
            $config->categories = 0;
        }
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
        if (!empty($data->categories)) {
            $categories = explode(",", $data->categories);
            $config->categories = json_encode($categories);
        } else {
            $config->categories = 0;
        }
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
        $subpluginconfigid = $DB->get_field('hybridteaching_configs', 'subpluginconfigid', ['id' => $configid]);
        if (!$DB->delete_records('hybridteaching_configs', ['id' => $configid])) {
            $errormsg = 'errordeleteconfig';
        } else {
            call_user_func('hybridteach' . $this->splugindir . '_' .
                $this->hybridobject->type . "\\configs::delete_config", $subpluginconfigid);
            $htmodules = $DB->get_records('hybridteaching', ['config' => $configid]);
            foreach ($htmodules as $htmodule) {
                $htmodule->config = 0;
                $htmodule->typevc = '';
                $DB->update_record('hybridteaching', $htmodule);
            }
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

        $pluginmanager = \core_plugin_manager::instance();
        $subplugins = $pluginmanager->get_subplugins_of_plugin('mod_hybridteaching');
        $subtypes = [];
        $subenabled = [];
        foreach ($subplugins as $sub) {
            if ($sub->type == $this->splugintype) {
                $subenabled[$sub->name] = $sub->is_enabled();
                $subtypes[] = $sub->name;
            }
        }

        $conditions = '';
        if (isset($params['visible'])) {
            $conditions .= ' AND hi.visible = ?';
        }

        $inparams = [];
        $insql = '';
        if (!empty($subtypes)) {
            [$insql, $inparams] = $DB->get_in_or_equal($subtypes);
        }

        if (!empty($params)) {
            $inparams = array_merge($inparams, $params);
        }

        $categoriescond = $this->get_categories_conditions($params, $subenabled);
        $incategories = '';
        $categoriesparams = [];
        if (!empty($categoriescond)) {
            $incategories = $categoriescond['conditions'];
            $categoriesparams = $categoriescond['inparams'];

            if (!empty($categoriesparams)) {
                unset($inparams['category']);
                $inparams = array_merge($inparams, $categoriesparams);
            }
        }

        $sql = "SELECT hi.*
                  FROM {hybridteaching_configs} hi
                 WHERE hi.type $insql $conditions $incategories
              ORDER BY hi.visible DESC, hi.sortorder, hi.id";
        $configs = $DB->get_records_sql($sql, $inparams);
        $configsarray = json_decode(json_encode($configs), true);

        // Insert if subplugin is enabled or disabled.
        foreach ($configsarray as $key => $element) {
            if (isset($subenabled[$element['type']]) && $subenabled[$element['type']] == 1) {
                $configsarray[$key]['configenabled'] = true;
                $element['configenabled'] = true;
            } else {
                $configsarray[$key]['configenabled'] = false;
                $element['configenabled'] = false;
            }
        }

        /*
        // Revisar la ordenación por habilitado/deshabilitado.
        // Al activar esta ordenación no funcionan las opciones de cambiar de orden, y visible/ocult.

        $sortarray = [];

        foreach ($configsarray as $element) {
            foreach ($element as $key => $value) {
                if (!isset($sortarray[$key])) {
                    $sortarray[$key] = [];
                }
                $sortarray[$key][] = $value;
            }
        }
        $orderby = 'configenabled';
        array_multisort($sortarray[$orderby], SORT_DESC, $configsarray);
        */

        return $configsarray;
    }

    /**
     * Returns an array of configs with their names and types in a format suitable for select lists.
     *
     * @return array the config select list
     */
    public function hybridteaching_get_configs_select($coursecategory) {
        $configs = $this->hybridteaching_get_configs(['visible' => 1, 'category' => $coursecategory]);
        $configselect = [];
        foreach ($configs as $config) {
            $configselect[$config['id']."-".$config['type']] = $config['configname']." (".$config['type'].")";
        }
        return $configselect;
    }

    /**
     * Retrieves the subplugin directory based on the given type.
     *
     * @param string $type The type of the subplugin.
     * @return string The subplugin directory.
     */
    public static function get_subplugin_dir($type) {
        return substr($type, strlen("hybridteach"));
    }

    /**
     * Retrieves the conditions and inparams for filtering categories.
     *
     * @param array $params An array of parameters for filtering categories.
     *                      Supported keys:
     *                      - category: The category to filter by.
     * @return array Returns an array containing the conditions and inparams for filtering categories.
     *               The array has the following keys:
     *               - conditions: The SQL conditions for filtering categories.
     *               - inparams: An array of values to be used as parameters in the SQL conditions.
     */
    public static function get_categories_conditions($params, $subenabled = null) {
        global $DB;
        $conditions = '';
        $configsavailable = [];
        $configscategory = [];
        $inparams = [];

        if (isset($params['category'])) {
            $configs = $DB->get_records('hybridteaching_configs', ['visible' => 1]);

            if (!empty($subenabled)) {
                foreach ($configs as $key => $element) {
                    if (isset($subenabled[$element->type]) && $subenabled[$element->type] == 1) {
                        $configsavailable[] = ['id' => $element->id, 'categories' => $element->categories];
                    }
                }

                foreach ($configsavailable as $config) {
                    if ($config['categories'] == 0 || in_array($params['category'], json_decode($config['categories']))) {
                        $configscategory[] = $config['id'];
                    }
                }
            } else {
                foreach ($configs as $config) {
                    if ($config->categories == 0 || in_array($params['category'], json_decode($config->categories))) {
                        $configscategory[] = $config->id;
                    }
                }
            }

            if (!empty($configscategory)) {
                [$insql, $inparams] = $DB->get_in_or_equal($configscategory);
                $conditions = " AND hi.id " . $insql;
            }
        }

        return ['conditions' => $conditions, 'inparams' => $inparams];
    }
}
