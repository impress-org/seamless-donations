<?php

/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

class SeamlessDonationsDonationPostType extends SeamlessDonationsAdminPageFramework_PostType {

	/**
	 * Automatically called with the 'wp_loaded' hook.
	 */
	public function setUp () {

		$donations_type_setup = array();
		// argument - http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
		$donations_setup
			= array(
			'labels'            => array(
				'name'                => __ ( 'Donations', 'seamless-donations' ),
				'singular_name'       => __ ( 'Donation', 'seamless-donations' ),
				'add_new_item'        => __ ( 'Donation', 'seamless-donations' ),
				'edit_item'           => __ ( 'Donation', 'seamless-donations' ),
				'new_item'            => __ ( 'Donation', 'seamless-donations' ),
				'view_item'           => __ ( 'Donation', 'seamless-donations' ),
				'search_items'        => __ ( 'Search donations', 'seamless-donations' ),
				'not_found'           => __ ( 'No donations found', 'seamless-donations' ),
				'not_found_in_trash'  => __ (
					'No donations found in Trash', 'seamless-donations' ),
				'restored_from_trash' => __ ( 'donation', 'seamless-donations' ),
			),
			'supports'          => array( 'title' ),
			'public'            => true,
			'show_table_filter' => false,
			'menu_icon'         => 'dashicons-palmtree',

		);

		$compact_menus = get_option ( 'dgx_donate_compact_menus' );
		if( $compact_menus == 1 ) {
			$donations_setup['show_ui']      = true;
			$donations_setup['show_in_menu'] = 'SeamlessDonationsAdmin';
			unset( $donations_setup['public'] );
			unset( $donations_setup['menu_icon'] );
		}

		$donations_setup      = apply_filters ( 'seamless_donations_donations_setup', $donations_setup );
		$donations_type_setup = apply_filters ( 'seamless_donations_donations_type_setup', $donations_type_setup );

		$this->setArguments ( $donations_setup );

		if( sizeof ( $donations_type_setup ) > 0 ) {
			$this->addTaxonomy (
				'donation_types',  // taxonomy slug
				$donations_type_setup );
		}
	}

	public function style_SeamlessDonationsDonationPostType ( $sStyle ) {

		// clean up donor list table
		$donation_header_style = ".add-new-h2 {display:none}" . PHP_EOL;   // delete Add New from top
		$donation_header_style .= ".bulkactions {display:none}" . PHP_EOL; // delete Bulk Actions section
		$donation_header_style .= ".edit {display:none}" . PHP_EOL;        // delete the quick actions under the link
		$donation_header_style .= ".trash {display:none}" . PHP_EOL;      // Shows Trash on the list of donations
		$donation_header_style .= ".inline {display:none}" . PHP_EOL;
		$donation_header_style .= ".view {display:none}" . PHP_EOL;

		// right-align donor amount information
		$donation_header_style .= ".column-amount {text-align:right; width:200px}" .
		                          PHP_EOL; // right-align dollar amounts
		$donation_header_style .= "th#amount.manage-column.column-amount {text-align:right}";
		$donation_header_style .= "th.manage-column.column-amount {text-align:right}";

		// clean up donor edit page
		$donation_header_style .= "#edit-slug-box {display:none}" . PHP_EOL;
		$donation_header_style .= "#delete-action {display:none}" . PHP_EOL; // shows trash in the donation item
		$donation_header_style .= ".page-title-action {display:none}" . PHP_EOL;
		$donation_header_style .= "a.edit-post-status {display:none}" . PHP_EOL;
		$donation_header_style .= "a.edit-visibility {display:none}" . PHP_EOL;
		$donation_header_style .= "a.edit-timestamp {display:none}" . PHP_EOL;
		$donation_header_style .= "#preview-action {display:none}" . PHP_EOL;
		$donation_header_style .= "label[for=slugdiv-hide] {display:none}" . PHP_EOL;
		$donation_header_style .= "#slugdiv {display:none}" . PHP_EOL;

		$donation_header_style = apply_filters ( 'seamless_donations_donation_header_style', $donation_header_style );

		return $sStyle . $donation_header_style;
	}

	public function script_SeamlessDonationsDonationPostType ( $sScript ) {

		// make sure the post title name can't be edited - set to readonly
		$script = 'var titleObj = document.getElementById("title");';
		$script .= 'if ((titleObj !== undefined) && (titleObj !== null)) {';
		$script .= 'titleObj.setAttribute("readonly", "true");}';

		return $sScript . $script;
	}

