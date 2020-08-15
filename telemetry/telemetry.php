<?php
/**
 * Seamless Donations by David Gewirtz, adopted from Allen Snook
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: http://zatzlabs.com/seamless-donations/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2020 by David Gewirtz
 *
 */

$check_sd_dir = WP_CONTENT_DIR . '/plugins/' . "seamless-donations/seamless-donations.php";
register_activation_hook($check_sd_dir, 'seamless_donations_telemetry_activated');
register_deactivation_hook($check_sd_dir, 'seamless_donations_telemetry_deactivated');

// when the plugin is activated, record activation count and timestamp
function seamless_donations_telemetry_activated(){
    $activation_count = get_option('dgx_donate_activation_count');
    if($activation_count !== false) {
        update_option('dgx_donate_activation_count', $activation_count+1);
    } else {
        update_option('dgx_donate_activation_count', 1);
    }
    $timestamp = time();
    update_option('dgx_donate_activation_timestamp', $timestamp);
}

function seamless_donations_telemetry_deactivated(){
    $timestamp = time();
    update_option('dgx_donate_deactivation_timestamp', $timestamp);
}

/*function time_elapsed_A($secs){
    $bit = array(
        'y' => $secs / 31556926 % 12,
        'w' => $secs / 604800 % 52,
        'd' => $secs / 86400 % 7,
        'h' => $secs / 3600 % 24,
        'm' => $secs / 60 % 60,
        's' => $secs % 60
    );

    foreach($bit as $k => $v)
        if($v > 0)$ret[] = $v . $k;

    return join(' ', $ret);
}


function time_elapsed_B($secs){
    $bit = array(
        ' year'        => $secs / 31556926 % 12,
        ' week'        => $secs / 604800 % 52,
        ' day'        => $secs / 86400 % 7,
        ' hour'        => $secs / 3600 % 24,
        ' minute'    => $secs / 60 % 60,
        ' second'    => $secs % 60
    );

    foreach($bit as $k => $v){
        if($v > 1)$ret[] = $v . $k . 's';
        if($v == 1)$ret[] = $v . $k;
    }
    array_splice($ret, count($ret)-1, 0, 'and');
    $ret[] = 'ago.';

    return join(' ', $ret);
}*/



//
//$nowtime = time();
//$oldtime = 1335939007;
//
//echo "time_elapsed_A: ".time_elapsed_A($nowtime-$oldtime)."\n";
//echo "time_elapsed_B: ".time_elapsed_B($nowtime-$oldtime)."\n";

/** Output:
time_elapsed_A: 6d 15h 48m 19s
time_elapsed_B: 6 days 15 hours 48 minutes and 19 seconds ago.
 **/