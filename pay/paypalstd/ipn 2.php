<?php
/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

// Load WordPress
include "../../../../../wp-config.php";

// Load Seamless Donations Core
require_once '../../inc/geography.php';
require_once '../../inc/currency.php';
require_once '../../inc/utilities.php';
require_once '../../inc/legacy.php';
require_once '../../inc/donations.php';

require_once '../../legacy/dgx-donate.php';
require_once '../../legacy/dgx-donate-admin.php';
require_once '../../seamless-donations-admin.php';
require_once '../../seamless-donations-form.php';
require_once '../../dgx-donate-paypalstd.php';

class Seamless_Donations_PayPal_IPN_Handler {

	var $chat_back_url  = '';
	var $host_header    = '';
	var $post_data      = array();
	var $session_id     = '';
	var $transaction_id = '';

	public function __construct() {

		// Grab all the post data
		$post = file_get_contents( 'php://input' );
		parse_str( $post, $data );
		$this->post_data = $data;

		/* DEBUGGING STUFF
		if ( isset( $_POST ) ) {
			dgx_donate_debug_log( '$_POST array size: ' . count( $_POST ) );
		} else {
			dgx_donate_debug_log( '$_POST not set.' );
		}
		if ( isset( $_GET ) ) {
			dgx_donate_debug_log( '$_GET array size: ' . count( $_GET ) );
		} else {
			dgx_donate_debug_log( '$_GET not set.' );
		}
		seamless_donations_post_array_to_log();
		seamless_donations_force_a_backtrace_to_log();

		seamless_donations_server_global_to_log( 'PHP_SELF', true );
		seamless_donations_server_global_to_log( 'REQUEST_METHOD', true );
		seamless_donations_server_global_to_log( 'HTTP_REFERER' , true);
		seamless_donations_server_global_to_log( 'HTTPS' , true);
		seamless_donations_server_global_to_log( 'REQUEST_URI', true);
		seamless_donations_server_global_to_log( 'QUERY_STRING', true);
		seamless_donations_server_global_to_log( 'DOCUMENT_ROOT', true);
		seamless_donations_server_global_to_log( 'HTTP_ACCEPT', true);
		seamless_donations_server_global_to_log( 'HTTP_HOST', true);
		seamless_donations_server_global_to_log( 'HTTP_USER_AGENT', true);
		seamless_donations_server_global_to_log( 'REMOTE_ADDR', true);
		seamless_donations_server_global_to_log( 'REMOTE_HOST', true);
		 END DEBUGGING BLOCK */

		// Set up for production or test
		$this->configure_for_production_or_test();

		// Extract the session and transaction IDs from the POST
		$this->get_ids_from_post();

		if ( ! empty( $this->session_id ) ) {
			dgx_donate_debug_log( '----------------------------------------' );
			dgx_donate_debug_log( 'PROCESSING PAYPAL IPN TRANSACTION (HTTPS)' );
			dgx_donate_debug_log( "Seamless Donations Version: " . dgx_donate_get_version() );

			$response = $this->reply_to_paypal();

			if ( "VERIFIED" == $response ) {
				$this->handle_verified_ipn();
			} else if ( "INVALID" == $response ) {
				$this->handle_invalid_ipn();
			} else {
				$this->handle_unrecognized_ipn( $response );
			}

			do_action( 'seamless_donations_paypal_ipn_processing_complete', $this->session_id, $this->transaction_id );
			dgx_donate_debug_log( 'IPN processing complete.' );
		} else {
			dgx_donate_debug_log( 'Null IPN (Empty session id).  Nothing to do.' );
		}
	}

	function configure_for_production_or_test( $tls_or_ssl_or_curl = 'tls' ) {

		if ( "SANDBOX" == get_option( 'dgx_donate_paypal_server' ) ) {
			$this->host_header = "Host: www.sandbox.paypal.com\r\n";
			switch ( $tls_or_ssl_or_curl ) {
				case 'tls':
					$this->chat_back_url = "tls://www.sandbox.paypal.com";
					break;
				case 'ssl':
					$this->chat_back_url = "ssl://www.sandbox.paypal.com:443/";
					break;
				case 'curl':
					$this->chat_back_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
					break;
			}
		} else {
			$this->host_header = "Host: www.paypal.com\r\n";
			switch ( $tls_or_ssl_or_curl ) {
				case 'tls':
					$this->chat_back_url = "tls://www.paypal.com";
					break;
				case 'ssl':
					$this->chat_back_url = "ssl://www.paypal.com:443/";
					break;
				case 'curl':
					$this->chat_back_url = "https://www.paypal.com/cgi-bin/webscr";
					break;
			}
		}
	}

