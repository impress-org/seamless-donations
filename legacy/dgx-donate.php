<?php
/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

/******************************************************************************************************/
function dgx_donate_get_giving_levels () {

	$builtinGivingLevels = array( 1000, 500, 200, 100, 50, 20, 10, 5 );

	// leaving as-is for compatibility
	$givingLevels = apply_filters ( 'dgx_donate_giving_levels', $builtinGivingLevels );

	// check if filter results are unusable
	if( count ( $givingLevels ) == 0 ) {
		$givingLevels = array( 1000 ); // default = just $1000
	}

	return $givingLevels;
}

/******************************************************************************************************/
function dgx_donate_is_valid_giving_level ( $amount ) {

	$givingLevels = dgx_donate_get_giving_levels ();

	if( in_array ( $amount, $givingLevels ) ) {
		return true;
	}

	return false;
}

/******************************************************************************************************/
function dgx_donate_enable_giving_level ( $amount ) {

	if( dgx_donate_is_valid_giving_level ( $amount ) ) {
		$key = dgx_donate_get_giving_level_key ( $amount );
		update_option ( $key, "yes" );
	}
}

/******************************************************************************************************/
function dgx_donate_disable_giving_level ( $amount ) {

	if( dgx_donate_is_valid_giving_level ( $amount ) ) {
		$key = dgx_donate_get_giving_level_key ( $amount );
		delete_option ( $key );
	}
}

/******************************************************************************************************/
function dgx_donate_is_giving_level_enabled ( $amount ) {

	$levelEnabled = false;

	if( dgx_donate_is_valid_giving_level ( $amount ) ) {
		$key   = dgx_donate_get_giving_level_key ( $amount );
		$value = get_option ( $key );
		if( ! empty( $value ) ) {
			$levelEnabled = true;
		}
	}

	return $levelEnabled;
}

/******************************************************************************************************/
function dgx_donate_get_giving_level_key ( $amount ) {

	$key = "dgx_donate_giving_level_" . $amount;

	return $key;
}

/******************************************************************************************************/
function dgx_donate_queue_scripts () {

	$load_in_footer = ( 'true' == get_option ( 'dgx_donate_scripts_in_footer' ) );

	$script_url = plugin_dir_url ( dirname ( __FILE__ ) ) . 'js/script.js';
	wp_enqueue_script ( 'dgx_donate_script', $script_url, array( 'jquery' ), false, $load_in_footer );

	$script_url = plugins_url ( '/js/geo-selects.js', __FILE__ );
	wp_enqueue_script ( 'dgx_donate_geo_selects_script', $script_url, array( 'jquery' ), false, $load_in_footer );

	// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
	wp_localize_script (
		'dgx_donate_script', 'dgxDonateAjax',
		array(
			'ajaxurl'            => admin_url ( 'admin-ajax.php' ),
			'nonce'              => wp_create_nonce ( 'dgx-donate-nonce' ),
			'postalCodeRequired' => dgx_donate_get_countries_requiring_postal_code ()
		)
	);
}

add_action ( 'wp_enqueue_scripts', 'dgx_donate_queue_scripts' ); // will eventually be deprecated



/******************************************************************************************************/
function dgx_donate_display_thank_you () {

	$output       = "<p>";
	$thankYouText = get_option ( 'dgx_donate_thanks_text' );
	$thankYouText = nl2br ( $thankYouText );
	$output .= $thankYouText;
	$output .= "</p>";

	return $output;
}

/******************************************************************************************************/
function dgx_donate_debug_log ( $message ) {

	$max_log_line_count = 200;

	$debug_log = get_option ( 'dgx_donate_log' );

	if( empty( $debug_log ) ) {
		$debug_log = array();
	}

	$timestamp = current_time ( 'mysql' );

	$debug_log[] = $timestamp . ' ' . $message;

	if( count ( $debug_log ) > $max_log_line_count ) {
		$debug_log = array_slice ( $debug_log, - $max_log_line_count, 0 );
	}

	update_option ( 'dgx_donate_log', $debug_log );
}

