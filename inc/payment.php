<?php

/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

function seamless_donations_process_payment() {

	// Log
	$paypal_server = get_option( 'dgx_donate_paypal_server' );

	dgx_donate_debug_log( '----------------------------------------' );
	dgx_donate_debug_log( 'DONATION TRANSACTION STARTED' );
	dgx_donate_debug_log( 'Processing mode: ' . $paypal_server );
	$php_version = phpversion();
	dgx_donate_debug_log( "PHP Version: $php_version" );
	dgx_donate_debug_log( "Seamless Donations Version: " . dgx_donate_get_version() );
	dgx_donate_debug_log( "User browser: " . seamless_donations_get_browser_name() );

	$http_ipn_url  = plugins_url( '/dgx-donate-paypalstd-ipn.php', dirname( __FILE__ ) );
	$https_ipn_url = plugins_url( '/pay/paypalstd/ipn.php', dirname( __FILE__ ) );
	$https_ipn_url = str_ireplace( 'http://', 'https://', $https_ipn_url ); // force https check

	dgx_donate_debug_log( 'IPN (http): ' . $http_ipn_url );
	dgx_donate_debug_log( 'IPN (https): ' . $https_ipn_url );

	$nonce_bypass = get_option( 'dgx_donate_ignore_form_nonce' );

	if ( $nonce_bypass != '1' ) {
		$nonce = $_POST['nonce'];
		if ( ! wp_verify_nonce( $nonce, 'dgx-donate-nonce' ) ) {
			$nonce_error = 'Payment process nonce validation failure. ';
			$nonce_error .= 'Consider turning on Ignore Form Nonce Value in the Seamless Donations ';
			$nonce_error .= 'Settings tab under Host Compatibility Options.';
			dgx_donate_debug_log( $nonce_error );
			die( 'Access Denied. See Seamless Donations log for details.' );
		} else {
			dgx_donate_debug_log( "Payment process nonce $nonce validated." );
		}
	}

	// todo: not getting session ID ***************************************************
	// todo: reattach the javascript verification code

	$sd4_mode   = get_option( 'dgx_donate_start_in_sd4_mode' );
	$session_id = $_POST['_dgx_donate_session_id'];
	dgx_donate_debug_log( "Session ID retrieved from _POST: $session_id" );

	// now attempt to retrieve session data to see if it already exists (which would trigger an error)
	if ( $sd4_mode == false ) {
		// use the old transient system
		$session_data = get_transient( $session_id );
		dgx_donate_debug_log( 'Looking for pre-existing session data (legacy transient mode): ' . $session_id );
	} else {
		// use the new guid/audit db system
		$session_data = seamless_donations_get_audit_option( $session_id );
		dgx_donate_debug_log( 'Looking for pre-existing session data (guid/audit db mode): ' . $session_id );
	}

	if ( $session_data !== false ) {
		dgx_donate_debug_log( 'Session data already exists, returning false' );
		die();
	} else {

		dgx_donate_debug_log( 'Duplicate session data not found. Payment process data assembly can proceed.' );

		// Repack the POST
		$post_data = array();

		if ( isset( $_POST['_dgx_donate_redirect_url'] ) ) {
			$post_data['REFERRINGURL'] = $_POST['_dgx_donate_redirect_url'];
		} else {
			$post_data['REFERRINGURL'] = '';
		}
		if ( isset( $_POST['_dgx_donate_success_url'] ) ) {
			$post_data['SUCCESSURL'] = $_POST['_dgx_donate_success_url'];
		} else {
			$post_data['SUCCESSURL'] = '';
		}
		if ( isset( $_POST['_dgx_donate_session_id'] ) ) {
			$post_data['SESSIONID'] = $_POST['_dgx_donate_session_id'];
		} else {
			$post_data['SESSIONID'] = '';
		}
		if ( isset( $_POST['_dgx_donate_repeating'] ) ) {
			$post_data['REPEATING'] = $_POST['_dgx_donate_repeating'];
		} else {
			$post_data['REPEATING'] = '';
		}
		if ( isset( $_POST['_dgx_donate_designated'] ) ) {
			$post_data['DESIGNATED'] = $_POST['_dgx_donate_designated'];
		} else {
			$post_data['DESIGNATED'] = '';
		}
		if ( isset( $_POST['_dgx_donate_designated_fund'] ) ) {
			$post_data['DESIGNATEDFUND'] = $_POST['_dgx_donate_designated_fund'];
		} else {
			$post_data['DESIGNATEDFUND'] = '';
		}
		if ( isset( $_POST['_dgx_donate_tribute_gift'] ) ) {
			$post_data['TRIBUTEGIFT'] = $_POST['_dgx_donate_tribute_gift'];
		} else {
			$post_data['TRIBUTEGIFT'] = '';
		}
		if ( isset( $_POST['_dgx_donate_memorial_gift'] ) ) {
			$post_data['MEMORIALGIFT'] = $_POST['_dgx_donate_memorial_gift'];
		} else {
			$post_data['MEMORIALGIFT'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_name'] ) ) {
			$post_data['HONOREENAME'] = $_POST['_dgx_donate_honoree_name'];
		} else {
			$post_data['HONOREENAME'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honor_by_email'] ) ) {
			$post_data['HONORBYEMAIL'] = $_POST['_dgx_donate_honor_by_email'];
		} else {
			$post_data['HONORBYEMAIL'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_email'] ) ) {
			$post_data['HONOREEEMAIL'] = $_POST['_dgx_donate_honoree_email'];
		} else {
			$post_data['HONOREEEMAIL'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_address'] ) ) {
			$post_data['HONOREEADDRESS'] = $_POST['_dgx_donate_honoree_address'];
		} else {
			$post_data['HONOREEADDRESS'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_city'] ) ) {
			$post_data['HONOREECITY'] = $_POST['_dgx_donate_honoree_city'];
		} else {
			$post_data['HONOREECITY'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_state'] ) ) {
			$post_data['HONOREESTATE'] = $_POST['_dgx_donate_honoree_state'];
		} else {
			$post_data['HONOREESTATE'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_province'] ) ) {
			$post_data['HONOREEPROVINCE'] = $_POST['_dgx_donate_honoree_province'];
		} else {
			$post_data['HONOREEPROVINCE'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_country'] ) ) {
			$post_data['HONOREECOUNTRY'] = $_POST['_dgx_donate_honoree_country'];
		} else {
			$post_data['HONOREECOUNTRY'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_zip'] ) ) {
			$post_data['HONOREEZIP'] = $_POST['_dgx_donate_honoree_zip'];
		} else {
			$post_data['HONOREEZIP'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_email_name'] ) ) {
			$post_data['HONOREEEMAILNAME'] = $_POST['_dgx_donate_honoree_email_name'];
		} else {
			$post_data['HONOREEEMAILNAME'] = '';
		}
		if ( isset( $_POST['_dgx_donate_honoree_post_name'] ) ) {
			$post_data['HONOREEPOSTNAME'] = $_POST['_dgx_donate_honoree_post_name'];
		} else {
			$post_data['HONOREEPOSTNAME'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_first_name'] ) ) {
			$post_data['FIRSTNAME'] = $_POST['_dgx_donate_donor_first_name'];
		} else {
			$post_data['FIRSTNAME'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_last_name'] ) ) {
			$post_data['LASTNAME'] = $_POST['_dgx_donate_donor_last_name'];
		} else {
			$post_data['LASTNAME'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_phone'] ) ) {
			$post_data['PHONE'] = $_POST['_dgx_donate_donor_phone'];
		} else {
			$post_data['PHONE'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_email'] ) ) {
			$post_data['EMAIL'] = $_POST['_dgx_donate_donor_email'];
		} else {
			$post_data['EMAIL'] = '';
		}
		if ( isset( $_POST['_dgx_donate_add_to_mailing_list'] ) ) {
			$post_data['ADDTOMAILINGLIST'] = $_POST['_dgx_donate_add_to_mailing_list'];
		} else {
			$post_data['ADDTOMAILINGLIST'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_address'] ) ) {
			$post_data['ADDRESS'] = $_POST['_dgx_donate_donor_address'];
		} else {
			$post_data['ADDRESS'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_address2'] ) ) {
			$post_data['ADDRESS2'] = $_POST['_dgx_donate_donor_address2'];
		} else {
			$post_data['ADDRESS2'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_city'] ) ) {
			$post_data['CITY'] = $_POST['_dgx_donate_donor_city'];
		} else {
			$post_data['CITY'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_state'] ) ) {
			$post_data['STATE'] = $_POST['_dgx_donate_donor_state'];
		} else {
			$post_data['STATE'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_province'] ) ) {
			$post_data['PROVINCE'] = $_POST['_dgx_donate_donor_province'];
		} else {
			$post_data['PROVINCE'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_country'] ) ) {
			$post_data['COUNTRY'] = $_POST['_dgx_donate_donor_country'];
		} else {
			$post_data['COUNTRY'] = '';
		}
		if ( isset( $_POST['_dgx_donate_donor_zip'] ) ) {
			$post_data['ZIP'] = $_POST['_dgx_donate_donor_zip'];
		} else {
			$post_data['ZIP'] = '';
		}
		if ( isset( $_POST['_dgx_donate_increase_to_cover'] ) ) {
			$post_data['INCREASETOCOVER'] = $_POST['_dgx_donate_increase_to_cover'];
		} else {
			$post_data['INCREASETOCOVER'] = '';
		}
		if ( isset( $_POST['_dgx_donate_anonymous'] ) ) {
			$post_data['ANONYMOUS'] = $_POST['_dgx_donate_anonymous'];
		} else {
			$post_data['ANONYMOUS'] = '';
		}
		if ( isset( $_POST['_dgx_donate_employer_match'] ) ) {
			$post_data['EMPLOYERMATCH'] = $_POST['_dgx_donate_employer_match'];
		} else {
			$post_data['EMPLOYERMATCH'] = '';
		}
		if ( isset( $_POST['_dgx_donate_employer_name'] ) ) {
			$post_data['EMPLOYERNAME'] = $_POST['_dgx_donate_employer_name'];
		} else {
			$post_data['EMPLOYERNAME'] = '';
		}
		if ( isset( $_POST['_dgx_donate_occupation'] ) ) {
			$post_data['OCCUPATION'] = $_POST['_dgx_donate_occupation'];
		} else {
			$post_data['OCCUPATION'] = '';
		}
		if ( isset( $_POST['_dgx_donate_uk_gift_aid'] ) ) {
			$post_data['UKGIFTAID'] = $_POST['_dgx_donate_uk_gift_aid'];
		} else {
			$post_data['UKGIFTAID'] = '';
		}
		if ( isset( $_POST['nonce'] ) ) {
			$post_data['NONCE'] = $_POST['nonce'];
		} else {
			$post_data['NONCE'] = '';
		}

		// pull override data from hidden form (might be modified by users with callbacks)
		if ( isset( $_POST['business'] ) ) {
			$post_data['BUSINESS'] = $_POST['business'];
		} else {
			$post_data['BUSINESS'] = '';
		}
		if ( isset( $_POST['return'] ) ) {
			$post_data['RETURN'] = $_POST['return'];
		} else {
			$post_data['RETURN'] = '';
		}
		if ( isset( $_POST['notify_url'] ) ) {
			$post_data['NOTIFY_URL'] = $_POST['notify_url'];
		} else {
			$post_data['NOTIFY_URL'] = '';
		}
		if ( isset( $_POST['item_name'] ) ) {
			$post_data['ITEM_NAME'] = $_POST['item_name'];
		} else {
			$post_data['ITEM_NAME'] = '';
		}
		if ( isset( $_POST['cmd'] ) ) {
			$post_data['CMD'] = $_POST['cmd'];
		} else {
			$post_data['CMD'] = '';
		}
		if ( isset( $_POST['p3'] ) ) {
			$post_data['P3'] = $_POST['p3'];
		} else {
			$post_data['P3'] = '';
		}
		if ( isset( $_POST['t3'] ) ) {
			$post_data['T3'] = $_POST['t3'];
		} else {
			$post_data['T3'] = '';
		}
		if ( isset( $_POST['a3'] ) ) {
			;
			$post_data['A3'] = $_POST['a3'];
		} else {
			$post_data['A3'] = '';
		}

		// Resolve the donation amount
		if ( strcasecmp( $_POST['_dgx_donate_amount'], "OTHER" ) == 0 ) {
			$post_data['AMOUNT'] = floatval( $_POST['_dgx_donate_user_amount'] );
		} else {
			$post_data['AMOUNT'] = floatval( $_POST['_dgx_donate_amount'] );
		}
		if ( $post_data['AMOUNT'] < 1.00 ) {
			$post_data['AMOUNT'] = 1.00;
		}

		if ( 'US' == $post_data['HONOREECOUNTRY'] ) {
			$post_data['PROVINCE'] = '';
		} else if ( 'CA' == $post_data['HONOREECOUNTRY'] ) {
			$post_data['HONOREESTATE'] = '';
		} else {
			$post_data['HONOREESTATE']    = '';
			$post_data['HONOREEPROVINCE'] = '';
		}

		// If no country entered, pull in the default
		if ( $post_data['COUNTRY'] == '' ) {
			$post_data['COUNTRY'] = get_option( 'dgx_donate_default_country' );
		}

		if ( 'US' == $post_data['COUNTRY'] ) {
			$post_data['PROVINCE'] = '';
		} else if ( 'CA' == $post_data['COUNTRY'] ) {
			$post_data['STATE'] = '';
		} else {
			$post_data['STATE']    = '';
			$post_data['PROVINCE'] = '';
		}

		$post_data['PAYMENTMETHOD'] = "PayPal"; // $_POST['dgx_donate_payment_method']
		$post_data['SDVERSION']     = dgx_donate_get_version();

		// Sanitize the data (remove leading, trailing spaces quotes, brackets)
		foreach ( $post_data as $key => $value ) {
			$temp              = trim( $value );
			$temp              = str_replace( "\"", "", $temp );
			$temp              = strip_tags( $temp );
			$post_data[ $key ] = $temp;
		}
		// account for different permalink styles
		$success_url = $post_data['SUCCESSURL'];
		$qmark       = strpos( $success_url, '?' );
		if ( $qmark === false ) {
			$success_url .= "?thanks=true";
		} else {
			$success_url .= "&thanks=true";
		}
		$post_data['RETURN'] = $success_url;

		dgx_donate_debug_log( "Success URL: $success_url" );

		$post_data = apply_filters(
			'seamless_donations_payment_post_data', $post_data );

		// insert extra validation for GoodByeCaptcha and any other validation
		$challenge_response_passed = apply_filters( 'seamless_donations_challenge_response_request', true, $post_data );

		if ( true !== $challenge_response_passed ) // for sure there is an error
		{
			if ( is_wp_error( $challenge_response_passed ) ) {
				$error_message = $challenge_response_passed->get_error_message();
			} else {
				$error_message = (string) $challenge_response_passed;
			}
			dgx_donate_debug_log( 'Form challenge-response failed:' . $error_message );
			die( esc_html__( 'Invalid response to challenge. Are you human?' ) );
		}

		if ( $sd4_mode == false ) {
			// Save it all in a transient
			$transient_token = $post_data['SESSIONID'];
			set_transient( $transient_token, $post_data, 7 * 24 * 60 * 60 ); // 7 days
			dgx_donate_debug_log( 'Saving transaction data using legacy mode' );
		} else {
			seamless_donations_update_audit_option( $session_id, $post_data );
			dgx_donate_debug_log( 'Saving transaction data using guid/audit db mode' );
		}

		// more log data
		$obscurify = get_option( 'dgx_donate_log_obscure_name' ); // false if not set
		if ( $obscurify == '1' ) {
			// obscurify for privacy
			$donor_name = strtolower( $post_data['FIRSTNAME'] . $post_data['LASTNAME'] );
			$donor_name = seamless_donations_obscurify_string( $donor_name, '*', false );
		} else {
			$donor_name = $post_data['FIRSTNAME'] . ' ' . $post_data['LASTNAME'];
		}
		dgx_donate_debug_log( 'Name: ' . $donor_name );
		dgx_donate_debug_log( 'Amount: ' . $post_data['AMOUNT'] );

		dgx_donate_debug_log( "Preparation complete. Entering PHP post code." );

		// new posting code
		// Build the PayPal query string
		$post_args = "?";

		$post_args .= "first_name=" . urlencode( $post_data['FIRSTNAME'] ) . "&";
		$post_args .= "last_name=" . urlencode( $post_data['LASTNAME'] ) . "&";
		$post_args .= "address1=" . urlencode( $post_data['ADDRESS'] ) . "&";
		$post_args .= "address2=" . urlencode( $post_data['ADDRESS2'] ) . "&";
		$post_args .= "city=" . urlencode( $post_data['CITY'] ) . "&";
		$post_args .= "zip=" . urlencode( $post_data['ZIP'] ) . "&";

		if ( 'US' == $post_data['COUNTRY'] ) {
			$post_args .= "state=" . urlencode( $post_data['STATE'] ) . "&";
		} else {
			if ( 'CA' == $post_data['COUNTRY'] ) {
				$post_args .= "state=" . urlencode( $post_data['PROVINCE'] ) . "&";
			}
		}

		$post_args .= "country=" . urlencode( $post_data['COUNTRY'] ) . "&";
		$post_args .= "email=" . urlencode( $post_data['EMAIL'] ) . "&";
		$post_args .= "custom=" . urlencode( $post_data['SESSIONID'] ) . "&";

		// fill in repeating data, overriding if necessary
		dgx_donate_debug_log( "Checking for repeat. REPEAT value is [" . $post_data['REPEATING'] . "]." );
		if ( $post_data['REPEATING'] == '' ) {
			if ( $post_data['CMD'] == '' ) {
				$post_data['CMD'] = '_donations';
			}
			$post_args .= "amount=" . urlencode( $post_data['AMOUNT'] ) . "&";
			$post_args .= "cmd=" . urlencode( $post_data['CMD'] ) . "&";
		} else {
			if ( $post_data['CMD'] == '' ) {
				$post_data['CMD'] = '_xclick-subscriptions';
			}
			if ( $post_data['P3'] == '' ) {
				$post_data['P3'] = '1';
			}
			if ( $post_data['T3'] == '' ) {
				$post_data['T3'] = 'M';
			}
			$post_args .= "cmd=" . urlencode( $post_data['CMD'] ) . "&";
			$post_args .= "p3=" . urlencode( $post_data['P3'] ) . "&";  // 1, M = monthly
			$post_args .= "t3=" . urlencode( $post_data['T3'] ) . "&";
			$post_args .= "src=1&sra=1&"; // repeat until cancelled, retry on failure
			$post_args .= "a3=" . urlencode( $post_data['AMOUNT'] ) . "&";
			$log_msg = "Enabling repeating donation, cmd=" . $post_data['CMD'];
			$log_msg .= ", p3=" . $post_data['P3'] . ", t3=" . $post_data['T3'];
			$log_msg .= ", a3=" . $post_data['AMOUNT'];
			dgx_donate_debug_log( $log_msg );
		}

		$notify_url = plugins_url( '/dgx-donate-paypalstd-ipn.php', __FILE__ );

		$paypal_email  = get_option( 'dgx_donate_paypal_email' );
		$currency_code = get_option( 'dgx_donate_currency' );

		// fill in the rest of the form data, overriding if necessary
		if ( $post_data['BUSINESS'] == '' ) {
			$post_data['BUSINESS'] = $paypal_email;
		}
		if ( $post_data['NOTIFY_URL'] == '' ) {
			$post_data['NOTIFY_URL'] = $notify_url;
		}
		dgx_donate_debug_log( "Computed RETURN value: '" . $post_data['RETURN'] . "'" );

		$post_args .= "business=" . urlencode( $post_data['BUSINESS'] ) . "&";
		$post_args .= "return=" . urlencode( $post_data['RETURN'] ) . "&";
		$post_args .= "notify_url=" . urlencode( $post_data['NOTIFY_URL'] ) . "&";
		$post_args .= "item_name=" . urlencode( $post_data['ITEM_NAME'] ) . "&";
		$post_args .= "quantity=" . urlencode( '1' ) . "&";
		$post_args .= "currency_code=" . urlencode( $currency_code ) . "&";
		$post_args .= "no_note=" . urlencode( '1' ) . "&";

		if ( $paypal_server == "SANDBOX" ) {
			$form_action = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$form_action = "https://www.paypal.com/cgi-bin/webscr";
		}

		//	var_dump ( $post_args );
		//
		//	die();

		// dgx_donate_debug_log ( "Post args: " . $post_args );

		dgx_donate_debug_log( "Redirecting to PayPal... now!" );

		wp_redirect( $form_action . $post_args );
		exit;
	}
}

