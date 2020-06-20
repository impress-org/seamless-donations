/*
 * My Private Site by David Gewirtz, adopted from Jon ‘jonradio’ Pearkins
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: https://zatzlabs.com/project/my-private-site/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2020 by David Gewirtz
 */

// from https://www.wpbasics.org/how-to-add-a-jquery-ui-accordion-to-wordpress/
//jquery-ui-accordion
jQuery(document).ready(function($) {
    $( ".accordion" ).accordion({
        collapsible: true, active: true, heightStyle: "content"
    });
});

// style that forces the first accordian element to be shown as open when page loads
jQuery(document).ready(function($) {
    $( ".accordion-1st-open" ).accordion({
        collapsible: true, active: 0, heightStyle: "content"
    });
});