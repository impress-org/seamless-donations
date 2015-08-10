// Based on code copyright 2012 Designgeneers! Web Design (email: info@designgeneers.com)
//
/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

function DgxDonateTrim(s) {
    // used in 4.0
    if (s == undefined) {
        s = "";
    }

    s = s.replace(/(^\s*)|(\s*$)/gi, "");
    s = s.replace(/[ ]{2,}/gi, " ");
    s = s.replace(/\n /, "\n");
    return s;
}

function DgxDonateLooksLikeMail(str) {
    var lastAtPos = str.lastIndexOf('@');
    var lastDotPos = str.lastIndexOf('.');
    return (lastAtPos < lastDotPos && lastAtPos > 0 && str.indexOf('@@') == -1 && lastDotPos > 2 && (str.length - lastDotPos) > 2);
}

function DgxDonateCountNeedles(needle, haystack) {
    var count = 0;
    var index = -1;
    index = haystack.indexOf(needle, index + 1);
    while (index != -1) {
        count++;
        index = haystack.indexOf(needle, index + 1);
    }

    return count;
}

function DgxDonateIsValidAmount(amount) {
    // Empty amounts are not allowed
    if (amount == "") {
        return false;
    }

    // Check for anything other than numbers and decimal points
    var matchTest = amount.match(/[^0123456789.]/g);
    if (matchTest != null) {
        alert('Please use only numbers when specifying your donation amount.');
        return false;
    }

    // Count the number of decimal points
    var pointCount = DgxDonateCountNeedles(".", amount);

    // If more than one decimal point, fail right away
    if (pointCount > 1) {
        return false;
    }

    // A leading zero is not allowed
    if (amount.substr(0, 1) == "0") {
        return false;
    }

    // A leading decimal point is not allowed (minimum donation is 1.00)
    if (amount.substr(0, 1) == ".") {
        return false;
    }

    // If we have a decimal point and there is anything other than two digits after it, fail
    if (pointCount == 1) {
        var pointIndex = amount.indexOf(".");
        if (pointIndex + 2 != (amount.length - 1)) {
            return false;
        }
    }

    return true;
}

function DgxDonateUpdateControls(controlStates) {
    if ('undefined' != typeof controlStates.donateButton) {
        if (controlStates.donateButton) {
            jQuery('.dgx-donate-pay-enabled').show();
            jQuery('.dgx-donate-pay-disabled').hide();
        } else {
            jQuery('.dgx-donate-pay-enabled').hide();
            jQuery('.dgx-donate-pay-disabled').show();
        }
    }

    if ('undefined' != typeof controlStates.ajaxSpinner) {
        if (controlStates.ajaxSpinner) {
            jQuery('.dgx-donate-busy').show();
        } else {
            jQuery('.dgx-donate-busy').hide();
        }
    }
}

