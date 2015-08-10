<?php

/* Copyright 2014 Allen Snook (email: allendav@allendav.com) */

class Dgx_Donate_Admin_Form_Options_View {
	private $form_options;

	function __construct() {
		add_action( 'dgx_donate_menu', array( $this, 'menu_item' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'init', array( $this, 'init_defaults' ) );

		$this->form_options = array(
			array(
				'prompt' => __( 'Designated Funds Checkbox and Section', 'seamless-donations' ),
				'key' => 'dgx_donate_show_designated_funds_section',
				'default' => 'true',
				'show_require_option' => false
			),
			array(
				'prompt' => __( 'Repeating Donation Checkbox', 'seamless-donations' ),
				'key' => 'dgx_donate_show_repeating_option',
				'default' => 'true',
				'show_require_option' => false
			),
			array(
				'prompt' => __( 'Tribute Gift Checkbox and Section', 'seamless-donations' ),
				'key' => 'dgx_donate_show_tribute_section',
				'default' => 'true',
				'show_require_option' => false
			),
			array(
				'prompt' => __( 'Employer Match Section', 'seamless-donations' ),
				'key' => 'dgx_donate_show_employer_section', /* just the match option checkbox */
				'default' => 'true',
				'show_require_option' => false
			),
			array(
				'prompt' => __( 'Donor Telephone Field', 'seamless-donations' ),
				'key' => 'dgx_donate_show_donor_telephone_field',
				'default' => 'true',
				'show_require_option' => true
			),
			array(
				'prompt' => __( 'Donor Employer Field', 'seamless-donations' ),
				'key' => 'dgx_donate_show_donor_employer_field',
				'default' => 'true',
				'show_require_option' => true
			),
			array(
				'prompt' => __( 'Donor Occupation Field', 'seamless-donations' ),
				'key' => 'dgx_donate_show_donor_occupation_field',
				'default' => 'false',
				'show_require_option' => true
			),
			array(
				'prompt' => __( 'Mailing List Checkbox', 'seamless-donations' ),
				'key' => 'dgx_donate_show_mailing_list_option',
				'default' => 'true',
				'show_require_option' => false
			),
			array(
				'prompt' => __( 'Anonymous Donation Checkbox', 'seamless-donations' ),
				'key' => 'dgx_donate_show_anonymous_option',
				'default' => 'true',
				'show_require_option' => false
			),
			array(
				'prompt' => __( 'Donor Address Section', 'seamless-donations' ),
				'key' => 'dgx_donate_show_donor_address_fields',
				'default' => 'true',
				'show_require_option' => false
			)
		);
	}

	function menu_item() {
		add_submenu_page(
			'dgx_donate_menu_page',
			__( 'Form Options', 'seamless-donations' ),
			__( 'Form Options', 'seamless-donations' ),
			'manage_options',
			'dgx_donate_form_options_page',
			array( $this, 'menu_page' )
		);
	}

	function init_defaults() {
		foreach ( (array) $this->form_options as $form_option ) {
			$option_value = get_option( $form_option['key'] );
			if ( empty( $option_value ) ) {
				update_option( $form_option['key'], $form_option['default'] );
			}
		}
	}

	function menu_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
		}

		$form_option_whitelist = array( 'false', 'true', 'required' );

