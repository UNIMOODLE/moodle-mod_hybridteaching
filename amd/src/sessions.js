
export const init = () => {
    document.getElementById('id_perpage').onchange = function() {
        var formPrefix = 'mform3';
        var formElement = document.querySelector('[id^="' + formPrefix + '"]');
        if (formElement) {
            formElement.submit();
        }
    };
};
