<?php

namespace mod_hybridteaching\event;

class attendance_updated extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'hybridteaching_attendance';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     * 
     */
    public function get_description() {
        return 'User with id ' . $this->userid . ' updated attendance with id ' .
            $this->other['attid'];
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventattupdated', 'mod_hybridteaching');
    }

    /**
     * Replace add_to_log() statement.
     *
     * @return array of parameters to be passed to legacy add_to_log() function.
     */
    protected function get_legacy_logdata() {
        return false;
    }

    /**
     * Get objectid mapping
     *
     * @return array of parameters for object mapping.
     */
    public static function get_objectid_mapping() {
        return false;
    }
}