/******************************************************************************************************/
function dgx_donate_get_meta_map () {

	return array(
		'SESSIONID'        => '_dgx_donate_session_id',
		'AMOUNT'           => '_dgx_donate_amount',
		'REPEATING'        => '_dgx_donate_repeating',
		'DESIGNATED'       => '_dgx_donate_designated',
		'DESIGNATEDFUND'   => '_dgx_donate_designated_fund',
		'TRIBUTEGIFT'      => '_dgx_donate_tribute_gift',
		'MEMORIALGIFT'     => '_dgx_donate_memorial_gift',
		'HONOREENAME'      => '_dgx_donate_honoree_name',
		'HONORBYEMAIL'     => '_dgx_donate_honor_by_email',
		'HONOREEEMAILNAME' => '_dgx_donate_honoree_email_name',
		'HONOREEEMAIL'     => '_dgx_donate_honoree_email',
		'HONOREEPOSTNAME'  => '_dgx_donate_honoree_post_name',
		'HONOREEADDRESS'   => '_dgx_donate_honoree_address',
		'HONOREECITY'      => '_dgx_donate_honoree_city',
		'HONOREESTATE'     => '_dgx_donate_honoree_state',
		'HONOREEPROVINCE'  => '_dgx_donate_honoree_province',
		'HONOREECOUNTRY'   => '_dgx_donate_honoree_country',
		'HONOREEZIP'       => '_dgx_donate_honoree_zip',
		'FIRSTNAME'        => '_dgx_donate_donor_first_name',
		'LASTNAME'         => '_dgx_donate_donor_last_name',
		'PHONE'            => '_dgx_donate_donor_phone',
		'EMAIL'            => '_dgx_donate_donor_email',
		'ADDTOMAILINGLIST' => '_dgx_donate_add_to_mailing_list',
		'ADDRESS'          => '_dgx_donate_donor_address',
		'ADDRESS2'         => '_dgx_donate_donor_address2',
		'CITY'             => '_dgx_donate_donor_city',
		'STATE'            => '_dgx_donate_donor_state',
		'PROVINCE'         => '_dgx_donate_donor_province',
		'COUNTRY'          => '_dgx_donate_donor_country',
		'ZIP'              => '_dgx_donate_donor_zip',
		'INCREASETOCOVER'  => '_dgx_donate_increase_to_cover',
		'ANONYMOUS'        => '_dgx_donate_anonymous',
		'PAYMENTMETHOD'    => '_dgx_donate_payment_method',
		'EMPLOYERMATCH'    => '_dgx_donate_employer_match',
		'EMPLOYERNAME'     => '_dgx_donate_employer_name',
		'OCCUPATION'       => '_dgx_donate_occupation',
		'UKGIFTAID'        => '_dgx_donate_uk_gift_aid'
	);
}

/******************************************************************************************************/
function dgx_donate_create_empty_donation_record () {

	dgx_donate_debug_log ( "Creating donation record..." );
	$sd4_mode = get_option ( 'dgx_donate_start_in_sd4_mode' );
	if( $sd4_mode == false ) {
		$post_type = 'dgx-donation';
	} else {
		$post_type = 'donation';
	}
	dgx_donate_debug_log ( "...of type $post_type" );

	// Get all the dates - timezone fix thanks to pkwooster
	$gmt_offset    = - get_option ( 'gmt_offset' );
	$php_time_zone = date_default_timezone_get ();
	if( $gmt_offset > 0 ) {
		$time_zone = 'Etc/GMT+' . $gmt_offset;
	} else {
		$time_zone = 'Etc/GMT' . $gmt_offset;
	}
	date_default_timezone_set ( $time_zone );

	$year           = date ( 'Y' );
	$month          = date ( 'm' );
	$day            = date ( 'd' );
	$year_month_day = date ( 'Y-m-d' );
	$time           = date ( 'g:i:s A' );
	$date_time      = date ( 'Y-m-d H:i:s' );

	// set the PHP timezone back the way it was
	date_default_timezone_set ( $php_time_zone );

	// the title is Lastname, Firstname (YYYY-MM-dd)
	$post_title = $date_time;

	$new_donation = array(
		'post_title'   => $post_title,
		'post_content' => '',
		'post_status'  => 'publish',
		'post_date'    => $date_time,
		'post_author'  => 1,
		'post_type'    => $post_type,
	);

	$donation_id = wp_insert_post ( $new_donation );

	// Save some meta
	update_post_meta ( $donation_id, '_dgx_donate_year', $year );
	update_post_meta ( $donation_id, '_dgx_donate_month', $month );
	update_post_meta ( $donation_id, '_dgx_donate_day', $day );
	update_post_meta ( $donation_id, '_dgx_donate_time', $time );

	return $donation_id;
}

/******************************************************************************************************/
function dgx_donate_create_donation_from_transient_data ( $transient_data ) {

	// Create a new donation record
	$donation_id = dgx_donate_create_empty_donation_record ();

	$meta_map = dgx_donate_get_meta_map ();

	foreach( (array) $meta_map as $transient_data_key => $postmeta_key ) {
		update_post_meta ( $donation_id, $postmeta_key, $transient_data[ $transient_data_key ] );
	}

	return $donation_id;
}

/******************************************************************************************************/
function dgx_donate_create_donation_from_donation ( $old_donation_id ) {

	// Create a new donation record by cloning an old one (useful for repeating donations)
	dgx_donate_debug_log ( "about to create donation from old donation $old_donation_id" );
	$new_donation_id = dgx_donate_create_empty_donation_record ();
	dgx_donate_debug_log ( "new donation id = $new_donation_id" );

	$meta_map = dgx_donate_get_meta_map ();

	foreach( (array) $meta_map as $transient_data_key => $postmeta_key ) {
		$old_donation_meta_value = get_post_meta ( $old_donation_id, $postmeta_key, true );
		update_post_meta ( $new_donation_id, $postmeta_key, $old_donation_meta_value );
	}

	dgx_donate_debug_log ( "done with dgx_donate_create_donation_from_donation, returning new id $new_donation" );

	return $new_donation_id;
}

