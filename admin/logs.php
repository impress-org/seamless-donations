<?php
/**
 *
 * Seamless Donations by David Gewirtz, adopted from Allen Snook
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: http://zatzlabs.com/seamless-donations/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2020 by David Gewirtz
 * /
 */

//	Exit if .php file accessed directly
if (!defined('ABSPATH')) exit;

add_action('cmb2_admin_init', 'seamless_donations_admin_logs_menu');

//// LOGS - MENU ////
function seamless_donations_admin_logs_menu() {
    $args = array(
        'id'           => 'seamless_donations_tab_logs_page',
        'title'        => 'Seamless Donations - Logs',
        // page title
        'menu_title'   => 'Logs',
        // title on left sidebar
        'tab_title'    => 'Logs',
        // title displayed on the tab
        'object_types' => array('options-page'),
        'option_key'   => 'seamless_donations_tab_logs',
        'parent_slug'  => 'seamless_donations_tab_main',
        'tab_group'    => 'seamless_donations_tab_set',
        'save_button'  => 'Delete Log',
    );

    // 'tab_group' property is supported in > 2.4.0.
    if (version_compare(CMB2_VERSION, '2.4.0')) {
        $args['display_cb'] = 'seamless_donations_cmb_options_display_with_tabs';
    }

    do_action('seamless_donations_tab_logs_before', $args);

    // call on button hit for page save
    add_action('admin_post_seamless_donations_tab_logs', 'seamless_donations_tab_logs_process_buttons');

    // clear previous error messages if coming from another page
    seamless_donations_clear_cmb2_submit_button_messages($args['option_key']);

    $args        = apply_filters('seamless_donations_tab_logs_menu', $args);
    $log_options = new_cmb2_box($args);

    if (isset($_REQUEST['page'])) {
        if ($_REQUEST['page'] == 'seamless_donations_tab_logs') {
            seamless_donations_admin_logs_section_ssl($log_options);
            seamless_donations_admin_logs_section_data($log_options);
            seamless_donations_admin_cron_logs_section_data($log_options);

            do_action('seamless_donations_tab_logs_after', $log_options);
        }
    }
}

//// LOGS - SECTION - DATA ////
function seamless_donations_admin_logs_section_ssl($section_options) {
    $gateway = get_option('dgx_donate_payment_processor_choice');

    // the following code is indicative of a minor architectural flaw in Seamless Donations
    // in that all admin pages are always instantiated. The approach doesn't seem to cause
    // too much of a load, except for the following, which calls the IPN processor.
    // This poorly optimized approach is being left in because callbacks might have been
    // used by user code that expected this behavior and changing it could cause breakage
    if (isset($_REQUEST['page'])) {
        if ($_REQUEST['page'] == 'seamless_donations_tab_logs') {
            $security     = seamless_donations_get_security_status();
            $section_desc = seamless_donations_display_tls_status($security);
            $section_desc .= '<BR>Get comprehensive SSL report for ';
            $section_desc .= '<A target="_blank" HREF="https://www.ssllabs.com/ssltest/analyze.html?d=';
            $section_desc .= $security["ipn_domain_url"] . '">' . $security["ipn_domain_url"] . '</A>.';
            if ($gateway == 'PAYPAL') {
                $section_desc .= ' Review up-to-the-minute system operation status for PayPal ';
                $section_desc .= '<A target="_blank" HREF="https://www.paypal-status.com/product/sandbox">Sandbox</A> and ';
                $section_desc .= '<A target="_blank" HREF="https://www.paypal-status.com/product/production">Live</A> ';
                $section_desc .= 'servers.';
            }
            if ($gateway == 'STRIPE') {
                $section_desc .= ' Review up-to-the-minute system operation status for ';
                $section_desc .= '<A target="_blank" HREF="https://status.stripe.com/">Stripe servers</A>.';
            }
        }
    }

    $section_options->add_field(array(
        'name'        => __('Payment Processor Compatibility', 'cmb2'),
        'id'          => 'seamless_donations_log_ssl',
        'type'        => 'title',
        'after_field' => $section_desc,

    ));
    $section_options = apply_filters('seamless_donations_tab_logs_section_ssl', $section_options);
}

