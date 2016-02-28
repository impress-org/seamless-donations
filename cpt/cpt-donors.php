<?php

/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

class SeamlessDonationsDonorPostType extends SeamlessDonationsAdminPageFramework_PostType {

	/**
	 * Automatically called with the 'wp_loaded' hook.
	 */
	public function setUp () {

		$donors_type_setup = array();

		// argument - http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
		$donors_setup
			= array(
			'labels'            => array(
				'name'                => __ ( 'Donors', 'seamless-donations' ),
				'singular_name'       => __ ( 'Donor', 'seamless-donations' ),
				'add_new_item'        => __ ( 'Donor', 'seamless-donations' ),
				'edit_item'           => __ ( 'Donor', 'seamless-donations' ),
				'new_item'            => __ ( 'Donor', 'seamless-donations' ),
				'view_item'           => __ ( 'Donor', 'seamless-donations' ),
				'search_items'        => __ ( 'Search donors', 'seamless-donations' ),
				'not_found'           => __ ( 'No donors found', 'seamless-donations' ),
				'not_found_in_trash'  => __ (
					'No donors found in Trash', 'seamless-donations' ),
				'restored_from_trash' => __ ( 'donor', 'seamless-donations' ),
			),
			'supports'          => array( 'title' ),
			'public'            => true,
			'show_table_filter' => false,
			'menu_icon'         => 'dashicons-palmtree',
		);

		$compact_menus = get_option ( 'dgx_donate_compact_menus' );
		if( $compact_menus == 1 ) {
			$donors_setup['show_ui']      = true;
			$donors_setup['show_in_menu'] = 'SeamlessDonationsAdmin';
			unset( $donors_setup['public'] );
			unset( $donors_setup['menu_icon'] );
		}

		$donors_setup      = apply_filters ( 'seamless_donations_donors_setup', $donors_setup );
		$donors_type_setup = apply_filters ( 'seamless_donations_donors_type_setup', $donors_type_setup );

		$this->setArguments ( $donors_setup );

		if( sizeof ( $donors_type_setup ) > 0 ) {
			$this->addTaxonomy (
				'donor_type',  // taxonomy slug
				$donors_type_setup );
		}
	}

	public function style_SeamlessDonationsDonorPostType ( $sStyle ) {

		// clean up donor list table
		$donor_header_style = ".add-new-h2 {display:none}" . PHP_EOL;   // delete Add New from top
		$donor_header_style .= ".bulkactions {display:none}" . PHP_EOL; // delete Bulk Actions section
		$donor_header_style .= ".edit {display:none}" . PHP_EOL;        // delete the quick actions under the link
		$donor_header_style .= ".trash {display:none}" . PHP_EOL;
		$donor_header_style .= ".inline {display:none}" . PHP_EOL;
		$donor_header_style .= ".view {display:none}" . PHP_EOL;

		// right-align donor amount information
		$donor_header_style .= ".column-amount {text-align:right; width:200px}" . PHP_EOL; // right-align dollar amounts
		$donor_header_style .= "th#amount.manage-column.column-amount {text-align:right}";
		$donor_header_style .= "th.manage-column.column-amount {text-align:right}";

		// clean up donor edit page
		$donor_header_style .= "#edit-slug-box {display:none}" . PHP_EOL;
		$donor_header_style .= ".page-title-action {display:none}" . PHP_EOL;
		$donor_header_style .= "#delete-action {display:none}" . PHP_EOL;
		$donor_header_style .= "a.edit-post-status {display:none}" . PHP_EOL;
		$donor_header_style .= "a.edit-visibility {display:none}" . PHP_EOL;
		$donor_header_style .= "a.edit-timestamp {display:none}" . PHP_EOL;
		$donor_header_style .= "#preview-action {display:none}" . PHP_EOL;
		$donor_header_style .= "label[for=slugdiv-hide] {display:none}" . PHP_EOL;
		$donor_header_style .= "#slugdiv {display:none}" . PHP_EOL;

		$donor_header_style = apply_filters ( 'seamless_donations_donor_header_style', $donor_header_style );

		return $sStyle . $donor_header_style;
	}

	public function script_SeamlessDonationsDonorPostType ( $sScript ) {

		// make sure the post title name can't be edited - set to readonly
		$script = 'var titleObj = document.getElementById("title");';
		$script .= 'if ((titleObj !== undefined) && (titleObj !== null)) {';
		$script .= 'titleObj.setAttribute("readonly", "true");}';

		return $sScript . $script;
	}

