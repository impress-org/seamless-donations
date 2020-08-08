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

add_action('cmb2_admin_init', 'seamless_donations_admin_thanks_menu');

//// THANK YOU - MENU ////
function seamless_donations_admin_thanks_menu() {
    $args = array(
        'id'           => 'seamless_donations_tab_thanks_page',
        'title'        => 'Seamless Donations - Thank You Page',
        // page title
        'menu_title'   => 'Thank You Page',
        // title on left sidebar
        'tab_title'    => 'Thank You Page',
        // title displayed on the tab
        'object_types' => array('options-page'),
        'option_key'   => 'seamless_donations_tab_thanks',
        'parent_slug'  => 'seamless_donations_tab_main',
        'tab_group'    => 'seamless_donations_tab_set',
        'save_button'  => 'Save Settings',
    );

    // 'tab_group' property is supported in > 2.4.0.
    if (version_compare(CMB2_VERSION, '2.4.0')) {
        $args['display_cb'] = 'seamless_donations_cmb_options_display_with_tabs';
    }

    do_action('seamless_donations_tab_thanks_before', $args);

    // call on button hit for page save
    add_action('admin_post_seamless_donations_tab_thanks', 'seamless_donations_tab_thanks_process_buttons');

    // clear previous error messages if coming from another page
    seamless_donations_clear_cmb2_submit_button_messages($args['option_key']);

    $args           = apply_filters('seamless_donations_tab_thanks_menu', $args);
    $thanks_options = new_cmb2_box($args);
    if (isset($_REQUEST['page'])) {
        if ($_REQUEST['page'] == 'seamless_donations_tab_thanks') {
            seamless_donations_admin_thanks_section_data($thanks_options);

            do_action('seamless_donations_tab_forms_after', $thanks_options);
        }
    }
}

//// THANK YOU - SECTION - DATA ////
function seamless_donations_admin_thanks_section_data($section_options) {
    // init values
    $handler_function = 'seamless_donations_admin5_thanks_preload'; // setup the preload handler function
    $section_options  = apply_filters('seamless_donations_tab_thanks_section_data', $section_options);

    $section_desc = 'On this page you can configure a special thank you message which will appear to your ';
    $section_desc .= 'donors after they complete their donations. This is separate from the thank you email ';
    $section_desc .= 'that gets emailed to your donors.';

    // promo
    $feature_desc = 'Thank You Enhanced provides landing page redirect and short codes.';
    $feature_url  = 'http://zatzlabs.com/project/seamless-donations-thank-you-enhanced/';
    $section_desc .= seamless_donations_get_feature_promo($feature_desc, $feature_url, 'UPGRADE', ' ');

    seamless_donations_sd4_plugin_filter_remove(); // clean up for sd4 add-on
    $section_desc = apply_filters('seamless_donations_admin_thanks_section_note', $section_desc);

    seamless_donations_cmb2_add_static_desc($section_options, $section_desc, 'thank_you_desc');

    $section_options->add_field(array(
        'name'    => 'Thank You Page Text',
        'id'      => 'dgx_donate_thanks_text',
        'type'    => 'textarea',
        'default' => 'Thank you for donating!  A thank you email with the details of your donation will be sent to the email address you provided.',
        'desc'    => __('The text to display to a donor after a donation is completed.', 'seamless_donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_thanks_text', $handler_function);

    seamless_donations_display_cmb2_submit_button($section_options, array(
        'button_id'          => 'dgx_donate_button_thanks_settings',
        'button_text'        => 'Save Text',
        'button_success_msg' => __('Thank you message saved.', 'seamless-donations'),
        'button_error_msg'   => __('Please enter an appropriate thank you message.', 'seamless-donations'),
    ));
    $section_options = apply_filters('seamless_donations_tab_thanks_section_data_options', $section_options);
}

//// THANK YOU OPTIONS - PRELOAD DATA
function seamless_donations_admin5_thanks_preload($data, $object_id, $args, $field) {
    // preload function to ensure compatibility with pre-5.0 settings data

    // find out what field we're setting
    $field_id = $args["field_id"];

    // Pull from existing Seamless Donations data formats
    switch ($field_id) {
        // defaults
        case 'dgx_donate_thanks_text':
            return (get_option('dgx_donate_thanks_text'));
            break;
    }
}

//// FORM OPTIONS - PROCESS FORM SUBMISSIONS
function seamless_donations_tab_thanks_process_buttons() {
    // Process Save changes button

    $_POST = apply_filters('validate_page_slug_seamless_donations_tab_thanks', $_POST);

    if (isset($_POST['dgx_donate_button_thanks_settings'])) {
        $note = trim($_POST['dgx_donate_thanks_text']);

        $note = stripslashes($note);
        $allowed_html = [
            'a'      => [
                'href'  => [],
                'title' => [],
                'class' => [],
                'id' => [],
                'style' => [],
            ],
            'span'      => [
                'class' => [],
                'id' => [],
                'style' => [],
            ],
            'div'      => [
                'class' => [],
                'id' => [],
                'style' => [],
            ],
            'br'     => [],
            'em'     => [],
            'strong' => [],
            'b' => [],
            'i' => [],
            'h1' => [],
            'h2' => [],
            'h3' => [],
        ];
        $note= wp_kses( $note, $allowed_html );

        //$note = sanitize_text_field($note);

        if ($note == '') { // ain't right
            seamless_donations_flag_cmb2_submit_button_error('dgx_donate_button_thanks_settings');
        } else {
            update_option('dgx_donate_thanks_text', $note);
            seamless_donations_flag_cmb2_submit_button_success('dgx_donate_button_thanks_settings');
        }
    }
}
