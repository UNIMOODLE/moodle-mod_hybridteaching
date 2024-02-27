define([
    'core/ajax'
], function(ajax) {
    const manualattendance = async(e) => {
        var att = e.currentTarget;
        var attid = att.closest('tr').querySelector('[id^="' + 'attendance' + '"]').value;
        var id = document.getElementsByName('id')[0].value;
        if (att.checked) {
            set_manual_attendance(attid, 1, id);
        } else {
            set_manual_attendance(attid, 0, id);
        }
    };

    /**
     * Sets manual attendance
     *
     * @param {integer} attid
     * @param {array} status
     * @param {string} id (cmid)
     * @returns response with the modified slot information.
     */
    const set_manual_attendance = (attid, status, id) => ajax.call([{
        methodname: 'mod_hybridteaching_set_manual_attendance',
        args: {
            attid,
            status,
            id,
        },
    }])[0].done(response => {
        return response;
    }).fail(err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    /**
     * Disable check if session is in progress
     * 
     */
    const disable_checks_session_inprogress = () => {
        
        let queryString = window.location.search;
        let urlParams = new URLSearchParams(queryString);
        //urlParams.get('view') == "sessionattendance" ? document.getElementById("sessionattendancebtn").style.display = "none" : "";
        //urlParams.get('view') == "extendedstudentatt" ? document.getElementById("extendedstudentattbtn").style.display = "none" : "";
            
        var hybridteachingid = urlParams.get('id');
        var sessionid = urlParams.get('sessionid');
        ajax.call([{
            methodname: 'mod_hybridteaching_disable_attendance_inprogress',
            args: {
                hybridteachingid,
                sessionid
            },
        }])[0].done(response => {
            response = JSON.parse(response);
            var table = document.getElementById('hybridteachingattendance');
            
            if (urlParams.get('view') == "sessionattendance" || urlParams.get('view') == null) {
                if (Object.keys(response[0]).length > 0) {
                    let allchecks = document.getElementById("select-all-attendance");
                    allchecks.disabled = "true";
                    
                }
                for (var i in response[0]) {
                    let check = document.getElementById('session-'+response[0][i].id);
                    check.disabled = "true";
                }
            }
            if (urlParams.get('view')=="extendedsessionatt") {
                if (Object.keys(response[1]).length > 0) {
                    let allchecks = document.getElementById("select-all-attendance");
                    allchecks.disabled = "true";
                    console.log(response[1]);
                }
                for (var i in response[1]) {
                    let check = document.getElementById('attendance-'+response[1][i].id);
                    console.log("fila:"+check.rowIndex)
                    console.log("columna:"+check.cellIndex)
                    console.log("------------");
                    check.disabled = "true";
                    for (var i = 1; i < table.rows.length; i++) {
                        var rowatt = table.rows[i];
                        var cellat = rowatt.cells[0];
                        if (cellat.firstChild.id === check.id) {
                            let cellatt = rowatt.cells[8].firstChild;
                            cellatt.disabled = "true";
                            console.log(cellatt);
                        }
                      }
                }
            }
        }).fail(err => {
            console.log(err);
        })
    };

    return {
        init: () => {
            disable_checks_session_inprogress();
            window.addEventListener("beforeunload", (e) => {
                e.stopImmediatePropagation();
            }, true);
            if (document.getElementById('id_perpage') !== null) {
                document.getElementById('id_perpage').onchange = function() {
                    let formPrefix = 'optionsform';
                    let formElement = document.querySelector('[id^="' + formPrefix + '"]');
                    if (formElement) {
                        formElement.submit();
                    }
                };
            }
            if (document.getElementById('id_attfilter') !== null) {
                document.getElementById('id_attfilter').onchange = function() {
                    let formPrefix = 'optionsform';
                    let formElement = document.querySelector('[id^="' + formPrefix + '"]');
                    if (formElement) {
                        formElement.submit();
                    }
                };
            }
            if (document.getElementById('id_groupid') !== null) {
                document.getElementById('id_groupid').onchange = function() {
                    let formPrefix = 'optionsform';
                    let formElement = document.querySelector('[id^="' + formPrefix + '"]');
                    if (formElement) {
                        formElement.submit();
                    }
                };
            }
            if (document.getElementById('id_selectedsession') !== null) {
                document.getElementById('id_selectedsession').onchange = function() {
                    let formPrefix = 'sessionsform';
                    let formElement = document.querySelector('[id^="' + formPrefix + '"]');
                    if (formElement) {
                        formElement.submit();
                    }
                };
            }
            if (document.getElementById('id_selecteduser') !== null) {
                document.getElementById('id_selecteduser').onchange = function() {
                    let formPrefix = 'sessionsform';
                    let formElement = document.querySelector('[id^="' + formPrefix + '"]');
                    if (formElement) {
                        formElement.submit();
                    }
                };
            }
            var manualatt = document.querySelectorAll('.attendance-validated');
            manualatt.forEach(att => {
                att.addEventListener('change', manualattendance, true);
            });
            
        }
    };
});
