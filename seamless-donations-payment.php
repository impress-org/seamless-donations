<?php

/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

// Log
dgx_donate_debug_log ( '----------------------------------------' );
dgx_donate_debug_log ( 'DONATION TRANSACTION STARTED' );
$php_version = phpversion ();
dgx_donate_debug_log ( "PHP Version: $php_version" );
dgx_donate_debug_log ( "Seamless Donations Version: " . dgx_donate_get_version () );
dgx_donate_debug_log ( "User browser: " . seamless_donations_get_browser_name () );
dgx_donate_debug_log ( 'IPN: ' . plugins_url ( '/dgx-donate-paypalstd-ipn.php', __FILE__ ) );

$nonce = $_POST['nonce'];
if( ! wp_verify_nonce ( $nonce, 'dgx-donate-nonce' ) ) {
	dgx_donate_debug_log ( 'Payment process nonce validation failure.' );
	die( 'Busted!' );
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