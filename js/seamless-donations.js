/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

//jQuery( document ).ajaxError(function( event, jqxhr, settings, exception ) {
//    console.log( "Triggered ajaxError handler." );
//});

jQuery(document).ready(function () {

    // radio button revealer, works with multiple sets per page
    // each input tag gets either (or both) a data-revealer or data-hide attribute
    // the contents of that attribute is a class or id (with the . or # as part of the string)
    // for the purposes of the forms engine, we'll work only with classes.

    jQuery("input:radio").change(function () {
        var revealTarget = jQuery(this).attr("data-reveal");
        var concealTarget = jQuery(this).attr("data-conceal");
        var checkTarget = jQuery(this).attr('data-check');
        var uncheckTarget = jQuery(this).attr('data-uncheck');
        if (revealTarget != undefined) {
            console.log("radio button: show " + revealTarget);
            jQuery(revealTarget).show('fast');
            showSelectedOptions(revealTarget);
        }
        if (concealTarget != undefined) {
            console.log("radio button: hide " + concealTarget);
            jQuery(concealTarget).hide('fast');
            hideSelectedOptions(concealTarget);
        }
        if (jQuery(this).is(":checked")) {
            checkSelectedNamedItems(checkTarget);
            uncheckSelectedNamedItems(uncheckTarget);
        }
    });

    // checkbox revealer, also works with multiple checkbox and hide/reveal sets per page
    // this uses only the data-revealer attribute, and reveals if checked and hides if unchecked.

    jQuery("input:checkbox").click(function () {
        var revealTarget = jQuery(this).attr('data-reveal');
        var concealTarget = jQuery(this).attr('data-conceal');
        var checkTarget = jQuery(this).attr('data-check');
        var uncheckTarget = jQuery(this).attr('data-uncheck');
        if (checkTarget !== undefined) {
            console.log("checkbox checktarget:" + checkTarget);
        }
        if (jQuery(this).is(":checked")) {
            console.log("checkbox click:is-checked (revealTarget:" + revealTarget + ")");
            jQuery(revealTarget).show('fast');
            showSelectedOptions(revealTarget);
            checkSelectedNamedItems(checkTarget);
            uncheckSelectedNamedItems(uncheckTarget);
        }
        if (jQuery(this).is(":not(:checked)")) {
            console.log("checkbox click:not-checked");
            //hideSelect();
            hideSelectedOptions(concealTarget);
            jQuery(revealTarget).hide('fast');
        }
    });


    function checkSelectedNamedItems(whatToCheck) {
        if (whatToCheck !== undefined) {
            var whatToCheckArray = whatToCheck.split(" ");
            for (itemToCheckIndex in whatToCheckArray) {
                var itemToCheck = whatToCheckArray[itemToCheckIndex];
                console.log("-- checkSelectedNamedItems (itemToCheck):" + itemToCheck);
                jQuery("[id=" + itemToCheck + "] input").attr('checked', true);
            }
        }
    }

    function uncheckSelectedNamedItems(whatToCheck) {
        if (whatToCheck !== undefined) {
            var whatToCheckArray = whatToCheck.split(" ");
            for (itemToCheckIndex in whatToCheckArray) {
                var itemToCheck = whatToCheckArray[itemToCheckIndex];
                console.log("-- checkSelectedNamedItems (itemToCheck):" + itemToCheck);
                jQuery("[id=" + itemToCheck + "] input").attr('checked', false);
            }
        }
    }

    // basic code to capture selection box changes
    //jQuery("select").each(function () {
    //    var conceal = jQuery(this).attr("data-conceal");
    //    console.log("select:" + conceal);
    //    jQuery(conceal).hide();
    //});

    jQuery("form").on("change", "select", function () {
        var nameOfChangedSelect = jQuery(this).attr("name");
        console.log("select change (invoked):" + nameOfChangedSelect);
        jQuery("select[name=" + nameOfChangedSelect + "] option:selected").each(function () {
            // triggering for everything, not just visible items
            var parent = jQuery(this).parent("select").attr("name");
            console.log("-- option:selected (parent select): " + parent);
            var reveal = jQuery(this).attr("data-reveal");
            console.log("---- option:selected (reveal: " + reveal);
            var optionKey = jQuery(this).attr("value");
            console.log("---- option:selected (optionKey): " + optionKey);
            var conceal = jQuery(this).parent("select").attr("data-conceal");
            console.log("---- option:selected (conceal): " + conceal);

            hideSelectedOptions(conceal);
            jQuery(reveal).show();
        });
    }).on("change");

    // if the select item that matches the cloak becomes visible, make the active option classes visible
    // we're looking at the class of the select item to see if it matches the what to reveal values
    function showSelectedOptions(whatToReveal) {
        // whatToReveal could be a set of classes
        if (whatToReveal !== undefined) {
            console.log("showSelectedOptions (" + whatToReveal + ")");
            var whatToRevealArray = whatToReveal.split(",");
            for (itemToCheckIndex in whatToRevealArray) {
                var itemToReveal = whatToRevealArray[itemToCheckIndex];
                console.log("-- showSelectedOptions (itemToReveal):" + itemToReveal);
                jQuery("select" + itemToReveal + " option:selected").each(function () {
                    var reveal = jQuery(this).attr("data-reveal");
                    jQuery(reveal).show();
                    var optionKey = jQuery(this).attr("value");
                    console.log("-- showSelectedOptions (option key:" + optionKey + "):" + reveal);
                });
            }
        }
    }

    function hideSelectedOptions(whatToConceal) {
        // whatToConceal could be a set of classes
        console.log("hideSelectedOptions (" + whatToConceal + ")");
        if (whatToConceal !== undefined) {
            var whatToConcealArray = whatToConceal.split(",");
            for (itemToConcealIndex in whatToConcealArray) {
                var itemToConceal = whatToConcealArray[itemToConcealIndex].trim();
                console.log("-- hideSelectedOptions (itemToConceal):" + itemToConceal);
                jQuery("select" + itemToConceal + " option:selected").each(function () {
                    var conceal = jQuery(this).attr("data-conceal");
                    jQuery(conceal).show();
                    var optionKey = jQuery(this).attr("value");
                    console.log("-- showSelectedOptions (option key:" + optionKey + "):" + conceal);
                });
                jQuery(itemToConceal).hide();
            }
        }
    }

    // should be showSelectedOptions?
    function showSelectedOptionsx(whatToReveal) {
        // whatToReveal could be a set of classes
        var whatToRevealArray = whatToReveal.split(",");
        console.log("showSelectedOptions (" + whatToReveal + ")");
        jQuery("select option:selected").each(function () {
            var reveal = jQuery(this).attr("data-reveal");
            if (reveal !== undefined) {
                var revealArray = reveal.split(",");

                for (itemToCheckIndex in whatToRevealArray) {
                    var itemToReveal = whatToRevealArray[itemToCheckIndex];
                    console.log("-- showSelectedOptions itemToReveal: " + whatToRevealArray[itemToCheckIndex]);
                    for (classToRevealIndex in revealArray) {
                        var classToReveal = revealArray[classToRevealIndex];
                        console.log("-- showSelectedOptions classToReveal: " + classToReveal);
                        if (itemToReveal.trim() == classToReveal.trim()) {
                            console.log("---- showSelectedOptions revealing: " + itemToReveal);
                            jQuery(itemToReveal).show();
                        }
                    }
                }
            }
        });
    }


    // unhacked version -- hacking new one called showSelectedOptions
    function showSelect() {
        console.log("showSelect (invoked)");
        jQuery("select option:selected").each(function () {
            var reveal = jQuery(this).attr("data-reveal");
            var optionKey = jQuery(this).attr("value");
            console.log("-- showSelect (option key:" + optionKey + "):" + reveal);
            jQuery(reveal).show();
        });
    }

    function hideSelect() {
        jQuery("select option:selected").each(function () {
            var conceal = jQuery(this).parent("select").attr("data-conceal");
            console.log("hideSelect:" + conceal);
            jQuery(conceal).hide();
        });
    }

    // process form clicks, trigger on the input inside the form-submit div
/*    jQuery(".seamless-donations-form-submit > input").click(function () {
        // validate and process form here
        // begin validation code
        var foo = SeamlessDonationsFormsEngineValidator();
        return false;
    }); */

    // process form clicks, trigger on the input inside the form-submit div
 /*   jQuery("#seamless-donations-form").submit(function () {
        // validate and process form here
        // begin validation code
        console.log("jQuery form submit function");
        // do we need .val(firstName);
        var test = jQuery("#seamless-donations-form").attr("action");
        console.log("jQuery form submit function test=" + test);
        if (test != "stop") {
            console.log("jQuery form submit function sending to validator");
            jQuery("#seamless-donations-form").attr("action", "stop");
            var valid = SeamlessDonationsFormsEngineValidator();
        } else {
            console.log("jQuery form submit function validator bypassed");
        }
        return valid;
    }); */

});

