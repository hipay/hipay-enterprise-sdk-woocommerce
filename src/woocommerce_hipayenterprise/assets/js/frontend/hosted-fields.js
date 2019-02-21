jQuery(function ($) {


    var checkout_form = $('form.checkout');
    var methodsInstance = {};
    var hipaySDK = {};

    function destroy() {

        for (var method in methodsInstance) {
            methodsInstance[method].destroy();
        }

        $(document.body).off('click', '#place_order', submitOrder);

        checkout_form.off('click', 'input[name="payment_method"]', addPaymentMethod);
    }

    function init() {
        methodsInstance = {};

        if (!containerExist()) {
            return true;
        }

        var defaultMethod = getSelectedMethod();

        hipaySDK = HiPay({
            username: hipay_config.apiUsernameTokenJs,
            password: hipay_config.apiPasswordTokenJs,
            environment: hipay_config.environment,
            lang: hipay_config.lang
        });

        createHostedFieldsInstance(defaultMethod);

    }

    function addPaymentMethod() {

        var method = getSelectedMethod();

        if (methodsInstance[method] === undefined) {
            createHostedFieldsInstance(method);
        }
    }

    function isHostedFields() {
        return hipay_config_card.operating_mode === "hosted_fields";
    }

    function isOneClick() {
        return $('input[name="wc-hipayenterprise_credit_card-payment-token"]:checked').val() !== undefined
            && $('input[name="wc-hipayenterprise_credit_card-payment-token"]:checked').val() !== 'new';
    }

    function submitOrder(e) {
        if (isHiPayMethod()) {
            e.preventDefault();
            e.stopPropagation();

            if (isCreditCardSelected() && (!isHostedFields() || isOneClick())) {
                processPayment();
            } else {

                var method = getSelectedMethod();

                if (isCreditCardSelected()) {
                    method = "card";
                }

                getPaymentData(method);
            }
        }
    }

    function processPayment() {
        checkout_form.submit();
    }

    function applyPaymentData(response, method) {

        var methodForm = $("#" + methodsInstance[method].options.selector);

        for (var data in response) {
            methodForm.append($("<input>").attr("type", "hidden").attr("name", method + "-" + data).val(response[data]));
        }
    }

    function getPaymentData(method) {
        hideErrorDiv(method);
        methodsInstance[method].getPaymentData()
            .then(function (response) {
                    if (isCreditCardSelected() && !isCardTypeActivated(response)) {
                        fillErrorDiv(hipay_config_i18n.activated_card_error, method);
                    } else {
                        applyPaymentData(response, method);
                        processPayment();
                    }
                },
                function (error) {
                    handleError(error, method);
                }
            );
    }

    function handleError(errors, method) {
        fillErrorDiv(errors[0].error, method);

        for (var error in errors) {
            var domElement = document.querySelector(
                "[data-hipay-id='hipay-" + method + "-field-error-" + errors[error].field + "']"
            );

            // If DOM element add error inside
            if (domElement) {
                domElement.innerText = errors[error].error;
            }
        }
    }

    function fillErrorDiv(error, method) {
        $("#error-js-" + method).show();
        $("#error-js-" + method).html(error);
    }

    function hideErrorDiv(method) {
        $("#error-js-" + method).hide();
        $("#error-js-" + method).html("");
    }

    function isHiPayMethod() {
        return $('input[name="payment_method"]:checked').val().indexOf('hipayenterprise_') !== -1;
    }

    function createHostedFieldsInstance(method) {

        if (isCreditCardSelected() && !isHostedFields()) {
            return true;
        }

        var configHostedFields = {};

        if (isCreditCardSelected()) {
            method = "card";
            configHostedFields = getCardConfig();
        }

        if (methodsInstance[method] !== undefined) {

            return methodsInstance[method];
        }

        blockUI();

        configHostedFields["selector"] = "hipayHF-container-" + method;
        configHostedFields["styles"] = {
            base: {
                fontFamily: hipay_config.fontFamily,
                color: hipay_config.color,
                fontSize: hipay_config.fontSize,
                fontWeight: hipay_config.fontWeight,
                placeholderColor: hipay_config.placeholderColor,
                caretColor: hipay_config.caretColor,
                iconColor: hipay_config.iconColor
            }
        };

        methodsInstance[method] = hipaySDK.create(method, configHostedFields);

        methodsInstance[method].on("blur", function (data) {
            // Get error container
            var domElement = document.querySelector(
                "[data-hipay-id='hipay-" + method + "-field-error-" + data.element + "']"
            );

            // Finish function if no error DOM element
            if (!domElement) {
                return;
            }

            // If not valid & not empty add error
            if (!data.validity.valid && !data.validity.empty) {
                domElement.innerText = data.validity.error;
            } else {
                domElement.innerText = '';
            }
        });

        methodsInstance[method].on("inputChange", function (data) {
            // Get error container
            var domElement = document.querySelector(
                "[data-hipay-id='hipay-" + method + "-field-error-" + data.element + "']"
            );

            // Finish function if no error DOM element
            if (!domElement) {
                return;
            }

            // If not valid & not potentiallyValid add error (input is focused)
            if (!data.validity.valid && !data.validity.potentiallyValid) {
                domElement.innerText = data.validity.error;
            } else {
                domElement.innerText = '';
            }
        });

        methodsInstance[method].on("ready", function () {
            unBlockUI();
        });
    }

    function getCardConfig() {

        var firstName = $('#billing_first_name').val();
        var lastName = $('#billing_last_name').val();

        return {
            multi_use: hipay_config_card.oneClick === "1",
            fields: {
                cardHolder: {
                    selector: "hipay-card-field-cardHolder",
                    defaultFirstname: firstName,
                    defaultLastname: lastName
                },
                cardNumber: {
                    selector: "hipay-card-field-cardNumber"
                },
                expiryDate: {
                    selector: "hipay-card-field-expiryDate"
                },
                cvc: {
                    selector: "hipay-card-field-cvc",
                    helpButton: true,
                    helpSelector: "hipay-help-cvc"
                }
            },
        };
    }

    function isCreditCardSelected() {
        return getSelectedMethod() === 'credit-card';
    }

    function getSelectedMethod() {
        return $('input[name="payment_method"]:checked').val().replace('hipayenterprise_', '').replace('_', '-');
    }

    function blockUI() {
        $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    }

    function unBlockUI() {
        $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').unblock();
    }

    function containerExist() {
        return $(".hipay-container-hosted-fields").length;
    }

    function isCardTypeActivated(result) {
        return (hipay_config_current_cart.activatedCreditCard.includes(result.payment_product));
    }

    $(document.body).on('updated_checkout', function () {
        destroy();
        init();
        $(document.body).on('click', '#place_order', submitOrder);
        checkout_form.on('click', 'input[name="payment_method"]', addPaymentMethod);
    });

    checkout_form.on('change', '#billing_first_name, #billing_last_name', function () {
        $(document.body).trigger('update_checkout');
    });
});
