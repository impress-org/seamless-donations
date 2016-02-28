<?php

/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

class SeamlessDonationsFundsPostType extends SeamlessDonationsAdminPageFramework_PostType {

	/**
	 * Automatically called with the 'wp_loaded' hook.
	 */
	public function setUp () {

		// argument - http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
		$funds_type_setup = array();
		$funds_setup      = array(
			'labels'    => array(
				'name'                => __ ( 'Funds', 'seamless-donations' ),
				'singular_name'       => __ ( 'Fund', 'seamless-donations' ),
				'add_new_item'        => __ ( 'Fund', 'seamless-donations' ),
				'edit_item'           => __ ( 'Fund', 'seamless-donations' ),
				'new_item'            => __ ( 'Fund', 'seamless-donations' ),
				'view_item'           => __ ( 'Fund', 'seamless-donations' ),
				'search_items'        => __ ( 'Search funds', 'seamless-donations' ),
				'not_found'           => __ ( 'No funds found', 'seamless-donations' ),
				'not_found_in_trash'  => __ ( 'No funds found in Trash', 'seamless-donations' ),
				'restored_from_trash' => __ ( 'fund', 'seamless-donations' ),
			),
			'supports'  => array( 'title' ),
			'public'    => true,
			'menu_icon' => 'dashicons-palmtree',

		);

		$compact_menus = get_option ( 'dgx_donate_compact_menus' );
		if( $compact_menus == 1 ) {
			$funds_setup['show_ui'] = true;
			$funds_setup['show_in_menu'] = 'SeamlessDonationsAdmin';
			unset($funds_setup['public']);
			unset($funds_setup['menu_icon']);
		}

		$funds_setup      = apply_filters ( 'seamless_donations_funds_setup', $funds_setup );
		$funds_type_setup = apply_filters ( 'seamless_donations_funds_type_setup', $funds_type_setup );

		$this->setArguments ( $funds_setup );

		if( sizeof ( $funds_type_setup ) > 0 ) {
			$this->addTaxonomy (
				'fund_types',  // taxonomy slug
				$funds_type_setup );
		}
	}

	public function columns_funds ( $aHeaderColumns ) {

		return array(
			       'cb'    => '<input type="checkbox" />', // Checkbox for bulk actions.
			       'title' => __ ( 'Title', 'seamless-donations' ),
			       'color' => __ ( 'Display on Donation Form', 'seamless-donations' ),
		       ) + $aHeaderColumns;
	}

	public function cell_funds_color ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		$_show = get_post_meta ( $iPostID, '_dgx_donate_fund_show', true );

		return esc_attr ( $_show );
	}

	public function style_SeamlessDonationsFundsPostType ( $sStyle ) {

		// clean up donor list table
		$funds_header_style = ".add-new-h2 {display:none}" . PHP_EOL;   // delete Add New from top
		$funds_header_style .= ".bulkactions {display:none}" . PHP_EOL; // delete Bulk Actions section
		$funds_header_style .= ".edit {display:none}" . PHP_EOL;        // delete the quick actions under the link
		$funds_header_style .= ".trash {display:none}" . PHP_EOL;
		$funds_header_style .= ".inline {display:none}" . PHP_EOL;
		$funds_header_style .= ".view {display:none}" . PHP_EOL;

		// right-align donor amount information
		$funds_header_style .= ".column-amount {text-align:right; width:200px}" . PHP_EOL; // right-align dollar amounts
		$funds_header_style .= "th#amount.manage-column.column-amount {text-align:right}";
		$funds_header_style .= "th.manage-column.column-amount {text-align:right}";

		// clean up donor edit page
		$funds_header_style .= "#edit-slug-box {display:none}" . PHP_EOL;
		$funds_header_style .= ".page-title-action {display:none}" . PHP_EOL;
		$funds_header_style .= "#delete-action {display:none}" . PHP_EOL;
		$funds_header_style .= "a.edit-post-status {display:none}" . PHP_EOL;
		$funds_header_style .= "a.edit-visibility {display:none}" . PHP_EOL;
		$funds_header_style .= "a.edit-timestamp {display:none}" . PHP_EOL;
		$funds_header_style .= "#preview-action {display:none}" . PHP_EOL;
		$funds_header_style .= "label[for=slugdiv-hide] {display:none}" . PHP_EOL;
		$funds_header_style .= "#slugdiv {display:none}" . PHP_EOL;

		$funds_header_style = apply_filters ( 'seamless_donations_funds_header_style', $funds_header_style );

		return $sStyle . $funds_header_style;
	}
}




