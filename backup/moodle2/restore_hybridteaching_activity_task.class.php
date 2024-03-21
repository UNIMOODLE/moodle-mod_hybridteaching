<?php
// This file is part of Moodle - http://moodle.org/
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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/hybridteaching/backup/moodle2/restore_hybridteaching_stepslib.php'); // Because it exists (must).

/**
 * Class restore_hybridteaching_activity_task
 *
 */
class restore_hybridteaching_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have.
     */
    protected function define_my_steps() {
        // Hybridteaching only has one structure step.
        $this->add_step(new restore_hybridteaching_activity_structure_step('hybridteaching_structure', 'hybridteaching.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('hybridteaching', ['intro', 'participants'], 'hybridteaching');
        $contents[] = new restore_decode_content('hybridteaching_attendance', ['hybridteachingid'], 'hybridteaching_attendance');
        $contents[] = new restore_decode_content('hybridteaching_session', ['hybridteachingid'], 'hybridteaching_session');
        $contents[] = new restore_decode_content('hybridteaching_attend_log', ['attendanceid'], 'hybridteaching_attend_log');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = [];

        $rules[] = new restore_decode_rule('HYBRIDTEACHINGINDEX', '/mod/hybridteaching/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('HYBRIDTEACHINGVIEWBYID', '/mod/hybridteaching/view.php?id=$1', 'course_module');

        return $rules;

    }


    /**
     * Defines and returns an array of restore log rules.
     *
     * @return array the array of restore log rules
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('hybridteaching', 'add', 'view.php?id={course_module}', '{hybridteaching}');
        $rules[] = new restore_log_rule('hybridteaching', 'update', 'view.php?id={course_module}', '{hybridteaching}');
        $rules[] = new restore_log_rule('hybridteaching', 'view', 'view.php?id={course_module}', '{hybridteaching}');
        $rules[] = new restore_log_rule('hybridteaching', 'choose', 'view.php?id={course_module}', '{hybridteaching}');
        $rules[] = new restore_log_rule('hybridteaching', 'choose again', 'view.php?id={course_module}', '{hybridteaching}');
        $rules[] = new restore_log_rule('hybridteaching', 'report', 'report.php?id={course_module}', '{hybridteaching}');

        return $rules;
    }


    /**
     * Define restore log rules for course.
     *
     * @return array
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        // Fix old wrong uses (missing extension).
        $rules[] = new restore_log_rule('hybridteaching', 'view all', 'index?id={course}', null,
                                        null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('hybridteaching', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
