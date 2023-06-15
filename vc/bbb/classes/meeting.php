<?php

use mod_bigbluebuttonbn\local\proxy\bigbluebutton_proxy;

class meeting {

    protected $instance;    

    /**
     * Constructor for the meeting object.
     *
     * @param instance $instance
    */
    public function __construct(instance $instance) {
        $this->instance = $instance;
    }

    /**
     * Send an end meeting message to BBB server
    */
    public function end_meeting($meeting_id, $moderator_password) {
        //bigbluebutton_proxy::end_meeting($this->instance->get_meeting_id(), $this->instance->get_moderator_password());
        bigbluebutton_proxy::end_meeting($meeting_id, $moderator_password);
    }

    public function get_meeting_id(?int $groupid = null): string {
        $baseid = sprintf(
            '%s-%s-%s',
            $this->get_instance_var('meetingid'),
            $this->get_course_id(),
            $this->get_instance_var('id')
        );

        if ($groupid === null) {
            $groupid = $this->get_group_id();
        }

        return sprintf('%s[%s]', $baseid, $groupid);
    }

}
