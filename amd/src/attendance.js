define([
    'core/ajax'
], function(ajax) {
    const manualattendance = async(e) => {
        var att = e.currentTarget;
        var attid = att.closest('tr').querySelector('[id^="' + 'attendance' + '"]').value;
        if (att.checked) {
            set_manual_attendance(attid, 1);
        } else {
            set_manual_attendance(attid, 0);
        }
    };

    /**
     * Sets manual attendance
     *
     * @param {integer} attid
     * @param {array} status
     * @returns response with the modified slot information.
     */
    const set_manual_attendance = (attid, status) => ajax.call([{
        methodname: 'mod_hybridteaching_set_manual_attendance',
        args: {
            attid,
            status,
        },
    }])[0].done(response => {
        return response;
    }).fail(err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    return {
        init: () => {
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
