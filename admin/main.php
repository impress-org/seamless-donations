<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// MAIN - TAB ////
function seamless_donations_admin_main ( $setup_object ) {

	do_action ( 'seamless_donations_admin_main_before', $setup_object );

	seamless_donations_admin_main_menu ( $setup_object );
	seamless_donations_admin_main_section_data ( $setup_object );

	do_action ( 'seamless_donations_admin_main_after', $setup_object );
}

//// MAIN - MENU ////
function seamless_donations_admin_main_menu ( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __ ( 'Seamless Donations', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_main',
	);
	$sub_menu_array = apply_filters ( 'seamless_donations_admin_main_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage ( $sub_menu_array );
}

//// LOGS - SECTION - DATA ////
function seamless_donations_admin_main_section_data ( $_setup_object ) {

	$main_section = array(
		'section_id' => 'seamless_donations_admin_main_section_data',    // the section ID
		'page_slug'  => 'seamless_donations_admin_main',    // the page slug that the section belongs to
		//'title'      => __ ( 'Seamless Donations', 'seamless-donations' ),   // the section title
	);
	$main_section = apply_filters ( 'seamless_donations_admin_main_section_data', $main_section );

	$html_folder = dirname ( dirname ( __FILE__ ) ) . '/html/';
	$html_file   = $html_folder . 'admin-main.html';
	$html_readme = file_get_contents ( $html_file );

	$main_object = array(
		array(
			'field_id'     => 'welcome_information',
			'type'         => 'welcome_html',
			'before_field' => $html_readme,
		),

	);

	$main_object = apply_filters ( 'seamless_donations_admin_main_section_data_options', $main_object );

	seamless_donations_process_add_settings_fields_with_options ( $main_object, $_setup_object, $main_section );
}

function seamless_donations_admin_main_section_html () {
}