	public function columns_donation ( $aHeaderColumns ) {

		$donation_header_array = array(
			// 'cb'     => '<input type="checkbox" />', // Checkbox for bulk actions.
			'title'  => __ ( 'Time/Date', 'seamless-donations' ),
			'donor'  => __ ( 'Donor', 'seamless-donations' ),
			'fund'   => __ ( 'Fund', 'seamless-donations' ),
			'amount' => __ ( 'Amount', 'seamless-donations' ),
		);

		$donation_header_array = apply_filters ( 'seamless_donations_donation_header_array', $donation_header_array );

		return $donation_header_array;
	}

	public function cell_donation_donor ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		$anon = get_post_meta ( $iPostID, '_dgx_donate_anonymous', true );
		if( ! $anon ) {
			$anon = "off";
		}
		if( $anon == "on" ) {
			$anon_msg = "Anonymity Requested";

			return esc_attr ( $anon_msg );
		} else {

			$first = get_post_meta ( $iPostID, '_dgx_donate_donor_first_name', true );
			$last  = get_post_meta ( $iPostID, '_dgx_donate_donor_last_name', true );

			return esc_attr ( $first . ' ' . $last );
		}
	}

	public function cell_donation_fund ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		$fund = get_post_meta ( $iPostID, '_dgx_donate_designated_fund', true );

		if( ! $fund ) {
			$fund_msg = "No fund specified";

			return esc_attr ( $fund_msg );
		} else {
			return esc_attr ( $fund );
		}
	}

	public function cell_donation_amount ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		$amount   = get_post_meta ( $iPostID, '_dgx_donate_amount', true );
		$currency = get_post_meta ( $iPostID, '_dgx_donate_donation_currency', true );

		return esc_attr ( $amount . ' ' . $currency );
	}

	public function cell_donation_repeat ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		$repeat = get_post_meta ( $iPostID, '_dgx_donate_repeating', true );
		if( ! $repeat ) {
			$repeat = "off";
		}

		if( $repeat == "on" ) {
			$repeat_msg = "Yes";

			return esc_attr ( $repeat_msg );
		} else {
			$repeat_msg = "-";

			return esc_attr ( $repeat_msg );
		}
	}
}

class SeamlessDonationsDonationDonorInfoMetaBox extends SeamlessDonationsAdminPageFramework_MetaBox {

	/*
	 * Use the setUp() method to define settings of this meta box.
	 */
	public function setUp () {

		// get display meta data
		$donation_id = $_GET['post'];
		$donor_id    = get_post_meta ( $donation_id, '_dgx_donate_donor_id', true );
		$first       = get_post_meta ( $donor_id, '_dgx_donate_donor_first_name', true );
		$last        = get_post_meta ( $donor_id, '_dgx_donate_donor_last_name', true );
		$email       = get_post_meta ( $donor_id, '_dgx_donate_donor_email', true );
		$phone       = get_post_meta ( $donor_id, '_dgx_donate_donor_phone', true );
		$address     = get_post_meta ( $donor_id, '_dgx_donate_donor_address', true );
		$address2    = get_post_meta ( $donor_id, '_dgx_donate_donor_address2', true );
		$city        = get_post_meta ( $donor_id, '_dgx_donate_donor_city', true );
		$state       = get_post_meta ( $donor_id, '_dgx_donate_donor_state', true );
		$province    = get_post_meta ( $donor_id, '_dgx_donate_donor_province', true );
		$country     = get_post_meta ( $donor_id, '_dgx_donate_donor_country', true );
		$zip         = get_post_meta ( $donor_id, '_dgx_donate_donor_zip', true );

		if( empty( $country ) ) { /* older versions only did US */
			$country = 'US';
			update_post_meta ( $donation_id, '_dgx_donate_donor_country', 'US' );
		}

		// construct basic address info block
		$html = "";
		$html .= $first . ' ' . $last . '<br>';
		$html .= $address != '' ? $address . '<br>' : '';
		$html .= $address2 != '' ? $address2 . '<br>' : '';
		$html .= $city != '' ? $city . ', ' : '';

		if( 'US' == $country ) {
			$html .= $state != '' ? $state . ' ' : '';
		} else if( 'CA' == $country ) {
			$html .= $province != '' ? $province . ' ' : '';
		}

		if( dgx_donate_country_requires_postal_code ( $country ) ) {
			$html .= $zip != '' ? $zip . '<br>' : '';
		}

		$countries    = dgx_donate_get_countries ();
		$country_name = $countries[ $country ];
		$html .= $country_name != '' ? $country_name . '<br>' : '';
		$html .= '<br>';
		$html .= $phone != '' ? $phone . '<br>' : '';
		$html .= $email != '' ? $email : '';

		$this->addSettingFields (

			array(
				'field_id'     => 'donor_info',
				'type'         => 'donor_info',
				'before_field' => $html,
			)
		);
	}
}

