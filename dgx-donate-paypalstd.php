<?php
/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

/* PayPal Website Payments Standard Module for Seamless Donations */

define( 'DGXDONATEPAYPALSTD', 'DGXDONATEPAYPALSTD' );

/******************************************************************************************************/
function dgx_donate_paypalstd_init () {

	// The showing and saving of settings uses actions
	add_action ( 'dgx_donate_show_settings_forms', 'dgx_donate_show_paypalstd_settings_form' );
	add_action ( 'dgx_donate_save_settings_forms', 'dgx_donate_save_paypalstd_settings_form' );

	// The donation form content uses a filter since it must return the form to the caller
	add_filter ( 'dgx_donate_donation_form', 'dgx_donate_show_paypalstd_donation_form' );
}

add_action ( 'init', 'dgx_donate_paypalstd_init' );

/******************************************************************************************************/
function dgx_donate_paypalstd_enqueue_scripts () {

	$load_in_footer = ( 'true' == get_option ( 'dgx_donate_scripts_in_footer' ) );
	wp_enqueue_script ( 'jquery' );
	$script_url = plugins_url ( '/js/paypalstd-script.js', __FILE__ );
	wp_enqueue_script ( 'dgx_donate_paypalstd_script', $script_url, array( 'jquery' ), false, $load_in_footer );
}

add_action ( 'wp_enqueue_scripts', 'dgx_donate_paypalstd_enqueue_scripts' );

/******************************************************************************************************/
function dgx_donate_show_paypalstd_settings_form () {

	// First, show our radio button
	$paymentGateway = get_option ( 'dgx_donate_payment_gateway' );
	if( $paymentGateway == DGXDONATEPAYPALSTD ) {
		$checked = "checked";
	} else {
		$checked = "";
	}

	echo "<p class=\"dgxdonategatewayname\"><input type=\"radio\" name=\"paymentgateway\" value=\"";
	echo DGXDONATEPAYPALSTD;
	echo "\" $checked /> <b>PayPal Standard</b></p>";

	// Now show our form content
	$checkSandbox = "";
	$checkLive    = "";
	$payPalServer = get_option ( 'dgx_donate_paypal_server' );
	if( strcasecmp ( $payPalServer, "SANDBOX" ) == 0 ) {
		$checkSandbox = "checked";
	} else {
		$checkLive = "checked";
	}

	$payPalEmail = get_option ( 'dgx_donate_paypal_email' );

	echo "<div class=\"form-field\">\n";
	echo "<label for='paypalemail'>" . esc_html__ ( 'PayPal Email Address', 'seamless-donations' ) . "</label><br/>\n";
	echo "<input type=\"text\" name=\"paypalemail\" value=\"$payPalEmail\" />\n";
	echo "<p class='description'>" .
	     esc_html__ ( 'The email address at which to receive payments.', 'seamless-donations' ) . "</p>\n";
	echo "</div> <!-- form-field --> \n";

	echo "<p>" . esc_html__ ( 'Mode:', 'seamless-donations' ) . " \n";
	echo "<input type='radio' name='paypalserver' value='SANDBOX' $checkSandbox /> " .
	     esc_html__ ( 'Sandbox (Test Server)', 'seamless-donations' ) . " ";
	echo "<input type='radio' name='paypalserver' value='LIVE' $checkLive /> " .
	     esc_html__ ( 'Live (Production Server)', 'seamless-donations' ) . "</p>";
	echo "<p>" . __ ( 'IPN URL', 'seamless-donations' ) . "<p>";
	$notify_url = plugins_url ( '/dgx-donate-paypalstd-ipn.php', __FILE__ );
	echo "<pre>$notify_url</pre>";
}

/******************************************************************************************************/
function dgx_donate_save_paypalstd_settings_form () {

	$paymentGateway = "";
	$payPalServer   = "";
	$payPalEmail    = "";

	if( isset( $_POST['paymentgateway'] ) ) {
		$paymentGateway = $_POST['paymentgateway'];
	}
	if( isset( $_POST['paypalserver'] ) ) {
		$payPalServer = $_POST['paypalserver'];
	}
	if( isset( $_POST['paypalemail'] ) ) {
		$payPalEmail = $_POST['paypalemail'];
	}

	// If they set the paymentGateway, record the setting
	// It is OK for all gateways to do this (so at least one does)
	if( ! empty( $paymentGateway ) ) {
		update_option ( 'dgx_donate_payment_gateway', $paymentGateway );
	}

	// If they set the paypalemail, record the setting
	if( ! empty( $payPalEmail ) ) {
		$payPalEmail = trim ( $payPalEmail );
		if( is_email ( $payPalEmail ) ) {
			update_option ( 'dgx_donate_paypal_email', $payPalEmail );
		}
	}

	// If they set the paypal server type (sandbox or live), record the setting
	if( ! empty( $payPalServer ) ) {
		update_option ( 'dgx_donate_paypal_server', $payPalServer );
	}
}

