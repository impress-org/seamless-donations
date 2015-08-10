<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */

class Dgx_Donate_Admin_Donations_View {
	function __construct() {
		add_action( 'dgx_donate_menu', array( $this, 'menu_item' ), 1 );
	}

	function menu_item() {
		add_submenu_page(
			'dgx_donate_menu_page',
			__( 'Donations', 'seamless-donations' ),
			__( 'Donations', 'seamless-donations' ),
			'manage_options',
			'dgx_donate_donation_report_page',
			array( $this, 'menu_page' )
		);
	}

	function menu_page() {
		echo "<div class='wrap'>\n";
		echo "<div id='icon-edit-pages' class='icon32'></div>\n";

		echo "<h2>" . esc_html__( 'Donations', 'seamless-donations' ) . "</h2>\n";

		// Validate User
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.') );
		}

		// Get form arguments
		$start_date = isset( $_POST['startdate'] ) ? $_POST['startdate'] : '';
		$end_date = isset( $_POST['enddate'] ) ? $_POST['enddate'] : '';

		// If we have form arguments, we must validate the nonce
		if ( count( $_POST ) ) {
			$nonce = $_POST['dgx_donate_donation_report_nonce'];
			if (!wp_verify_nonce( $nonce, 'dgx_donate_donation_report_nonce' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
			}
		}

		// Sanitize and adjust
		$start_date = dgx_donate_sanitize_date( $start_date, 1, 1, date( 'Y' ) );
		$start_timestamp = strtotime( $start_date );

		$end_date = dgx_donate_sanitize_date( $end_date, 12, 31, date( 'Y' ) );
		$end_timestamp = strtotime( $end_date );

		do_action( 'dgx_donate_donations_page_load' );

		echo "<div id='col-container'>\n";
		echo "<div id='col-right'>\n";
		echo "<div class='col-wrap'>\n";

		echo "<h3>" . esc_html( sprintf( __( 'Donation Report for %1$s to %2$s', 'seamless-donations' ), $start_date, $end_date ) ) . "</h3>\n";

		$args = array(
			'numberposts'     => '-1',
			'post_type'       => 'dgx-donation',
			'order'			  => 'ASC'
		);

		$my_donations = get_posts( $args );

		// Scan all the donations for that date range

		// Build a hashmap of funds (don't forget to handle undesignated) to an array of donationIDs
		// Sort by fund name, put undesignated last?
		// Loop through the hashmap, printing the fund, its donations, total for that fund
		// Finally, print a total for the entire timeperiod

		if ( count( $my_donations ) ) {
			$my_funds = array();

			foreach ( (array) $my_donations as $my_donation ) {
				$donation_id = $my_donation->ID;

				$ok_to_add = true;

				$year = get_post_meta( $donation_id, '_dgx_donate_year', true );
				$month = get_post_meta( $donation_id, '_dgx_donate_month', true );
				$day = get_post_meta( $donation_id, '_dgx_donate_day', true );
				$donation_date = $month . "/" . $day . "/" . $year;
				$donation_timestamp = strtotime( $donation_date );

				if ( $donation_timestamp < $start_timestamp ) {
					$ok_to_add = false;
				}

				if ( $donation_timestamp > $end_timestamp ) {
					$ok_to_add = false;
				}

				if ( $ok_to_add ) {
					$designated_fund = __( 'Undesignated', 'seamless-donations' );
					$designated = get_post_meta( $donation_id, '_dgx_donate_designated', true );
						if ( ! empty( $designated ) ) {
						$designated_fund = get_post_meta( $donation_id, '_dgx_donate_designated_fund', true );
					}

					if ( array_key_exists( $designated_fund, $my_funds ) ) {
						$temp_array = $my_funds[$designated_fund];
						$temp_array[] = $donation_id;
						$my_funds[$designated_fund] = $temp_array;
					} else {
						$my_funds[$designated_fund] = array( $donation_id );
					}
				}
			}

			ksort( $my_funds );

			// Start the table
			echo "<table class='widefat'><tbody>\n";
			echo "<tr>";
			echo "<th>" . esc_html__( 'Fund/Date', 'seamless-donations' ) . "</th>";
			echo "<th>" . esc_html__( 'Donor', 'seamless-donations' ) . "</th>";
			echo "<th>" . esc_html__( 'Amount', 'seamless-donations' ) . "</th>";
			echo "</tr>\n";

			// Now, loop on the funds and then the donation IDs inside them

			$grand_total = 0;
			$all_currency_codes_found = array();

			foreach ( (array) $my_funds as $my_fund => $fund_donation_ids) {
				$fund_total = 0;
				$fund_count = count( $fund_donation_ids );
				$fund_currency_codes_found = array();

				echo "<tr>";
				echo "<th colspan='3'>" . esc_html( $my_fund . " (" . $fund_count . ")" ) . "</th>";
				echo "</tr>\n";
				foreach ( (array) $fund_donation_ids as $donation_id ) {
					$year = get_post_meta( $donation_id, '_dgx_donate_year', true );
					$month = get_post_meta( $donation_id, '_dgx_donate_month', true );
					$day = get_post_meta( $donation_id, '_dgx_donate_day', true );
					$time = get_post_meta( $donation_id, '_dgx_donate_time', true );
					$first_name = get_post_meta( $donation_id, '_dgx_donate_donor_first_name', true );
					$last_name = get_post_meta( $donation_id, '_dgx_donate_donor_last_name', true );
					$amount = get_post_meta( $donation_id, '_dgx_donate_amount', true );
					$fund_total = $fund_total + floatval( $amount );
					$currency_code = dgx_donate_get_donation_currency_code( $donation_id );
					$all_currency_codes_found[$currency_code] = true;
					$fund_currency_codes_found[$currency_code] = true;
					$formatted_amount = dgx_donate_get_escaped_formatted_amount( $amount, 2, $currency_code );
					$donation_detail = dgx_donate_get_donation_detail_link( $donation_id );
					$donor_email = get_post_meta( $donation_id, '_dgx_donate_donor_email', true );
					$donor_detail = dgx_donate_get_donor_detail_link( $donor_email );

					echo "<tr>";
					echo "<td><a href='" . esc_url( $donation_detail ) . "'>" . esc_html( $year . "-" . $month . "-" . $day . " " . $time ) . "</a></td>";
					echo "<td><a href='" . esc_url( $donor_detail ) . "'>" . esc_html( $first_name . " " . $last_name ) . "</a></td>";
					echo "<td>" . $formatted_amount . "</td>";
					echo "</tr>\n";
				}
				if ( count( $fund_currency_codes_found ) > 1 ) {
					$formatted_fund_total = "-";
				} else {
					$formatted_fund_total = dgx_donate_get_escaped_formatted_amount( $fund_total, 2, $currency_code );
				}
				echo "<tr>";
				echo "<th>&nbsp;</th>";
				echo "<th>" . esc_html__( 'Fund Subtotal', 'seamless-donations' ) . "</th>";
				echo "<td>" . $formatted_fund_total . "</td>";
				echo "</tr>\n";
				$grand_total = $grand_total + $fund_total;
			}

			if ( count( $all_currency_codes_found ) > 1 ) {
				$formatted_grand_total = "-";
			} else {
				$formatted_grand_total = dgx_donate_get_escaped_formatted_amount( $grand_total, 2, $currency_code );
			}
			echo "<tr>";
			echo "<th>&nbsp</th>";
			echo "<th>" . esc_html__( 'Grand Total', 'seamless-donations' ) . "</th>";
			echo "<td>" . $formatted_grand_total . "</td>";
			echo "</tr>\n";

			echo "</tbody></table>\n";
		} else {
			echo "<p>" . esc_html__( 'No donations found.', 'seamless-donations' ) . "</p>\n";
		}

		do_action( 'dgx_donate_donations_page_right' );
		do_action( 'dgx_donate_admin_footer' );

		echo "</div> <!-- col-wrap -->\n";
		echo "</div> <!-- col-right -->\n";

		echo "<div id='col-left'>\n";
		echo "<div class='col-wrap'>\n";

		if ( count( $my_donations ) ) {
			$export_url = plugins_url( '/export.php', __FILE__ );
			echo "<h3>" . esc_html__( 'Export Report as Spreadsheet (CSV)', 'seamless-donations' ) ."</h3>\n";
			echo "<p>" . esc_html__( 'Click the following button to export detailed information for each donation in this report to a comma-separated-value (CSV) file compatible with most spreadsheet software.', 'seamless-donations' ) . "</p>";
			echo "<form method='POST' action='" . esc_url( $export_url ) . "'>\n";
			echo "<input type='hidden' name='startdate' value='" . esc_attr( $start_date ) . "' size='12'/>";
			echo "<input type='hidden' name='enddate' value='" . esc_attr( $end_date ) . "' size='12'/>";
			echo "</p><p>";
			echo "<input id='submit' class='button' type='submit' value='" . esc_attr__( 'Export Report', 'seamless-donations' ) . "' name='submit'></p>\n";
			echo "</form>";
			echo "<hr/>";
		}

		echo "<h3>" . esc_html__( 'Date Range', 'seamless-donations' ) . "</h3>\n";
		echo "<form method='POST' action=''>\n";

		$nonce = wp_create_nonce( 'dgx_donate_donation_report_nonce' );
		echo "<input type='hidden' name='dgx_donate_donation_report_nonce' value='" . esc_attr( $nonce ) ."' />\n";

		echo "<p>";
		echo esc_html__( 'Start Date', 'seamless-donations' ) . ": ";
		echo "<input type='text' name='startdate' value='" . esc_attr( $start_date ) . "' size='12'/>";
		echo "<br/>";
		echo esc_html__( 'End Date', 'seamless-donations' ) . ": ";
		echo "<input type='text' name='enddate' value='" . esc_attr( $end_date ) . "' size='12'/>";
		echo "</p>";
		echo "<p>";
		echo "<input id='submit' class='button' type='submit' value='" . esc_attr__( 'Update', 'seamless-donations' ) . "' name='submit'>";
		echo "</p>\n";
		echo "</form>";

		do_action( 'dgx_donate_donations_page_left' );

		echo "</div> <!-- col-wrap -->\n";
		echo "</div> <!-- col-left -->\n";
		echo "</div> <!-- col-container -->\n";

		echo "</div> <!-- wrap -->\n";
	}
}

$dgx_donate_admin_donations_view = new Dgx_Donate_Admin_Donations_View();
