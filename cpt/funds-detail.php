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

//// CUSTOM POST TYPE - FUNDS - DEFINE META BOXES ////
///
add_action('post_updated', 'seamless_donations_cpt_funds_process_buttons', 10, 2);

function seamless_donations_cpt_funds_detail_init() {
    $args = array(
        'id'           => 'seamless_donations_cpt_funds5_metabox',
        'title'        => 'Fund Information',
        'object_types' => array('funds',),
        'option_key'   => 'seamless_donations_cpt_funds',
    );

    $template_options = new_cmb2_box($args);

    if (isset($_GET['post'])) {
        $id   = $_GET['post'];
        $type = get_post_type($id);

        if ($type == 'funds') {
            seamless_donations_cpt_funds5_section_data($template_options);

            // http://rachievee.com/how-to-intercept-post-publishing-based-on-post-meta/

            add_action('add_meta_boxes_funds', 'seamless_donations_cpt_funds_detail_page_request_hook');
        }
    }
}


//// SETUP METABOX DETAILS
///
function seamless_donations_cpt_funds5_section_data($section_options) {
    $handler_function = 'seamless_donations_cpt_funds5_preload'; // setup the preload handler function

    // get display meta data
    $post_id = $_GET['post'];

    $section_options->add_field(array(
        'name'    => __('Display on donation form', 'seamless-donations'),
        'id'      => '_dgx_donate_fund_show',
        'type'    => 'radio_inline',
        'default' => __('Yes', 'seamless-donations'),
        'desc'    => __(
            'If you select Yes, this fund will be shown on the front-end donation form.' .
            '<br>If you select No, this fund will not be shown on the donation form.',
            'seamless-donations'),
        'options' => array(
            'yes' => __('Yes', 'seamless-donations'),
            'no'  => __('No', 'seamless-donations'),
        ),
    ));
    seamless_donations_preload_cmb2_field_filter('_dgx_donate_fund_show', $handler_function);

    //seamless_donations_cmb2_add_action_button( $section_options, "Save Defaults", "dgx_donate_button_funds_defaults" );

    $donation_list = get_post_meta($post_id, '_dgx_donate_donor_donations', true);
    $my_donations  = explode(',', $donation_list);
    $my_donations  = array_values(array_filter($my_donations)); // remove empty elements from the array

    // now build the table
    $html = "";

    if (count($my_donations) < 1) {
        $html .= "<p>" . esc_html__('No donations found.', 'seamless-donations') . "</p>";
    } else {
        $html .= "<table class='widefat'><tbody>\n";
        $html .= "<tr>";
        $html .= "<th>" . esc_html__('Date', 'seamless-donations') . "</th>";
        $html .= "<th>" . esc_html__('Donor', 'seamless-donations') . "</th>";
        $html .= "<th>" . esc_html__('Amount', 'seamless-donations') . "</th>";
        $html .= "</tr>\n";

        $fund_total     = 0;
        $currency_codes = array();

        foreach ((array)$my_donations as $donation_id) {
            $year  = get_post_meta($donation_id, '_dgx_donate_year', true);
            $month = get_post_meta($donation_id, '_dgx_donate_month', true);
            $day   = get_post_meta($donation_id, '_dgx_donate_day', true);
            $time  = get_post_meta($donation_id, '_dgx_donate_time', true);

            $donor_name = __('Anonymous', 'seamless-donations');
            $anonymous  = get_post_meta($donation_id, '_dgx_donate_anonymous', true);

            if (!empty($anonymous)) {
                $first      = get_post_meta($donation_id, '_dgx_donate_donor_first_name', true);
                $last       = get_post_meta($donation_id, '_dgx_donate_donor_last_name', true);
                $donor_name = sanitize_text_field($first . ' ' . $last);
            }

            $amount                         = get_post_meta($donation_id, '_dgx_donate_amount', true);
            $fund_total                     = $fund_total + floatval($amount);
            $currency_code                  = dgx_donate_get_donation_currency_code($donation_id);
            $currency_codes[$currency_code] = true;
            $formatted_amount               = dgx_donate_get_escaped_formatted_amount(
                floatval($amount), 2, $currency_code);

            $donation_detail = seamless_donations_get_donation_detail_link($donation_id);
            $html            .= "<tr><td><a href='" . esc_url($donation_detail) . "'>" .
                esc_html($year . "-" . $month . "- " . $day . " " . $time) . "</a></td>";
            $html            .= "<td>" . esc_html($donor_name) . "</td>";
            $html            .= "<td>" . $formatted_amount . "</td>";
            $html            .= "</tr>\n";
        }
        if (count($currency_codes) > 1) {
            $formatted_fund_total = "-";
        } else {
            $formatted_fund_total = dgx_donate_get_escaped_formatted_amount(floatval($fund_total), 2, $currency_code);
        }
        $html .= "<tr>";
        $html .= "<th>&nbsp</th><th>" . esc_html__('Fund Total', 'seamless-donations') . "</th>";
        $html .= "<td>" . $formatted_fund_total . "</td></tr>\n";

        $html .= "</tbody></table>\n";
    }

    $section_options->add_field(array(
        'name'        => __('Donation History', 'seamless-donations'),
        'id'          => 'seamless_donations_cpt_donor_data_more',
        'type'        => 'title',
        'after_field' => $html,
    ));
}

//// SETUP CSS HOOKS
///
// only run this when we're on the donor post type
function seamless_donations_cpt_funds_detail_page_request_hook($vars) {
    if (isset($vars->post_type)) {
        if ($vars->post_type == 'funds') {
            // adds special body class to customize the display of the funds list page
            add_filter('admin_body_class', 'seamless_donations_cpt_funds_detail_class_hook');
            //$vars = seamless_donations_cpt_donor_list_sort_order($vars);
        }

        return $vars;
    }
}

// add special body class to customize the display of the donor list page
function seamless_donations_cpt_funds_detail_class_hook($classes) {
    $classes .= ' seamless_donations_cpt_funds_detail';

    return $classes;
}

//// FUNDS OPTIONS - PRELOAD DATA
///
function seamless_donations_cpt_funds5_preload($data, $object_id, $args, $field) {
    // preload function to ensure compatibility with pre-5.0 settings data

    // find out what field we're setting
    $post_id  = $args["id"];
    $field_id = $args["field_id"];

    // Pull from existing Seamless Donations data formats
    switch ($field_id) {
        case '_dgx_donate_fund_show':

            $show_fund = get_post_meta($post_id, '_dgx_donate_fund_show', true);
            if ($show_fund != 'Yes') {
                return ('no');
            }

            return ('yes');
            break;
    }
}

//// FORM OPTIONS - PROCESS FORM SUBMISSIONS
///
function seamless_donations_cpt_funds_process_buttons($post_id, $passedParam) {

    if (!is_admin()) {
        return;
    }

    if (get_post_type($post_id) != 'funds') {
        return;
    }

    if (isset($_POST["_dgx_donate_fund_show"])) {
        if ($_POST["_dgx_donate_fund_show"] != 'yes') {
            update_post_meta($post_id, '_dgx_donate_fund_show','No');
        } else {
            update_post_meta($post_id, '_dgx_donate_fund_show','Yes');
        }
    }

    return;
}