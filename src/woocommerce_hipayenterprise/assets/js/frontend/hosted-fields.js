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
        methodsInstance[method].createToken()
            .then(function (response) {
                    if (isCreditCardSelected() && !isCardTypeActivated(response)) {
                        handleError(hipay_config_i18n.activated_card_error);
                    } else {
                        console.log(response);
                        applyPaymentData(response, method);
                        processPayment();
                    }
                },
                function (error) {
                    console.log(error);
                    handleError(error);
                }
            );
    }

    function handleError(error) {
        if (error) {
            $("#error-js").show();
            document.getElementById("error-js").innerHTML = error;
        } else {
            $("#error-js").hide();
        }
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
                    selector: "hipay-field-cardHolder",
                    defaultFirstname: firstName,
                    defaultLastname: lastName
                },
                cardNumber: {
                    selector: "hipay-field-cardNumber"
                },
                expiryDate: {
                    selector: "hipay-field-expiryDate"
                },
                cvc: {
                    selector: "hipay-field-cvc",
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

});
