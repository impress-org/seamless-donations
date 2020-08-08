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

add_action('cmb2_admin_init', 'seamless_donations_admin_templates_menu');

//// TEMPLATES - MENU ////
function seamless_donations_admin_templates_menu() {
    $args = array(
        'id'           => 'seamless_donations_tab_templates_page',
        'title'        => 'Seamless Donations - Thank You Templates',
        // page title
        'menu_title'   => 'Thank You Templates',
        // title on left sidebar
        'tab_title'    => 'Thank You Templates',
        // title displayed on the tab
        'object_types' => array('options-page'),
        'option_key'   => 'seamless_donations_tab_templates',
        'parent_slug'  => 'seamless_donations_tab_main',
        'tab_group'    => 'seamless_donations_tab_set',
        'save_button'  => 'Save Settings',
    );

    // 'tab_group' property is supported in > 2.4.0.
    if (version_compare(CMB2_VERSION, '2.4.0')) {
        $args['display_cb'] = 'seamless_donations_cmb_options_display_with_tabs';
    }

    do_action('seamless_donations_tab_templates_before', $args);

    // call on button hit for page save
    add_action('admin_post_seamless_donations_tab_templates', 'seamless_donations_tab_templates_process_buttons');

    // clear previous error messages if coming from another page
    seamless_donations_clear_cmb2_submit_button_messages($args['option_key']);

    $args             = apply_filters('seamless_donations_tab_templates_menu', $args);
    $template_options = new_cmb2_box($args);

    if (isset($_REQUEST['page'])) {
        if ($_REQUEST['page'] == 'seamless_donations_tab_templates') {
            seamless_donations_admin_templates_test_section_data($template_options);
            seamless_donations_admin_templates_section_data($template_options);

            do_action('seamless_donations_tab_templates_after', $template_options);
        }
    }
}

//// TEMPLATES - SECTION - TEST ////
function seamless_donations_admin_templates_test_section_data($section_options) {
    // init values
    $handler_function = 'seamless_donations_admin5_templates_preload'; // setup the preload handler function
    $section_options  = apply_filters('seamless_donations_tab_templates_test_section_data', $section_options);

    // Test email section
    $section_desc = '<i>Enter an email address (e.g. your own) to have a test email sent using the template.</i>';

    $section_options->add_field(array(
        'name'        => 'Send a Test Email',
        'id'          => 'seamless_donations_template_email_title',
        'type'        => 'title',
        'after_field' => $section_desc,
    ));

    $section_options->add_field(array(
        'name' => 'Send a Test Email',
        'id'   => 'seamless_donations_template_email_test',
        'type' => 'text_email',
        'desc' => __('The email address to receive the test message.', 'seamless_donations'),
    ));

    seamless_donations_cmb2_add_action_button($section_options, "Send Test Email", "dgx_donate_button_settings_templates_test_email");

    seamless_donations_display_cmb2_submit_button($section_options, array(
        'button_id'          => 'dgx_donate_button_settings_templates_test_email',
        'button_text'        => 'Send Test Email',
        'button_success_msg' => __('Test email sent.', 'seamless-donations'),
        'button_error_msg'   => __('Please enter valid email addresses.', 'seamless-donations'),
    ));
    $section_options = apply_filters('seamless_donations_tab_templates_test_section_data_options', $section_options);
}

