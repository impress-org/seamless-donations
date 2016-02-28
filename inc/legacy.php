<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

// manage the legacy import from Allen's original code and any format conversions for new data structures

// for 4.0.12 convert funds to ids
function seamless_donations_4012_update_indexes () {

	// prior to 4.0.12, donation records did not save fund ids
	// (even though it could have been a one-line fix, argh!)

	$indexes_updated = get_option ( 'dgx_donate_4012_indexes_updated' );

	if( ! $indexes_updated ) {

		seamless_donations_rebuild_funds_index ();
		seamless_donations_rebuild_donor_index ();

		$plugin_version = 'sd4012';
		update_option ( 'dgx_donate_4012_indexes_updated', $plugin_version );
	}
}

// for 4.0.13 add anonymous flag to donors
function seamless_donations_4013_update_anon () {

	// prior to 4.0.13, donor records did not save anonymous requests
	// now, if any donation requests anonymity, the donor is marked anon

	$anon_updated = get_option ( 'dgx_donate_4013_anon_updated' );

	if( ! $anon_updated ) {

		seamless_donations_rebuild_donor_anon_flag ();

		$plugin_version = 'sd4013';
		update_option ( 'dgx_donate_4013_anon_updated', $plugin_version );
	}
}

// tell users that there is a new version and that they need to update
function seamless_donations_sd40_update_alert_message () {

	if( isset ( $_REQUEST['page'] ) ) {
		if( $_REQUEST['page'] != 'dgx_donate_menu_page' ) {
			$url = get_admin_url () . "admin.php?page=dgx_donate_menu_page";
			echo "<div class=\"error\">";
			echo "<p>";
			echo esc_html__ (
				'Alert - Seamless Donations has had a major update. ', 'seamless-donations' );
			echo '<A HREF="' . $url . '">Click here</A> ';
			echo esc_html__ (
				'to learn about enabling your new features ', 'seamless-donations' );
			echo esc_html__ (
				'(they will remain off until you manually enable them).', 'seamless-donations' );
			echo "</p>";
			echo "</div>";
		}
	}
}

function seamless_donations_sd40_process_upgrade_check () {

	if( isset( $_POST['upgrade'] ) ) {
		if( $_POST['upgrade'] == 'sd40' ) {
			if( check_admin_referer ( 'upgrade_seamless_donations_sd40' ) ) {
				// now we need to determine if we've already updated to 4.0+ or not
				$sd4_mode = get_option ( 'dgx_donate_start_in_sd4_mode' );
				if( $sd4_mode == false ) {
					ob_start (); // start buffering output to allow for the redirect to work

					// do the upgrade
					if( ! seamless_donations_funds_was_legacy_imported () ) {
						seamless_donations_funds_legacy_import ();
					}
					if( ! seamless_donations_donations_was_legacy_imported () ) {
						seamless_donations_donations_legacy_import ();
					}
					if( ! seamless_donations_donors_was_legacy_imported () ) {
						seamless_donations_donors_legacy_import ();
					}
					update_option ( 'dgx_donate_start_in_sd4_mode', 'true' );

					$url = get_admin_url () . "admin.php?page=seamless_donations_admin_main";
					wp_redirect ( $url );
					exit;
					//echo "<h1><strong>Welcome to Seamless Donations 4.0 - UPGRADE SELECTED</strong></h1>";
				}
			}
		}
	}
}

