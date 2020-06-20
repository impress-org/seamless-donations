<?php

/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

// Load WordPress
include "../../../wp-config.php";

// Load Seamless Donations Core
require_once './inc/geography.php';
require_once './inc/currency.php';
require_once './inc/utilities.php';
require_once './inc/legacy.php';
require_once './inc/donations.php';
require_once './inc/payment.php';

require_once './legacy/dgx-donate.php';
require_once './legacy/dgx-donate-admin.php';
require_once './seamless-donations-admin.php';
require_once './seamless-donations-form.php';
require_once './dgx-donate-paypalstd.php';

seamless_donations_process_payment();
