/*
 * Seamless Donations by David Gewirtz, adopted from Allen Snook
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: http://zatzlabs.com/seamless-donations/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2020 by David Gewirtz
 *
 */
console.log("admin-scripts.js loaded");

// CODE FOR FORMS PAGE CUSTOMIZATION
// add javascript handler to CMB2 Select element
seamlessDonationsCheckForState();
jQuery("#dgx_donate_default_country").attr("onchange", "seamlessDonationsCheckForState()");

function seamlessDonationsCheckForState() {
    var countryElement = document.getElementById('dgx_donate_default_country');
    var country = countryElement.options[countryElement.selectedIndex].text;
    if (country !== 'United States') {
        jQuery(".cmb-row.cmb-type-select.cmb2-id-dgx-donate-default-state").attr("style", "display:none");
    } else {
        jQuery(".cmb-row.cmb-type-select.cmb2-id-dgx-donate-default-state").removeAttr("style");
    }
}