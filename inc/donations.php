<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

function seamless_donations_create_donation_from_transient_data ( $transient_data ) {

	// Create a new donation record
	$donation_id = dgx_donate_create_empty_donation_record ();

	$meta_map = dgx_donate_get_meta_map ();

	foreach( (array) $meta_map as $transient_data_key => $postmeta_key ) {
		if( $transient_data[ $transient_data_key ] != '' ) {
			// using switch so new special cases are easier to add
			switch( $postmeta_key ) {
				case '_dgx_donate_designated_fund':
					if( $transient_data[ $transient_data_key ] != 0 ) {
						// lookup the fund name from the id and save into the post meta data
						$fund_name = get_the_title($transient_data[ $transient_data_key ]);
						update_post_meta ( $donation_id, $postmeta_key, $fund_name );
					}
					break;
				default:
					update_post_meta ( $donation_id, $postmeta_key, $transient_data[ $transient_data_key ] );
			}
		}
	}

	// Now build in the donor data
	$first = get_post_meta ( $donation_id, '_dgx_donate_donor_first_name', true );
	$last  = get_post_meta ( $donation_id, '_dgx_donate_donor_last_name', true );

	// now move that data into a donor post type
	$donor_name = sanitize_text_field ( $first . ' ' . $last );
	$donor_slug = sanitize_title ( $donor_name );

	$post = get_page_by_path ( $donor_slug, OBJECT, 'donor' );

	if( $post == NULL ) {
		// create the new custom donor post
		$post_array = array(
			'post_title'   => $donor_name,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'donor',
		);
		$post_id    = wp_insert_post ( $post_array, true );
	} else {
		$post_id = $post->ID;
	}

	// record the donor id in the donation record
	update_post_meta($donation_id, '_dgx_donate_donor_id', $post_id);

	// update the donor detail options
	update_post_meta ( $post_id, '_dgx_donate_donor_first_name', $first );
	update_post_meta ( $post_id, '_dgx_donate_donor_last_name', $last );

	$email = get_post_meta ( $donation_id, '_dgx_donate_donor_email', true );
	if( $email !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_email', $email );
	}
	$employer = get_post_meta ( $donation_id, '_dgx_donate_employer_name', true );
	if( $employer !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_employer_name', $employer );
	}
	$occupation = get_post_meta ( $donation_id, '_dgx_donate_occupation', true );
	if( $occupation !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_occupation', $occupation );
	}
	$phone = get_post_meta ( $donation_id, '_dgx_donate_donor_phone', true );
	if( $phone !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_phone', $phone );
	}
	$address = get_post_meta ( $donation_id, '_dgx_donate_donor_address', true );
	if( $address !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_address', $address );
	}
	$address2 = get_post_meta ( $donation_id, '_dgx_donate_donor_address2', true );
	if( $address2 !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_address2', $address2 );
	}
	$city = get_post_meta ( $donation_id, '_dgx_donate_donor_city', true );
	if( $city !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_city', $city );
	}
	$state = get_post_meta ( $donation_id, '_dgx_donate_donor_state', true );
	if( $state !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_state', $state );
	}
	$province = get_post_meta ( $donation_id, '_dgx_donate_donor_province', true );
	if( $province !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_province', $province );
	}
	$country = get_post_meta ( $donation_id, '_dgx_donate_donor_country', true );
	if( $country !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_country', $country );
	}
	$zip = get_post_meta ( $donation_id, '_dgx_donate_donor_zip', true );
	if( $zip !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_zip', $zip );
	}

	// update the donations to point to this donor id
	$donations_list = get_post_meta ( $post_id, '_dgx_donate_donor_donations', true );
	if( $donations_list !== false ) {
		$donations_list .= ',' . $donation_id;
	} else {
		// this is the first donation for this donor
		$donations_list = $donation_id;
	}
	update_post_meta ( $post_id, '_dgx_donate_donor_donations', $donations_list );

	return $donation_id;
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

	// Now build in the donor data
	$first = get_post_meta ( $old_donation_id, '_dgx_donate_donor_first_name', true );
	$last  = get_post_meta ( $old_donation_id, '_dgx_donate_donor_last_name', true );

	// now move that data into a donor post type
	$donor_name = sanitize_text_field ( $first . ' ' . $last );
	$donor_slug = sanitize_title ( $donor_name );

	$post = get_page_by_path ( $donor_slug, OBJECT, 'donor' );

	if( $post == NULL ) {
		// create the new custom donor post
		$post_array = array(
			'post_title'   => $donor_name,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'donor',
		);
		$post_id    = wp_insert_post ( $post_array, true );
	} else {
		$post_id = $post->ID;
	}

	// record the donor id in the donation record
	update_post_meta($new_donation_id, '_dgx_donate_donor_id', $post_id);

	// update the donations to point to this donor id
	$donations_list = get_post_meta ( $post_id, '_dgx_donate_donor_donations', true );
	if( $donations_list !== false ) {
		$donations_list .= ',' . $old_donation_id;
	} else {
		// this is the first donation for this donor
		$donations_list = $old_donation_id;
	}
	update_post_meta ( $post_id, '_dgx_donate_donor_donations', $donations_list );

	dgx_donate_debug_log ( "done with dgx_donate_create_donation_from_donation, returning new id $new_donation" );

	return $new_donation_id;
}

function seamless_donations_create_donation_from_paypal_data ( $post_data ) {

	// Create a new donation record from paypal data (if transient no longer exists for some reason)
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

	// Now build in the donor data
	$first = get_post_meta ( $new_donation_id, '_dgx_donate_donor_first_name', true );
	$last  = get_post_meta ( $new_donation_id, '_dgx_donate_donor_last_name', true );

	// now move that data into a donor post type
	$donor_name = sanitize_text_field ( $first . ' ' . $last );
	$donor_slug = sanitize_title ( $donor_name );

	$post = get_page_by_path ( $donor_slug, OBJECT, 'donor' );

	if( $post == NULL ) {
		// create the new custom donor post
		$post_array = array(
			'post_title'   => $donor_name,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'donor',
		);
		$post_id    = wp_insert_post ( $post_array, true );
	} else {
		$post_id = $post->ID;
	}

	// record the donor id in the donation record
	update_post_meta($new_donation_id, '_dgx_donate_donor_id', $post_id);

	// update the donor detail options
	$email = get_post_meta ( $new_donation_id, '_dgx_donate_donor_email', true );
	if( $email !== false ) {
		update_post_meta ( $post_id, '_dgx_donate_donor_email', $email );
	}

	// update the donations to point to this donor id
	$donations_list = get_post_meta ( $post_id, '_dgx_donate_donor_donations', true );
	if( $donations_list !== false ) {
		$donations_list .= ',' . $new_donation_id;
	} else {
		// this is the first donation for this donor
		$donations_list = $new_donation_id;
	}
	update_post_meta ( $post_id, '_dgx_donate_donor_donations', $donations_list );

	dgx_donate_debug_log ( "Done with dgx_donate_create_donation_from_paypal_data, returning new id $new_donation_id" );

	return $new_donation_id;
}

function seamless_donations_get_donation_detail_link ( $donationID ) {

	$detailUrl = get_admin_url ();
	$detailUrl .= "post.php?post=$donationID&action=edit&post_type=donation";

	return $detailUrl;
}

