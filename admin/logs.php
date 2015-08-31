<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// LOGS - TAB ////
function seamless_donations_admin_logs ( $setup_object ) {

	do_action ( 'seamless_donations_admin_logs_before', $setup_object );

	seamless_donations_admin_logs_menu ( $setup_object );
	seamless_donations_admin_logs_section_data ( $setup_object );

	do_action ( 'seamless_donations_admin_logs_after', $setup_object );

	add_filter (
		'validate_page_slug_seamless_donations_admin_logs',
		'validate_page_slug_seamless_donations_admin_logs_callback',
		10, // priority (for this, always 10)
		3 ); // number of arguments passed (for this, always 3)
}

//// LOGS - MENU ////
function seamless_donations_admin_logs_menu ( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __ ( 'Log', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_logs',
	);
	$sub_menu_array = apply_filters ( 'seamless_donations_admin_logs_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage ( $sub_menu_array );
}

//// LOGS - PROCESS ////
function validate_page_slug_seamless_donations_admin_logs_callback (
	$_submitted_array, $_existing_array, $_setup_object ) {

	$section = seamless_donations_get_submitted_admin_section ( $_submitted_array );

	// no real need for switch, but structured this way for easy expansion
	switch( $section ) {
		case 'seamless_donations_admin_logs_section_data':
			delete_option ( 'dgx_donate_log' );
			$_setup_object->setSettingNotice ( 'Log data cleared.', 'updated' );
			break;
		case 'seamless_donations_admin_log_section_extension': // LET EXTENSIONS DO THE PROCESSING
			break;
		default:
			$_setup_object->setSettingNotice (
				__ ( 'There was an unexpected error in your entry.', 'seamless-donations' ) );
	}
}

//// LOGS - SECTION - DATA ////
function seamless_donations_admin_logs_section_data ( $_setup_object ) {

	$log_section = array(
		'section_id' => 'seamless_donations_admin_logs_section_data',    // the section ID
		'page_slug'  => 'seamless_donations_admin_logs',    // the page slug that the section belongs to
		'title'      => __ ( 'Log Data', 'seamless-donations' ),   // the section title
	);
	$log_section = apply_filters ( 'seamless_donations_admin_logs_section_data', $log_section );

	$debug_log_content = get_option ( 'dgx_donate_log' );
	$log_data          = '';

	if( empty( $debug_log_content ) ) {
		$log_data = esc_html__ ( 'The log is empty.', 'seamless-donations' );
	} else {
		foreach( $debug_log_content as $debug_log_entry ) {
			if( $log_data != "" ) {
				$log_data .= "\n";
			}
			$log_data .= esc_html ( $debug_log_entry );
		}
	}

	$debug_mode = get_option ( 'dgx_donate_debug_mode' );
	if( $debug_mode == 1 ) {
		// we're in debug, so we'll return lots of log info

		$display_options = array(
			__ ( 'Seamless Donations Log Data', 'seamless-donations' ) => $log_data,
			// Removes the default data by passing an empty value below.
			'Admin Page Framework'                                     => '',
			'Browser'                                                  => '',
		);
	} else {
		$display_options = array(
			__ ( 'Seamless Donations Log Data', 'seamless-donations' ) => $log_data,
			// Removes the default data by passing an empty value below.
			'Admin Page Framework'                                     => '',
			'WordPress'                                                => '',
			'PHP'                                                      => '',
			'Server'                                                   => '',
			'PHP Error Log'                                            => '',
			'MySQL'                                                    => '',
			'MySQL Error Log'                                          => '',
			'Browser'                                                  => '',
		);
	}

	$log_object = array(
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Delete Log', 'seamless-donations' ),
		),
		array(
			'field_id'   => 'system_information',
			'type'       => 'system',
			'title'      => __ ( 'System Information', 'seamless-donations' ),
			'data'       => $display_options,
			'attributes' => array(
				'name' => '',
			),
		)
	);

	$log_object = apply_filters ( 'seamless_donations_admin_logs_section_data_options', $log_object );

	seamless_donations_process_add_settings_fields_with_options ( $log_object, $_setup_object, $log_section );
}
