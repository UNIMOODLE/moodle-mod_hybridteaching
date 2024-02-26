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

use cache;
use cache_store;
use context;
use context_course;
use mod_hybridteaching\hybridteaching_proxy;
use stdClass;

/**
 * Class roles.
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
     * @param context $context Context of the activity
     * @param null|stdClass $hybridteaching Hybridteaching activity
     * @return array $data
     */
    public static function get_participant_data(context $context, ?stdClass $hybridteaching = null) {
        $data = [
            'all' => [
                'name' => get_string('mod_form_field_participant_list_type_all', 'hybridteaching'),
                'children' => [],
            ],
        ];
        $data['role'] = [
            'name' => get_string('mod_form_field_participant_list_type_role', 'hybridteaching'),
            'children' => self::get_roles_select($context, true),
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
     * @param stdClass|null $hybridteaching Hybridteaching activity
     * @param context $context Context of the activity
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
                        'role' => self::ROLE_MODERATOR, ];
                }
                continue;
            }
            $participantlist[] = [
                'selectiontype' => 'role',
                'selectionid' => $defaultrule,
                'role' => self::ROLE_MODERATOR, ];
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
            $params = [];
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

    /**
     * Evaluate if a user in a context is moderator based on roles and participation rules.
     *
     * @param context $context Context of the activity
     * @param array $participantlist Participant list
     * @param int $userid ID of the user
     *
     * @return bool
     */
    public static function is_moderator(context $context, array $participantlist, ?int $userid = null): bool {
        global $USER;
        // If an admin, then also a moderator.
        if (has_capability('moodle/site:config', $context)) {
            return true;
        }
        if (!is_array($participantlist)) {
            return false;
        }
        if (empty($userid)) {
            $userid = $USER->id;
        }
        $userroles = self::get_guest_role();
        if (!isguestuser()) {
            $userroles = self::get_user_roles($context, $userid);
        }
        return self::is_moderator_validator($participantlist, $userid, $userroles);
    }

    /**
     * Iterates participant list rules to evaluate if a user is moderator.
     *
     * @param array $participantlist
     * @param int $userid
     * @param array $userroles
     *
     * @return bool
     */
    protected static function is_moderator_validator(array $participantlist, int $userid, array $userroles): bool {
        // Iterate participant rules.
        foreach ($participantlist as $participant) {
            if (self::is_moderator_validate_rule($participant, $userid, $userroles)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Evaluate if a user is moderator based on roles and a particular participation rule.
     *
     * @param array $participant
     * @param int $userid
     * @param array $userroles
     *
     * @return bool
     */
    protected static function is_moderator_validate_rule(array $participant, int $userid, array $userroles): bool {
        if ($participant['role'] == self::ROLE_VIEWER) {
            return false;
        }
        // Validation for the 'all' rule.
        if ($participant['selectiontype'] == 'all') {
            return true;
        }
        // Validation for a 'user' rule.
        if ($participant['selectiontype'] == 'user') {
            if ($participant['selectionid'] == $userid) {
                return true;
            }
            return false;
        }
        // Validation for a 'role' rule.
        $role = self::get_role($participant['selectionid']);
        if ($role != null && array_key_exists($role->id, $userroles)) {
            return true;
        }
        return false;
    }


     /**
      * Get array with the participants, filter by moderator or viewer
      *
      * @param object $ht
      * @param int $groupsessionid
      * @param int $role
      *
      * @return array $users
      */
    public static function getparticipants($ht, $groupsessionid, $role = self::ROLE_VIEWER) {
        global $DB;
        $participants = json_decode($ht->participants);
        $users = [];
        $context = context_course::instance($ht->course);

        $course = get_course($context->instanceid);
        $groupmode = groups_get_course_groupmode($course);

        if ($ht) {
            list($htcourse, $cm) = get_course_and_cm_from_instance($ht->id, 'hybridteaching');
            $groupmode = groups_get_activity_groupmode($cm);
        }

        foreach ($participants as $participant) {

            // Check group mode.
            $groupid = 0;

            // Check by selectiontype.
            switch($participant->selectiontype){
                case 'user':
                    if ($participant->role == $role) {
                        if (is_numeric($participant->selectionid)) {
                            // No checking group mode if is 'user', cause the user or student may have been set manually.
                            $usermoodle = $DB->get_record('user', ['id' => $participant->selectionid],
                                'id, username, firstname, lastname, email');
                            $users[$usermoodle->id] = $usermoodle;
                        }
                    }
                    break;
                case 'role':
                    if ($participant->role == $role) {
                        if ($groupmode == SEPARATEGROUPS && $participant->role == self::ROLE_VIEWER) {
                            $groupid = $groupsessionid;
                        }
                        $usersrole = get_role_users($participant->selectionid, $context, false,
                            'u.id, u.username, u.firstname, u.lastname, u.email', 'u.username', false, $groupid);
                        foreach ($usersrole as $u) {
                            $users[$u->id] = $u;
                        }
                    }
                    break;
                case 'all':
                    if ($participant->role == $role) {
                        if ($groupmode == SEPARATEGROUPS) {
                            // Get users with access all groups.
                            $userswithcap = get_enrolled_users($context, 'moodle/site:accessallgroups', 0,
                                'u.id, u.username, u.firstname, u.lastname, u.email', 'u.username', null, 0, 0, true);
                            // Get users from groupsession.
                            $usersallother = get_enrolled_users($context, '', $groupsessionid,
                                'u.id, u.username, u.firstname, u.lastname, u.email', 'u.username', null, 0, 0, true);
                            $usersall = array_merge ($userswithcap, $usersallother);
                        } else {
                            $usersall = get_enrolled_users($context, '', 0,
                                'u.id, u.username, u.firstname, u.lastname, u.email', 'u.username', null, 0, 0, true);
                        }

                        foreach ($usersall as $u) {
                                $users[$u->id] = $u;
                        }
                    }
            }
        }

        return $users;
    }

     /**
      * Unset moderators users that exists in participants list.
      *
      * @param array $moderators
      * @param array $participants
      *
      * @return array $participants
      */
    public static function uniqueusers ($moderators, $participants) {
        foreach ($moderators as $moderator) {
            if (array_key_exists ($moderator->id, $participants)) {
                unset($participants[$moderator->id]);
            }
        }
        return $participants;
    }
}
