<?php
/*
Plugin Name: Seamless Donations
Plugin URI: http://zatzlabs.com/seamless-donations/
Description: A platform for global fundraising and rebuilding.
Version: 5.0.21
Author: David Gewirtz
Author URI: http://zatzlabs.com/lab-notes/
Text Domain: seamless-donations
Domain Path: /languages
License: GPL2
*/

/*  Copyright 2014 Allen Snook (email: allendav@allendav.com)
	Copyright 2015-2020 David Gewirtz (http://zatzlabs.com/contact-us/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

//	Exit if .php file accessed directly
if (!defined('ABSPATH')) exit;

function seamless_donations_set_version() {
    $plugin_data = get_plugin_data(__FILE__);
    $version     = $plugin_data['Version'];
    update_option('dgx_donate_active_version', $version);
}

//initialize the CMB2 library
if (file_exists(dirname(__FILE__) . '/library/cmb2/init.php')) {
    require_once dirname(__FILE__) . '/library/cmb2/init.php';
} else if (file_exists(dirname(__FILE__) . '/library/CMB2/init.php')) {
    require_once dirname(__FILE__) . '/library/CMB2/init.php';
}

// prepare and check for obsolete, enabled plugins
require_once 'inc5/legacy5.php';

// bring in stripe early in case we need it
if (!class_exists('\Stripe\Stripe')) {
    require_once('library/stripe-php/init.php');
}

// initialize Seamless Donations include files
require_once 'inc5/alerts5.php';
require_once 'inc/geography.php';
require_once 'inc/currency.php';
require_once 'inc/debug.php';
require_once 'inc/utilities.php';
require_once 'inc/legacy.php';
require_once 'inc/payment.php';
require_once 'inc/stripe.php';
require_once 'inc/security.php';
require_once 'inc/cron.php';
require_once 'inc5/utilities5.php';
require_once 'inc5/widgets5.php';

require_once 'legacy/dgx-donate.php';
require_once 'legacy/dgx-donate-admin.php';
require_once 'seamless-donations-form.php';
require_once 'dgx-donate-paypalstd.php';

function seamless_donations_admin_loader() {
    // loads for Seamless Donations 5.0 and above

    // load UI library elements
    require_once 'library/cmb2-addons/cmb2-radio-image.php';

    // bring in telemetry
    require_once 'telemetry/deactivate.php';
    //require_once 'telemetry/telemetry.php';

    // bring in the admin page tabs
    require_once 'admin/main.php';
    require_once 'admin/templates.php';
    require_once 'admin/thanks.php';
    require_once 'admin/forms.php';
    require_once 'admin/addons.php';
    require_once 'admin/settings.php';
    require_once 'admin/licenses.php';
    require_once 'admin/logs.php';

    // bring in the v5.0 custom post types
    require_once 'cpt/donor-list.php';
    require_once 'cpt/donor-detail.php';
    require_once 'cpt/funds-list.php';
    require_once 'cpt/funds-detail.php';
    require_once 'cpt/donation-list.php';
    require_once 'cpt/donation-detail.php';

    // bring in other resources
    require_once 'inc/form-engine.php';
    require_once 'inc/donations.php';
}

function seamless_donations_legacy_admin_loader() {
    // load the legacy pre-4.0 admin system
    require_once 'legacy/admin-views/main.php';
    require_once 'legacy/admin-views/donor-detail.php';
    require_once 'legacy/admin-views/donation-detail.php';

    require_once 'legacy/admin-views/donations.php';
    require_once 'legacy/admin-views/donors.php';
    require_once 'legacy/admin-views/funds.php';
    require_once 'legacy/admin-views/templates.php';
    require_once 'legacy/admin-views/completed.php';
    require_once 'legacy/admin-views/form-options.php';
    require_once 'legacy/admin-views/settings.php';
    require_once 'legacy/admin-views/log.php';
    require_once 'legacy/admin-views/help.php';
}

//// plugin loaded

function seamless_donations_plugin_loaded() {
    seamless_donations_sd4_plugin_load_check();
    seamless_donations_sd4_plugin_reactivation_check();
    load_plugin_textdomain('seamless-donations', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'seamless_donations_plugin_loaded');

//// Register activation and deactivation

//register_activation_hook(__FILE__, 'seamless_donations_plugin_activated');
register_deactivation_hook(__FILE__, 'seamless_donations_plugin_deactivated');

function seamless_donations_plugin_activated() {
}

function seamless_donations_plugin_deactivated() {
    wp_clear_scheduled_hook('seamless_donations_daily_cron_hook');
    wp_clear_scheduled_hook('seamless_donations_hourly_cron_hook');
    dgx_donate_cron_log('Hourly and daily crons deactivated.');
}

//// load and enqueue supporting resources

function seamless_donations_enqueue_scripts() {
    wp_enqueue_script('jquery');

    $script_url = plugins_url('/js/seamless-donations.js', __FILE__);
    wp_enqueue_script('seamless_javascript_code', $script_url, array('jquery'));

    $script_url = plugins_url('/library/node-uuid/uuid.js', __FILE__);
    wp_register_script('seamless_javascript_uuid', $script_url);
    wp_enqueue_script('seamless_javascript_uuid');

    // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
    wp_localize_script(
        'seamless_javascript_code', 'dgxDonateAjax',
        array(
            'ajaxurl'            => admin_url('admin-ajax.php'),
            'nonce'              => wp_create_nonce('dgx-donate-nonce'),
            'postalCodeRequired' => dgx_donate_get_countries_requiring_postal_code(),
        )
    );
}

function seamless_donations_admin_enqueue_scripts() {
    // helpful resources for the accordion:
    // https://jqueryui.com/themeroller/
    // https://www.youtube.com/watch?v=LGbv2GL0eI0
    // https://jqueryui.com/accordion/
    wp_enqueue_script('jquery');
    $script_url = plugins_url('/js/accordion.js', __FILE__);
    wp_enqueue_script('jquery-ui-accordion');
    wp_enqueue_script('custom-accordion', $script_url, array('jquery'));

    // remodal library used by telemetry
    wp_enqueue_script('remodal', plugins_url('/library/remodal/remodal.min.js', __FILE__));
    wp_enqueue_style('remodal', plugins_url('/library/remodal/remodal.css', __FILE__));
    wp_enqueue_style('remodal-default-theme', plugins_url('/library/remodal/remodal-default-theme.css', __FILE__));

    $script_url = plugins_url('/js/tabs.js', __FILE__);
    wp_enqueue_script('custom-tabs', $script_url, array('jquery'));
    wp_enqueue_script('jquery-ui-tabs');

    wp_register_style('jquery-custom-style',
        plugins_url('/css/jquery-ui-1.12.1/jquery-ui.css', __FILE__),
        array(), '1', 'screen');
    wp_enqueue_style('jquery-custom-style');

    // cmb2 add-ons to the bottom
    $script_url = plugins_url('/js/admin-scripts.js', __FILE__);
    wp_register_script('admin-scripts', $script_url, array('jquery'), '1.0', true);
    wp_enqueue_script('admin-scripts');
}

add_action('wp_enqueue_scripts', 'seamless_donations_enqueue_scripts');          // DG version of scripts
add_action('admin_enqueue_scripts', 'seamless_donations_admin_enqueue_scripts'); // DG version of scripts

function seamless_donations_queue_stylesheet() {
    $form_style = get_option('dgx_donate_form_style');
    switch ($form_style) {
        case 'classic':
            $styleurl = plugins_url('/css/classic-styles.css', __FILE__);
            break;
        case 'modern':
            $styleurl = plugins_url('/css/modern-styles.css', __FILE__);
            break;
        case 'none':
            return;
    }

    $styleurl = apply_filters('seamless_donations_stylesheet_enqueue', $styleurl);

    wp_register_style('seamless_donations_css', $styleurl);
    wp_enqueue_style('seamless_donations_css');
}

// enqueue styles, preserving legacy style for existing sites
$stylesheet_priority = get_option('dgx_donate_stylesheet_priority');
if ($stylesheet_priority == false) $stylesheet_priority = '';
if ($stylesheet_priority == '') {
    add_action('wp_enqueue_scripts', 'seamless_donations_queue_stylesheet');
} else {
    // should prevent interference from most themes
    add_action('wp_enqueue_scripts', 'seamless_donations_queue_stylesheet', 9999);
}

function seamless_donations_queue_admin_stylesheet() {
    do_action('seamless_donations_add_styles_first');

    $style_url = plugins_url('/css/adminstyles.css', __FILE__);

    wp_register_style('seamless_donations_admin_css', $style_url);
    wp_enqueue_style('seamless_donations_admin_css');

    do_action('seamless_donations_add_styles_after');
}

add_action('admin_enqueue_scripts', 'seamless_donations_queue_admin_stylesheet');

//// donation-specific code

function seamless_donations_get_escaped_formatted_amount($amount, $decimal_places = 2, $currency_code = '') {
    // same as dgx_donate_get_escaped_formatted_amount

    if (empty($currency_code)) {
        $currency_code = get_option('dgx_donate_currency');
    }

    $currencies      = dgx_donate_get_currencies();
    $currency        = $currencies[$currency_code];
    $currency_symbol = $currency['symbol'];

    return $currency_symbol . esc_html(number_format($amount, $decimal_places));
}

//// new 4.0+ shortcode for 4.0+ forms and admin environment

add_shortcode('seamless-donations', 'seamless_donations_shortcode');

function seamless_donations_shortcode($atts) {
    $output = '';

    // shortcodes in SD4.0.5 and up are extensible
    // they are controlled by a $shortcode_features array defined below
    // it is up to each function to determine whether it should display anything
    $shortcode_features = array(
        array(
            'seamless_donations_shortcode_form',
            'seamless_donations_shortcode_form_filter',
            10,
            1,
        ),
        array(
            'seamless_donations_shortcode_thanks',
            'seamless_donations_shortcode_thanks_filter',
            10,
            1,
        ),
        array(
            'seamless_donations_shortcode_cancel',
            'seamless_donations_shortcode_cancel_filter',
            10,
            1,
        ),
    );

    // extend the array
    $shortcode_features = apply_filters('seamless_donations_shortcode_features', $shortcode_features);

    // create the filters
    for ($i = 0; $i < count($shortcode_features); ++$i) {
        $filter_name     = $shortcode_features[$i][0];
        $filter_func     = $shortcode_features[$i][1];
        $filter_priority = $shortcode_features[$i][2];
        $filter_args     = $shortcode_features[$i][3];
        add_filter($filter_name, $filter_func, $filter_priority, $filter_args);
    }

    // process each filter in turn, adding to the output (in reality, you want one filter to run)
    for ($i = 0; $i < count($shortcode_features); ++$i) {
        $filter_name = $shortcode_features[$i][0];
        $output      .= apply_filters($filter_name, $atts);
    }
    return $output;
}

function seamless_donations_shortcode_thanks_filter($atts) {
    $shortcode_mode = '';
    $output         = '';

    if (isset($_GET['thanks'])) {
        $shortcode_mode = 'show_thanks';
    } else if (isset($_GET['auth'])) {
        $shortcode_mode = 'show_thanks';
    }
    if ($shortcode_mode == 'show_thanks') {
        $output = dgx_donate_display_thank_you();
    }

    return $output;
}

function seamless_donations_shortcode_cancel_filter($atts) {
    $output = '';

    if (isset($_GET['cancel'])) {
        $output = seamless_donations_display_cancel_page($_GET['cancel']);
    }
    return $output;
}

function seamless_donations_shortcode_form_filter($atts) {
    $sd4_mode       = get_option('dgx_donate_start_in_sd4_mode');
    $shortcode_mode = 'show_form';
    $output         = '';

    if (isset($_GET['thanks'])) {
        $shortcode_mode = 'show_thanks';
    } else if (isset($_GET['auth'])) {
        $shortcode_mode = 'show_thanks';
    }
    if (isset($_GET['cancel'])) {
        $shortcode_mode = 'show_error';
    }
    if (!isset($_GET['noshow'])) {
        if ($shortcode_mode == 'show_form' and $atts == '') {
            if ($sd4_mode == false) {
                $output .= "<div style='background-color:red; color:white'>";
                $output .= "<P style='padding:5px;'>Warning: This form needs to be updated. ";
                $output .= "Please update using the [seamless-donations] shortcode.</P>";
                $output .= "</div>";
            } else {
                $output = "";
                $output = seamless_donations_generate_donation_form();

                if (empty($output)) {
                    $output = "<p>Error: No payment gateway selected. ";
                    $output .= "Please choose a payment gateway in Seamless Donations >> Settings.</p>";
                }
            }
        }
    }
    return $output;
}

function seamless_donations_init() {
    seamless_donations_set_version(); // make sure we've got the version set as an option

    // Check to see if we're supposed to run an upgrade
    seamless_donations_sd40_process_upgrade_check();
    seamless_donations_5000_check_addons();

    // Check to see if first-time run
    $first_run_time = get_option('dgx_donate_first_run_time');
    if ($first_run_time == false) {
        update_option('dgx_donate_first_run_time', time());
    }
    $from_name = get_option('dgx_donate_email_name');
    if ($from_name == false) {
        // this is a pure 4.0+ start
        update_option('dgx_donate_start_in_sd4_mode', 'true');
        update_option('dgx_donate_form_style', 'modern');
        $sd4_mode = true;
    } else {
        // now we need to determine if we've already updated to 4.0+ or not
        $sd4_mode = get_option('dgx_donate_start_in_sd4_mode');
        if ($sd4_mode != false) {
            $sd4_mode = true;
        }
    }

    // Check to see if we're processing donation form data
    // This section drives the payment form through the shortcode page, rather than a separate PHP file
    // done because some hosts can't handle redirecting forms to another php file.
    $process_form_via = get_option('dgx_donate_form_via_action');
    if ($process_form_via == '1') {
        $form_via_mode = '';

        if (isset($_POST['_dgx_donate_form_via'])) {
            $form_via_mode = $_POST['_dgx_donate_form_via'];
        }
        if ($form_via_mode == '1') {
            seamless_donations_process_payment();
        }
    }

    // Initialize options to defaults as needed
    if ($sd4_mode) {
        seamless_donations_init_defaults();
        seamless_donations_init_audit();
        seamless_donations_admin_loader();
        seamless_donations_init_custom_post_types();

        // check for any sd4 upgrades
        seamless_donations_4012_update_indexes();
        seamless_donations_4013_update_anon();

        // check for any sd5 upgrades
        seamless_donations_sd5004_debug_mode_update();
        seamless_donations_sd5021_stripe_invoices();

        // prepare payment gateways
        seamless_donations_init_payment_gateways();
        seamless_donations_provisionally_process_gateway_result();

        // enable cron
        seamless_donations_schedule_crons();
    } else {
        dgx_donate_init_session();
        dgx_donate_init_defaults();
        seamless_donations_legacy_admin_loader();
        add_action('admin_notices', 'seamless_donations_sd40_update_alert_message');
    }

    // Display an admin notice if we are in sandbox mode
    $gateway = get_option('dgx_donate_payment_processor_choice');
    if ($gateway == 'STRIPE') {
        $stripe_mode = get_option('dgx_donate_stripe_server');
        if (strcasecmp($stripe_mode, "SANDBOX") == 0) {
            add_action('admin_notices', 'dgx_donate_admin_sandbox_msg');
        }
    } else {
        $payPalServer = get_option('dgx_donate_paypal_server');
        if (strcasecmp($payPalServer, "SANDBOX") == 0) {
            add_action('admin_notices', 'dgx_donate_admin_sandbox_msg');
        }
    }

    // Display an admin notice on the Seamless Donations pages for new support message
    if (isset($_GET["page"])) {
        $current_page = $_GET["page"];

        if (stripos($current_page, 'seamless_donations_admin') !== false) {
            add_action('admin_notices', 'seamless_donations_admin_new_support_msg');
        }

        // Display an admin notice if we are in debug mode
        $debug_mode = get_option('dgx_donate_debug_mode');
        if ($debug_mode == false) {
            $debug_mode = 'OFF';
        }
        if ($debug_mode != 'OFF') {
            add_action('admin_notices', 'seamless_donations_admin_debug_mode_msg');
        }
    }

    // Display an admin notice if add-ons are out of date and need to be updated
    $skip_addon_check = get_option('dgx_donate_legacy_addon_check');
    if ($skip_addon_check != 'on') {
        $pre_5_licenses = get_option('dgx_donate_5000_deactivated_addons');

        if ($pre_5_licenses != false) {
            if ($pre_5_licenses != '') {
                add_action('admin_notices', 'seamless_donations_5000_disabled_addon_message');
            }
        }
    }

    // This runs a debug block defined in debug.php used for code testing
    $debug_mode = get_option('dgx_donate_debug_mode');
    if ($debug_mode == 'BLOCK') {
        debug_test_block();
    }
}

add_action('init', 'seamless_donations_init');

function seamless_donations_init_defaults() {
    // functionally identical to dgx_donate_init_defaults, but likely to change over time

    // Thank you email option defaults

    // validate name - replace with sanitized blog name if needed
    $from_name = get_option('dgx_donate_email_name');
    if (empty($from_name)) {
        $from_name = get_bloginfo('name');
        $from_name = preg_replace("/[^a-zA-Z ]+/", "", $from_name); // letters and spaces only please
        update_option('dgx_donate_email_name', $from_name);
    }

    // validate email - replace with admin email if needed
    $from_email = get_option('dgx_donate_email_reply');
    if (empty($from_email) || !is_email($from_email)) {
        $from_email = get_option('admin_email');
        update_option('dgx_donate_email_reply', $from_email);
    }

    $thankSubj = get_option('dgx_donate_email_subj');
    if (empty($thankSubj)) {
        $thankSubj = "Thank you for your donation";
        update_option('dgx_donate_email_subj', $thankSubj);
    }

    $bodyText = get_option('dgx_donate_email_body');
    if (empty($bodyText)) {
        $bodyText = "Dear [firstname] [lastname],\n\n";
        $bodyText .= "Thank you for your generous donation of [amount]. Please note that no goods ";
        $bodyText .= "or services were received in exchange for this donation.";
        update_option('dgx_donate_email_body', $bodyText);
    }

    $recurring_text = get_option('dgx_donate_email_recur');
    if (empty($recurring_text)) {
        $recurring_text = __(
            "Thank you for electing to have your donation automatically repeated each month.", 'seamless-donations');
        update_option('dgx_donate_email_recur', $recurring_text);
    }

    $designatedText = get_option('dgx_donate_email_desig');
    if (empty($designatedText)) {
        $designatedText = "Your donation has been designated to the [fund] fund.";
        update_option('dgx_donate_email_desig', $designatedText);
    }

    $anonymousText = get_option('dgx_donate_email_anon');
    if (empty($anonymousText)) {
        $anonymousText
            = "You have requested that your donation be kept anonymous.  Your name will not be revealed to the public.";
        update_option('dgx_donate_email_anon', $anonymousText);
    }

    $mailingListJoinText = get_option('dgx_donate_email_list');
    if (empty($mailingListJoinText)) {
        $mailingListJoinText
                             = "Thank you for joining our mailing list.  We will send you updates from time-to-time.  If ";
        $mailingListJoinText .= "at any time you would like to stop receiving emails, please send us an email to be ";
        $mailingListJoinText .= "removed from the mailing list.";
        update_option('dgx_donate_email_list', $mailingListJoinText);
    }

    $tributeText = get_option('dgx_donate_email_trib');
    if (empty($tributeText)) {
        $tributeText
                     = "You have asked to make this donation in honor of or memory of someone else.  Thank you!  We will notify the ";
        $tributeText .= "honoree within the next 5-10 business days.";
        update_option('dgx_donate_email_trib', $tributeText);
    }

    $employer_text = get_option('dgx_donate_email_empl');
    if (empty($employer_text)) {
        $employer_text = "You have specified that your employer matches some or all of your donation. ";
        update_option('dgx_donate_email_empl', $employer_text);
    }

    $closingText = get_option('dgx_donate_email_close');
    if (empty($closingText)) {
        $closingText = "Thanks again for your support!";
        update_option('dgx_donate_email_close', $closingText);
    }

    $signature = get_option('dgx_donate_email_sig');
    if (empty($signature)) {
        $signature = "Director of Donor Relations";
        update_option('dgx_donate_email_sig', $signature);
    }

    //// PayPal defaults
    $notifyEmails = get_option('dgx_donate_notify_emails');
    if (empty($notifyEmails)) {
        $notifyEmails = get_option('admin_email');
        update_option('dgx_donate_notify_emails', $notifyEmails);
    }

    // pre-5.0.5
    $paymentGateway = get_option('dgx_donate_payment_gateway');
    if ($paymentGateway == false) {
        // old pre-Stripe gateway never initialized
        $newGateway = get_option('dgx_donate_payment_processor_choice');
        if ($newGateway == false) {
            update_option('dgx_donate_payment_processor_choice', 'STRIPE');
        }
    } else {
        // we have data
        $newGateway = get_option('dgx_donate_payment_processor_choice');
        if ($newGateway == false) {
            // old gateway was initialized (had to be to PayPal), new gateway was not
            update_option('dgx_donate_payment_processor_choice', 'PAYPAL');
        }
    }
    if (empty($paymentGateway)) {
        update_option('dgx_donate_payment_gateway', DGXDONATEPAYPALSTD);
    }

    $payPalServer = get_option('dgx_donate_paypal_server');
    if (empty($payPalServer)) {
        update_option('dgx_donate_paypal_server', 'LIVE');
    }

    $payPalServer = get_option('dgx_donate_stripe_server');
    if (empty($payPalServer)) {
        update_option('dgx_donate_stripe_server', 'SANDBOX');
    }

    $stripe_billing = get_option('dgx_donate_stripe_billing_address');
    if(empty($stripe_billing)) {
        update_option('dgx_donate_stripe_billing_address', 'auto');
    }

    $paypal_email = get_option('dgx_donate_paypal_email');
    if (!is_email($paypal_email)) {
        update_option('dgx_donate_paypal_email', '');
    }

    // Thank you page default
    $thankYouText = get_option('dgx_donate_thanks_text');
    if (empty($thankYouText)) {
        $message = "Thank you for donating!  A thank you email with the details of your donation ";
        $message .= "will be sent to the email address you provided.";
        update_option('dgx_donate_thanks_text', $message);
    }

    // Giving levels default
    $givingLevels = dgx_donate_get_giving_levels();
    $noneChecked  = true;
    foreach ($givingLevels as $givingLevel) {
        $levelEnabled = dgx_donate_is_giving_level_enabled($givingLevel);
        if ($levelEnabled) {
            $noneChecked = false;
        }
    }
    if ($noneChecked) {
        // Select 1000, 500, 100, 50 by default
        dgx_donate_enable_giving_level(1000);
        dgx_donate_enable_giving_level(500);
        dgx_donate_enable_giving_level(100);
        dgx_donate_enable_giving_level(50);
    }

    // Form styles
    $form_styles = get_option('dgx_donate_form_style');
    if ($form_styles == false) {
        update_option('dgx_donate_form_style', 'classic');
    }
    if (empty($form_styles)) {
        update_option('dgx_donate_form_style', 'classic');
    }

    $style_tweaks = get_option('dgx_donate_stylesheet_priority');
    if ($style_tweaks == false) {
        if ($form_styles == false or empty($form_styles)) {
            update_option('dgx_donate_stylesheet_priority', false);
            update_option('dgx_donate_labels_for_input', false);
        } else {
            update_option('dgx_donate_stylesheet_priority', true);
            update_option('dgx_donate_labels_for_input', true);
        }
    }
    if (empty($style_tweaks)) {
        if ($form_styles == false or empty($form_styles)) {
            update_option('dgx_donate_stylesheet_priority', false);
            update_option('dgx_donate_labels_for_input', false);
        } else {
            update_option('dgx_donate_stylesheet_priority', true);
            update_option('dgx_donate_labels_for_input', true);
        }
    }

    // Currency
    $currency = get_option('dgx_donate_currency');
    if (empty($currency)) {
        update_option('dgx_donate_currency', 'USD');
    }

    // Country default
    $default_country = get_option('dgx_donate_default_country');
    if (empty($default_country)) {
        update_option('dgx_donate_default_country', 'US');
    }

    // State default
    $default_state = get_option('dgx_donate_default_state');
    if (empty($default_state)) {
        update_option('dgx_donate_default_state', 'NY');
    }

    // Province default
    $default_province = get_option('dgx_donate_default_province');
    if (empty($default_province)) {
        update_option('dgx_donate_default_province', 'AB');
    }

    // Show Employer match section default
    $show_employer_section = get_option('dgx_donate_show_employer_section');
    if (empty($show_employer_section)) {
        update_option('dgx_donate_show_employer_section', 'false');
    }

    // Show occupation field default
    $show_occupation_section = get_option('dgx_donate_show_donor_occupation_field');
    if (empty($show_occupation_section)) {
        update_option('dgx_donate_show_donor_occupation_field', 'false');
    }

    // Show donor employer default
    $show_occupation_section = get_option('dgx_donate_show_donor_employer_field');
    if (empty($show_occupation_section)) {
        update_option('dgx_donate_show_donor_employer_field', 'false');
    }

    // Show Tribute Gift section default
    $show_tribute_section = get_option('dgx_donate_show_tribute_section');
    if (empty($show_tribute_section)) {
        update_option('dgx_donate_show_tribute_section', 'true');
    }

    $donor_organization = get_option('dgx_donate_organization_name');
    if (empty($donor_organization)) {
        update_option('dgx_donate_organization_name', '');
    }

    // Scripts location default -- not used since 3.x
    $scripts_in_footer = get_option('dgx_donate_scripts_in_footer');
    if (empty($scripts_in_footer)) {
        update_option('dgx_donate_scripts_in_footer', 'false');
    }

    // Obscurify donor names in logs by default
    $obscurify = get_option('dgx_donate_log_obscure_name');
    if ($obscurify === false) {
        update_option('dgx_donate_log_obscure_name', '1');
    }
}

function seamless_donations_init_custom_post_types() {
    // added in v5.0
    // not exactly sure why the detail page needs to be called via an action, but
    // I think it has something to do with when the CMB2 library is loaded from the
    // CMB2 plugin (which will be replaced by a library once base coding is done)
    seamless_donations_cpt_donor_list_init();
    add_action('cmb2_admin_init', 'seamless_donations_cpt_donor_detail_init');
    seamless_donations_cpt_funds_list_init();
    add_action('cmb2_admin_init', 'seamless_donations_cpt_funds_detail_init');
    seamless_donations_cpt_donation_list_init();
    add_action('cmb2_admin_init', 'seamless_donations_cpt_donation_detail_init');
}

function seamless_donations_init_add_plugin_link($links, $file) {
    if (plugin_basename(__FILE__) == $file) {
        $row_meta = array(
            'support' => '<a href="' . esc_url('https://zatzlabs.com/submit-ticket/') .
                '" target="_blank" aria-label="' . esc_attr__('Get support', 'seamless-donations') .
                '">' . esc_html__('Open a support ticket', 'seamless-donations') . '</a>',
        );
        if (seamless_donations_addon_legacy_addons_still_loaded()) {
            $row_meta['update'] = '<a href="' . esc_url('https://zatzlabs.com/seamless-donations-5-0-released-important-tips/') .
                '" target="_blank" aria-label="' . esc_attr__('How to update', 'seamless-donations') .
                '" style="color:red;">' . esc_html__('How to update add-ons', 'seamless-donations') . '</a>';
        } else {
            $row_meta['rate'] = '<a href="' . esc_url('https://wordpress.org/support/plugin/seamless-donations/reviews/') .
                '" target="_blank" aria-label="' . esc_attr__('Please review', 'seamless-donations') .
                '">' . esc_html__('Please post a review ★★★★★', 'seamless-donations') . '</a>';
        }
        return array_merge($links, $row_meta);
    }
    return (array)$links;
}

add_filter('plugin_row_meta', 'seamless_donations_init_add_plugin_link', 10, 2);

function seamless_donations_init_audit() {
    // this checks to see if the audit table exists and builds it, if not
    global $wpdb;

    $table_name = $wpdb->prefix . "seamless_donations_audit";

    if (($wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") != $table_name) or
        get_option("dgx_donate_db_version") != "1.0.1"
    ) {
        // table doesn't exist, add it
        $charset_collate = $wpdb->get_charset_collate();

        $sql
            = "CREATE TABLE $table_name (
  			option_id bigint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
  			option_name varchar(64) NOT NULL DEFAULT '',
			option_value longtext NOT NULL,
  			autoload varchar(20) NOT NULL DEFAULT 'yes',
  			donor_email varchar(128) NOT NULL DEFAULT '',
  			created_on TIMESTAMP DEFAULT 0,
  			changed_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (option_id),
			KEY donor_email (donor_email),
  			UNIQUE KEY option_name (option_name)
			) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option("dgx_donate_db_version", "1.0.1");
    } else {
        // in future releases, might want to check dgx_donate_db_version and update database if desired
    }
}

function seamless_donations_add_audit_string($option_name, $option_value) {
    global $wpdb;

    $table_name  = $wpdb->prefix . "seamless_donations_audit";
    $option_name = trim($option_name);

    if (empty($option_name)) {
        return false;
    }

    // http://codex.wordpress.org/Class_Reference/wpdb#REPLACE_row
    $replace_result = $wpdb->replace(
        $table_name,
        array(
            'option_name'  => $option_name,
            'option_value' => $option_value,
        )
    );

    return $replace_result;
}

function seamless_donations_update_audit_option($option_name, $option_value) {
    global $wpdb;

    $table_name  = $wpdb->prefix . "seamless_donations_audit";
    $option_name = trim($option_name);

    if (empty($option_name)) {
        return false;
    }

    if (!isset($option_value['CURRENCY'])) {
        $currency                 = get_option('dgx_donate_currency');
        $option_value['CURRENCY'] = $currency;
    }

    // http://codex.wordpress.org/Class_Reference/wpdb#REPLACE_row
    $replace_result = $wpdb->replace(
        $table_name,
        array(
            'option_name'  => $option_name,
            'option_value' => maybe_serialize($option_value),
        )
    );

    return $replace_result;
}

function seamless_donations_get_audit_option($option_name) {
    global $wpdb;

    $table_name  = $wpdb->prefix . "seamless_donations_audit";
    $option_name = trim($option_name);

    $query = "SELECT * FROM $table_name WHERE option_name='" . $option_name . "'";

    $option_object = $wpdb->get_row($query, ARRAY_A);

    if ($option_object != NULL) {
        // do something with the link
        return maybe_unserialize($option_object['option_value']);
    } else {
        // no matching option record found
        return false;
    }
}

function seamless_donations_init_session() {
    $session_id                               = seamless_donations_get_guid('sd');
    $GLOBALS['seamless_donations_session_id'] = $session_id;
    dgx_donate_debug_log('Session ID (guid/audit db mode): ' . $session_id);
}

/******************************************************************************************************/
function dgx_donate_get_version() {
    $pluginVersion = get_option('dgx_donate_active_version');

    return $pluginVersion;
}


