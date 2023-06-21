
<?php

class helper{
    public static function get_hours_format($secs) {
        $hours = floor($secs / 3600);
        $minutes = floor(($secs - ($hours * 3600)) / 60);
        $formattime = '';
        
        if ($hours > 0) {
            $formattime .= $hours . ' h ';
        }

        if ($minutes > 0) {
            $formattime .=  $minutes . ' min';
        }
        return $formattime;
    }
}