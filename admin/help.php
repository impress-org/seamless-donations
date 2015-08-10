<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

function seamless_donations_admin_help( $setup_object ) {

	// Help/FAQ
	$setup_object->addSubMenuPage(
		array(
			'title'     => __( 'Help/FAQ', 'seamless-donations' ),
			'page_slug' => 'seamless_donations_help',
		)
	);
}