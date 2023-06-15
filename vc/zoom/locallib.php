<?php

use core\oauth2\rest;

global $CFG;

// Meeting types.
define('HTZOOM_INSTANT_MEETING', 1);
define('HTZOOM_SCHEDULED_MEETING', 2);
define('HTZOOM_RECURRING_MEETING', 3);
define('HTZOOM_SCHEDULED_WEBINAR', 5);
define('HTZOOM_RECURRING_WEBINAR', 6);
define('HTZOOM_RECURRING_MEETING_FIXEDTIME', 8);
// Number of meetings per page from zoom's get user report.
define('HTZOOM_DEFAULT_RECORDS_PER_CALL', 30);
define('HTZOOM_MAX_RECORDS_PER_CALL', 300);

//grabaciones
define('HTZOOM_RECORDING_CLOUD', 'cloud');
define('HTZOOM_RECORDING_DISABLED', 'none');

/**
 * Check if the error indicates that a user is not found or does not belong to the current account.
 *
 * @param string $error
 * @return bool
 */
function htzoom_is_user_not_found_error($error) {
    return strpos($error, 'not exist') !== false || strpos($error, 'not belong to this account') !== false
        || strpos($error, 'not found on this account') !== false;
}

/**
 * Check if the error indicates that roles is not found.
 *
 * @param string $error
 * @return bool
 */
function htzoom_is_roles_not_found_error($error) {
    return strpos($error,'invalid access')!==false;
}

/**
 * Check if the error indicates that users is not found.
 *
 * @param string $error
 * @return bool
 */
function htzoom_is_users_not_found_error($error) {
    return strpos($error,'invalid access')!==false;
}

function get_zone_access($hybridteaching){
    
    require_once($CFG->dirroot.'/mod/hybridteaching/vc/hybrid'.$type.'/locallib.php'); 

}
