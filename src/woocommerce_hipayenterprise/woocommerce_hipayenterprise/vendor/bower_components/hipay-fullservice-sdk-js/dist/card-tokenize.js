/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

jQuery(document).ready(function($){
    $(".ioBB").val($("#ioBB").val());
    $('<a href="#" class="tooltips">' + i18nCVCLabelLocal + '<span>' + i18nCVCTooltipLocal + '</span></a>').insertAfter('#cvc');
    $('.card-js #card-number').attr('placeholder',i18nCardNumberLocal);
    $('.card-js #the-card-name-id').attr('placeholder',i18nNameOnCardLocal);
    $('.card-js .expiry').attr('placeholder',i18nDateLocal);


    function checkPaymentDate() {
        if ($(".expiry").val() === null || $(".expiry").val() === "") {
            $(".expiry").addClass("error-input-hp");
            var pInsert = $("<span>"+i18nFieldIsMandatory+"</span>");
            $(".expiry").after(pInsert);
            pInsert.addClass("error-text-hp");
            return false;
        }
        return true;
    }

    $("#tokenizerForm").submit(function (e) {
        var form = this;
        // prevent form from being submitted 
        e.preventDefault();
        e.stopPropagation();

        if (myPaymentMethodSelected) {

            if ($("input[name=ccTokenHipay]:checked").length) {
                // at least one of the radio buttons was checked
                $("#tokenizerForm").hide();
                $("#payment-loader-hp").show();
                $("#payment-confirmation > .ps-shown-by-js > button").prop("disabled", true);

                form.submit();
                return true; // allow whatever action would normally happen to continue
            }

            var formErrors = !hiPayInputControl.checkControl("cc");
            formErrors = !checkPaymentDate() || formErrors;

            if (formErrors) {
                return false;
            }
            var multiUse = 0;
            if ($("#saveTokenHipay").is(":checked")) {
                multiUse = 1;
            }
            //set param for Api call
            var params = {
                card_number: $("#card-number").val(),
                cvc: $("#cvc").val(),
                card_expiry_month: $("input[name=expiry-month]").val(),
                card_expiry_year: $("input[name=expiry-year]").val(),
                card_holder: $("#the-card-name-id").val(),
                multi_use: multiUse
            };
            HiPay.setTarget(api_tokenjs_mode); // default is production/live

            HiPay.setCredentials(api_tokenjs_username, api_tokenjs_password_publickey);

            HiPay.create(params,
                function (result) {
                    // The card has been successfully tokenized
                    var token = result.token;
                    var brand = "";
                    if (result.hasOwnProperty("domestic_network")) {
                        brand = result.domestic_network;
                    } else {
                        brand = result.brand;
                    }
                    var pan = result.pan;
                    var card_expiry_month = result.card_expiry_month;
                    var card_expiry_year = result.card_expiry_year;
                    var card_holder = result.card_holder;
                    var issuer = result.issuer;
                    var country = result.country;

                    if (activatedCreditCard.indexOf(brand.toLowerCase().replace(" ", "-")) !== -1) {

                        $("#tokenizerForm").hide();
                        $("#payment-loader-hp").show();
                        $("#payment-confirmation > .ps-shown-by-js > button").prop("disabled", true);

                        // set tokenization response
                        $("#card-token").val(token);
                        $("#card-brand").val(brand);
                        $("#card-pan").val(pan);
                        $("#card-holder").val($("#the-card-name-id").val());
                        $("#card-expiry-month").val(card_expiry_month);
                        $("#card-expiry-year").val(card_expiry_year);
                        $("#card-issuer").val(issuer);
                        $("#card-country").val(country);

                        // we empty the form so we don't send credit card informations to the server
                        $("#card-number").val("");
                        $("#cvc").val("");
                        $("input[name=expiry-month]").val("");
                        $("input[name=expiry-year]").val("");
                        $("#the-card-name-id").val("");

                        //submit the form
                        form.submit();

                        return true;
                    } else {
                        $("#error-js").show();
                        $(".error").text(activatedCreditCardError);
                        return false;
                    }

                },
                function (errors) {
                    // An error occurred
                    $("#error-js").show();
                    if (typeof errors.message != "undefined") {
                        message = i18nBadRequest;
                        switch (errors.code) {
                            case 416:
                                message = i18nTokenisationError416;
                                break;
                        }
                        $(".error").text(message);
                    } else {
                        $(".error").text(i18nBadRequest);
                    }
                    return false;
                }
            );

        }
    });
});