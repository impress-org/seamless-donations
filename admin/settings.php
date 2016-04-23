<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// SETTINGS - TAB ////
function seamless_donations_admin_settings( $setup_object ) {

	do_action( 'seamless_donations_admin_settings_before', $setup_object );

	// create the admin tab menu
	seamless_donations_admin_settings_menu( $setup_object );

	// create the sections
	seamless_donations_admin_settings_section_emails( $setup_object );
	seamless_donations_admin_settings_section_paypal( $setup_object );
	seamless_donations_admin_settings_section_hosts( $setup_object );

	do_action( 'seamless_donations_admin_settings_before_tweaks', $setup_object );

	seamless_donations_admin_settings_section_tweaks( $setup_object );
	seamless_donations_admin_settings_section_tabs( $setup_object );
	seamless_donations_admin_settings_section_debug( $setup_object );

	do_action( 'seamless_donations_admin_settings_after', $setup_object );

	add_filter(
		'validate_page_slug_seamless_donations_admin_settings',
		'validate_page_slug_seamless_donations_admin_settings_callback', 10,
		// priority (for this, always 10)
		3 ); // number of arguments passed (for this, always 3)
}

//// SETTINGS - MENU ////
function seamless_donations_admin_settings_menu( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __( 'Settings', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_settings',
	);
	$sub_menu_array = apply_filters( 'seamless_donations_admin_settings_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage( $sub_menu_array );
}

//// SETTINGS - PROCESS ////
function validate_page_slug_seamless_donations_admin_settings_callback(
	$_submitted_array, $_existing_array, $_setup_object
) {

	$_submitted_array = apply_filters(
		'validate_page_slug_seamless_donations_admin_settings_callback', $_submitted_array, $_existing_array,
		$_setup_object );

	$section = seamless_donations_get_submitted_admin_section( $_submitted_array );

	switch ( $section ) {
		case 'seamless_donations_admin_settings_section_emails': // SAVE EMAILS //
			$email_list        = $_submitted_array[ $section ]['dgx_donate_notify_emails'];
			$email_array       = explode( ',', $email_list );
			$clean_email_array = array();
			foreach ( $email_array as $email ) {
				$email = trim( $email );
				$email = sanitize_email( $email );
				array_push( $clean_email_array, $email );
				if ( ! is_email( $email ) ) {
					$_aErrors[ $section ]['dgx_donate_notify_emails'] = __(
						'Valid email address required.', 'seamless-donations' );
					$_setup_object->setFieldErrors( $_aErrors );
					$_setup_object->setSettingNotice(
						__( 'There were errors in your submission.', 'seamless-donations' ) );

					return $_existing_array;
				}
			}
			$email_list = implode( ',', $clean_email_array );
			update_option( 'dgx_donate_notify_emails', $email_list );
			$_setup_object->setSettingNotice( 'Form updated successfully.', 'updated' );
			break;
		case 'seamless_donations_admin_settings_section_paypal': // SAVE PAYPAL //
			$email  = $_submitted_array[ $section ]['dgx_donate_paypal_email'];
			$email  = sanitize_email( $email );
			$option = $_submitted_array[ $section ]['dgx_donate_paypal_server'];
			if ( ! is_email( $email ) ) {
				$_aErrors[ $section ]['dgx_donate_paypal_email'] = __(
					'Valid email address required.', 'seamless-donations' );
				$_setup_object->setFieldErrors( $_aErrors );
				$_setup_object->setSettingNotice(
					__( 'There were errors in your submission.', 'seamless-donations' ) );

				return $_existing_array;
			}
			update_option( 'dgx_donate_paypal_email', $email );
			update_option( 'dgx_donate_paypal_server', $option );
			$_setup_object->setSettingNotice( 'Form updated successfully.', 'updated' );
			break;
		case 'seamless_donations_admin_settings_section_hosts': // SAVE HOSTS //
			$settings_notice = 'Form updated successfully.';
			update_option( 'dgx_donate_form_via_action', $_submitted_array[ $section ]['dgx_donate_form_via_action'] );
			update_option( 'dgx_donate_browser_uuid', $_submitted_array[ $section ]['dgx_donate_browser_uuid'] );
			update_option( 'dgx_donate_ignore_form_nonce',
			               $_submitted_array[ $section ]['dgx_donate_ignore_form_nonce'] );
			$_setup_object->setSettingNotice( $settings_notice, 'updated' );
			break;
		case 'seamless_donations_admin_settings_section_tweaks': // SAVE TWEAKS //
			$settings_notice = 'Form updated successfully.';
			update_option( 'dgx_donate_compact_menus', $_submitted_array[ $section ]['dgx_donate_compact_menus'] );
			$_setup_object->setSettingNotice( $settings_notice, 'updated' );
			break;
		case 'seamless_donations_admin_settings_section_tabs': // SAVE TABS //
			update_option( 'dgx_donate_display_admin_donors_tab', 'show' );
			update_option( 'dgx_donate_display_admin_donations_tab', 'show' );
			update_option( 'dgx_donate_display_admin_funds_tab', 'show' );
			$_setup_object->setSettingNotice( 'Form updated successfully.', 'updated' );
			break;
		case 'seamless_donations_admin_settings_section_debug': // SAVE DEBUG //
			$settings_notice = 'Form updated successfully.';
			update_option( 'dgx_donate_debug_mode', $_submitted_array[ $section ]['dgx_donate_debug_mode'] );
			update_option( 'dgx_donate_log_obscure_name', $_submitted_array[ $section ]['dgx_donate_log_settings'][0] );
			if ( $_submitted_array[ $section ]['dgx_donate_rebuild_xref_by_name'] == "1" ) {
				dgx_donate_debug_log( '----------------------------------------' );
				dgx_donate_debug_log( 'INDEX CROSS-REFERENCE ATTEMPTED' );
				seamless_donations_rebuild_donor_index();
				seamless_donations_rebuild_funds_index();
				seamless_donations_rebuild_donor_anon_flag();
				$settings_notice .= ' Cross-reference index rebuild by name complete.';
				dgx_donate_debug_log( 'Cross-reference index rebuild by name complete.' );
			}
			$_setup_object->setSettingNotice( $settings_notice, 'updated' );
			break;
		case 'seamless_donations_admin_settings_section_extension': // LET EXTENSIONS DO THE PROCESSING
			break;
		default:
			$_setup_object->setSettingNotice(
				__( 'There was an unexpected error in your entry.', 'seamless-donations' ) );
	}
}

//// SETTINGS - SECTION - EMAILS ////
function seamless_donations_admin_settings_section_emails( $_setup_object ) {

	// Test email section
	$section_desc = 'Enter one or more emails that should be notified when a new donation arrives. ';
	$section_desc .= 'You can separate multiple email addresses with commas.';

	$settings_emails_section
		                     = array(
		'section_id'  => 'seamless_donations_admin_settings_section_emails',    // the section ID
		'page_slug'   => 'seamless_donations_admin_settings',    // the page slug that the section belongs to
		'title'       => __( 'Notification Emails', 'seamless-donations' ),   // the section title
		'description' => __( $section_desc, 'seamless-donations' ),
	);
	$settings_emails_section = apply_filters(
		'seamless_donations_admin_settings_section_emails', $settings_emails_section );

	$settings_emails_options = array(
		array(
			'field_id'    => 'dgx_donate_notify_emails',
			'type'        => 'text',
			'title'       => __( 'Notification Email Address(es)', 'seamless-donations' ),
			'description' => __(
				'Email address(es) that should be notified (e.g. administrators) of new donations.',
				'seamless-donations' ),
			'attributes'  => array(
				'size' => 80,
			),
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __( 'Update', 'seamless-donations' ),
		),
	);
	$settings_emails_options = apply_filters(
		'seamless_donations_admin_settings_section_emails_options', $settings_emails_options );

	seamless_donations_process_add_settings_fields_with_options(
		$settings_emails_options, $_setup_object, $settings_emails_section );
}

//// SETTINGS - SECTION - PAYPAL ////
function seamless_donations_admin_settings_section_paypal( $_setup_object ) {

	// Test email section
	$section_desc = 'Set up your PayPal deposit information. ';
	$section_desc .= '<span style="color:blue">Confused about setting up PayPal? ' . '</span>';
	$section_desc .= '<A HREF="https://youtu.be/n8z0ejIEowo"><span style="color:blue">';
	$section_desc .= 'Watch this video tutorial.</span></A>';

	// the following code is indicative of a minor architectural flaw in Seamless Donations
	// in that all admin pages are always instantiated. The approach doesn't seem to cause
	// too much of a load, except for the following, which calls the IPN processor.
	// This poorly optimized approach is being left in because callbacks might have been
	// used by user code that expected this behavior and changing it could cause breakage
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $_REQUEST['page'] == 'seamless_donations_admin_settings' ) {
			$security = seamless_donations_get_security_status();
			$section_desc .= seamless_donations_display_security_status( $security );
		}
	}

	$settings_paypal_section
		= array(
		'section_id'  => 'seamless_donations_admin_settings_section_paypal',    // the section ID
		'page_slug'   => 'seamless_donations_admin_settings',    // the page slug that the section belongs to
		'title'       => __( 'PayPal Settings', 'seamless-donations' ),   // the section title
		'description' => __( $section_desc, 'seamless-donations' ),
	);

	$form_display_options = array(
		'LIVE'    => 'Live (Production Server)',
		'SANDBOX' => 'Sandbox (Test Server)',
	);

	$http_ipn_url  = plugins_url( '/dgx-donate-paypalstd-ipn.php', dirname( __FILE__ ) );
	$https_ipn_url = plugins_url( '/pay/paypalstd/ipn.php', dirname( __FILE__ ) );
	$https_ipn_url = str_ireplace( 'http://', 'https://', $https_ipn_url ); // force https check

	$settings_paypal_section = apply_filters(
		'seamless_donations_admin_settings_section_paypal', $settings_paypal_section );

	$settings_paypal_options = array(
		array(
			'field_id'    => 'dgx_donate_paypal_email',
			'type'        => 'text',
			'title'       => __( 'PayPal Email Address', 'seamless-donations' ),
			'description' => __(
				'The email address at which to receive payments. Be sure to change this when you change from Sandbox to Live.',
				'seamless-donations' ),
			'attributes'  => array(
				'size' => 40,
			),
		),
		array(
			'field_id' => 'dgx_donate_paypal_server',
			'title'    => __( 'PayPal Interface Mode', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'LIVE', // the index key of the label array below
			'label'    => $form_display_options,
		),
		array(
			'field_id'     => 'settings_paypal_ipn_https_url',
			'title'        => __( 'PayPal IPN URL (https)', 'seamless-donations' ),
			'type'         => 'ipn_url_html',
			'description'  => __(
				'This is the SSL-compliant URL you should use with PayPal once you have a valid SSL certificate installed.' ),
			'before_field' => $https_ipn_url,
		),
		array(
			'field_id'     => 'settings_paypal_ipn_url',
			'title'        => __( 'PayPal IPN URL (old)', 'seamless-donations' ),
			'type'         => 'ipn_url_html',
			'description'  => __(
				'<span style=\'color:red\'>YOU SHOULD NO LONGER USE THIS. This is the non-https IPN. This may not work in the Sandbox and will definitely not work on live sites after September 30, 2016.</span>' ),
			'before_field' => $http_ipn_url,
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __( 'Save PayPal Settings', 'seamless-donations' ),
		),
	);
	$settings_paypal_options = apply_filters(
		'seamless_donations_admin_settings_section_paypal_options', $settings_paypal_options );

	seamless_donations_process_add_settings_fields_with_options(
		$settings_paypal_options, $_setup_object, $settings_paypal_section );
}

