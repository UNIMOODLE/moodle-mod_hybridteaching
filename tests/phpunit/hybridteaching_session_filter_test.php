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
use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/helpers/calendar_helpers.php');
require_once($CFG->dirroot . '/config.php');

use mod_hybridteaching\helpers\password;
use mod_hybridteaching\controller\sessions_controller;
use mod_hybridteaching\filters\session_filter_select;
use mod_hybridteaching\filters\session_filter_date;
use mod_hybridteaching\filters\session_filter_duration;
use mod_hybridteaching\filters\session_filter_type;
use mod_hybridteaching\filters\session_filter_text;
use mod_hybridteaching\form\sessions_form;

/**
 * Testing session filter
 *
 * @group hybridteaching
 */
class hybridteaching_session_filter_test extends \advanced_testcase {

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
     * @var \stdClass
     */
    private static $userecordvc;
    /**
     * @var int
     */
    private static $page;
    /**
     * @var int
     */
    private static $value;
    /**
     * Course start
     */
    public const COURSE_START = 1704099600;
    /**
     * Course end
     */
    public const COURSE_END = 1706605200;
    /**
     * @var string
     */
    private static $namesession;

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
        self::$value = 1;
    }

    /**
     * Session filter management
     *
     * Session filter management
     *
     * @package    mod_hybridteaching
     * @copyright  2023 Proyecto UNIMOODLE
     * @param string $name
     * @param string $label
     * @param string $advanced
     * @param string $field
     * @param array $options
     * @param int $operator
     * @param string $value
     * @param int $unittime
     * @dataProvider dataprovider
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_session_filter($name = null, $label = null, $advanced = null,
    $field = null, $options = 0, $operator, $value = '', $unittime = 1) {
        // Reset after execute the test.
        global $DB;
        // Session filter select.
        $sessionfilterselect = new session_filter_select($name, $label, $advanced, $field, $options);

        $formdata = ['name' => $name, 'value' => $value, 'name_sdt' => $name, 'name_edt' => $name];
        $formdata2 = ['novalue' => $name, 'value' => '', 'name_sdt' => '', 'name_edt' => ''];

        $sessionfilterselect->check_data((object)$formdata);
        $sessionfilterselect->check_data((object)$formdata2);
        $sessionfilterselect->get_sql_filter($formdata2);
        $sessionfilterselect->get_sql_filter($formdata);

        // Session filter date.
        $sessionfilterdate = new session_filter_date($name, $label, $advanced, $field, $options);

        $sessionfilterdate->check_data((object)$formdata);
        $sessionfilterdate->check_data((object)$formdata2);

        $datasqlfilter = ['after' => time(), 'before' => time()];
        $datasqlfilter2 = ['after' => null, 'before' => null];

        $sessionfilterdate->get_sql_filter($datasqlfilter);
        $datasqlfilter['before'] = null;
        $sessionfilterdate->get_sql_filter($datasqlfilter);
        $datasqlfilter['before'] = time();
        $datasqlfilter['after'] = null;
        $sessionfilterdate->get_sql_filter($datasqlfilter);
        $sessionfilterdate->get_sql_filter($datasqlfilter2);
        // Session filter duration.
        $sessionfilterduration = new session_filter_duration($name, $label, $advanced, $field);
        $sessionfilterduration->session_filter_text($name, $label, $advanced, $field);
        $this->assertNotNull($sessionfilterduration->get_operators());
        $this->assertNotNull($sessionfilterduration->get_unit_time());

        $dataduration = ['operator' => 4, 'value' => '', 'unittime' => $unittime, 'name' => $name,
        'description' => 'description', 'time' => time()];
        $sessionfilterduration->get_sql_filter($dataduration);
        $dataduration['operator'] = $operator;
        $dataduration['value'] = $value;
        $sessionfilterduration->get_sql_filter($dataduration);

        // Session filter type.
        $sessionfiltertype = new session_filter_type($name, $label, $advanced);
        $sessionfiltertype->session_filter_type($name, $label, $advanced);

        // Session filter text.
        $sessionfiltertext = new session_filter_text($name, $label, $advanced, $field);
        $sessionfiltertext->session_filter_text($name, $label, $advanced, $field);
        $this->assertNotNull($sessionfiltertext->get_operators());
        $sessionfiltertext->get_sql_filter(['operator' => $operator, 'value' => $value]);
        $laberfiterduration = $sessionfilterduration->get_label(['operator' => $operator, 'value' => $value,
        'unittime' => $unittime]);

        $this->assertTrue(true);
    }

    /**
     * Data provider for execute
     *
     * @return array[]
     */
    public static function dataprovider(): array {

        return [
            [ 'name', 'c', 'd', 'b', ['value' => 'value'], 0, 40, 1],
            [ 'name', 'b', 'c', 'd', ['value' => 'value'], 1, 50, 2],
            [ 'name', 'c', 'd', 'b', ['value' => 'value'], 2, 50, 2],
            [ 'name', 'b', 'c', 'd', ['value' => 'value'], 2, 60, 1],
            [ 'name', 'b', 'c', 'd', ['value' => 'value'], 2, 60, 1],
            [ 'name', 'b', 'c', 'd', ['value' => 'value'], 1, 60, 1],
            [ 'name', 'b', 'c', 'd', ['value' => 'value'], 0, 60, 1],

        ];
    }

}
