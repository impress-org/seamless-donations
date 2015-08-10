<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */

class Dgx_Donate_Admin_Donation_Detail_View {
	static function show( $donation_id ) {
		// Validate User
	    if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
	    }

		// Get form arguments
		$delete_donation = "";
		if ( isset( $_POST['delete_donation'] ) ) {
			$delete_donation = $_POST['delete_donation'];
		}

		// If we have form arguments, we must validate the nonce
		if ( count( $_POST ) ) {
			$nonce = $_POST['dgx_donate_donation_detail_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'dgx_donate_donation_detail_nonce' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
			}
		}

		echo "<div class='wrap'>\n";
		echo "<div id='icon-edit-pages' class='icon32'></div>\n";
		echo "<h2>" . esc_html__( 'Donation Detail', 'seamless-donations' ) . "</h2>\n";

		$donation_deleted = false;
		if ( "true" == $delete_donation ) {
			dgx_donate_debug_log( "Donation (ID: $donation_id) deleted" );
			wp_delete_post( $donation_id, true ); /* true = force delete / bypass trash */
			$donation_deleted = true;
			$message = __( 'Donation deleted', 'seamless-donations' );
		}

		// Display any message
		if ( ! empty( $message ) ) {
			echo "<div id='message' class='updated below-h2'>\n";
			echo "<p>" . esc_html( $message ) . "</p>\n";
			echo "</div>\n";
		}