function seamless_donations_sd40_upgrade_form () {

	$url = get_admin_url () . "admin.php?page=seamless_donations_admin_main";
	?>
	<div class="error below-h2">
		<h1><strong>Welcome to Seamless Donations 4.0</strong></h1>

		<h2>This update modifies your data and donation form layout. It has the potential to cause breakage.</h2>

		<h2>Be sure to <span style="background: red; color: white;">backup and test on a staging server</span>
			before updating your live server.</h2>

		<P>In March 2015, Seamless Donations was adopted by David Gewirtz. Since then, David has been working hard
			to
			bring you great new features and prepare Seamless Donations for a future with lots of new capabilities
			that
			will help you raise funds and make the world a better place. If you'd like to learn more about the new
			features or dive deep into the development process, feel free to read David's <A HREF="">Seamless
				Donations
				Lab Notes</A>.</P>

		<H3><STRONG>Here are some of the new features you'll find in 4.0:</STRONG></H3>
		<UL>
			<LI><B>Updated, modern admin UI:</B> The admin interface has been updated to a modern tabbed-look.</LI>
			<LI><B>Custom post types:</B> Funds and donors have now been implemented as custom post types. This
				gives
				you the ability to use all of WordPress' post management and display tools with donors and funds.
				The
				donation data has always been a custom post type, but it is now also available to manipulate using
				plugins and themes outside of Seamless Donations.
			</LI>
			<LI><B>Designed for extensibility:</B> The primary design goal of 4.0 was to add hooks in the form of
				filters and actions that web designers can use to modify the behavior of Seamless Donations to fit
				individual needs. The plugin was re-architected to allow for loads of extensibility.
			</LI>
			<LI><B>Forms engine designed for extensibility:</B> Rather than just basic form code, Seamless Donations
				4.0
				now has a brand-new array-driven forms engine, which will give web site builders the ability to
				modify
				and access
				every part of the form before it is displayed to donors.
			</LI>
			<LI><B>Admin UI designed for extensibility:</B> Yep, like everything else, the admin interface has been
				designed to allow for extensibility.
			</LI>
			<LI><B>Translation-ready:</B> Seamless Donations 4.0 has had numerous tweaks to allow it to be
				translated
				into other languages.
			</LI>
		</UL>
		<h3><strong>Be sure to implement these changes and test</strong></h3>
		<UL>
			<LI><B>Change the form shortcode:</B> The [dgx-donate] shortcode is deprecated and will issue an update
				warning once you update. The new shortcode is [seamless-donations].
			</LI>
			<LI><B>Check your CSS:</B> Most of the CSS should remain the same, but because the form interaction has
				been
				updated, your CSS may change.
			</LI>
			<LI><B>Check your data:</B> Great pains have been taken to be sure the data migrates correctly, but
				please,
				please, PLEASE double-check it.
			</LI>
		</UL>
		<h3><strong>Please watch this "what to look for" video before you begin testing the beta release</strong>
		</h3>
		<iframe width="640" height="360" src="https://www.youtube.com/embed/SWm6GivlJi0?rel=0" frameborder="0"
		        allowfullscreen></iframe>
		<br><br>

		<form method="post" action="<?php echo $url; ?>">
			<?php wp_nonce_field ( 'upgrade_seamless_donations_sd40' ); ?>
			<input type="hidden" name="upgrade" value="sd40"/>
			<input type="submit" class="button button-primary"
			       value="I have made a backup. Let's do this upgrade!"/>
		</form>
		<p></p>
	</div>
	<?php
}

//********************** FUNDS LEGACY DATA MANAGEMENT *******************************

function seamless_donations_funds_was_legacy_imported () {

	$funds_imported = get_option ( 'dgx_donate_designated_funds_legacy_import' );

	if( ! $funds_imported ) {
		return false;
	} else {
		return true;
	}
}

function seamless_donations_funds_legacy_import () {

	$funds_imported = get_option ( 'dgx_donate_designated_funds_legacy_import' );

	if( ! $funds_imported ) {
		$fund_array = get_option ( 'dgx_donate_designated_funds' );
		while( $fund_option = current ( $fund_array ) ) {

			// initialize import of legacy data
			if( $fund_option == 'SHOW' ) {
				$meta_value = 'Yes';
			} else {
				$meta_value = 'No';
			}
			$fund_name = key ( $fund_array );
			$fund_name = sanitize_text_field ( $fund_name );

			// create the new custom fund post
			$post_array = array(
				'post_title'   => $fund_name,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'funds',
			);

			$post_id = wp_insert_post ( $post_array, true );

			// update the option
			if( $post_id != 0 ) {
				update_post_meta ( $post_id, '_dgx_donate_fund_show', $meta_value );
			}

			next ( $fund_array );
		}
		// write out the legacy import completed flag
		$plugin_version = 'sd40';
		update_option ( 'dgx_donate_designated_funds_legacy_import', $plugin_version );
	}
}

//********************** DONATIONS LEGACY DATA MANAGEMENT *******************************

function seamless_donations_donations_was_legacy_imported () {

	$donations_imported = get_option ( 'dgx_donate_donations_legacy_import' );

	if( ! $donations_imported ) {
		return false;
	} else {
		return true;
	}
}

function seamless_donations_donations_legacy_import () {

	$donations_imported = get_option ( 'dgx_donate_donations_legacy_import' );

	if( ! $donations_imported ) {
		// we need to convert the posts of type dgx-donation to type donation (the hyphen doesn't
		// work for some of the UI elements

		$args = array(
			'post_type'      => 'dgx-donation',
			'posts_per_page' => - 1,
		);

		$donation_array = get_posts ( $args );

		while( $donation_option = current ( $donation_array ) ) {

			set_post_type ( $donation_option->ID, 'donation' );

			next ( $donation_array );
		}

		// write out the legacy import completed flag
		$plugin_version = 'sd40';
		update_option ( '_dgx_donate_donations_legacy_import', $plugin_version );
	}
}

//********************** DONORS LEGACY DATA MANAGEMENT *******************************