function DgxDonateDoCheckout() {
    // Set control visibility
    DgxDonateUpdateControls({donateButton: false, ajaxSpinner: false});

    // First we do a client side validation
    // We should also do a server side validation in the ajax handler

    // Reset the error message field
    jQuery('.dgx-donate-error-msg').html("");
    jQuery('.dgx-donate-error-msg').css('visibility', 'hidden');

    // Check for missing or invalid data
    // Flag the missing places with background soft red
    // Send back a false if anything amiss
    var formValidates = true;

    // Reset any input alert colors
    jQuery('#dgx-donate-form').find("input").removeClass('dgx-donate-invalid-input');

    // Get the form data
    var values = {};
    jQuery.each(jQuery('#dgx-donate-form').serializeArray(), function (i, field) {
        values[field.name] = field.value;
    });

    var sessionID = values['_dgx_donate_session_id'];
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

    var amount = "";

    if (donationAmount == "OTHER") {
        amount = userAmount;
    }
    else {
        amount = donationAmount;
    }

    if (!DgxDonateIsValidAmount(amount)) {
        formValidates = false;
        DgxDonateMarkInvalid("_dgx_donate_user_amount");
    }

    if (tributeGift == 'on') {
        if (honoreeName == "") {
            formValidates = false;
            DgxDonateMarkInvalid("_dgx_donate_honoree_name");
        }
        if (honorByEmail == 'TRUE') {
            if (honoreeEmailName == "") {
                formValidates = false;
                DgxDonateMarkInvalid("_dgx_donate_honoree_email_name");
            }
            if (honoreeEmail == "") {
                formValidates = false;
                DgxDonateMarkInvalid("_dgx_donate_honoree_email");
            }
        }
        else /* honor by postal mail */
        {
            if (honoreePostName == "") {
                formValidates = false;
                DgxDonateMarkInvalid("_dgx_donate_honoree_post_name");
            }
            if (honoreeAddress == "") {
                formValidates = false;
                DgxDonateMarkInvalid("_dgx_donate_honoree_address");
            }
            if (honoreeCity == "") {
                formValidates = false;
                DgxDonateMarkInvalid("_dgx_donate_honoree_city");
            }
            if (honoreeZip == "") {
                if (dgxDonateAjax.postalCodeRequired.indexOf(honoreeCountry) >= 0) {
                    formValidates = false;
                    DgxDonateMarkInvalid("_dgx_donate_honoree_zip");
                }
            }
        }
    }

    if (firstName == "") {
        formValidates = false;
        DgxDonateMarkInvalid("_dgx_donate_donor_first_name");
    }

    if (lastName == "") {
        formValidates = false;
        DgxDonateMarkInvalid("_dgx_donate_donor_last_name");
    }

    var phoneRequired = jQuery('#dgx-donate-form').find("input[name='_dgx_donate_donor_phone']").hasClass('required');
    if (phoneRequired && phone == "") {
        formValidates = false;
        DgxDonateMarkInvalid("_dgx_donate_donor_phone");
    }

    if (email == "") {
        formValidates = false;
        DgxDonateMarkInvalid("_dgx_donate_donor_email");
    }

    var employerRequired = jQuery('#dgx-donate-form').find("input[name='_dgx_donate_employer_name']").hasClass('required');
    if (employerRequired && employerName == "") {
        formValidates = false;
        DgxDonateMarkInvalid("_dgx_donate_employer_name");
    }

    var occupationRequired = jQuery('#dgx-donate-form').find("input[name='_dgx_donate_occupation']").hasClass('required');
    if (occupationRequired && occupation == "") {
        formValidates = false;
        DgxDonateMarkInvalid("_dgx_donate_occupation");
    }

    if (employerMatch == 'on') {
        if (employerName == "") {
            formValidates = false;
            DgxDonateMarkInvalid("_dgx_donate_employer_name");
        }
    }

    var addressRequired = jQuery('#dgx-donate-form').find("input[name='_dgx_donate_donor_address']").hasClass('required');
    if (addressRequired) {
        if (address == "") {
            formValidates = false;
            DgxDonateMarkInvalid("_dgx_donate_donor_address");
        }

        if (city == "") {
            formValidates = false;
            DgxDonateMarkInvalid("_dgx_donate_donor_city");
        }

        if (zip == "") {
            if (dgxDonateAjax.postalCodeRequired.indexOf(country) >= 0) {
                formValidates = false;
                DgxDonateMarkInvalid("_dgx_donate_donor_zip");
            }
        }
    }

    if (!formValidates) {
        alert('Some required information is missing or invalid.  Please complete the fields highlighted in red');
        DgxDonateUpdateControls({donateButton: true, ajaxSpinner: false});
        return false;
    }

    // If validation succeeds, post the data to ajax to create a transient
    // and update the hidden form with the visible form values that PayPal cares about
    DgxDonateUpdateControls({donateButton: false, ajaxSpinner: true});

    var hiddenForm = jQuery('#dgx-donate-hidden-form');

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
    var nonce = dgxDonateAjax.nonce;

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

    jQuery.post(dgxDonateAjax.ajaxurl, data, DgxDonateCallback);

    return false;
}

function DgxDonateCallback(data) {
    // Submit the hidden form to take the user to PayPal
    console.log("Inside DgxDonateCallback");
    jQuery('#dgx-donate-hidden-form').submit();
}

function DgxDonateMarkInvalid(fieldname) {
    var selector = "input[name=" + fieldname + "]";
    jQuery('#dgx-donate-form').find(selector).addClass('dgx-donate-invalid-input');
}

function DgxDonateAjaxError(event, jqxhr, settings, exception) {
    // Set control visibility
    DgxDonateUpdateControls({donateButton: true, ajaxSpinner: false});

    // Display the error
    alert("An Ajax error occurred while requesting the resource - " + settings.url + " - No donation was completed.  Please try again later.");

    return false;
}

jQuery(document).ready(function () {
    // Set control visibility
    DgxDonateUpdateControls({donateButton: true, ajaxSpinner: false});

    // Register our AJAX error handler
    // jQuery(document).ajaxError( DgxDonateAjaxError );
});

