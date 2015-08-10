<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */
/* Copyright 2015 David Gewirtz, based on code by Allen Snook */

class Dgx_Donate_Admin_Main_View {
	function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_item' ), 9 );
	}

	function menu_item() {
		add_menu_page(
			__( 'Seamless Donations', 'seamless-donations' ),
			__( 'Seamless Donations', 'seamless-donations' ),
			'manage_options',
			'dgx_donate_menu_page',
			array( $this, 'menu_page' )
		);
		do_action( 'dgx_donate_menu' );
	}

	function menu_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
		}

		$donor_id = isset( $_GET['donor'] ) ? $_GET['donor'] : '';
		$donation_id = isset( $_GET['donation'] ) ? $_GET['donation'] : '';

		if ( ! empty( $donor_id ) ) {
			Dgx_Donate_Admin_Donor_Detail_View::show( $donor_id );
		} else if ( ! empty( $donation_id ) ) {
			Dgx_Donate_Admin_Donation_Detail_View::show( $donation_id );
		} else {
			self::show();
		}
	}

	static function show() {
		echo "<div class='wrap'>\n";
		echo "<div id='icon-edit-pages' class='icon32'></div>\n";
		echo "<h2>" . esc_html__( 'Seamless Donations for WordPress', 'seamless-donations' ) . "</h2>\n";

		// Quick Links
		$quick_links = array(
			array(
				'title' => __( 'Donations', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_donation_report_page"
			),
			array(
				'title' => __( 'Donors', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_donor_report_page"
			),
			array(
				'title' => __( 'Funds', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_funds_page"
			),
			array(
				'title' => __( 'Thank You Emails', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_template_page"
			),
			array(
				'title' => __( 'Thank You Page', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_thank_you_page"
			),
			array(
				'title' => __( 'Form Options', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_form_options_page"
			),
			array(
				'title' => __( 'Settings', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_settings_page"
			),
			array(
				'title' => __( 'Log', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_debug_log_page"
			),
			array(
				'title' => __( 'Help/FAQ', 'seamless-donations' ),
				'url' => get_admin_url() . "admin.php?page=dgx_donate_help_page"
			),
		);

		echo "<p><strong>" . __( 'Quick Links', 'seamless-donations' ) . ": ";

		$count = 0;
		foreach( (array) $quick_links as $quick_link ) {
			echo "<a href='" . esc_url( $quick_link['url'] ) . "'><strong>" . esc_html( $quick_link['title'] ) . "</strong></a>";
			$count++;
			if ( $count != count( $quick_links ) ) {
				echo " | ";
			}
		}
		echo "</p>";

		// Seamless Donations 4.0 upgrade form
		seamless_donations_sd40_upgrade_form();

		// Recent Donations
		echo "<div id='col-container'>\n";
		echo "<div id='col-right'>\n";
		echo "<div class='col-wrap'>\n";

		echo "<h3>" . esc_html__( 'Recent Donations', 'seamless-donations' ) . "</h3>\n";

		$args = array(
			'numberposts'     => '10',
			'post_type'       => 'dgx-donation'
		);

		$my_donations = get_posts( $args );

		if ( count( $my_donations ) ) {
			echo "<table class='widefat'><tbody>\n";
			echo "<tr>";
			echo "<th>" . esc_html__( 'Date', 'seamless-donations' ) . "</th>";
			echo "<th>" . esc_html__( 'Donor', 'seamless-donations' ) . "</th>";
			echo "<th>" . esc_html__( 'Amount', 'seamless-donations' ) . "</th>";
			echo "</tr>\n";

			foreach ( (array) $my_donations as $my_donation ) {
				$donation_id = $my_donation->ID;

				$year = get_post_meta( $donation_id, '_dgx_donate_year', true );
				$month = get_post_meta( $donation_id, '_dgx_donate_month', true );
				$day = get_post_meta( $donation_id, '_dgx_donate_day', true );
				$time = get_post_meta( $donation_id, '_dgx_donate_time', true );
				$donation_date = $month . "/" . $day . "/" . $year;

				$first_name = get_post_meta( $donation_id, '_dgx_donate_donor_first_name', true );
				$last_name = get_post_meta( $donation_id, '_dgx_donate_donor_last_name', true );
				$donor_email = get_post_meta( $donation_id, '_dgx_donate_donor_email', true );
				$donor_detail = dgx_donate_get_donor_detail_link( $donor_email );

				$amount = get_post_meta( $donation_id, '_dgx_donate_amount', true );
				$currency_code = dgx_donate_get_donation_currency_code( $donation_id );
				$formatted_amount = dgx_donate_get_escaped_formatted_amount( $amount, 2, $currency_code );

				$donation_detail = dgx_donate_get_donation_detail_link( $donation_id );
				echo "<tr>";
				echo "<td>";
				echo "<a href='" . esc_url( $donation_detail ) . "'>";
				echo esc_html( $donation_date . ' ' . $time) . "</a>";
				echo "</td>";
				echo "<td>";
				echo "<a href='" . esc_url( $donor_detail ) . "'>";
				echo esc_html( $first_name . ' ' . $last_name ) . "</a>";
				echo "</td>";
				echo "<td>" . $formatted_amount . "</td>";
				echo "</tr>\n";
		}

			echo "</tbody></table>\n";
		} else {
			echo "<p>" . esc_html__( 'No donations found.', 'seamless-donations' ) . "</p>\n";
		}

		do_action( 'dgx_donate_main_page_right' );
		do_action( 'dgx_donate_admin_footer' );

		echo "</div> <!-- col-wrap -->\n";
		echo "</div> <!-- col-right -->\n";


		echo "<div id='col-left'>\n";
		echo "<div class='col-wrap'>\n";

		echo "<h3>" . esc_html__( "Latest News", 'seamless-donations' ) . "</h3>";


		// regular news feed
		echo "<div class='rss-widget'>";
		wp_widget_rss_output( array(
			'url' => 'http://zatzlabs.com/feed/',
			'title' => __( "What's up with Seamless Donations", 'seamless-donations' ),
			'items' => 3,
			'show_summary' => 1,
			'show_author' => 0,
			'show_date' => 1
		) );
		echo "</div>";

		do_action( 'dgx_donate_main_page_left' );

		echo "</div> <!-- col-wrap -->\n";
		echo "</div> <!-- col-left -->\n";
		echo "</div> <!-- col-container -->\n";

		echo "</div> <!-- wrap -->\n";
	}
}

$dgx_donate_admin_main_view = new Dgx_Donate_Admin_Main_View();
