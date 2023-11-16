// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The main mod_hybridteaching configuration form js.
 *
 * @copyright   2023 isyc <isyc@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get all selectors in one place.
 *
 */
const ELEMENT_SELECTOR = {
    useAttendance: () => document.getElementById('id_useattendance'),
    useVC: () => document.getElementById('id_usevideoconference'),
    useVCRecord: () => document.getElementById('id_userecordvc'),
    typeVC: () => document.getElementById('id_typevc'),
    recordOptions: () => document.getElementById('id_sectionrecording'),
    sectionAttendance: () => document.getElementById('id_sectionattendance'),
    sectionAccess: () => document.getElementById('id_sectionsessionaccess'),
    sectionInitialStates: () => document.getElementById('id_sectioninitialstates'),
    sectionAudience: () => document.getElementById('id_sectionaudience'),
    participantList: () => document.getElementsByName('participants')[0],
    modStandardGrade: () => document.getElementById('id_modstandardgrade'),
    sessionscheduling: () => document.getElementById('id_sessionscheduling'),
    permisionscontainer: () => document.getElementById('id_sectionaudiencecontainer'),
};

export const init = () => {
    /**
     * page load
     */
    useVC();
    useAttendance();
    useSessionsScheduling();
    //
    ELEMENT_SELECTOR.useVCRecord().addEventListener('change', (e) => useVCRecord(e));
    ELEMENT_SELECTOR.useVC().addEventListener('change', (e) => useVC(e));
    ELEMENT_SELECTOR.useAttendance().addEventListener('change', () => useAttendance());
    ELEMENT_SELECTOR.sessionscheduling().addEventListener('change', () => useSessionsScheduling());
    ELEMENT_SELECTOR.permisionscontainer().querySelector('[name="hybridteaching_participant_selection"').disabled = true;
};

const useSessionsScheduling = (e = ELEMENT_SELECTOR.sessionscheduling()) => {
    let sessionscheduling = ELEMENT_SELECTOR.sessionscheduling();
    let sectionsessions = sessionscheduling.closest('#id_sectionsessions');
    if (is_checkbox_checked(e) && is_element_displayed(e)) {
        sectionsessions.querySelector('#fitem_id_starttime').setAttribute('style', 'display:none');
        sectionsessions.querySelector('#fgroup_id_durationgroup').setAttribute('style', 'display:none');
        sectionsessions.querySelector('#id_duration').value = 0;
        sectionsessions.querySelector('#id_starttime_enabled').checked = 0;

    } else {
        sectionsessions.querySelector('#fitem_id_starttime').setAttribute('style', 'display:flex');
        sectionsessions.querySelector('#fgroup_id_durationgroup').setAttribute('style', 'display:flex');
        sectionsessions.querySelector('#fitem_id_starttime').value = 0;

    }

};

const useAttendance = (e = ELEMENT_SELECTOR.useAttendance()) => {
    let sectionAttendance = ELEMENT_SELECTOR.sectionAttendance();
    let formi = sectionAttendance.getElementsByTagName('INPUT');
    let modStandardGrade = ELEMENT_SELECTOR.modStandardGrade();
    let gradeformselect = modStandardGrade.getElementsByTagName('SELECT')[0];
    if (is_checkbox_checked(e) && is_element_displayed(e)) {
        formi.forEach(input => {
            if (input.type !== 'hidden' && input.type !== 'text') {
                input.value = 1;
            }
        });
        sectionAttendance.setAttribute('style', 'display:block');

        gradeformselect.value = 'point';
        modStandardGrade.setAttribute('style', 'display:block');
    } else {
        formi.forEach(input => {
            if (input.type !== 'hidden') {
                input.value = '';
            }
        });
        sectionAttendance.setAttribute('style', 'display:none');

        gradeformselect.value = 'none';
        modStandardGrade.setAttribute('style', 'display:none');
    }

};

const useVC = (e = ELEMENT_SELECTOR.useVC()) => {
    let recordoptions = ELEMENT_SELECTOR.useVCRecord();
    let typeVC = ELEMENT_SELECTOR.typeVC();
    let sectionAccess = ELEMENT_SELECTOR.sectionAccess();
    let accessformi = sectionAccess.getElementsByTagName('INPUT');
    let sectionInitialStates = ELEMENT_SELECTOR.sectionInitialStates();
    let initialformi = sectionInitialStates.getElementsByTagName('INPUT');
    let sectionAudience = ELEMENT_SELECTOR.sectionAudience();
    let participantList = ELEMENT_SELECTOR.participantList();
    let oldtypevc = ELEMENT_SELECTOR.typeVC().value;
    let plistold = participantList.value;
    if (is_checkbox_checked(e) && is_element_displayed(e)) {
        recordoptions.value = 1;
        typeVC.value = oldtypevc;
        recordoptions.closest('.form-group').setAttribute('style', 'display:flex');
        typeVC.closest('.form-group').setAttribute('style', 'display:flex');

        accessformi.forEach(accesinput => {
            if (accesinput.type !== 'hidden'&& accesinput.type !== 'text') {
                accesinput.value = 1;
            }
        });
        sectionAccess.setAttribute('style', 'display:block');

        initialformi.forEach(initialinput => {
            if (initialinput.type !== 'hidden'&& initialinput.type !== 'text') {
                initialinput.value = 1;
            }
        });
        sectionInitialStates.setAttribute('style', 'display:block');

        if (typeof plistold !== 'undefined') {
            participantList.value = plistold;
        }
        sectionAudience.setAttribute('style', 'display:block');

    } else {
        recordoptions.value = 0;
        oldtypevc = typeVC.value;
        typeVC.value = 0;
        recordoptions.closest('.form-group').setAttribute('style', 'display:none');
        typeVC.closest('.form-group').setAttribute('style', 'display:none');
        accessformi.forEach(accesinput => {
            if (accesinput.type !== 'hidden') {
                accesinput.value = 0;
            }
        });
        sectionAccess.setAttribute('style', 'display:none');

        initialformi.forEach(initialinput => {
            if (initialinput.type !== 'hidden') {
                initialinput.value = 0;
            }
        });
        sectionInitialStates.setAttribute('style', 'display:none');

        plistold = participantList.value;
        participantList.value = '[]';
        sectionAudience.setAttribute('style', 'display:none');
    }
    useVCRecord();
};

const useVCRecord = (e = ELEMENT_SELECTOR.useVCRecord()) => {
    let recordOptions = ELEMENT_SELECTOR.recordOptions();
    let formi = recordOptions.getElementsByTagName('INPUT');
    if (is_checkbox_checked(e) && is_element_displayed(e)) {
        formi.forEach(input => {
            if (input.type !== 'hidden' && input.type !== 'text') {
                input.value = 1;
            }
        });
        recordOptions.setAttribute('style', 'display:block');
    } else {
        formi.forEach(input => {
            if (input.type !== 'hidden') {
                input.value = 0;
            }
        });
        recordOptions.setAttribute('style', 'display:none');
    }

};

const is_checkbox_checked = (e) => {
    if (e.target !== undefined) {
        return e.target.checked;
    } return e.checked;
};

const is_element_displayed = (e) => {
    let displayed;
    if (e.target !== undefined) {
        displayed = window.getComputedStyle(e.target.closest('.form-group'), null).display;
    } else {
        displayed = window.getComputedStyle(e.closest('.form-group'), null).display;
    }
    return displayed != 'none';
};