class SeamlessDonationsFundsCustomPostTypeMetaBox extends SeamlessDonationsAdminPageFramework_MetaBox {

	/*
	 * Use the setUp() method to define settings of this meta box.
	 */
	public function setUp () {

		/**
		 * Adds setting fields in the meta box.
		 */
		$this->addSettingFields (

			array( // Single set of radio buttons
			       'field_id'    => '_dgx_donate_fund_show',
			       'title'       => __ ( 'Display on donation form', 'seamless-donations' ),
			       'type'        => 'radio',
			       'label'       => array(
				       'Yes' => 'Yes',
				       'No'  => 'No',
			       ),
			       'default'     => 'Yes',
			       //'after_label' => '<br />',
			       //'attributes'  => array(
			       //   'c' => array(
			       //       'disabled' => 'disabled',
			       //   ),
			       //),
			       'description' => __ (
				       'If you select Yes, this fund will be shown on the front-end donation form.' .
				       '<br>If you select No, this fund will not be shown on the donation form.',
				       'seamless-donations' )
			)
		);
	}
}

class SeamlessDonationsFundsDetailMetaBox extends SeamlessDonationsAdminPageFramework_MetaBox {

	/*
	 * Use the setUp() method to define settings of this meta box.
	 */
	public function setUp () {

		// get fund data
		$post_id = $_GET['post'];;

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
			$html .= "<th>" . esc_html__ ( 'Donor', 'seamless-donations' ) . "</th>";
			$html .= "<th>" . esc_html__ ( 'Amount', 'seamless-donations' ) . "</th>";
			$html .= "</tr>\n";

			$fund_total          = 0;
			$currency_codes = array();

			foreach( (array) $my_donations as $donation_id ) {

				$year       = get_post_meta ( $donation_id, '_dgx_donate_year', true );
				$month      = get_post_meta ( $donation_id, '_dgx_donate_month', true );
				$day        = get_post_meta ( $donation_id, '_dgx_donate_day', true );
				$time       = get_post_meta ( $donation_id, '_dgx_donate_time', true );

				$donor_name  = __ ( 'Anonymous', 'seamless-donations' );
				$anonymous = get_post_meta ( $donation_id, '_dgx_donate_anonymous', true );

				if( ! empty( $anonymous ) ) {
					$first       = get_post_meta ( $donation_id, '_dgx_donate_donor_first_name', true );
					$last        = get_post_meta ( $donation_id, '_dgx_donate_donor_last_name', true );
					$donor_name = sanitize_text_field ( $first . ' ' . $last );
				}

				$amount                                 = get_post_meta ( $donation_id, '_dgx_donate_amount', true );
				$fund_total                            = $fund_total + floatval ( $amount );
				$currency_code                          = dgx_donate_get_donation_currency_code ( $donation_id );
				$currency_codes[ $currency_code ] = true;
				$formatted_amount                       = dgx_donate_get_escaped_formatted_amount (
						$amount, 2, $currency_code );

				$donation_detail = seamless_donations_get_donation_detail_link ( $donation_id );
				$html .= "<tr><td><a href='" . esc_url ( $donation_detail ) . "'>" .
				         esc_html ( $year . "-" . $month . "- " . $day . " " . $time ) . "</a></td>";
				$html .= "<td>" . esc_html ( $donor_name ) . "</td>";
				$html .= "<td>" . $formatted_amount . "</td>";
				$html .= "</tr>\n";
			}
			if( count ( $currency_codes ) > 1 ) {
				$formatted_fund_total = "-";
			} else {
				$formatted_fund_total = dgx_donate_get_escaped_formatted_amount ( $fund_total, 2, $currency_code );
			}
			$html .= "<tr>";
			$html .= "<th>&nbsp</th><th>" . esc_html__ ( 'Fund Total', 'seamless-donations' ) . "</th>";
			$html .= "<td>" . $formatted_fund_total . "</td></tr>\n";

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

// todo make sure user can't add duplicate fund names
new SeamlessDonationsFundsPostType( 'funds' );
new SeamlessDonationsFundsCustomPostTypeMetaBox(
	NULL,   // meta box ID - can be null.
	__ ( 'Fund Settings', 'seamless-donations' ), // title
	array( 'funds' ),                 // post type slugs: post, page, etc.
	'normal',                                        // context
	'low'                                          // priority
);
new SeamlessDonationsFundsDetailMetaBox(
		NULL,                                           // meta box ID - can be null.
		__ ( 'Donations', 'seamless-donations' ),  // title
		array( 'funds' ),   // post type slugs: post, page, etc.
		'normal',           // context
		'low'               // priority
);
