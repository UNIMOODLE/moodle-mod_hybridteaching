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
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\import;

use stdClass;
use csv_import_reader;
use mod_hybridteaching\controller\notify_controller;
use mod_hybridteaching\controller\sessions_controller;
use DateTime;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');
require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/notify_controller.php');

/**
 * Class sessions_import.
 */
class sessions_import {

    /** @var string $error The errors message from reading the xml */
    protected $error = '';

    /** @var array $sessions The sessions info */
    protected $sessions = [];

    /** @var array $mappings The mappings info */
    protected $mappings = [];

    /** @var int The id of the csv import */
    protected $importid = 0;

    /** @var csv_import_reader|null  $importer */
    protected $importer = null;

    /** @var array $foundheaders */
    protected $foundheaders = [];

    /** @var bool $useprogressbar Control whether importing should use progress bars or not. */
    protected $useprogressbar = false;

    /** @var \core\progress\display_if_slow|null $progress The progress bar instance. */
    protected $progress = null;

    /** @var object $courseprovided If course has been provided we don't need to map the course field*/
    protected $course = null;

    /**
     * Store an error message for display later
     *
     * @param string $msg
     */
    public function fail($msg) {
        $this->error = $msg;
        return false;
    }

    /**
     * Get the CSV import id
     *
     * @return string The import id.
     */
    public function get_importid() {
        return $this->importid;
    }

    /**
     * Get the list of headers required for import.
     *
     * @return array The headers (lang strings)
     */
    public function list_required_headers() {
        $headers = [];

        $headers[] = get_string('groups');
        $headers[] = get_string('name');
        $headers[] = get_string('start', 'hybridteching');
        $headers[] = get_string('duration', 'hybridteching');
        $headers[] = get_string('description');
        $headers[] = get_string('repeaton', 'hybridteching');
        $headers[] = get_string('repeatevery', 'hybridteching');
        $headers[] = get_string('repeatuntil', 'hybridteching');

        return $headers;
    }

    /**
     * Get the list of headers found in the import.
     *
     * @return array The found headers (names from import)
     */
    public function list_found_headers() {
        return $this->foundheaders;
    }

    /**
     * Read the data from the mapping form.
     *
     * @param object $data The mapping data.
     */
    protected function read_mapping_data($data) {
        $headerkeys = [];
        $headerkeys[] = 'groups';
        $headerkeys[] = 'name';
        $headerkeys[] = 'starttime';
        $headerkeys[] = 'duration';
        $headerkeys[] = 'timetype';
        $headerkeys[] = 'attexempt';
        $headerkeys[] = 'description';
        $headerkeys[] = 'repeaton';
        $headerkeys[] = 'repeatevery';
        $headerkeys[] = 'repeatuntil';

        $valuecount = count($headerkeys) - 1;
        if ($data) {
            $headervalues = [];
            for ($i = 0; $i <= $valuecount; $i++) {
                $headervalues[] = $data->{"header$i"} ?? null;
            }
        } else {
            $headervalues = range(0, $valuecount);
        }
        return array_combine($headerkeys, $headervalues);
    }

    /**
     * Get the a column from the imported data.
     *
     * @param array $row The imported raw row
     * @param int $index The column index we want
     * @return string The column data.
     */
    protected function get_column_data($row, $index) {
        if ($index < 0) {
            return '';
        }
        return isset($row[$index]) ? $row[$index] : '';
    }

