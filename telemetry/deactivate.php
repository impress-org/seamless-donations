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

// based on code from https://github.com/CodeCabin/plugin-deactivation-survey

add_filter('seamless_donations_deactivate_feedback_form_plugins', function ($plugins) {
    $time_now  = time();
    $time_then = get_option('dgx_donate_first_run_time');

    $plugins[] = (object)array(
        'slug'         => 'seamless-donations',
        'version'      => get_option('dgx_donate_active_version'),
        'timeNow'      => $time_now,
        'installTime'  => $time_then,
        'useDuration'  => $time_now - $time_then,
        'telemetryUrl' => seamless_donations_telemetry_url(),
    );
    return $plugins;
});

if (!is_admin())
    return;

global $pagenow;

if ($pagenow != "plugins.php")
    return;

if (defined('SEAMLESS_DONATIONS_DEACTIVATE_FEEDBACK_FORM_INCLUDED'))
    return;
define('SEAMLESS_DONATIONS_DEACTIVATE_FEEDBACK_FORM_INCLUDED', true);

add_action('admin_enqueue_scripts', function () {
    // Enqueue scripts
    wp_enqueue_script('seamless-donations-deactivate-feedback-form', plugin_dir_url(__FILE__) . 'js/deactivate.js');
    wp_enqueue_style('seamless-donations-deactivate-feedback-form', plugin_dir_url(__FILE__) . 'css/deactivate.css');

    // Localized strings
    wp_localize_script('seamless-donations-deactivate-feedback-form', 'seamless_donations_deactivate_feedback_form_strings', array(
        'quick_feedback'      => __('Help us improve. Why are you deactivating?', 'seamless-donations'),
        'foreword'            => __('Your feedback is fully anonymous and will be read directly by the lead developer', 'seamless-donations'),
        'better_plugins_name' => __('Please tell us which plugin?', 'seamless-donations'),
        'please_tell_us'      => __('Please tell us the reason so we can improve the plugin', 'seamless-donations'),
        'do_not_attach_email' => __('Do not send my e-mail address with this feedback', 'seamless-donations'),

        'brief_description' => __('Please share any feedback you wish', 'seamless-donations'),

        'cancel'                => __('Cancel', 'seamless-donations'),
        'skip_and_deactivate'   => __('Skip &amp; Deactivate', 'seamless-donations'),
        'submit_and_deactivate' => __('Submit &amp; Deactivate', 'seamless-donations'),
        'please_wait'           => __('Please wait', 'seamless-donations'),
        'thank_you'             => __('Thank you!', 'seamless-donations'),
    ));

    // Plugins
    $plugins = apply_filters('seamless_donations_deactivate_feedback_form_plugins', array());

    // Reasons
    $defaultReasons = array(
        'no-longer-needed'       => __('I don\'t need Seamless Donations any more', 'seamless-donations'),
        'missing-feature'        => __('Seamless Donations is missing a feature I need', 'seamless-donations'),
        'not-get-to-work'        => __('I couldn\'t get it to work right', 'seamless-donations'),
        'found-better-plugin'    => __('I found a plugin I like better', 'seamless-donations'),
        'plugin-broke-site'      => __('Seamless Donations broke my site', 'seamless-donations'),
        'short-period'           => __('I only needed Seamless Donations for a short period', 'seamless-donations'),
        'temporary-deactivation' => __('It\'s a temporary deactivation. I\'m troubleshooting', 'seamless-donations'),
        'other'                  => __('Other', 'seamless-donations'),
    );

    foreach ($plugins as $plugin) {
        $plugin->reasons = apply_filters('seamless_donations_deactivate_feedback_form_reasons', $defaultReasons, $plugin);
    }

    // Send plugin data
    wp_localize_script('seamless-donations-deactivate-feedback-form', 'seamless_donations_deactivate_feedback_form_plugins', $plugins);
});

/**
 * Hook for adding plugins, pass an array of objects in the following format:
 *  'slug'        => 'plugin-slug'
 *  'version'    => 'plugin-version'
 * @return array The plugins in the format described above
 */
add_filter('seamless_donations_deactivate_feedback_form_plugins', function ($plugins) {
    return $plugins;
});

