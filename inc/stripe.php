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

function seamless_donations_init_stripe($api_key) {
    \Stripe\Stripe::setAppInfo(
        'WordPress SeamlessDonationsStripe',
        get_option('dgx_donate_active_version'),
        'https://zatzlabs.com',
        'pp_partner_HLBzVKtlNzaGrU'           // Used by Stripe to identify your plugin
    );

    \Stripe\Stripe::setApiKey($api_key);
    \Stripe\Stripe::setApiVersion('2017-06-05');

   wp_enqueue_script('stripe', 'https://js.stripe.com/v3/');

    if (isset($_SERVER['HTTPS'])) {
        // Present an error to the user
    }
}


function xxx_seamless_donations_stripe_test() {
    $https_ipn_url = plugins_url('/pay/stripe/webhook.php', dirname(__FILE__));
    $https_ipn_url = str_ireplace('http://', 'https://', $https_ipn_url); // force https check

    // Set your secret key. Remember to switch to your live secret key in production!
    // See your keys here: https://dashboard.stripe.com/account/apikeys

    // Get API key

    $stripe_mode = get_option('dgx_donate_stripe_server');
    if ($stripe_mode == 'LIVE') {
        $api_key = get_option('dgx_donate_live_stripe_secret_key');
    } else {
        $api_key = get_option('dgx_donate_test_stripe_secret_key');
    }

    \Stripe\Stripe::setApiKey($api_key);

    try {
        // Use Stripe's library to make requests...
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => [
                [
                    'name'        => 'T-shirt',
                    'description' => 'Comfortable cotton t-shirt',
                    //'images' => ['https://example.com/t-shirt.png'],
                    'amount'      => 500,
                    'currency'    => 'usd',
                    'quantity'    => 1,
                ],
            ],
            'success_url'          => $https_ipn_url . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $https_ipn_url . '?boom=cancel',
        ]);
    } catch (\Stripe\Exception\CardException $e) {
        // Since it's a decline, \Stripe\Exception\CardException will be caught
        echo 'Status is:' . $e->getHttpStatus() . '\n';
        echo 'Type is:' . $e->getError()->type . '\n';
        echo 'Code is:' . $e->getError()->code . '\n';
        // param is '' in this case
        echo 'Param is:' . $e->getError()->param . '\n';
        echo 'Message is:' . $e->getError()->message . '\n';
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo "fail Too many requests made to the API too quickly";
        // Too many requests made to the API too quickly
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo "fail Invalid parameters were supplied to Stripe's API";
        // Invalid parameters were supplied to Stripe's API
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo "fail Authentication with Stripe's API failed";
        // Authentication with Stripe's API failed
        // (maybe you changed API keys recently)
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo "fail Network communication with Stripe failed";
        // Network communication with Stripe failed
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo "fail" . isset($e->getError()->message) ? ' ' . $e->getError()->message : '';;
        // Display a very generic error to the user, and maybe send
        // yourself an email
    } catch (Exception $e) {
        echo "fail something else happened that's probably not stripe";
        // Something else happened, completely unrelated to Stripe
    }

    return $session;
}


