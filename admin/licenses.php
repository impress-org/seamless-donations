<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// LICENSES - TAB ////
function seamless_donations_admin_licenses ( $setup_object ) {

	do_action ( 'seamless_donations_admin_licenses_before', $setup_object );

	seamless_donations_admin_licenses_menu ( $setup_object );
	seamless_donations_admin_licenses_section_registration ( $setup_object );

	do_action ( 'seamless_donations_admin_licenses_after', $setup_object );

	add_filter (
		'validate_page_slug_seamless_donations_admin_licenses',
		'validate_page_slug_seamless_donations_admin_licenses_callback',
		10, // priority (for this, always 10)
		3 ); // number of arguments passed (for this, always 3)
}

//// LICENSES - MENU ////
function seamless_donations_admin_licenses_menu ( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __ ( 'Licenses', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_licenses',
	);
	$sub_menu_array = apply_filters ( 'seamless_donations_admin_licenses_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage ( $sub_menu_array );
}

//// SETTINGS - PROCESS ////
function validate_page_slug_seamless_donations_admin_licenses_callback (
	$_submitted_array, $_existing_array, $_setup_object ) {

	$_submitted_array = apply_filters (
		'validate_page_slug_seamless_donations_admin_licenses_callback',
		$_submitted_array, $_existing_array, $_setup_object );

	$section = seamless_donations_get_submitted_admin_section ( $_submitted_array );

	// no real need for switch, but structured this way for easy expansion
	switch( $section ) {
		case 'seamless_donations_admin_licenses_section_extension': // LET EXTENSIONS DO THE PROCESSING
			break;
		default:
			$_setup_object->setSettingNotice (
				__ ( 'There was an unexpected error in your entry.', 'seamless-donations' ) );
	}
}

//// LICENSES - SECTION - LICENSES ////
function seamless_donations_admin_licenses_section_registration ( $_setup_object ) {

	$section_desc = 'If you have purchased any premium extensions, you will be able to enter ';
	$section_desc .= 'their license keys here. Your active license key is required to run the extension ';
	$section_desc .= 'and will also enable you to get automatic updates for the duration of your license.';

	$licenses_registration_section = array(
		'section_id'  => 'seamless_donations_admin_licenses_section_registration',    // the section ID
		'page_slug'   => 'seamless_donations_admin_licenses',    // the page slug that the section belongs to
		'title'       => __ ( 'License Activation', 'seamless-donations' ),   // the section title
		'description' => __ ( $section_desc, 'seamless-donations' ),
	);

	$licenses_registration_section = apply_filters (
		'seamless_donations_admin_licenses_section_registration', $licenses_registration_section );

	$licenses_registration_options = array(
		array(
			'field_id'     => 'licenses_no_licenses',
			'title'        => __ ( 'Licenses', 'seamless-donations' ),
			'type'         => 'licenses_html',
			'before_field' => __ (
				'Nothing has been installed or activated that requires a license.', 'seamless-donations' ),
		),

	);

	$licenses_registration_options = apply_filters (
		'seamless_donations_admin_licenses_section_registration_options', $licenses_registration_options );

	seamless_donations_process_add_settings_fields_with_options (
		$licenses_registration_options, $_setup_object, $licenses_registration_section );
}
