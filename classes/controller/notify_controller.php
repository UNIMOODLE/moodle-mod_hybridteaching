<?php

class notify_controller {

    /**
     * Show (print) the pending messages and clear them
     */
    public static function show() {
        global $SESSION, $OUTPUT;

        if (isset($SESSION->mod_attendance_notifyqueue)) {
            foreach ($SESSION->mod_attendance_notifyqueue as $message) {
                echo $OUTPUT->notification($message->message, 'notify'.$message->type);
            }
            unset($SESSION->mod_attendance_notifyqueue);
        }
    }

    /**
     * Queue a text as a problem message to be shown latter by show() method
     *
     * @param string $message a text with a message
     */
    public static function notify_problem($message) {
        self::queue_message($message, \core\output\notification::NOTIFY_ERROR);
    }

    /**
     * Queue a text as a simple message to be shown latter by show() method
     *
     * @param string $message a text with a message
     */
    public static function notify_message($message) {
        self::queue_message($message, \core\output\notification::NOTIFY_INFO);
    }

    /**
     * queue a text as a suceess message to be shown latter by show() method
     *
     * @param string $message a text with a message
     */
    public static function notify_success($message) {
        self::queue_message($message, \core\output\notification::NOTIFY_SUCCESS);
    }

    /**
     * queue a text as a message of some type to be shown latter by show() method
     *
     * @param string $message a text with a message
     * @param string $messagetype one of the \core\output\notification messages ('message', 'suceess' or 'problem')
     */
    private static function queue_message($message, $messagetype=\core\output\notification::NOTIFY_INFO) {
        global $SESSION;

        if (!isset($SESSION->mod_attendance_notifyqueue)) {
            $SESSION->mod_attendance_notifyqueue = array();
        }
        $m = new stdclass();
        $m->type = $messagetype;
        $m->message = $message;
        $SESSION->mod_attendance_notifyqueue[] = $m;
    }
}
