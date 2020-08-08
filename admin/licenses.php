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

add_action('cmb2_admin_init', 'seamless_donations_admin_licenses_menu');

//// LICENSES - MENU ////
function seamless_donations_admin_licenses_menu() {
    $args = array(
        'id'           => 'seamless_donations_tab_licenses_page',
        'title'        => 'Seamless Donations - Licenses',
        // page title
        'menu_title'   => 'Licenses',
        // title on left sidebar
        'tab_title'    => 'Licenses',
        // title displayed on the tab
        'object_types' => array('options-page'),
        'option_key'   => 'seamless_donations_tab_licenses',
        'parent_slug'  => 'seamless_donations_tab_main',
        'tab_group'    => 'seamless_donations_tab_set',
        'save_button'  => 'Save Settings',
    );

    // 'tab_group' property is supported in > 2.4.0.
    if (version_compare(CMB2_VERSION, '2.4.0')) {
        $args['display_cb'] = 'seamless_donations_cmb_options_display_with_tabs';
    }

    do_action('seamless_donations_tab_licenses_before', $args);

    // call on button hit for page save
    add_action('admin_post_seamless_donations_tab_licenses', 'seamless_donations_tab_licenses_process_buttons');

    // clear previous error messages if coming from another page
    seamless_donations_clear_cmb2_submit_button_messages($args['option_key']);

    $args             = apply_filters('seamless_donations_tab_licenses_menu', $args);
    $licenses_options = new_cmb2_box($args);

    if (isset($_REQUEST['page'])) {
        if ($_REQUEST['page'] == 'seamless_donations_tab_licenses') {
            seamless_donations_admin_licenses_section_data($licenses_options);

            do_action('seamless_donations_tab_licenses_after', $licenses_options);
        }
    }
}

//// LICENSES - SECTION - TEST ////
function seamless_donations_admin_licenses_section_data($section_options) {
    $section_desc = 'If you have purchased any premium extensions, you will be able to enter ';
    $section_desc .= 'their license keys here. Your active license key is required to run the extension ';
    $section_desc .= 'and will also enable you to get automatic updates for the duration of your license.';

    $skip_addon_check = get_option('dgx_donate_legacy_addon_check');
    if ($skip_addon_check != 'on') {
        $pre_5_licenses = get_option('dgx_donate_5000_deactivated_addons');

        if ($pre_5_licenses != false) {
            if ($pre_5_licenses != '') {
                $section_desc .= '<br><br>';
                $section_desc .= '<span style=\'color:red\'><i><b>WARNING: </b>';
                $section_desc .= 'The following add-ons are incompatible with this version of Seamless Donations and have been disabled: ';
                $section_desc .= $pre_5_licenses;
                $section_desc .= '. You will need to upgrade these add-ons before you can use them again.</span>';
            }
        }
    }

    $section_options->add_field(array(
        'name'        => 'License Activation',
        'id'          => 'seamless_donations_template_email_title',
        'type'        => 'title',
        'after_field' => __($section_desc, 'seamless-donations'),
    ));

    //    $section_options = apply_filters (
    //        'seamless_donations_admin_licenses_section_registration', $section_options );

    $section_options->add_field(array(
        'name'         => 'Licenses',
        'id'           => 'licenses_no_licenses',
        'type'         => 'licenses_html',
        'before_field' => __(
            'Nothing has been installed or activated that requires a license.', 'seamless-donations'),
    ));

    seamless_donations_sd4_plugin_filter_remove(); // clear out old registrations
    $section_options = apply_filters('seamless_donations_admin_licenses_section_registration_options', $section_options);
}

//// LICENSES - PROCESS ////
function seamless_donations_tab_licenses_process_buttons() {
    $_POST = apply_filters('validate_page_slug_seamless_donations_tab_licenses', $_POST);
}