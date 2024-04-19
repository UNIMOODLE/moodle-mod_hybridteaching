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
require_once($CFG->dirroot . '/mod/hybridteaching/externallib.php');

require_once($CFG->dirroot . '/config.php');

use mod_hybridteaching\helpers\password;
use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\controller\attendance_controller;
use mod_hybridteaching\helpers\grades;

/**
 * Testing set attendance
 *
 * @group hybridteaching
 */
class hybridteaching_set_attendance_test extends \advanced_testcase {

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
     * Set an attendance
     *
     * Set an attendance
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @param string $typevc
     * @param int $attexempt
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_set_attendance($param, $typevc, $attexempt) {
        // Reset after execute the test.
        global $DB;
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
            'usevideoconference' => self::$usevideoconference,
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
        $data->typevc = $typevc;
        $data->attexempt = $attexempt;
        $data->durationgroup['duration'] = $datadecoded->durationgroup->duration;
        $data->durationgroup['timetype'] = $sessioncontroller::MINUTE_TIMETYPE;
        $data->duration = $sessioncontroller::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);

        // Create session.
        $session = $sessioncontroller->create_session($data);
        $sessionexpected = $sessioncontroller->get_session($session->id);
        $this->assertNotNull($sessionexpected);
        $attendancecontroller = new attendance_controller();
        // Set attendance.
        $attendanceid = $attendancecontroller->hybridteaching_set_attendance(0, $sessionexpected);
        $attendanceid = $attendancecontroller->hybridteaching_set_attendance($hybridobject, $sessionexpected);
        $attendancesrecords = $DB->get_records('hybridteaching_attendance', ['hybridteachingid' => $session->id]);
        $this->assertNotNull($attendancesrecords);
        // Get attendance created by session_id and/or user_id.
        $this->assertNotNull($attendancecontroller->hybridteaching_get_attendance($sessionexpected, self::$user->id));

        $this->assertNotNull($attendancecontroller->hybridteaching_get_attendance_from_id($attendanceid));

        $external = new \hybridteaching_external();
        $modaltext = $external->get_modal_text($sessionexpected->id);
        $external->get_modal_text_parameters();
        $external->get_modal_text_returns();
        // Disable attendance in progress.
        $external->disable_attendance_inprogress($cm->id);
        $this->assertNotNull($external->disable_attendance_inprogress($cm->id, $sessionexpected->id));
        $external->disable_attendance_inprogress_returns();

        $this->assertNotNull($external->get_display_actions($sessionexpected->id, self::$user->id));
        $grades = new grades();
        $this->assertTrue($grades->is_session_exempt($sessionexpected->id));
        // Has valid attendance.
        $this->assertNotNull($grades->has_valid_attendance($hybridobject->id, $sessionexpected->id, self::$user->id));
        $this->assertNotNull($attendancecontroller->hybridteaching_get_attendance_from_id(null));
        // Count user attend.
        $grades->count_user_att($hybridobject, self::$user->id);
        // Update session/attendance visivility.
        $attendancecontroller->update_session_visibility($sessionexpected->id, 1);

        $attendancecontroller->user_attendance_early_leave($sessionexpected,
        $attendancecontroller->hybridteaching_get_attendance($attendanceid));
        $attendancecontroller->user_attendance_early_leave($sessionexpected, $attendanceid);
        $this->assertFalse($attendancecontroller->user_attendance_early_leave($sessionexpected, ''));

    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'bbb', 2, ],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":10000000,"durationgroup":{"duration":450000000,"timetype":null}}'
             , 'meet', 1, ],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'bbb', 0, ],
            ['{"hybridteachingid":2,"name":"Test de prueba","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}'
             , 'meet', 1, ],
        ];
    }


}
