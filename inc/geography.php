<?php
/*
Seamless Donations by David Gewirtz, adopted from Allen Snook

Lab Notes: http://zatzlabs.com/lab-notes/
Plugin Page: http://zatzlabs.com/seamless-donations/
Contact: http://zatzlabs.com/contact-us/

Copyright (c) 2015 by David Gewirtz
*/

/* Abbreviations for Countries
 * lists country codes defined by ISO 3166-1
 * Based on list at https://developer.paypal.com/docs/classic/api/country_codes/
 */
function dgx_donate_get_countries() {
	$countries = array(
		'AX' => 'Aland Islands',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua and Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',

		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia-Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei Darussalam',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',

		'KM' => 'Cambodia',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Colombia',
		'KM' => 'Comoros',
		'CD' => 'Democratic Republic of Congo',
		'CG' => 'Congo',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'HR' => 'Croatia',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',

		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',

		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'ER' => 'Eriteria',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',

		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'GF' => 'French Guiana',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',

		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GG' => 'Guernsey',
		'GN' => 'Guinea',
		'GW' => 'Guinea Bissau',
		'GY' => 'Guyana',

		'HM' => 'Heard Island / McDonald Islands',
		'VA' => 'Holy See (Vatican)',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',

		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IE' => 'Ireland',
		'IM' => 'Isle of Man',
		'IL' => 'Israel',
		'IT' => 'Italy',

		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JE' => 'Jersey',
		'JO' => 'Jordan',

		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KR' => 'Korea, Republic of',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',

		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LS' => 'Lesotho',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',

		'MO' => 'Macao',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia, Federated States of',
		'MD' => 'Moldova, Republic of',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'ME' => 'Montenegro',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',

		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Calendonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',

		'OM' => 'Oman',

		'PW' => 'Palau',
		'PS' => 'Palestine',
		'PA' => 'Panama',
		'PY' => 'Paraguay',
		'PG' => 'Papua New Guinea',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',

		'QA' => 'Qatar',

		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RS' => 'Republic of Serbia',
		'RU' => 'Russian Federation',
		'RW' => 'Rwanda',

		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts and Nevis',
		'LC' => 'Saint Lucia',
		'PM' => 'Saint Pierre and Miquelon',
		'VC' => 'Saint Vincent / Grenadines',
		'WS' => 'Samoa',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome and Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia / South Sandwich',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard and Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',

		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania, United Republic of',
		'TH' => 'Thailand',
		'TL' => 'Timor-Leste',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad and Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks and Caicos Islands',
		'TV' => 'Tuvalu',

		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'GB' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'US Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',

		'VU' => 'Vanuatu',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'VG' => 'Virgin Islands, British',
		'VI' => 'Virgin Islands, U.S.',

		'WF' => 'Wallis and Futuna',
		'EH' => 'Western Sahara',

		'YE' => 'Yemen',

		'ZM' => 'Zambia'
	);

	$countries = apply_filters (
		'seamless_donations_geography_country_list', $countries );

	return $countries;
}

/*
 * From http://about.usps.com/publications/pub141/section-2-references.htm
 */
function dgx_donate_get_countries_requiring_postal_code() {
	$countries = array(
		'AU', 'AT',
		'BE', 'BR',
		'CA', 'CN', 'CZ',
		'DK',
		'FO', 'FI', 'FR',
		'DE', 'GR', 'GL',
		'HU',
		'IN', 'ID', 'IT',
		'JP',
		'KR',
		'LI', 'LU',
		'MY', 'MX', 'MC',
		'NL', 'NO',
		'PH', 'PL', 'PT',
		'RU',
		'SZ', 'ZA', 'ES', 'SE', 'CH',
		'TH', 'TR', 'SG',
		'GB', 'US'
	);

	return $countries;
}

function dgx_donate_country_requires_postal_code( $country_code ) {
	return in_array( $country_code, dgx_donate_get_countries_requiring_postal_code() );
}

/**
 * Abbreviations for U.S. States
 * Based on list at https://developer.paypal.com/docs/classic/api/StateandProvinceCodes/
 */
function dgx_donate_get_states() {
	$states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AS' => 'American Samoa',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FM' => 'Federated States of Micronesia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'GU' => 'Guam',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MH' => 'Marshall Islands',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'MP' => 'Northern Mariana Islands',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PW' => 'Palau',
		'PA' => 'Pennsylvania',
		'PR' => 'Puerto Rico',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VI' => 'Virgin Islands',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		'AA' => 'Armed Forces Americas',
		'AE' => 'Armed Forces',
		'AP' => 'Armed Forces Pacific'
	);

	$states = apply_filters (
		'seamless_donations_geography_state_list', $states );

	return $states;
}

/*
 * Abbreviations for Canadian Provinces
 * Based on list at https://developer.paypal.com/docs/classic/api/StateandProvinceCodes/
 */
function dgx_donate_get_provinces() {
	$provinces = array(
		'AB' => 'Alberta',
		'BC' => 'British Columbia',
		'MB' => 'Manitoba',
		'NB' => 'New Brunswick',
		'NL' => 'Newfoundland and Labrador',
		'NT' => 'Northwest Territories',
		'NS' => 'Nova Scotia',
		'NU' => 'Nunavut',
		'ON' => 'Ontario',
		'PE' => 'Prince Edward Island',
		'QC' => 'Quebec',
		'SK' => 'Saskatchewan',
		'YT' => 'Yukon'
	);

	$provinces = apply_filters (
		'seamless_donations_geography_province_list', $provinces );

	return $provinces;
}

function dgx_donate_get_country_selector( $select_name, $select_initial_value)
{
	$output = "<select class='dgx_donate_country_select' id='" . esc_attr( $select_name ) . "' name='" . esc_attr( $select_name ) . "'>";

	$countries = dgx_donate_get_countries();

	foreach ( $countries as $country_code => $country_name ) {
		$selected = "";
		if ( strcasecmp( $select_initial_value, $country_code ) == 0 ) {
			$selected = " selected ";
		}
		$output .= "<option value='" . esc_attr( $country_code ) . "'" . esc_attr( $selected ) . ">" . esc_html( $country_name ) ."</option>";
	}

	$output .= "</select>";

	return $output;
}

function dgx_donate_get_state_selector( $select_name, $select_initial_value)
{
	$output = "<select class='dgx_donate_state_select' id='" . esc_attr( $select_name ) . "' name='" . esc_attr( $select_name ) . "'>";

	$states = dgx_donate_get_states();

	foreach ( $states as $state_abbr => $state_name ) {
		$selected = "";
		if ( strcasecmp( $select_initial_value, $state_abbr ) == 0 ) {
			$selected = " selected ";
		}
		$output .= "<option value='" . esc_attr( $state_abbr ) . "'" . esc_attr( $selected ) . ">" . esc_html( $state_name ) ."</option>";
	}

	$output .= "</select>";

	return $output;
}

function dgx_donate_get_province_selector( $select_name, $select_initial_value)
{
	$output = "<select class='dgx_donate_province_select' id='" . esc_attr( $select_name ) . "' name='" . esc_attr( $select_name ) . "'>";

	$provinces = dgx_donate_get_provinces();

	foreach ( $provinces as $province_abbr => $province_name ) {
		$selected = "";
		if ( strcasecmp( $select_initial_value, $province_abbr ) == 0 ) {
			$selected = " selected ";
		}
		$output .= "<option value='" . esc_attr( $province_abbr ) . "'" . esc_attr( $selected ) . ">" . esc_html( $province_name ) ."</option>";
	}

	$output .= "</select>";

	return $output;
}
