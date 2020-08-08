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
if ( !defined( 'ABSPATH' ) ) exit;

//// CUSTOM POST TYPE - DONOR - SETUP ////
function seamless_donations_cpt_donor_list_init() {
	// argument - http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
	$donors_setup
		= array(
		'labels'            => array(
			'name'                => __( 'Donors', 'seamless-donations' ),
			'singular_name'       => __( 'Donor', 'seamless-donations' ),
			'add_new_item'        => __( 'Donor', 'seamless-donations' ),
			'edit_item'           => __( 'Donor', 'seamless-donations' ),
			'new_item'            => __( 'Donor', 'seamless-donations' ),
			'view_item'           => __( 'Donor', 'seamless-donations' ),
			'search_items'        => __( 'Search donors', 'seamless-donations' ),
			'not_found'           => __( 'No donors found', 'seamless-donations' ),
			'not_found_in_trash'  => __(
				'No donors found in Trash', 'seamless-donations' ),
			'restored_from_trash' => __( 'donor', 'seamless-donations' ),
		),
		'supports'          => array( 'title' ),
		'public'            => true,
		'show_table_filter' => false,
		'menu_icon'         => 'dashicons-palmtree',
	);

	// adding custom columns: http://justintadlock.com/archives/2011/06/27/custom-columns-for-custom-post-types
	add_filter( 'manage_edit-donor_columns', 'seamless_donations_cpt_donor_columns' );
	add_action( 'manage_donor_posts_custom_column', 'seamless_donations_cpt_donor_column_contents', 10, 2 );
	add_action( 'load-edit.php', 'seamless_donations_cpt_donor_list_page_actions' );
	add_filter( 'manage_edit-donor_sortable_columns', 'seamless_donations_cpt_donor_sortable_columns' );

	$compact_menus = get_option( 'dgx_donate_compact_menus' );
	if ( $compact_menus == 1 ) {
		$donors_setup['show_ui']      = true;
		$donors_setup['show_in_menu'] = 'seamless_donations_tab_main';
		unset( $donors_setup['public'] );
		unset( $donors_setup['menu_icon'] );
	} else {
		add_action( 'admin_menu', 'seamless_donations_remove_donor_addnew_submenu', 999 );
	}

	$donors_setup = apply_filters( 'seamless_donations_donors_setup', $donors_setup );
	register_post_type( 'donor', $donors_setup );

	// From 4.0 code, setup optional taxonomy
	$donors_type_setup = array();
	$donors_type_setup = apply_filters( 'seamless_donations_donors_type_setup', $donors_type_setup );
	register_taxonomy( 'donor', 'donor_type', $donors_type_setup );
}

function seamless_donations_remove_donor_addnew_submenu() {
	remove_submenu_page( 'edit.php?post_type=donor', 'post-new.php?post_type=donor' );
}

//// CUSTOM POST TYPE - DONOR - DEFINE COLUMNS ////
///
// specify columns on donor list page
function seamless_donations_cpt_donor_columns( $columns ) {
	$columns = array(
		'cb'            => '&lt;input type="checkbox" />',
		'title'         => __( 'Donor' ),
		'email'         => __( 'Email' ),
		'occupation'    => __( 'Occupation' ),
		'employer'      => __( 'Employer' ),
		'total_donated' => __( 'Total Donated' ),
	);

    $columns = apply_filters( 'seamless_donations_donor_header_array', $columns );
	return $columns;
}

// specify column content on donor list page
function seamless_donations_cpt_donor_column_contents( $column, $post_id ) {
	global $post;

	switch ( $column ) {
		case 'email' :

			/* Get the post meta. */
			$email = get_post_meta( $post_id, '_dgx_donate_donor_email', true );

			/* If none is found, output a default message. */
			if ( empty( $email ) ) {
				echo __( '<i>not specified</i>' );
			} /* If there is a duration, append 'minutes' to the text string. */
			else {
				echo __( $email );
			}

			break;

		case 'occupation':

			/* Get the post meta. */
			$occupation = get_post_meta( $post_id, '_dgx_donate_occupation', true );

			/* If none is found, output a default message. */
			if ( empty( $occupation ) ) {
				echo __( '<i>not specified</i>' );
			} /* If there is a duration, append 'minutes' to the text string. */
			else {
				echo __( $occupation );
			}

			break;

		case 'employer':

			/* Get the post meta. */
			$employer = get_post_meta( $post_id, '_dgx_donate_donor_employer', true );

			/* If none is found, output a default message. */
			if ( empty( $employer ) ) {
				echo __( '<i>not specified</i>' );
			} /* If there is a duration, append 'minutes' to the text string. */
			else {
				echo __( $employer );
			}

			break;

		case 'total_donated':
			$donation_list = get_post_meta( $post_id, '_dgx_donate_donor_donations', true );

			$amount            = floatval( 0.0 );
			$donation_id_array = explode( ',', $donation_list );
			$donation_id_array = array_values(
				array_filter( $donation_id_array ) ); // remove empty elements from the array

			while ( $donation_id = current( $donation_id_array ) ) {
				$new_amount = floatVal( get_post_meta( $donation_id, '_dgx_donate_amount', true ) );
				$amount     += $new_amount;

				next( $donation_id_array );
			}

			$currency_code    = dgx_donate_get_donation_currency_code( $donation_id );
			$formatted_amount = dgx_donate_get_escaped_formatted_amount( $amount, 2, $currency_code );

			echo __( $formatted_amount );
	}
}

//// SETUP SORTING
///
function seamless_donations_cpt_donor_sortable_columns( $columns ) {
	$columns['email']      = 'email';
	$columns['occupation'] = 'occupation';
	$columns['employer']   = 'employer';

	return $columns;
}

// make sure to check for sort orders
function seamless_donations_cpt_donor_list_sort_order( $vars ) {
	/* Check if 'orderby' is set to '_dgx_donate_donor_email'. */
	if ( isset( $vars['orderby'] ) && '_dgx_donate_donor_employer' == $vars['orderby'] ) {
		/* Merge the query vars with our custom variables. */
		$vars = array_merge(
			$vars,
			array(
				'meta_key' => '_dgx_donate_donor_email',
				'orderby'  => 'meta_value_num',
			)
		);
	}
	if ( isset( $vars['orderby'] ) && '_dgx_donate_donor_occupation' == $vars['orderby'] ) {
		/* Merge the query vars with our custom variables. */
		$vars = array_merge(
			$vars,
			array(
				'meta_key' => '_dgx_donate_donor_occupation',
				'orderby'  => 'meta_value_num',
			)
		);
	}
	if ( isset( $vars['orderby'] ) && '_dgx_donate_donor_employer' == $vars['orderby'] ) {
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
function seamless_donations_cpt_donor_list_page_actions() {
	add_filter( 'request', 'seamless_donations_cpt_donor_list_page_request_hook' );
}

// only run this when we're on the donor post type
function seamless_donations_cpt_donor_list_page_request_hook( $vars ) {
	if ( isset( $vars['post_type'] ) && $vars['post_type'] == 'donor' ) {
		// adds special body class to customize the display of the donor list page
		add_filter( 'admin_body_class', 'seamless_donations_cpt_donor_list_class_hook' );

		$vars = seamless_donations_cpt_donor_list_sort_order( $vars );
	}

	return $vars;
}

// add special body class to customize the display of the donor list page
function seamless_donations_cpt_donor_list_class_hook( $classes ) {
	$classes .= ' seamless_donations_cpt_donor_list';

	return $classes;
}
