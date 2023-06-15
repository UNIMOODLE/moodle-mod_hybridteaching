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
 * Display information about all the mod_hybridteaching modules in the requested course.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require('../../../../config.php');
require_once('../controller/sessions_controller.php');

$action = required_param('action', PARAM_ALPHANUMEXT);
$moduleid = optional_param('id', 0, PARAM_INT);
$sesionid = optional_param('sid', 0, PARAM_INT);
$hybridteachingid = required_param('h', PARAM_INT);
$return = optional_param('returnurl', '', PARAM_URL);

$PAGE->set_url('/mod/hybridteaching/classes/action/session_action.php');
$PAGE->set_context(context_system::instance());

require_admin();
require_sesskey();

$hybridteaching = $DB->get_record('hybridteaching', array('id' => $hybridteachingid), '*', MUST_EXIST);
$sessioncontroller = new sessions_controller($hybridteaching, 'hybridteaching_session');
$sessionslist = $sessioncontroller->load_sessions();

if (!array_key_exists($sesionid, $sessionslist)) {
    redirect($return);
}

switch ($action) {
    case 'disable':
        $sessioncontroller->enable_data($sesionid, false);
        break;
    case 'enable':
        $sessioncontroller->enable_data($sesionid, true);
        break;
    case 'delete':
        $sessioncontroller->delete_session($sesionid, $hybridteachingid);
        break;
}

redirect($return);