//// TEMPLATES - SECTION - EMAIL TEMPLATES ////
function seamless_donations_admin_templates_section_data($section_options) {
    // init values
    $handler_function = 'seamless_donations_admin5_templates_preload'; // setup the preload handler function
    $section_options  = apply_filters('seamless_donations_tab_templates_section_data', $section_options);

    // Template email section
    $section_desc = '<i>The template on this page is used to generate thank you emails for ';
    $section_desc .= 'donation. You can include placeholders ';
    $section_desc .= '[firstname] [lastname] [fund] and/or [amount]. These placeholders will ';
    $section_desc .= 'be filled in with the donor and donation details.</i>';

    $section_options->add_field(array(
        'name'        => 'Email Template',
        'id'          => 'seamless_donations_template_title',
        'type'        => 'title',
        'after_field' => $section_desc,
    ));

    $section_options->add_field(array(
        'name'    => __('From / Reply-To Name', 'seamless-donations'),
        'id'      => 'dgx_donate_email_name',
        'type'    => 'text',
        'desc'    => __('The name the thank you email should appear to come from (e.g. your organization name or your name).',
            'seamless_donations'),
        'default' => '',
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_name', $handler_function);

    $section_options->add_field(array(
        'name'    => __('From / Reply-To Email Address', 'seamless-donations'),
        'id'      => 'dgx_donate_email_reply',
        'type'    => 'text_email',
        'desc'    => __('The email address the thank you email should appear to come from.',
            'seamless_donations'),
        'default' => '',
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_reply', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Subject', 'seamless-donations'),
        'id'      => 'dgx_donate_email_subj',
        'type'    => 'text',
        'desc'    => __('The subject of the email (e.g. Thank You for Your Donation).',
            'seamless_donations'),
        'default' => __('Thank you for your donation', 'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_subj', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Body', 'seamless-donations'),
        'id'      => 'dgx_donate_email_body',
        'type'    => 'textarea',
        'desc'    => __('The body of the email message to all donors.',
            'seamless_donations'),
        'default' => __(
            'Dear [firstname] [lastname],' . PHP_EOL . PHP_EOL .
            'Thank you for your generous donation of [amount]. ' .
            'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_body', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Recurring Donations', 'seamless-donations'),
        'id'      => 'dgx_donate_email_recur',
        'type'    => 'textarea',
        'desc'    => __('This message will be included when the donor elects to make their donation recurring.',
            'seamless_donations'),
        'default' => __(
            'Dear [firstname] [lastname],' . PHP_EOL . PHP_EOL .
            'Thank you for electing to have your donation automatically repeated each month.' .
            'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_recur', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Designated Fund', 'seamless-donations'),
        'id'      => 'dgx_donate_email_desig',
        'type'    => 'textarea',
        'desc'    => __('This message will be included when the donor designates their donation to a specific fund.',
            'seamless_donations'),
        'default' => __(
            'Your donation has been designated to the [fund] fund.' .
            'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_desig', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Anonymous Donations', 'seamless-donations'),
        'id'      => 'dgx_donate_email_anon',
        'type'    => 'textarea',
        'desc'    => __('This message will be included when the donor requests their donation get kept anonymous.',
            'seamless_donations'),
        'default' => __(
            'You have requested that your donation be kept anonymous. ' .
            'Your name will not be revealed to the public.', 'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_anon', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Mailing List Join', 'seamless-donations'),
        'id'      => 'dgx_donate_email_list',
        'type'    => 'textarea',
        'desc'    => __('This message will be included when the donor elects to join the mailing list.',
            'seamless_donations'),
        'default' => __(
            'Thank you for joining our mailing list.  We will send you updates from time-to-time. ' .
            'If at any time you would like to stop receiving emails, please ' .
            'send us an email to be removed from the mailing list.', 'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_list', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Employer Match', 'seamless-donations'),
        'id'      => 'dgx_donate_email_empl',
        'type'    => 'textarea',
        'desc'    => __('This message will be included when the donor selects the employer match.',
            'seamless_donations'),
        'default' => __(
            'You have specified that your employer matches some or all of your donation.',
            'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_empl', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Tribute Gift', 'seamless-donations'),
        'id'      => 'dgx_donate_email_trib',
        'type'    => 'textarea',
        'desc'    => __('This message will be included when the donor elects to make their donation a tribute gift.',
            'seamless_donations'),
        'default' => __(
            'You have asked to make this donation in honor of or memory of someone else. ' .
            'We will notify the honoree within the next 5-10 business days.', 'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_trib', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Closing', 'seamless-donations'),
        'id'      => 'dgx_donate_email_close',
        'type'    => 'textarea',
        'desc'    => __('The closing text of the email message to all donors.',
            'seamless_donations'),
        'default' => __('Thanks again for your support!', 'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_close', $handler_function);

    $section_options->add_field(array(
        'name'    => __('Signature', 'seamless-donations'),
        'id'      => 'dgx_donate_email_sig',
        'type'    => 'textarea',
        'desc'    => __('The signature at the end of the email message to all donors.',
            'seamless_donations'),
        'default' => __('Director of Donor Relations', 'seamless-donations'),
    ));
    seamless_donations_preload_cmb2_field_filter('dgx_donate_email_sig', $handler_function);

    seamless_donations_display_cmb2_submit_button($section_options, array(
        'button_id'          => 'dgx_donate_button_template_settings',
        'button_text'        => 'Save Changes',
        'button_success_msg' => __('Changes saved.', 'seamless-donations'),
        'button_error_msg'   => __('', 'seamless-donations'),
    ));
    $section_options = apply_filters('seamless_donations_tab_templates_section_data_options', $section_options);
}

//// FORM OPTIONS - PRELOAD DATA
function seamless_donations_admin5_templates_preload($data, $object_id, $args, $field) {
    // preload function to ensure compatibility with pre-5.0 settings data

    // find out what field we're setting
    $field_id = $args["field_id"];

    // Pull from existing Seamless Donations data formats
    switch ($field_id) {
        // defaults
        case 'dgx_donate_email_name':
            return (get_option('dgx_donate_email_name'));
        case 'dgx_donate_email_reply':
            return (get_option('dgx_donate_email_reply'));
        case 'dgx_donate_email_subj':
            return (get_option('dgx_donate_email_subj'));
            break;
        case 'dgx_donate_email_body':
            return (get_option('dgx_donate_email_body'));
            break;
        case 'dgx_donate_email_recur':
            return (get_option('dgx_donate_email_recur'));
            break;
        case 'dgx_donate_email_desig':
            return (get_option('dgx_donate_email_desig'));
            break;
        case 'dgx_donate_email_anon':
            return (get_option('dgx_donate_email_anon'));
            break;
        case 'dgx_donate_email_list':
            return (get_option('dgx_donate_email_list'));
            break;
        case 'dgx_donate_email_empl':
            return (get_option('dgx_donate_email_empl'));
            break;
        case 'dgx_donate_email_trib':
            return (get_option('dgx_donate_email_trib'));
            break;
        case 'dgx_donate_email_close':
            return (get_option('dgx_donate_email_close'));
            break;
        case 'dgx_donate_email_sig':
            return (get_option('dgx_donate_email_sig'));
            break;
    }
}

//// FORM OPTIONS - PROCESS FORM SUBMISSIONS
function seamless_donations_tab_templates_process_buttons() {
    $_POST = apply_filters('validate_page_slug_seamless_donations_tab_templates', $_POST);

    // Process Test Email button
    if (isset($_POST['dgx_donate_button_settings_templates_test_email'])) {
        $none_enabled = true;
        $test_mail    = $_POST['seamless_donations_template_email_test'];
        $test_mail    = sanitize_email($test_mail);
        if (!is_email($test_mail)) { // check address
            seamless_donations_flag_cmb2_submit_button_error('dgx_donate_button_settings_templates_test_email');
        } else {
            dgx_donate_send_thank_you_email(0, $test_mail);

            seamless_donations_flag_cmb2_submit_button_success('dgx_donate_button_settings_templates_test_email');
        }
    }
    // Process Save changes button
    if (isset($_POST['dgx_donate_button_template_settings'])) {
        update_option('dgx_donate_email_name', $_POST['dgx_donate_email_name']);
        update_option('dgx_donate_email_reply', $_POST['dgx_donate_email_reply']);
        update_option('dgx_donate_email_subj', $_POST['dgx_donate_email_subj']);
        update_option('dgx_donate_email_body', $_POST['dgx_donate_email_body']);
        update_option('dgx_donate_email_recur', $_POST['dgx_donate_email_recur']);
        update_option('dgx_donate_email_desig', $_POST['dgx_donate_email_desig']);
        update_option('dgx_donate_email_anon', $_POST['dgx_donate_email_anon']);
        update_option('dgx_donate_email_list', $_POST['dgx_donate_email_list']);
        update_option('dgx_donate_email_empl', $_POST['dgx_donate_email_empl']);
        update_option('dgx_donate_email_trib', $_POST['dgx_donate_email_trib']);
        update_option('dgx_donate_email_close', $_POST['dgx_donate_email_close']);
        update_option('dgx_donate_email_sig', $_POST['dgx_donate_email_sig']);
        seamless_donations_flag_cmb2_submit_button_success('dgx_donate_button_template_settings');
    }
}
