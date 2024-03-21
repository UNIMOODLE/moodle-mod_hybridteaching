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
 * Display information about all the mod_hybridteaching modules in the requested course. *
 * @package    mod_hybridteaching
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\helpers;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');

/**
 * Class export.
 */
class export {
    /** @var string $error The errors message from reading the xml */
    protected $error = '';

    /** @var array $sessions The sessions info */
    protected $sessions = [];

    /** @var stdClass|null  $table */
    protected $table = null;

    /** @var object $course The course object */
    protected $course = null;

    /** @var string $group The group name */
    protected $group = null;

    /** @var object $context The context object */
    protected $context = null;

    /** @var object $hybridteaching The hybridteaching object */
    protected $hybridteaching = null;

    /**
     * Constructs a new instance of the class.
     *
     * @param object $data The data used to initialize the object.
     */
    public function __construct($data) {
        global $DB;

        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $this->context = $data->context;
        $this->hybridteaching = $data->hybridteaching;
        if ($data->group === "0") {
            $this->group = get_string('allparticipants');
        } else {
            $this->group = $DB->get_field('groups', 'name', ['id' => $data->group]);
        }
        $this->course = $data->coursename;

        $this->table = new stdClass();
        $this->table->headers = $this->get_headers($data);
        $this->table->body = $this->get_body($data);
    }

    /**
     * Retrieves the headers for the given data.
     *
     * @param mixed $data The data to retrieve headers from.
     * @return array The headers retrieved from the data.
     */
    public function get_headers($data) {
        $headers = [];

        $headers[] = get_string('firstname');
        $headers[] = get_string('lastname');
        $headers[] = get_string('groups');
        $headers[] = get_string('email');
        $headers[] = get_string('takensessions', 'hybridteaching');
        $headers[] = get_string('selectedsessions', 'hybridteaching');
        $headers[] = get_string('percentage', 'hybridteaching');
        $sessions = $this->get_headers_dates($data);
        foreach ($sessions as $session) {
            $timestamp = explode(" - ", $session->dategroup)[0];
            $formatteddate = date("Y-m-d H:i", $timestamp);
            $newdategroup = str_replace($timestamp, $formatteddate, $session->dategroup);
            $headers[] = $newdategroup;
            $this->sessions[] = $session->id;
        }

        return $headers;
    }

    /**
     * Retrieves the headers and dates of sessions.
     *
     * @param mixed $data The data used to retrieve the sessions.
     *                    If the 'includeallsessions' parameter is set, all sessions will be retrieved.
     *                    Otherwise, the sessions will be retrieved based on the start and end dates provided in the $data object.
     * @return array An array of sessions containing the headers and dates.
     */
    public function get_headers_dates($data) {
        $sessions = [];
        if (isset($data->includeallsessions)) {
            $sessions = $this->get_sessions_dates();
        } else {
            $sessions = $this->get_sessions_dates([
                'sessionstartdate' => $data->sessionstartdate,
                'sessionenddate' => $data->sessionenddate, ]);
        }

        return $sessions;
    }

    /**
     * Retrieves the session dates for export headers based on the provided parameters.
     *
     * @param array $params The parameters to filter the session dates.
     *                      - sessionstartdate (string): The start date of the session.
     *                      - sessionenddate (string): The end date of the session.
     * @return array The session dates.
     */
    public function get_sessions_dates($params = []) {
        global $DB;

        $where = '';
        if (!empty($params['sessionstartdate']) && !empty($params['sessionenddate'])) {
            $where .= " AND starttime between :sessionstartdate AND :sessionenddate";
        }

        if (!has_capability('mod/hybridteaching:viewhiddenitems', $this->context)) {
            $where .= " AND visible = :visible";
            $params['visible'] = 1;
        }

        $allgroups = get_string('allgroups', 'hybridteaching');

        $params['htid'] = $this->hybridteaching->id;
        $sql = "SELECT hs.id,
                           CASE
                                WHEN hs.groupid = 0 THEN CONCAT(hs.starttime, ' - All groups')
                                WHEN g.id IS NOT NULL THEN CONCAT(hs.starttime, ' - ', g.name)
                                ELSE '-'
                            END AS dategroup
                  FROM {hybridteaching_session} hs
             LEFT JOIN {groups} g ON g.id = hs.groupid
                 WHERE hs.hybridteachingid = :htid $where
              ORDER BY hs.id";

        $sessions = $DB->get_records_sql($sql, $params);

        return $sessions;
    }

