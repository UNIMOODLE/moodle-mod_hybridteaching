<?php

namespace mod_hybridteaching\helpers;

use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/hybridteaching/classes/controller/sessions_controller.php');

class export {
    /** @var string $error The errors message from reading the xml */
    protected $error = '';

    /** @var array $sessions The sessions info */
    protected $sessions = [];

    /** @var stdClass|null  $table */
    protected $table = null;

    protected $course = null;

    protected $group = null;

    protected $context = null;

    /**
     * Constructs a new instance of the class.
     *
     * @param mixed $data The data used to initialize the object.
     */
    public function __construct($data) {
        global $DB;

        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $this->context = $data->context;
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

        $headers[] = get_string('lastname');
        $headers[] = get_string('firstname');
        $headers[] = get_string('groups');
        $headers[] = get_string('email');
        $headers[] = get_string('takensessions', 'hybridteaching');
        $headers[] = get_string('selectedsessions', 'hybridteaching');
        $headers[] = get_string('percentage', 'hybridteaching');
        $sessions = $this->get_headers_dates($data);
        foreach ($sessions as $session) {
            $headers[] = $session->dategroup;
            $this->sessions[] = $session->id;
        }

        return $headers;
    }

    public function get_headers_dates($data) {
        $sessions = [];
        if (isset($data->includeallsessions)) {
            $sessions = $this->get_sessions_dates();
        } else {
            $sessions = $this->get_sessions_dates([
                'sessionstartdate' => $data->sessionstartdate,
                'sessionenddate' => $data->sessionenddate]);
        }

        return $sessions;
    }

    /**
     * Retrieves the session dates for export headers based on the provided parameters.
     *
     * @param array $params The parameters to filter the session dates.
     *                      - sessionstartdate (string): The start date of the session.
     *                      - sessionenddate (string): The end date of the session.
     * @throws Some_Exception_Class A description of the exception thrown.
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

        $sql = "SELECT hs.id, 
                       IF(hs.groupid = 0, CONCAT(from_unixtime(hs.starttime), ' - ', '$allgroups'),
                        IF (g.id IS NOT NULL, CONCAT(from_unixtime(hs.starttime), ' - ', 'g.name'), '-')) AS dategroup
                  FROM {hybridteaching_session} hs
             LEFT JOIN {groups} g ON g.id = hs.groupid
                 WHERE 1 = 1 $where";

        $sessions = $DB->get_records_sql($sql, $params);

        return $sessions;
    }

    /**
     * Retrieves the body of the function based on the provided parameters.
     *
     * @param array $params The parameters used to filter the body.
     * @throws Exception If an error occurs during the retrieval process.
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

        $where = '';
        if ($params->group != 0) {
            $allparams = $allparams + ['group' => $params->group];
            $where .= " AND gm.groupid = :group";
        }

        $sql = "SELECT
                        u.firstname,
                        u.lastname,
                        IF (g.id IS NOT NULL, g.name, '-') AS groupname,
                        u.email,
                        GROUP_CONCAT(CONCAT(hs.id, ',', ha.status) SEPARATOR '|') AS attbysess
                  FROM {hybridteaching_attendance} ha
            INNER JOIN {hybridteaching_session} hs ON hs.id = ha.sessionid
            INNER JOIN {user} u ON u.id = ha.userid
             LEFT JOIN {groups_members} gm ON ha.userid = gm.userid
             LEFT JOIN {groups} g ON g.id = gm.groupid
                 WHERE 1 = 1 $where
              GROUP BY ha.userid;";

        $body = $DB->get_records_sql($sql, $allparams);

        return $body;
    }

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
     * @throws Some_Exception_Class Description of exception
     * @return void
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
                    $attParts = explode(",", $att);
                    if ($attParts[0] == $session && $attParts[1] != 0) {
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
                $status = 0;
                foreach ($attbysess as $att) {
                    $attParts = explode(",", $att);
                    if ($attParts[0] == $session) {
                        $status = $attParts[1];
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
     * @throws Some_Exception_Class Description of exception.
     * @return void
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
                    $attParts = explode(",", $att);
                    if ($attParts[0] == $session && $attParts[1] != 0) {
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
                $status = 0;
                foreach ($attbysess as $att) {
                    $attParts = explode(",", $att);
                    if ($attParts[0] == $session) {
                        $status = $attParts[1];
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