	function get_ids_from_post() {

		$this->session_id     = isset( $this->post_data["custom"] ) ? $this->post_data["custom"] : '';
		$this->transaction_id = isset( $this->post_data["txn_id"] ) ? $this->post_data["txn_id"] : '';
	}

	function reply_to_paypal() {

		$request_data        = $this->post_data;
		$request_data['cmd'] = '_notify-validate';
		$request             = http_build_query( $request_data );

		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= $this->host_header;
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen( $request ) . "\r\n\r\n";

		$required_curl_version = '7.34.0';
		$response              = '';

		dgx_donate_debug_log( "IPN chatback attempt via TLS..." );
		$fp = fsockopen( $this->chat_back_url, 443, $errno, $errstr, 30 );
		if ( ! $fp ) {
			dgx_donate_debug_log( "IPN chatback attempt via SSL..." );
			$this->configure_for_production_or_test( 'ssl' );
			$fp = stream_socket_client( $this->chat_back_url, $errno, $errstr, 30 );
		}
		if ( $fp ) {
			dgx_donate_debug_log( "IPN chatback attempt completed. Checking response..." );
			fputs( $fp, $header . $request );

			$done = false;
			do {
				if ( feof( $fp ) ) {
					$done = true;
				} else {
					$response = fgets( $fp, 1024 );
					$done     = in_array( $response, array( "VERIFIED", "INVALID" ) );
				}
			} while( ! $done );
		} else {
			// let's try cURL as a final fallback
			// based on sample PayPal code at https://github.com/paypal/ipn-code-samples/blob/master/paypal_ipn.php
			dgx_donate_debug_log( "IPN chatback attempt via SSL failed, attempting cURL..." );
			$this->configure_for_production_or_test( 'curl' );
			if ( function_exists( 'curl_init' ) ) {
				$ch           = curl_init( $this->chat_back_url );
				$version      = curl_version();
				$curl_compare = seamless_donations_version_compare( $version['version'], $required_curl_version );

				if ( $curl_compare == '<' ) {
					curl_close( $ch );
					$ch = false; // kill the curl call
				}
				if ( $ch != false ) {
					curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
					curl_setopt( $ch, CURLOPT_POST, 1 );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, $request );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 1 );
					curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
					curl_setopt( $ch, CURLOPT_FORBID_REUSE, 1 );
					curl_setopt( $ch, CURLOPT_SSLVERSION, 6 ); //Integer NOT string TLS v1.2

					// set TCP timeout to 30 seconds
					curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
					curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Connection: Close' ) );

					// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html"
					// and set the directory path of the certificate as shown below.
					// Ensure the file is readable by the webserver.
					// This is mandatory for some environments.
					// $cert = __DIR__ . "/ssl/cacert.pem";
					// dgx_donate_debug_log( "Loading certificate from $cert" );
					// curl_setopt( $ch, CURLOPT_CAINFO, $cert );

					$response = curl_exec( $ch );
					if ( curl_errno( $ch ) != 0 ) { // cURL error
						dgx_donate_debug_log(
							"IPN failed: unable to establish network chatback connection to PayPal via cURL" );
						dgx_donate_debug_log( "IPN cURL error: " . curl_error( $ch ) );
						$version = curl_version();
						dgx_donate_debug_log( "cURL version: " . $version['version'] . " OpenSSL version: " .
						                      $version['ssl_version'] );
						// https://curl.haxx.se/docs/manpage.html#--tlsv12
						// https://en.wikipedia.org/wiki/Comparison_of_TLS_implementations
						dgx_donate_debug_log( "PayPal requires TLSv1.2, which requires cURL 7.34.0 and OpenSSL 1.0.1." );
						dgx_donate_debug_log( "See https://en.wikipedia.org/wiki/Comparison_of_TLS_implementations" );
						dgx_donate_debug_log( "for minimum versions for other implementations." );
					} else {
						// Split response headers and payload, a better way for strcmp
						dgx_donate_debug_log( "IPN chatback attempt via cURL completed. Checking response..." );
						$tokens   = explode( "\r\n\r\n", trim( $response ) );
						$response = trim( end( $tokens ) );
					}
					curl_close( $ch );
				}
			} else {
				dgx_donate_debug_log(
					"Unable to complete chatback attempt. SSL incompatible. Consider enabling cURL library." );
				dgx_donate_debug_log( "See https://en.wikipedia.org/wiki/Comparison_of_TLS_implementations" );
				dgx_donate_debug_log( "for minimum versions for other implementations." );
			}
		}

		fclose( $fp );

		//dgx_donate_debug_log ( "url = {$this->chat_back_url}, errno = $errno, errstr = $errstr" );
		return $response;
	}

	function handle_verified_ipn() {

		$sd4_mode       = get_option( 'dgx_donate_start_in_sd4_mode' );
		$payment_status = $this->post_data["payment_status"];

		dgx_donate_debug_log( "IPN VERIFIED for session ID {$this->session_id}" );
		dgx_donate_debug_log( "PayPal reports payment status: {$payment_status}" );

		if ( "Completed" == $payment_status ) {
			// Check if we've already logged a transaction with this same transaction id
			$donation_id = get_donations_by_meta( '_dgx_donate_transaction_id', $this->transaction_id, 1 );

			if ( 0 == count( $donation_id ) ) {
				// We haven't seen this transaction ID already

				// See if a donation for this session ID already exists
				$donation_id = get_donations_by_meta( '_dgx_donate_session_id', $this->session_id, 1 );

				if ( 0 == count( $donation_id ) ) {
					// We haven't seen this session ID already

					// Retrieve the data
					if ( $sd4_mode == false ) {
						// retrieve from transient
						$donation_form_data = get_transient( $this->session_id );
					} else {
						// retrieve from audit db table
						$donation_form_data = seamless_donations_get_audit_option( $this->session_id );
					}

					if ( ! empty( $donation_form_data ) ) {
						// Create a donation record
						if ( $sd4_mode == false ) {
							dgx_donate_debug_log(
								"Creating donation from transient data in pre-4.x mode." );
							$donation_id = dgx_donate_create_donation_from_transient_data( $donation_form_data );
						} else {
							dgx_donate_debug_log( "Creating donation from transaction audit data in 4.x mode." );
							$donation_id = seamless_donations_create_donation_from_transaction_audit_table(
								$donation_form_data );
						}
						dgx_donate_debug_log(
							"Created donation {$donation_id} for session ID {$this->session_id}" );

						if ( $sd4_mode == false ) {
							// Clear the transient
							delete_transient( $this->session_id );
						}
					} else {
						// We have a session_id but no transient (the admin might have
						// deleted all previous donations in a recurring donation for
						// some reason) - so we will have to create a donation record
						// from the data supplied by PayPal
						if ( $sd4_mode == false ) {
							$donation_id = dgx_donate_create_donation_from_paypal_data( $this->post_data );
							dgx_donate_debug_log(
								"Created donation {$donation_id} " .
								"from PayPal data (no transient data found) in pre-4.x mode." );
						} else {
							$donation_id = seamless_donations_create_donation_from_paypal_data();
							dgx_donate_debug_log(
								"Created donation {$donation_id} " .
								"from PayPal data (no audit db data found) in 4.x mode." );
						}
					}
				} else {
					// We have seen this session ID already, create a new donation record for this new transaction

					// But first, flatten the array returned by get_donations_by_meta for _dgx_donate_session_id
					$donation_id = $donation_id[0];

					$old_donation_id = $donation_id;
					if ( $sd4_mode == false ) {
						$donation_id = dgx_donate_create_donation_from_donation( $old_donation_id );
					} else {
						$donation_id = seamless_donations_create_donation_from_donation( $old_donation_id );
					}
					dgx_donate_debug_log(
						"Created donation {$donation_id} (recurring donation, donor data copied from donation {$old_donation_id}" );
				}
			} else {
				// We've seen this transaction ID already - ignore it
				$donation_id = '';
				dgx_donate_debug_log( "Transaction ID {$this->transaction_id} already handled - ignoring" );
			}

			if ( ! empty( $donation_id ) ) {
				// Update the raw paypal data
				update_post_meta( $donation_id, '_dgx_donate_transaction_id', $this->transaction_id );
				update_post_meta( $donation_id, '_dgx_donate_payment_processor', 'PAYPALSTD' );
				update_post_meta( $donation_id, '_dgx_donate_payment_processor_data', $this->post_data );
				// save the currency of the transaction
				$currency_code = $this->post_data['mc_currency'];
				dgx_donate_debug_log( "Payment currency = {$currency_code}" );
				update_post_meta( $donation_id, '_dgx_donate_donation_currency', $currency_code );
			}

			// @todo - send different notification for recurring?

			// Send admin notification
			dgx_donate_send_donation_notification( $donation_id );
			// Send donor notification
			dgx_donate_send_thank_you_email( $donation_id );
		}
	}

	function handle_invalid_ipn() {

		dgx_donate_debug_log( "IPN failed (INVALID) for sessionID {$this->session_id}" );
	}

	function handle_unrecognized_ipn( $paypal_response ) {

		dgx_donate_debug_log( "IPN failed (unrecognized response) for sessionID {$this->session_id}" );
		dgx_donate_debug_log( "==> " . $paypal_response );
	}
}
dgx_donate_debug_log("pay/paypalstd/ipn.php called outside of constructor.");
$seamless_donations_ipn_responder = new Seamless_Donations_PayPal_IPN_Handler();

/**
 * We cannot send nothing, so send back just a simple content-type message
 */

echo "content-type: text/plain\n\n";
