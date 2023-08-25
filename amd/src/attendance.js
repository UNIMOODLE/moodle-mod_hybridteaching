
const manualattendance = (e) => {
    att = e.currentTarget;
    if (att.checked) {
        
        confirm('yes') ? console.log('y') : att.checked = false;
    } else {
        confirm('no') ? console.log('n') : att.checked = true;
    }

} 

export const init = () => {
    document.getElementById('id_perpage').onchange = function() {
        var formPrefix = 'optionsform';
        var formElement = document.querySelector('[id^="' + formPrefix + '"]');
        if (formElement) {
            formElement.submit();
        }
    };
    manualatt = document.querySelectorAll('.attendance-validated');
    manualatt.forEach(att => {
        att.addEventListener('change', manualattendance, true); 
    });
};
