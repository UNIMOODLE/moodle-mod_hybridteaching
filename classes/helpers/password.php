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
 * The password and qr helper.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hybridteaching\helpers;
require_once(dirname(__FILE__).'../../../../../config.php');
require_once($CFG->libdir.'/tcpdf/tcpdf_barcodes_2d.php'); // Used for generating qrcode.
use \TCPDF2DBarcode;
use \html_writer;
class password {

    /**
     * Render the session QR code.
     *
     * @param stdClass $session
     */
    public static function hybridteaching_renderqrcode($session) {
        global $CFG;
        $cm = get_coursemodule_from_instance('hybridteaching', $session->id);
        if (strlen($session->studentpassword) > 0) {
            $qrcodeurl = $CFG->wwwroot . '/mod/hybridteaching/passwordaccess.php?qrpass=' .
                $session->studentpassword . '&id=' . $session->id . '&secretqr=' . $session->rotateqrsecret;;
        } else {
            $qrcodeurl = $CFG->wwwroot . '/mod/hybridteaching/passwordaccess.php?id=' . $session->id .
             '&secretqr=' . $session->rotateqrsecret;
        }
        $barcode = new TCPDF2DBarcode($qrcodeurl, 'QRCODE');
        $image = $barcode->getBarcodePngData(12, 12);
        echo html_writer::img('data:image/png;base64,' . base64_encode($image), get_string('qrcode', 'hybridteaching'));
        echo '<br>' . ($qrcodeurl);
    }
    
    /**
     * Generate QR code passwords.
     *
     * @param stdClass $session
     */
    public static function hybridteaching_generate_passwords($session) {
        global $DB;
        $password = array();
    
        for ($i = 0; $i < 30; $i++) {
            array_push($password, array("attendanceid" => $session->id,
                "password" => random_string(), "expirytime" => time() + (15 * $i)));
        }
    
        $DB->insert_records('hybridteaching_session_pwd', $password);
    }
    
    /**
     * Render JS for rotate QR code passwords.
     *
     * @param stdClass $session
     */
    public static function hybridteaching_renderqrcoderotate($session) {
        // Load required js.
        $cm = get_coursemodule_from_instance('hybridteaching', $session->id);
        echo html_writer::tag('script', '',
            [
                'src' => 'js/qrcode/qrcode.min.js',
                'type' => 'text/javascript'
            ]
        );
        echo html_writer::tag('script', '',
            [
                'src' => 'js/password/rotateQR.js',
                'type' => 'text/javascript'
            ]
        );
        echo html_writer::div('', '', ['id' => 'qrcode']); // Div to display qr code.
        echo html_writer::div(get_string('qrcodevalidbefore', 'hybridteaching').' '.
                              html_writer::span('0', '', ['id' => 'rotate-time']).' '
                              .get_string('qrcodevalidafter', 'hybridteaching'), 'qrcodevalid'); // Div to display timer.
        // Js to start the password manager.
        echo '
        <script type="text/javascript">
            let qrCodeRotate = new rotateQR();
            qrCodeRotate.start(' . $session->id . ', document.getElementById("qrcode"), document.getElementById("rotate-time"));
            var cmid = ' . $cm->id . ';
        </script>';
        
    }
    
    /**
     * Return QR code passwords.
     *
     * @param stdClass $session
     */
    public static function hybridteaching_return_passwords($session) {
        global $DB;
    
        $sql = 'SELECT * FROM {hybridteaching_session_pwd} WHERE attendanceid = ? AND expirytime > ? ORDER BY expirytime ASC';
        return json_encode($DB->get_records_sql($sql, ['attendanceid' => $session->id, time()], $strictness = IGNORE_MISSING));
    }
}