		if ( ! $donation_deleted ) {
			echo "<div id='col-container'>\n";
			echo "<div id='col-right'>\n";
			echo "<div class='col-wrap'>\n";

			echo "<h3>" . esc_html__( 'Donation Details', 'seamless-donations' ) . "</h3>\n";
			echo "<table class='widefat'><tbody>\n";

			$year = get_post_meta( $donation_id, '_dgx_donate_year', true );
			$month = get_post_meta( $donation_id, '_dgx_donate_month', true );
			$day = get_post_meta( $donation_id, '_dgx_donate_day', true );
			$time = get_post_meta( $donation_id, '_dgx_donate_time', true );

			echo "<tr>";
			echo "<th>" . esc_html__( 'Date', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $month . "/" . $day . "/" . $year . " " . $time ) . "</td></tr>\n";

			$amount = get_post_meta( $donation_id, '_dgx_donate_amount', true );
			$currency_code = dgx_donate_get_donation_currency_code( $donation_id );
			$formatted_amount = dgx_donate_get_escaped_formatted_amount( $amount, 2, $currency_code );
			echo "<tr>";
			echo "<th>" . esc_html__( 'Amount', 'seamless-donations' ) . "</th>";
			echo "<td>" . $formatted_amount . "</td></tr>\n";

			$add_to_mailing_list = get_post_meta( $donation_id, '_dgx_donate_add_to_mailing_list', true );
			if ( ! empty( $add_to_mailing_list ) ) {
				$add_to_mailing_list = __( 'Yes', 'seamless-donations' );
			} else {
				$add_to_mailing_list = __( 'No', 'seamless-donations' );
			}
			echo "<tr><th>" . esc_html__( 'Add to Mailing List?', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $add_to_mailing_list ) . "</td></tr>\n";

			$anonymous = get_post_meta( $donation_id, '_dgx_donate_anonymous', true );
			if ( empty( $anonymous ) ) {
				$anonymous = __( 'No', 'seamless-donations' );
			} else {
				$anonymous = __( 'Yes', 'seamless-donations' );
			}
			echo "<tr><th>" . esc_html__( 'Would like to remain anonymous?', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $anonymous ) . "</td></tr>\n";

			$fund_name = __( 'Undesignated', 'seamless-donations' );
			$designated = get_post_meta( $donation_id, '_dgx_donate_designated', true );
			if ( ! empty( $designated ) ) {
				$fund_name = get_post_meta( $donation_id, '_dgx_donate_designated_fund', true );
			}
			echo "<tr><th>" . esc_html__( 'Designated Fund', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $fund_name ) . "</td></tr>\n";

			$employer_match = get_post_meta( $donation_id, '_dgx_donate_employer_match', true );
			if ( empty( $employer_match ) ) {
				$employer_match_message = __( 'No', 'seamless-donations' );
			} else {
				$employer_match_message = __( 'Yes', 'seamless-donations' );
			}
			echo "<tr><th>" . esc_html__( 'Employer Match', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $employer_match_message ) . "</td></tr>\n";

			$employer_name = get_post_meta( $donation_id, '_dgx_donate_employer_name', true );
			if ( empty( $employer_name ) ) {
				$employer_name_message = '-';
			} else {
				$employer_name_message = $employer_name;
			}
			echo "<tr><th>" . esc_html__( 'Employer', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $employer_name_message ) . "</td></tr>\n";

			$occupation = get_post_meta( $donation_id, '_dgx_donate_occupation', true );
			if ( empty( $occupation ) ) {
				$occupation_message = '-';
			} else {
				$occupation_message = $occupation;
			}
			echo "<tr><th>" . esc_html__( 'Occupation', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $occupation_message ) . "</td></tr>\n";

			$donor_country = get_post_meta( $donation_id, '_dgx_donate_donor_country', true );
			if ( 'GB' == $donor_country ) {
				$uk_gift_aid = get_post_meta( $donation_id, '_dgx_donate_uk_gift_aid', true );
				if ( empty( $uk_gift_aid ) ) {
					$uk_gift_aid_message = __( 'No', 'seamless-donations' );
				} else {
					$uk_gift_aid_message = __( 'Yes', 'seamless-donations' );
				}
				echo "<tr><th>" . esc_html__( 'UK Gift Aid', 'seamless-donations' ) . "</th>";
				echo "<td>" . esc_html( $uk_gift_aid_message ) . "</td></tr>\n";
			}

			$tribute_gift_message = __( 'No', 'seamless-donations' );
			$tribute_gift = get_post_meta( $donation_id, '_dgx_donate_tribute_gift', true );
			if ( ! empty( $tribute_gift ) ) {
				$tribute_gift_message = __( 'Yes', 'seamless-donations' ) . " - ";

				$honoree_name = get_post_meta( $donation_id, '_dgx_donate_honoree_name', true );
				$honor_by_email = get_post_meta( $donation_id, '_dgx_donate_honor_by_email', true );
				$honoree_email_name = get_post_meta( $donation_id, '_dgx_donate_honoree_email_name', true );
				$honoree_post_name = get_post_meta( $donation_id, '_dgx_donate_honoree_post_name', true );
				$honoree_email = get_post_meta( $donation_id, '_dgx_donate_honoree_email', true );
				$honoree_address = get_post_meta( $donation_id, '_dgx_donate_honoree_address', true );
				$honoree_city = get_post_meta( $donation_id, '_dgx_donate_honoree_city', true );
				$honoree_state = get_post_meta( $donation_id, '_dgx_donate_honoree_state', true );
				$honoree_province = get_post_meta( $donation_id, '_dgx_donate_honoree_province', true );
				$honoree_zip = get_post_meta( $donation_id, '_dgx_donate_honoree_zip', true );
				$honoree_country = get_post_meta( $donation_id, '_dgx_donate_honoree_country', true );
				$memorial_gift = get_post_meta( $donation_id, '_dgx_donate_memorial_gift', true );

				if ( empty( $memorial_gift ) ) {
					$tribute_gift_message .= __( 'in honor of', 'seamless-donations' ) . ' ';
				} else {
					$tribute_gift_message .= __( 'in memory of', 'seamless-donations' ). ' ';
				}

				$tribute_gift_message .= $honoree_name . "<br/><br/>";
				if ( 'TRUE' == $honor_by_email ) {
					$tribute_gift_message .= __( 'Send acknowledgement via email to', 'seamless-donations' ) . '<br/>';
					$tribute_gift_message .= esc_html( $honoree_email_name ) . "<br/>";
					$tribute_gift_message .= esc_html( $honoree_email ) . "<br/>";
				} else {
					$tribute_gift_message .= __( 'Send acknowledgement via postal mail to', 'seamless-donations' ) . '<br/>';
					$tribute_gift_message .= esc_html( $honoree_post_name ) . "<br/>";
					$tribute_gift_message .= esc_html( $honoree_address ) . "<br/>";


					if ( ! empty( $honoree_city ) ) {
						$tribute_gift_message .= esc_html( $honoree_city . " " );
					}
					if ( 'US' == $honoree_country ) {
						$tribute_gift_message .= esc_html( $honoree_state . " " );
					} else if ( 'CA' == $honoree_country ) {
						$tribute_gift_message .= esc_html( $honoree_province . " " );
					}

					if ( dgx_donate_country_requires_postal_code( $honoree_country ) ) {
							$tribute_gift_message .= esc_html( " " . $honoree_zip );
					}
					$tribute_gift_message .= "<br/>";

					$countries = dgx_donate_get_countries();
					$honoree_country_name = $countries[$honoree_country];
					$tribute_gift_message .= esc_html( $honoree_country_name ) . "<br/><br/>";
				}
			}
			echo "<tr>";
			echo "<th>" . esc_html__( 'Tribute Gift', 'seamless-donations' ) . "</th>";
			echo "<td>" . $tribute_gift_message . "</td></tr>\n";

			$payment_method = get_post_meta( $donation_id, '_dgx_donate_payment_method', true );
			echo "<tr><th>" . esc_html__( 'Payment Method', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $payment_method ) . "</td></tr>\n";

			$repeating = get_post_meta( $donation_id, '_dgx_donate_repeating', true );
			$is_repeating_donation = ! empty( $repeating );
			if ( $is_repeating_donation ) {
				$repeatingText = __( 'Yes', 'seamless-donations' );
			} else {
				$repeatingText = __( 'No', 'seamless-donations' );
			}
			echo "<tr><th>" . esc_html__( 'Repeating', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $repeatingText ) . "</td></tr>\n";

			$session_id = get_post_meta( $donation_id, '_dgx_donate_session_id', true );
			echo "<tr><th>" . esc_html__( 'Session ID', 'seamless-donations' ) . "</th>";
			echo "<td>" . esc_html( $session_id ) . "</td></tr>\n";

			$transaction_id = get_post_meta( $donation_id, '_dgx_donate_transaction_id', true );
			echo "<tr><th>" . esc_html__( 'Transaction ID', 'seamless-donations' ) ."</th>";
			echo "<td>" . esc_html( $transaction_id ) . "</td></tr>\n";

			echo "</tbody></table>\n";

			if ( $is_repeating_donation ) {
				// Display links to related (same session ID) donations
				$related_donation_ids = get_donations_by_meta( '_dgx_donate_session_id', $session_id, -1 );

				// Unset this donation if present (it probably will be)
				if ( ( $index = array_search( $donation_id, $related_donation_ids ) ) !== false ) {
					unset( $related_donation_ids[$index] );
				}

				echo "<h3>" . esc_html__( 'Related Donations', 'seamless-donations' ) . "</h3>\n";
				echo "<p class='description'>";
				echo esc_html__( 'For repeating donations, displays a list of other donations in the series (subscription)', 'seamless-donations' );
				echo "</p>\n";
				// Show the array
				echo "<table class='widefat'><tbody>\n";
				if ( count( $related_donation_ids ) ) {
					echo "<tr>";
					echo "<th>" . esc_html__( 'Date', 'seamless-donations' ). "</th>";
					echo "<th>" . esc_html__( 'Transaction ID', 'seamless-donations' ) . "</th></tr>";
					foreach ( (array) $related_donation_ids as $related_donation_id ) {
						$year = get_post_meta( $related_donation_id, '_dgx_donate_year', true );
						$month = get_post_meta( $related_donation_id, '_dgx_donate_month', true );
						$day = get_post_meta( $related_donation_id, '_dgx_donate_day', true );
						$time = get_post_meta( $related_donation_id, '_dgx_donate_time', true );
						$donation_date = $month . "/" . $day . "/" . $year;

						$transaction_id = get_post_meta( $related_donation_id, '_dgx_donate_transaction_id', true );

						$donation_detail = dgx_donate_get_donation_detail_link( $related_donation_id );
						echo "<tr>";
						echo "<td><a href='" . esc_url( $donation_detail ) . "'>" . esc_html( $donation_date . " " . $time ) . "</a></td>";
						echo "<td>" . esc_html( $transaction_id ) . "</td></tr>\n";
					}
				} else {
					echo "<tr>";
					echo "<th>" . esc_html__( 'No related donations found', 'seamless-donations' ) . "</th>";
					echo "</tr>\n";
				}
				echo "</tbody></table>\n";
			}

			do_action('dgx_donate_donation_detail_right', $donation_id);

			do_action('dgx_donate_admin_footer');

			echo "</div> <!-- col-wrap -->\n";
			echo "</div> <!-- col-right -->\n";

			echo "<div id=\"col-left\">\n";
			echo "<div class=\"col-wrap\">\n";

			Dgx_Donate_Admin_Donor_Detail_View::echo_donor_information( $donation_id );

			echo "<h3>" . esc_html__( 'Delete this Donation', 'seamless-donations' ) . "</h3>";
			echo "<p>" . esc_html__( 'Click the following button to delete this donation.  This will also remove this donation from all reports.  This operation cannot be undone.', 'seamless-donations' ) . "</p>";

			if ( $is_repeating_donation ) {
				echo "<p><strong>" . esc_html__( 'This is a repeating donation (subscription).  Deleting this donation does NOT end the subscription.  The donor will need to log into PayPal to end the subscription.', 'seamless-donations') . "</strong></p>";
			}

			echo "<form method='POST' action=''>\n";
			$nonce = wp_create_nonce( 'dgx_donate_donation_detail_nonce' );
			echo "<input type='hidden' name='dgx_donate_donation_detail_nonce' value='" . esc_attr( $nonce ) . "' />\n";
			echo "<input type='hidden' name='delete_donation' value='true' />";
			echo "<p><input class='button' type='submit' value='" . esc_attr__( 'Delete Donation', 'seamless-donations' ) ."'";
			echo " onclick=\"return confirm('" . esc_attr( 'Are you sure you want to delete this donation?', 'seamless-donations' ) . "');\"></p>\n";
			echo "</form>";

			do_action( 'dgx_donate_donation_detail_left', $donation_id );

			echo "</div> <!-- col-wrap -->\n";
			echo "</div> <!-- col-left -->\n";
			echo "</div> <!-- col-container -->\n";
		}

		echo "</div> <!-- wrap -->\n";

	}
}