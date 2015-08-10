<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */

class Dgx_Donate_Admin_Funds_View {
	function __construct() {
		add_action( 'dgx_donate_menu', array( $this, 'menu_item' ), 7 );
	}

	function menu_item() {
		add_submenu_page(
			'dgx_donate_menu_page',
			__( 'Funds', 'seamless-donations' ),
			__( 'Funds', 'seamless-donations' ),
			'manage_options',
			'dgx_donate_funds_page',
			array( $this, 'menu_page' )
		);
	}

	function menu_page() {
		// Validate user
		if ( ! current_user_can( 'manage_options' ) ) {
	      wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
	    }

		// Get form arguments
		$fund_to_add = isset( $_POST['addfund'] ) ? $_POST['addfund'] : '';
		$fund_to_add = strip_tags( $fund_to_add );
		$fund_to_add = htmlspecialchars( $fund_to_add );

		$edit_funds = isset( $_POST['edit_funds'] ) ? $_POST['edit_funds'] : '';

		// If we have form arguments, we must validate the nonce
		if ( count( $_POST ) > 0 ) {
			$nonce = $_POST['dgx_donate_fund_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'dgx_donate_fund_nonce' ) )
			{
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'seamless-donations' ) );
			}
		}

		echo "<div class='wrap'>";
		echo "<div id='icon-edit-pages' class='icon32'></div>";
		echo "<h2>" . esc_html__( 'Designated Funds', 'seamless-donations' ) . "</h2>";

		// Did they use the add form on the page?
		if ( ! empty( $fund_to_add ) ) {
			$message = __( "Fund added.", 'seamless-donations' );
			$show_in_list = "SHOW"; /* default for new funds */

			$fund_array = get_option( 'dgx_donate_designated_funds' );
			if ( empty( $fund_array ) ) {
				$fund_array = array();
				$fund_array[$fund_to_add] = $show_in_list;
			}
			else {
				$fund_array[$fund_to_add] = $show_in_list;
			}

			ksort( $fund_array );
			update_option( 'dgx_donate_designated_funds', $fund_array );
		}

		// Did they use the edit form on the page?
		if ( ! empty( $edit_funds ) ) {
			$fund_array = get_option( 'dgx_donate_designated_funds' );
			ksort( $fund_array );

			// update display status
			$fund_num = 0;
			foreach ( (array) $fund_array as $key => $value ) {
				$display_name = "display_" . $fund_num;

				if ( isset( $_POST[$display_name] ) ) {
					if ( strcasecmp( $fund_array[$key], "HIDE" ) == 0 ) {
						$fund_array[$key] = "SHOW";
						$message = __( "Fund(s) updated.", 'seamless-donations' );
					}
				}
				else {
					if ( strcasecmp( $fund_array[$key], "SHOW" ) == 0 ) {
						$fund_array[$key] = "HIDE";
						$message = __( "Fund(s) updated.", 'seamless-donations' );
					}
				}

				$fund_num = $fund_num + 1;
			}

			// any to delete?
			$fund_num = 0;
			$temp_array = $fund_array;
			foreach ( (array) $fund_array as $key => $value ) {
				$delete_name = "delete_" . $fund_num;

				if ( isset( $_POST[$delete_name] ) ) {
					unset( $temp_array[$key] );
					$message = __( "Fund(s) deleted.", 'seamless-donations' );
				}

				$fund_num = $fund_num + 1;
			}
			$fund_array = $temp_array;

			// save the result back
			update_option( 'dgx_donate_designated_funds', $fund_array );
		}

		// Display any message
		if ( ! empty( $message ) ) {
			echo "<div id='message' class='updated below-h2'>";
			echo "<p>" . esc_html( $message ) . "</p>";
			echo "</div>";
		}

		// Start the form
		$fund_array = get_option( 'dgx_donate_designated_funds' );
		if ( ! empty( $fund_array ) ) {
			ksort( $fund_array );
		}
		else {
			$fund_array = array();
		}

