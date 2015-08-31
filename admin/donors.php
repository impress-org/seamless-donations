<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// DONORS - TAB ////
function seamless_donations_admin_donors ( $setup_object ) {

	$display_tab = get_option ( 'dgx_donate_display_admin_donors_tab' );
	if( $display_tab === false ) {
		update_option ( 'dgx_donate_display_admin_donors_tab', 'show' );
		$display_tab = 'show';
	}

	if( $display_tab == 'show' ) {

		do_action ( 'seamless_donations_admin_donors_before', $setup_object );

		seamless_donations_admin_donors_menu ( $setup_object );
		seamless_donations_admin_donors_section_help ( $setup_object );

		do_action ( 'seamless_donations_admin_donors_after', $setup_object );

		add_filter (
			'validate_page_slug_seamless_donations_admin_donors',
			'validate_page_slug_seamless_donations_admin_donors_callback',
			10, // priority (for this, always 10)
			3 ); // number of arguments passed (for this, always 3)
	}
}

//// DONORS - MENU ////
function seamless_donations_admin_donors_menu ( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __ ( 'Donors', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_donors',
	);
	$sub_menu_array = apply_filters ( 'seamless_donations_admin_donors_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage ( $sub_menu_array );
}

//// DONORS - PROCESS ////
function validate_page_slug_seamless_donations_admin_donors_callback (
	$_submitted_array, $_existing_array, $_setup_object ) {

	$section = seamless_donations_get_submitted_admin_section ( $_submitted_array );

	// no real need for switch, but structured this way for easy expansion
	switch( $section ) {
		case 'seamless_donations_admin_donors_section_help':
			update_option ( 'dgx_donate_display_admin_donors_tab', 'hide' );
			$_setup_object->setSettingNotice ( 'Donors tab hidden. Restore using Settings Tab.', 'updated' );
			wp_redirect ( admin_url ( 'admin.php?page=seamless_donations_admin_main' ) );
			exit(); // required to make wp_redirect work
			break;
		case 'seamless_donations_admin_donors_section_extension': // LET EXTENSIONS DO THE PROCESSING
			break;
		default:
			$_setup_object->setSettingNotice (
				__ ( 'There was an unexpected error in your entry.', 'seamless-donations' ) );
	}
}

//// DONORS - SECTION - DATA ////
function seamless_donations_admin_donors_section_help ( $_setup_object ) {

	$help_section = array(
		'section_id' => 'seamless_donations_admin_donors_section_help',    // the section ID
		'page_slug'  => 'seamless_donations_admin_donors',    // the page slug that the section belongs to
		'title'      => __ ( 'Where Did Donors Go?', 'seamless-donations' ),   // the section title
	);
	$help_section = apply_filters ( 'seamless_donations_admin_donors_section_help', $help_section );

	$help_object = array(

		array(
			'field_id'     => 'random_html',
			'type'         => 'random_html',
			'before_field' => '<img src="' . plugins_url ( 'images/new-donor-location.jpg', dirname ( __FILE__ ) ) .
			                  '">',
			'title'        => __ ( 'To a menu on the Dashboard', 'seamless-donations' ),
			'description'  => __ (
				'Donors is now a custom post type available from the main dashboard.', 'seamless-donations' ),

		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Permanently Hide This Tab', 'seamless-donations' ),
		),
	);

	$help_object = apply_filters ( 'seamless_donations_admin_donations_section_help_options', $help_object );

	seamless_donations_process_add_settings_fields_with_options ( $help_object, $_setup_object, $help_section );
}