<?php
/*
 * Seamless Donations by David Gewirtz, adopted from Allen Snook
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: http://zatzlabs.com/seamless-donations/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2020 by David Gewirtz
 *
 */

function seamless_donations_schedule_crons() {
    add_action('seamless_donations_daily_cron_hook', 'seamless_donations_daily_cron');
    add_action('seamless_donations_hourly_cron_hook', 'seamless_donations_hourly_cron');
    if (!wp_next_scheduled('seamless_donations_daily_cron_hook')) {
        wp_schedule_event(time(), 'daily', 'seamless_donations_daily_cron_hook');
        dgx_donate_cron_log('Daily cron scheduled.');
    }
//    if (!wp_next_scheduled('seamless_donations_hourly_cron_hook')) {
//        wp_schedule_event(time(), 'hourly', 'seamless_donations_hourly_cron_hook');
//        dgx_donate_cron_log('Hourly cron scheduled.');
//    }
}

function seamless_donations_daily_cron() {
    dgx_donate_cron_log('Processing daily cron.');
    $payment_gateway = get_option('dgx_donate_payment_processor_choice');
    if ($payment_gateway == 'STRIPE') {
        // do stripe-related processing
        dgx_donate_cron_log('Initiating daily Stripe transaction download.');
        seamless_donations_stripe_poll_last_months_transactions();
        dgx_donate_cron_log('-- Daily Stripe transaction download complete.');
    }
}

function seamless_donations_hourly_cron() {
    //dgx_donate_cron_log('Processing hourly cron.');
}