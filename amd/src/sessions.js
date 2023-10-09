define([
    'core/ajax',
], function(ajax) {
    const sessionexempt = (e) => {
        sess = e.currentTarget;
        sessid = sess.dataset.id
        if (sess.checked) {
            set_session_exempt(sessid, 1);
        } else {
            set_session_exempt(sessid, 0);
        }
    } 

    /**
     * Sets manual attendance
     * 
     * @param {integer} sessid
     * @param {array} status
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
            
            const attsess = document.querySelectorAll('.attexempt');
            attsess.forEach(sess => {
                sess.addEventListener('change', sessionexempt, true); 
            });
        }
    }
});