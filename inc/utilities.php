<?php

/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

// quick array name-of function
// from http://php.net/manual/en/function.key.php
function seamless_donations_name_of( array $a, $pos ) {

	$temp = array_slice( $a, $pos, 1, true );

	return key( $temp );
}

// from http://www.w3schools.com/php/filter_validate_url.asp
// returns a clean URL or false
// use === false to check it
function seamless_donations_validate_url( $url ) {

	// Remove all illegal characters from a url
	$url = filter_var( $url, FILTER_SANITIZE_URL );

	// Validate url
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
		return $url;
	} else {
		return false;
	}
}

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

function seamless_donations_obscurify_string( $s, $char = '*', $inner_obscure = true ) {

	$length = strlen( $s );
	if ( $length > 6 ) {
		$segment_size = intval( $length / 3 );
		$seg1         = substr( $s, 0, $segment_size );
		$seg2         = substr( $s, $segment_size, $segment_size );
		$seg3         = substr( $s, $segment_size * 2, $length - ( $segment_size * 2 ) );

		if ( $inner_obscure ) {
			$seg2 = str_repeat( $char, $segment_size );
		} else {
			$seg1 = str_repeat( $char, $segment_size );
			$seg3 = str_repeat( $char, strlen( $seg3 ) );
		}

		$s = $seg1 . $seg2 . $seg3;
	}

	return $s;
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

function seamless_donations_version_compare( $ver1, $ver2 ) {

	// returns > if 1 > 2, = if 1 = 2, < if 1 < 2
	// 4.0.2 is greater than 3.1.1, but 4.0 and 4.0.0 are equal

	$p    = '/[^0-9.]/i'; // remove all alphanumerics
	$ver1 = preg_replace( $p, '', $ver1 );
	$ver2 = preg_replace( $p, '', $ver2 );

	$v1 = explode( '.', $ver1 );
	$v2 = explode( '.', $ver2 );

	// make the two arrays counts match
	$most = max( array( count( $v1 ), count( $v2 ) ) );
	$v1   = array_pad( $v1, $most, '0' );
	$v2   = array_pad( $v2, $most, '0' );

	for ( $i = 0; $i < count( $v1 ); ++ $i ) {
		if ( intval( $v1[ $i ] ) > intval( $v2[ $i ] ) ) {
			return '>';
		}
		if ( intval( $v1[ $i ] ) < intval( $v2[ $i ] ) ) {
			return '<';
		}
	}

	return '=';
}

// This function builds both options and settings based on passed arrays
// The $options_array is an array that would be passed to the addSettingsField method
// If $settings_array is passed (not false), it will create a section and add the options to that section
function seamless_donations_process_add_settings_fields_with_options(
	$options_array, $apf_object, $settings_array = array()
) {

	if ( count( $settings_array ) > 0 ) {
		$apf_object->addSettingSections( $settings_array );
		$section_id = $settings_array['section_id'];
	}

	for ( $i = 0; $i < count( $options_array ); ++ $i ) {

		// read in stored options
		// by using this approach, we don't need to special-case for
		// fields and field types that don't save option data
		$option = $options_array[ $i ]['field_id'];

		$stored_option = get_option( $option, false );
		if ( $stored_option != false ) {
			$options_array[ $i ]['default'] = $stored_option;
		}

		// build up the settings field display
		if ( count( $settings_array ) > 0 ) {
			$apf_object->addSettingFields( $section_id, $options_array[ $i ] );
		} else {
			$apf_object->addSettingFields( $options_array[ $i ] );
		}
	}
}

// scans the admin UI sections, looks for a 'submit' type field named 'submit' that has a non-null value
// this is admittedly less efficient than just picking values out of the array, but it makes for
// considerably easier-to-read code for admin form processing. Given that admin submits are relatively
// rare and the array scan is short, it's a fair trade-off for more maintainable code
function seamless_donations_get_submitted_admin_section( $_the_array ) {

	$slug = $_POST['page_slug'];
	for ( $i = 0; $i < count( $_the_array ); ++ $i ) {
		$key = seamless_donations_name_of( $_the_array, $i );
		if ( strpos( $key, $slug ) === 0 ) { // key begins with slug
			if ( isset( $_the_array[ $key ]['submit'] ) ) {
				if ( $_the_array[ $key ]['submit'] != NULL ) {
					return ( $key );
				}
			}
		}
	}

	return false;
}

function seamless_donations_get_guid( $namespace = '' ) {

	$ver = 'SDS01-'; // Session IDs now have versioning SD=Seamless Donations, S=Server, 01=first version

	// based on post by redtrader http://php.net/manual/en/function.uniqid.php#107512
	$guid = '';
	$uid  = uniqid( "", true );
	$data = $namespace;
	$data .= isset( $_SERVER['REQUEST_TIME'] ) ? $_SERVER['REQUEST_TIME'] : '';
	$data .= isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$data .= isset( $_SERVER['LOCAL_ADDR'] ) ? $_SERVER['LOCAL_ADDR'] : '';
	$data .= isset( $_SERVER['LOCAL_PORT'] ) ? $_SERVER['LOCAL_PORT'] : '';
	$data .= isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	$data .= isset( $_SERVER['REMOTE_PORT'] ) ? $_SERVER['REMOTE_PORT'] : '';
	$hash = strtoupper( hash( 'ripemd128', $uid . $guid . md5( $data ) ) );
	$guid = substr( $hash, 0, 8 ) . '-' . substr( $hash, 8, 4 ) . '-' . substr( $hash, 12, 4 ) . '-' .
	        substr( $hash, 16, 4 ) . '-' .
	        substr( $hash, 20, 12 );

	return $ver . $guid;
}

function seamless_donations_get_browser_name() {

	$path = plugin_dir_path( __FILE__ );
	$path = dirname( dirname( dirname( dirname( $path ) ) ) ); // up the path (probably a better way)
	$path .= '/wp-admin/includes/dashboard.php';

	require_once( $path );
	$browser_data = wp_check_browser_version();

	isset( $browser_data['name'] ) ? $browser_name = $browser_data['name'] : $browser_name = '';
	isset( $browser_data['version'] ) ? $browser_version = $browser_data['version'] : $browser_version = '';

	return $browser_name . ' ' . $browser_version;
}

// label display functions

function seamless_donations_get_feature_promo( $desc, $url, $upgrade = "UPGRADE" ) {

	$feature_desc = sanitize_text_field( htmlspecialchars( $desc ) );

	$promo = '<br>';
	$promo .= '<span style="background-color:DarkGoldenRod; color:white;font-style:normal;text-weight:bold">';
	$promo .= '&nbsp;' . $upgrade . ':&nbsp;';
	$promo .= '</span>';
	$promo .= '<span style="color:DarkGoldenRod;font-style:normal;">';
	$promo .= '&nbsp;' . $feature_desc . ' ';
	$promo .= '<A target="_blank" HREF="' . $url . '">Learn more.</A>';
	$promo .= '</span>';

	return $promo;
}

function seamless_donations_display_label( $before = '&nbsp;', $message = 'BETA', $after = '' ) {

	$label = $before . '<span style="background-color:darkgrey; color:white;font-style:normal;text-weight:bold">';
	$label .= '&nbsp;' . $message . '&nbsp;';
	$label .= '</span>' . $after;

	return $label;
}

// *** DATABASE REBUILD ***

function seamless_donations_rebuild_funds_index() {

	// first clear out the donations meta items
	$args        = array(
		'post_type'   => 'funds',
		'post_status' => 'publish',
		'nopaging'    => 'true',
	);
	$posts_array = get_posts( $args );

	// loop through a list of funds
	for ( $i = 0; $i < count( $posts_array ); ++ $i ) {

		// extract the fund id from the donation and fund records
		$fund_id = $posts_array[ $i ]->ID;
		delete_post_meta( $fund_id, '_dgx_donate_donor_donations' );
		delete_post_meta( $fund_id, '_dgx_donate_fund_total' );
	}

	// then loop through the donations

	$args = array(
		'post_type'   => 'donation',
		'post_status' => 'publish',
		'nopaging'    => 'true',
	);

	$posts_array = get_posts( $args );

	// loop through a list of donations with funds attached
	for ( $i = 0; $i < count( $posts_array ); ++ $i ) {

		// extract the fund id from the donation and fund records
		$donation_id = $posts_array[ $i ]->ID;
		$fund_name   = get_post_meta( $donation_id, '_dgx_donate_designated_fund', true );

		if ( $fund_name != '' ) {
			// todo need additional code to go in and reconstruct ids based on possible new names
			$fund    = get_page_by_title( $fund_name, 'OBJECT', 'funds' );
			$fund_id = $fund->ID;

			// update the donation record with the fund id -- also link the funds to the donations
			update_post_meta( $donation_id, '_dgx_donate_designated_fund_id', $fund_id );

			// update the donations list to point to this donation id
			seamless_donations_add_donation_id_to_fund( $fund_id, $donation_id );

			// update the donation total for the fund
			seamless_donations_add_donation_amount_to_fund_total( $donation_id, $fund_id );
		}
	}
}

function seamless_donations_recalculate_fund_total( $fund_id ) {

	$fund_total = 0.0;

	$donations_list       = get_post_meta( $fund_id, '_dgx_donate_donor_donations', true );
	$donations_list_array = explode( ',', $donations_list );

	for ( $i = 0; $i < count( $donations_list_array ); ++ $i ) {
		if ( $donations_list_array[ $i ] != '' ) {
			$donation_id     = $donations_list_array[ $i ];
			$donation_amount = get_post_meta( $donation_id, '_dgx_donate_amount', true );
			if ( $donation_amount != '' ) {
				$donation_amount = floatval( $donation_amount );
				$fund_total += $donation_amount;
			}
		}
	}
	$fund_total = strval( $fund_total );
	update_post_meta( $fund_id, '_dgx_donate_fund_total', $fund_total );
}

function seamless_donations_rebuild_donor_index() {

	// first clear out the donations meta items
	$args        = array(
		'post_type'   => 'donor',
		'post_status' => 'publish',
		'nopaging'    => 'true',
	);
	$posts_array = get_posts( $args );

	// loop through a list of donors
	for ( $i = 0; $i < count( $posts_array ); ++ $i ) {

		// extract the donor id from the donation and fund records
		$donor_id = $posts_array[ $i ]->ID;
		delete_post_meta( $donor_id, '_dgx_donate_donor_donations' );
		delete_post_meta( $donor_id, '_dgx_donate_donor_total' );
	}

	// then loop through the donations

	$args = array(
		'post_type'   => 'donation',
		'post_status' => 'publish',
		'nopaging'    => 'true',
	);

	$posts_array = get_posts( $args );

	// loop through a list of donations with funds attached
	for ( $i = 0; $i < count( $posts_array ); ++ $i ) {

		// extract the donor id from the donation and donor records
		$donation_id = $posts_array[ $i ]->ID;
		$first       = get_post_meta( $donation_id, '_dgx_donate_donor_first_name', true );
		$last        = get_post_meta( $donation_id, '_dgx_donate_donor_last_name', true );

		// now move that data into a donor post type
		$donor_name = sanitize_text_field( $first . ' ' . $last );

		if ( $donor_name != '' ) {
			// this code, like in funds, assumes the names haven't been changed.
			// todo need additional code to go in and reconstruct ids based on possible new names
			$donor    = get_page_by_title( $donor_name, 'OBJECT', 'donor' );
			$donor_id = $donor->ID;

			// update the donation record with the donor id -- also link the donor to the donations
			update_post_meta( $donation_id, '_dgx_donate_donor_id', $donor_id );

			// update the donations list to point to this donation id
			seamless_donations_add_donation_id_to_donor( $donation_id, $donor_id );

			// update the donation total for the donor
			seamless_donations_add_donation_amount_to_donor_total( $donation_id, $donor_id );
		}
	}
}

function seamless_donations_rebuild_donor_anon_flag() {

	// first clear out the donations meta items
	$args        = array(
		'post_type'   => 'donor',
		'post_status' => 'publish',
		'nopaging'    => 'true',
	);
	$posts_array = get_posts( $args );

	// loop through a list of donors
	for ( $i = 0; $i < count( $posts_array ); ++ $i ) {

		// set all donors to anonymous = no
		$donor_id = $posts_array[ $i ]->ID;
		update_post_meta( $donor_id, '_dgx_donate_anonymous', 'no' );
	}

	// then loop through the donations

	$args = array(
		'post_type'   => 'donation',
		'post_status' => 'publish',
		'nopaging'    => 'true',
	);

	$posts_array = get_posts( $args );

	// loop through a list of donations
	for ( $i = 0; $i < count( $posts_array ); ++ $i ) {

		// extract the donor id from the donation and donor records
		$donation_id = $posts_array[ $i ]->ID;
		$first       = get_post_meta( $donation_id, '_dgx_donate_donor_first_name', true );
		$last        = get_post_meta( $donation_id, '_dgx_donate_donor_last_name', true );
		$anon        = get_post_meta( $donation_id, '_dgx_donate_anonymous', true );

		// now move that data into a donor post type
		$donor_name = sanitize_text_field( $first . ' ' . $last );

		if ( $anon == 'on' ) {
			// this code, like in funds, assumes the names haven't been changed.
			// todo need additional code to go in and reconstruct ids based on possible new names
			$donor    = get_page_by_title( $donor_name, 'OBJECT', 'donor' );
			$donor_id = $donor->ID;

			update_post_meta( $donor_id, '_dgx_donate_anonymous', 'yes' );
		}
	}
}

function seamless_donations_recalculate_donor_total( $donor_id ) {

	$donor_total = 0.0;

	$donations_list       = get_post_meta( $donor_id, '_dgx_donate_donor_donations', true );
	$donations_list_array = explode( ',', $donations_list );

	for ( $i = 0; $i < count( $donations_list_array ); ++ $i ) {
		if ( $donations_list_array[ $i ] != '' ) {
			$donation_id     = $donations_list_array[ $i ];
			$donation_amount = get_post_meta( $donation_id, '_dgx_donate_amount', true );
			if ( $donation_amount != '' ) {
				$donation_amount = floatval( $donation_amount );
				$donor_total += $donation_amount;
			}
		}
	}
	$donor_total = strval( $donor_total );
	update_post_meta( $donor_id, '_dgx_donate_fund_total', $donor_total );
}

// *** EDD LICENSING ***

function seamless_donations_store_url() {

	return "http://zatzlabs.com";
}

function seamless_donations_get_license_key( $item ) {

	$license_key   = '';
	$license_array = unserialize( get_option( 'dgxdonate_licenses' ) );
	if ( isset( $license_array[ $item ] ) ) {
		$license_key = $license_array[ $item ];
	}

	return $license_key;
}

function seamless_donations_confirm_license_key( $key ) {

	if ( $key == '' ) {
		return false;
	}

	return true;
}

function seamless_donations_edd_activate_license( $product, $license, $url ) {

	dgx_donate_debug_log( '----------------------------------------' );
	dgx_donate_debug_log( 'LICENSE ACTIVATION STARTED' );

	// retrieve the license from the database
	$license = trim( $license );
	dgx_donate_debug_log( 'Product: ' . $product );
	dgx_donate_debug_log( 'License key: ' . seamless_donations_obscurify_string( $license ) );

	// Call the custom API.
	$response = wp_remote_get(
		add_query_arg(
			array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( $product ) // the name of our product in EDD
			), $url ), array(
			'timeout'   => 15,
			'sslverify' => false,
		) );

	// make sure the response came back okay
	if ( is_wp_error( $response ) ) {
		dgx_donate_debug_log( 'Response error detected: ' . $response->get_error_message() );

		return false;
	}

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// $license_data->license will be either "active" or "inactive" <-- "valid"
	if ( isset( $license_data->license ) && $license_data->license == 'active' || $license_data->license == 'valid' ) {
		dgx_donate_debug_log( 'License check value: ' . $license_data->license );
		dgx_donate_debug_log( 'License check returning valid.' );

		return 'valid';
	}

	dgx_donate_debug_log( 'License check returning invalid.' );

	return 'invalid';
}

function seamless_donations_edd_deactivate_license( $product, $license, $url ) {

	dgx_donate_debug_log( '----------------------------------------' );
	dgx_donate_debug_log( 'LICENSE DEACTIVATION STARTED' );

	// retrieve the license from the database

	$license = trim( $license );
	dgx_donate_debug_log( 'Product: ' . $product );
	dgx_donate_debug_log( 'License key: ' . seamless_donations_obscurify_string( $license ) );

	// Call the custom API.
	$response = wp_remote_get(
		add_query_arg(
			array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $product ) // the name of our product in EDD
			), $url ), array(
			'timeout'   => 15,
			'sslverify' => false,
		) );

	// make sure the response came back okay
	if ( is_wp_error( $response ) ) {
		dgx_donate_debug_log( 'Response error detected: ' . $response->get_error_message() );

		return false;
	}

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	// $license_data->license will be either "active" or "inactive" <-- "valid"
	if ( isset( $license_data->license ) && $license_data->license == 'deactivated' ) {
		dgx_donate_debug_log( 'License check value: ' . $license_data->license );
		dgx_donate_debug_log( 'License check returning deactivated.' );

		return 'deactivated';
	}

	dgx_donate_debug_log( 'License check returning invalid.' );

	return 'invalid';
}