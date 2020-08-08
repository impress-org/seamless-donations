<?php
/*
 * Seamless Donations by David Gewirtz, adopted from Allen Snook
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: http://zatzlabs.com/seamless-donations/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2020 by David Gewirtz
 *
 */


function seamless_donations_debug_alert( $a ) {

    echo "<script>";
    echo 'alert("' . $a . '");';
    echo "</script>";
}

function seamless_donations_debug_log( $a ) {

    echo "<script>";
    echo 'console.log("' . $a . '");';
    echo "</script>";
}


// based on http://php.net/manual/en/function.var-dump.php notes by edwardzyang
function seamless_donations_var_dump_to_string( $mixed = NULL ) {

	ob_start();
	var_dump( $mixed );
	$content = ob_get_contents();
	ob_end_clean();
	$content = html_entity_decode( $content );

	return $content;
}

// differs from above because (a) to log, and (b) no html_entity_decode
function seamless_donations_var_dump_to_log( $mixed = NULL ) {

	$debug_log = get_option( 'dgx_donate_log' );

	if ( empty( $debug_log ) ) {
		$debug_log = array();
	}

	ob_start();
	var_dump( $mixed );
	$message = ob_get_contents();
	ob_end_clean();

	$debug_log[] = $message;

	update_option( 'dgx_donate_log', $debug_log );
}

function seamless_donations_printr_to_log( $mixed = NULL ) {

    $debug_log = get_option( 'dgx_donate_log' );

    if ( empty( $debug_log ) ) {
        $debug_log = array();
    }

    $message = print_r( $mixed, true );

    $debug_log[] = $message;

    update_option( 'dgx_donate_log', $debug_log );
}

function seamless_donations_post_array_to_log() {

	$debug_log = get_option( 'dgx_donate_log' );

	if ( empty( $debug_log ) ) {
		$debug_log = array();
	}

	$timestamp = current_time( 'mysql' );

	foreach ( $_POST as $key => $value ) {
		$debug_log[] = $timestamp . ' $_POST[' . $key . ']: ' . $value;
	}

	update_option( 'dgx_donate_log', $debug_log );
}

function seamless_donations_server_global_to_log( $arg, $show_always=false ) {

	if ( isset( $_SERVER[ $arg ] ) ) {
		dgx_donate_debug_log( '$_SERVER[' . $arg . ']: ' . $_SERVER[ $arg ] );
	} else {
		if($show_always) {
			dgx_donate_debug_log( '$_SERVER[' . $arg . ']: not set' );
		}
	}
}

function seamless_donations_backtrace_to_log() {

	$debug_log = get_option( 'dgx_donate_log' );

	if ( empty( $debug_log ) ) {
		$debug_log = array();
	}

	ob_start();
	debug_print_backtrace();
	$message = ob_end_clean();

	$debug_log[] = $message;

	update_option( 'dgx_donate_log', $debug_log );
}

function seamless_donations_force_a_backtrace_to_log() {

	seamless_donations_backtrace_to_log();
}


