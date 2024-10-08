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
 * The qr class for qr rotations using js.
 *
 * @package     mod_hybridteaching
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class rotateQR {

    constructor() {
        this.sessionId = 0;
        this.password = "";
        this.qrCodeInstance = "";
        this.qrCodeHTMLElement = "";
    }

    start(sessionId, qrCodeHTMLElement, timerHTMLElement) {
        this.sessionId = sessionId;
        this.qrCodeHTMLElement = qrCodeHTMLElement;
        this.timerHTMLElement = timerHTMLElement;
        this.fetchAndRotate();
    }

    qrCodeSetUp() {
        this.qrCodeInstance = new QRCode(this.qrCodeHTMLElement, {
            text: '',
            width: 492,
            height: 492,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    }

    changeQRCode(password) {
        var qrcodeurl = document.URL.substr(0,document.URL.lastIndexOf('/')) + '/passwordaccess.php?qrpass=' +
             password + '&id=' + this.sessionId + '&attaction=1';
        this.qrCodeInstance.clear();
        this.qrCodeInstance.makeCode(qrcodeurl);
    }

    updateTimer(timeLeft) {
        this.timerHTMLElement.innerHTML = timeLeft;
    }

    startRotating() {
        var parent = this;

        // First iteration.
        let initialpass = Object.values(parent.password).find(function(element) {
            if (element.expirytime > Math.round(new Date().getTime() / 1000)) {
                return element;
            }
        });
        let password = initialpass.password;
        let studentpassword = password.substr(Math.random(0,7), 6);
        document.getElementsByClassName('student-password')[0].children[0].textContent = studentpassword;
        fetch('password.php?instance='+parent.sessionId+'&changepassword=1&password='+studentpassword+'', {
            headers: {
                'Content-Type': 'application/json; charset=utf-8'
            }
        }).then((response) => response.json()) // Gets the data in json.
        .then(function(data) {
            console.log(data);
        }).catch(err => {
            console.log(err);
            console.error("Error fetching QR passwords from API.");
        });

        // Loop function qr update on timer. 
        setInterval(function() {
            var found = Object.values(parent.password).find(function(element) {
                if (element.expirytime > Math.round(new Date().getTime() / 1000)) {
                    if (password != element.password) {
                        password = element.password;
                        let studentpassword = password.substr(Math.random(0,7), 6);
                        document.getElementsByClassName('student-password')[0].children[0].textContent = studentpassword;
                        fetch('password.php?instance='+parent.sessionId+'&changepassword=1&password='+studentpassword+'', {
                            headers: {
                                'Content-Type': 'application/json; charset=utf-8'
                            }
                        }).then((response) => response.json()) // Gets the data in json.
                        .then(function(data) {
                            console.log(data);
                        }).catch(err => {
                            console.log(err);
                            console.error("Error fetching QR passwords from API.");
                        });
                    }
                    return element;
                }
            });
            if (found == undefined) {
                location.reload(true);
            } else {
                parent.changeQRCode(found.password);
                parent.updateTimer(found.expirytime - Math.round(new Date().getTime() / 1000));
            }
        }, 1000);
    };

    fetchAndRotate() {
        //usar fetch para query bd actualizacion contraseña estudiantes
        var parent = this;
        fetch('password.php?instance='+this.sessionId+'&returnpasswords=1', {
                headers: {
                    'Content-Type': 'application/json; charset=utf-8'
                }
            })
            .then((response) => response.json()) // Gets the data in json.
            .then(function(data) {
                parent.password = data;
                parent.qrCodeSetUp();
                parent.startRotating();
            }).catch(err => {
                console.log(err);
                console.error("Error fetching QR passwords from API.");
        });
    }
}