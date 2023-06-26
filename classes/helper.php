
<?php

class helper{
    
    public static function get_hours_format($secs) {
        $hours = floor($secs / 3600);
        $minutes = floor(($secs - ($hours * 3600)) / 60);
        $formattime = '';
        
        if ($hours > 0) {
            $formattime .= $hours . ' h ';
        }

        if ($minutes > 0) {
            $formattime .=  $minutes . ' min';
        }
        return $formattime;
    }

    public static function subplugin_instance_exists($instanceid){
        global $DB;
        $instance = $DB->get_record('hybridteaching_instances', ['id' => $instanceid, 'visible' => 1]);
        if ($instance){
            //comprobar con plugin manager que el tipo de vc existe
            $pluginmanager = core_plugin_manager::instance();
            $subplugins = $pluginmanager->get_subplugins_of_plugin('mod_hybridteaching');
            $find=false;
            foreach ($subplugins as $subplugin) {
                if ($subplugin->type=='hybridteachvc'){
                    if ($subplugin->type=='hybridteachvc' && $subplugin->name==$instance->type){
                        $find=true;
                        break;
                    }
                }
            }
            if ($find){
                return 1;  //correct
            } else {
                return 0;  //no subplugin
            }
            
        }
        else 
            return -1;   //no instance

    }

}