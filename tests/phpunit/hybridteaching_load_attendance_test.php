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

use mod_hybridteaching\helpers\password;
use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\controller\attendance_controller;

/**
 * Testing load attendance
 *
 * @group hybridteaching
 */
class hybridteaching_load_attendance_test extends \advanced_testcase {

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
            ['startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        self::$userecordvc = 0;
        self::$config = 0;
        self::$action = 1;
        self::$usevideoconference = 1;
    }

    /**
     * Load an attendance
     *
     * Load an attendance
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @param string $view
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_load_attendance($param, $view) {
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
            'userecordvc' => self::$userecordvc,
            ]);
        $cm = get_coursemodule_from_instance('hybridteaching', $hybridobject->id, self::$course->id);

        $sessioncontroller = new sessions_controller($hybridobject);
        // Simulate data form.
        $datadecoded = json_decode($param);
        $data = new \StdClass();
        $data->hybridteachingid = $datadecoded->hybridteachingid;
        $data->name = $datadecoded->name;
        $data->context = $datadecoded->context;
        $data->starttime = time();
        $data->durationgroup['duration'] = $datadecoded->durationgroup->duration;
        $data->durationgroup['timetype'] = $sessioncontroller::MINUTE_TIMETYPE;
        $data->duration = $sessioncontroller::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);

        // Create session.
        $session = $sessioncontroller->create_session($data);
        $sessionexpected = $sessioncontroller->get_session($session->id);
        $this->assertNotNull($sessionexpected);
        $attendancecontroller = new attendance_controller($hybridobject);
        // Set attendance.
        $attendanceid = $attendancecontroller->hybridteaching_set_attendance($hybridobject, $sessionexpected);
        $this->assertNotNull($attendanceid);
        // Simulate connection time.
        $DB->execute("UPDATE {hybridteaching_attendance}
                         SET connectiontime = 1000
                       WHERE hybridteachingid = :hybridteachingid",
                           ['hybridteachingid' => $hybridobject->id]);
        // Check that attendance was loaded successfuly.
        $this->assertNotNull($attendancecontroller->load_attendance_assistance(['starttime' => 1642736531
        , 'userid' => self::$user->id, 'view' => 'studentattendance'], 'starttime > 0 '));
        $this->assertNotNull($attendancecontroller->load_attendance(0, 0, ['starttime' => 1642736531, 'view' => $view,
        'groupid' => 1]));
        $attendancecontroller->hybridteaching_get_attendance_users_in_session($sessionexpected->id, $hybridobject->id);
        // Catch exception.
        $attendancecontroller->hybridteaching_get_attendance_users_in_session($sessionexpected->id, $hybridobject->name);
        $this->assertIsNumeric($attendancecontroller->count_attendance(self::$user->firstname, self::$user->lastname, 1));
        $attendanceobject = $attendancecontroller->hybridteaching_get_attendance_from_id($attendanceid);
        $listarray = ['userid' => $attendanceobject->userid];
        $listnumeric = $attendanceobject->userid;
        // Load sessions attendant.
        $attendancecontroller->load_sessions_attendant($listarray);
        $attendancecontroller->load_sessions_attendant($attendanceobject);
        $attendancecontroller->load_sessions_attendant($listnumeric);
        // Count attendance of session by parameters.
        $this->assertIsNumeric($attendancecontroller->count_sess_attendance(['sessionid' => $attendanceid, 'status' => 1]));
        // Count user attendance.
        $attendancescount = $DB->get_records('hybridteaching_attendance', ['hybridteachingid' => $hybridobject->id], 'id', 'id');

    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            ['{"hybridteachingid":1,"name":"Test", "description": "description of session","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}'
             , 'extendedstudentatt', ],
            ['{"hybridteachingid":2,"name":"Test 2","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}'
             , 'extendedsessionatt', ],
        ];
    }


}
