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

function seamless_donations_stripe_get_payment_intent($payment_id) {
    $intent = \Stripe\PaymentIntent::retrieve(
        $payment_id,
        []
    );
    return $intent;
}

function seamless_donations_stripe_get_invoice_from_payment_intent($payment_id) {
    $intent     = seamless_donations_stripe_get_payment_intent($payment_id);
    $invoice_id = $intent->invoice;
    return $invoice_id;
}

function seamless_donations_stripe_get_invoice_list_from_payment_intents($days = 30) {
    $intent_array = array();
    $intent_list  = \Stripe\PaymentIntent::all([
        'created' => [
            // Check for subscriptions created in the last year.
            'gte' => time() - $days * 24 * 60 * 60,
        ],
    ]);
    foreach ($intent_list->autoPagingIterator() as $intent) {
        if (isset($intent->invoice)) {
            $invoice_id                = $intent->invoice;
            $subscription_id           = seamless_donations_stripe_get_subscription_from_invoice($invoice_id);
            $intent_array[$invoice_id] = $subscription_id;
        }
    }
    return $intent_array;
}

function seamless_donations_stripe_get_invoice($invoice_id) {
    $invoice = \Stripe\Invoice::retrieve(
        $invoice_id,
        []
    );
    return $invoice;
}

function seamless_donations_stripe_get_subscription_from_invoice($invoice_id) {
    $invoice         = seamless_donations_stripe_get_invoice($invoice_id);
    $subscription_id = $invoice->subscription;
    return $subscription_id;
}

function seamless_donations_stripe_get_invoice_list_from_subscription($subscription_id, $days = 30) {
    $invoice_array = array();
    $invoice_list  = \Stripe\Invoice::all([
        'created'      => [
            // Check for subscriptions created in the last year.
            'gte' => time() - $days * 24 * 60 * 60,
        ],
        'subscription' => $subscription_id,
    ]);
    foreach ($invoice_list->autoPagingIterator() as $invoice) {
        $reason                     = $invoice->billing_reason;
        $invoice_id                 = $invoice->id;
        $invoice_array[$invoice_id] = $reason;
    }
    return $invoice_array;
}

function seamless_donations_stripe_get_subscription($subscription_id) {
    $subscription = \Stripe\Subscription::retrieve(
        $subscription_id,
        []
    );
    return $subscription;
}

function seamless_donations_stripe_get_latest_invoice_from_subscription($subscription_id) {
    $subscription = seamless_donations_stripe_get_subscription($subscription_id);
    $latest       = $subscription->latest_invoice;
    return $latest;
}

function seamless_donations_stripe_get_first_invoice_from_subscription($subscription_id, $days = 30) {
    $list = seamless_donations_stripe_get_invoice_list_from_subscription($subscription_id, $days);
    foreach ($list as $invoice_id => $status) {
        if ($status == 'subscription_update') {
            return $invoice_id;
        }
        if ($status == 'subscription_create') {
            return $invoice_id;
        }
    }
    return false;
}

function seamless_donations_stripe_is_first_invoice_of_subscription($invoice_id, $days = 30) {
    $subscription_id = seamless_donations_stripe_get_subscription_from_invoice($invoice_id);
    $list            = seamless_donations_stripe_get_invoice_list_from_subscription($subscription_id, $days);
    if ($list[$invoice_id] == 'subscription_update') {
        return true;
    }
    return false;
}