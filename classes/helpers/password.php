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

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'../../../../../config.php');
require_login();
require_once($CFG->libdir.'/tcpdf/tcpdf_barcodes_2d.php'); // Used for generating qrcode.
use TCPDF2DBarcode;
use html_writer;

/**
 * Class password.
 */
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
                $session->studentpassword . '&id=' . $session->id . '&secretqr=' . $session->rotateqrsecret . '&attaction=1';
        } else {
            $qrcodeurl = $CFG->wwwroot . '/mod/hybridteaching/passwordaccess.php?id=' . $session->id .
             '&secretqr=' . $session->rotateqrsecret . '&attaction=1';
        }
        $barcode = new TCPDF2DBarcode($qrcodeurl, 'QRCODE');
        $image = $barcode->getBarcodePngData(12, 12);
        echo html_writer::img('data:image/png;base64,' . base64_encode($image), get_string('qrcode', 'hybridteaching'));
    }

    /**
     * Generate QR code passwords.
     *
     * @param stdClass $session
     */
    public static function hybridteaching_generate_passwords($session) {
        global $DB;
        $password = [];
        $qrupdatetime = get_config('hybridteaching', 'qrupdatetime');
        !$qrupdatetime ? $qrupdatetime = 15 : '';
        for ($i = 0; $i < 30; $i++) {
            array_push($password, ["attendanceid" => $session->id,
                "password" => random_string(), "expirytime" => time() + ($qrupdatetime * $i), ]);
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
                'type' => 'text/javascript',
            ]
        );
        echo html_writer::tag('script', '',
            [
                'src' => 'js/password/rotateQR.js',
                'type' => 'text/javascript',
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

    /**
     * Return QR code passwords.
     *
     * @param stdClass $session
     */
    public static function hybridteaching_set_password($session, $password) {
        global $DB;

        $session->studentpassword = $password;
        $DB->update_record('hybridteaching', $session);
    }
}
