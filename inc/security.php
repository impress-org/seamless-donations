<?php
/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2016 by David Gewirtz
 */

function seamless_donations_get_security_status() {

	$status = array();

	$status['file_get_contents_enabled'] = false; // determines if file_get_contents can be used to check the SSL page
	$status['curl_enabled']              = false; // determines if the cURL library was found and enabled
	$status['ssl_version_ok']            = false; // determines if the current SSL version is high enough
	$status['curl_version_ok']           = false; // determine if the current cURL version is high enough
	$status['https_ipn_works']           = false; // determines if the SSL IPN is functional
	$status['http_ipn_works']            = false; // determines if the basic IPN is functional
	$status['curl_version']              = 'N/A';
	$status['ssl_version']               = 'N/A';

	$required_curl_version = '7.34.0';
	$required_ssl_version  = '1.0.1';

	$http_ipn_url  = plugins_url( '/dgx-donate-paypalstd-ipn.php', dirname( __FILE__ ) );
	$https_ipn_url = plugins_url( '/pay/paypalstd/ipn.php', dirname( __FILE__ ) );
	$https_ipn_url = str_ireplace( 'http://', 'https://', $https_ipn_url ); // force https check

	// determine availability and version compatibility
	// all these calls are stfu'd because we have no idea what they'll do across the interwebs
	if ( @function_exists( 'curl_init' ) ) {
		$status['curl_enabled'] = true;

		$ch = @curl_init();
		if ( $ch != false ) {
			$version                = @curl_version();
			$curl_compare           = @seamless_donations_version_compare( $version['version'], $required_curl_version );
			$ssl_compare            = @seamless_donations_version_compare( $version['ssl_version'],
			                                                              $required_ssl_version );
			$status['curl_version'] = $version['version'];
			$status['ssl_version']  = $version['ssl_version'];

			if ( $curl_compare != '<' ) {
				$status['curl_version_ok'] = true;
			}
			if ( $ssl_compare != '<' ) {
				$status['ssl_version_ok'] = true;
			}

			@curl_close( $ch );
		}
	}

	if ( @ini_get( 'allow_url_fopen' ) ) {
		$status['file_get_contents_enabled'] = true;

		$test_result = @file_get_contents( $http_ipn_url );
		if ( $test_result !== false ) {
			$status['http_ipn_works'] = true;
		}

		$test_result = @file_get_contents( $https_ipn_url );
		if ( $test_result !== false ) {
			$status['https_ipn_works'] = true;
		}
	}

	return $status;
}

