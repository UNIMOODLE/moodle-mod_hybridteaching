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

class helper {

    /**
     * Generate the hours format from the given seconds.
     *
     * @param int $secs The number of seconds.
     * @return string The formatted time in hours and minutes.
     */
    public static function get_hours_format($secs) {
        $hours = floor($secs / HOURSECS);
        $minutes = floor(($secs - ($hours * HOURSECS)) / MINSECS);
        $formattime = '';

        if ($hours > 0) {
            $formattime .= $hours . ' h ';
        }

        if ($minutes > 0) {
            $formattime .= $minutes . ' min';
        }

        if (!empty($secs) && empty($formattime)) {
            $formattime = get_string('lessamin', 'hybridteaching');
        }

        return $formattime;
    }

    /**
     * Checks if a subplugin config exists.
     *
     * @param int $configid The ID of the subplugin config.
     * @param string $type The type of subplugin (vc or storage).
     * @return mixed Returns the config if found, 0 if no subplugin, -1 if no config.
     */
    public static function subplugin_config_exists($configid, $type = 'vc') {
        global $DB;
        $config = $DB->get_record('hybridteaching_configs', ['id' => $configid, 'visible' => 1]);

        if ($config) {
            $pluginmanager = core_plugin_manager::instance();
            $subplugins = $pluginmanager->get_subplugins_of_plugin('mod_hybridteaching');
            $find = false;

            foreach ($subplugins as $subplugin) {
                if ($type == 'vc') {
                    if ($subplugin->type == 'hybridteachvc' && $subplugin->name == $config->type) {
                        $find = true;
                        break;
                    }
                } else if ($type == 'store') {
                    if ($subplugin->type == 'hybridteachstore' && $subplugin->name == $config->type) {
                        $find = true;
                        break;
                    }
                }
            }

            if ($find) {
                return $config;  // Correct.
            } else {
                return 0;  // No subplugin.
            }
        } else {
            return -1;  // No config.
        }
    }
}