    /**
     * Retrieves the body of the function based on the provided parameters.
     *
     * @param object $params The parameters used to filter the body.
     * @return array The body of the function.
     */
    public function get_body($params) {
        global $DB;

        $body = [];
        $allparams = [];
        $inparams = [];
        $insql = '';
        if (!empty($this->sessions)) {
            [$insql, $inparams] = $DB->get_in_or_equal($this->sessions, SQL_PARAMS_NAMED, 'sessions');
            $allparams = $allparams + $inparams;
        }

        $wheregroups = '';
        $groups = '';
        $selectgroups = "'-' AS groupname,";
        if ($params->group != 0) {
            $allparams = $allparams + ['group' => $params->group];
            $wheregroups .= " AND gm.groupid = :group";
            $groups = " LEFT JOIN {groups_members} gm ON ha.userid = gm.userid
                        LEFT JOIN {groups} g ON g.id = gm.groupid
                                    AND g.courseid = :courseid";
            $selectgroups = "CASE WHEN g.id IS NOT NULL THEN g.name ELSE '-' END AS groupname,";
            $allparams = $allparams + ['courseid' => $this->hybridteaching->course];
        }

        $allparams = $allparams + ['htid' => $this->hybridteaching->id];

        $sql = "SELECT
                        ha.id,
                        u.id as userid,
                        u.firstname,
                        u.lastname,
                        $selectgroups
                        u.email,
                        CONCAT(hs.id, ' - ', ha.status) AS attbysess
                  FROM {hybridteaching_attendance} ha
            INNER JOIN {hybridteaching_session} hs ON hs.id = ha.sessionid
            INNER JOIN {user} u ON u.id = ha.userid
                        $groups
                 WHERE hs.hybridteachingid = :htid AND hs.id $insql $wheregroups";

        $body = $DB->get_records_sql($sql, $allparams);

        $groupedattendance = array_reduce($body, function ($result, $value) {
            $userid = $value->userid;
            if (!isset($result[$userid])) {
                $result[$userid] = [];
            }
            $result[$userid][] = $value;
            return $result;
        }, []);

        // Concatenar attendance separados por |.
        $finalattendance = array_map(function ($records) {
            $attendance = array_map(function ($record) {
                return $record->attbysess;
            }, $records);

            $result = new stdClass();
            $result->firstname = $records[0]->firstname;
            $result->lastname = $records[0]->lastname;
            $result->groupname = $records[0]->groupname;
            $result->email = $records[0]->email;
            $result->attbysess = implode(' | ', $attendance);

            return $result;
        }, $groupedattendance);

        return $finalattendance;
    }

    /**
     * Exports the data to a file in the specified format.
     *
     * @param string $filename The name of the file to export to.
     * @param string $format The format of the exported file (text or excel/ods).
     */
    public function export($filename, $format) {
        if ($format === 'text') {
            $this->export_text($filename);
        } else {
            $this->export_excel_ods($filename, $format);
        }
    }

