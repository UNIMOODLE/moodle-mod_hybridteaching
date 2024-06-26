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
use mod_hybridteaching\controller\common_controller;
use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helpers/calendar_helpers.php');
require_once($CFG->dirroot . '/config.php');

use mod_hybridteaching\helpers\password;
use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\controller\attendance_controller;

/**
 * Testing print session info
 *
 * @group hybridteaching
 */
class hybridteaching_print_session_info_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    /**
     * @var \stdClass
     */
    private static $course;
    /**
     * @var \stdClass
     */
    private static $context;
    /**
     * @var \stdClass
     */
    private static $coursecontext;
    /**
     * @var \stdClass
     */
    private static $user;
    /**
     * @var \stdClass
     */
    private static $config;
    /**
     * @var int
     */
    private static $userecordvc;
    /**
     * @var int
     */
    private static $action;
    /**
     * @var \stdClass
     */
    private static $group;
    /**
     * @var int
     */
    private static $usevideoconference;
    /**
     * Course start
     */
    public const COURSE_START = 1704099600;
    /**
     * Course end
     */
    public const COURSE_END = 1706605200;


    public function setUp(): void {
        global $USER;
        parent::setUp();
        $this->resetAfterTest(true);
        self::setAdminUser();
        self::$course = self::getDataGenerator()->create_course(
            ['name' => 'course1', 'startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );
        self::$group = self::getDataGenerator()->create_group(['courseid' => self::$course->id, 'name' => 'group1']);
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        self::$userecordvc = 0;
        self::$config = 0;
        self::$action = 1;
        self::$usevideoconference = 1;
    }

    /**
     * Print session info
     *
     * Return string of session info
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @param int $action
     * @param int $gradesattsession
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_print_session_info($param, $action, $gradesattsession, $grade = 0, $groupid = 0) {
        // Reset after execute the test.
        global $DB, $USER;
        // Module instance.
        $moduleinstance = new \stdClass();
        $moduleinstance->course = self::$course->id;
        // Create hybobj.
        $generator = self::getDataGenerator()->get_plugin_generator('mod_hybridteaching');
        $hybridobject = $generator->create_instance(['course' => self::$course->id,
            'name' => 'hybt',
            'timetype' => null,
            'config' => self::$config,
            'groupid' => self::$group->id,
            'userecordvc' => self::$userecordvc,
            'grade' => $grade,
            'maxgradeattendanceunit' => $gradesattsession,
            ]);

        $cm = get_coursemodule_from_instance('hybridteaching', $hybridobject->id, self::$course->id);

        $sessioncontroller = new sessions_controller($hybridobject);
        // Simulate data form.
        $datadecoded = json_decode($param);
        $data = new \StdClass();
        $data->hybridteachingid = $hybridobject->id;
        $data->name = $datadecoded->name;
        $data->context = $datadecoded->context;
        $data->starttime = time();
        $data->groupid = self::$group->id;
        $data->durationgroup['duration'] = $datadecoded->durationgroup->duration;
        $data->durationgroup['timetype'] = $sessioncontroller::MINUTE_TIMETYPE;
        $data->duration = $sessioncontroller::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);

        // Create session.
        $session = $sessioncontroller->create_session($data);
        $this->assertNotNull($session);
        $sessionexpected = $sessioncontroller->get_session($session->id);
        $this->assertNotNull($sessionexpected);
        $attendancecontroller = new attendance_controller();
        // Get session info string.
        $this->assertNotNull($attendancecontroller->hybridteaching_print_session_info(''));
        $this->assertNotNull($attendancecontroller->hybridteaching_print_session_info($sessionexpected));
        $sessioninfo = $attendancecontroller->hybridteaching_print_session_info($sessionexpected);
        $this->assertGreaterThan(0, strlen($sessioninfo));
        // Change to attendance.
        $allhyboj = $DB->get_record('hybridteaching', ['id' => $hybridobject->id]);
        $this->assertNotNull($attendancecontroller->hybridteaching_print_attendance_for_user($hybridobject->id, self::$user));
        // No string noattendance in lang/en.
        $dataexport = new stdClass();
        $dataexport->context = self::$coursecontext;
        $dataexport->hybridteaching = $hybridobject;
        // Or other.
        $dataexport->includeallsessions = 1;
        // Or other.
        $dataexport->group = 0;
        $dataexport->coursename = 'coursename';
        $exporter = new \mod_hybridteaching\helpers\export($dataexport);
    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":1}}'
             , 1, '1', null, null],

            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
            "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":2}}'
             , 1, '2', 100, self::$group->id],

            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45031,"timetype":1}}'
             , 1, '3', 100],

            ['{"hybridteachingid":2,"name":"Test de prueba","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}'
             , 0, '1', null],

             ['{"hybridteachingid":2,"name":"Test de prueba","context":50,"starttime":1642736531,
                "durationgroup":{"duration":0,"timetype":1}}'
                , 0, '1', 100, self::$group->id],
        ];
    }


}