/******************************************************************************************************/
function dgx_donate_create_donation_from_paypal_data ( $post_data ) {

	// Create a new donation record from paypal data (if transient no longer exists for some reason)
	dgx_donate_debug_log ( "about to create donation from paypal post data" );
	$new_donation_id = dgx_donate_create_empty_donation_record ();
	dgx_donate_debug_log ( "new donation id = $new_donation_id" );

	// @todo - loop over the meta map translating paypal keys into our keys
	// @todo ADDRESS

	$payment_gross = isset( $_POST['payment_gross'] ) ? $_POST['payment_gross'] : '';
	$mc_gross      = isset( $_POST['mc_gross'] ) ? $_POST['mc_gross'] : '';

	$amount = empty( $payment_gross ) ? $mc_gross : $payment_gross;

	update_post_meta ( $new_donation_id, '_dgx_donate_donor_first_name', $_POST['first_name'] );
	update_post_meta ( $new_donation_id, '_dgx_donate_donor_last_name', $_POST['last_name'] );
	update_post_meta ( $new_donation_id, '_dgx_donate_donor_email', $_POST['payer_email'] );
	update_post_meta ( $new_donation_id, '_dgx_donate_amount', $amount );

	dgx_donate_debug_log ( "done with dgx_donate_create_donation_from_paypal_data, returning new id $new_donation_id" );

	return $new_donation_id;
}

/******************************************************************************************************/
function dgx_donate_get_donation_detail_link ( $donationID ) {

	$detailUrl = get_admin_url ();
	$detailUrl .= "admin.php?page=dgx_donate_menu_page&donation=" . $donationID;

	return $detailUrl;
}

function dgx_donate_get_donor_detail_link ( $donorEmail ) {

	$detailUrl = get_admin_url ();

	// TODO: URLENCODE?
	$detailUrl .= "admin.php?page=dgx_donate_menu_page&donor=" . $donorEmail;

	return $detailUrl;
}

/******************************************************************************************************/
function dgx_donate_init () {

	// Start a PHP session if none has been started yet
	// The means to test whether a session has been started varies by PHP version

	if( version_compare ( phpversion (), '5.4.0', '>=' ) ) {
		$session_already_started = ( session_status () === PHP_SESSION_ACTIVE );
	} else {
		$session_id              = session_id ();
		$session_already_started = ( ! empty( $session_id ) );
	}

	if( ! $session_already_started ) {
		session_start ();
	}

	// Initialize options to defaults as needed
	dgx_donate_init_defaults ();

	// Display an admin notice if we are in sandbox mode

	$payPalServer = get_option ( 'dgx_donate_paypal_server' );
	if( strcasecmp ( $payPalServer, "SANDBOX" ) == 0 ) {
		add_action ( 'admin_notices', 'dgx_donate_admin_sandbox_msg' );
	}
}

// add_action ( 'init', 'dgx_donate_init' );

/******************************************************************************************************/
function dgx_donate_admin_sandbox_msg () {

	echo "<div class=\"error\">";
	echo "<p>";
	echo esc_html__ (
		'Warning - Seamless Donations is currently configured to use the Sandbox (Test Server).',
		'seamless-donations' );
	echo "</p>";
	echo "</div>";
}

/******************************************************************************************************/
function dgx_donate_get_month_year_selector ( $monthSelectName, $yearSelectName ) {

	$output = "<select name=\"$monthSelectName\">";

	for( $month = 1; $month <= 12; $month ++ ) {
		$formattedMonth = sprintf ( "%02u", $month );
		$output .= "<option value=\"$formattedMonth\"> $formattedMonth </option>\n";
	}

	$output .= "</select>";

	$output .= " / ";

	$output .= "<select name=\"$yearSelectName\">";

	$startYear = date ( 'Y' );
	$startYear = intval ( $startYear );
	$endYear   = $startYear + 15;

	for( $year = $startYear; $year <= $endYear; $year ++ ) {
		$output .= "<option value=\"$year\"> $year </option>\n";
	}

	$output .= "</select>";

	return $output;
}

