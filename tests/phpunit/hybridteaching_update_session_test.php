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
use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helpers/calendar_helpers.php');
require_once($CFG->dirroot . '/config.php');

use mod_hybridteaching\helpers\password;
use mod_hybridteaching\controller\sessions_controller;

/**
 * Testing update session
 *
 * @group hybridteaching
 */
class hybridteaching_update_session_test extends \advanced_testcase {

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
    private static $isupdated;
    /**
     * @var int
     */
    private static $updatecalen;
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
        self::$isupdated = 0;
        self::$updatecalen = 1;
    }

    /**
     * Update session
     *
     * Update a session
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $baseparam
     * @param string $newname
     * @param string $newdescription
     * @param string $typevc
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_update_session($baseparam, $newname, $newdescription, $typevc = null) {
        global $DB;
        // Create hybobj.
        $generator = self::getDataGenerator()->get_plugin_generator('mod_hybridteaching');
        $hybridobject = $generator->create_instance(['course' => self::$course->id,
            'name' => 'hybt',
            'timetype' => null,
            'config' => self::$config,
            'userecordvc' => self::$userecordvc,
            ]);
        $cm = get_coursemodule_from_instance('hybridteaching', $hybridobject->id, self::$course->id);

        $sessioncontroller = new sessions_controller();
        $sessioncontroller = new sessions_controller($hybridobject);
        // Simulate data form.
        $datadecoded = json_decode($baseparam);
        $data = new \StdClass();
        $data->hybridteachingid = $hybridobject->id;
        $data->name = $datadecoded->name;
        $data->typevc = $typevc;
        $data->updatecalen = self::$updatecalen;
        $data->context = $datadecoded->context;
        $data->starttime = time();
        $data->durationgroup['duration'] = $datadecoded->durationgroup->duration;
        $data->durationgroup['timetype'] = $sessioncontroller::MINUTE_TIMETYPE;
        $data->duration = $sessioncontroller::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);

        // Create session.
        $session = $sessioncontroller->create_session($data);

        $sessionexpected = $sessioncontroller->get_session($session->id);
        $this->assertNotNull($sessioncontroller->get_session($session->id));
        // Update session.
        $sessionedited = clone $session;
        $sessionedited->name = $newname;
        $sessionedited->description = $newdescription;
        if ($typevc == null) {
            $sessionedited = 1;
        }
        $sessioncontroller->update_session($sessionedited);

        // Check session and sessionedited are the same.
        $this->assertSame($sessioncontroller->get_session($sessionedited->id)->id, $sessionexpected->id);

        $sessioncontroller->get_session($sessionedited->id)->name != $sessionexpected->name ? self::$isupdated = 1
        : self::$isupdated = 0;
        // Check that name was changed.
        $this->assertNotEquals($sessioncontroller->get_session($sessionedited->id)->name, $sessionexpected->name);
        $this->assertEquals(1, self::$isupdated);
        // Check that session was modified.

        $sessioncontroller->load_sessions(0, 0, ['starttime' => $data->starttime - 1000], 'hybridteachingid != 0');

        $this->assertGreaterThan(0, $sessioncontroller->get_session($sessionedited->id)->timemodified);
        // Try insert subplugin extension.
        $DB->insert_record('hybridteachvc_bbb', ['htsession' => $sessionexpected->id, 'meetingid' => 1, 'recordingid' => 1,
        'createtime' => 1]);
        $this->assertIsBool($sessioncontroller->session_started($sessionexpected));
    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            ['{"hybridteachingid":1,"name":"Session 1", "description":"description 1","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}',
             "Session testing edited", "Description of session edited", 'bbb' ],
             ['{"hybridteachingid":1,"name":"Session 1", "description":"description 1","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}',
             "Session testing edited", "Description of session edited", 'bbb' ],
            ['{"hybridteachingid":1,"name":"Session 2", "description":"description 2","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}',
             "Session testing edited 2", "Description of session 2 edited", 'meet'],
            ['{"hybridteachingid":1,"name":"Session 2", "description":"description 2","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}',
             "Session testing edited 2", "Description of session 2 edited", 'meet'],
        ];
    }



}
