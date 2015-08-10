<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */

class Dgx_Donate_Admin_Settings_View {
	function __construct() {
		add_action( 'dgx_donate_menu', array( $this, 'menu_item' ), 13 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function menu_item() {
		add_submenu_page(
			'dgx_donate_menu_page',
			__( 'Settings', 'seamless-donations' ),
			__( 'Settings', 'seamless-donations' ),
			'manage_options',
			'dgx_donate_settings_page',
			array( $this, 'menu_page' )
		);
	}

	function menu_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
		}

		// If we have form arguments, we must validate the nonce
		if ( count( $_POST ) ) {
			$nonce = $_POST['dgx_donate_settings_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'dgx_donate_settings_nonce' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
			}
		}

		// Save our global settings
		$notification_emails = ( isset( $_POST['notifyemails'] ) ) ? $_POST['notifyemails'] : '';
		if ( ! empty( $notification_emails ) ) {
			update_option( 'dgx_donate_notify_emails', $notification_emails );
			$message = __( 'Settings updated.', 'seamless-donations' );
		}

		// Where to load the scripts
		$scripts_in_footer = ( isset( $_POST['scripts_in_footer'] ) ) ? $_POST['scripts_in_footer'] : '';
		if ( ! empty( $scripts_in_footer ) ) {
			if ( "true" == $scripts_in_footer ) {
				update_option( 'dgx_donate_scripts_in_footer', 'true' );
			} else {
				update_option( 'dgx_donate_scripts_in_footer', 'false' );
			}
			$message = __( 'Settings updated.', 'seamless-donations' );
		}

		// Save each payment gateway's settings
		do_action( 'dgx_donate_save_settings_forms' );

		// Set up a nonce
		$nonce = wp_create_nonce( 'dgx_donate_settings_nonce' );

		echo "<div class='wrap'>\n";
		echo "<div id='icon-edit-pages' class='icon32'></div>\n";
		echo "<h2>" . esc_html__( 'Settings', 'seamless-donations' ) . "</h2>\n";

		// Display any message
		if ( ! empty( $message ) ) {
			echo "<div id='message' class='updated below-h2'>\n";
			echo "<p>" . esc_html( $message ) . "</p>\n";
			echo "</div>\n";
		}

		echo "<div id='col-container'>\n";
		echo "<div id='col-right'>\n";
		echo "<div class='col-wrap'>\n";

		// Notification Emails
		echo "<h3>" . esc_html__( 'Notification Emails', 'seamless-donations' ) . "</h3>\n";
		echo "<p>" . esc_html__( 'Enter one or more emails that should be notified when a new donation arrives.  You can separate multiple email addresses with commas.', 'seamless-donations' ). "</p>";

		$notify_emails = get_option('dgx_donate_notify_emails');
		echo "<form method='POST' action=''>\n";
		echo "<input type='hidden' name='dgx_donate_settings_nonce' value='" . esc_attr( $nonce ) . "' />\n";

		echo "<div class='form-field'>\n";
		echo "<label for='notifyemails'>" . esc_html__( 'Notification Email Address(es)', 'seamless-donations' ) . "</label><br/>\n";
		echo "<input type='text' name='notifyemails' value='" . esc_attr( $notify_emails ) ."' />\n";
		echo "<p class='description'>" . esc_html__( 'Email address(es) that should be notified (e.g. administrators) of new donations.', 'seamless-donations' ) . "</p>\n";
		echo "</div>\n";

		echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Update', 'seamless-donations' ) . "' name='submit'></p>\n";
		echo "</form>";
		echo "<br/>";

		// Payment gateways
		echo "<h3>" . esc_html( 'Payment Gateways', 'seamless-donations' ) . "</h3>\n";
		if ( has_action( 'dgx_donate_show_settings_forms' ) ) {
			echo "<form method='POST' action=''>\n";
			echo "<input type='hidden' name='dgx_donate_settings_nonce' value='" . esc_attr( $nonce ) . "' />\n";

			do_action( 'dgx_donate_show_settings_forms' );
			echo "<p><input id='submit' class='button' type='submit' value='" . esc_html__( 'Update', 'seamless-donations' ) . "' name='submit'></p>\n";
			echo "</form>";
		} else {
			echo "<p>" . esc_html__( 'Error: No payment gateways found', 'seamless-donations' ) . "</p>";
		}

		do_action( 'dgx_donate_settings_page_right' );
		do_action( 'dgx_donate_admin_footer' );

		echo "</div>\n";
		echo "</div>\n";

		echo "<div id='col-left'>\n";
		echo "<div class='col-wrap'>\n";

		// Load Scripts Where?
		echo "<h3>" . esc_html__( 'Scripts', 'seamless-donations' ) . "</h3>";
		echo "<p>" . esc_html__( 'Whether to load scripts in the header or footer.', 'seamless-donations' ) . "</p>";
		echo "<form method='POST' action=''>\n";
		echo "<input type='hidden' name='dgx_donate_settings_nonce' value='" . esc_attr( $nonce ) . "' />\n";

		$scripts_in_footer = get_option( 'dgx_donate_scripts_in_footer' );
		$true_checked = ( 'true' == $scripts_in_footer ) ? "checked" : '';
		$false_checked = ( 'false' == $scripts_in_footer) ? "checked" : '';

		echo "<p><input type='radio' name='scripts_in_footer' value='false' $false_checked />" . esc_html__( 'Load scripts in the header (default)', 'seamless-donations' ) . "</p>";
		echo "<p><input type='radio' name='scripts_in_footer' value='true' $true_checked />" . esc_html__( 'Load scripts in the footer', 'seamless-donations' ) . "</p>";

		echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Update', 'seamless-donations' ) . "' name='submit'/></p>\n";
		echo "</form>";

		do_action( 'dgx_donate_settings_page_left' );

		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n";
		echo "</div>\n";
	}

	function admin_enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
		$script_url = plugins_url( '/legacy/js/geo-selects.js', __FILE__ );
		wp_enqueue_script( 'dgx_donate_geo_selects_script', $script_url, array( 'jquery' ) );
	}
}

$dgx_donate_admin_settings_view = new Dgx_Donate_Admin_Settings_View();