/******************************************************************************************************/
function dgx_donate_get_donation_section ( $formContent ) {

	$output = "";
	$output .= "<div class='dgx-donate-form-section' id='dgx-donate-form-donation-section'>\n";
	$output .= "<h2>" . esc_html__ ( 'Donation Information', 'seamless-donations' ) . "</h2>\n";

	$output .= "<p>" . esc_html__ ( 'I would like to make a donation in the amount of:', 'seamless-donations' ) .
	           "</p>";

	$output .= "<p>";
	$checked      = " checked=\"checked\" ";
	$classmod     = "";
	$givingLevels = dgx_donate_get_giving_levels ();
	foreach( $givingLevels as $givingLevel ) {
		$key = dgx_donate_get_giving_level_key ( $givingLevel );

		if( dgx_donate_is_giving_level_enabled ( $givingLevel ) ) {
			$formattedAmount = dgx_donate_get_escaped_formatted_amount ( $givingLevel, 0 );
			$output .= "<input $classmod type=\"radio\" name=\"_dgx_donate_amount\" value=\"$givingLevel\" $checked /> $formattedAmount ";
			$checked  = ""; // only select the first one
			$classmod = " class=\"horiz\" "; // only classmod the second and higher ones
		}
	}
	$output .= "</p>";

	$output .= "<p><input type='radio' name='_dgx_donate_amount' value='OTHER' id='dgx-donate-other-radio' />";
	$output .= esc_html__ ( 'Other: ', 'seamless-donations' );
	$output .= "<input type=\"text\" class=\"aftertext\" id=\"dgx-donate-other-input\" name=\"_dgx_donate_user_amount\" />";
	$output .= "</p>\n";

	// Designated Funds

	if( 'true' == get_option ( 'dgx_donate_show_designated_funds_section' ) ) {
		$showFundCount = 0;
		$fundArray     = get_option ( 'dgx_donate_designated_funds' );

		if( ! empty( $fundArray ) ) {
			ksort ( $fundArray );

			foreach( (array) $fundArray as $key => $value ) {
				if( strcasecmp ( $fundArray[ $key ], "SHOW" ) == 0 ) {
					$showFundCount = $showFundCount + 1;
				}
			}
		}

		if( $showFundCount > 0 ) {
			$output .= "<p>";
			$output .= "<input type='checkbox' id='dgx-donate-designated' name='_dgx_donate_designated'/>";
			$output .= esc_html__ (
				"I would like to designate this donation to a specific fund", 'seamless-donations' );
			$output .= "</p>";

			$output .= "<div class='dgx-donate-form-designated-box'>";
			$output .= "<p>" . esc_html__ ( 'Designated Fund: ', 'seamless-donations' ) . " ";
			$output .= "<select class='aftertext' name='_dgx_donate_designated_fund'>";

			foreach( (array) $fundArray as $key => $value ) {
				if( strcasecmp ( $fundArray[ $key ], "SHOW" ) == 0 ) {
					$fundName = stripslashes ( $key );
					$output .= "<option value='" . esc_attr ( $fundName ) . "' > " . esc_html ( $fundName ) .
					           "</option>";
				}
			}

			$output .= "</select>";
			$output .= "</p>";
			$output .= "</div>"; /* dgx-donate-form-designated-box */
		}
	}

	// Repeating donations
	if( 'true' == get_option ( 'dgx_donate_show_repeating_option' ) ) {
		$output .= "<p>";
		$output .= "<input type='checkbox' id='dgx-donate-repeating' name='_dgx_donate_repeating'/>";
		$output .= esc_html__ ( "I would like this donation to automatically repeat each month", 'seamless-donations' );
		$output .= "</p>";
	}

	$output .= "</div>"; /* dgx-donate-form-section */

	$formContent .= $output;

	return $formContent;
}

/******************************************************************************************************/
function dgx_donate_get_tribute_section ( $formContent ) {

	if( 'true' == get_option ( 'dgx_donate_show_tribute_section' ) ) {
		$honoree_state    = get_option ( 'dgx_donate_default_state' );
		$honoree_province = get_option ( 'dgx_donate_default_province' );
		$honoree_country  = get_option ( 'dgx_donate_default_country' );

		$output = "";
		$output .= "<div class='dgx-donate-form-section' id='dgx-donate-form-tribute-section'>\n";
		$output .= "<h2>" . esc_html__ ( 'Tribute Gift', 'seamless-donations' ) . "</h2>\n";
		$output .= "<div class='dgx-donate-form-expander'>\n";
		$output .= "<p class='dgx-donate-form-expander-header'>";
		$output .= "<input type='checkbox' id='dgx-donate-tribute' name='_dgx_donate_tribute_gift' /> ";
		$output .= esc_html__ ( 'Check here to donate in honor or memory of someone', 'seamless-donations' ) .
		           " </p>\n";
		$output .= "<div class='dgx-donate-form-tribute-box'>\n";
		$output .= "<p>";
		$output .= "<input type='checkbox' name='_dgx_donate_memorial_gift' />";
		$output .= esc_html__ ( 'Check here if this is a memorial gift', 'seamless-donations' ) . " ";
		$output .= "</p>\n";
		$output .= "<hr/>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_name'>" . esc_html__ ( "Honoree's Name:", 'seamless-donations' ) .
		           " </label>";
		$output .= "<input type='text' name='_dgx_donate_honoree_name' size='20' value='' />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<input type='radio' name='_dgx_donate_honor_by_email' value='TRUE' /> ";
		$output .= esc_html__ ( 'Send acknowledgement via email to ', 'seamless-donations' ) . " ";
		$output .= "</p>";
		$output .= "<div class='dgx-donate-form-subsection'>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_address'>" . esc_html__ ( 'Name:', 'seamless-donations' ) .
		           " </label>";
		$output .= "<input type='text' name='_dgx_donate_honoree_email_name' size='20' value='' />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_email'>" . esc_html__ ( 'Email:', 'seamless-donations' ) .
		           " </label>";
		$output .= "<input type='text' name='_dgx_donate_honoree_email' size='20' value='' />";
		$output .= "</p>";
		$output .= "</div>";
		$output .= "<p>";
		$output .= "<input type='radio' name='_dgx_donate_honor_by_email' value='FALSE' /> ";
		$output .= esc_html__ ( 'Send acknowledgement via postal mail to ', 'seamless-donations' ) . " ";
		$output .= "</p>";
		$output .= "<div class='dgx-donate-form-subsection'>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_address'>" . esc_html__ ( 'Name:', 'seamless-donations' ) .
		           " </label>";
		$output .= "<input type='text' name='_dgx_donate_honoree_post_name' size='20' value='' />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_address'>" . esc_html__ ( 'Address:', 'seamless-donations' ) .
		           " </label>";
		$output .= "<input type='text' name='_dgx_donate_honoree_address' size='20' value='' />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_city'>" . esc_html__ ( 'City:', 'seamless-donations' ) .
		           " </label>";
		$output .= "<input type='text' name='_dgx_donate_honoree_city' value='' />";
		$output .= "</p>";

		$output .= "<div class='dgx_donate_geography_selects'>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_state'>" . esc_html__ ( 'State:', 'seamless-donations' ) .
		           "</label>";
		$output .= dgx_donate_get_state_selector ( "_dgx_donate_honoree_state", $honoree_state );
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_province'>" . esc_html__ ( 'Province:', 'seamless-donations' ) .
		           "</label>";
		$output .= dgx_donate_get_province_selector ( "_dgx_donate_honoree_province", $honoree_province );
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_country'>" . esc_html__ ( 'Country:', 'seamless-donations' ) .
		           "</label>";
		$output .= dgx_donate_get_country_selector ( "_dgx_donate_honoree_country", $honoree_country );
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_honoree_zip'>" . esc_html__ ( 'Postal Code:', 'seamless-donations' ) .
		           "</label>";
		$output .= "<input class='dgx_donate_zip_input' type='text' name='_dgx_donate_honoree_zip' size='10' value='' />";
		$output .= "</p>";
		$output .= "</div>"; // dgx_donate_geography_selects

		$output .= "</div>"; /* dgx-donate-form-subsection */
		$output .= "</div>"; /* dgx-donate-form-tribute-box */
		$output .= "</div>"; /* dgx-donate-form-expander */
		$output .= "</div>\n"; /* dgx-donate-form-section */

		$formContent .= $output;
	}

	return $formContent;
}

