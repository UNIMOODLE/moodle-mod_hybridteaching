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

    public function __construct($data) {
        global $DB;

        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        $this->context = $data->context;
        if ($data->group === "0") {
            $this->group = get_string('allgroups', 'hybridteaching');
        } else {
            $this->group = $DB->get_field('groups', 'name', ['id' => $data->group]);
        }
        $this->course = $data->coursename;

        $this->table = new stdClass();
        $this->table->headers = $this->get_headers($data);
        $this->table->body = $this->get_body();
    }

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
        /*foreach ($sessions as $session) {
            $headers[] = $session->dategroup;
            $this->sessions[] = $session->id;
        }*/

        return $headers;
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
     * @return void
     */
    public function export($filename) {
        global $DB;

        $filename = "export.txt";

        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

        echo get_string('course').";".$this->course."\n";
        echo get_string('group').";".$this->group."\n\n";

        echo implode(";", $this->table->headers)."\n";
        foreach ($this->table->body as $row) {
            /*$line = implode(";", get_object_vars($row));
            echo $line."\n";*/
        }
        die;
    }

    public function get_headers_dates($data) {
        $sessions = [];
        if (isset($data->includeallsessions)) {
            $sessions = $this->get_sessions_dates(['group' => $data->group]);
        } else {
            $sessions = $this->get_sessions_dates(['group' => $data->group,
                'sessionstartdate' => $data->sessionstartdate,
                'sessionenddate' => $data->sessionenddate]);
        }

        return $sessions;
    }

    public function get_sessions_dates($params) {
        global $DB;

        $where = '';
        if ($params['group'] == 0) {
            unset($params['group']);
        } else {
            $where .= " AND groupid = :group";
        }

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

    public function get_body() {
        global $DB;

        $body = [];
        $inparams = [];
        $insql = '';
        if (!empty($this->sessions)) {
            [$insql, $inparams] = $DB->get_in_or_equal($this->sessions);
        }

        $allgroups = get_string('allgroups', 'hybridteaching');

        $sql = "SELECT ha.id,
                        u.firstname,
                        u.lastname,
                        IF(hs.groupid = 0, '$allgroups',
                         IF (g.id IS NOT NULL, CONCAT(from_unixtime(hs.starttime), ' - ', 'g.name'), '-')) AS dategroup,
                        u.email,
                        sums.totalatt,
                        sums.totalsessions,
                        sums.porcentaje
                  FROM {hybridteaching_attendance} ha
            INNER JOIN {hybridteaching_session} hs ON hs.id = ha.sessionid
            INNER JOIN {user} u ON u.id = ha.userid
             LEFT JOIN {groups} g ON g.id = hs.groupid
            INNER JOIN (
                    SELECT ha.userid,
                            SUM(IF(ha.status = 1 OR ha.status = 2, 1, 0)) as totalatt,
                            (SUM(IF(ha.status = 1 OR ha.status = 2, 1, 0)) / COUNT(*)) * 100 AS porcentaje,
                            COUNT(*) as totalsessions
                      FROM {hybridteaching_attendance} ha
                    -- WHERE ha.sessionid $insql
                  GROUP BY ha.userid
                ) AS sums ON ha.userid = sums.userid
                WHERE 1 = 1
                GROUP BY ha.userid;";

        $body = $DB->get_records_sql($sql, $inparams);

        return $body;
    }
}
