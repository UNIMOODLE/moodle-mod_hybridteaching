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
require_once($CFG->dirroot . '/mod/hybridteaching/lib.php');
require_once($CFG->dirroot . '/config.php');

use mod_hybridteaching\plugininfo\hybridteachstore;
use mod_hybridteaching\plugininfo\hybridteachvc;
use mod_hybridteaching\helpers\password;
use mod_hybridteaching\controller\sessions_controller;

class hybridteaching_plugin_info_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    private static $course;
    private static $context;
    private static $coursecontext;
    private static $user;
    private static $config;
    private static $userecordvc;
    private static $isupdated;

    private static $updatecalen;
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
        self::$isupdated = 0;
        self::$updatecalen = 1;
    }

    /**
     * Plugin info
     *
     * Plugin info
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
    public function test_plugin_info($baseparam, $newname, $newdescription, $typevc = null) {
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

        $hybridteachvc = new hybridteachvc();
        $hybridteachstore = new hybridteachstore();
        $this->assertTrue($hybridteachvc->is_uninstall_allowed());
        $this->assertTrue($hybridteachstore->is_uninstall_allowed());

        $this->assertNotNull($hybridteachvc->get_settings_section_name());
        $this->assertNotNull($hybridteachstore->get_settings_section_name());

        $this->assertIsBool($hybridteachvc->is_enabled());
        $this->assertIsBool($hybridteachstore->is_enabled());
        // Return if the plugin supports it.
        $supp = hybridteaching_supports(FEATURE_MOD_PURPOSE);
        $this->assertNotNull($supp);

    }

    public static function dataprovider(): array {

        return [
            ['{"hybridteachingid":1,"name":"Session 1","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}',
             "Session testing edited", "Description of session edited", "bbb" ],
             ['{"hybridteachingid":1,"name":"Session 1","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}',
             "Session testing edited", "Description of session edited", "bbb", 1 ],
            ['{"hybridteachingid":1,"name":"Session 2","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}',
             "Session testing edited 2", "Description of session 2 edited", "meet"],
            ['{"hybridteachingid":1,"name":"Session 2","context":50,"starttime":1642736531,
             "durationgroup":{"duration":45000,"timetype":null}}',
             "Session testing edited 2", "Description of session 2 edited", "meet" ],
        ];
    }

    

}
