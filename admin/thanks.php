<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// THANKS - TAB ////
function seamless_donations_admin_thanks ( $setup_object ) {

	do_action ( 'seamless_donations_admin_thanks_before', $setup_object );

	seamless_donations_admin_thanks_menu ( $setup_object );
	seamless_donations_admin_thanks_section_note ( $setup_object );

	do_action ( 'seamless_donations_admin_thanks_after', $setup_object );

	add_filter (
		'validate_page_slug_seamless_donations_admin_thanks',
		'validate_page_slug_seamless_donations_admin_thanks_callback',
		10, // priority (for this, always 10)
		3 ); // number of arguments passed (for this, always 3)
}

//// THANKS - MENU ////
function seamless_donations_admin_thanks_menu ( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __ ( 'Thank You Page', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_thanks',
	);
	$sub_menu_array = apply_filters ( 'seamless_donations_admin_thanks_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage ( $sub_menu_array );
}

//// SETTINGS - PROCESS ////
function validate_page_slug_seamless_donations_admin_thanks_callback (
	$_submitted_array, $_existing_array, $_setup_object ) {

	$_submitted_array = apply_filters (
		'validate_page_slug_seamless_donations_admin_thanks_callback',
		$_submitted_array, $_existing_array, $_setup_object );

	$section = seamless_donations_get_submitted_admin_section ( $_submitted_array );

	// no real need for switch, but structured this way for easy expansion
	switch( $section ) {
		case 'seamless_donations_admin_thanks_section_note': // SAVE EMAILS //
			$note = trim($_submitted_array[ $section ]['dgx_donate_thanks_text']);
			$note = sanitize_text_field($note);
			if( $note == "" ) {
				$_aErrors[ $section ]['dgx_donate_thanks_text'] = __ (
					'Field must not be empty.', 'seamless-donations' );
				$_setup_object->setFieldErrors ( $_aErrors );
				$_setup_object->setSettingNotice (
					__ ( 'There were errors in your submission.', 'seamless-donations' ) );

				return $_existing_array;
			}
			update_option ( 'dgx_donate_thanks_text', $note );
			$_setup_object->setSettingNotice ( 'Form updated successfully.', 'updated' );
			break;
		default:
			$_setup_object->setSettingNotice (
				__ ( 'There was an unexpected error in your entry.', 'seamless-donations' ) );
	}
}

//// THANKS - SECTION - NOTE ////
function seamless_donations_admin_thanks_section_note ( $_setup_object ) {

	$section_desc = 'On this page you can configure a special thank you message which will appear to your ';
	$section_desc .= 'donors after they complete their donations. This is separate from the thank you email ';
	$section_desc .= 'that gets emailed to your donors.';

	$thanks_note_section = array(
		'section_id'  => 'seamless_donations_admin_thanks_section_note',    // the section ID
		'page_slug'   => 'seamless_donations_admin_thanks',    // the page slug that the section belongs to
		'title'       => __ ( 'Thank You Page', 'seamless-donations' ),   // the section title
		'description' => __ ( $section_desc, 'seamless-donations' ),
	);

	$thanks_note_section = apply_filters ( 'seamless_donations_admin_thanks_section_note', $thanks_note_section );

	$thanks_note_options = array(
		array(
			'field_id'    => 'dgx_donate_thanks_text',
			'type'        => 'textarea',
			'title'       => __ ( 'Thank You Page Text', 'seamless-donations' ),
			'description' => __ (
				'The text to display to a donor after a donation is completed.', 'seamless-donations' ),
			'default'     => 'Thank you for donating! A thank you email with the details of ' .
			                 'your donation will be sent to the email address you provided.',
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Save Changes', 'seamless-donations' ),
		)
	);

	$thanks_note_options = apply_filters (
		'seamless_donations_admin_thanks_section_note_options', $thanks_note_options );

	seamless_donations_process_add_settings_fields_with_options ( $thanks_note_options, $_setup_object, $thanks_note_section );
}

add_filter ( 'validate_page_slug_seamless_donations_admin_thanks', 'validate_thank_you_page_submit', 10, 3 );

function validate_thank_you_page_submit ( $submitted_array, $existing_array, $setup_object ) {

	if( $submitted_array['thank_you_page_note']['thank_you_page_submit'] == 'Save Changes' ) {
		if( trim ( $submitted_array['thank_you_page_note']['thank_you_page_message'] ) == "" ) {
			$_aErrors = array();
			// $variable[ 'section_id' ]['field_id']
			$_aErrors['thank_you_page_note']['thank_you_page_message'] = __ (
				'This value must not be empty.', 'seamless-donations' );
			$setup_object->setFieldErrors ( $_aErrors );
			$setup_object->setSettingNotice ( __ ( 'You must enter a thank you page message.', 'seamless-donations' ) );
		} else {
			$thank_you_text = $submitted_array['thank_you_page_note']['thank_you_page_message'];
			$thank_you_text = sanitize_text_field ( $thank_you_text );
			update_option ( 'dgx_donate_thanks_text', $thank_you_text );
			// write out the confirmation message for successful completion.
			$setup_object->oMsg->aMessages['option_updated'] = __ (
				'Thank you page text has been updated.', 'seamless-donations' );
		}
	}

	return $submitted_array;
}