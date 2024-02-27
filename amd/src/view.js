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
