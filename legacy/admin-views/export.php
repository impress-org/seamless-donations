<?php

/* Copyright 2013 Allen Snook (email: allendav@allendav.com) */

include( '../../../../wp-config.php' );

if ( ! current_user_can( 'manage_options' ) ) {

	header( "Content-Type: text/html" );
	echo "<html>\n";
	echo "<head>\n";
	echo "<title>Unauthorized</title>\n";
	echo "</head>\n";
	echo "<body>\n";
	echo "<h1>Unauthorized</h1>\n";
	echo "<p>You do not have permission to access this page.  Did you forget to log in?</p>";
	echo "</body>\n";
	echo "</html>\n";
}
else
{
	$startDate = $_POST['startdate'];
	$endDate = $_POST['enddate'];

	$startTimeStamp = strtotime( $startDate );
	$endTimeStamp = strtotime( $endDate );
	$countries = dgx_donate_get_countries();

	$firstOne = true;
	$post_type = 'dgx-donation';

	$args = array(
		'numberposts'     => '-1',
		'post_type'       => $post_type,
		'orderby'         => 'post_date',
		'order'           => 'DESC'
	);

	$myDonations = get_posts( $args );

	foreach ( (array) $myDonations as $myDonation )
    {
		$donationID = $myDonation->ID;

		// Make sure this donation's date falls in the range
		$okToAdd = true;
		$year = get_post_meta( $donationID, '_dgx_donate_year', true );
		$month = get_post_meta( $donationID, '_dgx_donate_month', true );
		$day = get_post_meta( $donationID, '_dgx_donate_day', true );
		$donationDate = $month . "/" . $day . "/" . $year;
		$donationTimeStamp = strtotime( $donationDate );

		if ( $donationTimeStamp < $startTimeStamp )
		{
			$okToAdd = false;
		}

		if ( $donationTimeStamp > $endTimeStamp )
		{
			$okToAdd = false;
		}

		if ( $okToAdd )
		{
			// Order in CSV
			// Date (MM/DD/YYYY), Time (HH:MM:SS A), First Name, Last Name, Amount, Repeating (YES/NO),
			// Designated Fund, Gift Item, Donor Phone, Donor Email, Donor Address, Donor Address2,
			// Donor City, Donor State, Donor Zip, OK to Add to Mailing List (YES/NO)

			if ( $firstOne )
			{
				header( "Content-type: text/csv" );
				header( "Content-Disposition: attachment; filename=export.csv" );

				echo "\"Date\",\"Time\",\"First Name\",\"Last Name\",\"Amount\",\"Currency\",\"Repeating\",";
				echo "\"Designated Fund\",\"Gift Item\",\"Phone\",\"Email\",\"Address\",\"Address 2\",";
				echo "\"City\",\"State/Prov\",\"Postal Code\",\"Country\",\"Employer\",\"Occupation\",";
				echo "\"OK to Add to Mailing List\"\n";

				$firstOne = false;
			}

			$time = get_post_meta( $donationID, '_dgx_donate_time', true );
			$firstName = get_post_meta( $donationID, '_dgx_donate_donor_first_name', true );
			$lastName = get_post_meta( $donationID, '_dgx_donate_donor_last_name', true );
			$amount = get_post_meta( $donationID, '_dgx_donate_amount', true );

			$currency_code = dgx_donate_get_donation_currency_code( $donationID );
			$formatted_amount = dgx_donate_get_plain_formatted_amount( $amount, 2, $currency_code, false );
			$repeating = get_post_meta( $donationID, '_dgx_donate_repeating', true );
			if ( empty( $repeating ) )
			{
				$repeating = "No";
			}
			else
			{
				$repeating = "Yes";
			}
			$designatedFundName = "Undesignated";
			$designated = get_post_meta( $donationID, '_dgx_donate_designated', true );
			if ( ! empty( $designated ) )
			{
				$designatedFundName = get_post_meta( $donationID, '_dgx_donate_designated_fund', true );
			}
			$gift_item_id = get_post_meta( $donationID, '_dgx_donate_gift_item_id', true );
			$gift_item_title = "";
			if ( !empty( $gift_item_id ) ) {
				$gift_item_title = get_the_title( $gift_item_id );
			}
			$phone = get_post_meta( $donationID, '_dgx_donate_donor_phone', true );
			$email = get_post_meta( $donationID, '_dgx_donate_donor_email', true );
			$address = get_post_meta( $donationID, '_dgx_donate_donor_address', true );
			$address2 = get_post_meta( $donationID, '_dgx_donate_donor_address2', true );
			$city = get_post_meta( $donationID, '_dgx_donate_donor_city', true );
			$state = get_post_meta( $donationID, '_dgx_donate_donor_state', true );
			$province = get_post_meta( $donationID, '_dgx_donate_donor_province', true );
			$country_code = get_post_meta( $donationID, '_dgx_donate_donor_country', true );

			if ( empty( $country_code ) ) { /* older versions only did US */
				$country_code = 'US';
				update_post_meta( $donationID, '_dgx_donate_donor_country', 'US' );
			}

			if ( 'US' == $country_code ) {
				$state_or_prov = $state;
			} else if ( 'CA' == $country_code ) {
				$state_or_prov = $province;
			} else {
				$state_or_prov = '';
			}

			$country = $countries[$country_code];

			$zip = get_post_meta( $donationID, '_dgx_donate_donor_zip', true );
			$addToMailingList = get_post_meta( $donationID, '_dgx_donate_add_to_mailing_list', true );
			if ( strcasecmp( $addToMailingList, 'on' ) == 0 )
			{
				$addToMailingList = "Yes";
			}
			else
			{
				$addToMailingList = "No";
			}

			$employer = get_post_meta( $donationID, '_dgx_donate_employer_name', true );
			$occupation = get_post_meta( $donationID, '_dgx_donate_occupation', true );

			echo "\"$donationDate\",\"$time\",\"$firstName\",\"$lastName\",\"$formatted_amount\",\"$currency_code\",\"$repeating\",";
			echo "\"$designatedFundName\",\"$gift_item_title\",\"$phone\",\"$email\",\"$address\",\"$address2\",";
			echo "\"$city\",\"$state_or_prov\",\"$zip\",\"$country\",\"$employer\",\"$occupation\",";
			echo "\"$addToMailingList\"\n";
		}
	}

	if ( $firstOne ) // We never got any data
	{
		header( "Content-Type: text/html" );
		echo "<html>\n";
		echo "<head>\n";
		echo "<title>Error</title>\n";
		echo "</head>\n";
		echo "<body>\n";
		echo "<h1>Error</h1>\n";
		echo "<p>No data was found for that date range ($startDate - $endDate).  Please try a wider date range.</p>";
		echo "</body>\n";
		echo "</html>\n";
	}
}