		// If we have form arguments, we must validate the nonce
		if ( count( $_POST ) ) {
			$nonce = $_POST['dgx_donate_form_options_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'dgx_donate_form_options_nonce' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
			}

			// Save options table settings (if present)
			foreach ( (array) $this->form_options as $form_option ) {
				$key = $form_option['key'];
				$new_option_value = ( isset( $_POST[$key] ) ) ? $_POST[$key] : '';

				if ( ! empty( $new_option_value ) ) {
					if ( in_array( $new_option_value, $form_option_whitelist ) ) {
						update_option( $key, $new_option_value );
						$message = __( 'Settings updated.', 'seamless-donations' );
					}
				}
			}

			// Save giving level selections (if present)
			$giving_levels = ( isset( $_POST['dgx_donate_giving_levels'] ) ) ? $_POST['dgx_donate_giving_levels'] : '';
			if ( ! empty( $giving_levels ) ) {
				dgx_donate_save_giving_levels_settings();
				$message = __( 'Settings updated.', 'seamless-donations' );
			}

			// Save currency (if present)
			$currency = ( isset( $_POST['dgx_donate_currency'] ) ) ? $_POST['dgx_donate_currency'] : '';
			if ( ! empty( $currency ) ) {
				update_option( 'dgx_donate_currency', $currency );
				$message = __( 'Settings updated.', 'seamless-donations' );
			}

			// Save default country (if present)
			$default_country = ( isset( $_POST['dgx_donate_default_country'] ) ) ? $_POST['dgx_donate_default_country'] : '';
			if ( ! empty( $default_country ) ) {
				update_option( 'dgx_donate_default_country', $default_country );
				$message = __( 'Settings updated.', 'seamless-donations' );
			}

			// Save default state (if present)
			$default_state = ( isset( $_POST['dgx_donate_default_state'] ) ) ? $_POST['dgx_donate_default_state'] : '';
			if ( ! empty( $default_state ) ) {
				update_option( 'dgx_donate_default_state', $default_state );
				$message = __( 'Settings updated.', 'seamless-donations' );
			}

			// Save default province (if present)
			$default_province = ( isset( $_POST['dgx_donate_default_province'] ) ) ? $_POST['dgx_donate_default_province'] : '';
			if ( ! empty( $default_province ) ) {
				update_option( 'dgx_donate_default_province', $default_province );
				$message = __( 'Settings updated.', 'seamless-donations' );
			}
		}

		// Set up a nonce
		$nonce = wp_create_nonce( 'dgx_donate_form_options_nonce' );

		echo "<div class='wrap'>\n";
		echo "<div id='icon-edit-pages' class='icon32'></div>";
		echo "<h2>" . esc_html__( 'Form Options', 'seamless-donations' ) . "</h2>";

		// Display any message
		if ( ! empty( $message ) ) {
			echo "<div id='message' class='updated below-h2'>";
			echo "<p>" . esc_html( $message ) . "</p>";
			echo "</div>";
		}

		echo "<div id='col-container'>";
		echo "<div id='col-right'>";
		echo "<div class='col-wrap'>";

		// Fields and Sections Table
		echo "<h3>" . esc_html__( 'Form Fields and Sections', 'seamless-donations' ) . "</h3>";
		echo "<p>" . esc_html__( 'Choose which form fields and sections you would like to show or require.', 'seamless-donations' ). "</p>";

		echo "<form method='POST' action=''>";
		echo "<input type='hidden' name='dgx_donate_form_options_nonce' value='" . esc_attr( $nonce ) . "' />";

		echo "<table class='widefat'>";
		echo "<tbody>";
		echo "<tr>";
		echo "<th>" . esc_html__( 'Field/Section', 'seamless-donations' ) . "</th>";
		echo "<th style='text-align: center;'>" . esc_html__( "Don't Show", 'seamless-donations' ) . "</th>";
		echo "<th style='text-align: center;'>" . esc_html__( 'Show', 'seamless-donations' ) . "</th>";
		echo "<th style='text-align: center;'>" . esc_html__( 'Require', 'seamless-donations' ) . "</th>";
		echo "</tr>";

		foreach ( (array) $this->form_options as $form_option ) {
			echo "<tr>";
			echo "<td>" . esc_html( $form_option['prompt'] ) . "</td>";
			foreach( $form_option_whitelist as $setting ) {
				echo "<td style='text-align: center;'>";
				$key = $form_option['key'];
				$current_setting = get_option( $key );
				if ( ( 'required' !== $setting ) || $form_option['show_require_option'] ) {
					echo "<input type='radio' name='" . esc_attr( $key ) . "' value='" . esc_attr( $setting ) . "' " . checked( $current_setting, $setting, false ) . " />";
				}
				echo "</td>";
			}
			echo "</tr>";
		}

