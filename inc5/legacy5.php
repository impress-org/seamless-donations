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
if (!defined('ABSPATH')) exit;

function seamless_donations_addon_version_check($addon, $version) {
    // validation code that will run in later versions to disable plugins, if necessary
    // false prevents the add-ons from doing anything
    return true;
}

function seamless_donations_addon_legacy_addons_still_loaded() {
    $bwp = "seamless-donations-basic-widget-pack/seamless-donations-basic-widget-pack.php";
    $dlm = "seamless-donations-delete-monster/seamless-donations-delete-monster.php";
    $glm = "seamless-donations-giving-level-manager/seamless-donations-giving-level-manager.php";
    $tye = "seamless-donations-thankyou-enhanced/seamless-donations-thankyou-enhanced.php";

    // this could be an array and a loop, but it's not
    $plugins = get_plugins();
    if (isset($plugins[$bwp])) {
        if (substr($plugins[$bwp]["Version"], 0, 1) == '1') {
            return true;
        }
    }
    if (isset($plugins[$dlm])) {
        if (substr($plugins[$dlm]["Version"], 0, 1) == '1') {
            return true;
        }
    }
    if (isset($plugins[$glm])) {
        if (substr($plugins[$glm]["Version"], 0, 1) == '1') {
            return true;
        }
    }
    if (isset($plugins[$tye])) {
        if (substr($plugins[$tye]["Version"], 0, 1) == '1') {
            return true;
        }
    }

    return false;
}

function seamless_donations_sd4_plugin_load_check() {
    $skip_addon_check = get_option('dgx_donate_legacy_addon_check');
    if ($skip_addon_check != 'on') {
        // deactivate legacy plugins on site load
        $bwp = "seamless-donations-basic-widget-pack/seamless-donations-basic-widget-pack.php";
        $dlm = "seamless-donations-delete-monster/seamless-donations-delete-monster.php";
        $glm = "seamless-donations-giving-level-manager/seamless-donations-giving-level-manager.php";
        $tye = "seamless-donations-thankyou-enhanced/seamless-donations-thankyou-enhanced.php";

        // this could be an array and a loop, but it's not
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $plugins = get_plugins();
        if (isset($plugins[$bwp])) {
            if (substr($plugins[$bwp]["Version"], 0, 1) == '1') {
                deactivate_plugins($bwp);
                flush_rewrite_rules();
                remove_filter(
                    'seamless_donations_admin_licenses_section_registration_options',
                    'seamless_donations_bwp_admin_licenses_section_registration_options');
            }
        }
        if (isset($plugins[$dlm])) {
            if (substr($plugins[$dlm]["Version"], 0, 1) == '1') {
                deactivate_plugins($dlm);
                flush_rewrite_rules();
                remove_filter(
                    'seamless_donations_admin_licenses_section_registration_options',
                    'seamless_donations_dm_admin_licenses_section_registration_options');
            }
        }
        if (isset($plugins[$glm])) {
            if (substr($plugins[$glm]["Version"], 0, 1) == '1') {
                deactivate_plugins($glm);
                flush_rewrite_rules();
                remove_filter(
                    'seamless_donations_admin_licenses_section_registration_options',
                    'seamless_donations_glm_admin_licenses_section_registration_options');
            }
        }
        if (isset($plugins[$tye])) {
            if (substr($plugins[$tye]["Version"], 0, 1) == '1') {
                deactivate_plugins($tye);
                flush_rewrite_rules();
                remove_filter(
                    'seamless_donations_admin_licenses_section_registration_options',
                    'seamless_donations_tye_admin_licenses_section_registration_options');
            }
        }
    }
}

function seamless_donations_sd4_plugin_filter_remove() {
    // deactivate legacy plugins on site load
    $bwp = "seamless-donations-basic-widget-pack/seamless-donations-basic-widget-pack.php";
    $dlm = "seamless-donations-delete-monster/seamless-donations-delete-monster.php";
    $glm = "seamless-donations-giving-level-manager/seamless-donations-giving-level-manager.php";
    $tye = "seamless-donations-thankyou-enhanced/seamless-donations-thankyou-enhanced.php";

    // this could be an array and a loop, but it's not
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $plugins = get_plugins();
    if (isset($plugins[$bwp])) {
        if (substr($plugins[$bwp]["Version"], 0, 1) == '1') {
            remove_filter(
                'seamless_donations_admin_licenses_section_registration_options',
                'seamless_donations_bwp_admin_licenses_section_registration_options');
        }
    }
    if (isset($plugins[$dlm])) {
        if (substr($plugins[$dlm]["Version"], 0, 1) == '1') {
            remove_filter(
                'seamless_donations_admin_licenses_section_registration_options',
                'seamless_donations_dm_admin_licenses_section_registration_options');
        }
    }
    if (isset($plugins[$glm])) {
        if (substr($plugins[$glm]["Version"], 0, 1) == '1') {
            remove_filter(
                'seamless_donations_admin_licenses_section_registration_options',
                'seamless_donations_glm_admin_licenses_section_registration_options');
        }
    }
    if (isset($plugins[$tye])) {
        if (substr($plugins[$tye]["Version"], 0, 1) == '1') {
            remove_filter(
                'seamless_donations_admin_licenses_section_registration_options',
                'seamless_donations_tye_admin_licenses_section_registration_options');
            remove_filter('seamless_donations_admin_thanks_section_note',
                'seamless_donations_tye_admin_thanks_section_note');
        }
    }
}

