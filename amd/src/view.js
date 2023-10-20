define([
    'core/ajax', 'core/toast', 'core/str'
], function(ajax, toast, str) {

    const hybridteaching_student_view_update = (sessionid, userid) => ajax.call([{
        methodname: 'mod_hybridtaeching_get_display_actions',
        args: {
            sessionid,
            userid,
        }
    }])[0].done(response => {
        return JSON.parse(response);
    }).fail(err => {
        console.log(err);
    });
    const update_view = (sessionid, userid, test = 0) => {
        hybridteaching_student_view_update(sessionid, userid).done( r => {
            r = JSON.parse(r);
            useform = false;
            if (r.buttons == null || r.buttons == false ) {
                clearInterval(test);
            } else {
                display = r.buttons;
                if (!r.admin) {
                    display == 'enter' ? toast.add(str.get_string('entersession', 'hybridteaching')) :
                        toast.add(str.get_string('exitsession', 'hybridteaching'));
                    var exitstr = str.get_string('exitsession', 'hybridteaching');
                    exitstr.done(function(exitstring) {
                        display == 'exit' ? document.querySelector('#zonemessage').querySelector('.alert-info').textContent =
                         exitstring : '';
                    })
                }

                forms = (document.getElementsByTagName('form'));
                forms.forEach(form => {
                    let formid = (form.getAttribute('id'));
                    formid !== undefined && formid !== null ? useform = true : '';
                    if (useform && (display == 'enter' || display == 'exit')) {
                        switch (formid) {
                            case 'joinvc':
                                display == 'enter' ? form.setAttribute('style', 'display: block;') :
                                form.setAttribute('style', 'display: none;');
                                break;
                            case 'showqr':
                                display == 'enter' ? form.setAttribute('style', 'display: block;') :
                                form.setAttribute('style', 'display: none;');
                                break;
                            case 'registeratt':
                                display == 'enter' ? form.setAttribute('style', 'display: block;') :
                                form.setAttribute('style', 'display: none;');
                                break;
                            case 'finishatt':
                                display == 'enter' ? form.setAttribute('style', 'display: none;') :
                                form.setAttribute('style', 'display: block;');
                                break;
                            default:
                                break;
                        }
                    }
                });
            }
            
        });
    }
    return {
        init: (sessionid, userid) => {
            update_view(sessionid, userid);
            hybridteaching_student_view_update(sessionid, userid);
            var test = setInterval(() => {
                update_view(sessionid, userid, test)}, 15000);
        }
    }
});