/**
 * @return {boolean}
 */
function SeamlessDonationsFormsEngineValidator() {
    var formOkay = true;

    // first hide the error message data
    jQuery('.seamless-donations-forms-error-message').hide('fast').text();
    jQuery('.seamless-donations-error-message-field').hide('fast');

    // the approach is to find visible elements with 'validation' and check them
    jQuery('#seamless-donations-form input:visible').each(function (index) {
        var validations = jQuery(this).attr('data-validate');
        if (validations !== undefined) {
            // the element has a validation request
            // the validation request can be one or more validation names, separated by commas
            var validationArray = validations.split(",");
            var valDex;
            for (valDex = 0; valDex < validationArray.length; ++valDex) {
                var validationTest = validationArray[valDex];
                switch (validationTest) {
                    case 'required':
                        if (!SeamlessDonationsValidateRequired(this)) {
                            formOkay = false;
                        }
                        break;
                    case 'currency':
                        if (!SeamlessDonationsValidateCurrency(this)) {
                            formOkay = false;
                        }
                        break;
                    case 'email':
                        if (!SeamlessDonationsValidateEmail(this)) {
                            formOkay = false;
                        }
                        break;
                }
            }
        }
    });

    console.log("SeamlessDonationsFormsEngineValidator: before if(!formOkay)");
    if (!formOkay) {
        jQuery('.seamless-donations-forms-error-message').text('Please correct your input.').show('fast');
        jQuery('body').scrollTop(0);
        console.log("-- SeamlessDonationsFormsEngineValidator: form not okay");
        return false; // returning false blocks the page loader from executing on submit
    } else {
        // the form is okay, so go on to the form submit function, another JavaScript
        console.log("-- SeamlessDonationsFormsEngineValidator: form passed validation");
        return true;
    }
}