class SeamlessDonationsDonationDetailMetaBox extends SeamlessDonationsAdminPageFramework_MetaBox {

	/*
	 * Use the setUp() method to define settings of this meta box.
	 */
	public function setUp () {

		// get donor data
		$post_id = $_GET['post'];;

		// now build the table
		$html = "";

		$html .= $this->show_donation_detail ( $post_id );

		$this->addSettingFields (

			array(
				'field_id'     => 'donor_info',
				'type'         => 'donor_info',
				'before_field' => $html,
			)
		);
	}

	public function show_donation_detail ( $donation_id ) {

		$html = "";

		$html .= "<table class='widefat'><tbody>\n";

		$year  = get_post_meta ( $donation_id, '_dgx_donate_year', true );
		$month = get_post_meta ( $donation_id, '_dgx_donate_month', true );
		$day   = get_post_meta ( $donation_id, '_dgx_donate_day', true );
		$time  = get_post_meta ( $donation_id, '_dgx_donate_time', true );

		$html .= "<tr>";
		$html .= "<th>" . esc_html__ ( 'Date', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $month . "/" . $day . "/" . $year . " " . $time ) . "</td></tr>\n";

		$amount           = get_post_meta ( $donation_id, '_dgx_donate_amount', true );
		$currency_code    = dgx_donate_get_donation_currency_code ( $donation_id );
		$formatted_amount = dgx_donate_get_escaped_formatted_amount ( $amount, 2, $currency_code );
		$html .= "<tr>";
		$html .= "<th>" . esc_html__ ( 'Amount', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . $formatted_amount . "</td></tr>\n";

		$add_to_mailing_list = get_post_meta ( $donation_id, '_dgx_donate_add_to_mailing_list', true );
		if( ! empty( $add_to_mailing_list ) ) {
			$add_to_mailing_list = __ ( 'Yes', 'seamless-donations' );
		} else {
			$add_to_mailing_list = __ ( 'No', 'seamless-donations' );
		}
		$html .= "<tr><th>" . esc_html__ ( 'Add to Mailing List?', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $add_to_mailing_list ) . "</td></tr>\n";

		$anonymous = get_post_meta ( $donation_id, '_dgx_donate_anonymous', true );
		if( empty( $anonymous ) ) {
			$anonymous = __ ( 'No', 'seamless-donations' );
		} else {
			$anonymous = __ ( 'Yes', 'seamless-donations' );
		}
		$html .= "<tr><th>" . esc_html__ ( 'Would like to remain anonymous?', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $anonymous ) . "</td></tr>\n";

		$fund_name  = __ ( 'Undesignated', 'seamless-donations' );
		$designated = get_post_meta ( $donation_id, '_dgx_donate_designated', true );
		if( ! empty( $designated ) ) {
			$fund_name = get_post_meta ( $donation_id, '_dgx_donate_designated_fund', true );
		}
		$html .= "<tr><th>" . esc_html__ ( 'Designated Fund', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $fund_name ) . "</td></tr>\n";

		$employer_match = get_post_meta ( $donation_id, '_dgx_donate_employer_match', true );
		if( empty( $employer_match ) ) {
			$employer_match_message = __ ( 'No', 'seamless-donations' );
		} else {
			$employer_match_message = __ ( 'Yes', 'seamless-donations' );
		}
		$html .= "<tr><th>" . esc_html__ ( 'Employer Match', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $employer_match_message ) . "</td></tr>\n";

		$employer_name = get_post_meta ( $donation_id, '_dgx_donate_employer_name', true );
		if( empty( $employer_name ) ) {
			$employer_name_message = '-';
		} else {
			$employer_name_message = $employer_name;
		}
		$html .= "<tr><th>" . esc_html__ ( 'Employer', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $employer_name_message ) . "</td></tr>\n";

		$occupation = get_post_meta ( $donation_id, '_dgx_donate_occupation', true );
		if( empty( $occupation ) ) {
			$occupation_message = '-';
		} else {
			$occupation_message = $occupation;
		}
		$html .= "<tr><th>" . esc_html__ ( 'Occupation', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $occupation_message ) . "</td></tr>\n";

		$donor_country = get_post_meta ( $donation_id, '_dgx_donate_donor_country', true );
		if( 'GB' == $donor_country ) {
			$uk_gift_aid = get_post_meta ( $donation_id, '_dgx_donate_uk_gift_aid', true );
			if( empty( $uk_gift_aid ) ) {
				$uk_gift_aid_message = __ ( 'No', 'seamless-donations' );
			} else {
				$uk_gift_aid_message = __ ( 'Yes', 'seamless-donations' );
			}
			$html .= "<tr><th>" . esc_html__ ( 'UK Gift Aid', 'seamless-donations' ) . "</th>";
			$html .= "<td>" . esc_html ( $uk_gift_aid_message ) . "</td></tr>\n";
		}

		$tribute_gift_message = __ ( 'No', 'seamless-donations' );
		$tribute_gift         = get_post_meta ( $donation_id, '_dgx_donate_tribute_gift', true );
		if( ! empty( $tribute_gift ) ) {
			$tribute_gift_message = __ ( 'Yes', 'seamless-donations' ) . " - ";

			$honoree_name       = get_post_meta ( $donation_id, '_dgx_donate_honoree_name', true );
			$honor_by_email     = get_post_meta ( $donation_id, '_dgx_donate_honor_by_email', true );
			$honoree_email_name = get_post_meta ( $donation_id, '_dgx_donate_honoree_email_name', true );
			$honoree_post_name  = get_post_meta ( $donation_id, '_dgx_donate_honoree_post_name', true );
			$honoree_email      = get_post_meta ( $donation_id, '_dgx_donate_honoree_email', true );
			$honoree_address    = get_post_meta ( $donation_id, '_dgx_donate_honoree_address', true );
			$honoree_city       = get_post_meta ( $donation_id, '_dgx_donate_honoree_city', true );
			$honoree_state      = get_post_meta ( $donation_id, '_dgx_donate_honoree_state', true );
			$honoree_province   = get_post_meta ( $donation_id, '_dgx_donate_honoree_province', true );
			$honoree_zip        = get_post_meta ( $donation_id, '_dgx_donate_honoree_zip', true );
			$honoree_country    = get_post_meta ( $donation_id, '_dgx_donate_honoree_country', true );
			$memorial_gift      = get_post_meta ( $donation_id, '_dgx_donate_memorial_gift', true );

			if( empty( $memorial_gift ) ) {
				$tribute_gift_message .= __ ( 'in honor of', 'seamless-donations' ) . ' ';
			} else {
				$tribute_gift_message .= __ ( 'in memory of', 'seamless-donations' ) . ' ';
			}

			$tribute_gift_message .= $honoree_name . "<br/><br/>";
			if( 'TRUE' == $honor_by_email ) {
				$tribute_gift_message .= __ ( 'Send acknowledgement via email to', 'seamless-donations' ) . '<br/>';
				$tribute_gift_message .= esc_html ( $honoree_email_name ) . "<br/>";
				$tribute_gift_message .= esc_html ( $honoree_email ) . "<br/>";
			} else {
				$tribute_gift_message .= __ ( 'Send acknowledgement via postal mail to', 'seamless-donations' ) .
				                         '<br/>';
				$tribute_gift_message .= esc_html ( $honoree_post_name ) . "<br/>";
				$tribute_gift_message .= esc_html ( $honoree_address ) . "<br/>";

				if( ! empty( $honoree_city ) ) {
					$tribute_gift_message .= esc_html ( $honoree_city . " " );
				}
				if( 'US' == $honoree_country ) {
					$tribute_gift_message .= esc_html ( $honoree_state . " " );
				} else if( 'CA' == $honoree_country ) {
					$tribute_gift_message .= esc_html ( $honoree_province . " " );
				}

				if( dgx_donate_country_requires_postal_code ( $honoree_country ) ) {
					$tribute_gift_message .= esc_html ( " " . $honoree_zip );
				}
				$tribute_gift_message .= "<br/>";

				$countries            = dgx_donate_get_countries ();
				$honoree_country_name = $countries[ $honoree_country ];
				$tribute_gift_message .= esc_html ( $honoree_country_name ) . "<br/><br/>";
			}
		}
		$html .= "<tr>";
		$html .= "<th>" . esc_html__ ( 'Tribute Gift', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . $tribute_gift_message . "</td></tr>\n";

		$payment_method = get_post_meta ( $donation_id, '_dgx_donate_payment_method', true );
		$html .= "<tr><th>" . esc_html__ ( 'Payment Method', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $payment_method ) . "</td></tr>\n";

		$repeating             = get_post_meta ( $donation_id, '_dgx_donate_repeating', true );
		$is_repeating_donation = ! empty( $repeating );
		if( $is_repeating_donation ) {
			$repeatingText = __ ( 'Yes', 'seamless-donations' );
		} else {
			$repeatingText = __ ( 'No', 'seamless-donations' );
		}
		$html .= "<tr><th>" . esc_html__ ( 'Repeating', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $repeatingText ) . "</td></tr>\n";

		$session_id = get_post_meta ( $donation_id, '_dgx_donate_session_id', true );
		$html .= "<tr><th>" . esc_html__ ( 'Session ID', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $session_id ) . "</td></tr>\n";

		$transaction_id = get_post_meta ( $donation_id, '_dgx_donate_transaction_id', true );
		$html .= "<tr><th>" . esc_html__ ( 'Transaction ID', 'seamless-donations' ) . "</th>";
		$html .= "<td>" . esc_html ( $transaction_id ) . "</td></tr>\n";

		$html .= "</tbody></table>\n";

		if( $is_repeating_donation ) {
			// Display links to related (same session ID) donations
			$related_donation_ids = get_donations_by_meta ( '_dgx_donate_session_id', $session_id, - 1 );

			// Unset this donation if present (it probably will be)
			if( ( $index = array_search ( $donation_id, $related_donation_ids ) ) !== false ) {
				unset( $related_donation_ids[ $index ] );
			}

			$html .= "<h3>" . esc_html__ ( 'Related Donations', 'seamless-donations' ) . "</h3>\n";
			$html .= "<p class='description'>";
			$html .= esc_html__ (
				'For repeating donations, displays a list of other donations in the series (subscription)',
				'seamless-donations' );
			$html .= "</p>\n";
			// Show the array
			$html .= "<table class='widefat'><tbody>\n";
			if( count ( $related_donation_ids ) ) {
				$html .= "<tr>";
				$html .= "<th>" . esc_html__ ( 'Date', 'seamless-donations' ) . "</th>";
				$html .= "<th>" . esc_html__ ( 'Transaction ID', 'seamless-donations' ) . "</th></tr>";
				foreach( (array) $related_donation_ids as $related_donation_id ) {
					$year          = get_post_meta ( $related_donation_id, '_dgx_donate_year', true );
					$month         = get_post_meta ( $related_donation_id, '_dgx_donate_month', true );
					$day           = get_post_meta ( $related_donation_id, '_dgx_donate_day', true );
					$time          = get_post_meta ( $related_donation_id, '_dgx_donate_time', true );
					$donation_date = $month . "/" . $day . "/" . $year;

					$transaction_id = get_post_meta ( $related_donation_id, '_dgx_donate_transaction_id', true );

					$donation_detail = seamless_donations_get_donation_detail_link ( $related_donation_id );
					$html .= "<tr>";
					$html .= "<td><a href='" . esc_url ( $donation_detail ) . "'>" .
					         esc_html ( $donation_date . " " . $time ) . "</a></td>";
					$html .= "<td>" . esc_html ( $transaction_id ) . "</td></tr>\n";
				}
			} else {
				$html .= "<tr>";
				$html .= "<th>" . esc_html__ ( 'No related donations found', 'seamless-donations' ) . "</th>";
				$html .= "</tr>\n";
			}
			$html .= "</tbody></table>\n";
		}

		return $html;
	}
}

new SeamlessDonationsDonationPostType( 'donation' );
new SeamlessDonationsDonationDonorInfoMetaBox(
	NULL,                                               // meta box ID - can be null.
	__ ( 'Donor Information', 'seamless-donations' ),   // title
	array( 'donation' ),    // post type slugs: post, page, etc.
	'normal',               // context
	'low'                   // priority
);
new SeamlessDonationsDonationDetailMetaBox(
	NULL,                                               // meta box ID - can be null.
	__ ( 'Donation Details', 'seamless-donations' ),   // title
	array( 'donation' ),    // post type slugs: post, page, etc.
	'normal',               // context
	'low'                   // priority
);

// remove the Add New donation menu
add_action ( 'admin_menu', 'seamless_donations_remove_add_new_donation_menu' );
function seamless_donations_remove_add_new_donation_menu () {

	remove_submenu_page ( 'edit.php?post_type=donation', 'post-new.php?post_type=donation' );
}
