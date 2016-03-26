<?php
/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

$apf_folder = dirname( dirname( __FILE__ ) ) . '/library/apf/';

if ( ! class_exists( 'SeamlessDonationsAdminPageFramework' ) ) {
	include_once( $apf_folder . 'admin-page-framework.php' );
}

//// WIDGET BASICS ////

class SeamlessDonationsThisMonth_Widget extends SeamlessDonationsAdminPageFramework_Widget {

	public function setUp() {

		$this->setArguments(
			array(
				'description' => __(
					'Displays the total of all donations made in the current month.',
					'seamless-donations' ),
			)
		);
	}

	public function load( $oAdminWidget ) {

		// Prepare the settings array for the widget
		// the $this->addSettingFields() takes multiple parameters, each an array to be added

		// promo
		$promo_text = 'Basic Widget Pack provides five additional informative and helpful widgets.';
		$promo_url  = 'http://zatzlabs.com/project/seamless-donations-basic-widget-pack/';
		$promo_desc = seamless_donations_get_feature_promo( $promo_text, $promo_url );

		$this->addSettingFields(
			array( // display title field
			       'field_id' => 'month_total_title',
			       'type'     => 'text',
			       'title'    => __( 'Title', 'seamless-donations' ),
			       'default'  => __( 'Donations This Month', 'seamless-donations' ),
			),
			array(
				'field_id' => 'month_total_name',
				'type'     => 'text',
				'title'    => __( 'Month Total Name', 'seamless-donations' ),
				'default'  => __( 'Month Total', 'seamless-donations' ),
			),
			array(
				'field_id' => 'month_total_separator',
				'type'     => 'text',
				'title'    => __( 'Separator Between Name and Total', 'seamless-donations' ),
				'default'  => __( ' - ', 'seamless-donations' ),
			)
		);

		if ( ! function_exists( 'seamless_donations_bwp_is_compatible' ) ) {
				$this->oForm->aFields['_default']['month_total_separator']['description']
					= __( $promo_desc, 'seamless-donations' );
		}
	}

	public
	function validate(
		$aSubmit, $aStored, $oAdminWidget
	) {

		return $aSubmit;
	}

	public
	function content(
		$sContent, $aArguments, $aFormData
	) {

		// retrieve widget options

		if ( isset( $aFormData['month_total_separator'] ) ) {
			$total_sep = $aFormData['month_total_separator'];
		} else {
			$total_sep = ' - ';
		}
		if ( isset( $aFormData['month_total_name'] ) ) {
			$total_name = $aFormData['month_total_name'];
		} else {
			$total_name = 'Total This Month';
		}

		$html = '<h2 class="widget-title">' . $aFormData['month_total_title'] . '</h2>';

		////

		/** Set up a date interval object for 6 months ago (you can change as required) */
		$interval         = new DateInterval( 'P1M' );
		$interval->invert = 1;

		/** Grab the date as it was 6 months ago */
		$date = new DateTime( date( 'Y-m-d' ) );
		$date->add( $interval );

		/////
		// select the donations to show

		$args = array(
			'post_type'   => 'donation',
			'post_status' => 'publish',
			'nopaging'    => true,
			'order_by'    => 'date',
			'date_query'  => array(
				'after' => $date->format( 'Y-m-d' )
			)
		);

		$donations_array = get_posts( $args );

		$donation_total = 0.0;

		// loop through a list of donations
		for ( $i = 0; $i < count( $donations_array ); ++ $i ) {

			// extract the fund id from the donation and fund records
			$donation_id = $donations_array[ $i ]->ID;

			$amount = get_post_meta( $donation_id, '_dgx_donate_amount', true );
			$donation_total += floatval( $amount );
		}

		$html .= "<ul>";
		$html .= '<li>';

		$html .= $total_name;
		$html .= $total_sep;

		$donation_total = dgx_donate_get_escaped_formatted_amount( floatval( $donation_total ), 0 );
		$html .= $donation_total;

		$html .= '</li>';

		$html .= "</ul>";

		return $html;
	}
}

new SeamlessDonationsThisMonth_Widget( __( 'Donations This Month', 'seamless-donations' ) );