function SeamlessDonationsValidateRequired(validationObject) {
    var elementID = jQuery(validationObject).attr('id');
    var elementType = jQuery(validationObject).attr('type');
    var elementName = jQuery(validationObject).attr('name');
    var elementValue = jQuery(validationObject).val();
    if (elementType == 'text') {
        // ignore for non-text elements in this version
        var divSelector = "input[name=" + elementName + "]";
        var errorSelector = "div[id=" + elementName + "-error-message]";
        if (SeamlessDonationsTrim(elementValue) == '') {
            jQuery('#seamless-donations-form').find(divSelector).addClass('seamless-donations-invalid-input');
            jQuery('#seamless-donations-form').find(errorSelector).text('This is a required field.').show('fast');

            return false;
        } else {
            jQuery('#seamless-donations-form').find(divSelector).removeClass('seamless-donations-invalid-input');
            return true
        }

    }
    return true;
}

function SeamlessDonationsValidateEmail(validationObject) {

    var elementID = jQuery(validationObject).attr('id');
    var elementType = jQuery(validationObject).attr('type');
    var elementName = jQuery(validationObject).attr('name');
    var elementValue = jQuery(validationObject).val();

    if (elementType == 'text' && elementValue != '') {
        var divSelector = "input[name=" + elementName + "]";
        var errorSelector = "div[id=" + elementName + "-error-message]";

        var lastAtPos = elementValue.lastIndexOf('@');
        var lastDotPos = elementValue.lastIndexOf('.');
        var isEmail = (lastAtPos < lastDotPos && lastAtPos > 0
        && elementValue.indexOf('@@') == -1 && lastDotPos > 2 && (elementValue.length - lastDotPos) > 2);
        if (!isEmail) {
            jQuery('#seamless-donations-form').find(divSelector).addClass('seamless-donations-invalid-input');
            jQuery('#seamless-donations-form').find(errorSelector).text('Please enter a valid email address.').show('fast');
            return false;
        }
        jQuery('#seamless-donations-form').find(divSelector).removeClass('seamless-donations-invalid-input');
    }
    return true;
}