function seamless_donations_sd4_plugin_reactivate_check() {
    // exit legacy plugins when user attempts to reactivate
    $bwp = "seamless-donations-basic-widget-pack/seamless-donations-basic-widget-pack.php";
    $dlm = "seamless-donations-delete-monster/seamless-donations-delete-monster.php";
    $glm = "seamless-donations-giving-level-manager/seamless-donations-giving-level-manager.php";
    $tye = "seamless-donations-thankyou-enhanced/seamless-donations-thankyou-enhanced.php";

    $bwp_msg = "The Seamless Donations Basic Widget Pack add-on";
    $dlm_msg = "The Seamless Donations Delete Monster add-on";
    $glm_msg = "The Seamless Donations Giving Level Manager add-on";
    $tye_msg = "The Seamless Donations Thank You Enhanced add-on";

    $ood_msg = " is incompatible with the new 5.0 version of Seamless Donations.";
    $ood_msg .= " If you're getting this message, please delete the add-on's folder";
    $ood_msg .= " from the wp-content/plugins folder on the server. You can then";
    $ood_msg .= " use WordPress's Add New plugin feature to upload a compatible version.<br><br>";
    $ood_msg .= "If you need to download the latest version of the add-on, go to ";
    $ood_msg .= '<A HREF="https://zatzlabs.com/account/">your account page</A>,';
    $ood_msg .= " click on View Details and Downloads from your Purchase History,";
    $ood_msg .= " and at the bottom of the page, you'll see a link to the 2.0 version";
    $ood_msg .= " of the add-on. Download that and install it on your site.<br><br>";
    $ood_msg .= 'If you run into any snags at all, <A HREF="https://zatzlabs.com/submit-ticket/">open a ticket</A>.';

    // todo look for activation by hand of legacy plugin and stop it
    // this could be an array and a loop, but it's not
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $plugins = get_plugins();
    if (isset($plugins[$bwp])) {
        if (substr($plugins[$bwp]["Version"], 1, 1) == '1') {
            exit($bwp_msg . $ood_msg);
        }
    }
    if (isset($plugins[$dlm])) {
        if (substr($plugins[$dlm]["Version"], 0, 1) == '1') {
            exit($bwp_msg . $ood_msg);
        }
    }
    if (isset($plugins[$glm])) {
        if (substr($plugins[$glm]["Version"], 0, 1) == '1') {
            exit($bwp_msg . $ood_msg);
        }
    }
    if (isset($plugins[$tye])) {
        if (substr($plugins[$tye]["Version"], 0, 1) == '1') {
            exit($bwp_msg . $ood_msg);
        }
    }
}

function seamless_donations_sd4_plugin_reactivation_check() {
    $check_dir_bwp = WP_CONTENT_DIR . '/plugins/' . "seamless-donations-basic-widget-pack/seamless-donations-basic-widget-pack.php";
    $check_dir_dlm = WP_CONTENT_DIR . '/plugins/' . "seamless-donations-delete-monster/seamless-donations-delete-monster.php";
    $check_dir_glm = WP_CONTENT_DIR . '/plugins/' . "seamless-donations-giving-level-manager/seamless-donations-giving-level-manager.php";
    $check_dir_tye = WP_CONTENT_DIR . '/plugins/' . "seamless-donations-thankyou-enhanced/seamless-donations-thankyou-enhanced.php";

    register_deactivation_hook($check_dir_bwp, 'seamless_donations_sd4_plugin_reactivate_check');
    register_deactivation_hook($check_dir_dlm, 'seamless_donations_sd4_plugin_reactivate_check');
    register_deactivation_hook($check_dir_glm, 'seamless_donations_sd4_plugin_reactivate_check');
    register_deactivation_hook($check_dir_tye, 'seamless_donations_sd4_plugin_reactivate_check');
}

function seamless_donations_sd5004_debug_mode_update() {
    $mode = get_option('dgx_donate_debug_mode');
    if ($mode == 1) {
        update_option('dgx_donate_debug_mode', 'VERBOSE');
    }
}