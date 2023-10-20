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

/**
 * The main mod_hybridteaching configuration form.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com> oc: bbb
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\helpers;

use cache;
use cache_store;
use context;
use context_course;
use mod_hybridteaching\hybridteaching_proxy;
use stdClass;

/**
 * Class used for roles asignee management
 *
 * @package    mod_hybridteaching
 * @copyright  2023 isyc <isyc@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class roles {

    /** @var int The hybridteaching viewer role */
    public const ROLE_VIEWER = 'viewer';

    /** @var string The hybridteaching moderator role */
    public const ROLE_MODERATOR = 'moderator';

    /**
     * Returns user roles in a context.
     *
     * @param context $context
     * @param int $userid
     *
     * @return array $userroles
     */
    public static function get_user_roles(context $context, int $userid): array {
        global $DB;
        $userroles = get_user_roles($context, $userid);
        if ($userroles) {
            $where = '';
            foreach ($userroles as $userrole) {
                $where .= (empty($where) ? ' WHERE' : ' OR') . ' id=' . $userrole->roleid;
            }
            $userroles = $DB->get_records_sql('SELECT * FROM {role}' . $where);
        }
        return $userroles;
    }

    /**
     * Returns guest role wrapped in an array.
     *
     * @return array
     */
    protected static function get_guest_role() {
        $guestrole = get_guest_role();
        return [$guestrole->id => $guestrole];
    }

    /**
     * Returns an array containing all the users in a context wrapped for html select element.
     *
     * @param context_course $context
     * @param null $hybridteaching
     * @return array $users
     */
    public static function get_users_array(context_course $context, $hybridteaching = null) {
        // CONTRIB-7972, check the group of current user and course group mode.
        $groups = null;
        $users = (array) get_enrolled_users($context, '', 0, 'u.*', null, 0, 0, true);
        $course = get_course($context->instanceid);
        $groupmode = groups_get_course_groupmode($course);
        if ($hybridteaching) {
            list($bbcourse, $cm) = get_course_and_cm_from_instance($hybridteaching->id, 'hybridteaching');
            $groupmode = groups_get_activity_groupmode($cm);

        }
        if ($groupmode == SEPARATEGROUPS && !has_capability('moodle/site:accessallgroups', $context)) {
            global $USER;
            $groups = groups_get_all_groups($course->id, $USER->id);
            $users = [];
            foreach ($groups as $g) {
                $users += (array) get_enrolled_users($context, '', $g->id, 'u.*', null, 0, 0, true);
            }
        }
        return array_map(
            function($u) {
                return ['id' => $u->id, 'name' => fullname($u)];
            },
            $users);
    }
    /**
     * Returns an array containing all the roles in a context.
     *
     * @param context|null $context $context
     * @param bool|null $onlyviewableroles
     *
     * @return array $roles
     */
    public static function get_roles(?context $context = null, ?bool $onlyviewableroles = true) {
        global $CFG;

        if ($onlyviewableroles == true && $CFG->branch >= 35) {
            $roles = (array) get_viewable_roles($context);
            foreach ($roles as $key => $value) {
                $roles[$key] = $value;
            }
        } else {
            $roles = (array) role_get_names($context);
            foreach ($roles as $key => $value) {
                $roles[$key] = $value->localname;
            }
        }

        return $roles;
    }

    /**
     * Returns an array containing all the roles in a context wrapped for html select element.
     *
     * @param context|null $context $context
     * @param bool $onlyviewableroles
     *
     * @return array $users
     */
    protected static function get_roles_select(context $context = null, bool $onlyviewableroles = true) {
        global $CFG;

        if ($onlyviewableroles == true && $CFG->branch >= 35) {
            $roles = (array) get_viewable_roles($context);
            foreach ($roles as $key => $value) {
                $roles[$key] = ['id' => $key, 'name' => $value];
            }
        } else {
            $roles = (array) role_get_names($context);
            foreach ($roles as $key => $value) {
                $roles[$key] = ['id' => $value->id, 'name' => $value->localname];
            }
        }

        return $roles;
    }

    /**
     * Returns role that corresponds to an id.
     *
     * @param string|integer $id
     *
     * @return stdClass|null $role
     */
    protected static function get_role($id): ?stdClass {
        $roles = (array) role_get_names();
        if (is_numeric($id) && isset($roles[$id])) {
            return (object) $roles[$id];
        }
        foreach ($roles as $role) {
            if ($role->shortname == $id) {
                return $role;
            }
        }
        return null;
    }

    /**
     * Returns an array to populate a list of participants used in mod_form.js.
     *
     * @param context $context
     * @param null|stdClass $hybridactivity
     * @return array $data
     */
    public static function get_participant_data(context $context, ?stdClass $hybridteaching = null) {
        $data = [
            'all' => [
                'name' => get_string('mod_form_field_participant_list_type_all', 'hybridteaching'),
                'children' => []
            ],
        ];
        $data['role'] = [
            'name' => get_string('mod_form_field_participant_list_type_role', 'hybridteaching'),
            'children' => self::get_roles_select($context, true)
        ];
        $data['user'] = [
            'name' => get_string('mod_form_field_participant_list_type_user', 'hybridteaching'),
            'children' => self::get_users_array($context, $hybridteaching),
        ];
        return $data;
    }

    /**
     * Returns an array to populate a list of participants used in mod_form.php.
     *
     * @param stdClass|null $hybridteaching
     * @param context $context
     *
     * @return array
     */
    public static function get_participant_list(?stdClass $hybridteaching, context $context): array {
        global $USER;

        if ($hybridteaching == null) {
            return self::get_participant_rules_encoded(
                self::get_participant_list_default($context, $USER->id)
            );
        }
        if (empty($hybridteaching->participants)) {
            $hybridteaching->participants = "[]";
        }
        $rules = json_decode($hybridteaching->participants, true);
        if (empty($rules)) {
            $rules = self::get_participant_list_default($context,
                hybridteaching_proxy::get_instance_ownerid($hybridteaching));
        }
        return self::get_participant_rules_encoded($rules);
    }

    /**
     * Returns an array to populate a list of participants used in mod_form.php with default values.
     *
     * @param context $context
     * @param int|null $ownerid
     *
     * @return array
     */
    protected static function get_participant_list_default(context $context, ?int $ownerid = null) {
        $participantlist = [];
        $participantlist[] = [
            'selectiontype' => 'all',
            'selectionid' => 'all',
            'role' => self::ROLE_VIEWER,
        ];
        $defaultrules = explode(',', 0);
        foreach ($defaultrules as $defaultrule) {
            if ($defaultrule == '0') {
                if (!empty($ownerid) && is_enrolled($context, $ownerid)) {
                    $participantlist[] = [
                        'selectiontype' => 'user',
                        'selectionid' => (string) $ownerid,
                        'role' => self::ROLE_MODERATOR];
                }
                continue;
            }
            $participantlist[] = [
                'selectiontype' => 'role',
                'selectionid' => $defaultrule,
                'role' => self::ROLE_MODERATOR];
        }
        return $participantlist;
    }

    /**
     * Returns an array to populate a list of participants used in mod_form.php with hybri$hybridteaching values.
     *
     * @param array $rules
     *
     * @return array
     */
    protected static function get_participant_rules_encoded(array $rules): array {
        foreach ($rules as $key => $rule) {
            if ($rule['selectiontype'] !== 'role' || is_numeric($rule['selectionid'])) {
                continue;
            }
            $role = self::get_role($rule['selectionid']);
            if ($role == null) {
                unset($rules[$key]);
                continue;
            }
            $rule['selectionid'] = $role->id;
            $rules[$key] = $rule;
        }
        return $rules;
    }

    /**
     * Returns an array to populate a list of participant_selection used in mod_form.php.
     *
     * @return array
     */
    public static function get_participant_selection_data(): array {
        return [
            'type_options' => [
                'all' => get_string('mod_form_field_participant_list_type_all', 'hybridteaching'),
                'role' => get_string('mod_form_field_participant_list_type_role', 'hybridteaching'),
                'user' => get_string('mod_form_field_participant_list_type_user', 'hybridteaching'),
            ],
            'type_selected' => 'all',
            'options' => ['all' => '---------------'],
            'selected' => 'all',
        ];
    }


    /**
     * Count the number of users with a specific role in a given context.
     *
     * @param int $roleid The ID of the role to count users for.
     * @param context $context The context in which to count users.
     * @param bool $parent (optional) Whether to include parent contexts in the count. Default is false.
     * @param int|null $group (optional) The ID of the group to filter users by. Default is null.
     * @return int The number of users with the specified role in the given context.
     */
    public static function count_role_users($roleid, context $context, $parent = false, $group = null) {
        global $DB;

        if ($parent) {
            if ($contexts = $context->get_parent_context_ids()) {
                $parentcontexts = ' OR r.contextid IN ('.implode(',', $contexts).')';
            } else {
                $parentcontexts = '';
            }
        } else {
            $parentcontexts = '';
        }

        if ($roleid) {
            list($rids, $params) = $DB->get_in_or_equal($roleid, SQL_PARAMS_QM);
            $roleselect = "AND r.roleid $rids";
        } else {
            $params = array();
            $roleselect = '';
        }

        if ($group) {
            $groupjoin   = "JOIN {groups_members} gm ON gm.userid = u.id";
            $groupselect = " AND gm.groupid = ? ";
            $params['groupid'] = $group;
        } else {
            $groupjoin   = '';
            $groupselect = '';
        }

        array_unshift($params, $context->id);

        $sql = "SELECT COUNT(DISTINCT u.id)
                  FROM {role_assignments} r
                  JOIN {user} u ON u.id = r.userid
                  $groupjoin
                 WHERE (r.contextid = ? $parentcontexts)
                       $roleselect
                       $groupselect
                       AND u.deleted = 0";

        return $DB->count_records_sql($sql, $params);
    }
}
