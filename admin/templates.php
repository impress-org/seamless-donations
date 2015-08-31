<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// TEMPLATES - TAB ////
function seamless_donations_admin_templates ( $setup_object ) {

	do_action ( 'seamless_donations_admin_templates_before', $setup_object );

	// create the admin tab menu
	seamless_donations_admin_templates_menu ( $setup_object );

	// create the two sections
	seamless_donations_admin_templates_section_test ( $setup_object );
	seamless_donations_admin_templates_section_template ( $setup_object );

	do_action ( 'seamless_donations_admin_templates_after', $setup_object );

	add_filter (
		'validate_page_slug_seamless_donations_admin_templates',
		'validate_page_slug_seamless_donations_admin_templates_callback',
		10, // priority (for this, always 10)
		3 ); // number of arguments passed (for this, always 3)

}

//// TEMPLATES - MENU ////
function seamless_donations_admin_templates_menu ( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __ ( 'Thank You Templates', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_templates',
	);
	$sub_menu_array = apply_filters ( 'seamless_donations_admin_templates_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage ( $sub_menu_array );
}

//// TEMPLATES - PROCESS ////
function validate_page_slug_seamless_donations_admin_templates_callback (
	$_submitted_array, $_existing_array, $_setup_object ) {

	$_submitted_array = apply_filters (
		'validate_page_slug_seamless_donations_admin_templates_callback',
		$_submitted_array, $_existing_array, $_setup_object );

	$section = seamless_donations_get_submitted_admin_section ( $_submitted_array );

	switch( $section ) {
		case 'seamless_donations_admin_templates_section_test': // SAVE EMAILS //
			$test_mail = $_submitted_array[ $section ]['email_test_address'];
			$test_mail = sanitize_email ( $test_mail );
			if( ! is_email ( $test_mail ) ) { // check address
				$_aErrors[ $section ]['email_test_address'] = __ (
					'Valid email address required.', 'seamless-donations' );
				$_setup_object->setFieldErrors ( $_aErrors );
				$_setup_object->setSettingNotice (
					__ ( 'There were errors in your submission.', 'seamless-donations' ) );

				return $_existing_array;
			}
			dgx_donate_send_thank_you_email ( 0, $test_mail );
			$_setup_object->setSettingNotice ( 'Test email sent.', 'updated' );
			break;
		case 'seamless_donations_admin_templates_section_template': // SAVE TEMPLATE //
			// check email address
			$email = $_submitted_array[ $section ]['dgx_donate_email_reply'];
			$email = sanitize_email ( $email );
			if( ! is_email ( $email ) ) {
				$_aErrors[ $section ]['dgx_donate_paypal_email'] = __ (
					'Valid email address required.', 'seamless-donations' );
				$_setup_object->setFieldErrors ( $_aErrors );
				$_setup_object->setSettingNotice (
					__ ( 'There were errors in your submission.', 'seamless-donations' ) );

				return $_existing_array;
			}
			// check array fields for clean and not-empty
			for( $i = 0; $i < count ( $_submitted_array[ $section ] ); ++ $i ) {
				$key   = seamless_donations_name_of ( $_submitted_array[ $section ], $i );
				$value = trim ( $_submitted_array[ $section ][ $key ] );
				$value = wp_kses_post ( $value );
				if( $key == 'submit' ) {
					continue; // not a text field
				}
				if( $key == 'dgx_donate_email_reply' ) {
					continue; // already tested for validation
				}
				if( $value == "" ) {
					$_aErrors[ $section ][ $key ] = __ (
						'This field must not be empty.', 'seamless-donations' );
					$_setup_object->setFieldErrors ( $_aErrors );
					$_setup_object->setSettingNotice (
						__ ( 'There were errors in your submission.', 'seamless-donations' ) );

					return $_existing_array;
				}
			}
			// save array fields as clean, sanitized options
			for( $i = 0; $i < count ( $_submitted_array[ $section ] ); ++ $i ) {
				$key   = seamless_donations_name_of ( $_submitted_array[ $section ], $i );
				$value = trim ( $_submitted_array[ $section ][ $key ] );
				if( $key == 'dgx_donate_email_reply' ) {
					$value = sanitize_email ( $value );
				} else {
					$value = wp_kses_post ( $value );
				}

				if( $key == 'submit' ) {
					continue; // not a text field
				}
				update_option ( $key, $value );
			}
			$_setup_object->setSettingNotice ( 'Form updated successfully.', 'updated' );
			break;
		case 'seamless_donations_admin_templates_section_extension': // LET EXTENSIONS DO THE PROCESSING
			break;
		default:
			$_setup_object->setSettingNotice (
				__ ( 'There was an unexpected error in your entry.', 'seamless-donations' ) );
	}
}

//// TEMPLATES - SECTION - TEST ////
function seamless_donations_admin_templates_section_test ( $_setup_object ) {

	// Test email section
	$section_desc = 'Enter an email address (e.g. your own) to have a test email sent using the template.';

	$email_test_section
		                = array(
		'section_id'  => 'seamless_donations_admin_templates_section_test',    // the section ID
		'page_slug'   => 'seamless_donations_admin_templates',    // the page slug that the section belongs to
		'title'       => __ ( 'Send a Test Email', 'seamless-donations' ),   // the section title
		'description' => __ ( $section_desc, 'seamless-donations' ),
	);
	$email_test_section = apply_filters ( 'seamless_donations_admin_templates_section_test', $email_test_section );

	$email_test_options = array(
		array(
			'field_id'    => 'email_test_address',
			'type'        => 'text',
			'title'       => __ ( 'Email Address', 'seamless-donations' ),
			'description' => __ (
				'The email address to receive the test message.', 'seamless-donations' ),
			'attributes'  => array(
				'size' => 40,
			),
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Send Test Email', 'seamless-donations' ),
		)
	);
	$email_test_options = apply_filters (
		'seamless_donations_admin_templates_section_test_options', $email_test_options );

	seamless_donations_process_add_settings_fields_with_options (
		$email_test_options, $_setup_object, $email_test_section );
}

//// TEMPLATES - SECTION - TEMPLATE ////
function seamless_donations_admin_templates_section_template ( $_setup_object ) {

	// Email template settings
	$section_desc = 'The template on this page is used to generate thank you emails for ';
	$section_desc .= 'each donation.dgx-donate You can include placeholders ';
	$section_desc .= 'such as [firstname] [lastname] [fund] and/or [amount]. These placeholders will ';
	$section_desc .= 'automatically be filled in with the donor and donation details. ';

	$email_template_section = array(
		'section_id'  => 'seamless_donations_admin_templates_section_template',    // the section ID
		'page_slug'   => 'seamless_donations_admin_templates',    // the page slug that the section belongs to
		'title'       => __ ( 'Email Template', 'seamless-donations' ),   // the section title
		'description' => __ ( $section_desc, 'seamless-donations' ),
	);
	$email_template_section = apply_filters (
		'seamless_donations_admin_templates_section_template', $email_template_section );

	// array for setting up settings element for this section
	// be sure to use the name of the option (i.e., get_option) for the field_id
	$email_options = array(
		array(
			'field_id'    => 'dgx_donate_email_name',
			'type'        => 'text',
			'title'       => __ ( 'From / Reply-To Name', 'seamless-donations' ),
			'description' => __ (
				'The name the thank you email should appear to come from (e.g. your organization name or your name).',
				'seamless-donations' ),
			'default'     => __ ( '', 'seamless-donations' ),
			'attributes'  => array(
				'size' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_reply',
			'type'        => 'text',
			'title'       => __ ( 'From / Reply-To Email Address', 'seamless-donations' ),
			'description' => __ (
				'The email address the thank you email should appear to come from.',
				'seamless-donations' ),
			'default'     => __ ( '', 'seamless-donations' ),
			'attributes'  => array(
				'size' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_subj',
			'type'        => 'text',
			'title'       => __ ( 'Subject', 'seamless-donations' ),
			'description' => __ (
				'The subject of the email (e.g. Thank You for Your Donation).',
				'seamless-donations' ),
			'default'     => __ ( 'Thank you for your donation', 'seamless-donations' ),
			'attributes'  => array(
				'size' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_body',
			'type'        => 'textarea',
			'title'       => __ ( 'Body', 'seamless-donations' ),
			'description' => __ (
				'The body of the email message to all donors.',
				'seamless-donations' ),
			'default'     => __ (
				'Dear [firstname] [lastname],' . PHP_EOL . PHP_EOL .
				'Thank you for your generous donation of [amount]. ' .
				'Please note that no goods or services were received in exchange for this donation.',
				'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_recur',
			'type'        => 'textarea',
			'title'       => __ ( 'Recurring Donations', 'seamless-donations' ),
			'description' => __ (
				'This message will be included when the donor elects to make their donation recurring.',
				'seamless-donations' ),
			'default'     => __ (
				'Thank you for electing to have your donation automatically repeated each month.',
				'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_desig',
			'type'        => 'textarea',
			'title'       => __ ( 'Designated Fund', 'seamless-donations' ),
			'description' => __ (
				'This message will be included when the donor designates their donation to a specific fund.',
				'seamless-donations' ),
			'default'     => __ ( 'Your donation has been designated to the [fund] fund.', 'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_anon',
			'type'        => 'textarea',
			'title'       => __ ( 'Anonymous Donations', 'seamless-donations' ),
			'description' => __ (
				'This message will be included when the donor requests their donation get kept anonymous.',
				'seamless-donations' ),
			'default'     => __ (
				'You have requested that your donation be kept anonymous. ' .
				'Your name will not be revealed to the public.', 'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_list',
			'type'        => 'textarea',
			'title'       => __ ( 'Mailing List Join', 'seamless-donations' ),
			'description' => __ (
				'This message will be included when the donor elects to join the mailing list.',
				'seamless-donations' ),
			'default'     => __ (
				'Thank you for joining our mailing list.  We will send you updates from time-to-time. ' .
				'If at any time you would like to stop receiving emails, please ' .
				'send us an email to be removed from the mailing list.', 'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_empl',
			'type'        => 'textarea',
			'title'       => __ ( 'Employer Match', 'seamless-donations' ),
			'description' => __ (
				'This message will be included when the donor selects the employer match.',
				'seamless-donations' ),
			'default'     => __ (
				'You have specified that your employer matches some or all of your donation.', 'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_trib',
			'type'        => 'textarea',
			'title'       => __ ( 'Tribute Gift', 'seamless-donations' ),
			'description' => __ (
				'This message will be included when the donor elects to make their donation a tribute gift.',
				'seamless-donations' ),
			'default'     => __ (
				'You have asked to make this donation in honor of or memory of someone else. ' .
				'We will notify the honoree within the next 5-10 business days.', 'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_close',
			'type'        => 'textarea',
			'title'       => __ ( 'Closing', 'seamless-donations' ),
			'description' => __ (
				'The closing text of the email message to all donors.',
				'seamless-donations' ),
			'default'     => __ ( 'Thanks again for your support!', 'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id'    => 'dgx_donate_email_sig',
			'type'        => 'textarea',
			'title'       => __ ( 'Signature', 'seamless-donations' ),
			'description' => __ (
				'The signature at the end of the email message to all donors.',
				'seamless-donations' ),
			'default'     => __ ( 'Director of Donor Relations', 'seamless-donations' ),
			'attributes'  => array(
				'cols' => 80,
			),
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Save Changes', 'seamless-donations' ),
		)
	);
	$email_options = apply_filters ( 'seamless_donations_admin_templates_section_template_options', $email_options );

	seamless_donations_process_add_settings_fields_with_options (
		$email_options, $_setup_object, $email_template_section );
}

