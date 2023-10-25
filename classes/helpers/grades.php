<?php

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'../../../../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/mod/hybridteaching/classes/controller/attendance_controller.php');

class grades {
    const ATTENDED_SESSIONS = 1;
    const PERCENTAGE_ATTENDED_SESSIONS = 2;
    const PERCENTAGE_TOTAL_TIME_ATTENDED_SESSIONS = 3;

    /**
     * Updates the grades of users in the hybrid teaching module.
     *
     * @param int $htid The ID of the hybrid teaching module.
     * @param array $userids An array of user IDs to update the grades for. If empty, all enrolled users will be updated.
     * @throws Some_Exception_Class description of exception
     * @return bool|int Returns false if the grade or max grade attendance is empty, 
     *                  otherwise it returns true or false based on the success of grade update.
     */
    public function hybridteaching_update_users_grade($htid, $userids = array()) {
        global $DB;

        $ht = $DB->get_record('hybridteaching', array('id' => $htid));
        $cmid = get_coursemodule_from_instance('hybridteaching', $htid);
        if (empty($ht->grade) || empty($ht->maxgradeattendance)) {
            return false;
        }

        list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'hybridteaching');

        if (empty($userids)) {
            $context = context_module::instance($cm->id);
            $userids = array_keys(get_enrolled_users($context, '', 0, 'u.id'));
        }

        $grades = array();
        foreach ($userids as $userid) {
            $grades[$userid] = new stdClass();
            $grades[$userid]->userid = $userid;

            if ($this->has_taken_attendance($ht->id, $userid)) {
                $grades[$userid]->rawgrade = $this->get_instance_grade_for($ht, $userid);
            } else {
                $grades[$userid]->rawgrade = null;
            }
        }

