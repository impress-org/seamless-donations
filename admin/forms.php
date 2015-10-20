<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

//// FORMS - TAB ////
function seamless_donations_admin_forms ( $setup_object ) {

	do_action ( 'seamless_donations_admin_forms_before', $setup_object );

	seamless_donations_admin_forms_menu ( $setup_object );
	seamless_donations_admin_forms_section_levels ( $setup_object );
	seamless_donations_admin_forms_section_defaults ( $setup_object );
	seamless_donations_admin_forms_section_fields ( $setup_object );
	seamless_donations_admin_forms_section_tweaks ( $setup_object );

	do_action ( 'seamless_donations_admin_forms_after', $setup_object );

	add_filter (
		'validate_page_slug_seamless_donations_admin_forms',
		'validate_page_slug_seamless_donations_admin_forms_callback',
		10, // priority (for this, always 10)
		3 ); // number of arguments passed (for this, always 3)
}

//// FORMS - MENU ////
function seamless_donations_admin_forms_menu ( $_setup_object ) {

	$sub_menu_array = array(
		'title'     => __ ( 'Form Options', 'seamless-donations' ),
		'page_slug' => 'seamless_donations_admin_forms',
	);
	$sub_menu_array = apply_filters ( 'seamless_donations_admin_forms_menu', $sub_menu_array );
	$_setup_object->addSubMenuPage ( $sub_menu_array );
}

//// SETTINGS - PROCESS ////
function validate_page_slug_seamless_donations_admin_forms_callback (
	$_submitted_array, $_existing_array, $_setup_object ) {

	$_submitted_array = apply_filters (
		'validate_page_slug_seamless_donations_admin_forms_callback',
		$_submitted_array, $_existing_array, $_setup_object );

	$section = seamless_donations_get_submitted_admin_section ( $_submitted_array );

	switch( $section ) {
		case 'seamless_donations_admin_forms_section_levels': // SAVE LEVELS //

			$none_enabled = true;

			$giving_levels = dgx_donate_get_giving_levels ();
			foreach( $giving_levels as $giving_level ) {
				if( $_submitted_array[ $section ]['giving_levels'][ $giving_level ] ) {
					dgx_donate_enable_giving_level ( $giving_level );
					$none_enabled = false;
				} else {
					dgx_donate_disable_giving_level ( $giving_level );
				}
			}

			if( $none_enabled ) {
				$_aErrors[ $section ]['giving_levels'] = __ (
					'At least one giving level is required.', 'seamless-donations' );
				$_setup_object->setFieldErrors ( $_aErrors );
				$_setup_object->setSettingNotice (
					__ ( 'There were errors in your submission.', 'seamless-donations' ) );

				return $_existing_array;
			}

			$_setup_object->setSettingNotice ( 'Form updated successfully.', 'updated' );
			break;
		case 'seamless_donations_admin_forms_section_defaults': // SAVE DEFAULTS //
			update_option (
				'dgx_donate_currency', $_submitted_array[ $section ]['dgx_donate_currency'] );
			update_option ( 'dgx_donate_default_country', $_submitted_array[ $section ]['dgx_donate_default_country'] );
			$_setup_object->setSettingNotice ( 'Form updated successfully.', 'updated' );
			break;
		case 'seamless_donations_admin_forms_section_fields': // SAVE FIELDS //
			update_option (
				'dgx_donate_show_designated_funds_section',
				$_submitted_array[ $section ]['dgx_donate_show_designated_funds_section'] );
			update_option (
				'dgx_donate_show_repeating_option',
				$_submitted_array[ $section ]['dgx_donate_show_repeating_option'] );
			update_option (
				'dgx_donate_show_tribute_section',
				$_submitted_array[ $section ]['dgx_donate_show_tribute_section'] );
			update_option (
				'dgx_donate_show_employer_section',
				$_submitted_array[ $section ]['dgx_donate_show_employer_section'] );
			update_option (
				'dgx_donate_show_donor_telephone_field',
				$_submitted_array[ $section ]['dgx_donate_show_donor_telephone_field'] );
			update_option (
				'dgx_donate_show_donor_employer_field',
				$_submitted_array[ $section ]['dgx_donate_show_donor_employer_field'] );
			update_option (
				'dgx_donate_show_donor_occupation_field',
				$_submitted_array[ $section ]['dgx_donate_show_donor_occupation_field'] );
			update_option (
				'dgx_donate_show_mailing_list_option',
				$_submitted_array[ $section ]['dgx_donate_show_mailing_list_option'] );
			update_option (
				'dgx_donate_show_anonymous_option',
				$_submitted_array[ $section ]['dgx_donate_show_anonymous_option'] );
			update_option (
				'dgx_donate_show_donor_address_fields',
				$_submitted_array[ $section ]['dgx_donate_show_donor_address_fields'] );

			$_setup_object->setSettingNotice ( 'Form updated successfully.', 'updated' );
			break;
		case 'seamless_donations_admin_forms_section_tweaks': // SAVE TWEAKS //
			update_option (
				'dgx_donate_labels_for_input', $_submitted_array[ $section ]['dgx_donate_labels_for_input'] );
			$_setup_object->setSettingNotice ( 'Form updated successfully.', 'updated' );
			break;
		case 'seamless_donations_admin_forms_section_extension': // LET EXTENSIONS DO THE PROCESSING
			break;
		default:
			$_setup_object->setSettingNotice (
				__ ( 'There was an unexpected error in your entry.', 'seamless-donations' ) );
	}
}

