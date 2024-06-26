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
use hybridteachvc_bbb\sessions;

/**
 * Testing vc create session
 *
 * @group hybridteaching
 */
class hybridteaching_vc_create_session_test extends \advanced_testcase {

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
        self::$userecordvc = 0;
        self::$config = 0;
    }

    /**
     * Create session
     *
     * Create a session
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_vc_create_session($param) {
        // Reset after execute the test.
        global $DB;
        // Module instance.
        $moduleinstance = new \stdClass();
        $moduleinstance->course = self::$course->id;

        $bbbconfigid = $this->createbbbconfig();
        $config = $this->createconfig($bbbconfigid);
        $generator = self::getDataGenerator()->get_plugin_generator('mod_hybridteaching');
        $hybridobject = $generator->create_instance(['course' => self::$course->id,
            'name' => 'hybt',
            'timetype' => null,
            'config' => self::$config,
            'userecordvc' => self::$userecordvc,
            ]);
        $cm = get_coursemodule_from_instance('hybridteaching', $hybridobject->id, self::$course->id);

        $bbbconfig = new \hybridteachvc_bbb\configs($hybridobject, "bbb");
        $bbbconfigstd = new \StdClass();
        $bbbconfigstd->serverurl = "url";
        $bbbconfigstd->sharedsecret = "sharedsecret";
        $configid = $bbbconfig->create_config($bbbconfigstd);

        // Create hybobj.

        $sessioncontroller = new sessions_controller($hybridobject);
        $sessioncontroller->get_subpluginvc_class("bbb");
        $sessioncontroller->require_subplugin_session("bbb");
        $sessioncontroller->get_subpluginstore_class("youtube");
        $sessioncontroller->require_subplugin_store("youtube");

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
        $external = new \hybridteaching_external();
        $this->assertNotNull($external->get_user_has_recording_capability('', 2));
        $external->get_user_has_recording_capability_returns();
        $external->get_user_has_recording_capability('bbb', 2);

        $sessionexpected = $sessioncontroller->get_session($session->id);

        $bbb = new sessions();
        // Create session for bbb vc.
        $sessioncreated = $bbb->create_unique_session_extended($sessionexpected, $hybridobject);
        $displayactions = $external->get_display_actions($session->id, self::$user->id);

        $this->assertNotNull($displayactions);
        $this->assertNotNull($sessioncontroller->get_last_undated_session());
        $this->assertIsBool($sessioncreated);

    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}'],
            ['{"hybridteachingid":2,"name":"Test de prueba","context":50,"starttime":1642736531,"durationgroup":
                {"duration":45000,"timetype":null}}'],
        ];
    }

    /**
     * Create subplugin config
     *
     * @return int $bbbconfigid
     */
    public static function createbbbconfig() {

        $bbbconfig = new \hybridteachvc_bbb\configs(null, "bbb");
        $bbbconfigstd = new \StdClass();
        $bbbconfigstd->serverurl = "url";
        $bbbconfigstd->sharedsecret = "sharedsecret";
        $bbbconfigid = $bbbconfig->create_config($bbbconfigstd);
        return $bbbconfigid;
    }

    /**
     * Create config
     *
     * @param int $bbbconfigid
     * @return int $configid
     */
    public static function createconfig($bbbconfigid) {
        global $DB;
        $config = new \StdClass();
        $config->configname = "configname";
        $config->category = 1;
        $config->version = 1;
        $config->visible = 1;
        $config->type = "bbb";
        $config->timecreated = time();
        $config->id = $bbbconfigid;
        $configid = $DB->get_record_sql('SELECT id
                                           FROM {hybridteaching_configs}
                                          WHERE id =
                                        (SELECT max(id)
                                           FROM {hybridteaching_configs})');
        return $configid;
    }


}
