<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

function seamless_donations_create_donation_from_transaction_audit_table ( $transaction_form_data ) {

	// Create a new donation record
	$donation_id = dgx_donate_create_empty_donation_record ();

	$meta_map = dgx_donate_get_meta_map ();

	foreach( (array) $meta_map as $transaction_form_data_key => $postmeta_key ) {
		if( $transaction_form_data[ $transaction_form_data_key ] != '' ) {
			// using switch so new special cases are easier to add
			switch( $postmeta_key ) {
				case '_dgx_donate_designated_fund':
					// save fund data from transaction into the donation record
					if( $transaction_form_data[ $transaction_form_data_key ] != 0 ) {
						$fund_id = $transaction_form_data[ $transaction_form_data_key ];

						// lookup the fund name from the id and save into the post meta data
						$fund_name = get_the_title ( $fund_id );
						update_post_meta ( $donation_id, '_dgx_donate_designated_fund', $fund_name );

						// update the donation record with the fund id -- also link the funds to the donations
						update_post_meta ( $donation_id, '_dgx_donate_designated_fund_id', $fund_id );

						// update the donations list to point to this donation id
						seamless_donations_add_donation_id_to_fund ( $fund_id, $donation_id );

						// update the donation total for the fund
						seamless_donations_add_donation_amount_to_fund_total ( $donation_id, $fund_id );
					}
					break;
				default:
					update_post_meta ( $donation_id, $postmeta_key, $transaction_form_data[ $transaction_form_data_key ] );
			}
		}
	}

	$donor_id = seamless_donations_update_donor_data ( $donation_id );

	$email = get_post_meta ( $donation_id, '_dgx_donate_donor_email', true );
	if( $email !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_email', $email );
	}
	$employer = get_post_meta ( $donation_id, '_dgx_donate_employer_name', true );
	if( $employer !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_employer_name', $employer );
	}
	$occupation = get_post_meta ( $donation_id, '_dgx_donate_occupation', true );
	if( $occupation !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_occupation', $occupation );
	}
	$phone = get_post_meta ( $donation_id, '_dgx_donate_donor_phone', true );
	if( $phone !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_phone', $phone );
	}
	$address = get_post_meta ( $donation_id, '_dgx_donate_donor_address', true );
	if( $address !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_address', $address );
	}
	$address2 = get_post_meta ( $donation_id, '_dgx_donate_donor_address2', true );
	if( $address2 !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_address2', $address2 );
	}
	$city = get_post_meta ( $donation_id, '_dgx_donate_donor_city', true );
	if( $city !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_city', $city );
	}
	$state = get_post_meta ( $donation_id, '_dgx_donate_donor_state', true );
	if( $state !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_state', $state );
	}
	$province = get_post_meta ( $donation_id, '_dgx_donate_donor_province', true );
	if( $province !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_province', $province );
	}
	$country = get_post_meta ( $donation_id, '_dgx_donate_donor_country', true );
	if( $country !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_country', $country );
	}
	$zip = get_post_meta ( $donation_id, '_dgx_donate_donor_zip', true );
	if( $zip !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_zip', $zip );
	}
	$anon = get_post_meta ( $donation_id, '_dgx_donate_anonymous', true );
	if( $anon == 'on' ) {
		update_post_meta ( $donor_id, '_dgx_donate_anonymous', 'yes' );
	}

	return $donation_id;
}


function seamless_donations_add_donation_id_to_fund ( $fund_id, $donation_id ) {
	// update the donations list to point to this donation id
	$donations_list = get_post_meta ( $fund_id, '_dgx_donate_donor_donations', true );
	if( $donations_list != '' ) {
		$donations_list .= ',' . $donation_id;
	} else {
		// this is the first donation for this donor
		$donations_list = $donation_id;
	}
	update_post_meta ( $fund_id, '_dgx_donate_donor_donations', $donations_list );
}

function seamless_donations_add_donation_amount_to_fund_total ( $donation_id, $fund_id ) {
	// update the donation total for the fund
	$donation_amount = get_post_meta ( $donation_id, '_dgx_donate_amount', true );
	$fund_total      = get_post_meta ( $fund_id, '_dgx_donate_fund_total', true );

	if( $donation_amount != '' ) {
		if( $fund_total == '' ) {
			$fund_total = 0.0;
		} else {
			$fund_total = floatval ( $fund_total );
		}
		$donation_amount = floatval ( $donation_amount );
		$fund_total += $donation_amount;
		$fund_total = strval ( $fund_total );
		update_post_meta ( $fund_id, '_dgx_donate_fund_total', $fund_total );
	}
}

function seamless_donations_create_donation_from_donation ( $old_donation_id ) {

	// Create a new donation record by cloning an old one (useful for repeating donations)
	dgx_donate_debug_log ( "About to create donation from old donation $old_donation_id" );
	$new_donation_id = dgx_donate_create_empty_donation_record ();
	dgx_donate_debug_log ( "New donation id = $new_donation_id" );

	$meta_map = dgx_donate_get_meta_map ();

	foreach( (array) $meta_map as $transient_data_key => $postmeta_key ) {
		$old_donation_meta_value = get_post_meta ( $old_donation_id, $postmeta_key, true );
		update_post_meta ( $new_donation_id, $postmeta_key, $old_donation_meta_value );
	}

	seamless_donations_update_donor_data ( $old_donation_id, $new_donation_id );

	dgx_donate_debug_log ( "done with dgx_donate_create_donation_from_donation, returning new id $new_donation_id" );

	return $new_donation_id;
}

