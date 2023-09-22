<?php 

require_once(dirname(__FILE__).'../../../../../config.php');
require_once($CFG->libdir . '/gradelib.php');

class grades {
    const ATTENDED_SESSIONS = 1;
    const PERCENTAJE_ATTENDED_SESSIONS = 2;
    const PERCENTAJE_TOTAL_TIME_ATTENDED_SESSIONS = 3;

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

    public function has_taken_attendance($htid, $userid, $sessid = null) {
        global $DB;
        $params = array('userid' => $userid, 'hybridteachingid' => $htid);
        !empty($sessid) ? $params['sessionid'] = $sessid : null;
        return $DB->record_exists('hybridteaching_attendance', $params);
    }

    public function get_instance_grade_for($ht, $userid) {
        switch ($ht->maxgradeattendanceunit) {
            case self::ATTENDED_SESSIONS:
                return $this->calc_grade_by_attended_sess($ht, $userid);
                break;
            case self::PERCENTAJE_ATTENDED_SESSIONS:
                return $this->calc_grade_by_attended_sess_per($ht, $userid);
                break;
            case self::PERCENTAJE_TOTAL_TIME_ATTENDED_SESSIONS:
                return $this->calc_grade_by_attended_sess_time_per($ht, $userid);
                break;
        }
    }

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
    
    public function calc_grade_by_attended_sess_per($ht, $userid) {
        global $DB;
        
        $usergrade = 0;
        $maxgradeper = $ht->maxgradeattendance / 100;
        $attendancetomaxgrade = round($DB->count_records('hybridteaching_session', 
            array('hybridteachingid' => $ht->id, 'attexempt' => 0)) * $maxgradeper); 

        $userattendance = $this->count_user_att($ht, $userid);

        if ($userattendance->totalattended >= $attendancetomaxgrade) {
            $usergrade = $ht->grade;
        } else {
            $usergrade = ($userattendance->totalattended / $attendancetomaxgrade) * $ht->grade;
        }

        return $usergrade;
    }
    
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

        foreach ($userids as $userid) {
            if ($this->has_taken_attendance($ht->id, $userid, $sessid) && !$this->is_session_exempt($sessid)) {
                if ($att = $this->has_valid_attendance($ht->id, $sessid, $userid)) {
                    $att->grade = $this->calc_att_grade_for($ht, $sessid, $att->id);
                    $DB->update_record('hybridteaching_attendance', $att);
                }
            }
        }
    }

    public function has_valid_attendance($htid, $sessid, $userid) {
        global $DB;
        return $DB->get_record('hybridteaching_attendance', array(
            'hybridteachingid' => $htid,
            'sessionid' => $sessid,
            'userid' => $userid
        ));
    }

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

    public function is_session_exempt($sessid) {
        global $DB;
        return $DB->record_exists('hybridteaching_session', array('id' => $sessid, 'attexempt' => 1));
    }
}