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
// from https://jqueryui.com/tabs/#default
// from https://gcostudios.com/how-to-add-jquery-ui-tabs-into-wordpress/
jQuery(document).ready(function($) {
    $('#tabs').tabs();

//hover states on the static widgets
    $('#dialog_link, ul#icons li').hover(
        function() { $(this).addClass('ui-state-hover'); },
        function() { $(this).removeClass('ui-state-hover'); }
    );
});