/******************************************************************************************************/
function dgx_donate_show_paypalstd_donation_form ( $content ) {

	// If we are the actively selected gateway
	$paymentGateway = get_option ( 'dgx_donate_payment_gateway' );
	if( $paymentGateway == DGXDONATEPAYPALSTD ) {
		// Open the form
		$content .= "<!-- SD " . dgx_donate_get_version () . " legacy mode -->";
		$content .= "<form id=\"dgx-donate-form\" method=\"post\" onsubmit=\"return DgxDonateDoCheckout();\" >";

		// Save the session ID as a hidden input
		$session_id = 'dgxdonate_' . substr ( session_id (), 0, 10 ) . '_' . time ();
		$content .= "<input type=\"hidden\" name=\"_dgx_donate_session_id\" value=\"$session_id\" />";

		// Start the outermost container
		$content .= "<div id=\"dgx-donate-container\">\n";

		// Pick and choose the built in sections this gateway supports
		$content = dgx_donate_paypalstd_warning_section ( $content );
		$content = dgx_donate_get_donation_section ( $content );
		$content = dgx_donate_get_tribute_section ( $content );
		$content = dgx_donate_get_employer_section ( $content );
		$content = dgx_donate_get_donor_section ( $content );
		$content = dgx_donate_get_billing_section ( $content );
		$content = dgx_donate_paypalstd_payment_section ( $content );

		// Close the outermost container
		$content .= "</div>\n";

		// Close the form
		$content .= "</form>\n";

		$content .= dgx_donate_paypalstd_get_hidden_form ();
	}

	return $content;
}

/******************************************************************************************************/
function dgx_donate_paypalstd_get_current_url () {

	if( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
		$http = 'https';
	} else {
		$http = 'http';
	}

	$currentUrl = $http . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	return $currentUrl;
}

/******************************************************************************************************/
function dgx_donate_paypalstd_get_hidden_form () {

	seamless_donations_debug_log ( "Inside dgx_donate_paypalstd_get_hidden_form" );
	$paypalEmail = get_option ( 'dgx_donate_paypal_email' );

	$payPalServer = get_option ( 'dgx_donate_paypal_server' );
	if( $payPalServer == "SANDBOX" ) {
		$formAction = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	} else {
		$formAction = "https://www.paypal.com/cgi-bin/webscr";
	}

	$notifyUrl = plugins_url ( '/dgx-donate-paypalstd-ipn.php', __FILE__ );

	$sessionID  = session_id ();
	$successUrl = dgx_donate_paypalstd_get_current_url ();

	if( strpos ( $successUrl, "?" ) === false ) {
		$successUrl .= "?";
	} else {
		$successUrl .= "&";
	}

	$successUrl .= "thanks=1&sessionid=";
	$successUrl .= "$sessionID";

	$currency_code = get_option ( 'dgx_donate_currency' );

	$item_name = apply_filters ( 'dgx_donate_item_name', __ ( 'Donation', 'seamless-donations' ) );

	$output = "";
	$output .= "<form id='dgx-donate-hidden-form' action='" . esc_attr ( $formAction ) .
	           "' method='post' target='_top' >";
	$output .= "<input type='hidden' name='cmd' value='_donations' />";
	$output .= "<input type='hidden' name='business' value='" . esc_attr ( $paypalEmail ) . "' />";
	$output .= "<input type='hidden' name='return' value='" . esc_attr ( $successUrl ) . "' />";

	$output .= "<input type='hidden' name='first_name' value='' /> ";
	$output .= "<input type='hidden' name='last_name' value='' />";
	$output .= "<input type='hidden' name='address1' value='' />";
	$output .= "<input type='hidden' name='address2' value='' />";
	$output .= "<input type='hidden' name='city' value='' />";

	$output .= "<input type='hidden' name='state' value='' />"; // removed if country not US or Canada
	$output .= "<input type='hidden' name='zip' value='' />";
	$output .= "<input type='hidden' name='country' value='' />";
	$output .= "<input type='hidden' name='email' value='' />";

	$output .= "<input type='hidden' name='custom' value='' />";
	$output .= "<input type='hidden' name='notify_url' value='" . esc_attr ( $notifyUrl ) . "' />";

	$output .= "<input type='hidden' name='item_name' value='" . esc_attr ( $item_name ) . "' />";
	$output .= "<input type='hidden' name='amount' value='1.00' />";
	$output .= "<input type='hidden' name='quantity' value='1' />";

	$output .= "<input type='hidden' name='currency_code' value='" . esc_attr ( $currency_code ) . "' />";

	$output .= "<input type='hidden' name='no_note' value='1' />";

	$output .= "<input type='hidden' name='src' value='1' />"; // removed when not repeating
	$output .= "<input type='hidden' name='p3' value='1' />";  // removed when not repeating
	$output .= "<input type='hidden' name='t3' value='1' />";  // removed when not repeating
	$output .= "<input type='hidden' name='a3' value='1' />";  // removed when not repeating

	$output .= "</form>";

	seamless_donations_debug_log ( "Exiting dgx_donate_paypalstd_get_hidden_form" );

	return $output;
}

