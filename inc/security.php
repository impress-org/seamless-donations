<?php
/**
 * Seamless Donations by David Gewirtz, adopted from Allen Snook
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: http://zatzlabs.com/seamless-donations/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2020 by David Gewirtz
 *
 */

//	Exit if .php file accessed directly
if (!defined('ABSPATH')) exit;

function seamless_donations_get_security_status() {
    $gateway = get_option('dgx_donate_payment_processor_choice');
    $status  = array();

    $required_curl_version = '7.34.0';
    $required_ssl_version  = '1.0.1';
    $required_tls_version  = '1.2';

    $status['file_get_contents_enabled'] = false; // determines if file_get_contents can be used to check the SSL page
    $status['curl_enabled']              = false; // determines if the cURL library was found and enabled
    $status['ssl_version_ok']            = false; // determines if the current SSL version is high enough
    $status['curl_version_ok']           = false; // determine if the current cURL version is high enough
    $status['tls_version_ok']            = false;
    $status['https_ipn_works']           = false; // determines if the SSL IPN is functional
    $status['http_ipn_works']            = false; // determines if the basic IPN is functional
    $status['curl_version']              = 'N/A';
    $status['ssl_version']               = 'N/A';
    $status['required_curl_version']     = $required_curl_version;
    $status['required_ssl_version']      = $required_ssl_version;
    $status['required_tls_version']      = $required_tls_version;
    $status['ipn_domain_ok']             = false;
    $status['ipn_domain_ip']             = 'N/A';
    $status['ipn_domain_url']            = 'N/A';
    $status['payment_ready_ok']          = false;

    //$http_ipn_url  = plugins_url('/dgx-donate-paypalstd-ipn.php', dirname(__FILE__));
    $https_ipn_url = plugins_url('/pay/paypalstd/ipn.php', dirname(__FILE__));
    $https_ipn_url = str_ireplace('http://', 'https://', $https_ipn_url); // force https check
    $https_ipn_url .= '?status_check=true';

    // determine availability and version compatibility
    // all these calls are stfu'd because we have no idea what they'll do across the interwebs
    if (@function_exists('curl_init')) {
        $status['curl_enabled'] = true;

        $ch = @curl_init();
        if ($ch != false) {
            $version                = @curl_version();
            $curl_compare           = @seamless_donations_version_compare($version['version'], $required_curl_version);
            $ssl_compare            = @seamless_donations_version_compare($version['ssl_version'],
                $required_ssl_version);
            $status['curl_version'] = $version['version'];
            $status['ssl_version']  = $version['ssl_version'];

            if ($curl_compare != '<') {
                $status['curl_version_ok'] = true;
            }
            if ($ssl_compare != '<') {
                $status['ssl_version_ok'] = true;
            }

            @curl_close($ch);
        }
    }

    if (@ini_get('allow_url_fopen')) {
        $status['file_get_contents_enabled'] = true;

        //        $test_result = @file_get_contents($http_ipn_url);
        //        if ($test_result !== false) {
        //            $status['http_ipn_works'] = true;
        //        }

        $test_result = @file_get_contents($https_ipn_url);
        if ($test_result !== false) {
            $status['https_ipn_works'] = true;
        }
    }

    if ($status['curl_version_ok']) {
        // code from https://gist.github.com/olivierbellone/5fbe074004059c1be5cc81408b72c7b3
        $ch = curl_init('https://www.howsmyssl.com/a/check');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $json                  = json_decode($data);
        $status['tls_version'] = $json->tls_version;
        if ($status['tls_version'] == '') {
            $status['tls_version'] = 'N/A';
        } else {
            $tls_compare = @seamless_donations_version_compare($status['tls_version'], $status['required_tls_version']);
            if ($tls_compare != '<') {
                $status['tls_version_ok'] = true;
            }
        }
    }

    // check to see if domain for IPN is local or externally accessible
    $url_parts                = parse_url($https_ipn_url);
    $status['ipn_domain_url'] = $url_parts['host'];
    $ip_address               = @gethostbyname($status['ipn_domain_url']);
    if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
        $status['ipn_domain_ok'] = true;
        $status['ipn_domain_ip'] = $ip_address;
    } else {
        $status['ipn_domain_ip'] = 'N/A';
    }

    if ($gateway == 'PAYPAL') {
        if (!$status['file_get_contents_enabled'] || !$status['curl_enabled'] || !$status['tls_version_ok'] ||
            !$status['curl_version_ok'] || !$status['https_ipn_works'] or !$status['ipn_domain_ok']) {
            $status['payment_ready_ok'] = false;
        } else {
            $status['payment_ready_ok'] = true;
        }
    }
    if ($gateway == 'STRIPE') {
        if (!$status['file_get_contents_enabled'] || !$status['curl_enabled'] || !$status['tls_version_ok'] ||
            !$status['curl_version_ok']) {
            $status['payment_ready_ok'] = false;
        } else {
            $status['payment_ready_ok'] = true;
        }
    }

    return $status;
}

