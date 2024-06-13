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
use mod_hybridteaching\controller\common_controller;
use hybridteachvc_bbb\configs;
use mod_hybridteaching\controller\configs_controller;

/**
 * Testing create unique session
 *
 * @group hybridteaching
 */
class hybridteaching_create_session_test extends \advanced_testcase {

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
        self::$config = 0;
        self::$userecordvc = 1;
    }

    /**
     * Create session
     *
     * Create a session
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @param string $typevc
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_create_session($param, $configdata, $typevc = null) {
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
            'userecordvc' => self::$userecordvc,
            ]);
        $cm = get_coursemodule_from_instance('hybridteaching', $hybridobject->id, self::$course->id);

        // Create config.
        $subpluginid = $this->subplugininfo();
        $hybridconfig = new \stdClass();
        $hybridconfig->type = 'bbb';
        $configscontroller = new configs_controller($hybridconfig, 'hybridteachvc');
        $this->createconfig($configscontroller, $hybridobject, $configdata, $subpluginid);

        $sessioncontroller = new sessions_controller($hybridobject);
        // Simulate data form.
        $datadecoded = json_decode($param);
        $data = new \StdClass();
        $data->hybridteachingid = $hybridobject->id;
        $data->name = $datadecoded->name;
        isset($datadecoded->description) ? $data->description = $datadecoded->description : $data->description = null;
        $data->context = $datadecoded->context;
        $data->starttime = time();
        $data->userecordvc = self::$userecordvc;
        $data->typevc = $typevc;
        $data->durationgroup['duration'] = $datadecoded->durationgroup->duration;
        $data->durationgroup['timetype'] = $sessioncontroller::MINUTE_TIMETYPE;
        isset($datadecoded->noduration) ? $data->duration = null :
        $data->duration = $sessioncontroller::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);

        // Create session.
        $session = $sessioncontroller->create_session($data);

        $this->assertGreaterThan(0, $sessioncontroller->count_sessions());
        $this->assertNotNull($sessioncontroller->get_last_undated_session());
        $sessionexpected = $sessioncontroller->get_session($session->id);

        $this->assertNotNull($sessionexpected);
        // Check conf of this session doesnt exist.
        $this->assertFalse($sessioncontroller->get_sessionconfig_exists($sessionexpected));
        // Check this session is started.
        $this->assertFalse($sessioncontroller->session_started($sessionexpected));
        // Get the last session created.
        $this->assertNotNull($sessioncontroller->get_last_session());
        // Enable data.
        $commoncontroller = new common_controller();
        $this->assertTrue($commoncontroller->hybridteaching_exist($hybridobject->id));
        $this->assertFalse($commoncontroller->hybridteaching_exist('fail'));
        $commoncontroller->enable_data($hybridobject->id, 1, 'hybridteaching_session');
        // Get enable data session.
        $commoncontroller->get_enabled_data('hybridteaching_session');

    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            ['{"name":"Test 1", "description": "description ","context":50,"starttime":1642736531,
            "durationgroup":{"duration":45000,"timetype":null}}'
            , '{"configname":"Testing", "category":1, "version":2024010901, "visible":1,"subpluginconfigid":1,
            "id":99}', 'bbb',
            ],
            ['{"name":"Name","description": "description session", "context":50,"starttime":1642736531,"durationgroup":
            {"duration":45000,"timetype":null}}',
            '{"configname":"Testing", "categories":"1", "version":2024010901, "visible":1,"subpluginconfigid":1, "id":99}',
            'bbb'],
            ['{"name":"Name","context":50,"description": "description session","starttime":1642736531,"durationgroup":
            {"duration":45000,"timetype":null}}',
            '{"configname":"Testing", "categories":"cat1,cat2,cat3,", "version":2024010901, "visible":1,"subpluginconfigid":1,
            "id":99}', 'meet'],
            ['{"name":"Name","context":50,"description": "description session","starttime":1642736531,"noduration":true,
            "durationgroup":{"duration":45000,"timetype":null}}',
            '{"configname":"Testing", "categories":"1", "version":2024010901, "visible":1,"subpluginconfigid":1, "id":99}',
            'bbb'],
            ['{"name":"Name","context":50,"description": "description session","starttime":1642736531,"noduration":true,
            "durationgroup":{"duration":45000,"timetype":null}}',
            '{"configname":"Testing", "categories":"1", "version":2024010901, "visible":1,"subpluginconfigid":1, "id":99}',
            'bbb'],

        ];
    }

    /**
     * Create sub plugin config
     *
     * @return int $subpluginid
     */
    public function subplugininfo() {
        // Create subplugin config.
        $databbb = new \stdClass();
        $databbb->serverurl = "serverurl";
        $databbb->sharedsecret = "sharedsecret";
        $subpluginid = configs::create_config($databbb);
        return $subpluginid;
    }

    /**
     * Create config
     *
     * @param configs_controller $configscontroller
     * @param \stdClass $hybridobject
     * @param \stdClass $configdata
     * @param int $bbbid
     * @return void create_config
     */
    public function createconfig($configscontroller, $hybridobject, $configdata, $bbbid) {

        // Create config data.
        $config = new \StdClass();
        $configdatadecoded = json_decode($configdata);
        $config->id = $bbbid;
        $config->configname = $configdatadecoded->configname;
        isset($configdatadecoded->category) ? $config->category = $configdatadecoded->category : $config->category = null;
        $config->id = $configdatadecoded->subpluginconfigid;
        $config->subplugintype = $bbbid;
        // Create config / Da error con un include de la funcion.
        $configscontroller->hybridteaching_create_config($config);
    }


}
