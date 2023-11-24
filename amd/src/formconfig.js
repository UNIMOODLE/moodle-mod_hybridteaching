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

import {get_string as getString} from 'core/str';
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
    sectionInitialStatus: () => document.getElementById('id_sectioninitialstates'),
    sectionAudience: () => document.getElementById('id_sectionaudience'),
    participantList: () => document.getElementsByName('participants')[0],
    modStandardGrade: () => document.getElementById('id_modstandardgrade'),
    sessionscheduling: () => document.getElementById('id_sessionscheduling'),
    permisionscontainer: () => document.getElementById('id_sectionaudiencecontainer'),
};

//initialformi check fun on change typevc;
export const init = () => {
    /**
     * Page load actions and events.
     */
    useVC();
    useAttendance();
    useSessionsScheduling();
    vcInitialStatus(ELEMENT_SELECTOR.typeVC());
    // Event listeners.
    ELEMENT_SELECTOR.useVCRecord().addEventListener('change', (e) => useVCRecord(e));
    ELEMENT_SELECTOR.useVC().addEventListener('change', (e) => useVC(e));
    ELEMENT_SELECTOR.useAttendance().addEventListener('change', () => useAttendance());
    ELEMENT_SELECTOR.sessionscheduling().addEventListener('change', () => useSessionsScheduling());
    ELEMENT_SELECTOR.permisionscontainer().querySelector('[name="hybridteaching_participant_selection"').disabled = true;
    ELEMENT_SELECTOR.typeVC().addEventListener('change', (e) => vcInitialStatus(e.target));
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
    let sectionInitialStatus = ELEMENT_SELECTOR.sectionInitialStatus();
    let initialformi = sectionInitialStatus.getElementsByTagName('INPUT');
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
        sectionInitialStatus.setAttribute('style', 'display:block');

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
        sectionInitialStatus.setAttribute('style', 'display:none');

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

const vcInitialStatus = (e) => {
    let target = (e);
    if ( target.options[target.selectedIndex] === undefined) {
        return null;
    }
    let vctypevalue = target.options[target.selectedIndex].value;
    let regex = /[A-Za-z]+/i;
    let vctype = vctypevalue.match(regex)[0];
    let validStatus = [];
    let disabledOptions = [];
    validStatus['vc'] = ELEMENT_SELECTOR.sectionInitialStatus().querySelectorAll('input:not([type=hidden])');
    validStatus['store'] = ELEMENT_SELECTOR.recordOptions().querySelectorAll('input:not([type=hidden])');
    switch (vctype) {
        case 'bbb':
            disabledOptions = ['id_downloadrecords'];
            break;
        case 'meet':
            disabledOptions = ['id_disablecam', 'id_disablemic', 'id_disableprivatechat', 'id_disablepublicchat',
                'id_disablenote', 'id_hideuserlist', 'id_blockroomdesign', 'id_ignorelocksettings',
                'id_initialrecord', 'id_hiderecordbutton'];
            break;
        case 'teams':
            disabledOptions = ['id_disableprivatechat', 'id_disablenote', 'id_hideuserlist', 'id_blockroomdesign',
                'id_ignorelocksettings', 'id_hiderecordbutton', 'id_showpreviewrecord'];
            break;
        case 'zoom':
            disabledOptions = ['id_disableprivatechat', 'id_disablepublicchat', 'id_disablenote', 'id_hideuserlist',
                'id_blockroomdesign', 'id_ignorelocksettings', 'id_initialrecord', 'id_hiderecordbutton',
                'id_showpreviewrecord'];
            break;
        default:
            break;
    }
    vcInitialStatusDisplay(validStatus['vc'], validStatus['store'], disabledOptions, vctype).then();
};

const vcInitialStatusDisplay = async(vc, store, conditions, vctype) => {
    const nostateconfig = await getString('noinitialstateconfig', 'mod_hybridteaching');
    let disabledOptions = conditions;
    vc.forEach(input => {
        if (disabledOptions.includes(input.id)) {
            input.closest('.row.fitem').setAttribute('style', 'display:none;');
            input.value = 0;
        } else {
            input.closest('.row.fitem').setAttribute('style', 'display:flex;');
            input.value = 1;
        }
    });
    store.forEach(input => {
        if (disabledOptions.includes(input.id)) {
            input.closest('.row.fitem').setAttribute('style', 'display:none;');
            input.value = 0;
        } else {
            input.closest('.row.fitem').setAttribute('style', 'display:flex;');
            input.value = 1;
        }
    });
    if (vctype === 'meet') {
        if (document.getElementById('nostateconfig') !== null) {
            document.getElementById('nostateconfig').setAttribute('style', 'display:flex');
        } else {
            let stateNode = document.createElement('p');
            stateNode.append(nostateconfig);
            stateNode.setAttribute('id', 'nostateconfig');
            stateNode.setAttribute('class', 'text-info');
            document.getElementById('id_sectioninitialstatescontainer').appendChild(stateNode);
        }
    } else {
        if (document.getElementById('nostateconfig') !== null) {
            document.getElementById('nostateconfig').setAttribute('style', 'display:none');
        }
    }
};
