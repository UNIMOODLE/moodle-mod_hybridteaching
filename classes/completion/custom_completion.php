<?php 

declare(strict_types=1);

namespace mod_hybridteaching\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for the hybridteaching activity.
 *
 * Class for defining mod_hybridteaching's custom completion rules and fetching the completion statuses
 * of the custom completion rules for a given hybridteaching instance and a user.
 *
 * @package mod_hybridteaching
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $hybridteachingid = $this->cm->instance;

        if (!$hybridteaching = $DB->get_record('hybridteaching', ['id' => $hybridteachingid])) {
            throw new \moodle_exception('Unable to find hybridteaching with id ' . $hybridteachingid);
        }
        $status=0;

// TO-DO : HACER AQUI LA REGLA PARA CONTAR QUE EL USUARIO HA ASISTIDO A XX SESIONES        
        /*
        $postcountparams = ['userid' => $userid, 'hybridteachingid' => $hybridteachingid];
        $postcountsql = "SELECT COUNT(*)
                           FROM {forum_posts} fp
                           JOIN {forum_discussions} fd ON fp.discussion = fd.id
                          WHERE fp.userid = :userid
                            AND fd.forum = :forumid";

        if ($rule == 'completiondiscussions') {
            $status = $forum->completiondiscussions <=
                $DB->count_records('forum_discussions', ['forum' => $forumid, 'userid' => $userid]);
        } else if ($rule == 'completionreplies') {
            $status = $forum->completionreplies <=
                $DB->get_field_sql($postcountsql . ' AND fp.parent <> 0', $postcountparams);
        } else if ($rule == 'completionposts') {
            $status = $forum->completionposts <= $DB->get_field_sql($postcountsql, $postcountparams);
        }*/

        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [
            'completionattendance',
        ];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $completionattendance = $this->cm->customdata['customcompletionrules']['completionattendance'] ?? 0;
        
        return [
            'completionattendance' => get_string('completiondetail:attendance', 'hybridteaching', $completionattendance),
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionattendance',
            'completionusegrade',
            'completionpassgrade',
        ];
    }


}
