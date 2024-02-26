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

/**
 * Class notify_controller
 */
class notify_controller {
    /**
     * Show (print) the pending messages and clear them
     */
    public static function show() {
        global $SESSION, $OUTPUT;

        if (isset($SESSION->mod_hybridteaching_notifyqueue)) {
            foreach ($SESSION->mod_hybridteaching_notifyqueue as $message) {
                echo $OUTPUT->notification($message->message, 'notify'.$message->type);
            }
            unset($SESSION->mod_hybridteaching_notifyqueue);
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

        if (!isset($SESSION->mod_hybridteaching_notifyqueue)) {
            $SESSION->mod_hybridteaching_notifyqueue = [];
        }
        $m = new \stdclass();
        $m->type = $messagetype;
        $m->message = $message;
        $SESSION->mod_hybridteaching_notifyqueue[] = $m;
    }
}