/******************************************************************************************************/
function dgx_donate_paypalstd_warning_section ( $formContent ) {

	// Display any setup warnings we need to display here (e.g. running in test mode)
	$payPalServer = get_option ( 'dgx_donate_paypal_server' );
	if( $payPalServer == "SANDBOX" ) {
		$formContent .= "<div class='dgx-donate-form-section' id='dgx-donate-form-sandbox-warning'>";
		$formContent .= "<p>";
		$formContent .= esc_html__ (
			"Warning - Seamless Donations is currently configured to use the Sandbox (Test Server).",
			'seamless-donations' );
		$formContent .= "</p>";
		$formContent .= "</div>";
	}

	// Echo a NOSCRIPT warning
	$formContent .= "<noscript>";
	$formContent .= "<div class='dgx-donate-form-section' id='dgx-donate-form-noscript-warning'>";
	$formContent
		.=
		"<p>" . esc_html__ ( "Warning:  To make a donation, you must first enable JavaScript.", 'seamless-donations' ) .
		"</p>";
	$formContent .= "</div>";
	$formContent .= "</noscript>";

	return $formContent;
}

/******************************************************************************************************/
function dgx_donate_paypalstd_payment_section ( $form_content ) {

	// Show the button that kicks it all off

	$processing_image_url      = plugins_url ( '/images/ajax-loader.gif', __FILE__ );
	$button_image_url          = plugins_url ( '/images/paypal_btn_donate_lg.gif', __FILE__ );
	$disabled_button_image_url = plugins_url ( '/images/paypal_btn_donate_lg_disabled.gif', __FILE__ );

	$section = "<div class='dgx-donate-form-section' id='dgx-donate-form-payment-section'>"
	           . "<p>"
	           . "<input class='dgx-donate-pay-enabled' type='image' src='" . esc_url ( $button_image_url ) .
	           "' value='" . esc_attr__ ( 'Donate Now', 'seamless-donations' ) . "'/>"
	           . "<img class='dgx-donate-pay-disabled' src='" . esc_url ( $disabled_button_image_url ) . "' />"
	           . "<img class='dgx-donate-busy' src='" . esc_url ( $processing_image_url ) . "' />"
	           . "</p>"
	           . "<p class='dgx-donate-error-msg'></p>"
	           . "</div>\n";

	$form_content .= $section;

	return $form_content;
}

/******************************************************************************************************/
function dgx_donate_paypalstd_detail () {

	echo "<p>TODO: dgx_donate_paypalstd_detail</p>";
}

