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

class hybridteaching_set_visibility_record_session_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    private static $course;
    private static $context;
    private static $coursecontext;
    private static $user;
    private static $config;
    private static $userecordvc;
    private static $isvisible;
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
        self::$isvisible = 1;
    }

    /**
     * Set visibility record
     *
     * Set visibility record session
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @param boolean $visibility
     * @param boolean $visibilitychat
     * @covers \hybridteaching_set_visibility_record_session::set_visibility_record_session
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_set_visibility_record_session($param, $visibility, $visibilitychat) {
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
            'userecordvc' => self::$userecordvc
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
        $visibility == 1 ? $data->visiblerecord = 1 : $data->visiblerecord = 0;
        $visibilitychat == 1 ? $data->visiblechat = 1 : $data->visiblechat = 0;
 

        // Create session.
        $session = $sessioncontroller->create_session($data);
        $sessionexpected = $sessioncontroller->get_session($session->id);
        // Print print_r($sessionexpected).
        $this->assertNotNull($sessionexpected);
        // Check that session records are visible.
        $sessionexpected->visiblerecord == 1 ? self::$isvisible = 1 : self::$isvisible = 0;
        $this->assertEquals(1, $sessionexpected->visiblerecord);
        // Change visibility (if visibility == 1, visibility = 0 and if visibility == 0, visibility = 1).
        $sessioncontroller->set_record_visibility($sessionexpected->id);
        // Change visibility chat
        $sessioncontroller->set_chat_visibility($sessionexpected->id);

        // Change again visibilities.
        // Change visibility records.
        $sessioncontroller->set_record_visibility($sessionexpected->id);
        // Change visibility chat
        $sessioncontroller->set_chat_visibility($sessionexpected->id);
        $sessionbeforevisibleset = $sessioncontroller->get_session($sessionexpected->id);
        $sessionbeforevisibleset->visiblerecord == 1 ? self::$isvisible = 1 : self::$isvisible = 0;
        // Check that session records are invisible now.
        $this->assertEquals(0, $sessionbeforevisibleset->visiblerecord);
        

    }
    public static function dataprovider(): array {

        return [
           ['{"hybridteachingid":2,"name":"Test de prueba","context":50,"starttime":1642736531,
            "durationgroup":{"duration":45000,"timetype":null}}', 1, 0],
           ['{"hybridteachingid":2,"name":"Test de prueba 2","context":50,"starttime":1642736531,
            "durationgroup":{"duration":45000,"timetype":null}}', 0, 1],
           ['{"hybridteachingid":2,"name":"Test de prueba 3","context":50,"starttime":1642736531,
            "durationgroup":{"duration":45000,"timetype":null}}', 1, 0],
        ];
    }

    /**
     * @covers \hybridteaching_set_visibility_record_session::isvisible
     */
    public function test_isvisible() {
        $this->assertEquals(1, self::$isvisible);
    }

}
