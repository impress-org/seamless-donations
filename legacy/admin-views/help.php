<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */

class Dgx_Donate_Admin_Help_View {
	function __construct() {
		add_action( 'dgx_donate_menu', array( $this, 'menu_item' ), 17 );
	}

	function menu_item() {
		add_submenu_page(
			'dgx_donate_menu_page',
			__( 'Help/FAQ', 'seamless-donations' ),
			__( 'Help/FAQ', 'seamless-donations' ),
			'manage_options',
			'dgx_donate_help_page',
			array( $this, 'menu_page' )
		);
	}

	function menu_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
		}

		$faqs = array(
			array(
				'question' => __( 'What is the shortcode I need to insert on a page to see the donation form?', 'seamless-donations' ),
				'answer' => "[dgx-donate]"
			),
			array(
				'question' => __( 'Do I need to set my IPN notification URL at PayPal?', 'seamless-donations' ),
				'answer' => __( 'In general it is not needed, but some users have needed to set it to get notifications working correctly.', 'seamless-donations' )
			),
			array(
				'question' => __( 'What URL should I use for the IPN notification URL at PayPal?', 'seamless-donations' ),
				'answer' => __( 'The correct URL to use is displayed on your settings page under Payment Gateways.', 'seamless-donations' )
			),
			array(
				'question' => __( 'How does my donor end a repeating donation?', 'seamless-donations' ),
				'answer' => __( "The donor can log into their PayPal account, go to their profile under 'My Account,' then 'Preapproved Payments', and end the repeating donation there.", 'seamless-donations' )
			),
			array(
				'question' => __( 'I have a question not answered here.  Where can I find the plugin\'s support forum?', 'seamless-donations' ),
				'answer' => __( 'You can find the support forum at', 'seamless-donations' ) . " <a href='http://wordpress.org/support/plugin/seamless-donations'>http://wordpress.org/support/plugin/seamless-donations</a>"
			),
		);

		echo "<div class='wrap'>";
		echo "<div id='icon-edit-pages' class='icon32'></div>";
		echo "<h2>" . __( 'Help/FAQ', 'seamless-donations' ) . "</h2>";

		foreach( $faqs as $faq ) {
			echo "<h3>" . $faq['question'] . "</h3>";
			echo "<p>" . $faq['answer'] . "</p>";
		}

		do_action( 'dgx_donate_admin_footer' );

		echo "</div>";
	}
}

$dgx_donate_admin_help_view = new Dgx_Donate_Admin_Help_View();
