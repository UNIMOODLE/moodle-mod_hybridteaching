<?php
defined('MOODLE_INTERNAL') || die;

$tasks = array(
    array(
        'classname' => 'hybridteachstore_pumukit\task\updatestores',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '*/1',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
);