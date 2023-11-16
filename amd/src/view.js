define([
    'core/ajax', 'core/toast', 'core/str'
], function(ajax, toast, str) {

    const hybridteaching_student_view_update = (sessionid, userid) => ajax.call([{
        methodname: 'mod_hybridteaching_get_display_actions',
        args: {
            sessionid,
            userid,
        }
    }])[0].done(response => {
        return JSON.parse(response);
    }).fail(err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });
    const update_view = (sessionid, userid, timer = 0) => {
        hybridteaching_student_view_update(sessionid, userid).done( r => {
            r = JSON.parse(r);
            var useform = false;
            if (r.buttons === null || r.buttons == false ) {
                clearInterval(timer);
            } else {
                var display = r.buttons;
                if (!r.admin) {
                    if (display == 'enter') {
                        toast.add(str.get_string('entersession', 'hybridteaching'));
                    } else {
                        toast.add(str.get_string('exitsession', 'hybridteaching'));
                    }
                    var exitstr = str.get_string('exitsession', 'hybridteaching');
                    exitstr.done(function(exitstring) {
                        if (display == 'exit') {
                            document.querySelector('#zonemessage').querySelector('.alert-info').textContent =
                            exitstring;
                        }
                    });
                    clearInterval(timer);
                }

                var forms = (document.getElementsByTagName('form'));
                forms.forEach(form => {
                    let formid = (form.getAttribute('id'));
                    if (formid !== undefined && formid !== null) {
                        useform = true;
                    }
                    if (useform && (display == 'enter' || display == 'exit')) {
                        switch (formid) {
                            case 'joinvc':
                                if (display == 'enter') {
                                    form.setAttribute('style', 'display: block;');
                                } else {
                                    form.setAttribute('style', 'display: none;');
                                }
                                break;
                            case 'showqr':
                                if (display == 'enter') {
                                    form.setAttribute('style', 'display: block;');
                                } else {
                                    form.setAttribute('style', 'display: none;');
                                }
                                break;
                            case 'registeratt':
                                if (display == 'enter') {
                                    form.setAttribute('style', 'display: block;');
                                } else {
                                    form.setAttribute('style', 'display: none;');
                                }
                                break;
                            case 'finishatt':
                                if (display == 'enter') {
                                    form.setAttribute('style', 'display: none;');
                                } else {
                                    form.setAttribute('style', 'display: block;');
                                }
                                break;
                            default:
                                break;
                        }
                    }
                });
            }
        });
    };
    return {
        init: (sessionid, userid) => {
            update_view(sessionid, userid);
            hybridteaching_student_view_update(sessionid, userid);
            var timer = setInterval(() => {
                update_view(sessionid, userid, timer);}, 15000);
        }
    };
});