    /**
     * Export hybridteaching attendance as a text file.
     *
     * @param string $filename The name of the file to be downloaded.
     */
    public function export_text($filename) {
        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

        echo get_string('course').";".$this->course."\n";
        echo get_string('group').";".$this->group."\n\n";

        echo implode(";", $this->table->headers)."\n";
        foreach ($this->table->body as $row) {
            $attbysess = explode("|", $row->attbysess);
            unset($row->attbysess);

            $takensess = 0;
            foreach ($this->sessions as $session) {
                foreach ($attbysess as $att) {
                    $attparts = explode(",", $att);
                    if ($attparts[0] == $session && $attparts[1] != 0) {
                        $takensess++;
                    }
                }
            }

            $row->takensess = $takensess;
            $row->selectedsess = count($this->sessions);
            if ($row->selectedsess > 0 && $row->takensess > 0) {
                $row->percentage = round($row->takensess / $row->selectedsess * 100, 2) . '%';
            } else {
                $row->percentage = 0;
            }

            $line = implode(";", get_object_vars($row));
            foreach ($this->sessions as $session) {
                $status = HYBRIDTEACHING_ATTSTATUS_NOTVALID;
                foreach ($attbysess as $att) {
                    $attparts = explode(",", $att);
                    if ($attparts[0] == $session) {
                        $status = $attparts[1];
                        break;
                    }
                }

                $line .= ";" . $status;
            }
            echo $line."\n";
        }
        die;
    }

    /**
     * Export hybridteaching attendance to Excel or ODS format.
     *
     * @param string $filename The name of the exported file.
     * @param string $format   The format of the exported file ('excel' or 'ods').
     */
    public function export_excel_ods($filename, $format) {
        global $CFG;

        if ($format === 'excel') {
            require_once("$CFG->libdir/excellib.class.php");
            $filename .= ".xls";
            $workbook = new \MoodleExcelWorkbook("-");
        } else {
            require_once("$CFG->libdir/odslib.class.php");
            $filename .= ".ods";
            $workbook = new \MoodleODSWorkbook("-");
        }
        // Sending HTTP headers.
        $workbook->send($filename);
        // Creating the first worksheet.
        $myxls = $workbook->add_worksheet(get_string('modulenameplural', 'hybridteaching'));
        // Format types.
        $formatbc = $workbook->add_format();
        $formatbc->set_bold(1);

        $myxls->write(0, 0, get_string('course'), $formatbc);
        $myxls->write(0, 1, $this->course);
        $myxls->write(1, 0, get_string('group'), $formatbc);
        $myxls->write(1, 1, $this->group);

        $row = 3;
        $column = 0;
        foreach ($this->table->headers as $cell) {
            if (empty($cell)) {
                $myxls->merge_cells($row, $column - 1, $row, $column);
            } else {
                $myxls->write($row, $column, $cell, $formatbc);
            }
            $column++;
        }
        $row++;
        $column = 0;
        foreach ($this->table->body as $rowtable) {
            $attbysess = explode("|", $rowtable->attbysess);
            unset($rowtable->attbysess);

            $takensess = 0;
            foreach ($this->sessions as $session) {
                foreach ($attbysess as $att) {
                    $attparts = explode("-", $att);
                    if ($attparts[0] == $session && $attparts[1] != 0) {
                        $takensess++;
                    }
                }
            }

            $rowtable->takensess = $takensess;
            $rowtable->selectedsess = count($this->sessions);
            if ($rowtable->selectedsess > 0 && $rowtable->takensess > 0) {
                $rowtable->percentage = round($rowtable->takensess / $rowtable->selectedsess * 100, 2) . '%';
            } else {
                $rowtable->percentage = 0;
            }

            foreach ($this->sessions as $session) {
                $status = HYBRIDTEACHING_ATTSTATUS_NOTVALID;
                foreach ($attbysess as $att) {
                    $attparts = explode("-", $att);
                    if ($attparts[0] == $session) {
                        $status = $attparts[1];
                        break;
                    }
                }

                $rowtable->$session = $status;
            }

            foreach ($rowtable as $cell) {
                $myxls->write($row, $column++, $cell);
            }
            $row++;
            $column = 0;
        }
        $workbook->close();
    }
}