/******************************************************************************************************/
function dgx_donate_paypalstd_ajax_checkout () {

	// Log
	dgx_donate_debug_log ( '----------------------------------------' );
	dgx_donate_debug_log ( 'DONATION TRANSACTION STARTED' );
	dgx_donate_debug_log ( 'Test mode: B' );
	$php_version = phpversion ();
	dgx_donate_debug_log ( "PHP Version: $php_version" );
	dgx_donate_debug_log ( "Seamless Donations Version: " . dgx_donate_get_version () );
	dgx_donate_debug_log ( "User browser: " . seamless_donations_get_browser_name() );
	dgx_donate_debug_log ( 'IPN: ' . plugins_url ( '/dgx-donate-paypalstd-ipn.php', __FILE__ ) );

	$nonce = $_POST['nonce'];
	if( ! wp_verify_nonce ( $nonce, 'dgx-donate-nonce' ) ) {
		dgx_donate_debug_log ( 'Payment process nonce validation failure.' );
		die( 'Access Denied.' );
	} else {
		dgx_donate_debug_log ( "Payment process nonce $nonce validated." );
	}

	$sd4_mode   = get_option ( 'dgx_donate_start_in_sd4_mode' );
	$session_id = $_POST['sessionID'];
	dgx_donate_debug_log ( "Session ID retrieved from _POST: $session_id" );

	// now attempt to retrieve session data to see if it already exists (which would trigger an error)
	if( $sd4_mode == false ) {
		// use the old transient system
		$session_data = get_transient ( $session_id );
		dgx_donate_debug_log ( 'Looking for pre-existing session data (legacy transient mode): ' . $session_id );
	} else {
		// use the new guid/audit db system
		$session_data = seamless_donations_get_audit_option ( $session_id );
		dgx_donate_debug_log ( 'Looking for pre-existing session data (guid/audit db mode): ' . $session_id );
	}

	if( $session_data !== false ) {
		dgx_donate_debug_log ( 'Session data already exists, returning false' );
		die();
	} else {

		dgx_donate_debug_log ( 'Duplicate session data not found. Payment process data assembly can proceed.' );

		// all of this no longer necessary for transfer to PayPal, just for storage in local audit table
		$referringUrl    = $_POST['referringUrl'];
		$donationAmount  = $_POST['donationAmount'];
		$userAmount      = $_POST['userAmount'];
		$repeating       = $_POST['repeating'];
		$designated      = $_POST['designated'];
		$designatedFund  = $_POST['designatedFund'];
		$tributeGift     = $_POST['tributeGift'];
		$memorialGift    = $_POST['memorialGift'];
		$honoreeName     = $_POST['honoreeName'];
		$honorByEmail    = $_POST['honorByEmail'];
		$honoreeEmail    = $_POST['honoreeEmail'];
		$honoreeAddress  = $_POST['honoreeAddress'];
		$honoreeCity     = $_POST['honoreeCity'];
		$honoreeState    = $_POST['honoreeState'];
		$honoreeProvince = $_POST['honoreeProvince'];
		$honoreeCountry  = $_POST['honoreeCountry'];

		if( 'US' == $honoreeCountry ) {
			$honoreeProvince = '';
		} else if( 'CA' == $honoreeCountry ) {
			$honoreeState = '';
		} else {
			$honoreeState    = '';
			$honoreeProvince = '';
		}

		$honoreeZip       = $_POST['honoreeZip'];
		$honoreeEmailName = $_POST['honoreeEmailName'];
		$honoreePostName  = $_POST['honoreePostName'];
		$firstName        = $_POST['firstName'];
		$lastName         = $_POST['lastName'];
		$phone            = $_POST['phone'];
		$email            = $_POST['email'];
		$addToMailingList = $_POST['addToMailingList'];
		$address          = $_POST['address'];
		$address2         = $_POST['address2'];
		$city             = $_POST['city'];
		$state            = $_POST['state'];
		$province         = $_POST['province'];
		$country          = $_POST['country'];

		if( 'US' == $country ) {
			$province = '';
		} else if( 'CA' == $country ) {
			$state = '';
		} else {
			$state    = '';
			$province = '';
		}

		$zip             = $_POST['zip'];
		$increaseToCover = $_POST['increaseToCover'];
		$anonymous       = $_POST['anonymous'];
		$employerMatch   = $_POST['employerMatch'];
		$employerName    = $_POST['employerName'];
		$occupation      = $_POST['occupation'];
		$ukGiftAid       = $_POST['ukGiftAid'];

		// Resolve the donation amount
		if( strcasecmp ( $donationAmount, "OTHER" ) == 0 ) {
			$amount = floatval ( $userAmount );
		} else {
			$amount = floatval ( $donationAmount );
		}
		if( $amount < 1.00 ) {
			$amount = 1.00;
		}

		// Repack the POST
		$post_data                     = array();
		$post_data['REFERRINGURL']     = $referringUrl;
		$post_data['SESSIONID']        = $session_id;
		$post_data['AMOUNT']           = $amount;
		$post_data['REPEATING']        = $repeating;
		$post_data['DESIGNATED']       = $designated;
		$post_data['DESIGNATEDFUND']   = $designatedFund;
		$post_data['TRIBUTEGIFT']      = $tributeGift;
		$post_data['MEMORIALGIFT']     = $memorialGift;
		$post_data['HONOREENAME']      = $honoreeName;
		$post_data['HONORBYEMAIL']     = $honorByEmail;
		$post_data['HONOREEEMAIL']     = $honoreeEmail;
		$post_data['HONOREEADDRESS']   = $honoreeAddress;
		$post_data['HONOREECITY']      = $honoreeCity;
		$post_data['HONOREESTATE']     = $honoreeState;
		$post_data['HONOREEPROVINCE']  = $honoreeProvince;
		$post_data['HONOREECOUNTRY']   = $honoreeCountry;
		$post_data['HONOREEZIP']       = $honoreeZip;
		$post_data['HONOREEEMAILNAME'] = $honoreeEmailName;
		$post_data['HONOREEPOSTNAME']  = $honoreePostName;
		$post_data['FIRSTNAME']        = $firstName;
		$post_data['LASTNAME']         = $lastName;
		$post_data['PHONE']            = $phone;
		$post_data['EMAIL']            = $email;
		$post_data['ADDTOMAILINGLIST'] = $addToMailingList;
		$post_data['ADDRESS']          = $address;
		$post_data['ADDRESS2']         = $address2;
		$post_data['CITY']             = $city;
		$post_data['STATE']            = $state;
		$post_data['PROVINCE']         = $province;
		$post_data['COUNTRY']          = $country;
		$post_data['ZIP']              = $zip;
		$post_data['INCREASETOCOVER']  = $increaseToCover;
		$post_data['ANONYMOUS']        = $anonymous;
		$post_data['PAYMENTMETHOD']    = "PayPal";
		$post_data['EMPLOYERMATCH']    = $employerMatch;
		$post_data['EMPLOYERNAME']     = $employerName;
		$post_data['OCCUPATION']       = $occupation;
		$post_data['UKGIFTAID']        = $ukGiftAid;
		$post_data['SDVERSION']        = dgx_donate_get_version ();

		// Sanitize the data (remove leading, trailing spaces quotes, brackets)
		foreach( $post_data as $key => $value ) {
			$temp              = trim ( $value );
			$temp              = str_replace ( "\"", "", $temp );
			$temp              = strip_tags ( $temp );
			$post_data[ $key ] = $temp;
		}

		if( $sd4_mode == false ) {
			// Save it all in a transient
			$transient_token = $post_data['SESSIONID'];
			set_transient ( $transient_token, $post_data, 7 * 24 * 60 * 60 ); // 7 days
			dgx_donate_debug_log ( 'Saving transaction data using legacy mode' );
		} else {
			seamless_donations_update_audit_option ( $session_id, $post_data );
			dgx_donate_debug_log ( 'Saving transaction data using guid/audit db mode' );
		}

		// more log data
		dgx_donate_debug_log ( 'Name: ' . $post_data['FIRSTNAME'] . ' ' . $post_data['LASTNAME'] );
		dgx_donate_debug_log ( 'Amount: ' . $post_data['AMOUNT'] );
		dgx_donate_debug_log ( "Preparation complete. It is now up to PayPal to return data via IPN." );


		// Return success to AJAX caller as " code | message "
		// A return code of 0 indicates success, and the returnMessage is ignored
		// A return code of 1 indicates failure, and the returnMessage contains the error message
		$returnMessage = "0|SUCCESS";

		echo $returnMessage;

		die(); // this is required to return a proper result

	}
}

add_action ( 'wp_ajax_dgx_donate_paypalstd_ajax_checkout', 'dgx_donate_paypalstd_ajax_checkout' );
add_action ( 'wp_ajax_nopriv_dgx_donate_paypalstd_ajax_checkout', 'dgx_donate_paypalstd_ajax_checkout' );
