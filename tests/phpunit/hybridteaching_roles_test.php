<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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
use mod_hybridteaching\helpers\roles;

/**
 * Testing roles
 *
 * @group hybridteaching
 */
class hybridteaching_roles_test extends \advanced_testcase {

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
        global $USER;
        parent::setUp();
        $this->resetAfterTest(true);
        self::setAdminUser();
        self::$course = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );
        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        self::getDataGenerator()->enrol_user(self::$user->id, self::$course->id);
        self::$userecordvc = 0;
        self::$config = 0;
    }

    /**
     * Managing roles
     *
     * Managing roles
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $context
     * @param string $role
     * @param array $participantlist
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_roles($context = null, $role = null, $participantlist = null) {
        // Reset after execute the test.
        global $DB;
        $role == 'manager' ? self::setAdminUser() : $this->setGuestUser();
        $generator = self::getDataGenerator()->get_plugin_generator('mod_hybridteaching');
        $hybridobject = $generator->create_instance(['course' => self::$course->id,
            'name' => 'hybt',
            'timetype' => null,
            'config' => self::$config,
            'userecordvc' => self::$userecordvc,
            ]);

        $roles = new roles();
        $reflectionmethod = new \ReflectionMethod(roles::class, 'get_guest_role');
        $reflectionmethod->setAccessible(true);
        $getguestroles = $reflectionmethod->invoke($roles);

        $getroles = roles::get_roles(self::$coursecontext, false);
        $getroles = roles::get_roles(self::$coursecontext);
        $this->assertNotNull($getroles);

        $getuserroles = roles::get_user_roles(self::$coursecontext, self::$user->id);
        $this->assertNotNull($getuserroles);

        $reflectionmethod = new \ReflectionMethod(roles::class, 'get_role');
        $reflectionmethod->setAccessible(true);
        $getrole = $reflectionmethod->invoke($roles, $role);

        $participantselectiondata = roles::get_participant_selection_data();
        $this->assertNotNull($participantselectiondata);

        $participantdata = roles::get_participant_data(self::$coursecontext, $hybridobject);
        $this->assertNotNull($participantdata);

    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            [ 50, 'manager', []],
            [50, 'student', []],
            [ 50, 1, []],
            [40, 'manager', []],
            [40, 'manager', [self::$user]],
            [40, 'random_role', [self::$user]],
        ];
    }

}
