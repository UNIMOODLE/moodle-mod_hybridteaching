<?php

namespace mod_hybridteaching\event;

class session_record_viewed extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'hybridteaching_session';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     * 
     */
    public function get_description() {
        return 'User with id ' . $this->userid . ' has seen the record from session with id ' .
            $this->objectid;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventsessionrecordviewed', 'mod_hybridteaching');
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
