<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// ADDONS - TAB ////

function seamless_donations_admin_addons ( $setup_object ) {

	do_action ( 'seamless_donations_admin_addons_before', $setup_object );

	seamless_donations_admin_addons_menu ( $setup_object );
	seamless_donations_admin_addons_section_data ( $setup_object );

	do_action ( 'seamless_donations_admin_addons_after', $setup_object );
}

//// ADDONS - MENU ////
function seamless_donations_admin_addons_menu ( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __ ( 'Add-ons', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_addons',
	);
	$sub_menu_array = apply_filters ( 'seamless_donations_admin_addons_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage ( $sub_menu_array );
}

//// LOGS - SECTION - DATA ////
function seamless_donations_admin_addons_section_data ( $_setup_object ) {

	$addons_section = array(
		'section_id' => 'seamless_donations_admin_addons_section_data',    // the section ID
		'page_slug'  => 'seamless_donations_admin_addons',    // the page slug that the section belongs to
		//'title'      => __ ( 'Seamless Donations', 'seamless-donations' ),   // the section title
	);
	$addons_section = apply_filters ( 'seamless_donations_admin_addons_section_data', $addons_section );

	$html_folder = dirname ( dirname ( __FILE__ ) ) . '/html/';
	$html_file   = $html_folder . 'admin-addons.html';
	$html_readme = file_get_contents ( $html_file );

	$addons_object = array(
		array(
			'field_id'     => 'admin_information',
			'type'         => 'welcome_html',
			'before_field' => $html_readme,
		),

	);

	$addons_object = apply_filters ( 'seamless_donations_admin_addons_section_data_options', $addons_object );

	seamless_donations_process_add_settings_fields_with_options ( $addons_object, $_setup_object, $addons_section );
}

function seamless_donations_admin_addons_section_html () {
}