/******************************************************************************************************/
function dgx_donate_get_employer_section ( $form_content ) {

	if( 'true' == get_option ( 'dgx_donate_show_employer_section' ) ) {
		$output = "";
		$output .= "<div class='dgx-donate-form-section' id='dgx-donate-form-employer-section'>";
		$output .= "<h2>" . esc_html__ ( 'Employer Match', 'seamless-donations' ) . "</h2>";
		$output .= "<p>";
		$output .= "<input type='checkbox' id='dgx-donate-employer' name='_dgx_donate_employer_match' /> ";
		$output .= esc_html__ ( 'Check here if your employer matches donations', 'seamless-donations' ) . "</p>";
		$output .= "</div>"; /* dgx-donate-form-section */

		$form_content .= $output;
	}

	return $form_content;
}

/******************************************************************************************************/
function dgx_donate_get_donor_section ( $form_content ) {

	$output = "";
	$output .= "<div class='dgx-donate-form-section' id='dgx-donate-form-donor-section'>";
	$output .= "<h2>" . esc_html__ ( 'Donor Information', 'seamless-donations' ) . "</h2>";
	$output .= "<p>";
	$output .= "<label for='_dgx_donate_donor_first_name'>" . esc_html__ ( 'First Name:', 'seamless-donations' ) .
	           " </label>";
	$output .= "<input type='text' class='required' name='_dgx_donate_donor_first_name' value='' />";
	$output .= "</p>";
	$output .= "<p>";
	$output .= "<label for='_dgx_donate_donor_last_name'>" . esc_html__ ( 'Last Name:', 'seamless-donations' ) .
	           " </label>";
	$output .= "<input type='text' class='required' name='_dgx_donate_donor_last_name' value='' />";
	$output .= "</p>";

	$show_donor_telephone_field = get_option ( 'dgx_donate_show_donor_telephone_field' );
	if( 'false' !== $show_donor_telephone_field ) {
		$required = ( 'required' == $show_donor_telephone_field ) ? "class='required'" : '';
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_donor_phone'>" . esc_html__ ( 'Phone:', 'seamless-donations' ) .
		           " </label>";
		$output .= "<input type='text' {$required} name='_dgx_donate_donor_phone' value='' />";
		$output .= "</p>";
	}

	$show_donor_employer_field    = get_option ( 'dgx_donate_show_donor_employer_field' );
	$show_employer_match_checkbox = get_option (
		'dgx_donate_show_employer_section' ); // i.e. they are showing the matching checkbox section
	if( ( 'false' !== $show_donor_employer_field ) || ( 'false' !== $show_employer_match_checkbox ) ) {
		$required = ( 'required' == $show_donor_employer_field ) ? "class='required'" : '';
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_employer_name'>" . esc_html__ ( 'Employer:', 'seamless-donations' ) .
		           "</label>";
		$output .= "<input type='text' {$required} name='_dgx_donate_employer_name' value='' />";
		$output .= "</p>";
	}

	$show_donor_occupation_field = get_option ( 'dgx_donate_show_donor_occupation_field' );
	if( ( 'false' !== $show_donor_employer_field ) ) {
		$required = ( 'required' == $show_donor_occupation_field ) ? "class='required'" : '';
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_occupation'>" . esc_html__ ( 'Occupation:', 'seamless-donations' ) .
		           "</label>";
		$output .= "<input type='text' {$required} name='_dgx_donate_occupation' value='' />";
		$output .= "</p>";
	}

	$output .= "<p>";
	$output .= "<label for='_dgx_donate_donor_email'>" . esc_html__ ( 'Email:', 'seamless-donations' ) . " </label>";
	$output .= "<input type='text' class='required' name='_dgx_donate_donor_email' size='20' value='' />";
	$output .= "</p>";

	if( 'true' == get_option ( 'dgx_donate_show_mailing_list_option' ) ) {
		$output .= "<p>";
		$output .= "<input type='checkbox' name='_dgx_donate_add_to_mailing_list' />" .
		           esc_html__ ( 'Add me to your mailing list', 'seamless-donations' );
		$output .= "</p>";
	}

	if( 'true' == get_option ( 'dgx_donate_show_anonymous_option' ) ) {
		$output .= "<p>";
		$output .= "<input type='checkbox' name='_dgx_donate_anonymous' />";
		$output .= esc_html__ (
			'Please do not publish my name.  I would like to remain anonymous.', 'seamless-donations' );
		$output .= "</p>";
	}

	$output .= "</div>\n";

	$form_content .= $output;

	return $form_content;
}