function seamless_donations_display_security_status( $status ) {

	if ( ! $status['file_get_contents_enabled'] || ! $status['curl_enabled'] || ! $status['ssl_version_ok'] ||
	     ! $status['curl_version_ok'] || ! $status['https_ipn_works']
	) {
		// issue a warning
		$msg = '<div class="seamless-donations-warning-box">';
		$msg .= "<b>WARNING: Your server appears to have incompatibilities with PayPal's requirements</b>";
		$msg .= "<BR><i>cURL: " . $status['curl_version'] . ", SSL: " . $status['ssl_version'] . "</i>";

		if ( ! $status['file_get_contents_enabled'] ) {
			$msg .= '<p><b>allow_url_fopen not available: </b><br>' . <<<EOT
This is a setting in your server's php.ini file that allows the fopen function to get a remote file. Seamless Donations uses this as its primary way of determining whether your server properly supports https:// URLs. Without this feature turned on, it's not possible to tell whether your server will properly work with PayPal.
EOT;
			$msg .= '</p>';
		}

		if ( ! $status['curl_enabled'] ) {
			$msg .= '<p><b>cURL library not available: </b><br>' . <<<EOT
This is a library on your server that enables some communications features. Seamless Donations uses this as its way of determining whether your server is running the necessary versions of cURL and OpenSSL to support PayPal's TLS 1.2 security requirement. Without this feature library, it's not possible to tell whether your server will properly work with PayPal.
EOT;
			$msg .= '</p>';
		}

		if ( ! $status['ssl_version_ok'] ) {
			$msg .= '<p><b>OpenSSL version too low: </b><br>' . <<<EOT
PayPal requires TLSv1.2, which requires OpenSSL 1.0.1 or greater. Your server appears to be running an older version
EOT;
			$msg .= ' (' . $status['ssl_version'] . '). ' . <<<EOT
See <A HREF="https://en.wikipedia.org/wiki/Comparison_of_TLS_implementations">this page</A> for minimum versions and other implementations.</A> If you are running a different implementation, but a valid version of SSL, you may be able to disregard this message.
EOT;
			$msg .= '</p>';
		}

		if ( ! $status['curl_version_ok'] ) {
			$msg .= '<p><b>cURL version too low: </b><br>' . <<<EOT
PayPal requires TLSv1.2, which requires cURL 7.34.0 or greater. Your server appears to be running an older version
EOT;
			$msg .= ' (' . $status['curl_version'] . ').</p>';
		}

		if ( ! $status['https_ipn_works'] ) {
			$msg .= '<p><b>https:// IPN URL does not respond: </b><br>' . <<<EOT
Effective immediately, PayPal requires new Sandbox IPN URLs to be secured with SSL URLs. Seamless Donations tested the SSL IPN URL provided below and it did not respond. Please note that as of September 30, 2016 PayPal will require live IPN URLs to be secured by SSL. If you are getting this error at or near that time, you will cease to be able to collect donations via PayPal.</p><p>Please also note that your entire Web site does NOT need to be https-compliant to respond to PayPal IPNs. If you or your ISP properly installs an SSL certificate, then the IPN provided below will be sufficient to talk to PayPal, even if your WordPress site has not been converted to be https-compliant.
EOT;
			$msg .= '</p>';
		}

		// resources msg
		$msg .= '<p><b>Additional reading from David: </b><br>' . <<<EOT
		<a href="http://zatzlabs.com/adding-https-support-to-seamless-donations-4-0-16/">Adding https support to Seamless Donations 4.0.16</a><br>
		<a href="http://www.zdnet.com/article/paypal-et-al-web-site-kicked-in-the-saas/">PayPal et al: When your web site gets kicked in the SaaS</a><br>
		<a href="http://zatzlabs.com/tricks-i-learned-installing-my-first-ssl-certificate/">Tricks I learned installing my first SSL certificate</a><br>
EOT;
		$msg .= '</p>';

		$msg .= '<p><b>All about SSL and PayPal: </b><br>' . <<<EOT
		<a href="https://www.paypal-knowledge.com/infocenter/index?page=content&id=FAQ1916&expand=true&locale=en_US">PayPal's statement on SSL</a><br>
		<a href="http://www.wpbeginner.com/wp-tutorials/how-to-add-ssl-and-https-in-wordpress/">How to add SSL and HTTPS in WordPress</a><br>
		<a href="http://www.elegantthemes.com/blog/tips-tricks/how-to-use-ssl-https-with-wordpress">How to use SSL & HTTPS with WordPress</a><br>
		<a href="http://www.wpbeginner.com/wp-tutorials/how-to-add-free-ssl-in-wordpress-with-lets-encrypt/">How to Add Free SSL in WordPress with Let’s Encrypt</a><br>
		<a href="https://wordpress.org/plugins/really-simple-ssl/">Really Simple SSL plugin</a><br>
		<a href="https://letsencrypt.org/">Let's Encrypt main page</a><br>
EOT;
		$msg .= '</p>';

		$msg .= '<p><b>Helpful tools: </b><br>' . <<<EOT
		<a href="https://www.sslchecker.com/matcher">SSL key validator (might not be secure)</a><br>
		<a href="https://decoder.link/sslchecker/">SSL checker to confirm you have a proper SSL install</a><br>

EOT;
		$msg .= '</p>';
	} else {
		// it's all good
		$msg = '<div class="seamless-donations-success-box">';
		$msg .= "Congratulations! Your site appears compatible with PayPal's requirements.";
		$msg .= "<BR><i>cURL: " . $status['curl_version'] . ", SSL: " . $status['ssl_version'] .
		        ", HTTPS: responds</i>";
	}
	$msg .= '</div>';

	return $msg;
}