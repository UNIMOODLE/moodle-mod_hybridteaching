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
import {call as fetchMany} from 'core/ajax';
/**
 * Get all selectors in one place.
 *
 */
var cmgroupmodeselector = null;
if (document.getElementsByName('cmgroupmode')[0] !== undefined) {
     cmgroupmodeselector =  document.getElementsByName('cmgroupmode')[0].value;
}
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
    groupmode: () => document.getElementById('id_groupmode'),
    cmgroupmode: () => cmgroupmodeselector,
    pagecontext: () => get_page_context(document.getElementsByTagName('body')[0].getAttribute('class')),
};
var plistold = ELEMENT_SELECTOR.participantList().value;

/**
 * Gets user recording capability on current vctype
 *
 * @param {integer} typevc
 * @param {array} pagecontext
 * @returns response with the modified slot information.
 */
const userrecordingcapability = (typevc, pagecontext) => fetchMany([{
    methodname: 'mod_hybridteaching_user_has_recording_capability',
    args: {
        typevc,
        pagecontext,
    },
}])[0].done(response => {
    return response;
}).fail(err => {
    // eslint-disable-next-line no-console
    console.log(err);
});

//initialformi check fun on change typevc;
export const init = () => {
    /**
     * Page load actions and events.
     */
    useVC();
    useAttendance();
    useGroupMode(ELEMENT_SELECTOR.sessionscheduling()).then();
    useSessionsScheduling();
    vcInitialStatus(ELEMENT_SELECTOR.typeVC());
    // Event listeners.
    ELEMENT_SELECTOR.useVCRecord().addEventListener('change', (e) => useVCRecord(e));
    ELEMENT_SELECTOR.useVC().addEventListener('change', (e) => useVC(e));
    ELEMENT_SELECTOR.useAttendance().addEventListener('change', () => useAttendance());
    ELEMENT_SELECTOR.sessionscheduling().addEventListener('change', () => useSessionsScheduling());
    ELEMENT_SELECTOR.permisionscontainer().querySelector('[name="hybridteaching_participant_selection"').disabled = true;
    ELEMENT_SELECTOR.typeVC().addEventListener('change', (e) => vcInitialStatus(e.target));
    ELEMENT_SELECTOR.typeVC().addEventListener('change', () => useVCRecord());
    ELEMENT_SELECTOR.sessionscheduling().addEventListener('change', (e) => useGroupMode(e.target));
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

        modStandardGrade.setAttribute('style', 'display:block');
    } else {
        formi.forEach(input => {
            if (input.type !== 'hidden' && input.getAttribute('readonly') === null) {
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
            if (accesinput.type !== 'hidden' && accesinput.getAttribute('readonly') === null) {
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
        sectionAudience.setAttribute('style', 'display:none');
        if (document.getElementById('norecordingcap') !== null) {
            document.getElementById('norecordingcap').setAttribute('style', 'display:none');
        }
        document.getElementById('id_reusesession').checked = false;
    }
    useVCRecord();
};

const useVCRecord = async(e = ELEMENT_SELECTOR.useVCRecord()) => {
    let recordOptions = ELEMENT_SELECTOR.recordOptions();
    let formi = recordOptions.getElementsByTagName('INPUT');
    let typevc = get_vc_type(ELEMENT_SELECTOR.typeVC().value);
    const norecordingcap = await getString('norecordingmoderation', 'hybridteaching');
    userrecordingcapability(typevc, ELEMENT_SELECTOR.pagecontext()).done(r => {
        let response = JSON.parse(r);
        if (!response && response != 'notypevc') {
            ELEMENT_SELECTOR.useVCRecord().checked = false;
            ELEMENT_SELECTOR.useVCRecord().setAttribute('disabled', 'true');
            if (document.getElementById('norecordingcap') !== null && ELEMENT_SELECTOR.useVC().checked) {
                document.getElementById('norecordingcap').setAttribute('style', 'display:flex');
            } else if (ELEMENT_SELECTOR.useVC().checked) {
                let capnode = document.createElement('p');
                capnode.append(norecordingcap);
                capnode.setAttribute('id', 'norecordingcap');
                capnode.setAttribute('class', 'text-danger');
                document.getElementById('id_sectiongeneralcontainer').appendChild(capnode);
            }
        } else {
            ELEMENT_SELECTOR.useVCRecord().removeAttribute('disabled');
            if (document.getElementById('norecordingcap') !== null && ELEMENT_SELECTOR.useVC().checked) {
                document.getElementById('norecordingcap').setAttribute('style', 'display:none');
            }
        }
    }).then( function() {
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
    });

};

const useGroupMode = async(e = ELEMENT_SELECTOR.sessionscheduling()) => {
    let groupmode = ELEMENT_SELECTOR.groupmode();
    let sessionscheduling = e;
    let cmgroupmode = ELEMENT_SELECTOR.cmgroupmode();
    if (groupmode === undefined || groupmode === null) {
        if (cmgroupmode != 0) {
            sessionscheduling.setAttribute('disabled', 'true');
            sessionscheduling.checked = true;
            sessionscheduling.value = 1;
            sessionscheduling.closest('div').querySelector('[type^="hidden"]').value = 1;
        } else {
            document.getElementById('id_groupingid').setAttribute('disabled', 'true');
        }
    } else {
        if (!is_checkbox_checked(sessionscheduling)) {
            groupmode.closest('.fitem').setAttribute('style', 'display:none;');
            document.getElementById('fitem_id_groupingid').setAttribute('style', 'display:none;');
            groupmode.value = 0;
            document.getElementById('id_groupingid').value = 0;
        } else {
            groupmode.closest('.fitem').setAttribute('style', 'display:flex;');
            document.getElementById('fitem_id_groupingid').setAttribute('style', 'display:flex;');
            document.getElementById('id_groupingid').value = 0;
        }
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
    if (target.options[target.selectedIndex] === undefined) {
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

const get_page_context = (bodyinfo) => {
    let regex = /context-[0-9]+/i;
    return bodyinfo.match(regex)[0].substring(8);
};

const get_vc_type = (vctype) => {
    if (vctype === undefined || vctype === '') {
        return false;
    }
    let regex = /-[A-Za-z]+/i;
    return vctype.match(regex)[0].substring(1);
};
