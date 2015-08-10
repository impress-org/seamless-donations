<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

class SeamlessDonationsFundsPostType extends SeamlessDonationsAdminPageFramework_PostType {

	/**
	 * Automatically called with the 'wp_loaded' hook.
	 */
	public function setUp () {

		$funds_type_setup = array();
		$funds_setup
		                  = array( // argument - http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
		                           'labels'    => array(
			                           'name'                => __ ( 'Funds', 'seamless-donations' ),
			                           'singular_name'       => __ ( 'Fund', 'seamless-donations' ),
			                           'add_new_item'        => __ ( 'Fund', 'seamless-donations' ),
			                           'edit_item'           => __ ( 'Fund', 'seamless-donations' ),
			                           'new_item'            => __ ( 'Fund', 'seamless-donations' ),
			                           'view_item'           => __ ( 'Fund', 'seamless-donations' ),
			                           'search_items'        => __ ( 'Search funds', 'seamless-donations' ),
			                           'not_found'           => __ ( 'No funds found', 'seamless-donations' ),
			                           'not_found_in_trash'  => __ ( 'No funds found in Trash', 'seamless-donations' ),
			                           'restored_from_trash' => __ ( 'fund', 'seamless-donations' ),
		                           ),
		                           'supports'  => array( 'title' ),
		                           'public'    => true,
		                           'menu_icon' => 'dashicons-palmtree',

		);

		// check to see if compact menus has been enabled, which tucks menu item under donations
		// add the following elements to the array
		// not adding yet because not yet able to do this AND add tab in main form, so this is temp code for now
		// 'show_ui'               => true,
		// 'show_in_menu'          => 'SeamlessDonationsAdmin',

		$funds_setup      = apply_filters ( 'seamless_donations_funds_setup', $funds_setup );
		$funds_type_setup = apply_filters ( 'seamless_donations_funds_type_setup', $funds_type_setup );

		$this->setArguments ( $funds_setup );

		if( sizeof ( $funds_type_setup ) > 0 ) {
			$this->addTaxonomy (
				'fund_types',  // taxonomy slug
				$funds_type_setup );
		}
	}

	public function columns_funds ( $aHeaderColumns ) {

		return array(
			       'cb'    => '<input type="checkbox" />', // Checkbox for bulk actions.
			       'title' => __ ( 'Title', 'seamless-donations' ),
			       'color' => __ ( 'Display on Donation Form', 'seamless-donations' ),
		       ) + $aHeaderColumns;
	}

	public function cell_funds_color ( $sCell, $iPostID ) { // cell_{post type}_{column key}

		$_show = get_post_meta ( $iPostID, '_dgx_donate_fund_show', true );

		return esc_attr ( $_show );
	}

	public function style_SeamlessDonationsFundsPostType ( $sStyle ) {

		return $sStyle . "
        .color-sample-container {
            height: 3em;
        }
        .color-sample-container p {
            border: solid 1px #CCC;
            width: 3em;
            height: 100%;
        }
    ";
	}
}

class SeamlessDonationsFundsCustomPostTypeMetaBox extends SeamlessDonationsAdminPageFramework_MetaBox {

	/*
	 * Use the setUp() method to define settings of this meta box.
	 */
	public function setUp () {

		/**
		 * Adds setting fields in the meta box.
		 */
		$this->addSettingFields (

			array( // Single set of radio buttons
			       'field_id'    => '_dgx_donate_fund_show',
			       'title'       => __ ( 'Display on donation form', 'seamless-donations' ),
			       'type'        => 'radio',
			       'label'       => array(
				       'Yes' => 'Yes',
				       'No'  => 'No',
			       ),
			       'default'     => 'Yes',
			       //'after_label' => '<br />',
			       //'attributes'  => array(
			       //   'c' => array(
			       //       'disabled' => 'disabled',
			       //   ),
			       //),
			       'description' => __ (
				       'If you select Yes, this fund will be shown on the front-end donation form.' .
				       '<br>If you select No, this fund will not be shown on the donation form.',
				       'seamless-donations' )
			)
		);
	}
}

// todo make sure user can't add duplicate fund names
new SeamlessDonationsFundsPostType( 'funds' );
new SeamlessDonationsFundsCustomPostTypeMetaBox(
	null,   // meta box ID - can be null.
	__ ( 'Fund Settings', 'seamless-donations' ), // title
	array( 'funds' ),                 // post type slugs: post, page, etc.
	'normal',                                        // context
	'low'                                          // priority
);
