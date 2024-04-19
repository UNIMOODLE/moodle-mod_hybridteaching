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
require_once($CFG->dirroot . '/mod/hybridteaching/classes/observer.php');

use mod_hybridteaching\helpers\password;
use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\controller\attendance_controller;
use mod_hybridteaching\helpers\grades;

/**
 * Testing events
 *
 * @group hybridteaching
 */
class hybridteaching_events_test extends \advanced_testcase {

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
     * Manage an event
     *
     * Manage an event
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @param string $typevc
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_events($param, $typevc, $eventclass) {
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
        $hybridobject->usevideoconference = self::$usevideoconference;

        $sessioncontroller = new sessions_controller($hybridobject);
        // Simulate data form.
        $datadecoded = json_decode($param);
        $data = new \StdClass();
        $data->hybridteachingid = $datadecoded->hybridteachingid;
        $data->name = $datadecoded->name;
        $data->context = $datadecoded->context;
        $data->starttime = time();
        $data->typevc = $typevc;
        $data->durationgroup['duration'] = $datadecoded->durationgroup->duration;
        $data->durationgroup['timetype'] = $sessioncontroller::MINUTE_TIMETYPE;
        $data->duration = $sessioncontroller::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);
        // Create session.
        $session = $sessioncontroller->create_session($data);
        $sessionexpected = $sessioncontroller->get_session($session->id);

        // Manage every event.
        $this->manage_events($hybridobject, $cm, $eventclass, $sessionexpected->id);
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
             , 'bbb', 'attendance_manage_viewed'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'meet', 'attendance_updated'],

            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'meet', 'course_module_viewed'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'bbb', 'session_added'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'meet', 'session_deleted'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'bbb', 'session_finished'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'meet', 'session_info_viewed'],
             ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'bbb', 'session_joined'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'meet', 'session_manage_viewed'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'bbb', 'session_record_downloaded'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'meet', 'session_record_viewed'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'bbb', 'session_updated'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
             "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
             , 'meet', 'session_viewed'],
             ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'
            , 'bbb', 'attendance_viewed'],

        ];
    }


    /**
     * Manage events
     *
     * @param \stdClass $hybridobject
     * @param \stdClass $cm
     * @param string $classname
     * @param int $sessid
     */
    public function manage_events($hybridobject, $cm, $classname, $sessid) {
        $eventclassname = "\\mod_hybridteaching\\event\\" . $classname;
        $event = $eventclassname::create([
            'objectid' => $hybridobject->id,
            'context' => \context_module::instance($cm->id),
            'other' => [
                'multiplesess' => "1",
                'userid' => self::$user->id,
                'sessid' => $sessid,
                'attid' => 1,
                'action' => 1,
            ],
        ]);
        $this->assertNotNull($event->get_description());
        $this->assertNotNull($event->get_name());
        $this->assertNotNull($event->get_objectid_mapping());
        switch ($classname) {
            case "session_added":
                $event = $eventclassname::create([
                    'objectid' => $hybridobject->id,
                    'context' => \context_module::instance($cm->id),
                    'other' => [
                        'multiplesess' => "",
                        'userid' => self::$user->id,
                        'sessid' => $sessid,
                        'attid' => 1,
                        'action' => 1,
                    ],
                ]);
                $this->assertNotNull($event->get_description());
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_added::class, 'get_legacy_logdata');
                break;
            case "course_module_viewed":
                $this->assertNotNull($event->get_url());
                $method = new \ReflectionMethod(\mod_hybridteaching\event\course_module_viewed::class, 'get_legacy_logdata');
                break;
            case "session_viewed":
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_viewed::class, 'get_legacy_logdata');
                break;
            case "session_joined":
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_joined::class, 'get_legacy_logdata');
                $observer = new \mod_hybridteaching_observer();
                $observer->session_joined($event);
                break;
            case "session_manage_viewed":
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_manage_viewed::class, 'get_legacy_logdata');
                break;
            case "attendance_manage_viewed":
                $method = new \ReflectionMethod(\mod_hybridteaching\event\attendance_manage_viewed::class, 'get_legacy_logdata');
                break;
            case "attendance_viewed":
                $method = new \ReflectionMethod(\mod_hybridteaching\event\attendance_viewed::class, 'get_legacy_logdata');
                break;
            case "session_record_downloaded":
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_record_downloaded::class, 'get_legacy_logdata');
                break;
            case "session_record_viewed":
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_record_viewed::class, 'get_legacy_logdata');
                break;
            case 'attendance_updated':
                $method = new \ReflectionMethod(\mod_hybridteaching\event\attendance_updated::class, 'get_legacy_logdata');
                break;
            case 'session_added':
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_added::class, 'get_legacy_logdata');
                break;
            case 'session_deleted':
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_deleted::class,
                'get_legacy_logdata');
                break;
            case 'session_finished':
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_finished::class,
                'get_legacy_logdata');
                break;
            case 'session_info_viewed':
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_info_viewed::class,
                'get_legacy_logdata');
                break;
            case 'session_updated':
                $method = new \ReflectionMethod(\mod_hybridteaching\event\session_updated::class,
                'get_legacy_logdata');
                break;
            default:
                break;
        }
        $method->setAccessible(true);
        $getlegacylogdata = $method->invoke($event);
    }

}