	public function columns_donor ( $aHeaderColumns ) {

		$donor_header_array = array(
			//'cb'         => '<input type="checkbox" />', // Checkbox for bulk actions.
			'title'      => __ ( 'Donor', 'seamless-donations' ),
			'email'      => __ ( 'Email', 'seamless-donations' ),
			'occupation' => __ ( 'Occupation', 'seamless-donations' ),
			'employer'   => __ ( 'Employer', 'seamless-donations' ),
			'amount'     => __ ( 'Total Donated', 'seamless-donations' ),
		);

		$donor_header_array = apply_filters ( 'seamless_donations_donor_header_array', $donor_header_array );

		return $donor_header_array;
	}

	public function cell_donor_email ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		return esc_attr ( get_post_meta ( $iPostID, '_dgx_donate_donor_email', true ) );
	}

	public function cell_donor_employer ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		return esc_attr ( get_post_meta ( $iPostID, '_dgx_donate_donor_employer', true ) );
	}

	public function cell_donor_occupation ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		return esc_attr ( get_post_meta ( $iPostID, '_dgx_donate_donor_occupation', true ) );
	}

	public function cell_donor_amount ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		$donation_list = get_post_meta ( $iPostID, '_dgx_donate_donor_donations', true );

		$amount            = floatval ( 0.0 );
		$donation_id_array = explode ( ',', $donation_list );
		$donation_id_array = array_values (
			array_filter ( $donation_id_array ) ); // remove empty elements from the array

		while( $donation_id = current ( $donation_id_array ) ) {

			$new_amount = floatVal ( get_post_meta ( $donation_id, '_dgx_donate_amount', true ) );
			$amount += $new_amount;

			next ( $donation_id_array );
		}

		$currency_code    = dgx_donate_get_donation_currency_code ( $donation_id );
		$formatted_amount = dgx_donate_get_escaped_formatted_amount ( $amount, 2, $currency_code );

		return esc_attr ( $formatted_amount );
	}
}

class SeamlessDonationsDonorInfoMetaBox extends SeamlessDonationsAdminPageFramework_MetaBox {

	/*
	 * Use the setUp() method to define settings of this meta box.
	 */
	public function setUp () {

		// get display meta data
		$post_id  = $_GET['post'];
		$first    = get_post_meta ( $post_id, '_dgx_donate_donor_first_name', true );
		$last     = get_post_meta ( $post_id, '_dgx_donate_donor_last_name', true );
		$email    = get_post_meta ( $post_id, '_dgx_donate_donor_email', true );
		$phone    = get_post_meta ( $post_id, '_dgx_donate_donor_phone', true );
		$address  = get_post_meta ( $post_id, '_dgx_donate_donor_address', true );
		$address2 = get_post_meta ( $post_id, '_dgx_donate_donor_address2', true );
		$city     = get_post_meta ( $post_id, '_dgx_donate_donor_city', true );
		$state    = get_post_meta ( $post_id, '_dgx_donate_donor_state', true );
		$province = get_post_meta ( $post_id, '_dgx_donate_donor_province', true );
		$country  = get_post_meta ( $post_id, '_dgx_donate_donor_country', true );
		$zip      = get_post_meta ( $post_id, '_dgx_donate_donor_zip', true );
		$anon     = get_post_meta ( $post_id, '_dgx_donate_anonymous', true );

		if( empty( $country ) ) { /* older versions only did US */
			$country = 'US';
			update_post_meta ( $post_id, '_dgx_donate_donor_country', 'US' );
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
		$html .= $email != '' ? $email . '<br>' : '';
		$html .= esc_html__ ('Anonymity requested: ', 'seamless-donations') . $anon;

		$this->addSettingFields (

			array(
				'field_id'     => 'donor_info',
				'type'         => 'donor_info',
				'before_field' => $html,
			)
		);
	}
}

class SeamlessDonationsDonorDetailMetaBox extends SeamlessDonationsAdminPageFramework_MetaBox {