        return grade_update('mod/hybridteaching', $course->id, 'mod', 'hybridteaching', $ht->id, 0, $grades);
    }

    /**
     * Checks if attendance has been taken for a user in a hybrid teaching session.
     *
     * @param int $htid The hybrid teaching ID.
     * @param int $userid The user ID.
     * @param int|null $sessid The session ID (optional).
     * @return bool Returns true if attendance has been taken, false otherwise.
     */
    public function has_taken_attendance($htid, $userid, $sessid = null) {
        global $DB;
        $params = array('userid' => $userid, 'hybridteachingid' => $htid);
        !empty($sessid) ? $params['sessionid'] = $sessid : null;
        return $DB->record_exists('hybridteaching_attendance', $params);
    }

    /**
     * Retrieves the instance grade for a given $ht and $userid.
     *
     * @param mixed $ht The instance of the class.
     * @param mixed $userid The user ID.
     * @return mixed The instance grade.
     */
    public function get_instance_grade_for($ht, $userid) {
        switch ($ht->maxgradeattendanceunit) {
            case self::ATTENDED_SESSIONS:
                return $this->calc_grade_by_attended_sess($ht, $userid);
                break;
            case self::PERCENTAGE_ATTENDED_SESSIONS:
                return $this->calc_grade_by_attended_sess_per($ht, $userid);
                break;
            case self::PERCENTAGE_TOTAL_TIME_ATTENDED_SESSIONS:
                return $this->calc_grade_by_attended_sess_time_per($ht, $userid);
                break;
        }
    }

    /**
     * Calculates the grade for a user based on their attendance in a session.
     *
     * @param object $ht The session object containing the maximum grade for attendance.
     * @param int $userid The ID of the user.
     * @return float The calculated grade for the user.
     */
    public function calc_grade_by_attended_sess($ht, $userid) {
        $usergrade = 0;
        $attendancetomaxgrade = $ht->maxgradeattendance;
        $userattendance = $this->count_user_att($ht, $userid);

        if ($userattendance->totalattended >= $attendancetomaxgrade) {
            $usergrade = $ht->grade;
        } else {
            $usergrade = ($userattendance->totalattended / $attendancetomaxgrade) * $ht->grade;
        }

        return $usergrade;
    }

    /**
     * Calculates the grade for a user based on the attended sessions percentage.
     *
     * @param object $ht The hybrid teaching object.
     * @param int $userid The user ID.
     * @throws Some_Exception_Class Exception description.
     * @return float The calculated user grade.
     */
    public function calc_grade_by_attended_sess_per($ht, $userid) {
        global $DB;

        $usergrade = 0;
        $maxgradeper = $ht->maxgradeattendance / 100;
        $sql = "SELECT count(ha.id)
                  FROM {hybridteaching_attendance} ha
            INNER JOIN {hybridteaching_session} hs ON ha.sessionid = hs.id
                 WHERE ha.userid = :userid
                   AND ha.hybridteachingid = :hybridteachingid
                   AND ha.status != :status
                   AND hs.attexempt = :attexempt";
        $params = ['userid' => $userid, 'hybridteachingid' => $ht->id, 'status' => 3, 'attexempt' => 0];
        $attendancetomaxgrade = round($DB->count_records_sql($sql, $params) * $maxgradeper);

        $userattendance = $this->count_user_att($ht, $userid);

        if ($userattendance->totalattended >= $attendancetomaxgrade) {
            $usergrade = $ht->grade;
        } else {
            $usergrade = ($userattendance->totalattended / $attendancetomaxgrade) * $ht->grade;
        }

        return $usergrade;
    }

    /**
     * Calculate the grade for a user based on their attended session time percentage.
     *
     * @param object $ht The hybridteaching object.
     * @param int $userid The ID of the user.
     * @throws Exception If there is an error in the database query.
     * @return int The calculated grade for the user.
     */
    public function calc_grade_by_attended_sess_time_per($ht, $userid) {
        global $DB;

        $usergrade = 0;
        $maxgradeper = $ht->maxgradeattendance / 100;

        $sql = "SELECT SUM(duration) as totalduration
                  FROM {hybridteaching_session} hs
                 WHERE hs.hybridteachingid = :hybridteachingid
                   AND hs.attexempt = :attexempt";

        $params = array(
            'hybridteachingid' => $ht->id,
            'attexempt' => 0
        );

        $attendancetomaxgrade = round($DB->get_record_sql($sql, $params)->totalduration * $maxgradeper);

        $sql = "SELECT SUM(connectiontime) as totalconnectime
                  FROM {hybridteaching_attendance} ha
            INNER JOIN {hybridteaching_session} hs ON ha.sessionid = hs.id
                 WHERE ha.userid = :userid
                   AND ha.hybridteachingid = :hybridteachingid
                   AND (ha.status = :status OR ha.status = :status2)
                   AND hs.attexempt = :attexempt";

        $params = array(
            'userid' => $userid,
            'hybridteachingid' => $ht->id,
            'status' => 1,
            'status2' => 2,
            'attexempt' => 0
        );

        $userattendancetime = $DB->get_record_sql($sql, $params);

        if ($userattendancetime->totalconnectime >= $attendancetomaxgrade) {
            $usergrade = $ht->grade;
        } else {
            $usergrade = ($userattendancetime->totalconnectime / $attendancetomaxgrade) * $ht->grade;
        }

        return $usergrade;
    }

    /**
     * Counts the number of attendance records for a given user and hybrid teaching.
     *
     * @param mixed $ht The hybrid teaching object.
     * @param int $userid The user ID.
     * @throws Exception If there is an error in the SQL query.
     * @return object The total number of attendance records.
     */
    public function count_user_att($ht, $userid) {
        global $DB;

        $sql = "SELECT COUNT(ha.id) as totalattended
                  FROM {hybridteaching_attendance} ha
            INNER JOIN {hybridteaching_session} hs ON ha.sessionid = hs.id
                 WHERE ha.userid = :userid
                   AND ha.hybridteachingid = :hybridteachingid
                   AND (ha.status = :status OR ha.status = :status2)
                   AND hs.attexempt = :attexempt";

        $params = array(
            'userid' => $userid,
            'hybridteachingid' => $ht->id,
            'status' => 1,
            'status2' => 2,
            'attexempt' => 0
        );

        return $DB->get_record_sql($sql, $params);
    }

    /**
     * Update the attendance grades for a given hybrid teaching session and users.
     *
     * @param int $htid The ID of the hybrid teaching session.
     * @param int $sessid The ID of the session.
     * @param array $userids An array of user IDs. Defaults to an empty array.
     * @throws Exception If the hybrid teaching session does not have a grade.
     * @return bool Whether the attendance grades were successfully updated.
     */
    public function ht_update_users_att_grade($htid, $sessid, $userids = array()) {
        global $DB;

        $ht = $DB->get_record('hybridteaching', array('id' => $htid));
        $cmid = get_coursemodule_from_instance('hybridteaching', $htid);
        if (empty($ht->grade)) {
            return false;
        }

        list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'hybridteaching');

        if (empty($userids)) {
            $context = context_module::instance($cm->id);
            $userids = array_keys(get_enrolled_users($context, '', 0, 'u.id'));
        }

        $attcontroller = new attendance_controller($ht);
        foreach ($userids as $userid) {
            if ($this->has_taken_attendance($ht->id, $userid, $sessid) && !$this->is_session_exempt($sessid)) {
                if ($att = $this->has_valid_attendance($ht->id, $sessid, $userid)) {
                    if ($attcontroller::hybridteaching_get_last_attend($att->id, $userid)->action == 1) {
                        $notify = $attcontroller::hybridteaching_set_attendance_log($ht, (int)$sessid, 0, $userid);
                        $notify['ntype'] == 'success' ?
                            attendance_controller::hybridteaching_set_attendance($ht, (int)$sessid, 1, null, $userid)
                        : '';
                    } else {
                        attendance_controller::hybridteaching_set_attendance($ht, (int)$sessid, 0, null, $userid);
                    }
                    $att->grade = $this->calc_att_grade_for($ht, $sessid, $att->id);
                    $DB->set_field('hybridteaching_attendance', 'grade', $att->grade, ['id' => $att->id]);
                }
            }
        }
    }

    /**
     * Checks if the attendance for a given hybrid teaching session and user is valid.
     *
     * @param int $htid The hybrid teaching ID.
     * @param int $sessid The session ID.
     * @param int $userid The user ID.
     * @global moodle_database $DB The global database object.
     * @return mixed|null Returns the attendance record if it exists, otherwise null.
     */
    public function has_valid_attendance($htid, $sessid, $userid) {
        global $DB;
        return $DB->get_record('hybridteaching_attendance', array(
            'hybridteachingid' => $htid,
            'sessionid' => $sessid,
            'userid' => $userid
        ));
    }

    /**
     * Calculates the attendance grade for a given hybrid teaching session.
     *
     * @param mixed $ht The hybrid teaching object.
     * @param int $sessid The session ID.
     * @param int $attid The attendance ID.
     * @throws Exception If unable to fetch required data from the database.
     * @return int The attendance grade for the session.
     */
    public function calc_att_grade_for($ht, $sessid, $attid) {
        global $DB;
        $usersessgrade = 0;
        $userconnectime = $DB->get_field('hybridteaching_attendance', 'connectiontime', array('id' => $attid));
        $sessduration = $DB->get_field('hybridteaching_session', 'duration', array('id' => $sessid));
        if ($userconnectime >= $sessduration) {
            $usersessgrade = $ht->grade;
        } else {
            $usersessgrade = ($userconnectime / $sessduration) * $ht->grade;
        }

        return $usersessgrade;
    }

    /**
     * Determines if a session is exempt from attendance.
     *
     * @param int $sessid The ID of the session to check.
     * @global moodle_database $DB The global database object.
     * @return bool Returns true if the session is exempt, false otherwise.
     */
    public function is_session_exempt($sessid) {
        global $DB;
        return $DB->record_exists('hybridteaching_session', array('id' => $sessid, 'attexempt' => 1));
    }
}