/******************************************************************************************************/
function dgx_donate_get_billing_section ( $form_content ) {

	if( 'true' == get_option ( 'dgx_donate_show_donor_address_fields' ) ) {
		$donor_state    = get_option ( 'dgx_donate_default_state' );
		$donor_province = get_option ( 'dgx_donate_default_province' );
		$donor_country  = get_option ( 'dgx_donate_default_country' );

		$output = "";
		$output .= "<div class='dgx-donate-form-section' id='dgx-donate-form-address-section'>\n";
		$output .= "<h2>" . esc_html__ ( 'Donor Address', 'dgx_donate' ) . "</h2>\n";

		$output .= "<p>";
		$output .= "<label for='_dgx_donate_donor_address'>" . esc_html__ ( 'Address:', 'seamless-donations' ) .
		           " </label>";
		$output .= "<input type='text' class='required' name='_dgx_donate_donor_address'  size='20' value='' />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_donor_address2'>" . esc_html__ ( 'Address 2:', 'seamless-donations' );
		$output .= " <span class='dgx-donate-comment'>" . esc_html__ ( '(optional)', 'seamless-donations' ) .
		           "</span> </label>";
		$output .= "<input type='text' name='_dgx_donate_donor_address2'  size='20' value='' />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_donor_city'>" . esc_html__ ( 'City:', 'seamless-donations' ) . " </label>";
		$output .= "<input type='text' class='required' name='_dgx_donate_donor_city' value='' /> ";
		$output .= "</p>";

		$output .= "<div class='dgx_donate_geography_selects'>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_donor_state'>" . esc_html__ ( 'State:', 'seamless-donations' ) . "</label>";
		$output .= dgx_donate_get_state_selector ( "_dgx_donate_donor_state", $donor_state );
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_donor_province'>" . esc_html__ ( 'Province:', 'seamless-donations' ) .
		           "</label>";
		$output .= dgx_donate_get_province_selector ( "_dgx_donate_donor_province", $donor_province );
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_donor_country'>" . esc_html__ ( 'Country:', 'seamless-donations' ) .
		           "</label>";
		$output .= dgx_donate_get_country_selector ( "_dgx_donate_donor_country", $donor_country );
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<label for='_dgx_donate_donor_zip'>" . esc_html__ ( 'Postal Code:', 'seamless-donations' ) .
		           "</label>";
		$output .= "<input class='dgx_donate_zip_input' type='text' name='_dgx_donate_donor_zip'  size='10' value='' />";
		$output .= "</p>";
		$output .= "<p>";
		$output .= "<input class='dgx_donate_uk_gift_aid' type='checkbox' name='_dgx_donate_uk_gift_aid' />";
		$output .= esc_html ( 'I am a UK taxpayer and my gift qualifies for Gift Aid.', 'seamless-donations' );
		$output .= "</p>";
		$output .= "</div>"; // dgx_donate_geography_selects

		$output .= "</div>\n";

		$form_content .= $output;
	}

	return $form_content;
}

/******************************************************************************************************/
add_shortcode ( 'dgx-donate', 'dgx_donate_shortcode' );

function dgx_donate_shortcode ( $atts ) {

	$show_thanks = false;
	if( isset( $_GET['thanks'] ) ) {
		$show_thanks = true;
	} else if( isset( $_GET['auth'] ) ) {
		$show_thanks = true;
	}

	// Switch
	if( $show_thanks ) {
		$output = dgx_donate_display_thank_you ();
	} else {
		$output   = "";
		$sd4_mode = get_option ( 'dgx_donate_start_in_sd4_mode' );
		if( $sd4_mode != false ) {
			$output .= "<div style='background-color:red; color:white'>";
			$output .= "<P style='padding:5px;'>Warning: This form is out of date. ";
			$output .= "Please update using the [seamless-donations] shortcode.</P>";
			$output .= "</div>";
		} else {
			$output = apply_filters ( 'dgx_donate_donation_form', $output );

			if( empty( $output ) ) {
				$output
					= "<p>Error: No payment gateway selected.  Please choose a payment gateway in Seamless Donations >> Settings.</p>";
			}
		}
	}

	return $output;
}

