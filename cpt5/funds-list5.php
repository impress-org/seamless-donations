<?php
/**
 * Seamless Donations by David Gewirtz, adopted from Allen Snook
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: http://zatzlabs.com/seamless-donations/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2019 by David Gewirtz
 *
 */

//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//// CUSTOM POST TYPE - FUNDS - SETUP ////
///
function seamless_donations_cpt_funds_list_init() {
	// argument - http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
	$funds_setup
		= array(
		'labels'            => array(
			'name'                => __( 'Funds', 'seamless-donations' ),
			'singular_name'       => __( 'Fund', 'seamless-donations' ),
			'add_new_item'        => __( 'Fund', 'seamless-donations' ),
			'edit_item'           => __( 'Fund', 'seamless-donations' ),
			'new_item'            => __( 'Fund', 'seamless-donations' ),
			'view_item'           => __( 'Fund', 'seamless-donations' ),
			'search_items'        => __( 'Search funds', 'seamless-donations' ),
			'not_found'           => __( 'No funds found', 'seamless-donations' ),
			'not_found_in_trash'  => __(
				'No funds found in Trash', 'seamless-donations' ),
			'restored_from_trash' => __( 'funds', 'seamless-donations' ),
		),
		'supports'          => array( 'title' ),
		'public'            => true,
		'show_table_filter' => false,
		'menu_icon'         => 'dashicons-palmtree',
	);

	// adding custom columns: http://justintadlock.com/archives/2011/06/27/custom-columns-for-custom-post-types
	add_filter( 'manage_edit-funds_columns', 'seamless_donations_cpt_funds_columns' );
	add_action( 'manage_funds_posts_custom_column', 'seamless_donations_cpt_funds_column_contents', 10, 2 );
	add_action( 'load-edit.php', 'seamless_donations_cpt_list_page_actions' );
	add_filter( 'manage_edit-funds_sortable_columns', 'seamless_donations_cpt_funds_sortable_columns' );

	$compact_menus = get_option( 'dgx_donate_compact_menus' );
	if ( $compact_menus == 1 ) {
		$funds_setup['show_ui']      = true;
		$funds_setup['show_in_menu'] = 'seamless_donations_tab_main';
		unset( $funds_setup['public'] );
		unset( $funds_setup['menu_icon'] );
	}

	$funds_setup = apply_filters( 'seamless_donations_funds_setup', $funds_setup );
	register_post_type( 'funds', $funds_setup );

	// From 4.0 code, setup optional taxonomy
	$funds_type_setup = array();
	$funds_type_setup = apply_filters( 'seamless_donations_funds_type_setup', $funds_type_setup );
	register_taxonomy( 'funds', 'funds_type', $funds_type_setup );
}

//// CUSTOM POST TYPE - FUNDS - DEFINE COLUMNS ////
///
// specify columns on funds list page
function seamless_donations_cpt_funds_columns( $columns ) {
	$columns = array(
		'cb'              => '&lt;input type="checkbox" />',
		'title'           => __( 'Fund' ),
		'display_on_form' => __( 'Display on Donation Form' ),
	);

	return $columns;
}

// specify column content on funds list page
function seamless_donations_cpt_funds_column_contents( $column, $post_id ) {
	global $post;

	switch ( $column ) {
		case 'display_on_form' :

			/* Get the post meta. */
			$display_on_form = get_post_meta( $post_id, '_dgx_donate_fund_show', true );

			/* If none is found, output a default message. */
			if ( empty( $display_on_form ) ) {
				echo __( '<i>not specified</i>' );
			} /* If there is a duration, append 'minutes' to the text string. */
			else {
				echo __( $display_on_form );
			}

			break;
	}
}

//// SETUP SORTING
///
function seamless_donations_cpt_funds_sortable_columns( $columns ) {
	$columns['display_on_form'] = 'display_on_form';

	return $columns;
}

// add special body class to customize the funds list page
function seamless_donations_cpt_funds_list_class_hook( $classes ) {
	$classes .= ' seamless_donations_cpt_funds_list';

	return $classes;
}

// make sure to check for sort orders
function seamless_donations_cpt_funds_list_sort_order( $vars ) {
	if ( isset( $vars['orderby'] ) && '_dgx_donate_fund_show' == $vars['orderby'] ) {
		/* Merge the query vars with our custom variables. */
		$vars = array_merge(
			$vars,
			array(
				'meta_key' => '_dgx_donate_fund_show',
				'orderby'  => 'meta_value_num',
			)
		);
	}

	return $vars;
}

//// SETUP CSS HOOKS
///
// only run this when on an edit.php page, which is a list page for post types
function seamless_donations_cpt_list_page_actions() {
	add_filter( 'request', 'seamless_donations_cpt_funds_list_page_request_hook' );
}

// only run this when we're on the funds post type
function seamless_donations_cpt_funds_list_page_request_hook( $vars ) {
	if ( isset( $vars['post_type'] ) && $vars['post_type'] == 'funds' ) {
		// adds special body class to customize the display of the donor list page
		add_filter( 'admin_body_class', 'seamless_donations_cpt_funds_list_class_hook' );

		$vars = seamless_donations_cpt_funds_list_sort_order( $vars );
	}

	return $vars;
}