//// SETTINGS - SECTION - HOSTS ////
function seamless_donations_admin_settings_section_hosts( $_setup_object ) {

	$section_desc = 'Options that can help increase compatibility with your hosting provider.';
	$section_desc .= ' Details on what these options do can be found in ';
	$section_desc .= "<A HREF='http://zatzlabs.com/all-hosts-are-different/'>this Lab Note</A>.";

	$hosts_section = array(
		'section_id'  => 'seamless_donations_admin_settings_section_hosts',    // the section ID
		'page_slug'   => 'seamless_donations_admin_settings',    // the page slug that the section belongs to
		'title'       => __( 'Host Compatibility Options', 'seamless-donations' ),   // the section title
		'description' => __( $section_desc, 'seamless-donations' ),
	);

	$hosts_section = apply_filters( 'seamless_donations_admin_settings_section_hosts', $hosts_section );

	$form_via_action_desc
		                    = "This may help for sites/hosts that can't find seamless-donations-payment.php after form submitted.";
	$form_ignore_nonce_desc = "This may help for sites/hosts that that report permission denied after form submitted. ";
	$form_ignore_nonce_desc .= "<br><span style='color:red'>Warning: This could compromise form processing security ";
	$form_ignore_nonce_desc .= "or reliability. Be sure to perform sandbox tests after enabling this option.</span>";

	$form_transaction_desc = "This may help for sites/hosts that cache transaction IDs. ";
	$form_transaction_desc .= "Rather than generating the unique transaction ID in PHP on the server, ";
	$form_transaction_desc .= "this uses the device's native JavaScript.";
	$form_transaction_desc .= "<br><span style='color:red'>Warning: This could be unpredictable, ";
	$form_transaction_desc .= "depending on the age and compatibility of your user's device.</span>";

	$hosts_options = array(
		array(
			'field_id'    => 'dgx_donate_form_via_action',
			'title'       => __( 'Process Form Via', 'seamless-donations' ),
			'type'        => 'checkbox',
			'label'       => __(
				                 'Process form data via initiating page or post', 'seamless-donations' ) .
			                 seamless_donations_display_label(),
			'default'     => false,
			'description' => $form_via_action_desc,
		),
		array(
			'field_id'    => 'dgx_donate_ignore_form_nonce',
			'title'       => __( 'Form Nonces', 'seamless-donations' ),
			'type'        => 'checkbox',
			'label'       => __(
				                 'Ignore form nonce value', 'seamless-donations' ) . seamless_donations_display_label(),
			'default'     => false,
			'description' => $form_ignore_nonce_desc,
		),
		array(
			'field_id'    => 'dgx_donate_browser_uuid',
			'title'       => __( 'Browser-based IDs', 'seamless-donations' ),
			'type'        => 'checkbox',
			'label'       => __(
				                 'Generate unique transaction IDs in browser', 'seamless-donations' ) .
			                 seamless_donations_display_label(),
			'default'     => false,
			'description' => $form_transaction_desc,
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __( 'Save Host Options', 'seamless-donations' ),
		),
	);

	$hosts_options = apply_filters(
		'seamless_donations_admin_settings_section_hosts_options', $hosts_options );

	seamless_donations_process_add_settings_fields_with_options(
		$hosts_options, $_setup_object, $hosts_section );
}