/******************************************************************************************************/
function dgx_donate_send_thank_you_email ( $donationID, $testAddress = "" ) {

	if( ! empty( $testAddress ) ) {
		// Fill in dummy data
		$toEmail = $testAddress;
		// firstname
		$firstName = "Jane";
		// lastname
		$lastName = "Doe";
		// amount
		$formatted_amount = "$100.00";
		// fundname
		$fund = "Tesla Scholarship";
		// repeating y/n
		$repeating = "TRUE";
		// designated y/n
		$designated = "TRUE";
		// anonymous y/n
		$anonymous = "TRUE";
		// mailinglistjoin y/n
		$mailingListJoin = "TRUE";
		// tribute y/n
		$tribute = "TRUE";
		// employer match
		$employer_name = "Global Corporation";
	} else {
		// Get data from donationID
		$toEmail = get_post_meta ( $donationID, '_dgx_donate_donor_email', true );
		// firstname
		$firstName = get_post_meta ( $donationID, '_dgx_donate_donor_first_name', true );
		// lastname
		$lastName = get_post_meta ( $donationID, '_dgx_donate_donor_last_name', true );
		// amount
		$amount           = get_post_meta ( $donationID, '_dgx_donate_amount', true );
		$currency_code    = dgx_donate_get_donation_currency_code ( $donationID );
		$formatted_amount = dgx_donate_get_plain_formatted_amount ( $amount, 2, $currency_code, true );
		// fundname
		$fund = get_post_meta ( $donationID, '_dgx_donate_designated_fund', true );
		// recurring y/n
		$repeating = get_post_meta ( $donationID, '_dgx_donate_repeating', true );
		// designated y/n
		$designated = get_post_meta ( $donationID, '_dgx_donate_designated', true );
		// anonymous y/n
		$anonymous = get_post_meta ( $donationID, '_dgx_donate_anonymous', true );
		// mailinglistjoin y/n
		$mailingListJoin = get_post_meta ( $donationID, '_dgx_donate_add_to_mailing_list', true );
		// tribute y/n
		$tribute = get_post_meta ( $donationID, '_dgx_donate_tribute_gift', true );
		// employer match
		$employer_name = get_post_meta ( $donationID, '_dgx_doname_employer_name', true );
	}

	$subject = get_option ( 'dgx_donate_email_subj' );
	$subject = stripslashes ( $subject );

	$body      = get_option ( 'dgx_donate_email_body' );
	$body      = str_replace ( "[firstname]", $firstName, $body );
	$body      = str_replace ( "[lastname]", $lastName, $body );
	$body      = str_replace ( "[amount]", $formatted_amount, $body );
	$body      = str_replace ( "[fund]", $fund, $body );
	$body      = stripslashes ( $body );
	$emailBody = $body;
	$emailBody .= "\n\n";

	if( ! empty( $repeating ) ) {
		$text = get_option ( 'dgx_donate_email_recur' );
		$text = str_replace ( "[amount]", $formatted_amount, $text );
		$text = stripslashes ( $text );
		$emailBody .= $text;
		$emailBody .= "\n\n";
	}

	if( ! empty( $designated ) ) {
		$text = get_option ( 'dgx_donate_email_desig' );
		$text = str_replace ( "[fund]", $fund, $text );
		$text = stripslashes ( $text );
		$emailBody .= $text;
		$emailBody .= "\n\n";
	}

	if( ! empty( $anonymous ) ) {
		$text = get_option ( 'dgx_donate_email_anon' );
		$text = stripslashes ( $text );
		$emailBody .= $text;
		$emailBody .= "\n\n";
	}

	if( ! empty( $mailingListJoin ) ) {
		$text = get_option ( 'dgx_donate_email_list' );
		$text = stripslashes ( $text );
		$emailBody .= $text;
		$emailBody .= "\n\n";
	}

	if( ! empty( $tribute ) ) {
		$text = get_option ( 'dgx_donate_email_trib' );
		$text = stripslashes ( $text );
		$emailBody .= $text;
		$emailBody .= "\n\n";
	}

	if( ! empty( $employer_name ) ) {
		$text = get_option ( 'dgx_donate_email_empl' );
		$text = stripslashes ( $text );
		$emailBody .= $text;
		$emailBody .= "\n\n";
	}

	$text = get_option ( 'dgx_donate_email_close' );
	$text = stripslashes ( $text );
	$emailBody .= $text;
	$emailBody .= "\n\n";

	$text = get_option ( 'dgx_donate_email_sig' );
	$text = stripslashes ( $text );
	$emailBody .= $text;
	$emailBody .= "\n";

	$header             = "From: ";
	$from_email_name    = get_option ( 'dgx_donate_email_name' );
	$from_email_address = get_option ( 'dgx_donate_email_reply' );
	if( empty( $from_email_name ) ) {
		$header .= $from_email_address;
	} else {
		$header .= "\"" . $from_email_name . "\" <" . $from_email_address . ">\r\n";
	}

	$emailBody = apply_filters ( 'dgx_donate_thank_you_email_body', $emailBody );

	$mail_sent = wp_mail ( $toEmail, $subject, $emailBody, $header );

	if( ! $mail_sent ) {
		dgx_donate_debug_log ( "Error: Could NOT send mail." );
		dgx_donate_debug_log ( "Subject: $subject" );
		dgx_donate_debug_log ( "To Email: $toEmail" );
	}
}

