<?php
defined('MOODLE_INTERNAL') || die;

$tasks = array(
    array(
        'classname' => 'hybridteachvc_zoom\task\downloadrecords',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*/1',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
);