function SeamlessDonationsValidateCurrency(validationObject) {
    var elementID = jQuery(validationObject).attr('id');
    var elementType = jQuery(validationObject).attr('type');
    var elementName = jQuery(validationObject).attr('name');
    var elementValue = jQuery(validationObject).val();

    if (elementType == 'text') {

        var divSelector = "input[name=" + elementName + "]";
        var errorSelector = "div[id=" + elementName + "-error-message]";

        // Check for anything other than numbers and decimal points
        var matchTest = elementValue.match(/[^0123456789.]/g);
        if (matchTest != null) {
            jQuery('#seamless-donations-form').find(divSelector).addClass('seamless-donations-invalid-input');
            jQuery('#seamless-donations-form').find(errorSelector).text('Please use only numbers.').show('fast');
            return false;
        }

        // Count the number of decimal points
        var pointCount = DgxDonateCountNeedles(".", elementValue);

        // If more than one decimal point, fail right away
        if (pointCount > 1) {
            jQuery('#seamless-donations-form').find(divSelector).addClass('seamless-donations-invalid-input');
            jQuery('#seamless-donations-form').find(errorSelector).text('Please use only numbers.').show('fast');
            return false;
        }

        // A leading zero is not allowed
        if (elementValue.substr(0, 1) == "0") {
            jQuery('#seamless-donations-form').find(divSelector).addClass('seamless-donations-invalid-input');
            jQuery('#seamless-donations-form').find(errorSelector).text('A leading zero is not allowed.').show('fast');
            return false;
        }

        // A leading decimal point is not allowed (minimum donation is 1.00)
        if (elementValue.substr(0, 1) == ".") {
            jQuery('#seamless-donations-form').find(divSelector).addClass('seamless-donations-invalid-input');
            jQuery('#seamless-donations-form').find(errorSelector).text('Minimum value is 1.00.').show('fast');
            return false;
        }

        // If we have a decimal point and there is anything other than two digits after it, fail
        if (pointCount == 1) {
            var pointIndex = elementValue.indexOf(".");
            if (pointIndex + 2 != (elementValue.length - 1)) {
                jQuery('#seamless-donations-form').find(divSelector).addClass('seamless-donations-invalid-input');
                jQuery('#seamless-donations-form').find(errorSelector).text('Please use only numbers.').show('fast');
                return false;
            }
        }
        jQuery('#seamless-donations-form').find(divSelector).removeClass('seamless-donations-invalid-input');
    }
    return true;
}

function SeamlessDonationsTrim(s) {
    if (s == undefined) {
        s = "";
    }

    s = s.replace(/(^\s*)|(\s*$)/gi, "");
    s = s.replace(/[ ]{2,}/gi, " ");
    s = s.replace(/\n /, "\n");
    return s;
}

// THE FOLLOWING HOT MESS IS DEPRECATED. LEFT IN TEMPORARILY FOR REFERENCE

