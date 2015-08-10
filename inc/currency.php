<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

function dgx_donate_get_currencies () {

	$currencies = array(
		'AUD' => array( 'name' => 'Australian Dollar', 'symbol' => '$' ),
		'BRL' => array( 'name' => 'Brazilian Real', 'symbol' => 'R$' ),
		'CAD' => array( 'name' => 'Canadian Dollar', 'symbol' => '$' ),
		'CZK' => array( 'name' => 'Czech Koruna', 'symbol' => 'Kc' ),
		'DKK' => array( 'name' => 'Danish Krone', 'symbol' => 'kr' ),
		'EUR' => array( 'name' => 'Euro', 'symbol' => '&euro;' ),
		'HKD' => array( 'name' => 'Hong Kong Dollar', 'symbol' => '$' ),
		'HUF' => array( 'name' => 'Hungarian Forint', 'symbol' => 'Ft' ),
		'ILS' => array( 'name' => 'Indian Rupee', 'symbol' => '&#8377;' ),
		'INR' => array( 'name' => 'Israeli New Sheqel', 'symbol' => '&#8362;' ),
		'JPY' => array( 'name' => 'Japanese Yen', 'symbol' => '&yen;' ),
		'MYR' => array( 'name' => 'Malaysian Ringgit', 'symbol' => 'RM' ),
		'MXN' => array( 'name' => 'Mexican Peso', 'symbol' => '$' ),
		'NOK' => array( 'name' => 'Norwegian Krone', 'symbol' => 'kr' ),
		'NZD' => array( 'name' => 'New Zealand Dollar', 'symbol' => '$' ),
		'PHP' => array( 'name' => 'Philippine Peso', 'symbol' => '&#8369;' ),
		'PLN' => array( 'name' => 'Polish Zloty', 'symbol' => '&#122;&#322;' ),
		'GBP' => array( 'name' => 'Pound Sterling', 'symbol' => '&pound;' ),
		'RUB' => array( 'name' => 'Russian Ruble', 'symbol' => '&#8381;' ),
		'SGD' => array( 'name' => 'Singapore Dollar', 'symbol' => '$' ),
		'SEK' => array( 'name' => 'Swedish Krona', 'symbol' => 'kr' ),
		'CHF' => array( 'name' => 'Swiss Franc', 'symbol' => 'CHF' ),
		'TWD' => array( 'name' => 'Taiwan New Dollar', 'symbol' => '$' ),
		'THB' => array( 'name' => 'Thai Baht', 'symbol' => '&#3647;' ),
		'TRY' => array( 'name' => 'Turkish Lira', 'symbol' => '&#8378;' ),
		'USD' => array( 'name' => 'U.S. Dollar', 'symbol' => '$' )
	);

	return $currencies;
}

// builds a simple array of currency_symbol => currency_name
function dgx_donate_get_currency_list () {

	$currencies    = dgx_donate_get_currencies ();
	$currency_list = array();
	foreach( $currencies as $currency_code => $currency_details ) {

		$currency_list[ $currency_code ] = $currency_details['name'];
	}

	return $currency_list;
}

/*
 * From https://developer.paypal.com/docs/classic/api/currency_codes/
 */
function dgx_donate_get_currency_selector ( $select_name, $select_initial_value ) {

	$output = "<select id='" . esc_attr ( $select_name ) . "' name='" . esc_attr ( $select_name ) . "'>";

	$currencies = dgx_donate_get_currencies ();

	foreach( $currencies as $currency_code => $currency_details ) {
		$selected = "";
		if( strcasecmp ( $select_initial_value, $currency_code ) == 0 ) {
			$selected = " selected ";
		}
		$output .= "<option value='" . esc_attr ( $currency_code ) . "'" . esc_attr ( $selected ) . ">" .
		           esc_html ( $currency_details['name'] ) . "</option>";
	}

	$output .= "</select>";

	return $output;
}

function dgx_donate_get_escaped_formatted_amount ( $amount, $decimal_places = 2, $currency_code = '' ) {

	if( empty( $currency_code ) ) {
		$currency_code = get_option ( 'dgx_donate_currency' );
	}

	$currencies      = dgx_donate_get_currencies ();
	$currency        = $currencies[ $currency_code ];
	$currency_symbol = $currency['symbol'];

	return $currency_symbol . esc_html ( number_format ( $amount, $decimal_places ) );
}

function dgx_donate_get_plain_formatted_amount (
	$amount, $decimal_places = 2, $currency_code = '', $append_currency_code = false ) {

	if( empty( $currency_code ) ) {
		$currency_code = get_option ( 'dgx_donate_currency' );
	}

	$formatted_amount = number_format ( $amount, $decimal_places );
	if( $append_currency_code ) {
		$formatted_amount .= " (" . $currency_code . ")";
	}

	return $formatted_amount;
}

function dgx_donate_get_donation_currency_code ( $donation_id ) {

	/* gets the currency code for the donation */
	/* updates donations without one (pre version 2.8.1) as USD */
	$currency_code = get_post_meta ( $donation_id, '_dgx_donate_donation_currency', true );
	if( empty( $currency_code ) ) {
		$currency_code = "USD";
		update_post_meta ( $donation_id, '_dgx_donate_donation_currency', $currency_code );
	}

	return $currency_code;
}