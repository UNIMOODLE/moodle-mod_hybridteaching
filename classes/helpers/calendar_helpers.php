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

namespace mod_hybridteaching\helpers;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/calendar/lib.php');

class calendar_helpers {


    /**
     * Creates a calendar event for a hybrid teaching session.
     *
     * @param object &$session A reference to the hybrid teaching session object.
     * @throws None
     * @return mixed Returns the ID of the newly created calendar event if successful,
     * false otherwise.
     */
    public static function hybridteaching_create_calendar_event(&$session) {
        global $DB;

        // We don't want to create multiple calendar events for 1 session.
        if ($session->caleventid) {
            return $session->caleventid;
        }

        $hybridteaching = $DB->get_record('hybridteaching', ['id' => $session->hybridteachingid]);
        if (!$hybridteaching) {
            $hybridteaching = $DB->get_record('hybridteaching', ['id' => $DB->get_field('course_modules', 'instance',
                ['id' => $session->coursemodule], MUST_EXIST), ]);
        }
        $caleventdata = new stdClass();
        $caleventdata->name           = $session->name;
        $caleventdata->courseid       = $hybridteaching->course;
        $caleventdata->groupid        = !isset($session->groupid) ? 0 : $session->groupid;
        $caleventdata->instance       = $session->hybridteachingid;
        $caleventdata->timestart      = $session->starttime;
        $caleventdata->timeduration   = $session->duration;
        $caleventdata->description    = $session->description;
        $caleventdata->format         = $session->descriptionformat;
        $caleventdata->eventtype      = 'hybridteaching';
        $caleventdata->timemodified   = time();
        $caleventdata->modulename     = 'hybridteaching';

        if (!empty($session->groupid)) {
            $caleventdata->name .= " (". get_string('group', 'group') ." ". groups_get_group_name($session->groupid) .")";
        }

        $calevent = new stdClass();
        if ($calevent = \calendar_event::create($caleventdata, false)) {
            $session->caleventid = $calevent->id;
            $DB->set_field('hybridteaching_session', 'caleventid', $session->caleventid, ['id' => $session->id]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create multiple calendar events based on sessions data.
     *
     * @param array $sessionsids array of sessions ids
     */
    public static function hybridteaching_create_calendar_events($sessionsids) {
        global $DB;

        $sessions = $DB->get_recordset_list('hybridteaching_session', 'id', $sessionsids);

        foreach ($sessions as $session) {
            self::hybridteaching_create_calendar_event($session);
            if ($session->caleventid) {
                $DB->update_record('hybridteaching_session', $session);
            }
        }
    }

    /**
     * Update calendar event duration and date
     *
     * @param stdClass $session Session data
     * @return bool result of updating
     */
    public static function hybridteaching_update_calendar_event($session) {
        global $DB;

        $caleventid = $session->caleventid;
        $timeduration = $session->duration;
        $timestart = $session->starttime;

        if ($session->caleventid != 0) {
            $DB->delete_records_list('event', 'id', [$caleventid]);
            $session->caleventid = 0;
            $DB->update_record('hybridteaching_session', $session);
        }

        if ($session->caleventid == 0) {
            return self::hybridteaching_create_calendar_event($session);
        }

        $caleventdata = new stdClass();
        $caleventdata->timeduration   = $timeduration;
        $caleventdata->timestart      = $timestart;
        $caleventdata->timemodified   = time();
        $caleventdata->description    = $session->description;

        $calendarevent = \calendar_event::load($caleventid);
        if ($calendarevent) {
            return $calendarevent->update($caleventdata) ? true : false;
        } else {
            return false;
        }
    }

    /**
     * Delete calendar events for sessions
     *
     * @param array $sessionsids array of sessions ids
     * @return bool result of updating
     */
    public static function hybridteaching_delete_calendar_events($sessionid) {
        global $DB;
        $caleventid = $DB->get_field('hybridteaching_session', 'caleventid', ['id' => $sessionid]);
        if ($caleventid) {
            $DB->delete_records('event', ['id' => $caleventid]);
        }
    }

    /**
     * Check if calendar events are created for given sessions
     *
     * @param array $sessionsids of sessions ids
     * @return array | bool array of existing calendar events or false if none found
     */
    public static function hybridteaching_existing_calendar_events_ids($sessionsids) {
        global $DB;
        $caleventsids = array_keys($DB->get_records_list('hybridteaching_session', 'id', $sessionsids, '', 'caleventid'));
        $existingcaleventsids = array_filter($caleventsids);
        if (! empty($existingcaleventsids)) {
            return $existingcaleventsids;
        } else {
            return false;
        }
    }
}