//// SETTINGS - SECTION - TWEAKS ////
function seamless_donations_admin_settings_section_tweaks( $_setup_object ) {

	$section_desc = 'Options that can tweak your settings. Starting with one, undoubtedly more to come.';

	$tweaks_section = array(
		'section_id'  => 'seamless_donations_admin_settings_section_tweaks',    // the section ID
		'page_slug'   => 'seamless_donations_admin_settings',    // the page slug that the section belongs to
		'title'       => __( 'Setting Tweaks', 'seamless-donations' ),   // the section title
		'description' => __( $section_desc, 'seamless-donations' ),
	);

	$tweaks_section = apply_filters( 'seamless_donations_admin_settings_section_tweaks', $tweaks_section );

	$compact_desc = "<span style='color:red'>It may also be necessary to tweak Google Chrome to make this ";
	$compact_desc .= "feature work. See ";
	$compact_desc .= "<A href='http://wptavern.com/a-bug-in-chrome-45-causes-wordpress-admin-menu-to-break'>";
	$compact_desc .= " this article</A> for details. This feature is still under development.</span>";

	$tweaks_options = array(
		array(
			'field_id'    => 'dgx_donate_compact_menus',
			'title'       => __( 'Compact Menus', 'seamless-donations' ),
			'type'        => 'checkbox',
			'label'       => __(
				                 'Enable compact menu (tucks Donors, Funds, and Donations under Seamless Donations menu)',
				                 'seamless-donations' ) . seamless_donations_display_label(),
			'default'     => false,
			'description' => $compact_desc,
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __( 'Save Tweaks', 'seamless-donations' ),
		),
	);

	$tweaks_options = apply_filters(
		'seamless_donations_admin_settings_section_tweaks_options', $tweaks_options );

	seamless_donations_process_add_settings_fields_with_options(
		$tweaks_options, $_setup_object, $tweaks_section );
}

