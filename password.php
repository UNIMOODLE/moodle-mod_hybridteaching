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

/**
 * Displays help via AJAX call or in a new page
 *
 * Use {@see core_renderer::help_icon()} or {@see addHelpButton()} to display
 * the help icon.
 */

use mod_hybridteaching\helpers\password;

require_once(dirname(__FILE__).'/../../config.php');

$session = required_param('instance', PARAM_INT);
$session = $DB->get_record('hybridteaching', array('id' => $session), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('hybridteaching', $session->id);
require_login($cm->course, $cm);

$context = context_module::instance($cm->id);

if (optional_param('returnpasswords', 0, PARAM_INT) == 1) {
    header('Content-Type: application/json');
    echo password::hybridteaching_return_passwords($session);
    exit;
}
$showpassword = (isset($session->studentpassword) && strlen($session->studentpassword) > 0);
$showqr = (isset($session->useqr) && $session->useqr == 1);
$rotateqr = (isset($session->rotateqr) && $session->rotateqr == 1);
if(!$showpassword && !$showqr && !$rotateqr) {
    $return = new moodle_url('view.php', array('id' => $cm->id));
    redirect($return ,get_string('noqrpassworduse', 'hybridteaching'), null, \core\output\notification::NOTIFY_ERROR);
}
$PAGE->set_url('/mod/hybridteaching/password.php');
$PAGE->set_pagelayout('popup');

$PAGE->set_context(context_system::instance());

$PAGE->set_title(get_string('studentpassword', 'hybridteaching'));
echo $OUTPUT->header();

if ($rotateqr) {
    $showpassword = false;
}

if ($showpassword) {
    if ($showqr) {
        echo html_writer::div("<h2>".get_string('qrcodeandpasswordheader', 'hybridteaching'), 'qrcodeheader')."</h2>";
    } else {
        echo html_writer::div("<h2>".get_string('passwordheader', 'hybridteaching'), 'qrcodeheader')."</h2>";
    }
    echo html_writer::div("<h4>".$session->studentpassword,'student-password')."</h4>";
    echo html_writer::div('&nbsp;');
} else if ($showqr && !$rotateqr) {
    echo html_writer::div("<h2>".get_string('qrcodeheader', 'hybridteaching'), 'qrcodeheader')."</h2>";
}
if ($rotateqr) {
    echo html_writer::div("<h2>".get_string('qrcodeheader', 'hybridteaching'), 'qrcodeheader')."</h2>";
    password::hybridteaching_generate_passwords($session);
    password::hybridteaching_renderqrcoderotate($session);
} else if ($showqr) {
    password::hybridteaching_renderqrcode($session);
}
echo $OUTPUT->footer();