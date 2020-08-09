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
 *
 */

//	Exit if .php file accessed directly
if (!defined('ABSPATH')) exit;

//// CUSTOM POST TYPE - DONATIONS - SETUP ////
function seamless_donations_cpt_donation_list_init() {
    // argument - http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
    $donations_setup
        = array(
        'labels'            => array(
            'name'                => __('Donations', 'seamless-donations'),
            'singular_name'       => __('Donation', 'seamless-donations'),
            'add_new_item'        => __('Donation', 'seamless-donations'),
            'edit_item'           => __('Donation', 'seamless-donations'),
            'new_item'            => __('Donation', 'seamless-donations'),
            'view_item'           => __('Donation', 'seamless-donations'),
            'search_items'        => __('Search donations', 'seamless-donations'),
            'not_found'           => __('No donations found', 'seamless-donations'),
            'not_found_in_trash'  => __(
                'No donors found in Trash', 'seamless-donations'),
            'restored_from_trash' => __('donation', 'seamless-donations'),
        ),
        'supports'          => array('title'),
        'public'            => true,
        'show_table_filter' => false,
        'menu_icon'         => 'dashicons-palmtree',
    );

    // adding custom columns: http://justintadlock.com/archives/2011/06/27/custom-columns-for-custom-post-types
    add_filter('manage_edit-donation_columns', 'seamless_donations_cpt_donation_columns');
    add_action('manage_donation_posts_custom_column', 'seamless_donations_cpt_donation_column_contents', 10, 2);
    add_action('load-edit.php', 'seamless_donations_cpt_donation_list_page_actions');
    add_filter('manage_edit-donation_sortable_columns', 'seamless_donations_cpt_donation_sortable_columns');

    $compact_menus = get_option('dgx_donate_compact_menus');
    if ($compact_menus == 1) {
        $donations_setup['show_ui']      = true;
        $donations_setup['show_in_menu'] = 'seamless_donations_tab_main';
        unset($donations_setup['public']);
        unset($donations_setup['menu_icon']);
    } else {
        add_action('admin_menu', 'seamless_donations_remove_donation_addnew_submenu', 999);
    }

    $donations_setup = apply_filters('seamless_donations_donations_setup', $donations_setup);
    register_post_type('donation', $donations_setup);

    // From 4.0 code, setup optional taxonomy
    $donation_type_setup = array();
    $donation_type_setup = apply_filters('seamless_donations_donations_type_setup', $donation_type_setup);
    register_taxonomy('donation', 'donation_type', $donation_type_setup);
}

function seamless_donations_remove_donation_addnew_submenu() {
    remove_submenu_page('edit.php?post_type=donation', 'post-new.php?post_type=donation');
}

//// CUSTOM POST TYPE - DONATION - DEFINE COLUMNS ////
///
// specify columns on donation list page
function seamless_donations_cpt_donation_columns($columns) {
    $columns = array(
        'cb'              => '&lt;input type="checkbox" />',
        'title'           => __('Timestamp'),
        'donor_name'      => __('Donor'),
        'assigned_fund'   => __('Fund'),
        'payment_gateway' => __('Gateway'),
        'donation_amount' => __('Amount'),
    );

    $columns = apply_filters('seamless_donations_donation_header_array', $columns);
    return $columns;
}

// specify column content on donation list page
function seamless_donations_cpt_donation_column_contents($column, $post_id) {
    global $post;

    switch ($column) {
        case 'donor_name' :

            $anon = get_post_meta($post_id, '_dgx_donate_anonymous', true);
            if (!$anon) {
                $anon = "off";
            }
            if ($anon == "on") {
                $anon_msg = "Anonymity Requested";

                echo __($anon_msg);
            } else {
                $first = get_post_meta($post_id, '_dgx_donate_donor_first_name', true);
                $last  = get_post_meta($post_id, '_dgx_donate_donor_last_name', true);

                echo __($first . ' ' . $last);
            }
            break;

        case 'assigned_fund':

            /* Get the post meta. */
            $fund = get_post_meta($post_id, '_dgx_donate_designated_fund', true);

            if (!$fund) {
                $fund_msg = "<i>no fund specified</i>";

                echo __($fund_msg);
            } else {
                echo __($fund);
            }
            break;

        case 'donation_amount':

            $amount   = get_post_meta($post_id, '_dgx_donate_amount', true);
            $currency = get_post_meta($post_id, '_dgx_donate_donation_currency', true);

            echo __($amount . ' ' . $currency);

            break;

        case 'payment_gateway':

            $gateway = get_post_meta($post_id, '_dgx_donate_payment_processor', true);
            echo __($gateway);

            break;
    }
}

//// SETUP SORTING
///
function seamless_donations_cpt_donation_sortable_columns($columns) {
    $columns['donor']  = 'donor';
    $columns['fund']   = 'fund';
    $columns['amount'] = 'amount';

    return $columns;
}

// make sure to check for sort orders
function seamless_donations_cpt_donation_list_sort_order($vars) {
    /* Check if 'orderby' is set to '_dgx_donate_donor_email'. */
    if (isset($vars['orderby']) && '_dgx_donate_donor_employer' == $vars['orderby']) {
        /* Merge the query vars with our custom variables. */
        $vars = array_merge(
            $vars,
            array(
                'meta_key' => '_dgx_donate_donor_email',
                'orderby'  => 'meta_value_num',
            )
        );
    }
    if (isset($vars['orderby']) && '_dgx_donate_donor_occupation' == $vars['orderby']) {
        /* Merge the query vars with our custom variables. */
        $vars = array_merge(
            $vars,
            array(
                'meta_key' => '_dgx_donate_donor_occupation',
                'orderby'  => 'meta_value_num',
            )
        );
    }
    if (isset($vars['orderby']) && '_dgx_donate_donor_employer' == $vars['orderby']) {
        /* Merge the query vars with our custom variables. */
        $vars = array_merge(
            $vars,
            array(
                'meta_key' => '_dgx_donate_donor_employer',
                'orderby'  => 'meta_value_num',
            )
        );
    }

    return $vars;
}

//// SETUP CSS HOOKS
///
// only run this when on an edit.php page, which is a list page for post types
function seamless_donations_cpt_donation_list_page_actions() {
    add_filter('request', 'seamless_donations_cpt_donation_list_page_request_hook');
}

// only run this when we're on the donor post type
function seamless_donations_cpt_donation_list_page_request_hook($vars) {
    if (isset($vars['post_type']) && $vars['post_type'] == 'donation') {
        // adds special body class to customize the display of the donor list page
        add_filter('admin_body_class', 'seamless_donations_cpt_donation_list_class_hook');

        $vars = seamless_donations_cpt_donation_list_sort_order($vars);
    }

    return $vars;
}

// add special body class to customize the display of the donor list page
function seamless_donations_cpt_donation_list_class_hook($classes) {
    $classes .= ' seamless_donations_cpt_donation_list';

    return $classes;
}
