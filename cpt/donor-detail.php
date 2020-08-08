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

//// CUSTOM POST TYPE - DONOR - DEFINE META BOXES ////
///

function seamless_donations_cpt_donor_detail_init() {
    $args = array(
        'id'           => 'seamless_donations_cpt_donor_metabox',
        'title'        => 'Donor Information',
        'object_types' => array('donor',),
        'option_key'   => 'seamless_donations_cpt_donor',
    );

    $template_options = new_cmb2_box($args);

    if (isset($_GET['post'])) {
        $id   = $_GET['post'];
        $type = get_post_type($id);

        if ($type == 'donor') {
            seamless_donations_cpt_donors_section_data($template_options);

            add_action('add_meta_boxes_donor', 'seamless_donations_cpt_donor_detail_page_request_hook');
        }
    }


}

//// SETUP METABOX DETAILS
///
function seamless_donations_cpt_donors_section_data($section_options) {
    // get display meta data

    $post_id  = $_GET['post'];
    $first    = get_post_meta($post_id, '_dgx_donate_donor_first_name', true);
    $last     = get_post_meta($post_id, '_dgx_donate_donor_last_name', true);
    $email    = get_post_meta($post_id, '_dgx_donate_donor_email', true);
    $phone    = get_post_meta($post_id, '_dgx_donate_donor_phone', true);
    $address  = get_post_meta($post_id, '_dgx_donate_donor_address', true);
    $address2 = get_post_meta($post_id, '_dgx_donate_donor_address2', true);
    $city     = get_post_meta($post_id, '_dgx_donate_donor_city', true);
    $state    = get_post_meta($post_id, '_dgx_donate_donor_state', true);
    $province = get_post_meta($post_id, '_dgx_donate_donor_province', true);
    $country  = get_post_meta($post_id, '_dgx_donate_donor_country', true);
    $zip      = get_post_meta($post_id, '_dgx_donate_donor_zip', true);
    $anon     = get_post_meta($post_id, '_dgx_donate_anonymous', true);

    if (empty($country)) { /* older versions only did US */
        $country = 'US';
        update_post_meta($post_id, '_dgx_donate_donor_country', 'US');
    }

    // construct basic address info block
    $donor = $first . ' ' . $last;

    $html = "";
    $html .= $address != '' ? $address . '<br>' : '';
    $html .= $address2 != '' ? $address2 . '<br>' : '';
    $html .= $city != '' ? $city . ', ' : '';

    if ('US' == $country) {
        $html .= $state != '' ? $state . ' ' : '';
    } else if ('CA' == $country) {
        $html .= $province != '' ? $province . ' ' : '';
    }

    if (dgx_donate_country_requires_postal_code($country)) {
        $html .= $zip != '' ? $zip . '<br>' : '';
    }

    $countries    = dgx_donate_get_countries();
    $country_name = $countries[$country];
    $html         .= $country_name != '' ? $country_name . '<br>' : '';
    $html         .= '<br>';
    $html         .= $phone != '' ? $phone . '<br>' : '';
    $html         .= $email != '' ? $email . '<br>' : '';
    $html         .= esc_html__('Anonymity requested: ', 'seamless-donations') . $anon;

    $section_options->add_field(array(
        'name'        => __($donor, 'seamless-donations'),
        'id'          => 'seamless_donations_cpt_donor_data',
        'type'        => 'title',
        'after_field' => $html,
    ));

    $first = get_post_meta($post_id, '_dgx_donate_donor_first_name', true);
    $last  = get_post_meta($post_id, '_dgx_donate_donor_last_name', true);
    $email = get_post_meta($post_id, '_dgx_donate_donor_email', true);

    $donation_list = get_post_meta($post_id, '_dgx_donate_donor_donations', true);
    $my_donations  = explode(',', $donation_list);
    $my_donations  = array_values(array_filter($my_donations)); // remove empty elements from the array
    $html          = '';

    if (count($my_donations) < 1) {
        $html .= "<p>" . esc_html__('No donations found.', 'seamless-donations') . "</p>";
    } else {
        $html .= "<table class='widefat'><tbody>\n";
        $html .= "<tr>";
        $html .= "<th>" . esc_html__('Date', 'seamless-donations') . "</th>";
        $html .= "<th>" . esc_html__('Fund', 'seamless-donations') . "</th>";
        $html .= "<th>" . esc_html__('Amount', 'seamless-donations') . "</th>";
        $html .= "<th>" . esc_html__('Anonymous', 'seamless-donations') . "</th>";
        $html .= "</tr>\n";

        $donor_total          = 0;
        $donor_currency_codes = array();

        foreach ((array)$my_donations as $donation_id) {
            $year       = get_post_meta($donation_id, '_dgx_donate_year', true);
            $month      = get_post_meta($donation_id, '_dgx_donate_month', true);
            $day        = get_post_meta($donation_id, '_dgx_donate_day', true);
            $time       = get_post_meta($donation_id, '_dgx_donate_time', true);
            $designated = get_post_meta($donation_id, '_dgx_donate_designated', true);
            $anonymous  = get_post_meta($donation_id, '_dgx_donate_anonymous', true);

            $fund_name = __('Undesignated', 'seamless-donations');
            if (!empty($designated)) {
                $fund_name = get_post_meta($donation_id, '_dgx_donate_designated_fund', true);
            } else {
                $undesignated_post_id = $donation_id;
            }

            $amount                               = get_post_meta($donation_id, '_dgx_donate_amount', true);
            $donor_total                          = $donor_total + floatval($amount);
            $currency_code                        = dgx_donate_get_donation_currency_code($donation_id);
            $donor_currency_codes[$currency_code] = true;

            $formatted_amount = dgx_donate_get_escaped_formatted_amount(floatval($amount), 2, $currency_code);

            if ($anonymous == 'on') {
                $anonymous = 'Yes';
            } else {
                $anonymous = 'No';
            }

            $donation_detail = seamless_donations_get_donation_detail_link($donation_id);

            $html .= "<tr><td><a href='" . esc_url($donation_detail) . "'>" .
                esc_html($year . "-" . $month . "- " . $day . " " . $time) . "</a></td>";
            $html .= "<td>" . esc_html($fund_name) . "</td>";
            $html .= "<td>" . $formatted_amount . "</td>";
            $html .= "<td>" . $anonymous . "</td>";
            $html .= "</tr>\n";
        }
        if (count($donor_currency_codes) > 1) {
            $formatted_donor_total = "-";
        } else {
            $formatted_donor_total = dgx_donate_get_escaped_formatted_amount(floatval($donor_total), 2, $currency_code);
        }
        $html .= "<tr>";
        $html .= "<th>&nbsp</th><th>" . esc_html__('Donor Total', 'seamless-donations') . "</th>";
        $html .= "<td>" . $formatted_donor_total . "</td></tr>\n";

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
function seamless_donations_cpt_donor_detail_page_request_hook($vars) {
    if (isset($vars->post_type)) {
        if ($vars->post_type == 'donor') {
            // adds special body class to customize the display of the donor list page
            add_filter('admin_body_class', 'seamless_donations_cpt_donor_detail_class_hook');
            //$vars = seamless_donations_cpt_donor_list_sort_order($vars);
        }

        return $vars;
    }
}

// add special body class to customize the display of the donor list page
function seamless_donations_cpt_donor_detail_class_hook($classes) {
    $classes .= ' seamless_donations_cpt_donor_detail';

    return $classes;
}