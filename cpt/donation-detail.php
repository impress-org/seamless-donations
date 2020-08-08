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

//// CUSTOM POST TYPE - DONATION - DEFINE META BOXES ////
///

function seamless_donations_cpt_donation_detail_init() {
    $args = array(
        'id'           => 'seamless_donations_cpt_donation5_metabox',
        'title'        => 'Donation Information',
        'object_types' => array('donation',),
        'option_key'   => 'seamless_donations_cpt_donation',
    );

    $template_options = new_cmb2_box($args);

    if (isset($_GET['post'])) {
        $id   = $_GET['post'];
        $type = get_post_type($id);

        if ($type == 'donation') {
            seamless_donations_cpt_donation5_section_data($template_options);

            add_action('add_meta_boxes_donation', 'seamless_donations_cpt_donation_detail_page_request_hook');
        }
    }


}

//// SETUP METABOX DETAILS
///
function seamless_donations_cpt_donation5_section_data($section_options) {
    // get display meta data

    $donation_id = $_GET['post'];
    $donor_id    = get_post_meta($donation_id, '_dgx_donate_donor_id', true);
    $first       = get_post_meta($donor_id, '_dgx_donate_donor_first_name', true);
    $last        = get_post_meta($donor_id, '_dgx_donate_donor_last_name', true);
    $email       = get_post_meta($donor_id, '_dgx_donate_donor_email', true);
    $phone       = get_post_meta($donor_id, '_dgx_donate_donor_phone', true);
    $address     = get_post_meta($donor_id, '_dgx_donate_donor_address', true);
    $address2    = get_post_meta($donor_id, '_dgx_donate_donor_address2', true);
    $city        = get_post_meta($donor_id, '_dgx_donate_donor_city', true);
    $state       = get_post_meta($donor_id, '_dgx_donate_donor_state', true);
    $province    = get_post_meta($donor_id, '_dgx_donate_donor_province', true);
    $country     = get_post_meta($donor_id, '_dgx_donate_donor_country', true);
    $zip         = get_post_meta($donor_id, '_dgx_donate_donor_zip', true);

    if (empty($country)) { /* older versions only did US */
        $country = 'US';
        update_post_meta($donation_id, '_dgx_donate_donor_country', 'US');
    }

    // construct basic address info block
    $html = "";
    $html .= $first . ' ' . $last . '<br>';
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
    $html         .= $email != '' ? $email : '';

    $section_options->add_field(array(
        'name'        => __('Donor Information', 'seamless-donations'),
        'id'          => 'seamless_donations_cpt_donor_data',
        'type'        => 'title',
        'after_field' => $html,
    ));

    $html = "";

    $html .= "<table class='widefat'><tbody>\n";

    $year  = get_post_meta($donation_id, '_dgx_donate_year', true);
    $month = get_post_meta($donation_id, '_dgx_donate_month', true);
    $day   = get_post_meta($donation_id, '_dgx_donate_day', true);
    $time  = get_post_meta($donation_id, '_dgx_donate_time', true);

    $html .= "<tr>";
    $html .= "<th>" . esc_html__('Date', 'seamless-donations') . "</th>";
    $html .= "<td>" . esc_html($month . "/" . $day . "/" . $year . " " . $time) . "</td></tr>\n";

    $amount           = get_post_meta($donation_id, '_dgx_donate_amount', true);
    $currency_code    = dgx_donate_get_donation_currency_code($donation_id);
    $formatted_amount = dgx_donate_get_escaped_formatted_amount(floatval($amount), 2, $currency_code);
    $html             .= "<tr>";
    $html             .= "<th>" . esc_html__('Amount', 'seamless-donations') . "</th>";
    $html             .= "<td>" . $formatted_amount . "</td></tr>\n";

    $add_to_mailing_list = get_post_meta($donation_id, '_dgx_donate_add_to_mailing_list', true);
    if (!empty($add_to_mailing_list)) {
        $add_to_mailing_list = __('Yes', 'seamless-donations');
    } else {
        $add_to_mailing_list = __('No', 'seamless-donations');
    }
    $html .= "<tr><th>" . esc_html__('Add to Mailing List?', 'seamless-donations') . "</th>";
    $html .= "<td>" . esc_html($add_to_mailing_list) . "</td></tr>\n";

    $anonymous = get_post_meta($donation_id, '_dgx_donate_anonymous', true);
    if (empty($anonymous)) {
        $anonymous = __('No', 'seamless-donations');
    } else {
        $anonymous = __('Yes', 'seamless-donations');
    }
    $html .= "<tr><th>" . esc_html__('Would like to remain anonymous?', 'seamless-donations') . "</th>";
    $html .= "<td>" . esc_html($anonymous) . "</td></tr>\n";

    $fund_name  = __('Undesignated', 'seamless-donations');
    $designated = get_post_meta($donation_id, '_dgx_donate_designated', true);
    if (!empty($designated)) {
        $fund_name = get_post_meta($donation_id, '_dgx_donate_designated_fund', true);
    }
    $html .= "<tr><th>" . esc_html__('Designated Fund', 'seamless-donations') . "</th>";
    $html .= "<td>" . esc_html($fund_name) . "</td></tr>\n";

    $employer_match = get_post_meta($donation_id, '_dgx_donate_employer_match', true);
    if (empty($employer_match)) {
        $employer_match_message = __('No', 'seamless-donations');
    } else {
        $employer_match_message = __('Yes', 'seamless-donations');
    }
    $html .= "<tr><th>" . esc_html__('Employer Match', 'seamless-donations') . "</th>";
    $html .= "<td>" . esc_html($employer_match_message) . "</td></tr>\n";

    $employer_name = get_post_meta($donation_id, '_dgx_donate_employer_name', true);
    if (empty($employer_name)) {
        $employer_name_message = '-';
    } else {
        $employer_name_message = $employer_name;
    }
    $html .= "<tr><th>" . esc_html__('Employer', 'seamless-donations') . "</th>";
    $html .= "<td>" . esc_html($employer_name_message) . "</td></tr>\n";

    $occupation = get_post_meta($donation_id, '_dgx_donate_occupation', true);
    if (empty($occupation)) {
        $occupation_message = '-';
    } else {
        $occupation_message = $occupation;
    }
    $html .= "<tr><th>" . esc_html__('Occupation', 'seamless-donations') . "</th>";
    $html .= "<td>" . esc_html($occupation_message) . "</td></tr>\n";

    $donor_country = get_post_meta($donation_id, '_dgx_donate_donor_country', true);
    if ('GB' == $donor_country) {
        $uk_gift_aid = get_post_meta($donation_id, '_dgx_donate_uk_gift_aid', true);
        if (empty($uk_gift_aid)) {
            $uk_gift_aid_message = __('No', 'seamless-donations');
        } else {
            $uk_gift_aid_message = __('Yes', 'seamless-donations');
        }
        $html .= "<tr><th>" . esc_html__('UK Gift Aid', 'seamless-donations') . "</th>";
        $html .= "<td>" . esc_html($uk_gift_aid_message) . "</td></tr>\n";
    }

    $tribute_gift_message = __('No', 'seamless-donations');
    $tribute_gift         = get_post_meta($donation_id, '_dgx_donate_tribute_gift', true);
    if (!empty($tribute_gift)) {
        $tribute_gift_message = __('Yes', 'seamless-donations') . " - ";

        $honoree_name       = get_post_meta($donation_id, '_dgx_donate_honoree_name', true);
        $honor_by_email     = get_post_meta($donation_id, '_dgx_donate_honor_by_email', true);
        $honoree_email_name = get_post_meta($donation_id, '_dgx_donate_honoree_email_name', true);
        $honoree_post_name  = get_post_meta($donation_id, '_dgx_donate_honoree_post_name', true);
        $honoree_email      = get_post_meta($donation_id, '_dgx_donate_honoree_email', true);
        $honoree_address    = get_post_meta($donation_id, '_dgx_donate_honoree_address', true);
        $honoree_city       = get_post_meta($donation_id, '_dgx_donate_honoree_city', true);
        $honoree_state      = get_post_meta($donation_id, '_dgx_donate_honoree_state', true);
        $honoree_province   = get_post_meta($donation_id, '_dgx_donate_honoree_province', true);
        $honoree_zip        = get_post_meta($donation_id, '_dgx_donate_honoree_zip', true);
        $honoree_country    = get_post_meta($donation_id, '_dgx_donate_honoree_country', true);
        $memorial_gift      = get_post_meta($donation_id, '_dgx_donate_memorial_gift', true);

        if (empty($memorial_gift)) {
            $tribute_gift_message .= __('in honor of', 'seamless-donations') . ' ';
        } else {
            $tribute_gift_message .= __('in memory of', 'seamless-donations') . ' ';
        }

        $tribute_gift_message .= $honoree_name . "<br/><br/>";
        if ('TRUE' == $honor_by_email) {
            $tribute_gift_message .= __('Send acknowledgement via email to', 'seamless-donations') . '<br/>';
            $tribute_gift_message .= esc_html($honoree_email_name) . "<br/>";
            $tribute_gift_message .= esc_html($honoree_email) . "<br/>";
        } else {
            $tribute_gift_message .= __('Send acknowledgement via postal mail to', 'seamless-donations') .
                '<br/>';
            $tribute_gift_message .= esc_html($honoree_post_name) . "<br/>";
            $tribute_gift_message .= esc_html($honoree_address) . "<br/>";

            if (!empty($honoree_city)) {
                $tribute_gift_message .= esc_html($honoree_city . " ");
            }
            if ('US' == $honoree_country) {
                $tribute_gift_message .= esc_html($honoree_state . " ");
            } else if ('CA' == $honoree_country) {
                $tribute_gift_message .= esc_html($honoree_province . " ");
            }

            if (dgx_donate_country_requires_postal_code($honoree_country)) {
                $tribute_gift_message .= esc_html(" " . $honoree_zip);
            }
            $tribute_gift_message .= "<br/>";

            $countries            = dgx_donate_get_countries();
            $honoree_country_name = $countries[$honoree_country];
            $tribute_gift_message .= esc_html($honoree_country_name) . "<br/><br/>";
        }
    }
    $html .= "<tr>";
    $html .= "<th>" . esc_html__('Tribute Gift', 'seamless-donations') . "</th>";
    $html .= "<td>" . $tribute_gift_message . "</td></tr>\n";

    $payment_method = get_post_meta($donation_id, '_dgx_donate_payment_method', true);
    $html           .= "<tr><th>" . esc_html__('Payment Method', 'seamless-donations') . "</th>";
    $html           .= "<td>" . esc_html($payment_method) . "</td></tr>\n";

    $repeating             = get_post_meta($donation_id, '_dgx_donate_repeating', true);
    $is_repeating_donation = !empty($repeating);
    if ($is_repeating_donation) {
        $repeatingText = __('Yes', 'seamless-donations');
    } else {
        $repeatingText = __('No', 'seamless-donations');
    }
    $html .= "<tr><th>" . esc_html__('Repeating', 'seamless-donations') . "</th>";
    $html .= "<td>" . esc_html($repeatingText) . "</td></tr>\n";

    $session_id = get_post_meta($donation_id, '_dgx_donate_session_id', true);
    $html       .= "<tr><th>" . esc_html__('Session ID', 'seamless-donations') . "</th>";
    $html       .= "<td>" . esc_html($session_id) . "</td></tr>\n";

    $transaction_id = get_post_meta($donation_id, '_dgx_donate_transaction_id', true);
    $html           .= "<tr><th>" . esc_html__('Transaction ID', 'seamless-donations') . "</th>";
    $html           .= "<td>" . esc_html($transaction_id) . "</td></tr>\n";

    $html .= "</tbody></table>\n";

    if ($is_repeating_donation) {
        // Display links to related (same session ID) donations
        $related_donation_ids = get_donations_by_meta('_dgx_donate_session_id', $session_id, -1);

        // Unset this donation if present (it probably will be)
        if (($index = array_search($donation_id, $related_donation_ids)) !== false) {
            unset($related_donation_ids[$index]);
        }

        $html .= "<h3>" . esc_html__('Related Donations', 'seamless-donations') . "</h3>\n";
        $html .= "<p class='description'>";
        $html .= esc_html__(
            'For repeating donations, displays a list of other donations in the series (subscription)',
            'seamless-donations');
        $html .= "</p>\n";
        // Show the array
        $html .= "<table class='widefat'><tbody>\n";
        if (count($related_donation_ids)) {
            $html .= "<tr>";
            $html .= "<th>" . esc_html__('Date', 'seamless-donations') . "</th>";
            $html .= "<th>" . esc_html__('Transaction ID', 'seamless-donations') . "</th></tr>";
            foreach ((array)$related_donation_ids as $related_donation_id) {
                $year          = get_post_meta($related_donation_id, '_dgx_donate_year', true);
                $month         = get_post_meta($related_donation_id, '_dgx_donate_month', true);
                $day           = get_post_meta($related_donation_id, '_dgx_donate_day', true);
                $time          = get_post_meta($related_donation_id, '_dgx_donate_time', true);
                $donation_date = $month . "/" . $day . "/" . $year;

                $transaction_id = get_post_meta($related_donation_id, '_dgx_donate_transaction_id', true);

                $donation_detail = seamless_donations_get_donation_detail_link($related_donation_id);
                $html            .= "<tr>";
                $html            .= "<td><a href='" . esc_url($donation_detail) . "'>" .
                    esc_html($donation_date . " " . $time) . "</a></td>";
                $html            .= "<td>" . esc_html($transaction_id) . "</td></tr>\n";
            }
        } else {
            $html .= "<tr>";
            $html .= "<th>" . esc_html__('No related donations found', 'seamless-donations') . "</th>";
            $html .= "</tr>\n";
        }
        $html .= "</tbody></table>\n";
    }

    $section_options->add_field(array(
        'name'        => __('Donation Details', 'seamless-donations'),
        'id'          => 'seamless_donations_cpt_donor_data_more',
        'type'        => 'title',
        'after_field' => $html,
    ));
}

//// SETUP CSS HOOKS
///
// only run this when we're on the donor post type
function seamless_donations_cpt_donation_detail_page_request_hook($vars) {
    if (isset($vars->post_type)) {
        if ($vars->post_type == 'donation') {
            // adds special body class to customize the display of the donor list page
            add_filter('admin_body_class', 'seamless_donations_cpt_donation_detail_class_hook');
            //$vars = seamless_donations_cpt_donor_list_sort_order($vars);
        }

        return $vars;
    }
}

// add special body class to customize the display of the donor list page
function seamless_donations_cpt_donation_detail_class_hook($classes) {
    $classes .= ' seamless_donations_cpt_donation_detail';

    return $classes;
}