	/*
	 * Use the setUp() method to define settings of this meta box.
	 */
	public function setUp () {

		// get donor data
		$post_id = $_GET['post'];;
		$first = get_post_meta ( $post_id, '_dgx_donate_donor_first_name', true );
		$last  = get_post_meta ( $post_id, '_dgx_donate_donor_last_name', true );
		$email = get_post_meta ( $post_id, '_dgx_donate_donor_email', true );

		$donation_list = get_post_meta ( $post_id, '_dgx_donate_donor_donations', true );
		$my_donations  = explode ( ',', $donation_list );
		$my_donations  = array_values ( array_filter ( $my_donations ) ); // remove empty elements from the array

		// now build the table
		$html = "";

		if( count ( $my_donations ) < 1 ) {
			$html .= "<p>" . esc_html__ ( 'No donations found.', 'seamless-donations' ) . "</p>";
		} else {
			$html .= "<table class='widefat'><tbody>\n";
			$html .= "<tr>";
			$html .= "<th>" . esc_html__ ( 'Date', 'seamless-donations' ) . "</th>";
			$html .= "<th>" . esc_html__ ( 'Fund', 'seamless-donations' ) . "</th>";
			$html .= "<th>" . esc_html__ ( 'Amount', 'seamless-donations' ) . "</th>";
			$html .= "<th>" . esc_html__ ( 'Anonymous', 'seamless-donations' ) . "</th>";
			$html .= "</tr>\n";

			$donor_total          = 0;
			$donor_currency_codes = array();

			foreach( (array) $my_donations as $donation_id ) {

				$year       = get_post_meta ( $donation_id, '_dgx_donate_year', true );
				$month      = get_post_meta ( $donation_id, '_dgx_donate_month', true );
				$day        = get_post_meta ( $donation_id, '_dgx_donate_day', true );
				$time       = get_post_meta ( $donation_id, '_dgx_donate_time', true );
				$designated = get_post_meta ( $donation_id, '_dgx_donate_designated', true );
				$anonymous  = get_post_meta ( $donation_id, '_dgx_donate_anonymous', true );

				$fund_name = __ ( 'Undesignated', 'seamless-donations' );
				if( ! empty( $designated ) ) {
					$fund_name = get_post_meta ( $donation_id, '_dgx_donate_designated_fund', true );
				}

				$amount                                 = get_post_meta ( $donation_id, '_dgx_donate_amount', true );
				$donor_total                            = $donor_total + floatval ( $amount );
				$currency_code                          = dgx_donate_get_donation_currency_code ( $donation_id );
				$donor_currency_codes[ $currency_code ] = true;
				$formatted_amount                       = dgx_donate_get_escaped_formatted_amount (
					$amount, 2, $currency_code );

				if($anonymous == 'on') {
					$anonymous = 'Yes';
				} else {
					$anonymous = 'No';
				}

				$donation_detail = seamless_donations_get_donation_detail_link ( $donation_id );

				$html .= "<tr><td><a href='" . esc_url ( $donation_detail ) . "'>" .
				         esc_html ( $year . "-" . $month . "- " . $day . " " . $time ) . "</a></td>";
				$html .= "<td>" . esc_html ( $fund_name ) . "</td>";
				$html .= "<td>" . $formatted_amount . "</td>";
				$html .= "<td>" . $anonymous . "</td>";
				$html .= "</tr>\n";
			}
			if( count ( $donor_currency_codes ) > 1 ) {
				$formatted_donor_total = "-";
			} else {
				$formatted_donor_total = dgx_donate_get_escaped_formatted_amount ( $donor_total, 2, $currency_code );
			}
			$html .= "<tr>";
			$html .= "<th>&nbsp</th><th>" . esc_html__ ( 'Donor Total', 'seamless-donations' ) . "</th>";
			$html .= "<td>" . $formatted_donor_total . "</td></tr>\n";

			$html .= "</tbody></table>\n";
		}

		$this->addSettingFields (

			array(
				'field_id'     => 'donor_info',
				'type'         => 'donor_info',
				'before_field' => $html,
			)
		);
	}
}

new SeamlessDonationsDonorPostType( 'donor' );
new SeamlessDonationsDonorInfoMetaBox(
	NULL,                                           // meta box ID - can be null.
	__ ( 'Donor Information', 'seamless-donations' ),  // title
	array( 'donor' ),   // post type slugs: post, page, etc.
	'normal',           // context
	'low'               // priority
);
new SeamlessDonationsDonorDetailMetaBox(
	NULL,                                           // meta box ID - can be null.
	__ ( 'Donations', 'seamless-donations' ),  // title
	array( 'donor' ),   // post type slugs: post, page, etc.
	'normal',           // context
	'low'               // priority
);

// remove the Add New donor menu
add_action ( 'admin_menu', 'seamless_donations_remove_add_new_donor_menu' );
function seamless_donations_remove_add_new_donor_menu () {

	remove_submenu_page ( 'edit.php?post_type=donor', 'post-new.php?post_type=donor' );
}