//// FORMS - SECTION - LEVELS ////
function seamless_donations_admin_forms_section_levels ( $_setup_object ) {

	$section_desc = 'Select one or more suggested giving levels for your donors to choose from.';

	// promo
	$feature_desc = 'Giving Level Manager customizes donation levels, assigns labels for each level.';
	$feature_url = 'http://zatzlabs.com/project/seamless-donations-giving-level-manager/';
	$section_desc .= seamless_donations_get_feature_promo($feature_desc, $feature_url);

	$giving_levels_section = array(
		'section_id'  => 'seamless_donations_admin_forms_section_levels',    // the section ID
		'page_slug'   => 'seamless_donations_admin_forms',    // the page slug that the section belongs to
		'title'       => __ ( 'Giving Levels', 'seamless-donations' ),   // the section title
		'description' => __ ( $section_desc, 'seamless-donations' ),
	);

	$giving_levels_section = apply_filters ( 'seamless_donations_admin_forms_section_levels', $giving_levels_section );

	$giving_levels       = dgx_donate_get_giving_levels ();
	$giving_level_labels = array();
	for( $i = 0; $i < count ( $giving_levels ); ++ $i ) {
		$value                         = $giving_levels[ $i ];
		$giving_level_labels[ $value ] = $value;
	}
	$giving_level_defaults = array();
	for( $i = 0; $i < count ( $giving_levels ); ++ $i ) {
		$value                           = $giving_levels[ $i ];
		$giving_level_defaults[ $value ] = dgx_donate_is_giving_level_enabled ( $value );
	}

	$giving_levels_options = array(
		array( // Multiple checkbox items - for multiple checkbox items, set an array to the 'label' element.
		       'field_id'    => 'giving_levels',
		       'title'       => __ ( 'Display Levels', 'seamless-donations' ),
		       'type'        => 'checkbox',
		       'label'       => $giving_level_labels,
		       'default'     => $giving_level_defaults,
		       'after_label' => '<br />',
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Save Giving Levels', 'seamless-donations' ),
		)
	);

	$giving_levels_options = apply_filters (
		'seamless_donations_admin_forms_section_levels_options', $giving_levels_options );

	seamless_donations_process_add_settings_fields_with_options (
		$giving_levels_options, $_setup_object, $giving_levels_section );
}

