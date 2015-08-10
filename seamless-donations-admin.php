<?php

/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */


if( ! class_exists ( 'SeamlessDonationsAdminPageFramework' ) ) {
	include_once ( 'library/apf/admin-page-framework.php' );
}

// Extend the class
class SeamlessDonationsAdmin extends SeamlessDonationsAdminPageFramework {

	public function load_SeamlessDonationsAdmin () {

		// Display the plugin title
		add_filter ( "content_top_SeamlessDonationsAdmin", array( $this, 'seamless_donations_set_plugin_title' ) );
		add_filter ( "content_top_SeamlessDonationsAdmin", array( $this, 'seamless_donations_set_right_header' ) );
	}

	public function seamless_donations_set_plugin_title ( $filtered_content ) {

		$plugin_file       = dirname (  __FILE__ )  . '/seamless-donations.php';
		$plugin_data_array = get_plugin_data ( $plugin_file, false, false );

		$html = "<div class='seamless-donations-plugin-icon'>"
		        . "<div class='dashicons dashicons-palmtree seamless-donations-dashicon'></div>"
		        . "</div>"
		        . "<div class='seamless-donations-plugin-title'>"
		        . "<h1>" . $plugin_data_array['Name'] . "</h1>"
		        . "</div>";
		$html .= $filtered_content;

		return $html;
	}

	public function seamless_donations_set_right_header ( $filtered_content ) {

		$plugin_file       = dirname (  __FILE__ )  . '/seamless-donations.php';
		$plugin_data_array = get_plugin_data ( $plugin_file, false, false );

		$html = "<div class='seamless-donations-plugin-title-right' style=''>"
		        . $plugin_data_array['Name'] . " " . $plugin_data_array['Version']
		        . "</div>";
		$html .= $filtered_content;

		return $html;
	}

	public function setUp () {

		// Create the root menu
		// dash-icons are supported since WordPress v3.8
		$this->setRootMenuPage (
			'Seamless Donations',
			version_compare ( $GLOBALS['wp_version'], '3.8', '>=' ) ? 'dashicons-palmtree' : NULL
		);

		// instantiate the admin pages
		seamless_donations_admin_main ( $this );
		seamless_donations_admin_donations ( $this );
		seamless_donations_admin_donors ( $this );
		seamless_donations_admin_funds ( $this );
		seamless_donations_admin_templates ( $this );
		seamless_donations_admin_thanks ( $this );
		seamless_donations_admin_forms ( $this );
		seamless_donations_admin_settings ( $this );
		seamless_donations_admin_logs ( $this );
		//seamless_donations_admin_help ( $this );
	}

	public function validation_SeamlessDonationsAdmin ( $submitted_array, $existing_array, $setup_object ) {

		// whenever a submit button is pressed, this function calls a filter for that page
		// if that page's slug is seamless_donations_funds, it calls the filter:
		//    validate_page_slug_seamless_donations_funds with three parameters
		//      the array of data submitted with the form
		//      the array of data already in the database associated with the form
		//      the setup object, which is the class (for things like setting error messages)

		$slug          = $_GET['page'];
		$filter_name   = 'validate_page_slug_' . $slug;
		$filter_result = apply_filters ( $filter_name, $submitted_array, $existing_array, $setup_object );
		$setup_object->oForm->$slug;

		return $filter_result;
	}

	public function footer_left_SeamlessDonationsAdmin ( $sHTML ) {

		return "";
	}

	public function footer_right_SeamlessDonationsAdmin ( $sHTML ) {

		return "";
	}
}

// Instantiate the class object.
if( is_admin () ) {
	if( get_option ( 'dgx_donate_start_in_sd4_mode' ) != false ) {
		new SeamlessDonationsAdmin;
	}
}

