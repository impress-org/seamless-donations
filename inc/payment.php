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

function seamless_donations_process_payment() {
    seamless_donations_check_payment_nonce();

    $sd4_mode    = get_option('dgx_donate_start_in_sd4_mode');
    $session_id  = $_POST['_dgx_donate_session_id'];
    $php_version = phpversion();

    $payment_gateway = get_option('dgx_donate_payment_processor_choice');
    switch ($payment_gateway) {
        case 'PAYPAL':
            $gateway_mode = get_option('dgx_donate_paypal_server');
            $notify_url   = plugins_url('/pay/paypalstd/ipn.php', dirname(__FILE__));
            $notify_url   = str_ireplace('http://', 'https://', $notify_url); // force https check
            break;
        case 'STRIPE':
            $gateway_mode = get_option('dgx_donate_stripe_server');
            $notify_url   = plugins_url('/pay/stripe/webhook.php', dirname(__FILE__));
            $notify_url   = str_ireplace('http://', 'https://', $notify_url); // force https check
            if ($gateway_mode == 'LIVE') {
                $stripe_api_key    = get_option('dgx_donate_live_stripe_api_key');
                $stripe_secret_key = get_option('dgx_donate_live_stripe_secret_key');
            } else {
                $stripe_api_key    = get_option('dgx_donate_test_stripe_api_key');
                $stripe_secret_key = get_option('dgx_donate_test_stripe_secret_key');
            }
            break;
    }

    dgx_donate_debug_log('----------------------------------------');
    dgx_donate_debug_log('DONATION TRANSACTION STARTED');
    dgx_donate_debug_log("Session ID retrieved from _POST: $session_id");
    dgx_donate_debug_log('Processing mode: ' . $gateway_mode);
    dgx_donate_debug_log("PHP version: $php_version");
    dgx_donate_debug_log("Seamless Donations version: " . dgx_donate_get_version());
    dgx_donate_debug_log("User browser: " . seamless_donations_get_browser_name());
    dgx_donate_debug_log('Payment gateway: ' . $payment_gateway);
    dgx_donate_debug_log('Gateway mode: ' . $gateway_mode);
    dgx_donate_debug_log('Notify URL (https IPN): ' . $notify_url);

    $session_data = seamless_donations_check_preexisting_payment_session_data($session_id);

    if ($session_data !== false) {
        dgx_donate_debug_log('Session data already exists, returning false');
        die();
    } else {
        dgx_donate_debug_log('Duplicate session data not found. Payment process data assembly can proceed.');

        $post_data = seamless_donations_repack_payment_form_data_for_transmission_to_gateways();
        $post_data = apply_filters('seamless_donations_payment_post_data', $post_data);
        seamless_donations_perform_captcha_check($post_data);

        seamless_donations_save_payment_transaction_data_for_audit($sd4_mode, $post_data, $session_id);

        // more log data
        $donor_name = seamless_donations_obscurify_donor_name($post_data);
        dgx_donate_debug_log('Name: ' . $donor_name);
        dgx_donate_debug_log('Amount: ' . $post_data['AMOUNT']);
        dgx_donate_debug_log("Preparation complete.");

        switch ($payment_gateway) {
            case 'PAYPAL':
                dgx_donate_debug_log('Entering PayPal gateway processing.');
                $post_args = seamless_donations_build_paypal_query_string($post_data, $notify_url);
                seamless_donations_redirect_to_paypal($post_args, $gateway_mode);
                break;
            case 'STRIPE':
                dgx_donate_debug_log('Entering Stripe gateway processing.');
                $cancel_url = get_option('dgx_donate_form_url');
                if (strpos($cancel_url, '?') === false) {
                    $cancel_url .= '?';
                } else {
                    $cancel_url .= '&';
                }
                $cancel_url .= 'cancel=true&sessionid=' . $session_id;

                $stripe_data = seamless_donations_redirect_to_stripe($post_data, $stripe_secret_key, $notify_url, $cancel_url);
                if ($stripe_data == NULL) {
                    wp_redirect($cancel_url . '?cancel=error');
                    exit;
                }
                seamless_donations_stripe_js_redirect($stripe_data);
                break;
        }
    }
}

function seamless_donations_check_payment_nonce() {
    $nonce_bypass = get_option('dgx_donate_ignore_form_nonce');
    if ($nonce_bypass != '1') {
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'dgx-donate-nonce')) {
            $nonce_error = 'Payment process nonce validation failure. ';
            $nonce_error .= 'Consider turning on Ignore Form Nonce Value in the Seamless Donations ';
            $nonce_error .= 'Settings tab under Host Compatibility Options.';
            dgx_donate_debug_log($nonce_error);
            die('Access Denied. See Seamless Donations log for details.');
        } else {
            dgx_donate_debug_log("Payment process nonce $nonce validated.");
        }
    }
}

function seamless_donations_check_preexisting_payment_session_data($session_id) {
    $sd4_mode = get_option('dgx_donate_start_in_sd4_mode');
    // now attempt to retrieve session data to see if it already exists (which would trigger an error)
    if ($sd4_mode == false) {
        // use the old transient system
        $session_data = get_transient($session_id);
        dgx_donate_debug_log('Looking for pre-existing session data (legacy transient mode): ' . $session_id);
    } else {
        // use the new guid/audit db system
        $session_data = seamless_donations_get_audit_option($session_id);
        dgx_donate_debug_log('Looking for pre-existing session data (guid/audit db mode): ' . $session_id);
    }
    return $session_data;
}

function seamless_donations_perform_captcha_check($post_data) {
    // insert extra validation for GoodByeCaptcha and any other validation
    $challenge_response_passed = apply_filters('seamless_donations_challenge_response_request', true, $post_data);

    if (true !== $challenge_response_passed) // for sure there is an error
    {
        if (is_wp_error($challenge_response_passed)) {
            $error_message = $challenge_response_passed->get_error_message();
        } else {
            $error_message = (string)$challenge_response_passed;
        }
        dgx_donate_debug_log('Form challenge-response failed:' . $error_message);
        die(esc_html__('Invalid response to challenge. Are you human?'));
    }
}