function seamless_donations_donors_was_legacy_imported () {

	$donors_imported = get_option ( 'dgx_donate_donors_legacy_import' );

	if( ! $donors_imported ) {
		return false;
	} else {
		return true;
	}
}

function seamless_donations_donors_legacy_import () {

	$donors_imported = get_option ( 'dgx_donate_donors_legacy_import' );

	if( ! $donors_imported ) {
		// we need to scan the donations list (after converting to type seamless_donation)
		// and build up a list of donors from the donation list
		// *** DO NOT RUN UNTIL AFTER THE DONATION IMPORT ***

		$donor_array = array();

		// gather data from donations custom post type into $donor_array
		$args = array(
			'post_type'      => 'donation',
			'posts_per_page' => - 1,
		);

		$donation_array = get_posts ( $args );
		while( $donation_option = current ( $donation_array ) ) {

			$donation_id = $donation_option->ID;

			$first      = get_post_meta ( $donation_id, '_dgx_donate_donor_first_name', true );
			$last       = get_post_meta ( $donation_id, '_dgx_donate_donor_last_name', true );
			$email      = get_post_meta ( $donation_id, '_dgx_donate_donor_email', true );
			$employer   = get_post_meta ( $donation_id, '_dgx_donate_employer_name', true );
			$occupation = get_post_meta ( $donation_id, '_dgx_donate_occupation', true );
			$currency   = get_post_meta ( $donation_id, '_dgx_donate_donation_currency', true );

			$phone    = get_post_meta ( $donation_id, '_dgx_donate_donor_phone', true );
			$address  = get_post_meta ( $donation_id, '_dgx_donate_donor_address', true );
			$address2 = get_post_meta ( $donation_id, '_dgx_donate_donor_address2', true );
			$city     = get_post_meta ( $donation_id, '_dgx_donate_donor_city', true );
			$state    = get_post_meta ( $donation_id, '_dgx_donate_donor_state', true );
			$province = get_post_meta ( $donation_id, '_dgx_donate_donor_province', true );
			$country  = get_post_meta ( $donation_id, '_dgx_donate_donor_country', true );
			$zip      = get_post_meta ( $donation_id, '_dgx_donate_donor_zip', true );

			$name                               = $first . ' ' . $last;
			$donor_array[ $name ]['first']      = $first;
			$donor_array[ $name ]['last']       = $last;
			$donor_array[ $name ]['email']      = $email;
			$donor_array[ $name ]['employer']   = $employer;
			$donor_array[ $name ]['occupation'] = $occupation;
			$donor_array[ $name ]['currency']   = $currency;

			$donor_array[ $name ]['phone']    = $phone;
			$donor_array[ $name ]['address']  = $address;
			$donor_array[ $name ]['address2'] = $address2;
			$donor_array[ $name ]['city']     = $city;
			$donor_array[ $name ]['state']    = $state;
			$donor_array[ $name ]['province'] = $province;
			$donor_array[ $name ]['country']  = $country;
			$donor_array[ $name ]['zip']      = $zip;

			// create comma-separated list of donation IDs
			if( ! isset( $donor_array[ $name ]['donations'] ) ) {
				$donor_array[ $name ]['donations'] = $donation_id;
			} else {
				$donor_array[ $name ]['donations'] .= ',' . $donation_id;
			}

			next ( $donation_array );
		}

		// now move that data into a donor post type
		reset ( $donor_array );

		while( $donor = current ( $donor_array ) ) {
			$donor_name = key ( $donor_array );
			$donor_name = sanitize_text_field ( $donor_name );

			// create the new custom fund post
			$post_array = array(
				'post_title'   => $donor_name,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'donor',
			);

			$post_id = wp_insert_post ( $post_array, true );

			// update the options
			if( $post_id != 0 ) {
				update_post_meta ( $post_id, '_dgx_donate_donor_first_name', $donor['first'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_last_name', $donor['last'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_email', $donor['email'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_employer', $donor['employer'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_occupation', $donor['occupation'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_donations', $donor['donations'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_currency', $donor['currency'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_phone', $donor['phone'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_address', $donor['address'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_address2', $donor['address2'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_city', $donor['city'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_state', $donor['state'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_province', $donor['province'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_country', $donor['country'] );
				update_post_meta ( $post_id, '_dgx_donate_donor_zip', $donor['zip'] );

				// update the donations to point to this donor id
				$my_donations = explode ( ',', $donor['donations'] );

				if( count ( $my_donations ) > 0 ) {
					foreach( (array) $my_donations as $donation_id ) {
						update_post_meta ( $donation_id, '_dgx_donate_donor_id', $post_id );
					}
				}
			}

			next ( $donor_array );
		}

		// write out the legacy import completed flag
		$plugin_version = 'sd40';
		update_option ( 'dgx_donate_donors_legacy_import', $plugin_version );
	}
}

