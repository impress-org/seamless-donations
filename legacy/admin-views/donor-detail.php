<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */

// *********** THIS CODE HAS BEEN MIGRATED TO 4.0 -- DG
// *********** THIS FILE CAN BE DELETED AT CONSOLIDATION TIME

class Dgx_Donate_Admin_Donor_Detail_View {
	static function show( $donor_id ) {
		echo "<div class='wrap'>\n";
		echo "<div id='icon-edit-pages' class='icon32'></div>\n";
		echo "<h2>" . esc_html__( 'Donor Detail', 'seamless-donations' ) . "</h2>\n";

		$donor_email = strtolower( $donor_id );

		$args = array(
			'numberposts'     => '-1',
			'post_type'       => 'dgx-donation',
			'meta_key'		  => '_dgx_donate_donor_email',
			'meta_value'	  => $donor_email,
			'order'           => 'ASC'
	);

		$my_donations = get_posts( $args );

		$args = array(
			'numberposts'     => '1',
			'post_type'       => 'dgx-donation',
			'meta_key'		  => '_dgx_donate_donor_email',
			'meta_value'	  => $donor_email,
			'order'           => 'DESC'
		);

		$last_donation = get_posts( $args );

		if ( count( $my_donations ) < 1 ) {
			echo "<p>" . esc_html__( 'No donations found.', 'seamless-donations' ) . "</p>";
		} else {
			echo "<div id='col-container'>\n";
			echo "<div id='col-right'>\n";
			echo "<div class='col-wrap'>\n";

			echo "<h3>" . esc_html__( 'Donations by This Donor', 'seamless-donations' ) . "</h3>\n";
			echo "<table class='widefat'><tbody>\n";
			echo "<tr>";
			echo "<th>" . esc_html__( 'Date', 'seamless-donations' ) . "</th>";
			echo "<th>" . esc_html__( 'Fund', 'seamless-donations' ) . "</th>";
			echo "<th>" . esc_html__( 'Amount', 'seamless-donations' ) . "</th>";
			echo "</tr>\n";

			$donor_total = 0;
			$donor_currency_codes = array();

			foreach ( (array) $my_donations as $my_donation ) {
				$donation_id = $my_donation->ID;

				$year = get_post_meta( $donation_id, '_dgx_donate_year', true );
				$month = get_post_meta( $donation_id, '_dgx_donate_month', true );
				$day = get_post_meta( $donation_id, '_dgx_donate_day', true );
				$time = get_post_meta( $donation_id, '_dgx_donate_time', true );
				$fund_name = __( 'Undesignated', 'seamless-donations' );
				$designated = get_post_meta( $donation_id, '_dgx_donate_designated', true );
				if ( ! empty( $designated ) ) {
					$fund_name = get_post_meta( $donation_id, '_dgx_donate_designated_fund', true );
				}
				$amount = get_post_meta( $donation_id, '_dgx_donate_amount', true );
				$donor_total = $donor_total + floatval( $amount );
				$currency_code = dgx_donate_get_donation_currency_code( $donation_id );
				$donor_currency_codes[$currency_code] = true;
				$formatted_amount = dgx_donate_get_escaped_formatted_amount( $amount, 2, $currency_code );

				$donation_detail = dgx_donate_get_donation_detail_link( $donation_id );
				echo "<tr><td><a href='" . esc_url( $donation_detail ) . "'>" . esc_html( $year . "-" . $month . "- " . $day . " " . $time ) . "</a></td>";
				echo "<td>" . esc_html( $fund_name ) . "</td>";
				echo "<td>" . $formatted_amount . "</td>";
				echo "</tr>\n";
			}
			if ( count( $donor_currency_codes ) > 1 ) {
				$formatted_donor_total = "-";
			} else {
				$formatted_donor_total = dgx_donate_get_escaped_formatted_amount( $donor_total, 2, $currency_code );
			}
			echo "<tr>";
			echo "<th>&nbsp</th><th>" . esc_html__( 'Donor Total', 'seamless-donations' ) . "</th>";
			echo "<td>" . $formatted_donor_total . "</td></tr>\n";

			echo "</tbody></table>\n";

			do_action( 'dgx_donate_donor_detail_right', $donor_id );
			do_action( 'dgx_donate_admin_footer' );

			echo "</div> <!-- col-wrap -->\n";
			echo "</div> <!-- col-right -->\n";

			echo "<div id=\"col-left\">\n";
			echo "<div class=\"col-wrap\">\n";

			$donation_id = $last_donation[0]->ID;

			self::echo_donor_information( $donation_id );

			do_action( 'dgx_donate_donor_detail_left', $donor_id );

			echo "</div> <!-- col-wrap -->\n";
			echo "</div> <!-- col-left -->\n";
			echo "</div> <!-- col-container -->\n";
		}

		echo "</div> <!-- wrap -->\n";
	}

	static function echo_donor_information( $donation_id ) {
		$first_name = get_post_meta( $donation_id, '_dgx_donate_donor_first_name', true );
		$last_name = get_post_meta( $donation_id, '_dgx_donate_donor_last_name', true );
		$company = get_post_meta( $donation_id, '_dgx_donate_donor_company_name', true );
		$address1 = get_post_meta( $donation_id, '_dgx_donate_donor_address', true );
		$address2 = get_post_meta( $donation_id, '_dgx_donate_donor_address2', true );
		$city = get_post_meta( $donation_id, '_dgx_donate_donor_city', true );
		$state =  get_post_meta( $donation_id, '_dgx_donate_donor_state', true );
		$province =  get_post_meta( $donation_id, '_dgx_donate_donor_province', true );
		$country =  get_post_meta( $donation_id, '_dgx_donate_donor_country', true );
		if ( empty( $country ) ) { /* older versions only did US */
			$country = 'US';
			update_post_meta( $donation_id, '_dgx_donate_donor_country', 'US' );
		}
		$zip = get_post_meta( $donation_id, '_dgx_donate_donor_zip', true );
		$phone =  get_post_meta( $donation_id, '_dgx_donate_donor_phone', true );
		$email = get_post_meta( $donation_id, '_dgx_donate_donor_email', true );

		echo "<h3>" . esc_html__( 'Donor Information', 'seamless-donations' ) . "</h3>\n";
		echo "<table class='widefat'><tbody>\n";
		echo "<tr>";
		echo "<td>" . esc_html( $first_name . " " . $last_name ) . "<br/>";
		if ( ! empty( $company ) ) {
			echo esc_html( $company ) . "<br/>";
		}
		if ( ! empty( $address1 ) ) {
			echo esc_html( $address1 ) . "<br/>";
		}
		if ( ! empty( $address2 ) ) {
			echo esc_html( $address2 ) . "<br/>";;
		}
		if ( ! empty( $city ) ) {
			echo esc_html( $city . " " );
		}
		if ( 'US' == $country ) {
			echo esc_html( $state . " " );
		} else if ( 'CA' == $country ) {
			echo esc_html( $province . " " );
		}

		if ( dgx_donate_country_requires_postal_code( $country ) ) {
				echo esc_html( " " . $zip );
		}
		echo "<br/>";

		$countries = dgx_donate_get_countries();
		$country_name = $countries[$country];
		echo esc_html( $country_name ) . "<br/><br/>";

		if ( ! empty( $phone ) ) {
			echo esc_html( $phone ) . "<br/>";
		}
		if ( ! empty( $email ) ) {
			echo esc_html( $email );
		}
		echo "</td>";
		echo "</tr>";
		echo "</tbody></table>\n";
	}
}