/*
function SeamlessDonationsCheckout() {

    console.log("SeamlessDonationsCheckout: entered function");

    var mainForm = jQuery('#seamless-donations-form'); // cache the form

    // Get the form data
    var values = {};
    jQuery.each(mainForm.serializeArray(), function (i, field) {
        values[field.name] = field.value;
    });

    var sessionID = values['_dgx_donate_session_id'];
    var redirectURL = values['_dgx_donate_redirect_url'];
    var donationAmount = DgxDonateTrim(values['_dgx_donate_amount']);
    var userAmount = DgxDonateTrim(values['_dgx_donate_user_amount']);
    var repeating = DgxDonateTrim(values['_dgx_donate_repeating']);
    var designated = DgxDonateTrim(values['_dgx_donate_designated']);
    var designatedFund = DgxDonateTrim(values['_dgx_donate_designated_fund']);
    var increaseToCover = DgxDonateTrim(values['_dgx_donate_increase_to_cover']);
    var anonymous = DgxDonateTrim(values['_dgx_donate_anonymous']);
    var tributeGift = DgxDonateTrim(values['_dgx_donate_tribute_gift']);
    var employerMatch = DgxDonateTrim(values['_dgx_donate_employer_match']);
    var employerName = DgxDonateTrim(values['_dgx_donate_employer_name']);
    var occupation = DgxDonateTrim(values['_dgx_donate_occupation']);
    var memorialGift = DgxDonateTrim(values['_dgx_donate_memorial_gift']);
    var honoreeName = DgxDonateTrim(values['_dgx_donate_honoree_name']);
    var honorByEmail = DgxDonateTrim(values['_dgx_donate_honor_by_email']);
    var honoreeEmailName = DgxDonateTrim(values['_dgx_donate_honoree_email_name']);
    var honoreeEmail = DgxDonateTrim(values['_dgx_donate_honoree_email']);
    var honoreePostName = DgxDonateTrim(values['_dgx_donate_honoree_post_name']);
    var honoreeAddress = DgxDonateTrim(values['_dgx_donate_honoree_address']);
    var honoreeCity = DgxDonateTrim(values['_dgx_donate_honoree_city']);
    var honoreeState = DgxDonateTrim(values['_dgx_donate_honoree_state']);
    var honoreeProvince = DgxDonateTrim(values['_dgx_donate_honoree_province']);
    var honoreeCountry = DgxDonateTrim(values['_dgx_donate_honoree_country']);
    var honoreeZip = DgxDonateTrim(values['_dgx_donate_honoree_zip']);
    var firstName = DgxDonateTrim(values['_dgx_donate_donor_first_name']);
    var lastName = DgxDonateTrim(values['_dgx_donate_donor_last_name']);
    var phone = DgxDonateTrim(values['_dgx_donate_donor_phone']);
    var email = DgxDonateTrim(values['_dgx_donate_donor_email']);
    var addToMailingList = DgxDonateTrim(values['_dgx_donate_add_to_mailing_list']);
    var address = DgxDonateTrim(values['_dgx_donate_donor_address']);
    var address2 = DgxDonateTrim(values['_dgx_donate_donor_address2']);
    var city = DgxDonateTrim(values['_dgx_donate_donor_city']);
    var state = DgxDonateTrim(values['_dgx_donate_donor_state']);
    var province = DgxDonateTrim(values['_dgx_donate_donor_province']);
    var country = DgxDonateTrim(values['_dgx_donate_donor_country']);
    var zip = DgxDonateTrim(values['_dgx_donate_donor_zip']);
    var increaseToCover = DgxDonateTrim(values['_dgx_donate_increase_to_cover']);
    var paymentMethod = DgxDonateTrim(values['_dgx_donate_payment_method']);
    var ukGiftAid = DgxDonateTrim(values['_dgx_donate_uk_gift_aid']);
    var referringUrl = location.href;

    var amount = 0.0;

    // Resolve the donation amount
    if (donationAmount == "OTHER") {
        amount = parseFloat(userAmount);
    }
    else {
        amount = parseFloat(donationAmount);
    }
    if( amount < 1.00 ) {
        amount = 1.00;
    }
    // per http://stackoverflow.com/questions/6134039/format-number-to-always-show-2-decimal-places
    amount = parseFloat(Math.round(amount * 100) / 100).toFixed(2); // set to 2 digits
    amount.toString();

    // If validation succeeds, post the data to ajax to create a transient
    // and update the hidden form with the visible form values that PayPal cares about

    console.log("-- SeamlessDonationsCheckout: session id: " + sessionID);
    console.log("-- SeamlessDonationsCheckout: moving values into hidden section");

    var hiddenForm = jQuery('#dgx-donate-form-paypal-hidden-section');


    hiddenForm.find('input[name="first_name"]').val(firstName);
    hiddenForm.find('input[name="last_name"]').val(lastName);
    hiddenForm.find('input[name="address1"]').val(address);
    hiddenForm.find('input[name="address2"]').val(address2);
    hiddenForm.find('input[name="city"]').val(city);
    hiddenForm.find('input[name="state"]').val(state);
    hiddenForm.find('input[name="zip"]').val(zip);

    if ('US' == country) {
        hiddenForm.find('input[name="state"]').val(state);
    } else if ('CA' == country) {
        hiddenForm.find('input[name="state"]').val(province);
    } else {
        hiddenForm.find('input[name="state"]').remove();
    }

    hiddenForm.find('input[name="country"]').val(country);
    hiddenForm.find('input[name="email"]').val(email);
    hiddenForm.find('input[name="custom"]').val(sessionID);
    hiddenForm.find('input[name="amount"]').val(amount);

    if (!repeating) {
        hiddenForm.find('input[name="src"]').remove();
        hiddenForm.find('input[name="p3"]').remove();
        hiddenForm.find('input[name="t3"]').remove();
        hiddenForm.find('input[name="a3"]').remove();
    } else {
        hiddenForm.find('input[name="cmd"]').val('_xclick-subscriptions');
        hiddenForm.find('input[name="p3"]').val('1'); // 1, M = monthly
        hiddenForm.find('input[name="t3"]').val('M');
        hiddenForm.find('input[name="a3"]').val(amount);
        hiddenForm.find('input[name="amount"]').remove();
    }

    // Send the request

    console.log("-- SeamlessDonationsCheckout: before dgxDonateAjax.nonce");

    var nonce = dgxDonateAjax.nonce;
    console.log("-- SeamlessDonationsCheckout: nonce=" + nonce);
    console.log("-- SeamlessDonationsCheckout: preparing data array");

    var data = {
        action: 'dgx_donate_paypalstd_ajax_checkout',
        referringUrl: referringUrl,
        nonce: nonce,
        sessionID: sessionID,
        donationAmount: donationAmount,
        userAmount: userAmount,
        repeating: repeating,
        designated: designated,
        designatedFund: designatedFund,
        increaseToCover: increaseToCover,
        anonymous: anonymous,
        employerMatch: employerMatch,
        employerName: employerName,
        occupation: occupation,
        tributeGift: tributeGift,
        honoreeName: honoreeName,
        honorByEmail: honorByEmail,
        honoreeEmail: honoreeEmail,
        memorialGift: memorialGift,
        honoreeEmailName: honoreeEmailName,
        honoreePostName: honoreePostName,
        honoreeAddress: honoreeAddress,
        honoreeCity: honoreeCity,
        honoreeState: honoreeState,
        honoreeProvince: honoreeProvince,
        honoreeCountry: honoreeCountry,
        honoreeZip: honoreeZip,
        firstName: firstName,
        lastName: lastName,
        phone: phone,
        email: email,
        addToMailingList: addToMailingList,
        address: address,
        address2: address2,
        city: city,
        state: state,
        province: province,
        country: country,
        zip: zip,
        increaseToCover: increaseToCover,
        paymentMethod: paymentMethod,
        ukGiftAid: ukGiftAid
    };

    console.log("-- SeamlessDonationsCheckout: before jQuery.post");
    console.log("-- SeamlessDonationsCheckout: ajaxurl=" + dgxDonateAjax.ajaxurl);
    console.log("-- SeamlessDonationsCheckout: redirecturl=" + redirectURL);


    jQuery.ajax({
        type: 'POST',
        url: dgxDonateAjax.ajaxurl,
        data: data,
        success: function() {

// form.submit sends the post data, but seems to cycle infinitely because there's a submit handler
// in the form
// some notes: http://cwestblog.com/2012/11/21/javascript-go-to-url-using-post-variable/
// http://www.prowebguru.com/2013/10/send-post-data-while-redirecting-with-jquery/#.VaWMjxNVhBc
// THIS MOSTLY WORKS, problem is sending the redirect with the form data. erroring out on appendChild
            // also the redirectURL (which should be the paypal sandbox) is undefined
            // but this is where the ajax form processing should take place, one way or another
            console.log("-- SeamlessDonationsCheckout: jQuery.ajax success");
            var paypalFormTag = '<form action="' + redirectURL + '" />';
            var paypalForm = jQuery("#dgx-donate-form-paypal-hidden-section").wrapAll(paypalFormTag);
            paypalForm.submit();
            //response(data);
        }
    });

    //jQuery.post(dgxDonateAjax.ajaxurl, data, SeamlessDonationsAjaxCallback);
    console.log("-- SeamlessDonationsCheckout: after jQuery.post");

    return false;
}

function SeamlessDonationsAjaxCallback(data) {
    // Submit the hidden form to take the user to PayPal
    console.log("Inside SeamlessDonationsAjaxCallback");
    //jQuery('#seamless-donations-form').submit();
}
*/