function seamless_donations_save_payment_transaction_data_for_audit($sd4_mode, $post_data, $session_id) {
    if ($sd4_mode == false) {
        // Save it all in a transient
        $transient_token = $post_data['SESSIONID'];
        set_transient($transient_token, $post_data, 7 * 24 * 60 * 60); // 7 days
        dgx_donate_debug_log('Saving transaction data using legacy mode');
    } else {
        seamless_donations_update_audit_option($session_id, $post_data);
        dgx_donate_debug_log('Saving transaction data using guid/audit db mode');
    }
}

function seamless_donations_repack_payment_form_data_for_transmission_to_gateways() {
    // Repack the POST
    $post_data = array();

    $organization_name = get_option('dgx_donate_organization_name');
    if ($organization_name == false) {
        $organization_name = '';
    }
    $post_data['ORGANIZATION'] = $organization_name;

    if (isset($_POST['_dgx_donate_redirect_url'])) {
        $post_data['REFERRINGURL'] = $_POST['_dgx_donate_redirect_url'];
    } else {
        $post_data['REFERRINGURL'] = '';
    }
    if (isset($_POST['_dgx_donate_success_url'])) {
        $post_data['SUCCESSURL'] = $_POST['_dgx_donate_success_url'];
    } else {
        $post_data['SUCCESSURL'] = '';
    }
    if (isset($_POST['_dgx_donate_session_id'])) {
        $post_data['SESSIONID'] = $_POST['_dgx_donate_session_id'];
    } else {
        $post_data['SESSIONID'] = '';
    }
    if (isset($_POST['_dgx_donate_repeating'])) {
        $post_data['REPEATING'] = $_POST['_dgx_donate_repeating'];
    } else {
        $post_data['REPEATING'] = '';
    }
    if (isset($_POST['_dgx_donate_designated'])) {
        $post_data['DESIGNATED'] = $_POST['_dgx_donate_designated'];
    } else {
        $post_data['DESIGNATED'] = '';
    }
    if (isset($_POST['_dgx_donate_designated_fund'])) {
        $post_data['DESIGNATEDFUND'] = $_POST['_dgx_donate_designated_fund'];
    } else {
        $post_data['DESIGNATEDFUND'] = '';
    }
    if (isset($_POST['_dgx_donate_tribute_gift'])) {
        $post_data['TRIBUTEGIFT'] = $_POST['_dgx_donate_tribute_gift'];
    } else {
        $post_data['TRIBUTEGIFT'] = '';
    }
    if (isset($_POST['_dgx_donate_memorial_gift'])) {
        $post_data['MEMORIALGIFT'] = $_POST['_dgx_donate_memorial_gift'];
    } else {
        $post_data['MEMORIALGIFT'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_name'])) {
        $post_data['HONOREENAME'] = $_POST['_dgx_donate_honoree_name'];
    } else {
        $post_data['HONOREENAME'] = '';
    }
    if (isset($_POST['_dgx_donate_honor_by_email'])) {
        $post_data['HONORBYEMAIL'] = $_POST['_dgx_donate_honor_by_email'];
    } else {
        $post_data['HONORBYEMAIL'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_email'])) {
        $post_data['HONOREEEMAIL'] = $_POST['_dgx_donate_honoree_email'];
    } else {
        $post_data['HONOREEEMAIL'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_address'])) {
        $post_data['HONOREEADDRESS'] = $_POST['_dgx_donate_honoree_address'];
    } else {
        $post_data['HONOREEADDRESS'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_city'])) {
        $post_data['HONOREECITY'] = $_POST['_dgx_donate_honoree_city'];
    } else {
        $post_data['HONOREECITY'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_state'])) {
        $post_data['HONOREESTATE'] = $_POST['_dgx_donate_honoree_state'];
    } else {
        $post_data['HONOREESTATE'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_province'])) {
        $post_data['HONOREEPROVINCE'] = $_POST['_dgx_donate_honoree_province'];
    } else {
        $post_data['HONOREEPROVINCE'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_country'])) {
        $post_data['HONOREECOUNTRY'] = $_POST['_dgx_donate_honoree_country'];
    } else {
        $post_data['HONOREECOUNTRY'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_zip'])) {
        $post_data['HONOREEZIP'] = $_POST['_dgx_donate_honoree_zip'];
    } else {
        $post_data['HONOREEZIP'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_email_name'])) {
        $post_data['HONOREEEMAILNAME'] = $_POST['_dgx_donate_honoree_email_name'];
    } else {
        $post_data['HONOREEEMAILNAME'] = '';
    }
    if (isset($_POST['_dgx_donate_honoree_post_name'])) {
        $post_data['HONOREEPOSTNAME'] = $_POST['_dgx_donate_honoree_post_name'];
    } else {
        $post_data['HONOREEPOSTNAME'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_first_name'])) {
        $post_data['FIRSTNAME'] = $_POST['_dgx_donate_donor_first_name'];
    } else {
        $post_data['FIRSTNAME'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_last_name'])) {
        $post_data['LASTNAME'] = $_POST['_dgx_donate_donor_last_name'];
    } else {
        $post_data['LASTNAME'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_phone'])) {
        $post_data['PHONE'] = $_POST['_dgx_donate_donor_phone'];
    } else {
        $post_data['PHONE'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_email'])) {
        $post_data['EMAIL'] = $_POST['_dgx_donate_donor_email'];
    } else {
        $post_data['EMAIL'] = '';
    }
    if (isset($_POST['_dgx_donate_add_to_mailing_list'])) {
        $post_data['ADDTOMAILINGLIST'] = $_POST['_dgx_donate_add_to_mailing_list'];
    } else {
        $post_data['ADDTOMAILINGLIST'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_address'])) {
        $post_data['ADDRESS'] = $_POST['_dgx_donate_donor_address'];
    } else {
        $post_data['ADDRESS'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_address2'])) {
        $post_data['ADDRESS2'] = $_POST['_dgx_donate_donor_address2'];
    } else {
        $post_data['ADDRESS2'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_city'])) {
        $post_data['CITY'] = $_POST['_dgx_donate_donor_city'];
    } else {
        $post_data['CITY'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_state'])) {
        $post_data['STATE'] = $_POST['_dgx_donate_donor_state'];
    } else {
        $post_data['STATE'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_province'])) {
        $post_data['PROVINCE'] = $_POST['_dgx_donate_donor_province'];
    } else {
        $post_data['PROVINCE'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_country'])) {
        $post_data['COUNTRY'] = $_POST['_dgx_donate_donor_country'];
    } else {
        $post_data['COUNTRY'] = '';
    }
    if (isset($_POST['_dgx_donate_donor_zip'])) {
        $post_data['ZIP'] = $_POST['_dgx_donate_donor_zip'];
    } else {
        $post_data['ZIP'] = '';
    }
    if (isset($_POST['_dgx_donate_increase_to_cover'])) {
        $post_data['INCREASETOCOVER'] = $_POST['_dgx_donate_increase_to_cover'];
    } else {
        $post_data['INCREASETOCOVER'] = '';
    }
    if (isset($_POST['_dgx_donate_anonymous'])) {
        $post_data['ANONYMOUS'] = $_POST['_dgx_donate_anonymous'];
    } else {
        $post_data['ANONYMOUS'] = '';
    }
    if (isset($_POST['_dgx_donate_employer_match'])) {
        $post_data['EMPLOYERMATCH'] = $_POST['_dgx_donate_employer_match'];
    } else {
        $post_data['EMPLOYERMATCH'] = '';
    }
    if (isset($_POST['_dgx_donate_employer_name'])) {
        $post_data['EMPLOYERNAME'] = $_POST['_dgx_donate_employer_name'];
    } else {
        $post_data['EMPLOYERNAME'] = '';
    }
    if (isset($_POST['_dgx_donate_occupation'])) {
        $post_data['OCCUPATION'] = $_POST['_dgx_donate_occupation'];
    } else {
        $post_data['OCCUPATION'] = '';
    }
    if (isset($_POST['_dgx_donate_uk_gift_aid'])) {
        $post_data['UKGIFTAID'] = $_POST['_dgx_donate_uk_gift_aid'];
    } else {
        $post_data['UKGIFTAID'] = '';
    }
    if (isset($_POST['nonce'])) {
        $post_data['NONCE'] = $_POST['nonce'];
    } else {
        $post_data['NONCE'] = '';
    }

    // pull override data from hidden form (might be modified by users with callbacks)
    if (isset($_POST['business'])) {
        $post_data['BUSINESS'] = $_POST['business'];
    } else {
        $post_data['BUSINESS'] = '';
    }
    if (isset($_POST['return'])) {
        $post_data['RETURN'] = $_POST['return'];
    } else {
        $post_data['RETURN'] = '';
    }
    if (isset($_POST['notify_url'])) {
        $post_data['NOTIFY_URL'] = $_POST['notify_url'];
    } else {
        $post_data['NOTIFY_URL'] = '';
    }
    if (isset($_POST['item_name'])) {
        $post_data['ITEM_NAME'] = $_POST['item_name'];
    } else {
        $post_data['ITEM_NAME'] = '';
    }

    // PAYPAL ENCODINGS
    if (isset($_POST['cmd'])) {
        $post_data['CMD'] = $_POST['cmd'];
    } else {
        $post_data['CMD'] = '';
    }
    if (isset($_POST['p3'])) {
        $post_data['P3'] = $_POST['p3'];
    } else {
        $post_data['P3'] = '';
    }
    if (isset($_POST['t3'])) {
        $post_data['T3'] = $_POST['t3'];
    } else {
        $post_data['T3'] = '';
    }
    if (isset($_POST['a3'])) {
        ;
        $post_data['A3'] = $_POST['a3'];
    } else {
        $post_data['A3'] = '';
    }

    // Resolve the donation amount
    if (strcasecmp($_POST['_dgx_donate_amount'], "OTHER") == 0) {
        $post_data['AMOUNT'] = floatval($_POST['_dgx_donate_user_amount']);
    } else {
        $post_data['AMOUNT'] = floatval($_POST['_dgx_donate_amount']);
    }
    if ($post_data['AMOUNT'] < 1.00) {
        $post_data['AMOUNT'] = 1.00;
    }

    if ('US' == $post_data['HONOREECOUNTRY']) {
        $post_data['PROVINCE'] = '';
    } else if ('CA' == $post_data['HONOREECOUNTRY']) {
        $post_data['HONOREESTATE'] = '';
    } else {
        $post_data['HONOREESTATE']    = '';
        $post_data['HONOREEPROVINCE'] = '';
    }

    // If no country entered, pull in the default
    if ($post_data['COUNTRY'] == '') {
        $post_data['COUNTRY'] = get_option('dgx_donate_default_country');
    }

    if ('US' == $post_data['COUNTRY']) {
        $post_data['PROVINCE'] = '';
    } else if ('CA' == $post_data['COUNTRY']) {
        $post_data['STATE'] = '';
    } else {
        $post_data['STATE']    = '';
        $post_data['PROVINCE'] = '';
    }

    $gateway = get_option('dgx_donate_payment_processor_choice');
    if ($gateway == false) {
        $gateway = 'PayPal';
    }
    $post_data['PAYMENTMETHOD'] = $gateway;
    $post_data['SDVERSION']     = dgx_donate_get_version();

    // Sanitize the data (remove leading, trailing spaces quotes, brackets)
    foreach ($post_data as $key => $value) {
        $temp            = trim($value);
        $temp            = str_replace("\"", "", $temp);
        $temp            = strip_tags($temp);
        $post_data[$key] = $temp;
    }
    // account for different permalink styles
    $success_url = $post_data['SUCCESSURL'];
    $qmark       = strpos($success_url, '?');
    if ($qmark === false) {
        $success_url .= "?thanks=true";
        $success_url .= "&sessionid=" . $post_data['SESSIONID'];
    } else {
        $success_url .= "&thanks=true";
        $success_url .= "&sessionid=" . $post_data['SESSIONID'];
    }
    $post_data['RETURN'] = $success_url;
    dgx_donate_debug_log("Success URL: $success_url");

    return $post_data;
}

function seamless_donations_obscurify_donor_name($post_data) {
    $obscurify = get_option('dgx_donate_log_obscure_name'); // false if not set
    if ($obscurify == '1') {
        // obscurify for privacy
        $donor_name = strtolower($post_data['FIRSTNAME'] . $post_data['LASTNAME']);
        $donor_name = seamless_donations_obscurify_string($donor_name, '*', false);
    } else {
        $donor_name = $post_data['FIRSTNAME'] . ' ' . $post_data['LASTNAME'];
    }
    return $donor_name;
}

function seamless_donations_build_paypal_query_string($post_data, $notify_url) {
    // new posting code
    // Build the PayPal query string
    dgx_donate_debug_log("Building PayPal query string...");
    $post_args = "?";

    $post_args .= "first_name=" . urlencode($post_data['FIRSTNAME']) . "&";
    $post_args .= "last_name=" . urlencode($post_data['LASTNAME']) . "&";
    $post_args .= "address1=" . urlencode($post_data['ADDRESS']) . "&";
    $post_args .= "address2=" . urlencode($post_data['ADDRESS2']) . "&";
    $post_args .= "city=" . urlencode($post_data['CITY']) . "&";
    $post_args .= "zip=" . urlencode($post_data['ZIP']) . "&";

    if ('US' == $post_data['COUNTRY']) {
        $post_args .= "state=" . urlencode($post_data['STATE']) . "&";
    } else {
        if ('CA' == $post_data['COUNTRY']) {
            $post_args .= "state=" . urlencode($post_data['PROVINCE']) . "&";
        }
    }

    $post_args .= "country=" . urlencode($post_data['COUNTRY']) . "&";
    $post_args .= "email=" . urlencode($post_data['EMAIL']) . "&";
    $post_args .= "custom=" . urlencode($post_data['SESSIONID']) . "&";

    // fill in repeating data, overriding if necessary
    dgx_donate_debug_log("Checking for repeat. REPEAT value is [" . $post_data['REPEATING'] . "].");
    if ($post_data['REPEATING'] == '') {
        if ($post_data['CMD'] == '') {
            $post_data['CMD'] = '_donations';
        }
        $post_args .= "amount=" . urlencode($post_data['AMOUNT']) . "&";
        $post_args .= "cmd=" . urlencode($post_data['CMD']) . "&";
    } else {
        if ($post_data['CMD'] == '') {
            $post_data['CMD'] = '_xclick-subscriptions';
        }
        if ($post_data['P3'] == '') {
            $post_data['P3'] = '1';
        }
        if ($post_data['T3'] == '') {
            $post_data['T3'] = 'M';
        }

        $post_args .= "cmd=" . urlencode($post_data['CMD']) . "&";
        $post_args .= "p3=" . urlencode($post_data['P3']) . "&";  // 1, M = monthly
        $post_args .= "t3=" . urlencode($post_data['T3']) . "&";
        $post_args .= "src=1&sra=1&"; // repeat until cancelled, retry on failure
        $post_args .= "a3=" . urlencode($post_data['AMOUNT']) . "&";
        $log_msg   = "Enabling repeating donation, cmd=" . $post_data['CMD'];
        $log_msg   .= ", p3=" . $post_data['P3'] . ", t3=" . $post_data['T3'];
        $log_msg   .= ", a3=" . $post_data['AMOUNT'];
        dgx_donate_debug_log($log_msg);
    }

    $paypal_email  = get_option('dgx_donate_paypal_email');
    $currency_code = get_option('dgx_donate_currency');

    // fill in the rest of the form data, overriding if necessary
    if ($post_data['BUSINESS'] == '') {
        $post_data['BUSINESS'] = $paypal_email;
    }
    if ($post_data['NOTIFY_URL'] == '') {
        $post_data['NOTIFY_URL'] = $notify_url;
    }
    dgx_donate_debug_log("Computed RETURN value: '" . $post_data['RETURN'] . "'");

    $post_args .= "business=" . urlencode($post_data['BUSINESS']) . "&";
    $post_args .= "return=" . urlencode($post_data['RETURN']) . "&";
    $post_args .= "notify_url=" . urlencode($post_data['NOTIFY_URL']) . "&";
    $post_args .= "item_name=" . urlencode($post_data['ITEM_NAME']) . "&";
    $post_args .= "quantity=" . urlencode('1') . "&";
    $post_args .= "currency_code=" . urlencode($currency_code) . "&";
    $post_args .= "no_note=" . urlencode('1') . "&";
    $post_args .= "bn=" . urlencode('SeamlessDonations_SP') . "&";
    $post_args = apply_filters('seamless_donations_paypal_checkout_data', $post_args);

    dgx_donate_debug_log("Returning PayPal query string.");
    return $post_args;
}

function seamless_donations_build_donation_description($post_data) {
    // build the description
    $desc  = 'Donation by ';
    $donor = $post_data["FIRSTNAME"] . ' ' . $post_data["LASTNAME"];
    if (isset($post_data["ANONYMOUS"])) {
        if ($post_data["ANONYMOUS"] == 'on') {
            $donor = 'Anonymous';
        }
    }
    $desc .= $donor;
    if (isset($post_data['ORGANIZATION'])) {
        if ($post_data['ORGANIZATION'] != '') {
            $desc .= ' to ' . $post_data['ORGANIZATION'];
        }
    }
    if (isset($post_data["DESIGNATEDFUND"])) {
        $fund_id = $post_data["DESIGNATEDFUND"];
        $fund    = get_post($fund_id);
        if ($fund != NULL) {
            $fund_title = $fund->post_title;
            if ($fund_title != '') {
                $desc .= ' (' . $fund_title . ')';
            }
        }
    }
    if (isset($post_data["HONOREENAME"])) {
        if ($post_data["HONOREENAME"] != '') {
            $honor = false;
            if (isset($post_data["MEMORIALGIFT"])) {
                if ($post_data["MEMORIALGIFT"] == 'on') {
                    $desc  .= ' in memory of';
                    $honor = true;
                }
            }
            if (!$honor) {
                if (isset($post_data["TRIBUTEGIFT"])) {
                    if ($post_data["TRIBUTEGIFT"] == 'on') {
                        $desc  .= ' in honor of';
                        $honor = true;
                    }
                }
            }
            if ($honor) {
                $desc .= ' ' . $post_data["HONOREENAME"];
            }
        }
    }
    $desc = sanitize_text_field($desc);

    return $desc;
}

function seamless_donations_redirect_to_paypal($post_args, $paypal_server) {
    dgx_donate_debug_log("Redirecting to PayPal... now!");
    if ($paypal_server == "SANDBOX") {
        $form_action = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    } else {
        $form_action = "https://www.paypal.com/cgi-bin/webscr";
    }

    wp_redirect($form_action . $post_args);
    exit;
}

function seamless_donations_redirect_to_stripe($post_data, $api_key, $notify_url, $cancel_url) {
    $session = false;
    dgx_donate_debug_log('Preparing Stripe donation description...');
    $desc = seamless_donations_build_donation_description($post_data);

    dgx_donate_debug_log('Preparing redirect to Stripe...');
    dgx_donate_debug_log('-- Using API key: ' . seamless_donations_obscurify_stripe_key($api_key));
    \Stripe\Stripe::setApiKey($api_key);
    dgx_donate_debug_log('Completed setting Stripe API key.');

    $billing_address_collection = get_option('dgx_donate_stripe_billing_address');
    if ($post_data['REPEATING'] != '') {
        // this is a recurring donation
        $donation = [
            'billing_address_collection' => $billing_address_collection,
            'payment_method_types'       => ['card'],
            'line_items'                 => [
                [
                    'price_data'  => [
                        'currency'     => get_option('dgx_donate_currency'),
                        'product_data' => [
                            'name' => $desc,
                        ],
                        'recurring'    => [
                            'interval' => 'month',
                        ],
                        'unit_amount'  => $post_data['AMOUNT'] * 100,
                    ],
                    'quantity'    => 1,
                    'description' => $desc,
                ],
            ],
            'mode'                       => 'subscription',
            'metadata'                   => [
                'sd_session_id' => $post_data['SESSIONID'],
            ],
            'success_url'                => $post_data['RETURN'],
            'cancel_url'                 => $cancel_url,
            'customer_email'             => $post_data["EMAIL"],
        ];
    } else {
        $donation = [
            'billing_address_collection' => $billing_address_collection,
            'payment_method_types'       => ['card'],
            'line_items'                 => [
                [
                    'name'     => $desc,
                    //'description' => $desc,
                    'amount'   => $post_data['AMOUNT'] * 100,
                    'currency' => get_option('dgx_donate_currency'),
                    'quantity' => 1,
                ],
            ],
            'metadata'                   => [
                'sd_session_id' => $post_data['SESSIONID'],
            ],
            'success_url'                => $post_data['RETURN'],
            'cancel_url'                 => $cancel_url,
            'customer_email'             => $post_data["EMAIL"],
            'submit_type'                => 'donate',
        ];
    }
    $donation = apply_filters('seamless_donations_stripe_checkout_data', $donation);

    // https://www.php.net/manual/en/class.exception.php
    try {
        // Use Stripe's library to make requests
        $session = \Stripe\Checkout\Session::create($donation);
    } catch (\Stripe\Exception\CardException $e) {
        // Since it's a decline, \Stripe\Exception\CardException will be caught
        dgx_donate_debug_log('Status is:' . $e->getHttpStatus());
        dgx_donate_debug_log('Type is:' . $e->getError()->type);
        dgx_donate_debug_log('Code is:' . $e->getError()->code);
        // param is '' in this case
        dgx_donate_debug_log('Param is:' . $e->getError()->param);
        dgx_donate_debug_log('Message is:' . $e->getError()->message);
    } catch (\Stripe\Exception\RateLimitException $e) {
        dgx_donate_debug_log("Too many requests made to the API too quickly");
        dgx_donate_debug_log('-- Stripe message is: ' . $e->getMessage());
        // Too many requests made to the API too quickly
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        dgx_donate_debug_log("Invalid parameters were supplied to Stripe API");
        dgx_donate_debug_log('-- Stripe message is: ' . $e->getMessage());
        // Invalid parameters were supplied to Stripe's API
    } catch (\Stripe\Exception\AuthenticationException $e) {
        dgx_donate_debug_log("Authentication with Stripe API failed");
        dgx_donate_debug_log('-- Stripe message is: ' . $e->getMessage());
        // Authentication with Stripe's API failed
        // (maybe you changed API keys recently)
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        dgx_donate_debug_log("Network communication with Stripe failed");
        dgx_donate_debug_log('-- Stripe message is: ' . $e->getMessage());
        // Network communication with Stripe failed
    } catch (\Stripe\Exception\ApiErrorException $e) {
        dgx_donate_debug_log("Stripe API error exception.");
        dgx_donate_debug_log('-- Stripe message is: ' . $e->getMessage());
        // Display a very generic error to the user, and maybe send
        // yourself an email
    } catch (Exception $e) {
        dgx_donate_debug_log("A Stripe invocation failure occurred unrelated to Stripe functionality.");
        dgx_donate_debug_log('-- Stripe message is: ' . $e->getMessage());
        // Something else happened, completely unrelated to Stripe
    }

    //    https://stackoverflow.com/questions/17750143/catching-stripe-errors-with-try-catch-php-method
    //    $body = $e->getJsonBody();
    //    $err  = $body['error'];
    //
    //    print('Status is:' . $e->getHttpStatus() . "\n");
    //    print('Type is:' . $err['type'] . "\n");
    //    print('Code is:' . $err['code'] . "\n");
    //    // param is '' in this case
    //    print('Param is:' . $err['param'] . "\n");
    //    print('Message is:' . $err['message'] . "\n");

    return $session;
}

function seamless_donations_init_payment_gateways() {
    $payment_gateway = get_option('dgx_donate_payment_processor_choice');
    if ($payment_gateway == 'STRIPE') {
        if (!is_admin()) {
            // we only need to run this on client-facing pages
            $gateway_mode = get_option('dgx_donate_stripe_server');
            if ($gateway_mode == 'LIVE') {
                $stripe_api_key    = get_option('dgx_donate_live_stripe_api_key');
                $stripe_secret_key = get_option('dgx_donate_live_stripe_secret_key');
            } else {
                $stripe_api_key    = get_option('dgx_donate_test_stripe_api_key');
                $stripe_secret_key = get_option('dgx_donate_test_stripe_secret_key');
            }
            seamless_donations_init_stripe($stripe_api_key);
        }
    }
}

function seamless_donations_stripe_js_redirect($session) {
    $stripe_mode = get_option('dgx_donate_stripe_server');
    if ($stripe_mode == 'LIVE') {
        $api_key = get_option('dgx_donate_live_stripe_api_key');
    } else {
        $api_key = get_option('dgx_donate_test_stripe_api_key');
    }

    dgx_donate_debug_log('Entering stripe js test with mode ' . $stripe_mode);
    dgx_donate_debug_log('Stripe session id: ' . $session['id']);

    ?>
    <script src='https://js.stripe.com/v3/?ver=5.4.1'></script>
    <script>
        console.log('JS Stripe redirect');
        try {
            var stripe = Stripe(<?php echo '\'' . $api_key . '\''; ?>);
            stripe.redirectToCheckout({
                // Make the id field from the Checkout Session creation API response
                // available to this file, so you can provide it as parameter here
                // instead of the {{CHECKOUT_SESSION_ID}} placeholder.
                <?php
                echo 'sessionId: \'' . $session['id'] . '\'';
                ?>
            })
                .then(function (result) {
                    if (result.error) {
                        // Error scenario 1
                        // If `redirectToCheckout` fails due to a browser or network
                        // error, display the localized error message to your customer.
                        alert(result.error.message);
                    }
                }).catch(function (error) {
                if (result.error) {
                    // Error scenario 2
                    // If the promise throws an error
                    alert("We are experiencing issues connecting to our"
                        + " payments provider. " + error);
                }
            });
        } catch (error) {
            // Error scenario 3
            // If there is no internet connection at all
            alert("We are experiencing issues connecting to our"
                + " payments provider. You have not been charged. Please check"
                + " your internet connection and try again. If the problem"
                + " persists please contact us.");
        }
    </script>
    <?php
}

function seamless_donations_provisionally_process_gateway_result() {
    if (isset($_GET['thanks'])) {
        $gateway = get_option('dgx_donate_payment_processor_choice');
        if ($gateway == 'STRIPE') {
            $result = seamless_donations_stripe_check_for_successful_transaction();
        }
    }
}

function seamless_donations_stripe_check_for_successful_transaction() {
    // https://stripe.com/docs/payments/checkout/accept-a-payment#payment-success
    // https://stripe.com/docs/cli/flags

    dgx_donate_debug_log('Entering Stripe checking for successful transaction');

    $donation_succeeded  = false;
    $donation_session_id = $_GET["sessionid"];
    $currency_code       = get_option('dgx_donate_currency');
    $server_mode         = get_option('dgx_donate_stripe_server');
    if ($server_mode == 'LIVE') {
        $stripe_secret_key = get_option('dgx_donate_live_stripe_secret_key');
        $endpoint_secret   = get_option('dgx_donate_live_webhook_stripe_secret_key');
    } else {
        $stripe_secret_key = get_option('dgx_donate_test_stripe_secret_key');
        //$stripe_secret_key = get_option('dgx_donate_test_stripe_api_key');
        $endpoint_secret = get_option('dgx_donate_test_webhook_stripe_secret_key');
    }

    // Set your secret key. Remember to switch to your live secret key in production!
    // See your keys here: https://dashboard.stripe.com/account/apikeys
    \Stripe\Stripe::setApiKey($stripe_secret_key);
    dgx_donate_debug_log('Stripe API key set');

    $events = \Stripe\Event::all([
        'type'    => 'checkout.session.completed',
        'created' => [
            // Check for events created in the last 24 hours.
            'gte' => time() - 24 * 60 * 60,
        ],
    ]);

    dgx_donate_debug_log('Checking for donations');

    foreach ($events->autoPagingIterator() as $event) {
        $stripe_session    = $event->data->object;
        $stripe_session_id = $stripe_session->id;
        $stripe_mode       = $stripe_session->mode;
        if ($stripe_mode == 'payment') {
            // record a payment intent ID if a one-off donation
            $stripe_transaction_id = $stripe_session->payment_intent;
        } else {
            // record an invoice ID if a subscription
            $subscription_id       = $stripe_session->subscription;
            $stripe_transaction_id = seamless_donations_stripe_get_latest_invoice_from_subscription($subscription_id);
            seamless_donations_add_audit_string('STRIPE-SUBSCRIPTION-' . $subscription_id, $donation_session_id);
        }
        $sd_session_id = $stripe_session->metadata['sd_session_id'];

        if ($sd_session_id == $donation_session_id) {
            $donation_succeeded = true;
            dgx_donate_debug_log('Donation succeeded');
            break;
        }
    }

    if ($donation_succeeded) {
        seamless_donations_process_confirmed_purchase('STRIPE', $currency_code, $donation_session_id, $stripe_transaction_id, $stripe_session);
    } else {
        dgx_donate_debug_log('Donation not showing as succeeded');
    }
    return 'PASS';
}

function seamless_donations_stripe_poll_last_months_transactions() {
    dgx_donate_debug_log('Entering Stripe polling for unrecorded transactions');

    $server_mode = get_option('dgx_donate_stripe_server');
    if ($server_mode == 'LIVE') {
        $stripe_secret_key = get_option('dgx_donate_live_stripe_secret_key');
    } else {
        $stripe_secret_key = get_option('dgx_donate_test_stripe_secret_key');
    }

    // Set your secret key. Remember to switch to your live secret key in production!
    // See your keys here: https://dashboard.stripe.com/account/apikeys
    \Stripe\Stripe::setApiKey($stripe_secret_key);
    dgx_donate_debug_log('Stripe API key set');

    $invoice_list = seamless_donations_stripe_get_invoice_list_from_payment_intents();

    foreach ($invoice_list as $invoice_id => $subscription_id) {
        $donation_id = get_donations_by_meta('_dgx_donate_transaction_id', $invoice_id, 1);

        if (count($donation_id) == 0) {
            // We haven't seen this transaction ID already

            $original_session_id = seamless_donations_get_audit_option('STRIPE-SUBSCRIPTION-' . $subscription_id);
            if ($original_session_id !== false) {
                // we'll want to copy the contents of the session, pull out data for the call to
                // seamless_donations_process_confirmed_purchase, create a new session id
                // and write the old session data with the new ID to the audit table
                // this for any new subscription entry

                $original_session = seamless_donations_get_audit_option($original_session_id);
                $new_session_id   = seamless_donations_get_guid('sd'); // UUID on server
                seamless_donations_update_audit_option($new_session_id, $original_session);

                dgx_donate_debug_log('Found new recurring donation on Stripe');
                dgx_donate_debug_log('New session ID:' . $new_session_id);

                $currency         = $original_session['CURRENCY'];
                $transaction_data = seamless_donations_stripe_get_invoice($invoice_id);
                seamless_donations_process_confirmed_purchase('STRIPE', $currency, $new_session_id, $invoice_id, $transaction_data);
            }
            $a = 1;
        }
    }

    return 'PASS';
}

function seamless_donations_stripe_sd_5021_fix_uninvoiced_donation_subscriptions() {
    // this should only happen once as part of the Stripe update for 5.0.21
    // initiate Stripe

    $run_update  = false;
    $server_mode = get_option('dgx_donate_stripe_server');
    if ($server_mode == 'LIVE') {
        $stripe_secret_key = get_option('dgx_donate_live_stripe_secret_key');
        $stripe_updated    = get_option('dgx_donate_5021_stripe_invoices_live');
        if (!$stripe_updated) {
            $run_update     = true;
            $plugin_version = 'sd5021';
            update_option('dgx_donate_5021_stripe_invoices_live', $plugin_version);
        }
    } else {
        $stripe_secret_key = get_option('dgx_donate_test_stripe_secret_key');
        $stripe_updated    = get_option('dgx_donate_5021_stripe_invoices_test');
        if (!$stripe_updated) {
            $run_update     = true;
            $plugin_version = 'sd5021';
            update_option('dgx_donate_5021_stripe_invoices_test', $plugin_version);
        }
    }
    if (!$run_update) return;

    \Stripe\Stripe::setApiKey($stripe_secret_key);

    // initiate donation scan
    $count     = -1; // all
    $post_type = 'donation';
    $args      = array(
        'numberposts' => $count,
        'post_type'   => $post_type,
        'orderby'     => 'post_date',
        'order'       => 'DESC',
    );
    // scann all historical donations
    $my_donations = get_posts($args);

    foreach ($my_donations as $donation) {
        $donation_id     = $donation->ID;
        $subscription_id = '';
        $invoice_id      = '';

        // fix the transaction_ids in each donation record
        $transaction_id = get_post_meta($donation_id, '_dgx_donate_transaction_id', true);
        if (strpos($transaction_id, 'sub_', 0) !== false) {
            $subscription_id = $transaction_id;
            $invoice_id      = seamless_donations_stripe_get_first_invoice_from_subscription($subscription_id, 365);
            update_post_meta($donation_id, '_dgx_donate_transaction_id', $invoice_id);
        }
        if (strpos($transaction_id, 'in_', 0) !== false) {
            $invoice_id      = $transaction_id;
            $subscription_id = seamless_donations_stripe_get_subscription_from_invoice($invoice_id);
        }
        if ($transaction_id == NULL or $transaction_id == '') {
            $method1   = get_post_meta($donation_id, '_dgx_donate_payment_method', true);
            $method2   = get_post_meta($donation_id, '_dgx_donate_payment_processor', true);
            $repeating = get_post_meta($donation_id, '_dgx_donate_repeating', true);

            if ($method1 == 'STRIPE' and $method2 == 'STRIPE' and $repeating == 'on') {
                $stripe_data     = get_post_meta($donation_id, '_dgx_donate_payment_processor_data', true);
                $subscription_id = $stripe_data->subscription;
                $invoice_id      = seamless_donations_stripe_get_first_invoice_from_subscription($subscription_id, 365);
                update_post_meta($donation_id, '_dgx_donate_transaction_id', $invoice_id);
            }
        }

        // now fix the audit index for the stripe subscription
        if ($invoice_id != '') {
            $session_id = get_post_meta($donation_id, '_dgx_donate_session_id', true);
            $audit_key  = 'STRIPE-SUBSCRIPTION-' . $subscription_id;
            if (seamless_donations_get_audit_option($audit_key) == false) {
                // doesn't exist, so add
                seamless_donations_add_audit_string($audit_key, $session_id);
            }
        }
    }
}

function seamless_donations_process_confirmed_purchase($gateway, $currency, $donation_session_id, $transaction_id, $transaction_data) {
    $sd4_mode = get_option('dgx_donate_start_in_sd4_mode');
    dgx_donate_debug_log($gateway . ' TRANSACTION VERIFIED for session ID ' . $donation_session_id);

    // Check if we've already logged a transaction with this same transaction id
    $donation_id = get_donations_by_meta('_dgx_donate_transaction_id', $transaction_id, 1);

    if (count($donation_id) == 0) {
        // We haven't seen this transaction ID already

        // See if a donation for this session ID already exists
        $donation_id = get_donations_by_meta('_dgx_donate_session_id', $donation_session_id, 1);

        if (count($donation_id) == 0) {
            // We haven't seen this session ID already

            // Retrieve the data
            if ($sd4_mode == false) {
                // retrieve from transient
                $donation_form_data = get_transient($donation_session_id);
            } else {
                // retrieve from audit db table
                $donation_form_data = seamless_donations_get_audit_option($donation_session_id);
            }

            if (!empty($donation_form_data)) {
                // Create a donation record
                if ($sd4_mode == false) {
                    dgx_donate_debug_log(
                        "Creating donation from transient data in pre-4.x mode.");
                    $donation_id = dgx_donate_create_donation_from_transient_data($donation_form_data);
                } else {
                    dgx_donate_debug_log("Creating donation from transaction audit data in 4.x mode.");
                    $donation_id = seamless_donations_create_donation_from_transaction_audit_table(
                        $donation_form_data);
                }
                dgx_donate_debug_log(
                    "Created donation {$donation_id} for session ID {$donation_session_id}");

                if ($sd4_mode == false) {
                    // Clear the transient
                    delete_transient($donation_session_id);
                }
            } else {
                // We have a session_id but no transient (the admin might have
                // deleted all previous donations in a recurring donation for
                // some reason) - so we will have to create a donation record
                // from the data supplied by PayPal
                if ($sd4_mode == false) {
                    $donation_id = dgx_donate_create_donation_from_paypal_data($transaction_data);
                    dgx_donate_debug_log(
                        "Created donation {$donation_id} " .
                        "from PayPal data (no transient data found) in pre-4.x mode.");
                } else {
                    $donation_id = seamless_donations_create_donation_from_paypal_data();
                    dgx_donate_debug_log(
                        "Created donation {$donation_id} " .
                        "from PayPal data (no audit db data found) in 4.x mode.");
                }
            }
        } else {
            // We have seen this session ID already, create a new donation record for this new transaction

            // But first, flatten the array returned by get_donations_by_meta for _dgx_donate_session_id
            $donation_id = $donation_id[0];

            $old_donation_id = $donation_id;
            if ($sd4_mode == false) {
                $donation_id = dgx_donate_create_donation_from_donation($old_donation_id);
            } else {
                $donation_id = seamless_donations_create_donation_from_donation($old_donation_id);
            }
            dgx_donate_debug_log(
                "Created donation {$donation_id} (recurring donation, donor data copied from donation {$old_donation_id}");
        }
    } else {
        // We've seen this transaction ID already - ignore it
        $donation_id = '';
        dgx_donate_debug_log("Transaction ID {$transaction_id} already handled - ignoring");
    }

    if (!empty($donation_id)) {
        // Update the raw gateway data
        update_post_meta($donation_id, '_dgx_donate_transaction_id', $transaction_id);
        update_post_meta($donation_id, '_dgx_donate_payment_processor', $gateway);
        if ($gateway == 'STRIPE') {
            $stripe_session_id  = $transaction_data->id;
            $stripe_customer_id = $transaction_data->customer;
            update_post_meta($donation_id, '_dgx_donate_stripe_session_id', $stripe_session_id);
            update_post_meta($donation_id, '_dgx_donate_stripe_customer_id', $stripe_customer_id);
        }
        update_post_meta($donation_id, '_dgx_donate_payment_processor_data', $transaction_data);

        dgx_donate_debug_log("Payment currency = {$currency}");
        update_post_meta($donation_id, '_dgx_donate_donation_currency', $currency);
    }

    // @todo - send different notification for recurring?

    // Send admin notification
    dgx_donate_send_donation_notification($donation_id);
    // Send donor notification
    dgx_donate_send_thank_you_email($donation_id);
}

//function seamless_donations_stripe_create_donation_products() {
//
//    $gateway_mode = get_option('dgx_donate_stripe_server');
//    if ($gateway_mode == 'LIVE') {
//        $stripe_api_key    = get_option('dgx_donate_live_stripe_api_key');
//        $stripe_secret_key = get_option('dgx_donate_live_stripe_secret_key');
//    } else {
//        $stripe_api_key    = get_option('dgx_donate_test_stripe_api_key');
//        $stripe_secret_key = get_option('dgx_donate_test_stripe_secret_key');
//    }
//    $session = \Stripe\Product::
//    $stripe = new \Stripe\StripeClient($stripe_secret_key);
//
//    $stripe->products->all(['limit' => 3]);
//
//}