function seamless_donations_display_security_status($status) {
    if (!$status['file_get_contents_enabled'] || !$status['curl_enabled'] || !$status['ssl_version_ok'] ||
        !$status['curl_version_ok'] || !$status['https_ipn_works']
    ) {
        // issue a warning
        $msg = '<div class="seamless-donations-warning-box">';
        $msg .= "<b>WARNING: Your server appears to have incompatibilities with PayPal's requirements</b>";
        $msg .= "<BR><i>cURL: " . $status['curl_version'] . ", SSL: " . $status['ssl_version'] . "</i>";

        if (!$status['file_get_contents_enabled']) {
            $msg .= '<p><b>allow_url_fopen not available: </b><br>' . <<<EOT
This is a setting in your server's php.ini file that allows the fopen function to get a remote file. Seamless Donations uses this as its primary way of determining whether your server properly supports https:// URLs. Without this feature turned on, it's not possible to tell whether your server will properly work with PayPal.
EOT;
            $msg .= '</p>';
        }

        if (!$status['curl_enabled']) {
            $msg .= '<p><b>cURL library not available: </b><br>' . <<<EOT
This is a library on your server that enables some communications features. Seamless Donations uses this as its way of determining whether your server is running the necessary versions of cURL and OpenSSL to support PayPal's TLS 1.2 security requirement. Without this feature library, it's not possible to tell whether your server will properly work with PayPal.
EOT;
            $msg .= '</p>';
        } else {
            $paypal_status = seamless_donations_check_paypal_tls_URL();
            $msg           .= '<p><b>PayPal TLS test results: </b>' . $paypal_status . '<BR>' . <<<EOT
This is PayPal's <A HREF=https://www.paypal-notice.com/en/TLS-1.2-and-HTTP1.1-Upgrade/">own testing system</A>. The results you see are the results of how PayPal interprets your server's compatibility.
EOT;
        }
        if (!$status['ssl_version_ok']) {
            $msg .= '<p><b>OpenSSL version too low: </b><br>' . <<<EOT
PayPal requires TLSv1.2, which requires OpenSSL 1.0.1 or greater. Your server appears to be running an older version
EOT;
            $msg .= ' (' . $status['ssl_version'] . '). ' . <<<EOT
See <A HREF="https://en.wikipedia.org/wiki/Comparison_of_TLS_implementations">this page</A> for minimum versions and other implementations.</A> If you are running a different implementation, but a valid version of SSL, you may be able to disregard this message.
EOT;
            $msg .= '</p>';
        }

        if (!$status['curl_version_ok']) {
            $msg .= '<p><b>cURL version too low: </b><br>' . <<<EOT
PayPal requires TLSv1.2, which requires cURL 7.34.0 or greater. Your server appears to be running an older version
EOT;
            $msg .= ' (' . $status['curl_version'] . ').</p>';
        }

        if (!$status['https_ipn_works']) {
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
        $msg           = '<div class="seamless-donations-success-box">';
        $msg           .= "Congratulations! Your site appears compatible with PayPal's requirements.";
        $msg           .= "<BR><i>cURL: " . $status['curl_version'] . ", SSL: " . $status['ssl_version'] .
            ", HTTPS: responds</i>";
        $paypal_status = seamless_donations_check_paypal_tls_URL();
        $msg           .= '<p><b>PayPal TLS test results: </b>' . $paypal_status . '<BR>' . <<<EOT
This is PayPal's <A HREF=https://www.paypal-notice.com/en/TLS-1.2-and-HTTP1.1-Upgrade/">own testing system</A>. The results you see are the results of how PayPal interprets your server's compatibility.
EOT;
    }

    $msg .= '</div>';

    return $msg;
}

function seamless_donations_display_tls_status($status) {
    $gateway = get_option('dgx_donate_payment_processor_choice');

    $msg = '';
    $msg .= '<TABLE id="seamless_donations_tls_table">';
    $msg .= '<TR>';
    if (!$status['file_get_contents_enabled']) {
        $msg .= '<TD>' . seamless_donations_display_fail() . '</TD><TD>';
        $msg .= ' allow_url_fopen</TD><TD>Not enabled';
        $msg .= '</TD><TD>';
        $msg .= '<i>This option in PHP.INI must be enabled.</i>';
        $msg .= '</TD>';
    } else {
        $msg .= '<TD>' . seamless_donations_display_pass() . '</TD><TD>';
        $msg .= ' allow_url_fopen</TD><TD>Enabled';
        $msg .= '</TD><TD>';
        $msg .= '</TD>';
    }
    $msg .= '</TR>';

    $msg .= '<TR>';
    if (!$status['curl_enabled']) {
        $msg .= '<TD>' . seamless_donations_display_fail() . '</TD><TD>';
        $msg .= ' cURL</TD><TD>Not enabled';
        $msg .= '</TD><TD>';
        $msg .= '</TD>';
    } else {
        $msg .= '<TD>' . seamless_donations_display_pass() . '</TD><TD>';
        $msg .= ' cURL</TD><TD>Enabled';
        $msg .= '</TD><TD>';
        $msg .= '</TD>';
        $msg .= '</TR>';

        $msg .= '<TR>';
        if (!$status['curl_version_ok']) {
            $msg .= '<TD>' . seamless_donations_display_fail() . '</TD><TD>';
            $msg .= ' cURL Version</TD><TD' . $status['curl_version'];
            $msg .= '</TD><TD>';
            $msg .= '<i>Required version is ' . $status['required_curl_version'] . ' or greater</i>';
            $msg .= '</TD>';
        } else {
            $msg .= '<TD>' . seamless_donations_display_pass() . '</TD><TD>';
            $msg .= ' cURL Version</TD><TD>' . $status['curl_version'];
            $msg .= '</TD><TD>';
            $msg .= '</TD>';
            $msg .= '</TR>';

            $msg .= '<TR>';
            if (!$status['tls_version_ok']) {
                $msg .= '<TD>' . seamless_donations_display_fail() . '</TD><TD>';
                $msg .= ' TLS Version</TD><TD>' . $status['tls_version'];
                $msg .= '</TD><TD>';
                $msg .= '<i>Required version is ' . $status['required_tls_version'] . ' or greater</i>';
                $msg .= '</TD>';
            } else {
                $msg .= '<TD>' . seamless_donations_display_pass() . '</TD><TD>';
                $msg .= ' TLS Version</TD><TD>' . $status['tls_version'];
                $msg .= '</TD><TD>';
                $msg .= '</TD>';
            }
            $msg .= '</TR>';
        }
    }

    if ($gateway == 'PAYPAL') {
        $msg .= '<TR>';
        if (!$status['https_ipn_works']) {
            $msg .= '<TD>';
            $msg .= seamless_donations_display_fail();
            $msg .= '</TD><TD>';
            $msg .= ' HTTPS IPN</TD><TD>Unreachable';
            $msg .= '</TD><TD>';
            $msg .= '<i>The payment processor notification URL is not responding.</i>';
            $msg .= '</TD>';
        } else {
            $msg .= '<TD>' . seamless_donations_display_pass() . '</TD><TD>';
            $msg .= ' HTTPS IPN</TD><TD>Responds OK';
            $msg .= '</TD><TD>';
            $msg .= '</TD>';
        }
        $msg .= '</TR>';
    }

    if ($gateway == 'PAYPAL') {
        $msg .= '<TR>';
        if (!$status['ipn_domain_ok']) {
            $msg .= '<TD>';
            $msg .= seamless_donations_display_fail();
            $msg .= '</TD><TD>';
            $msg .= ' ' . $status['ipn_domain_url'] . '</TD><TD>Unreachable';
            $msg .= '</TD><TD>';
            $msg .= '<i>This domain is not reachable from the public Internet.</i>';
            $msg .= '</TD>';
        } else {
            $msg .= '<TD>' . seamless_donations_display_pass() . '</TD><TD>';
            $msg .= ' ' . $status['ipn_domain_url'] . '</TD><TD>' . $status['ipn_domain_ip'];
            $msg .= '</TD><TD>';
            $msg .= '</TD>';
        }
        $msg .= '</TR>';
    }

    $msg .= '</TABLE>';

    return $msg;
}

function seamless_donations_check_paypal_tls_URL() {
    // test code using PayPal's TLS test https://www.paypal-notice.com/en/TLS-1.2-and-HTTP1.1-Upgrade/
    $url = "https://tlstest.paypal.com/";
    $ch  = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $paypal_test = curl_exec($ch);
    curl_close($ch);
    return $paypal_test;
}