		// Display the designated funds
		$fund_nonce = wp_create_nonce( 'dgx_donate_fund_nonce' );

		echo "<div id='col-container'>";
		echo "<div id='col-right'>";
		echo "<div class='col-wrap'>";

		echo "<h3>" . esc_html__( "Designated Funds", 'seamless-donations' ) . "</h3>";

		if ( count( $fund_array ) > 0 ) {
			echo "<form method='POST' action=''>";
			echo "<input type='hidden' name='dgx_donate_fund_nonce' id='dgx_donate_fund_nonce' value='" . esc_attr( $fund_nonce ) . "' />";
			echo "<input type='hidden' name='edit_funds' value='1' />";
			echo "<table class='widefat'><tbody>";
			echo "<tr><th>" . esc_html__( 'Fund Name', 'seamless-donations' ) . "</th>";
			echo "<th class='dgxdonatecentered'>" . esc_html__( 'Display on Donation Form', 'seamless-donations' ) . "</th>";
			echo "<th class='dgxdonatecentered'>" . esc_html__( 'Delete', 'seamless-donations' ) . "</th></tr>";

			$fund_num = 0;

			foreach ( (array) $fund_array as $key => $value ) {
				$display_name = "display_" . $fund_num;
				$delete_name = "delete_" . $fund_num;
				echo "<tr>";
				$fund_name = stripslashes( $key );
				echo "<td>" . esc_html( $fund_name ) . "</td>";
				$checked = "";
				if ( strcasecmp( $value, "SHOW" ) == 0 ) {
					$checked = " checked ";
				}
				echo "<td class='dgxdonatecentered'><input type='checkbox' name='" . esc_attr( $display_name ) . "' value='1' $checked /></td>";
				echo "<td class='dgxdonatecentered'><input type='checkbox' name='" . esc_attr( $delete_name ) . "' value='1' /></td>";
				echo "</tr>";
				$fund_num = $fund_num + 1;
			}

			echo "</tbody></table>";

			echo "<p class='description'>";
			echo esc_html__( 'Note:  Deleting a fund from this list does NOT affect any donations already made to that fund, or any reports for that fund.', 'seamless-donations' );
			echo "</p>";
			echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Save Changes', 'seamless-donations' ) . "' name='submit'></p>";
			echo "</form>";
		}
		else {
			echo "<p>" . esc_html__( 'You have no designated funds defined', 'seamless-donations' ) . "</p>";
		}

		echo "</div> <!-- col-wrap -->";
		echo "</div> <!-- col-right -->";

		echo "<div id='col-left'>";
		echo "<div class='col-wrap'>";

		echo "<h3>" . esc_html__( 'Add New Designated Fund', 'seamless-donations' ) . "</h3>";
		echo "<form method='POST' action=''>";
		echo "<input type='hidden' name='dgx_donate_fund_nonce' id='dgx_donate_fund_nonce' value='" . esc_attr( $fund_nonce ) . "' />";

		echo "<div class='form-field'>";
		echo "<label for='addfund'>" . esc_html__( 'Name', 'seamless-donations' ) . "</label>";
		echo "<input type='text' name='addfund' size='40' />";
		echo "<p class='description'>" . esc_html__( 'The name of the desginated fund, as you want it to appear to visitors.', 'seamless-donations' ) . "</p>";
		echo "</div> <!-- form-field -->";

		echo "<p><input id='submit' class='button' type='submit' value='" . esc_attr__( 'Add New Designated Fund', 'seamless-donations' ) . "' name='submit'></p>";
		echo "</form>";

		echo "</div> <!-- col-wrap -->";
		echo "</div> <!-- col-left -->";
		echo "</div> <!-- col-container -->";
		echo "</div> <!-- wrap -->";
	}
}

$dgx_donate_admin_funds_view = new Dgx_Donate_Admin_Funds_View();
