/*
 Seamless Donations by David Gewirtz, adopted from Allen Snook

 Lab Notes: http://zatzlabs.com/lab-notes/
 Plugin Page: http://zatzlabs.com/seamless-donations/
 Contact: http://zatzlabs.com/contact-us/

 Copyright (c) 2015 by David Gewirtz
 */

jQuery(document).ready(function () {

    // this is the new option that generates the UUID in the browser
    // UUID code based on https://github.com/broofa/node-uuid

    jQuery("input[name='_dgx_donate_session_id']").val(function () {
        var guid = jQuery(this).val();
        var ver = 'SDB01-'; // Session ID version: SD=Seamless Donations, B=Browser, 01=first versiom

        if (guid == "browser-uuid") {
            return ver + uuid.v4().toUpperCase();
        } else {
            return guid;
        }
    });

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
