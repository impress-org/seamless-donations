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

function seamless_donations_admin_debug_mode_msg() {
    echo "<div class=\"error\">";
    echo "<p>";
    echo esc_html__(
        'Warning - Seamless Donations is currently in debug mode (security may be compromised). ' .
        'Turn off in Seamless Donations -> Settings -> Debug Mode.',
        'seamless-donations');
    echo "</p>";
    echo "</div>";
}

function seamless_donations_admin_new_support_msg() {
    $new_support_url = '<A href="http://zatzlabs.com/forums/">Seamless Donations Community Forums</A>. ';
    $ticket_url      = '<A href="http://zatzlabs.com/submit-ticket/">open a ticket</A>.';

    echo "<div class=\"error\">";
    echo "<p>";
    echo esc_html__(
        'Notice - Seamless Donations support is no ' .
        'longer provided on the WordPress.org forums. Please visit the new ',
        'seamless-donations');
    echo $new_support_url;
    echo esc_html__('If you need a timely reply from the developer, please ', 'seamless-donations');
    echo $ticket_url;
    echo "</p>";
    echo "</div>";
}

function dgx_donate_admin_sandbox_msg() {
    echo "<div class=\"error\">";
    echo "<p>";
    echo esc_html__('Warning - Seamless Donations is currently configured to use the sandbox test server.',
        'seamless-donations');
    echo "</p>";
    echo "</div>";
}

function seamless_donations_5000_disabled_addon_message() {
    $pre_5_licenses = get_option('dgx_donate_5000_deactivated_addons');

    $ood_msg = " If you're getting this message, please delete the add-on's folder";
    $ood_msg .= " from the wp-content/plugins folder on the server. You can then";
    $ood_msg .= " use WordPress's Add New plugin feature to upload a compatible version.";
    $ood_msg .= " If you need to download the latest version of the add-on, go to ";
    $ood_msg .= '<A HREF="https://zatzlabs.com/account/">your account page</A>,';
    $ood_msg .= " click on View Details and Downloads from your Purchase History,";
    $ood_msg .= " and at the bottom of the page, you'll see a link to the 2.0 version";
    $ood_msg .= " of the add-on. Download that and install it on your site.<br><br>";
    $ood_msg .= 'If you run into any snags at all, <A HREF="https://zatzlabs.com/submit-ticket/">open a ticket</A>.';

    $section_desc = 'Warning - The following Seamless Donations add-ons are incompatible with this version of Seamless Donations and have been disabled: ';
    $section_desc .= $pre_5_licenses;
    $section_desc .= '.<br><br>You will need to upgrade these add-ons before you can use them again.' . $ood_msg;
    echo "<div class=\"error\">";
    echo "<p>";
    echo __($section_desc, 'seamless-donations');
    echo "</p>";
    echo "</div>";
}

// tell users that there is a new version and that they need to update
function seamless_donations_sd40_update_alert_message() {
    if (isset ($_REQUEST['page'])) {
        if ($_REQUEST['page'] != 'dgx_donate_menu_page') {
            $url = get_admin_url() . "admin.php?page=dgx_donate_menu_page";
            echo "<div class=\"error\">";
            echo "<p>";
            echo esc_html__(
                'Alert - Seamless Donations has had a major update. ', 'seamless-donations');
            echo '<A HREF="' . $url . '">Click here</A> ';
            echo esc_html__(
                'to learn about enabling your new features ', 'seamless-donations');
            echo esc_html__(
                '(they will remain off until you manually enable them).', 'seamless-donations');
            echo "</p>";
            echo "</div>";
        }
    }
}