//// FORMS - SECTION - DEFAULTS ////
function seamless_donations_admin_forms_section_defaults ( $_setup_object ) {

	$section_desc
		= 'Select the currency you would like to receive donations in and the default country for the donation form.';

	$defaults_section = array(
		'section_id'  => 'seamless_donations_admin_forms_section_defaults',    // the section ID
		'page_slug'   => 'seamless_donations_admin_forms',    // the page slug that the section belongs to
		'title'       => __ ( 'Defaults', 'seamless-donations' ),   // the section title
		'description' => __ ( $section_desc, 'seamless-donations' ),
	);

	$defaults_section = apply_filters (
		'seamless_donations_admin_forms_section_defaults', $defaults_section );

	$defaults_options = array(
		array(
			'field_id' => 'dgx_donate_currency',
			'title'    => __ ( 'Default Currency', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'USD', // the index key of the label array below
			'label'    => dgx_donate_get_currency_list (),
		),
		array(
			'field_id' => 'dgx_donate_default_country',
			'title'    => __ ( 'Default Country', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'US', // the index key of the label array below
			'label'    => dgx_donate_get_countries (),
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Save Defaults', 'seamless-donations' ),
		)
	);

	$defaults_options = apply_filters (
		'seamless_donations_admin_forms_section_defaults_options', $defaults_options );

	seamless_donations_process_add_settings_fields_with_options (
		$defaults_options, $_setup_object, $defaults_section );
}

//// FORMS - SECTION - FIELDS ////
function seamless_donations_admin_forms_section_fields ( $_setup_object ) {

	$section_desc = 'Choose which form fields and sections you would like to hide, show or require.';

	$giving_levels_section             = array(
		'section_id'  => 'seamless_donations_admin_forms_section_fields',    // the section ID
		'page_slug'   => 'seamless_donations_admin_forms',    // the page slug that the section belongs to
		'title'       => __ ( 'Form Fields and Sections', 'seamless-donations' ),   // the section title
		'description' => __ ( $section_desc, 'seamless-donations' ),
		87
	);
	$form_display_options              = array(
		'true'  => 'Show',
		'false' => 'Don\'t Show',
	);
	$form_display_options_with_require = array(
		'true'     => 'Show',
		'false'    => 'Don\'t Show',
		'required' => 'Require',
	);

	$giving_levels_section = apply_filters (
		'seamless_donations_admin_forms_section_fields', $giving_levels_section );

	$giving_levels_options = array(
		array(
			'field_id' => 'dgx_donate_show_designated_funds_section',
			'title'    => __ ( 'Designated Funds Checkbox and Section', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'false', // the index key of the label array below
			'label'    => $form_display_options
		),
		array(
			'field_id' => 'dgx_donate_show_repeating_option',
			'title'    => __ ( 'Repeating Donation Checkbox', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'false', // the index key of the label array below
			'label'    => $form_display_options
		),
		array(
			'field_id' => 'dgx_donate_show_tribute_section',
			'title'    => __ ( 'Tribute Gift Checkbox and Section', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'true', // the index key of the label array below
			'label'    => $form_display_options
		),
		array(
			'field_id' => 'dgx_donate_show_employer_section',
			'title'    => __ ( 'Employer Match Section', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'false', // the index key of the label array below
			'label'    => $form_display_options
		),
		array(
			'field_id' => 'dgx_donate_show_donor_telephone_field',
			'title'    => __ ( 'Donor Telephone Field', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'true', // the index key of the label array below
			'label'    => $form_display_options_with_require
		),
		array(
			'field_id' => 'dgx_donate_show_donor_employer_field',
			'title'    => __ ( 'Donor Employer Field', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'false', // the index key of the label array below
			'label'    => $form_display_options_with_require
		),
		array(
			'field_id' => 'dgx_donate_show_donor_occupation_field',
			'title'    => __ ( 'Donor Occupation Field', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'false', // the index key of the label array below
			'label'    => $form_display_options_with_require
		),
		array(
			'field_id' => 'dgx_donate_show_mailing_list_option',
			'title'    => __ ( 'Mailing List Checkbox', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'false', // the index key of the label array below
			'label'    => $form_display_options
		),
		array(
			'field_id' => 'dgx_donate_show_anonymous_option',
			'title'    => __ ( 'Anonymous Donation Checkbox', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'false', // the index key of the label array below
			'label'    => $form_display_options
		),
		array(
			'field_id' => 'dgx_donate_show_donor_address_fields',
			'title'    => __ ( 'Donor Address Section', 'seamless-donations' ),
			'type'     => 'select',
			'default'  => 'false', // the index key of the label array below
			'label'    => $form_display_options
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Save Fields', 'seamless-donations' ),
		)
	);

	$giving_levels_options = apply_filters (
		'seamless_donations_admin_forms_section_fields_options', $giving_levels_options );

	seamless_donations_process_add_settings_fields_with_options (
		$giving_levels_options, $_setup_object, $giving_levels_section );
}

//// FORMS - SECTION - TWEAKS ////
function seamless_donations_admin_forms_section_tweaks ( $_setup_object ) {

	$section_desc = 'Options that can tweak your form. Starting with one, undoubtedly more to come.';

	$tweaks_section = array(
		'section_id'  => 'seamless_donations_admin_forms_section_tweaks',    // the section ID
		'page_slug'   => 'seamless_donations_admin_forms',    // the page slug that the section belongs to
		'title'       => __ ( 'Form Tweaks', 'seamless-donations' ),   // the section title
		'description' => __ ( $section_desc, 'seamless-donations' ),
	);

	$tweaks_section = apply_filters ( 'seamless_donations_admin_forms_section_tweaks', $tweaks_section );

	$tweaks_options = array(
		array(
			'field_id'    => 'dgx_donate_labels_for_input',
			'title'       => __ ( 'Label Tag', 'seamless-donations' ),
			'type'        => 'checkbox',
			'label'       => __ ( 'Add label tag to input form (may improve form layout for some themes)', 'seamless-donations'),
			'default'     => false,
			'after_label' => '<br />',
		),
		array(
			'field_id' => 'submit',
			'type'     => 'submit',
			'label'    => __ ( 'Save Tweaks', 'seamless-donations' ),
		)
	);

	$tweaks_options = apply_filters (
		'seamless_donations_admin_forms_section_tweaks_options', $tweaks_options );

	seamless_donations_process_add_settings_fields_with_options (
		$tweaks_options, $_setup_object, $tweaks_section );
}