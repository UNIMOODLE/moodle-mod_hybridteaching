<?php

namespace mod_hybridteaching\output;

use plugin_renderer_base;

defined('MOODLE_INTERNAL') || die();

class renderer extends plugin_renderer_base {

    public function zone_errors($message){
        return $this->render_from_template('mod_hybridteaching/view_page_zone_errors',$message);
    }
    public function zone_access($resultaccess){
        return $this->render_from_template('mod_hybridteaching/view_page_zone_access', $resultaccess);
    }
    public function zone_records(){
        return $this->render_from_template('mod_hybridteaching/view_page_zone_records',null);
    }
}