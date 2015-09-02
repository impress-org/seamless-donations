<?php

// quick array name-of function
// from http://php.net/manual/en/function.key.php
function seamless_donations_name_of ( array $a, $pos ) {

	$temp = array_slice ( $a, $pos, 1, true );

	return key ( $temp );
}

// from http://www.w3schools.com/php/filter_validate_url.asp
// returns a clean URL or false
// use === false to check it
function seamless_donations_validate_url ( $url ) {

	// Remove all illegal characters from a url
	$url = filter_var ( $url, FILTER_SANITIZE_URL );

	// Validate url
	if( ! filter_var ( $url, FILTER_VALIDATE_URL ) === false ) {
		return $url;
	} else {
		return false;
	}
}

function seamless_donations_debug_alert ( $a ) {

	echo "<script>";
	echo 'alert("' . $a . '");';
	echo "</script>";
}

function seamless_donations_debug_log ( $a ) {

	echo "<script>";
	echo 'console.log("' . $a . '");';
	echo "</script>";
}

// This function builds both options and settings based on passed arrays
// The $options_array is an array that would be passed to the addSettingsField method
// If $settings_array is passed (not false), it will create a section and add the options to that section
function seamless_donations_process_add_settings_fields_with_options (
	$options_array, $apf_object, $settings_array = array() ) {

	if( count ( $settings_array ) > 0 ) {
		$apf_object->addSettingSections ( $settings_array );
		$section_id = $settings_array['section_id'];
	}

	for( $i = 0; $i < count ( $options_array ); ++ $i ) {

		// read in stored options
		// by using this approach, we don't need to special-case for
		// fields and field types that don't save option data
		$option = $options_array[ $i ]['field_id'];

		$stored_option = get_option ( $option, false );
		if( $stored_option != false ) {
			$options_array[ $i ]['default'] = $stored_option;
		}

		// build up the settings field display
		if( count ( $settings_array ) > 0 ) {
			$apf_object->addSettingFields ( $section_id, $options_array[ $i ] );
		} else {
			$apf_object->addSettingFields ( $options_array[ $i ] );
		}
	}
}

// scans the admin UI sections, looks for a 'submit' type field named 'submit' that has a non-null value
// this is admittedly less efficient than just picking values out of the array, but it makes for
// considerably easier-to-read code for admin form processing. Given that admin submits are relatively
// rare and the array scan is short, it's a fair trade-off for more maintainable code
function seamless_donations_get_submitted_admin_section ( $_the_array ) {

	$slug = $_POST['page_slug'];
	for( $i = 0; $i < count ( $_the_array ); ++ $i ) {
		$key = seamless_donations_name_of ( $_the_array, $i );
		if( strpos ( $key, $slug ) === 0 ) { // key begins with slug
			if( isset( $_the_array[ $key ]['submit'] ) ) {
				if( $_the_array[ $key ]['submit'] != NULL ) {
					return ( $key );
				}
			}
		}
	}

	return false;
}

function seamless_donations_get_guid ( $namespace = '' ) {

	// based on post by redtrader http://php.net/manual/en/function.uniqid.php#107512
	$guid = '';
	$uid  = uniqid ( "", true );
	$data = $namespace;
	$data .= isset( $_SERVER['REQUEST_TIME'] ) ? $_SERVER['REQUEST_TIME'] : '';
	$data .= isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$data .= isset( $_SERVER['LOCAL_ADDR'] ) ? $_SERVER['LOCAL_ADDR'] : '';
	$data .= isset( $_SERVER['LOCAL_PORT'] ) ? $_SERVER['LOCAL_PORT'] : '';
	$data .= isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	$data .= isset( $_SERVER['REMOTE_PORT'] ) ? $_SERVER['REMOTE_PORT'] : '';
	$hash = strtoupper ( hash ( 'ripemd128', $uid . $guid . md5 ( $data ) ) );
	$guid = substr ( $hash, 0, 8 ) .
	        '-' .
	        substr ( $hash, 8, 4 ) .
	        '-' .
	        substr ( $hash, 12, 4 ) .
	        '-' .
	        substr ( $hash, 16, 4 ) .
	        '-' .
	        substr ( $hash, 20, 12 );

	return $guid;
}

function seamless_donations_get_browser_name () {

	$path = plugin_dir_path ( __FILE__ );
	$path = dirname ( dirname ( dirname ( dirname ( $path ) ) ) ); // up the path (probably a better way)
	$path .= '/wp-admin/includes/dashboard.php';

	require_once ( $path );
	$browser_data = wp_check_browser_version ();

	isset( $browser_data['name'] ) ? $browser_name = $browser_data['name'] : $browser_name = '';
	isset( $browser_data['version'] ) ? $browser_version = $browser_data['version'] :
		$browser_version = '';

	return $browser_name . ' ' . $browser_version;
}

// *** EDD LICENSING ***

function seamless_donations_store_url () {

	return "http://zatzlabs.com";
}

function seamless_donations_get_license_key ( $item ) {

	$license_key   = '';
	$license_array = unserialize ( get_option ( 'dgxdonate_licenses' ) );
	if( isset( $license_array[ $item ] ) ) {
		$license_key = $license_array[ $item ];
	}

	return $license_key;
}

function seamless_donations_confirm_license_key($key) {
	if($key == '') return false;
	return true;
}

function seamless_donations_edd_activate_license ( $product, $license, $url ) {

	// retrieve the license from the database
	$license = trim ( $license );

	// Call the custom API.
	$response = wp_remote_get (
		add_query_arg (
			array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode ( $product ) // the name of our product in EDD
			),
			$url
		),
		array(
			'timeout'   => 15,
			'sslverify' => false
		)
	);

	// make sure the response came back okay
	if( is_wp_error ( $response ) ) {
		return false;
	}

	// decode the license data
	$license_data = json_decode ( wp_remote_retrieve_body ( $response ) );

	// $license_data->license will be either "active" or "inactive" <-- "valid"
	if( isset( $license_data->license ) && $license_data->license == 'active'
	    || $license_data->license == 'valid'
	) {
		return 'valid';
	}

	return 'invalid';
}

function seamless_donations_edd_deactivate_license ( $product, $license, $url ) {

	// retrieve the license from the database
	$license = trim ( $license );

	// Call the custom API.
	$response = wp_remote_get (
		add_query_arg (
			array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode ( $product ) // the name of our product in EDD
			),
			$url
		),
		array(
			'timeout'   => 15,
			'sslverify' => false
		)
	);

	// make sure the response came back okay
	if( is_wp_error ( $response ) ) {
		return false;
	}

	// decode the license data
	$license_data = json_decode ( wp_remote_retrieve_body ( $response ) );

	// $license_data->license will be either "active" or "inactive" <-- "valid"
	if( isset( $license_data->license ) && $license_data->license == 'deactivated' ) {
		return 'deactivated';
	}

	return 'invalid';
}