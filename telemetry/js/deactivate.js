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

// Based on code from https://github.com/CodeCabin/plugin-deactivation-survey

(function ($) {
    if (!window.seamlessDonations)
        window.seamlessDonations = {};

    if (seamlessDonations.DeactivateFeedbackForm)
        return;

    seamlessDonations.DeactivateFeedbackForm = function (plugin) {
        var self = this;
        var strings = seamless_donations_deactivate_feedback_form_strings;

        this.plugin = plugin;

        // Dialog HTML
        //<input name="comments" placeholder="' + strings.brief_description + '"/>\
        var element = $('\
			<div class="seamless-donations-deactivate-dialog" data-remodal-id="' + plugin.slug + '">\
				<form>\
					<input type="hidden" name="plugin"/>\
					<h2>' + strings.quick_feedback + '</h2>\
					<p>\
						' + strings.foreword + '\
					</p>\
					<ul class="seamless-donations-deactivate-reasons"></ul>\
					<textarea rows="3" name="comments" placeholder="' + strings.brief_description + '"></textarea>\
					<br>\
					<p class="seamless-donations-deactivate-dialog-buttons">\
						<input type="submit" class="button confirm" value="' + strings.skip_and_deactivate + '"/>\
						<button data-remodal-action="cancel" class="button button-primary">' + strings.cancel + '</button>\
					</p>\
				</form>\
			</div>\
		')[0];
        this.element = element;

        $(element).find("input[name='plugin']").val(JSON.stringify(plugin));

        $(element).on("click", "input[name='reason']", function (event) {
            $(element).find("input[type='submit']").val(
                strings.submit_and_deactivate
            );
        });

        $(element).find("form").on("submit", function (event) {
            self.onSubmit(event);
        });

        // Reasons list
        var ul = $(element).find("ul.seamless-donations-deactivate-reasons");
        for (var key in plugin.reasons) {
            var li = $("<li><input type='radio' name='reason'/> <span></span></li>");

            $(li).find("input").val(key);
            $(li).find("span").html(plugin.reasons[key]);

            $(ul).append(li);
        }

        // Listen for deactivate
        $("#the-list [data-slug='" + plugin.slug + "'] .deactivate>a").on("click", function (event) {
            self.onDeactivateClicked(event);
        });
    }

    seamlessDonations.DeactivateFeedbackForm.prototype.onDeactivateClicked = function (event) {
        this.deactivateURL = event.target.href;

        event.preventDefault();

        if (!this.dialog)
            this.dialog = $(this.element).remodal();
        this.dialog.open();
    }

    seamlessDonations.DeactivateFeedbackForm.prototype.onSubmit = function (event) {
        var element = this.element;
        var strings = seamless_donations_deactivate_feedback_form_strings;
        var preData = $(element).find("form");
        var i;
        var chosenOption; // the option that's been checked
        var comment; // a comment, if provided

        // build data summary
        // process 8 radio button fields
        for (i = 1; i <= 8; i++) {
            if (preData[0][i].checked === true) {
                if (i < 8) {
                    chosenOption = preData[0][i].value;
                } else {
                    chosenOption = 'other';
                }
                comment = preData[0][9].value;
            }
        }
        // quickly sanitize the comments string
        // https://gomakethings.com/how-to-automatically-sanitize-reactive-data-with-vanilla-js/
        var temp = document.createElement('div');
        temp.textContent = comment;
        comment = temp.innerHTML;
        // prepare the telemetry variables
        var timeNow = this.plugin.timeNow;
        var installTime = this.plugin.installTime; // this will be the get_option install time value
        var useDuration = this.plugin.useDuration; // this is the difference between the two in seconds -- TEMP VALUE
        var packedData = {
            slug: this.plugin.slug,
            option: chosenOption,
            version: this.plugin.version,
            comment: comment,
            install_time: installTime,
            uninstall_time: timeNow,
            duration: useDuration,
        };

        var self = this;
        var data = JSON.stringify(packedData);

        $(element).find("button, input[type='submit']").prop("disabled", true);

        if ($(element).find("input[name='reason']:checked").length) {
            $(element).find("input[type='submit']").val(strings.thank_you);

            $.ajax({
                type: "POST",
                url: this.plugin.telemetryUrl + "/wp-json/zatz/v1/telemetry_uninstalls",
                data: data,
                complete: function () {
                    window.location.href = self.deactivateURL;
                }
            });
        } else {
            $(element).find("input[type='submit']").val(strings.please_wait);
            window.location.href = self.deactivateURL;
        }

        event.preventDefault();
        return false;
    }

    $(document).ready(function () {

        for (var i = 0; i < seamless_donations_deactivate_feedback_form_plugins.length; i++) {
            var plugin = seamless_donations_deactivate_feedback_form_plugins[i];
            new seamlessDonations.DeactivateFeedbackForm(plugin);
        }

    });

})(jQuery);