//// SETTINGS - SECTION - TABS ////
function seamless_donations_admin_settings_section_tabs( $_setup_object ) {

	// Test email section
	$section_desc = 'Restore hidden legacy v3.x admin tabs. ';
	$section_desc .= "These tabs were hidden because they're no longer relevant to this interface.";

	$settings_tabs_section
		                   = array(
		'section_id'  => 'seamless_donations_admin_settings_section_tabs',    // the section ID
		'page_slug'   => 'seamless_donations_admin_settings',    // the page slug that the section belongs to
		'title'       => __( 'Restore Hidden Tabs', 'seamless-donations' ),   // the section title
		'description' => __( $section_desc, 'seamless-donations' ),
	);
	$settings_tabs_section = apply_filters(
		'seamless_donations_admin_settings_section_tabs', $settings_tabs_section );

	$settings_tabs_options = array(
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __( 'Show All Tabs', 'seamless-donations' ),
		),
	);
	$settings_tabs_options = apply_filters(
		'seamless_donations_admin_settings_section_tabs_options', $settings_tabs_options );

	seamless_donations_process_add_settings_fields_with_options(
		$settings_tabs_options, $_setup_object, $settings_tabs_section );
}

//// SETTINGS - SECTION - DEBUG ////
function seamless_donations_admin_settings_section_debug( $_setup_object ) {

	$section_desc = 'Enables certain Seamless Donations debugging features. Reduces security. ';
	$section_desc .= 'Displays annoying (but effective) warning message until turned off.';

	$debug_section = array(
		'section_id'  => 'seamless_donations_admin_settings_section_debug',    // the section ID
		'page_slug'   => 'seamless_donations_admin_settings',    // the page slug that the section belongs to
		'title'       => __( 'Debug Options', 'seamless-donations' ),   // the section title
		'description' => __( $section_desc, 'seamless-donations' ),
	);

	$debug_section = apply_filters( 'seamless_donations_admin_settings_section_debug', $debug_section );

	$xref_name_desc = "<span style='color:red'>This runs once when you click Save Debug Mode. You probably ";
	$xref_name_desc .= "shouldn't run this unless requested to by the developer. This feature is still under development.</span>";

	// build the log settings values - this is an array because there will probably be more settings
	$obscurify           = get_option( 'dgx_donate_log_obscure_name' );
	$log_settings_values = array( $obscurify );

	$debug_options = array(
		array(
			'field_id'    => 'dgx_donate_debug_mode',
			'title'       => __( 'Debug Mode', 'seamless-donations' ),
			'type'        => 'checkbox',
			'label'       => __( 'Enable debug mode', 'seamless-donations' ),
			'default'     => false,
			'after_label' => '<br />',
		),
		array(
			'field_id'    => 'dgx_donate_log_settings',
			'title'       => __( 'Log Settings', 'seamless-donations' ),
			'type'        => 'checkbox',
			'label'       => array( __( 'Obscurify donor names in log', 'seamless-donations' ) ),
			'default'     => array( false ),
			'value'       => $log_settings_values,
			'after_label' => '<br />',
		),
		array(
			'field_id'    => 'dgx_donate_rebuild_xref_by_name',
			'title'       => __( 'Rebuild Indexes', 'seamless-donations' ),
			'type'        => 'checkbox',
			'label'       => __(
				                 'Rebuild Donations, Donors, and Funds cross-reference indexes (name priority)' ) .
			                 seamless_donations_display_label(),
			'default'     => false,
			'description' => $xref_name_desc,
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __( 'Save Debug Mode', 'seamless-donations' ),
		),
	);

	$debug_options = apply_filters(
		'seamless_donations_admin_settings_section_debug_options', $debug_options );

	seamless_donations_process_add_settings_fields_with_options(
		$debug_options, $_setup_object, $debug_section );
}