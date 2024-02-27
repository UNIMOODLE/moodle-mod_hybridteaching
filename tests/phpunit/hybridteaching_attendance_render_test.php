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
 * The testing class.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 Proyecto UNIMOODLE
 * @author      UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author      ISYC <soporte@isyc.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helpers/calendar_helpers.php');
require_once($CFG->dirroot . '/config.php');

use mod_hybridteaching\output\attendance_render;
use mod_hybridteaching\controller\sessions_controller;

class hybridteaching_attendance_render_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    private static $course;
    private static $context;
    private static $coursecontext;
    private static $user;
    private static $config;
    private static $userecordvc;
    public const COURSE_START = 1704099600;
    public const COURSE_END = 1706605200;


    public function setUp(): void {
        global $USER;
        parent::setUp();
        $this->resetAfterTest(true);
        self::setAdminUser();
        self::$course = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        self::$userecordvc = 0;
        self::$config = 0;
    }

    /**
     * Attendance render
     *
     * Attendance render
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @covers \hybridteaching_attendance_render::attendance_render
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_attendance_render($param, $column, $view, $paramstable = null) {
        // Reset after execute the test.
        global $DB;
        // Module instance.
        $moduleinstance = new \stdClass();
        $moduleinstance->course = self::$course->id;
        // Create hybobj with generator.
        $generator = self::getDataGenerator()->get_plugin_generator('mod_hybridteaching');
        $hybridobject = $generator->create_instance(['course' => self::$course->id,
            'name' => 'hybt',
            'timetype' => null,
            'config' => self::$config,
            'userecordvc' => self::$userecordvc
            ]);
        $cm = get_coursemodule_from_instance('hybridteaching', $hybridobject->id, self::$course->id);

        $sessioncontroller = new sessions_controller($hybridobject);
        // Simulate data form.
        $datadecoded = json_decode($param);
        $data = new \StdClass();
        $data->hybridteachingid = $hybridobject->id;
        $data->name = $datadecoded->name;
        isset($datadecoded->description) ? $data->description = $datadecoded->description : $data->description = null;
        $data->context = $datadecoded->context;
        $data->starttime = time();
        $data->durationgroup['duration'] = $datadecoded->durationgroup->duration;
        $data->durationgroup['timetype'] = $sessioncontroller::HOUR_TIMETYPE;
        $data->duration = $sessioncontroller::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);

        // Create session.
        $session = $sessioncontroller->create_session($data);
        $attendancerender = new attendance_render($hybridobject);
        // Get column name.
        $columnname = $attendancerender->get_column_name($column, $view);
        $this->assertGreaterThan(0, strlen($columnname));
        // Get table options.
        $tableoptions = $attendancerender->get_table_options(['visible' => 1], $paramstable, 'url', $session->id);
        $this->assertNotNull($tableoptions);
        $this->assertNotNull($attendancerender->get_operator());
 
    }
    public static function dataprovider(): array {

        return [

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strtype', 'sessionattendance', ["view" => "sessionattendance"]],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strdate', 'sessionattendance', ["view" => "sessionattendance"]],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strduration', 'sessionattendance', ["view" => "sessionattendance"]],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strname', 'sessionattendance', ["view" => "sessionattendance"]],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strattendance', 'sessionattendance', ["view" => "sessionattendance"]],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strattendance', 'studentattendance', ["view" => "sessionattendance"]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strusername', 'studentattendance', ["view" => "sessionattendance"]],
            
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strpfp', 'studentattendance', ["view" => "sessionattendance"]],
            
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strlastfirstname', 'studentattendance', ["view" => "sessionattendance"]],
            
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strentrytime', 'studentattendance', ["view" => "sessionattendance"]],
            
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strleavetime', 'studentattendance', ['view' => 'sessionattendance', 'userinf' => 1, 'log' => 1]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strpermanence', 'studentattendance', ['view' => 'studentattendance', 'userinf' => 1, 'log' => 1]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strhour', 'studentattendance', ["view" => "sessionattendance"]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strlogaction', 'studentattendance', ["view" => "sessionattendance"]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strcombinedatt', 'studentattendance', ["view" => "sessionattendance"]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strclassroom', 'studentattendance', ["view" => "sessionattendance"]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strvc', 'studentattendance', ["view" => "sessionattendance"]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'randomvalue', 'studentattendance', ["view" => "sessionattendance"]],

        ];
    }


}
