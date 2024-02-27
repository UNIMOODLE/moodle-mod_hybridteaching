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
//require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/configs_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helpers/calendar_helpers.php');
require_once($CFG->dirroot . '/mod/hybridteaching/lib.php');
require_once($CFG->dirroot . '/config.php');

use mod_hybridteaching\controller\configs_controller;
use hybridteachvc_bbb\configs;

class hybridteaching_create_config_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    private static $course;
    private static $context;
    private static $coursecontext;
    private static $user;
    private static $config;
    private static $userecordvc;
    private static $subplugintype;
    private static $type;
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
        self::$subplugintype = "vc";
        self::$type = "bbb";
    }

    /**
     *
     * Create a config
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $configdata
     * @param string $param
     * @covers \hybridteaching_create_session::create_session
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_create_config($configdata, $param) {
        // Reset after execute the test.
        global $DB;

        
        // Create hybobj.
        $generator = self::getDataGenerator()->get_plugin_generator('mod_hybridteaching');
        $hybridobject = $generator->create_instance(['course' => self::$course->id,
            'name' => 'hybt',
            'timetype' => null,
            'config' => self::$config,
            'userecordvc' => self::$userecordvc,
            'subplugintype' => self::$subplugintype,
            ]);
        $cm = get_coursemodule_from_instance('hybridteaching', $hybridobject->id, self::$course->id);

        $fileareas = hybridteaching_get_file_areas(self::$course, $cm, self::$coursecontext);
        $fileinfo =  hybridteaching_get_file_info('browser', 'areas', self::$course, $cm, self::$coursecontext
        , 'filearea', 1, 'filepath', 'filename');
        // Create subplugin config.
        $databbbid = $this->subplugininfo();
        $hybridconfig = new \stdClass();
        $hybridconfig->type = 'bbb';
        $configscontroller = new configs_controller($hybridconfig, 'hybridteachvc');
        // Create config.
        $this->createconfig($configscontroller, $hybridobject, $configdata, $databbbid);
        // Check config was created.
        $this->assertNotNull($configscontroller->hybridteaching_load_config(1));
        $this->assertNotNull($configscontroller->hybridteaching_get_configs_select(0));
        $this->assertNotNull($configscontroller->get_categories_conditions(['category' => 0]));
        // Delete config.
        $configscontroller->hybridteaching_delete_config(1);
        $configscontroller->hybridteaching_delete_config('fail');

    }
    public static function dataprovider(): array {

        return [
            ['{"configname":"Testing config data", "category":1, "version":2024010901, "visible":1,"subpluginconfigid":1, "id":99}'
            , '{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531, "durationgroup":{"duration":45000,"timetype":null} }', ],
            ['{"configname":"Testing config data", "category":1, "version":2024010901, "visible":0,"subpluginconfigid":1, "id":59}'
            , '{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
            "starttime":1642736531, "durationgroup":{"duration":45000,"timetype":null}, }, ', ],
        ];
    }
    /**
     * Create subplugin config
     * 
     * @return int $databbbid
     */
    public function subplugininfo(){
        // Create subplugin config.
        $databbb = new \stdClass();
        $databbb->serverurl = "serverurl";
        $databbb->sharedsecret = "sharedsecret";
        $databbbid = configs::create_config($databbb);
        return $databbbid;
    }


    /**
     * Create config.
     * 
     * @param configs_controller $configscontroller
     * @param \StdClass $hybridobject
     * @param \StdClass $configdata
     * @param int $bbbid
     * 
     */
    public function createconfig($configscontroller, $hybridobject, $configdata, $bbbid){

        // Create config data.
        $config = new \StdClass();
        $configdatadecoded = json_decode($configdata);
        $config->configname = $configdatadecoded->configname;
        isset($configdatadecoded->category) ? $config->category = $configdatadecoded->category : $config->category = null; 
        $config->id = $configdatadecoded->subpluginconfigid;
        $config->subplugintype = $bbbid;
        // Create config / Da error con un include de la funcion.
        $configscontroller->hybridteaching_create_config($config);
    }


}
