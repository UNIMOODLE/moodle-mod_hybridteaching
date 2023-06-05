<?php

use core\oauth2\rest;

global $CFG;

// Meeting types.
define('HYBRIDZOOM_INSTANT_MEETING', 1);
define('HYBRIDZOOM_SCHEDULED_MEETING', 2);
define('HYBRIDZOOM_RECURRING_MEETING', 3);
define('HYBRIDZOOM_SCHEDULED_WEBINAR', 5);
define('HYBRIDZOOM_RECURRING_WEBINAR', 6);
define('HYBRIDZOOM_RECURRING_MEETING_FIXEDTIME', 8);
// Number of meetings per page from zoom's get user report.
define('HYBRIDZOOM_DEFAULT_RECORDS_PER_CALL', 30);
define('HYBRIDZOOM_MAX_RECORDS_PER_CALL', 300);

//grabaciones
define('HYBRIDZOOM_RECORDING_CLOUD', 'cloud');
define('HYBRIDZOOM_RECORDING_DISABLED', 'none');

/**
 * Check if the error indicates that a user is not found or does not belong to the current account.
 *
 * @param string $error
 * @return bool
 */
function hybridzoom_is_user_not_found_error($error) {
    return strpos($error, 'not exist') !== false || strpos($error, 'not belong to this account') !== false
        || strpos($error, 'not found on this account') !== false;
}

/**
 * Check if the error indicates that roles is not found.
 *
 * @param string $error
 * @return bool
 */
function hybridzoom_is_roles_not_found_error($error) {
    return strpos($error,'invalid access')!==false;
}

/**
 * Check if the error indicates that users is not found.
 *
 * @param string $error
 * @return bool
 */
function hybridzoom_is_users_not_found_error($error) {
    return strpos($error,'invalid access')!==false;
}

function get_zone_access($hybridteaching){
    
    require_once($CFG->dirroot.'/mod/hybridteaching/vc/hybrid'.$type.'/locallib.php'); 

}
