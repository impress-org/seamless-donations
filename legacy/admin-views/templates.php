<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */

class Dgx_Donate_Admin_Templates_View {
	function __construct() {
		add_action( 'dgx_donate_menu', array( $this, 'menu_item' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function menu_item() {
		add_submenu_page(
			'dgx_donate_menu_page',
			__( 'Thank You Emails', 'seamless-donations' ),
			__( 'Thank You Emails', 'seamless-donations' ),
			'manage_options',
			'dgx_donate_template_page',
			array( $this, 'menu_page' )
		);
	}

	function menu_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
		}

		// Data driven ftw!
		$template_elements = array(
			'fromname' => array(
				'option' => 'dgx_donate_email_name',
				'label' => __( 'From / Reply-To Name', 'seamless-donations' ),
				'description' => __( 'The name the thank you email should appear to come from (e.g. your organization name or your name).', 'seamless-donations' ),
				'type' => 'text',
				'cols' => 40
			),
			'frommail' => array(
				'option' => 'dgx_donate_email_reply',
				'label' => __( 'From / Reply-To Email Address', 'seamless-donations' ),
				'description' => __( 'The email address the thank you email should appear to come from.', 'seamless-donations' ),
				'type' => 'text',
				'cols' => 40
			),
			'subject' => array(
				'option' => 'dgx_donate_email_subj',
				'label' => __( 'Subject', 'seamless-donations' ),
				'description' => __( 'The subject of the email (e.g. Thank You for Your Donation).', 'seamless-donations' ),
				'type' => 'text',
				'cols' => 40
			),
			'bodytext' => array(
				'option' => 'dgx_donate_email_body',
				'label' => __( 'Body', 'seamless-donations' ),
				'description' => __( 'The body of the email message to all donors.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			),
			'recurringtext' => array(
				'option' => 'dgx_donate_email_recur',
				'label' => __( 'Recurring Donations', 'seamless-donations' ),
				'description' => __( 'This message will be included when the donor elects to make their donation recurring.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			),
			'designatedtext' => array(
				'option' => 'dgx_donate_email_desig',
				'label' => __( 'Designated Fund', 'seamless-donations' ),
				'description' => __( 'This message will be included when the donor designates their donation to a specific fund.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			),
			'anonymoustext' => array(
				'option' => 'dgx_donate_email_anon',
				'label' => __( 'Anonymous Donations', 'seamless-donations' ),
				'description' => __( 'This message will be included when the donor requests their donation get kept anonymous.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			),
			'mailinglistjointext' => array(
				'option' => 'dgx_donate_email_list',
				'label' => __( 'Mailing List Join', 'seamless-donations' ),
				'description' => __( 'This message will be included when the donor elects to join the mailing list.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			),
			'employertext' => array(
				'option' => 'dgx_donate_email_empl',
				'label' => __( 'Employer Match', 'seamless-donations' ),
				'description' => __( 'This message will be included when the donor selects the employer match.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			),
			'tributetext' => array(
				'option' => 'dgx_donate_email_trib',
				'label' => __( 'Tribute Gift', 'seamless-donations' ),
				'description' => __( 'This message will be included when the donor elects to make their donation a tribute gift.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			),
			'closingtext' => array(
				'option' => 'dgx_donate_email_close',
				'label' => __( 'Closing', 'seamless-donations' ),
				'description' => __( 'The closing text of the email message to all donors.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			),
			'signature' => array(
				'option' => 'dgx_donate_email_sig',
				'label' => __( 'Signature', 'seamless-donations' ),
				'description' => __( 'The signature at the end of the email message to all donors.', 'seamless-donations' ),
				'type' => 'textarea',
				'cols' => 40,
				'rows' => 3
			)
		);

		// If we have form arguments, we must validate the nonce
		if ( count( $_POST ) ) {
			$nonce = $_POST['dgx_donate_template_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'dgx_donate_template_nonce' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
			}

			// If provided, get the form arguments and save them into options
			foreach ( (array) $template_elements as $key => $element ) {
				if ( isset( $_POST[$key] ) ) {
					$value = strip_tags( $_POST[$key] );

					if ( 'fromname' == $key ) {
						$value = preg_replace( "/[^a-zA-Z ]+/", "", $value ); // letters and spaces only please
					}

					if ( 'frommail' == $key ) {
						if ( ! is_email( $value ) ) {
							$value = get_option( 'admin_email' );
						}
					}

					update_option( $element['option'], $value );
					$message = __( 'Templates updated.', 'seamless-donations' );
				}
			}

			// Or, if they asked for a test email, send it
			$test_mail = isset( $_POST['testmail'] ) ? $_POST['testmail'] : '';
			$test_mail = strip_tags( $test_mail );
			if ( ! empty( $test_mail ) ) {
				dgx_donate_send_thank_you_email( 0, $test_mail );
				$message = __( 'Test email sent.', 'seamless-donations' );
			}
		}

		do_action('dgx_donate_email_template_page_load');

		// Otherwise, proceed
		echo "<div class='wrap'>\n";
		echo "<div id='icon-edit-pages' class='icon32'></div>\n";
		echo "<h2>" . esc_html__( 'Thank You Emails', 'seamless-donations' ) . "</h2>\n";

		// Display any message
		if ( ! empty( $message ) ) {
			echo "<div id='message' class='updated below-h2'>\n";
			echo "<p>" . esc_html( $message ) . "</p>\n";
			echo "</div>\n";
		}

		// Read in each option from the database
		foreach ( $template_elements as &$element ) {
			$element['value'] = stripslashes( get_option( $element['option'] ) );
		}
		unset( $element ); // break the lingering reference

		$nonce = wp_create_nonce('dgx_donate_template_nonce');

		echo "<div id='col-container'>\n";
		echo "<div id='col-right'>\n";
		echo "<div class='col-wrap'>\n";

		echo "<h3>" . esc_html__( 'Email Template', 'seamless-donations' ) . "</h3>\n";
		echo "<p>" . esc_html__( 'The template on this page is used to generate thank you emails for each donation.'. 'seamless-donations' ) . ' ';
		echo esc_html__( 'You can include placeholders such as [firstname] [lastname] [fund] and/or [amount].', 'seamless-donations' ) . ' ';
		echo esc_html__( 'These placeholders will automatically be filled in with the donor and donation details.', 'seamless-donations' ) . ' ';
		echo "</p>\n";

		// Emit the form
		echo "<form method='POST' action=''>\n";
		echo "<input type='hidden' name='dgx_donate_template_nonce' value='" . esc_attr( $nonce ) . "' />\n";

		foreach ( (array) $template_elements as $key => $element ) {
			echo "<div class='form-field'>\n";
			echo "<p><strong>" . esc_html( $element['label'] ) . "</strong> - ";
			echo "<span class='description'>" . esc_html( $element['description'] ) . "</span></p>";
			if ( 'text' == $element['type'] ) {
				echo "<input type='text' name='" . esc_attr( $key ) . "' size='" . esc_attr( $element['cols'] ) . "' value = '" . esc_attr( $element['value'] ) . "' />\n";
			} else {
				echo "<textarea style='resize: none;' name='" . esc_attr( $key ) . "' rows='" . esc_attr( $element['rows'] ) . "' cols='" . esc_attr( $element['cols'] ) . "'>" . esc_textarea( $element['value'] ) . "</textarea>\n";
			}
			echo "<br/><br/>";
			echo "</div>\n";
		}

		echo "<p><input class='button' type='submit' value='" . esc_attr__( 'Save Changes', 'seamless-donations' ) ."' name='submit'></p>\n";
		echo "</form>";

		do_action( 'dgx_donate_email_template_page_right' );
		do_action( 'dgx_donate_admin_footer' );

		echo "</div>\n"; // col-wrap
		echo "</div>\n"; // col-right

		echo "<div id='col-left'>\n";
		echo "<div class='col-wrap'>\n";

		echo "<h3>" . esc_html__( 'Send a Test Email', 'seamless-donations' ) . "</h3>\n";
		echo "<p>" . esc_html__( 'Enter an email address (e.g. your own) to have a test email sent using the template.', 'seamless-donations' ) . "</p>\n";

		echo "<form method='POST' action=''>\n";
		echo "<input type='hidden' name='dgx_donate_template_nonce' value='" . esc_attr( $nonce ) . "' />\n";

		echo "<div class='form-field'>\n";
		echo "<label for='testmail'>" . esc_html__( 'Email Address', 'seamless-donations' ) . "</label>\n";
		echo "<input type='text' name='testmail' size='40' />\n";
		echo "<p class='description'>" . esc_html__( 'The email address to receive the test message.', 'seamless-donations' ) . "</p>\n";
		echo "</div>\n";

		echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Send Test Email', 'seamless-donations' ) ."' name='submit'></p>\n";
		echo "</form>";

		do_action('dgx_donate_email_template_page_left');

		echo "</div>\n"; // col wrap
		echo "</div>\n"; // col left
		echo "</div>\n"; // col container

		echo "</div>\n"; // wrap
	}

	function admin_enqueue_scripts() {
		wp_enqueue_script( 'jquery' );

		$script_url = plugins_url( '../js/jquery.autosize.js', __FILE__ );
		wp_enqueue_script( 'dgx_donate_jquery_autosize', $script_url, array( 'jquery' ) );

		$script_url = plugins_url( '../js/autosize-loader.js', __FILE__ );
		wp_enqueue_script( 'dgx_donate_autosize_loader', $script_url, array( 'jquery', 'dgx_donate_jquery_autosize' ) );
	}
}

$dgx_donate_admin_templates_view = new Dgx_Donate_Admin_Templates_View();