function seamless_donations_create_donation_from_paypal_data () {

	// PROBABLY DEPRECATED

	// Create a new donation record from paypal data (if transient no longer exists for some reason)
	// with the addition of the transaction audit table in 4.0.5, this will probably not ever be called

	dgx_donate_debug_log ( "About to create donation from paypal post data" );
	$new_donation_id = dgx_donate_create_empty_donation_record ();
	dgx_donate_debug_log ( "New donation id = $new_donation_id" );

	// @todo - loop over the meta map translating paypal keys into our keys
	// @todo ADDRESS

	$payment_gross = isset( $_POST['payment_gross'] ) ? $_POST['payment_gross'] : '';
	$mc_gross      = isset( $_POST['mc_gross'] ) ? $_POST['mc_gross'] : '';

	$amount = empty( $payment_gross ) ? $mc_gross : $payment_gross;

	update_post_meta ( $new_donation_id, '_dgx_donate_donor_first_name', $_POST['first_name'] );
	update_post_meta ( $new_donation_id, '_dgx_donate_donor_last_name', $_POST['last_name'] );
	update_post_meta ( $new_donation_id, '_dgx_donate_donor_email', $_POST['payer_email'] );
	update_post_meta ( $new_donation_id, '_dgx_donate_amount', $amount );

	$donor_id = seamless_donations_update_donor_data ( $new_donation_id );

	// update the donor detail options
	$email = get_post_meta ( $new_donation_id, '_dgx_donate_donor_email', true );
	if( $email !== false ) {
		update_post_meta ( $donor_id, '_dgx_donate_donor_email', $email );
	}

	dgx_donate_debug_log ( "Done with dgx_donate_create_donation_from_paypal_data, returning new id $new_donation_id" );

	return $new_donation_id;
}

function seamless_donations_update_donor_data ( $old_donation_id, $new_donation_id = '' ) {

	// this function creates the donor record. It takes the donation id as a parameter
	// it supports a second donation id for those cases when the donation is updated from a previous donation record,
	// as in the case of a repeating donation

	if( $new_donation_id == '' ) {
		$new_donation_id = $old_donation_id;
	}

	// Now build in the donor data
	$first = get_post_meta ( $old_donation_id, '_dgx_donate_donor_first_name', true );
	$last  = get_post_meta ( $old_donation_id, '_dgx_donate_donor_last_name', true );

	// now move that data into a donor post type
	$donor_name = sanitize_text_field ( $first . ' ' . $last );
	$donor_slug = sanitize_title ( $donor_name );

	$donor = get_page_by_path ( $donor_slug, OBJECT, 'donor' );

	if( $donor == NULL ) {
		// create the new custom donor post
		$donor_array = array(
			'post_title'   => $donor_name,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'donor',
		);
		$donor_id    = wp_insert_post ( $donor_array, true );
	} else {
		$donor_id = $donor->ID;
	}

	// record the donor id in the donation record
	update_post_meta ( $new_donation_id, '_dgx_donate_donor_id', $donor_id );

	// update the donations to point to this donation id
	seamless_donations_add_donation_id_to_donor ( $new_donation_id, $donor_id );

	// update the donation total for the donor
	seamless_donations_add_donation_amount_to_donor_total ( $new_donation_id, $donor_id );

	// update basic donor info
	update_post_meta ( $donor_id, '_dgx_donate_donor_first_name', $first );
	update_post_meta ( $donor_id, '_dgx_donate_donor_last_name', $last );

	return $donor_id;
}

function seamless_donations_add_donation_id_to_donor ( $donation_id, $donor_id ) {

	$donations_list = get_post_meta ( $donor_id, '_dgx_donate_donor_donations', true );
	if( $donations_list != '' ) {
		$donations_list .= ',' . $donation_id;
	} else {
		// this is the first donation for this donor
		$donations_list = $donation_id;
	}
	update_post_meta ( $donor_id, '_dgx_donate_donor_donations', $donations_list );
}

function seamless_donations_add_donation_amount_to_donor_total ( $donation_id, $donor_id ) {
	// update the donation total for the donor
	$donation_amount = get_post_meta ( $donation_id, '_dgx_donate_amount', true );
	$donor_total      = get_post_meta ( $donor_id, '_dgx_donate_donor_total', true );

	if( $donation_amount != '' ) {
		if( $donor_total == '' ) {
			$donor_total = 0.0;
		} else {
			$donor_total = floatval ( $donor_total );
		}
		$donation_amount = floatval ( $donation_amount );
		$donor_total += $donation_amount;
		$donor_total = strval ( $donor_total );
		update_post_meta ( $donor_id, '_dgx_donate_donor_total', $donor_total );
	}
}

function seamless_donations_get_donation_detail_link ( $donationID ) {

	$detailUrl = get_admin_url ();
	$detailUrl .= "post.php?post=$donationID&action=edit&post_type=donation";

	return $detailUrl;
}

