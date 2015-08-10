<?php
/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */


/******************************************************************************************************/
function dgx_donate_echo_admin_footer () {

	$pluginVersion = dgx_donate_get_version ();

	echo "<p class=\"dgxdonateadminfooter\">Seamless Donations $pluginVersion</p>";
}

add_action ( 'dgx_donate_admin_footer', 'dgx_donate_echo_admin_footer' );

/******************************************************************************************************/
function dgx_donate_init_defaults () {

	// Thank you email option defaults

	// validate name - replace with santized blog name if needed
	$from_name = get_option ( 'dgx_donate_email_name' );
	if( empty( $from_name ) ) {
		$from_name = get_bloginfo ( 'name' );
		$from_name = preg_replace ( "/[^a-zA-Z ]+/", "", $from_name ); // letters and spaces only please
		update_option ( 'dgx_donate_email_name', $from_name );
	}

	// validate email - replace with admin email if needed
	$from_email = get_option ( 'dgx_donate_email_reply' );
	if( empty( $from_email ) || ! is_email ( $from_email ) ) {
		$from_email = get_option ( 'admin_email' );
		update_option ( 'dgx_donate_email_reply', $from_email );
	}

	$thankSubj = get_option ( 'dgx_donate_email_subj' );
	if( empty( $thankSubj ) ) {
		$thankSubj = "Thank you for your donation";
		update_option ( 'dgx_donate_email_subj', $thankSubj );
	}

	$bodyText = get_option ( 'dgx_donate_email_body' );
	if( empty( $bodyText ) ) {
		$bodyText = "Dear [firstname] [lastname],\n\n";
		$bodyText .= "Thank you for your generous donation of [amount]. Please note that no goods ";
		$bodyText .= "or services were received in exchange for this donation.";
		update_option ( 'dgx_donate_email_body', $bodyText );
	}

	$recurring_text = get_option ( 'dgx_donate_email_recur' );
	if( empty( $recurring_text ) ) {
		$recurring_text = __ (
			"Thank you for electing to have your donation automatically repeated each month.", 'seamless-donations' );
		update_option ( 'dgx_donate_email_recur', $recurring_text );
	}

	$designatedText = get_option ( 'dgx_donate_email_desig' );
	if( empty( $designatedText ) ) {
		$designatedText = "Your donation has been designated to the [fund] fund.";
		update_option ( 'dgx_donate_email_desig', $designatedText );
	}

	$anonymousText = get_option ( 'dgx_donate_email_anon' );
	if( empty( $anonymousText ) ) {
		$anonymousText
			= "You have requested that your donation be kept anonymous.  Your name will not be revealed to the public.";
		update_option ( 'dgx_donate_email_anon', $anonymousText );
	}

	$mailingListJoinText = get_option ( 'dgx_donate_email_list' );
	if( empty( $mailingListJoinText ) ) {
		$mailingListJoinText
			= "Thank you for joining our mailing list.  We will send you updates from time-to-time.  If ";
		$mailingListJoinText .= "at any time you would like to stop receiving emails, please send us an email to be ";
		$mailingListJoinText .= "removed from the mailing list.";
		update_option ( 'dgx_donate_email_list', $mailingListJoinText );
	}

	$tributeText = get_option ( 'dgx_donate_email_trib' );
	if( empty( $tributeText ) ) {
		$tributeText
			= "You have asked to make this donation in honor of or memory of someone else.  Thank you!  We will notify the ";
		$tributeText .= "honoree within the next 5-10 business days.";
		update_option ( 'dgx_donate_email_trib', $tributeText );
	}

	$employer_text = get_option ( 'dgx_donate_email_empl' );
	if( empty( $employer_text ) ) {
		$employer_text = "You have specified that your employer matches some or all of your donation. ";
		update_option ( 'dgx_donate_email_empl', $employer_text );
	}

	$closingText = get_option ( 'dgx_donate_email_close' );
	if( empty( $closingText ) ) {
		$closingText = "Thanks again for your support!";
		update_option ( 'dgx_donate_email_close', $closingText );
	}

	$signature = get_option ( 'dgx_donate_email_sig' );
	if( empty( $signature ) ) {
		$signature = "Director of Donor Relations";
		update_option ( 'dgx_donate_email_sig', $signature );
	}

	//// PayPal defaults
	$notifyEmails = get_option ( 'dgx_donate_notify_emails' );
	if( empty( $notifyEmails ) ) {
		$notifyEmails = get_option ( 'admin_email' );
		update_option ( 'dgx_donate_notify_emails', $notifyEmails );
	}

	$paymentGateway = get_option ( 'dgx_donate_payment_gateway' );
	if( empty( $paymentGateway ) ) {
		update_option ( 'dgx_donate_payment_gateway', DGXDONATEPAYPALSTD );
	}

	$payPalServer = get_option ( 'dgx_donate_paypal_server' );
	if( empty( $payPalServer ) ) {
		update_option ( 'dgx_donate_paypal_server', 'LIVE' );
	}

	$paypal_email = get_option ( 'dgx_donate_paypal_email' );
	if( ! is_email ( $paypal_email ) ) {
		update_option ( 'dgx_donate_paypal_email', '' );
	}

	// Thank you page default
	$thankYouText = get_option ( 'dgx_donate_thanks_text' );
	if( empty( $thankYouText ) ) {
		$message = "Thank you for donating!  A thank you email with the details of your donation ";
		$message .= "will be sent to the email address you provided.";
		update_option ( 'dgx_donate_thanks_text', $message );
	}

	// Giving levels default
	$givingLevels = dgx_donate_get_giving_levels ();
	$noneChecked  = true;
	foreach( $givingLevels as $givingLevel ) {
		$levelEnabled = dgx_donate_is_giving_level_enabled ( $givingLevel );
		if( $levelEnabled ) {
			$noneChecked = false;
		}
	}
	if( $noneChecked ) {
		// Select 1000, 500, 100, 50 by default
		dgx_donate_enable_giving_level ( 1000 );
		dgx_donate_enable_giving_level ( 500 );
		dgx_donate_enable_giving_level ( 100 );
		dgx_donate_enable_giving_level ( 50 );
	}

	// Currency
	$currency = get_option ( 'dgx_donate_currency' );
	if( empty( $currency ) ) {
		update_option ( 'dgx_donate_currency', 'USD' );
	}

	// Country default
	$default_country = get_option ( 'dgx_donate_default_country' );
	if( empty( $default_country ) ) {
		update_option ( 'dgx_donate_default_country', 'US' );
	}

	// State default
	$default_state = get_option ( 'dgx_donate_default_state' );
	if( empty( $default_state ) ) {
		update_option ( 'dgx_donate_default_state', 'WA' );
	}

	// Province default
	$default_province = get_option ( 'dgx_donate_default_province' );
	if( empty( $default_province ) ) {
		update_option ( 'dgx_donate_default_province', 'AB' );
	}

	// Show Employer match section default
	$show_employer_section = get_option ( 'dgx_donate_show_employer_section' );
	if( empty( $show_employer_section ) ) {
		update_option ( 'dgx_donate_show_employer_section', 'true' );
	}

	// Show Tribute Gift section default
	$show_tribute_section = get_option ( 'dgx_donate_show_tribute_section' );
	if( empty( $show_tribute_section ) ) {
		update_option ( 'dgx_donate_show_tribute_section', 'true' );
	}

	// Scripts location default
	$scripts_in_footer = get_option ( 'dgx_donate_scripts_in_footer' );
	if( empty( $scripts_in_footer ) ) {
		update_option ( 'dgx_donate_scripts_in_footer', 'false' );
	}
}