/******************************************************************************************************/
function dgx_donate_send_donation_notification ( $donationID ) {

	$fromEmail = get_option ( 'dgx_donate_reply_email' );
	$subject   = "[Seamless Donations] " . __ ( 'A donation has been received', 'seamless-donations' );
	$body
	           =
		__ ( 'A donation has been received.  Here are some details about the donation.', 'seamless-donations' ) . "\n";
	$body .= "\n";

	$body .= "Donor:\n";
	$firstName  = get_post_meta ( $donationID, '_dgx_donate_donor_first_name', true );
	$lastName   = get_post_meta ( $donationID, '_dgx_donate_donor_last_name', true );
	$city       = get_post_meta ( $donationID, '_dgx_donate_donor_city', true );
	$state      = get_post_meta ( $donationID, '_dgx_donate_donor_state', true );
	$zip        = get_post_meta ( $donationID, '_dgx_donate_donor_zip', true );
	$donorEmail = get_post_meta ( $donationID, '_dgx_donate_donor_email', true );
	$body .= "$firstName $lastName\n";
	$body .= "$city $state $zip\n";
	$body .= "$donorEmail\n";
	$body .= "\n";

	$tributeGift = get_post_meta ( $donationID, '_dgx_donate_tribute_gift', true );
	if( ! empty( $tributeGift ) ) {
		$body .= __ (
			         'NOTE:  The donor is making this donation in honor of / in memory of someone.',
			         'seamless-donations' ) .
		         " ";
		$body .= __ (
			         'Please see the donation details (using the link below) for more information.',
			         'seamless-donations' ) .
		         "\n";
		$body .= "\n";
	}

	$employer_match = get_post_meta ( $donationID, '_dgx_donate_employer_match', true );
	if( ! empty( $employer_match ) ) {
		$body .= __ ( 'NOTE:  The donor is making this donation with an employer match.', 'seamless-donations' ) . " ";
		$body .= __ (
			         'Please see the donation details (using the link below) for more information.',
			         'seamless-donations' ) .
		         "\n";
		$body .= "\n";
	}

	$amount                  = get_post_meta ( $donationID, '_dgx_donate_amount', true );
	$currency_code           = dgx_donate_get_donation_currency_code ( $donationID );
	$formattedDonationAmount = dgx_donate_get_plain_formatted_amount ( $amount, 2, $currency_code, true );
	$body .= __ ( 'Donation:', 'seamless-donations' ) . "\n";
	$body .= __ ( 'Amount:', 'seamless-donations' ) . " $formattedDonationAmount\n";

	$body .= "\n";
	$body .= __ ( 'Click on the following link to view all details for this donation:', 'seamless-donations' ) . "\n";

	$sd4_mode = get_option ( 'dgx_donate_start_in_sd4_mode' );
	if( $sd4_mode == false ) {
		$secureDonateLink = dgx_donate_get_donation_detail_link ( $donationID );
	} else {
		$secureDonateLink = seamless_donations_get_donation_detail_link ( $donationID );
	}

	$donateLink = str_replace ( "https:", "http:", $secureDonateLink );
	$body .= $donateLink;
	$body .= "\n";

	// Loop on addresses
	$notifyEmails  = get_option ( 'dgx_donate_notify_emails' );
	$notifyEmailAr = explode ( ',', $notifyEmails );

	foreach( $notifyEmailAr as $notifyEmail ) {
		$notifyEmail = trim ( $notifyEmail );
		if( ! empty( $notifyEmail ) ) {
			$headers = "From: $fromEmail\r\n";

			$mail_sent = wp_mail ( $notifyEmail, $subject, $body, $headers );

			if( ! $mail_sent ) {
				dgx_donate_debug_log ( "Error: Could NOT send mail." );
				dgx_donate_debug_log ( "Subject: $subject" );
				dgx_donate_debug_log ( "To Email: $notifyEmail" );
			}
		}
	}
}

function dgx_donate_init_session() {
	// Start a PHP session if none has been started yet
	// The means to test whether a session has been started varies by PHP version

	if( version_compare ( phpversion (), '5.4.0', '>=' ) ) {
		$session_already_started = ( session_status () === PHP_SESSION_ACTIVE );
	} else {
		$session_id              = session_id ();
		$session_already_started = ( ! empty( $session_id ) );
	}

	if( ! $session_already_started ) {
		session_start ();
	}
}