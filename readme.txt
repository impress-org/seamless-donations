=== Seamless Donations ===
Contributors: dgewirtz
Donate link: http://zatzlabs.com/project-donations/
Tags: donation, donations, paypal, donate, non-profit, charity, gifts, church, worship, churches, crowdfunding, donation plugin, fundraiser, fundraising, giving, nonprofit, paypal, PayPal Donate, paypal donations, recurring, recurring donations, wordpress donation plugin, wordpress donations, wp donation
Requires at least: 3.4
Tested up to: 4.3
Stable tag: 4.0.7
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Receive and manage donations (including repeating donations), track donors and send customized thank you messages.

== Description ==

**IMPORTANT: Before upgrading from 3.3 or if you are experiencing problems upgrading from v3 to v4, [read this](http://zatzlabs.com/fixing-seamless-donations-4-0-updateactivation-problems/).**

Need more than just a PayPal donation button? Would you like to allow your visitors to donate in honor of someone? Invite them to subscribe to your mailing list? Choose from designated funds? Do donations that automatically repeat each month? Allow them to mark their donation anonymous? Track donors and donations?

Seamless Donations does all this and more. All you need to do is embed a simple shortcode and supply your PayPal Website Payments Standard email address to start receiving donations today.

Seamless Donations is free, open-source, easy-to-use, and developer-friendly. No fees, no commissions.

Learn more on the [Seamless Donations home page](http://zatzlabs.com/project/seamless-donations/).

= Seamless Donations 4.0 is here! =

https://www.youtube.com/watch?v=75IjGyHp52o

= Here are some of the new features you’ll find in 4.0 =

* **Updated, modern admin UI:** The admin interface has been updated to a modern tabbed-look.
* **Custom post types:** Funds and donors have now been implemented as custom post types. This gives you the ability to use all of WordPress’ post management and display tools with donors and funds. The donation data has always been a custom post type, but it is now also available to manipulate using
plugins and themes outside of Seamless Donations.
* **Designed for extensibility:** The primary design goal of 4.0 was to add hooks in the form of filters and actions that web designers can use to modify the behavior of Seamless Donations to fit individual needs. The plugin was re-architected to allow for loads of extensibility.
* **Forms engine designed for extensibility:** Rather than just basic form code, Seamless Donations 4.0 now has a brand-new array-driven forms engine, which will give web site builders the ability to modify and access every part of the form before it is displayed to donors.
* **Shortcode engine designed for extensibility:** The main shortcode for the plugin has been designed so that extensions can add features to the main seamless-donations shortcode.
* **Admin UI designed for extensibility:** Yep, like everything else, the admin interface has been designed to allow for extensibility.
* **Translation-ready:** Seamless Donations 4.0 has had numerous tweaks to allow it to be translated into other languages.

= Developer Resources =

Seamless Donations 4.0 was designed from the ground up to be developer-friendly. Here are some of the developer resources you might find useful:

* [David's Lab Notes](http://zatzlabs.com/lab-notes/)
* [Actions and Filters](http://zatzlabs.com/codex/seamless-donations-actions-and-filters/)
* [Forms Engine](http://zatzlabs.com/codex/seamless-donations-forms-engine/)
* [Cloak/Reveal System](http://zatzlabs.com/codex/understanding-the-reveal-family-system/)
* [Training: Customizing Seamless Donations](http://zatzlabs.com/codex/introduction-to-seamless-donations-customization-using-hooks/)

= Support Note =

Many support questions can be answered by the [growing support FAQ on the plugin's home page](http://zatzlabs.com/seamless-donations/). If you can't find an answer there, you are invited to post questions [here on the Support boards](https://wordpress.org/support/plugin/seamless-donations).

= Mailing List =
If you'd like to keep up with the latest updates to this plugin, please visit [David's Lab Notes](http://zatzlabs.com/lab-notes/) and add yourself to the mailing list.

= Currency Support =

Support for the following currencies is built into Seamless Donations 4.0:

* Australian Dollar
* Brazilian Real
* Canadian Dollar
* Czech Koruna
* Danish Krone
* Euro
* Hong Kong Dollar
* Hungarian Forint
* Indian Rupee
* Israeli New Sheqel
* Japanese Yen
* Malaysian Ringgit
* Mexican Peso
* Norwegian Krone
* New Zealand Dollar
* Philippine Peso
* Polish Zloty
* Pound Sterling
* Russian Ruble
* Singapore Dollar
* Swedish Krona
* Swiss Franc
* Taiwan New Dollar
* Thai Baht
* Turkish Lira
* U.S. Dollar

= Translations =

* German translation (as of 4.0.1)
* Spanish translation, courtesy David Chávez (as of 4.0.2)
* French translation, courtesy Etienne Lombard (as of 4.0.2)
* HUGE thank you to users doing these translations!
* [Assist with translations](https://wordpress.org/support/topic/translators-check-in-here-so-youre-not-duplicating-work)

= Adoption Notice =

This plugin was adopted in March 2015 by David Gewirtz. Ongoing support and updates have continued, as evidenced by the major 4.0 upgrade. Feel free to visit [David's Lab Notes](http://zatzlabs.com/category/seamless-donations/) for a development roadmap and additional details. Special thanks to Allen Snook for originally creating the plugin and making adoption possible.

== Installation ==

1. Upload/install the Seamless Donations plugin
2. Activate the plugin
3. Set the email address for PayPal donations in the plugin settings
4. Create a new blank page (e.g. Donate Online)
5. Add the following shortcode to the page : [seamless-donations]
6. That's it - you're now receiving donations!

For those updating from 3.3 to 4.0, go to your Plugins page and deactivate Seamless Donations. Then delete the plugin. Refresh your plugins page (this is very important, make sure to refresh). Now you can Add New and bring Seamless Donations 4.0 onto your site. **If you are experiencing problems upgrading from v3 to v4, [read this](http://zatzlabs.com/fixing-seamless-donations-4-0-updateactivation-problems/).**

This video will provide more details:

https://www.youtube.com/watch?v=SWm6GivlJi0

= Be sure to test for the following changes in the new version =
* **Change the form shortcode:** The [dgx-donate] shortcode is deprecated and will issue an update warning once you update. The new shortcode is [seamless-donations].
* **Check your CSS:** Most of the CSS should remain the same, but because the form interaction has been updated, your CSS may change.
* **Check your data:** Great pains have been taken to be sure the data migrates correctly, but please, please, PLEASE double-check it.

== Frequently Asked Questions ==

= Does this work with US and non-US PayPal accounts? =

Yes!

= Does this handle US and non-US addresses? =

Yes!

= Does this work with PayPal Website Payments Standard? =

Yes!

= Do I have to pay a monthly fee to PayPal to use this? =

No! Website Payments Standard has no monthly cost. They do keep 2-3% of the donation.

= Can I customize the thank you message emailed to donors? =

Yes!

= Can I have multiple emails addresses receive notification when a donation is made? =

Yes!

= In-depth technical FAQ =

An in-depth technical FAQ is available on [the plugin's home page](http://zatzlabs.com/seamless-donations/). If you can't find an answer there, you are invited to post questions [here on the Support boards](https://wordpress.org/support/plugin/seamless-donations).

= Mailing List =
If you'd like to keep up with the latest updates to this plugin, please visit [David's Lab Notes](http://zatzlabs.com/lab-notes/) and add yourself to the mailing list.

== Screenshots ==

1. The donation form your visitor sees
2. Dashboard >> Seamless Donations Main Menu
3. Dashboard >> Donations custom post type
4. Dashboard >> Form Options tab
5. Dashboard >> Thank You Email template options

== Changelog ==

= 4.0.7 =
* **IMPORTANT:** Before upgrading from 3.3 or if you are experiencing problems upgrading from v3 to v4, [read this](http://zatzlabs.com/fixing-seamless-donations-4-0-updateactivation-problems/).
* Fixed bug in repeating donations

= 4.0.6 =
* Added a transaction audit database table that replaced the unreliable transient data system.
* Rewrote payment initiation system. Payments no longer are initiated by JavaScript running on visitors' browsers, but by a PHP script running inside the plugin on the server.
* Added new shortcode extensibility system.
* Added a debug mode checkbox to the Settings panel.
* Added a “Add label tag to input form (may improve form layout for some themes)” tweak to the Form Options panel.
* Full Lab Notes on update fixes [here](http://zatzlabs.com/reengineering-the-seamless-donations-core-payment-gateway/) and [here](http://zatzlabs.com/seamless-donations-4-0-6-incorporates-many-under-the-hood-improvements/).

= 4.0.5 =
* Public beta release only

= 4.0.4 =
* Beta release only

= 4.0.3 =
* Fixed fatal bug introduced in 4.0.2

= 4.0.2 =
* Added Spanish translation (thanks to David Chávez) and French translation (thanks to Etienne Lombard).
* Added new Form Tweaks section to Form Options, with an option to enable Label Tags. This may improve form layout for some themes, particularly those where vertical form field alignment needs improvement.
* Added an indicator comment in the form code to allow inspection to determine the version of the plugin that's currently running.
* Fixed bug in legacy export code introduced in 4.0. Unnecessary mode check caused the routine to fail.
* Fixed bug where getting the plugin version number failed internally in some instances.

= 4.0.1 =
* Added German translation
* Fixed problem with Windows servers and long path names
* Fixed multiple currency-related bugs: be sure to re-save your settings for this fix to take effect
* Fixed the giving level filter
* Fixed "undefined index" error
* Fixed bug where default fields didn't default properly
* Fixed overly oppressive field sanitization
* Full Lab Notes on update fixes [here](http://zatzlabs.com/seamless-donations-4-0-1-includes-german-translation-and-bug-fixes/)

= 4.0.0 =
* Major update
* Added updated, modern UI
* Funds and donors have now been implemented as custom post types.
* Designed for extensibility with support for wide range of hooks
* Array-driven forms engine
* Translation-ready

= 3.3.5 =
* Added update notice warning and splash so current site operators can have some warning before the new 4.0 version lands. Also added MailChimp subscribe form to main plugin page.

= 3.3.4 =
* Officially adopting the plugin and beginning support by David Gewirtz as new developer

= 3.3.3 =
* Officially marking this plugin as unsupported and putting it up for adoption

= 3.3.2 =
* Updated: Seamless Donation news feed updated to point to designgeneers.com
* Fixed: Corrected variable name to resolve PHP Warning for formatted amount that would be displayed on sending a test email
* Fixed: Corrected variable name to resolve PHP error for new donation created from PayPal data

= 3.3.1 =
* Tested with WordPress 4.1

= 3.3.0 =
* Changed PayPal IPN reply to use TLS instead of SSL because of the POODLE vulnerability
* Changed PayPal IPN reply to better handle unexpected characters and avoid IPN verification failure - props smarques

= 3.2.4 =
* Fixed: Don't start a PHP session if one has already been started - props nikdow and gingrichdk

= 3.2.3 =
* Fixed: Unwanted extra space in front of Add me to your mailing list prompt

= 3.2.2 =
* Added Currency Support: Brazilian Real, Czech Krona, Danish Krone, Hong Kong Dollar, Hungarian Forint, Israeli New Sheqel
* Added Currency Support: Malaysian Ringit, Mexican Peso, Norwegian Krone, New Zealand Dollar, Philippine Peso, Polish Zloty
* Added Currency Support: Russian Ruble, Singapore Dollar, Swedish Krona, Swiss Franc, Taiwan New Dollar, Thai Bhat, Turkish Lira

= 3.2.1 =
* Added: Occupation field to donation form and to donation detail in admin
* Added: Employer name to donation detail in admin
* Added: Employer and occupation fields to report

= 3.2.0 =
* Added: More control over which parts of the donation form appear

= 3.1.0 =
* Added: Filter for donation item name
* Added IDs for form sections to allow for more styling of the donation form

= 3.0.3 =
* Fixed: A few strings were not properly marked for translation.

= 3.0.2 =
* Fixed: Bug: Removed unused variable that was causing PHP warning

= 3.0.1 =
* Fixed: Bug: Was using admin_print_styles to enqueue admin CSS. Switched to correct hook - admin_enqueue_scripts

= 3.0.0 =
* Added: Gift Aid checkbox for UK donors
* Fixed: Bug that would cause IPN notifications to not be received

= 2.9.0 =
* Added: Optional employer match section to donation form - props Jamie Summerlin
* Fixed: Javascript error in admin on settings page

= 2.8.2 =
* Fixed: Don't require postal code for countries that don't require postal codes
* Fixed: International tribute gift addresses were not displaying country information in donation details

= 2.8.1 =
* Added: Support for non US currencies: Australian Dollar, Canadian Dollar, Euro, Pound Sterling, and Japanese Yen

= 2.8.0 =
* Added: Support for specifying name for emails to donors (instead of WordPress)
* Added: Automatic textarea height increase for email templates and thank you page
* Fixed: Bug that would allow invalid email address to cause email to donor to not go out (defaults to admin email now)

= 2.7.0 =
* Added: Support for donors located outside the United States

= 2.6.0 =
* Added: Support for repeating donations
* Added: Support for loading scripts in footer
* Added: Greyed out donate button on click
* Added: Prompt to confirm before deleting a donation in admin
* Added: Seamless Donations news feed to main plugin admin page
* Added: Help/FAQ submenu
* Added: Replaced main admin page buttons with Quick Links
* Added: Display of PayPal IPN URL in Settings
* Added: More logging to PayPal IPN for troubleshooting hosts that don't support fsockopen to PayPal on 443
* Fixed: Bug in displaying thank you after completing donation
* Fixed: Changed admin log formatting to make reading, cutting and pasting easier
* Fixed: Major update to admin pages code in support of localization

= 2.5.0 =
* Added support for designated funds
* Fixed: A couple warnings when saving changes to thank you email templates.

= 2.4.4 =
* Fixed: Cleaned up warnings when run with WP_DEBUG

= 2.4.3 =
* Fixed: Changed form submit target to _top most window (in case theme places content in iframes)
* Fixed: Updated plugin URI to point to allendav.com

= 2.4.2 =
* Automatically trim whitespace from PayPal email address to avoid common validation error and improve usability.

= 2.4.1 =
* Changed mail function to use WordPress wp_mail instead of PHP mail - this should help avoid dropped emails

= 2.4.0 =
* Added the ability to export donation information to spreadsheet (CSV - comma separated values)

= 2.3.0 =
* Added a setting to allow you to turn the Tribute Gift section of the form off if you'd like

= 2.2.0 =
* Added the ability to delete a donation (e.g. if you create a test donation)

= 2.1.7 =
* Rolled back change in 2.1.6 for ajax display due to unanticipated problem with search

= 2.1.6 =
* Added ajax error display to aid in debugging certain users not being able to complete donations on their sites

= 2.1.5 =
* Changed plugin name to simply Seamless Donations

= 2.1.4 =
* Added logging, log menu item and log display to help troubleshoot IPN problems

= 2.1.3 =
* Changed PayPal cmd from _cart to _donations to avoid donations getting delayed

= 2.1.2 =
* Removed tax deductible from donation form, since not everyone using the plugin is a charity

= 2.1.1 =
* Added missing states - AK and AL - to donation form
* Added more checks for invalid donation amounts (minimum donation is set to 1.00)
* Added support for WordPress installations using old-style (not pretty) permalinks
* Fix bug that caused memorial gift checkbox to be ignored

= 2.1.0 =
* Added new suggested giving amounts
* Now allows you to choose which suggested giving amounts are displayed on the donation form
* Added ability to change the default state for the donation form

= 2.0.2 =
* Initial release to WordPress.Org repository
