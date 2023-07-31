
<?php

class helper{
    
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
            $formattime .=  $minutes . ' min';
        }
        return $formattime;
    }

    /**
     * Checks if a subplugin instance exists.
     *
     * @param int $instanceid The ID of the subplugin instance.
     * @return mixed Returns the instance if found, 0 if no subplugin, -1 if no instance.
     */
    public static function subplugin_instance_exists($instanceid){
        global $DB;
        $instance = $DB->get_record('hybridteaching_instances', ['id' => $instanceid, 'visible' => 1]);
    
        if ($instance) {
            $pluginmanager = core_plugin_manager::instance();
            $subplugins = $pluginmanager->get_subplugins_of_plugin('mod_hybridteaching');
            $find = false;
    
            foreach ($subplugins as $subplugin) {
                if ($subplugin->type == 'hybridteachvc' && $subplugin->name == $instance->type) {
                    $find = true;
                    break;
                }
            }
    
            if ($find) {
                return $instance;  // Correct
            } else {
                return 0;  // No subplugin
            }
        } else {
            return -1;  // No instance
        }
    }

}