<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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

namespace hybridteachvc_bbb;

use mod_bigbluebuttonbn\local\proxy\proxy_base;

defined('MOODLE_INTERNAL') || die();

class bbbproxy extends proxy_base {
    /**
     * Minimum poll interval for remote bigbluebutton server in seconds.
     */
    const MIN_POLL_INTERVAL = 2;

    /**
     * Default poll interval for remote bigbluebutton server in seconds.
     */
    const DEFAULT_POLL_INTERVAL = 5;

    /**
     * Ensure that the remote server was contactable.
     *
     * @param instance $instance
     */
    public static function require_working_server(instance $vc): void {
        $version = null;
        try {
            $version = self::get_server_version();
        } catch (server_not_available_exception $e) {
            self::handle_server_not_available($vc);
        }

        if (empty($version)) {
            self::handle_server_not_available($vc);
        }
    }

    /**
     * Perform api request on BBB.
     *
     * @return null|string
     */
    public static function get_server_version(): ?string {
        $xml = proxy_base::fetch_endpoint_xml('');
        if (!$xml || $xml->returncode != 'SUCCESS') {
            return null;
        }
    
        if (!isset($xml->version)) {
            return null;
        }
    
        $serverversion = (string) $xml->version;
        return (double) $serverversion;
    }

    /**
     * Handle the server not being available.
     *
     * @param instance $instance
     */
    public static function handle_server_not_available($instance): void {
        \core\notification::add(
            self::get_server_not_available_message($instance),
            \core\notification::ERROR
        );
        //redirect(self::get_server_not_available_url($instance));
    }

    /**
     * Get message when server not available
     *
     * @param instance $instance
     * @return string
     */
    public static function get_server_not_available_message($instance): string {
        global $USER;

//AÑADIR AQUI EL MENSAJE DEPENDIENDO DEL ROL DENTRO DE LA INSTANCIA
// UN MENSAJE DISTINTO SI ES ADMIN, SI ES MODERADOR O SI ES ESTUDIANTE

        if (is_siteadmin($USER->id)) {
            return get_string('view_error_unable_join', 'mod_bigbluebuttonbn');
        /*} else if ($USER->is_moderator()) {
            return get_string('view_error_unable_join_teacher', 'mod_bigbluebuttonbn');
        */            
        } else {
            return get_string('view_error_unable_join_student', 'mod_bigbluebuttonbn');
        }
    }

    /**
     * Get URL to the page displaying that the server is not available
     *
     * @param instance $instance
     * @return string
     */
    public static function get_server_not_available_url($instance): string {

//AÑADIR AQUI EL MENSAJE DEPENDIENDO DEL ROL DENTRO DE LA INSTANCIA
// UN MENSAJE DISTINTO SI ES ADMIN, SI ES MODERADOR O SI ES ESTUDIANTE

        global $USER;
        if (is_siteadmin($USER->id)) {
            return new moodle_url('/admin/settings.php', ['section' => 'modsettingbigbluebuttonbn']);
        /*} else if ($instance->is_moderator()) {
            return new moodle_url('/course/view.php', ['id' => $instance->get_course_id()]);
        */
        } else {
            return new moodle_url('/course/view.php', ['id' => $instance->get_course_id()]);
        }
    }

}





