<?php

defined('MOODLE_INTERNAL') || die();

// List of observers.
$observers = array(
    array(
        'eventname'   => '\mod_hybridteaching\event\session_finished',
        'callback'    => 'mod_hybridteaching_observer::session_finished',
    ),    
    array(
        'eventname'   => '\mod_hybridteaching\event\session_joined',
        'callback'    => 'mod_hybridteaching_observer::session_joined',
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
        'eventname'   => '\mod_hybridteaching\event\attendance_updated',
        'callback'    => 'mod_hybridteaching_observer::attendance_updated',
    ),
);


