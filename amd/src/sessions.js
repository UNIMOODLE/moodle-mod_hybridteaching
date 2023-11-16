define([
    'core/ajax',
], function(ajax) {
    const sessionexempt = (e) => {
        var sess = e.currentTarget;
        var sessid = sess.dataset.id;
        if (sess.checked) {
            set_session_exempt(sessid, 1);
        } else {
            set_session_exempt(sessid, 0);
        }
    }
    
    const get_modal = (e) => {
        e.preventDefault();
        sess = e.currentTarget;
        sessid = sess.dataset.id
        get_modal_text(sessid);
    } 

    /**
     * Sets manual attendance
     *
     * @param {integer} sessid
     * @param {array} attexempt
     * @returns response with the modified slot information.
     */
    const set_session_exempt = (sessid, attexempt) => ajax.call([{
        methodname: 'mod_hybridteaching_set_session_exempt',
        args: {
            sessid,
            attexempt,
        },
    }])[0].done( response => {
        return response;
    }).fail( err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    const get_modal_text = (sessid) => ajax.call([{
        methodname: 'mod_hybridteaching_get_modal_text',
        args: {
            sessid,
        },
    }])[0].done(response => {
        const parsedResponse = JSON.parse(response);
        document.querySelector("#sessionamemodal").innerHTML = parsedResponse.sessname;
        if (parsedResponse.sesspass != undefined && parsedResponse.sesspass != "") {
            document.querySelector("#sessionpassmodal").innerHTML = parsedResponse.sesspass;
        }

        if (parsedResponse.sessurl != undefined && parsedResponse.sessurl != "") {
            document.querySelector("#sessionurlmodal").innerHTML = parsedResponse.sessurl;
        }
    }).fail( err => {
        console.log(err);
    });

    return {
        init: () => {
            document.getElementById('id_perpage').onchange = function() {
                var formPrefix = 'mform3';
                var formElement = document.querySelector('[id^="' + formPrefix + '"]');
                if (formElement) {
                    formElement.submit();
                }
            };

            const sessinfo = document.querySelectorAll('.sessinfo');
            sessinfo.forEach(sess => {
                sess.addEventListener('click', get_modal, true); 
            });
            
            const attsess = document.querySelectorAll('.attexempt');
            attsess.forEach(sess => {
                sess.addEventListener('change', sessionexempt, true);
            });
        }
    };
});