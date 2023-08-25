<?php

defined('MOODLE_INTERNAL') || die();

// List of observers.
$observers = array(
    array(
        'eventname'   => '\mod_hybridteaching\event\session_finished',
        'callback'    => 'mod_hybridteaching_observer::session_finished',
    ),    
    array(
        'eventname'   => '\mod_hybridteaching\event\session_join',
        'callback'    => 'mod_hybridteaching_observer::session_join',
    ),
    array(
        'eventname'   => '\mod_hybridteaching\event\view_session_record',
        'callback'    => 'mod_hybridteaching_observer::view_session_record',
    ),    
    array(
        'eventname'   => '\mod_hybridteaching\event\download_session_record',
        'callback'    => 'mod_hybridteaching_observer::download_session_record',
    ),
    array(
        'eventname'   => '\mod_hybridteaching\event\session_viewed',
        'callback'    => 'mod_hybridteaching_observer::session_viewed',
    ),    
    array(
        'eventname'   => '\mod_hybridteaching\event\session_added',
        'callback'    => 'mod_hybridteaching_observer::session_added',
    ),
    array(
        'eventname'   => '\mod_hybridteaching\event\session_updated',
        'callback'    => 'mod_hybridteaching_observer::session_updated',
    ),    
    array(
        'eventname'   => '\mod_hybridteaching\event\session_deleted',
        'callback'    => 'mod_hybridteaching_observer::session_deleted',
    ),
    array(
        'eventname'   => '\mod_hybridteaching\event\session_info_view',
        'callback'    => 'mod_hybridteaching_observer::session_info_view',
    ),    
    array(
        'eventname'   => '\mod_hybridteaching\event\session_manage_viewed',
        'callback'    => 'mod_hybridteaching_observer::session_manage_viewed',
    ),
    array(
        'eventname'   => '\mod_hybridteaching\event\attendance_manage_viewed',
        'callback'    => 'mod_hybridteaching_observer::attendance_manage_viewed',
    ),    
    array(
        'eventname'   => '\mod_hybridteaching\event\attendance_viewed',
        'callback'    => 'mod_hybridteaching_observer::attendance_viewed',
    ),
    array(
        'eventname'   => '\mod_hybridteaching\event\attendance_updated',
        'callback'    => 'mod_hybridteaching_observer::attendance_updated',
    ),
);