    /**
     * Constructor - parses the raw text for sanity.
     *
     * @param null|string $text The raw csv text.
     * @param null|string $encoding The encoding of the csv file.
     * @param null|string $delimiter The specified delimiter for the file.
     * @param string $importid The id of the csv import.
     * @param null|array $mappingdata The mapping data from the import form.
     * @param null|bool $useprogressbar Whether progress bar should be displayed, to avoid html output on CLI.
     * @param null|object $course The course object.
     * @param null|object $hybridteaching Hybridteaching object.
     */
    public function __construct($text = null, $encoding = null, $delimiter = null, $importid = 0,
                                $mappingdata = null, $useprogressbar = false, $course = null, $hybridteaching = null) {
        global $CFG, $DB;

        require_once($CFG->libdir . '/csvlib.class.php');

        $this->course = $course;
        $type = 'htsessions';

        if (!$importid) {
            if ($text === null) {
                return;
            }

            $this->importid = csv_import_reader::get_new_iid($type);
            $this->importer = new csv_import_reader($this->importid, $type);

            if (!$this->importer->load_csv_content($text, $encoding, $delimiter)) {
                $this->fail(get_string('invalidimportfile', 'hybridteaching'));
                $this->importer->cleanup();
                return;
            }
        } else {
            $this->importid = $importid;
            $this->importer = new csv_import_reader($this->importid, $type);
        }

        if (!$this->importer->init()) {
            $this->fail(get_string('invalidimportfile', 'hybridteaching'));
            $this->importer->cleanup();
            return;
        }

        $this->foundheaders = $this->importer->get_columns();
        $this->useprogressbar = $useprogressbar;
        $sessions = [];
        $rowcount = 1;

        while ($row = $this->importer->next()) {
            $mapping = $this->read_mapping_data($mappingdata);

            $session = new stdClass();
            $session->hybridteachingid = $hybridteaching->id;

            $groups = $this->get_column_data($row, $mapping['groups']);
            if (!empty($groups)) {
                $session->groups = explode(';', $groups);
            } else {
                $session->groups = 0;
            }

            $name = $this->get_column_data($row, $mapping['name']);
            if (!empty($name)) {
                $session->name = $name;
            } else {
                notify_controller::notify_problem(get_string('error:importsessionname', 'hybridteaching', $rowcount++));
                continue;
            }

            if (!empty($this->get_column_data($row, $mapping['starttime']))) {
                $starttime = DateTime::createFromFormat('Y-m-d H:i',
                $this->get_column_data($row, $mapping['starttime']))->getTimestamp();
                if ($starttime === false) {
                    notify_controller::notify_problem(get_string('formaterror:importsessionstarttime',
                        'hybridteaching', $rowcount++));
                    continue;
                }
            } else {
                notify_controller::notify_problem(get_string('error:importsessionstarttime', 'hybridteaching', $rowcount++));
                continue;
            }

            $session->starttime = $starttime;
            $duration = $this->get_column_data($row, $mapping['duration']);
            if (!empty($duration)) {
                if (is_number($duration)) {
                    $session->duration = $duration;
                } else {
                    notify_controller::notify_problem(get_string('formaterror:importsessionduration',
                        'hybridteaching', $rowcount++));
                    continue;
                }
            } else {
                notify_controller::notify_problem(get_string('error:importsessionduration', 'hybridteaching', $rowcount++));
                continue;
            }

            $timetype = $this->get_column_data($row, $mapping['timetype']);
            if (!empty($timetype) && ($timetype == sessions_controller::MINUTE_TIMETYPE ||
                $timetype == sessions_controller::HOUR_TIMETYPE)) {
                $session->timetype = $timetype;
            } else {
                notify_controller::notify_problem(get_string('error:importsessiontimetype', 'hybridteaching', $rowcount++));
                continue;
            }

            $attexempt = $this->get_column_data($row, $mapping['attexempt']);
            if (!empty($attexempt) && $attexempt == HYBRIDTEACHING_EXEMPT) {
                $session->attexempt = $attexempt;
            } else {
                $session->attexempt = HYBRIDTEACHING_NOT_EXEMPT;
            }

            // Wrap the plain text description in html tags.
            $session->sdescription['text'] = '<p>' . $this->get_column_data($row, $mapping['description']) . '</p>';
            $session->sdescription['format'] = FORMAT_HTML;
            $session->sdescription['itemid'] = 0;

            // Reapeating session settings.
            if (empty($mapping['repeaton'])) {
                $session->sdays = [];
            } else {
                $repeaton = $this->get_column_data($row, $mapping['repeaton']);
                $sdays = array_map('trim', explode('|', $repeaton));
                $session->sdays = array_fill_keys($sdays, 1);
            }

            if (empty($mapping['repeatevery'])) {
                $session->period = '';
            } else {
                $session->period = $this->get_column_data($row, $mapping['repeatevery']);
            }

            if (empty($mapping['repeatuntil'])) {
                $session->sessionenddate = null;
            } else {
                $session->sessionenddate = strtotime($this->get_column_data($row, $mapping['repeatuntil']));
            }

            if (!empty($session->sdays) && !empty($session->period) && !empty($session->sessionenddate)) {
                $session->addmultiply = 1;
            }

            $sessions[] = $session;
            $rowcount++;
        }

        $this->sessions = $sessions;
        $this->importer->close();
        if ($this->sessions == null) {
            $this->fail(get_string('invalidimportfile', 'hybridteaching'));
            return;
        } else {
            if ($this->useprogressbar === true) {
                $this->progress = new \core\progress\display_if_slow(get_string('processingfile', 'hybridteaching'));
                $this->progress->start_html();
            } else {
                $this->progress = new \core\progress\none();
            }
            $this->progress->start_progress('', count($this->sessions));
            raise_memory_limit(MEMORY_EXTRA);
            $this->progress->end_progress();
        }

    }

    /**
     * Get parse errors.
     *
     * @return array of errors from parsing the xml.
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * Create sessions using the CSV data.
     *
     * @param object $hybridteaching Hybridteaching object
     * @return void
     */
    public function import($hybridteaching) {
        global $DB;

        $okcount = 0;
        $sessioncontroller = new sessions_controller($hybridteaching);

        foreach ($this->sessions as $session) {
            $groupids = [];
            // Translate group names to group IDs. They are unique per course.

            if (is_array($session->groups)) {
                foreach ($session->groups as $groupname) {
                    $gid = groups_get_group_by_name($this->course->id, $groupname);
                    if ($gid === false) {
                        if ($groupname === 'All groups') {
                            $groupids[] = 0;
                        } else {
                            notify_controller::notify_problem(get_string('error:sessionunknowngroup',
                                'hybridteaching', $groupname));
                        }
                    } else {
                        $groupids[] = $gid;
                    }
                }
                $session->groups = $groupids;
            } else {
                $session->groups = [$session->groups];
            }

            $duration = $session->duration;
            foreach ($session->groups as $gid) {
                $session->groupid = $gid;
                if (!$this->session_exists($session, $hybridteaching->id)) {
                    $sessioncontroller->create_session($session);
                    $session->duration = $duration;
                    $okcount++;
                }
            }
        }

        $message = get_string('sessionsgenerated', 'hybridteaching', $okcount);
        if ($okcount < 1) {
            notify_controller::notify_message($message);
        } else {
            notify_controller::notify_success($message);
        }
    }

    /**
     * Check if an identical session exists.
     *
     * @param stdClass $session
     * @param int $attid
     * @return boolean
     */
    private function session_exists(stdClass $session, $attid) {
        global $DB;

        $check = [
            'groupid' => $session->groupid,
            'name' => $session->name,
            'starttime' => $session->starttime,
            'duration' => $session->duration,
        ];
        if ($DB->record_exists('hybridteaching_session', $check)) {
            return true;
        }
        return false;
    }
}
