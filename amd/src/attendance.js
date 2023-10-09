define([
    'core/ajax',
], function(ajax) {
    const manualattendance = (e) => {
        att = e.currentTarget;
        attid = att.closest('tr').querySelector('[id^="' + 'attendance' + '"]').value
        if (att.checked) {
            if (confirm('yes')) {
                console.log('y');
                set_manual_attendance(attid, 1);
            }  else {
                att.checked = false;
            }
        } else {
            if (confirm('no')) {
                console.log('n');
                set_manual_attendance(attid, 0);
            }  else {
                att.checked = true;
            }
        }
    } 

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
        console.log(err);
    });

    return {
        init: () => {
            if (document.getElementById('id_perpage') != null) {
                document.getElementById('id_perpage').onchange = function() {
                    let formPrefix = 'optionsform';
                    let formElement = document.querySelector('[id^="' + formPrefix + '"]');
                    if (formElement) {
                        formElement.submit();
                    }
                };
            }
            if (document.getElementById('id_selectedsession') != null) {
                document.getElementById('id_selectedsession').onchange = function() {
                    let formPrefix = 'sessionsform';
                    let formElement = document.querySelector('[id^="' + formPrefix + '"]');
                    if (formElement) {
                        formElement.submit();
                    }
                };
            }
            if (document.getElementById('id_selecteduser') != null) {
                document.getElementById('id_selecteduser').onchange = function() {
                    let formPrefix = 'sessionsform';
                    let formElement = document.querySelector('[id^="' + formPrefix + '"]');
                    if (formElement) {
                        formElement.submit();
                    }
                };
            }
            manualatt = document.querySelectorAll('.attendance-validated');
            manualatt.forEach(att => {
                att.addEventListener('change', manualattendance, true); 
            });
        }
    }
});