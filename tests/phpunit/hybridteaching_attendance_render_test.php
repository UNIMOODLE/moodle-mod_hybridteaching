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

use mod_hybridteaching\local\attendance_table;
use mod_hybridteaching\controller\sessions_controller;

class hybridteaching_attendance_table_test extends \advanced_testcase {

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
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_attendance_table($param, $column, $view, $paramstable = null, $nameform = '') {
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
        $sessionexpected = $sessioncontroller->get_session($session->id);
        $attendancetable = new attendance_table($hybridobject);
        // Get column name.
        $columnname = $attendancetable->get_column_name($column, $view);
        $this->assertGreaterThan(0, strlen($columnname));
        // Get table options.
        $tableoptions = $attendancetable->get_table_options(['visible' => 1], $paramstable, 'url', $session->id);
        // Get bulk options.
        $attendancetable->get_bulk_options_select($view);
        
        $this->assertNotNull($tableoptions);
        $this->assertNotNull($attendancetable->get_operator());
        switch ($nameform){
            case "bulksetexemptform":
                $bulksetexemptform = new \mod_hybridteaching\form\bulk_set_exempt_form(null, ['cm' => $cm, 'attendslist' => [], 'sessionid' => $sessionexpected->id
                , 'hybridteaching' => $hybridobject, 'view' => 'att', 'userid' => self::$user->id]);
                break;
            case "sessionsoptionform":
                // Session option form.
                $sessionsoptionform = new \mod_hybridteaching\form\session_options_form(null, ['id' => $sessionexpected->id, 'l' => []]);
                break;
            case "updatestarttimeform1":
                // Update start time form.
                $updatestarttimeform = new \mod_hybridteaching\form\bulk_update_starttime_form(null, ['cm' => $cm, 'sesslist' => [], 'hybridteaching' => $hybridobject
                , 'slist' => []]);
                break;
            case "updatedurationform":
                // Update duration form.
                $updatedurationform = new \mod_hybridteaching\form\bulk_update_duration_form(null, ['cm' => $cm, 'sesslist' => [], 'hybridteaching' => $hybridobject
                , 'slist' => []]);
                break;
            case "attuserfilteroptionsform":
                $attuserfilteroptionsform = new \mod_hybridteaching\form\attuserfilter_options_form(null, ['id' => $cm->id, 'att' => 1, 'fname' => 'name'
                , 'lname' => 'lastname', 'hid' => $hybridobject->id, 'view' => $view, 'sessid' => $sessionexpected->id, 'sort' => 'id', 'dir' => 'ASC'
                ,'perpage' => 1, 'attfilter' => 1, 'groupid' => 1, ]);
                break;
            default:
                break;

        }
            
    }
    public static function dataprovider(): array {

        return [

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strgroup', 'sessionattendance', ["view" => "sessionattendance"], "updatestarttimeform"],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strtype', 'sessionattendance', ["view" => "sessionattendance"], "updatestarttimeform"],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strtype', 'studentattendance', ["view" => "studentattendance"], "updatestarttimeform"],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strdate', 'sessionattendance', ["view" => "sessionattendance"], "sessionsoptionform"],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strduration', 'sessionattendance', ["view" => "sessionattendance"], "bulksetexemptform"],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strduration', 'extendedsessionatt', ["view" => "extendedsessionatt"], "attuserfilteroptionsform"],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strduration', 'extendedstudentatt', ["view" => "extendedstudentatt"], ""],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strname', 'sessionattendance', ["view" => "sessionattendance"]],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strattendance', 'sessionattendance', ["view" => "sessionattendance"]],
            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strgrade', 'studentattendance', ["view" => "sessionattendance"]],

            ['{"name":"Test de prueba","description": "description of session","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}', 'strusername', 'studentattendance', ["view" => "sessionattendance"]],
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