//// LOGS - SECTION - DATA ////
function seamless_donations_admin_logs_section_data($section_options) {
    $section_options->add_field(array(
        'name'    => __('Log Data', 'cmb2'),
        'id'      => 'seamless_donations_log_data',
        'type'    => 'title',
        'default' => 'log data',
    ));
    $section_options = apply_filters('seamless_donations_tab_logs_section_data', $section_options);

    $debug_log_option  = get_option('dgx_donate_debug_mode');
    $debug_log_content = get_option('dgx_donate_log');
    $log_data          = '';

    if (empty($debug_log_content)) {
        $log_data = esc_html__('The log is empty.', 'seamless-donations');
    } else {
        foreach ($debug_log_content as $debug_log_entry) {
            if ($log_data != "") {
                $log_data .= "\n";
            }
            if ($debug_log_option == 'RAWLOG') {
                $log_data .= $debug_log_entry;
            } else {
                $log_data .= esc_html($debug_log_entry);
            }
        }
    }

    $debug_mode = get_option('dgx_donate_debug_mode');
    if ($debug_mode == 'VERBOSE') {
        // we're in debug, so we'll return lots of log info

        $display_options = array(
            __('Seamless Donations Log Data', 'seamless-donations') => $log_data,
            // Removes the default data by passing an empty value below.
            'Admin Page Framework'                                  => '',
            'Browser'                                               => '',
        );
    } else {
        $display_options = array(
            __('Seamless Donations Log Data', 'seamless-donations') => $log_data,
            // Removes the default data by passing an empty value below.
            'Admin Page Framework'                                  => '',
            'WordPress'                                             => '',
            'PHP'                                                   => '',
            'Server'                                                => '',
            'PHP Error Log'                                         => '',
            'MySQL'                                                 => '',
            'MySQL Error Log'                                       => '',
            'Browser'                                               => '',
        );
    }

    $section_options->add_field(array(
        'name'    => __('System Information', 'cmb2'),
        'id'      => 'seamless_donations_system_information',
        'type'    => 'textarea_code',
        'default' => $log_data,
    ));

    seamless_donations_display_cmb2_submit_button($section_options, array(
        'button_id'          => 'dgx_donate_button_settings_logs_delete',
        'button_text'        => 'Delete Log',
        'button_success_msg' => __('Log deleted.', 'seamless-donations'),
        'button_error_msg'   => '',
    ));

    $section_options = apply_filters('seamless_donations_tab_logs_section_data_options', $section_options);
}

//// CRON LOGS - SECTION - DATA ////
function seamless_donations_admin_cron_logs_section_data($section_options) {
    $gateway = get_option('dgx_donate_payment_processor_choice');
    if ($gateway == 'STRIPE') {
        $section_options->add_field(array(
            'name'    => __('Cron Log Data', 'cmb2'),
            'id'      => 'seamless_donations_cron_log_data',
            'type'    => 'title',
            'default' => 'log data',
        ));
        $section_options = apply_filters('seamless_donations_tab_cron_logs_section_data', $section_options);

        $debug_log_option  = get_option('dgx_donate_debug_mode');
        $debug_log_content = get_option('dgx_donate_cron_log');
        $log_data          = '';

        if (empty($debug_log_content)) {
            $log_data = esc_html__('The log is empty.', 'seamless-donations');
        } else {
            foreach ($debug_log_content as $debug_log_entry) {
                if ($log_data != "") {
                    $log_data .= "\n";
                }
                if ($debug_log_option == 'RAWLOG') {
                    $log_data .= $debug_log_entry;
                } else {
                    $log_data .= esc_html($debug_log_entry);
                }
            }
        }

        $section_options->add_field(array(
            'name'    => __('Cron Log', 'cmb2'),
            'id'      => 'seamless_donations_cron_log_data_field',
            'type'    => 'textarea_code',
            'default' => $log_data,
            'desc'    => __('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
                'If you want more control over your cron, consider installing ' .
                '<A target="_blank" HREF="https://wordpress.org/plugins/wp-crontrol/">' .
                'WP Crontrol</A> from the WordPress.org plugin repository.', 'seamless-donations'),
        ));

        seamless_donations_display_cmb2_submit_button($section_options, array(
            'button_id'          => 'dgx_donate_button_settings_cron_logs_delete',
            'button_text'        => 'Delete Cron Log',
            'button_success_msg' => __('Cron log deleted.', 'seamless-donations'),
            'button_error_msg'   => '',
        ));

        $section_options = apply_filters('seamless_donations_tab_cron_logs_section_data_options', $section_options);
    }
}

//// LOGS - PROCESS ////
function seamless_donations_tab_logs_process_buttons() {
    $_POST = apply_filters('validate_page_slug_seamless_donations_tab_logs', $_POST);

    if (isset($_POST['dgx_donate_button_settings_logs_delete'])) {
        delete_option('dgx_donate_log');
        seamless_donations_flag_cmb2_submit_button_success('dgx_donate_button_settings_logs_delete');
    }

    if (isset($_POST['dgx_donate_button_settings_cron_logs_delete'])) {
        delete_option('dgx_donate_cron_log');
        seamless_donations_flag_cmb2_submit_button_success('dgx_donate_button_settings_cron_logs_delete');
    }
}

