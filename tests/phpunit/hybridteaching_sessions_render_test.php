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
require_once($CFG->dirroot . '/mod/hybridteaching/lib.php');

use mod_hybridteaching\local\attendance_table;
use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\local\sessions_table;

use mod_hybridteaching\output\renderer;

/**
 * Testing sessions render
 *
 * @group hybridteaching
 */
class hybridteaching_sessions_render_test extends \advanced_testcase {

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
     * @var \stdClass
     */
    private static $category;
    /**
     * @var \stdClass
     */
    private static $subcategory;
    /**
     * @var int
     */
    private static $page;
    /**
     * Course start
     */
    public const COURSE_START = 1704099600;
    /**
     * Course end
     */
    public const COURSE_END = 1706605200;


    public function setUp(): void {
        global $USER, $PAGE;
        parent::setUp();
        $this->resetAfterTest(true);
        self::setAdminUser();
        self::$category = self::getDataGenerator()->create_category(['name' => 'Category1']);
        self::$subcategory = self::getDataGenerator()->create_category(['name' => 'Subcategory1', 'parent' => self::$category->id]);
        self::$course = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        self::$userecordvc = 0;
        self::$config = 0;
        self::$page = $PAGE;
    }

    /**
     * Sessions render
     *
     * Sessions render
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $param
     * @param int $typelist
     * @param string $column
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_sessions_table($param, $typelist, $column) {
        // Reset after execute the test.
        global $DB, $COURSE, $USER;
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

        $sessioncontroller = new sessions_controller($hybridobject);
        // Simulate data form.
        $datadecoded = json_decode($param);
        $data = new \StdClass();
        $data->hybridteachingid = $hybridobject->id;
        $data->name = $datadecoded->name;
        isset($datadecoded->description) ? $data->description = $datadecoded->description :
        $data->description = null;
        $data->context = $datadecoded->context;
        $data->starttime = time();
        $data->durationgroup['duration'] = $datadecoded->durationgroup->duration;
        $data->durationgroup['timetype'] = $sessioncontroller::MINUTE_TIMETYPE;
        $data->duration = $sessioncontroller::calculate_duration($data->durationgroup['duration'],
                $data->durationgroup['timetype']);

        // Create session.
        $session = $sessioncontroller->create_session($data);

        $sessionsrender = new sessions_table($hybridobject, $typelist);

        $sessionsrender->get_bulk_options_select();
        $this->assertNotNull($sessionsrender->get_operator());
        $sessionsrender->get_column_name($column);

        $renderer = self::$page->get_renderer('mod_hybridteaching');
        $renderer->zone_access('');

        $COURSE = self::$course;
        $USER = self::$user;
        $sessionsrender = new sessions_table($hybridobject, 1);
        $sessionsrender->check_session_filters();
        $categories = ['cat1' => self::$category];
        // Build output categories.
        hybrid_build_output_categories([['id' => self::$category->id, 'name' => self::$category->name,
        'categories' => [['id' => self::$category->id, 'name' => self::$category->name]]]], 1);
        hybrid_get_categories_for_modal();
        hybrid_build_category_array(self::$category);
    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}', 1, 'strgroup'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}', 2, 'strtype'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}', 2, 'strdate'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}', 2, 'strduration'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}', 2, 'strname'],
            ['{"hybridteachingid":1,"name":"Test de prueba", "description": "description of session","context":50,
                "starttime":1642736531,"durationgroup":{"duration":45000,"timetype":null}}', 2, 'stranother'],

        ];
    }


}