		echo "</tbody>";
		echo "</table>";

		echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Update', 'seamless-donations' ) . "' name='submit'></p>\n";
		echo "</form>";
		echo "<br/>";

		do_action( 'dgx_donate_admin_footer' );

		echo "</div>\n";
		echo "</div>\n";

		echo "<div id='col-left'>\n";
		echo "<div class='col-wrap'>\n";

		// Giving Levels
		echo "<h3>" . esc_html__( 'Giving Levels', 'seamless-donations' ) . "</h3>";
		echo "<p>" . esc_html__( 'Select one or more suggested giving levels for your donors to choose from.', 'seamless-donations' ) . "</p>";
		echo "<form method='POST' action=''>\n";
		echo "<input type='hidden' name='dgx_donate_form_options_nonce' value='" . esc_attr( $nonce ) . "' />\n";
		echo "<input type='hidden' name='dgx_donate_giving_levels' value='1' />";
		$giving_levels = dgx_donate_get_giving_levels();
		foreach ( $giving_levels as $giving_level ) {
			$key = dgx_donate_get_giving_level_key( $giving_level );
			echo "<p><input type='checkbox' name='" . esc_attr( $key ) . "' value='yes' ";
			checked( dgx_donate_is_giving_level_enabled( $giving_level ) );
			echo " />" . esc_html( $giving_level ) . "</p>";
		}

		echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Update', 'seamless-donations' ) . "' name='submit' /></p>\n";
		echo "</form>";
		echo "<br/>";

		// Currency
		echo "<h3>" . esc_html__( 'Currency', 'seamless-donations' ) . "</h3>";
		echo "<p>" . esc_html__( "Select the currency you'd like to receive donations in.", 'seamless-donations' ) . "</p>";
		echo "<form method='POST' action=''>\n";
		echo "<input type='hidden' name='dgx_donate_form_options_nonce' value='" . esc_attr( $nonce ) . "' />\n";
		$currency = get_option( 'dgx_donate_currency' );
		echo "<p>";
		echo dgx_donate_get_currency_selector( 'dgx_donate_currency', $currency );
		echo "</p>";
		echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Update', 'seamless-donations' ) . "' name='submit' /></p>\n";
		echo "</form>";
		echo "<br/>";

		// Default country/state/province for donor
		// jQuery will take care of hiding / showing the state and province selector based on the country code
		echo "<h3>" . esc_html__( 'Default Country / State / Province', 'seamless-donations' ) . "</h3>";
		echo "<p>" . esc_html__( 'Select the default country / state / province for the donation form.', 'seamless-donations' ) . "</p>";

		echo "<div class='dgx_donate_geography_selects'>";
		echo "<form method='POST' action=''>\n";
		echo "<input type='hidden' name='dgx_donate_form_options_nonce' value='" . esc_attr( $nonce ) . "' />\n";

		$default_country = get_option( 'dgx_donate_default_country' );
		echo "<p>";
		echo dgx_donate_get_country_selector( 'dgx_donate_default_country', $default_country );
		echo "</p>";

		$default_state = get_option( 'dgx_donate_default_state' );
		echo "<p>";
		echo dgx_donate_get_state_selector( 'dgx_donate_default_state', $default_state );
		echo "</p>";

		$default_province = get_option( 'dgx_donate_default_province' );
		echo "<p>";
		echo dgx_donate_get_province_selector( 'dgx_donate_default_province', $default_province );
		echo "</p>";

		echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Update', 'seamless-donations' ) . "' name='submit' /></p>\n";
		echo "</form>";
		echo "</div>"; // dgx_donate_geography_selects
		echo "<br/>";

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

$dgx_donate_admin_form_options_view = new Dgx_Donate_Admin_Form_Options_View();