/******************************************************************************************************/
function get_donations_by_meta ( $meta_key, $meta_value, $count ) {

	$sd4_mode = get_option ( 'dgx_donate_start_in_sd4_mode' );
	if( $sd4_mode == false ) {
		$post_type = 'dgx-donation';
	} else {
		$post_type = 'donation';
	}
	$donation_ids = array();

	if( ! empty( $meta_value ) ) {
		$args = array(
			'numberposts' => $count,
			'post_type'   => $post_type,
			'meta_key'    => $meta_key,
			'meta_value'  => $meta_value,
			'orderby'     => 'post_date',
			'order'       => 'DESC'
		);

		$my_donations = get_posts ( $args );

		foreach( $my_donations as $donation ) {
			$donation_ids[] = $donation->ID;
		}
	}

	return $donation_ids;
}

function dgx_donate_sanitize_date ( $mdy_string, $default_month, $default_date, $default_year ) {

	$month = $default_month;
	$date  = $default_date;
	$year  = $default_year;

	if( ! empty( $mdy_string ) ) {
		// Split on m/d/y
		$date_array = explode ( "/", $mdy_string );
		$month      = $date_array[0];
		$date       = $date_array[1];
		$year       = $date_array[2];
	}

	if( $month < 1 ) {
		$month = 1;
	}

	if( $month > 12 ) {
		$month = 12;
	}

	if( $date < 1 ) {
		$date = 1;
	}

	if( $date > 31 ) {
		$date = 31;
	}

	if( $year < 100 ) {
		$year = 2000 + $year;
	}

	return $month . '/' . $date . '/' . $year;
}

/******************************************************************************************************/
function dgx_donate_save_giving_levels_settings () {

	$noneEnabled = true;

	$givingLevels = dgx_donate_get_giving_levels ();
	foreach( $givingLevels as $givingLevel ) {
		$key = dgx_donate_get_giving_level_key ( $givingLevel );
		if( isset( $_POST[ $key ] ) ) {
			dgx_donate_enable_giving_level ( $givingLevel );
			$noneEnabled = false;
		} else {
			dgx_donate_disable_giving_level ( $givingLevel );
		}
	}

	// If they are all disabled, at least enable the first one
	if( $noneEnabled ) {
		dgx_donate_enable_giving_level ( $givingLevels[0] );
	}
}
