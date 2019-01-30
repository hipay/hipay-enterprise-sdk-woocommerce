jQuery(function ($) {

    var hostedFieldsInstance;

    var hostedFields = {

        checkout_form: $('form.checkout'),

        init: function () {
            var self = this;

            this.checkout_form.on('change', '#billing_first_name, #billing_last_name', function () {
                $(document.body).trigger('update_checkout');
            });

            // Evenement plutot sur le onSubmit
            $(document.body).on('click', '#place_order', function (e) {
                self.submitOrder(e, self);
            });

            if (hostedFields.containerExist()) {

                $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                this.initializeHostedFields();
            }
        },

        /**
         * Initialize Hipay Hosted Field
         */
        initializeHostedFields: function () {

            this.hipaySDK = HiPay({
                username: hipay_config.apiUsernameTokenJs,
                password: hipay_config.apiPasswordTokenJs,
                environment: hipay_config.environment,
                lang: hipay_config.lang
            });

            var firstName = $('#billing_first_name').val();
            var lastName = $('#billing_last_name').val();

            this.configHostedFields = {
                selector: "hipayHF-container",
                multi_use: false,
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
                styles: {
                    base: {
                        fontFamily: hipay_config.fontFamily,
                        color: hipay_config.color,
                        fontSize: hipay_config.fontSize,
                        fontWeight: hipay_config.fontWeight,
                        placeholderColor: hipay_config.placeholderColor,
                        caretColor: hipay_config.caretColor,
                        iconColor: hipay_config.iconColor
                    }
                }
            };

            hostedFieldsInstance = this.hipaySDK.create("card", this.configHostedFields);
            var self = this;

            hostedFieldsInstance.on("change", function (data) {
                self.handleError(data.valid, data.error);
            });

            hostedFieldsInstance.on("ready", function () {
                $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').unblock();
            });
        },

        /**
         *
         * @param valid
         * @param error
         */
        handleError: function (valid, error) {
            if (error) {
                $("#error-js").show();
                document.getElementById("error-js").innerHTML = error;
            } else {
                $("#error-js").hide();
            }
        },

        /**
         *
         * @param response
         */
        processPayment: function () {
            $('form[name="checkout"]').submit();
        },

        /**
         * Apply tokenization result to form
         *
         * @param result
         */
        applyTokenization: function (result) {
            $("#payment-product").val(result.payment_product);
            $("#card-token").val(result.token);
            $("#card-holder").val(result.card_holder);
        },

        /**
         *
         * @returns {boolean}
         */
        isHipayHostedFieldsSelected: function () {
            return $('input[name="payment_method"]:checked').val() === hipay_config.hipay_gateway_id;
        },

        /**
         * @returns {boolean}
         */
        containerExist: function () {
            return $("#hipayHF-container").length;
        },

        /**
         *
         * @param e
         * @param hostedFields
         */
        submitOrder: function (e, hostedFields) {
            if (hostedFields.containerExist() && hostedFields.isHipayHostedFieldsSelected()) {
                e.preventDefault();
                e.stopPropagation();
                hostedFieldsInstance.createToken()
                    .then(function (response) {
                            if (isCardTypeActivated(response)) {
                                hostedFields.applyTokenization(response);
                                hostedFields.processPayment();
                            } else {
                                hostedFields.handleError(true, hipay_config_i18n.activated_card_error);
                            }
                        },
                        function (error) {
                            hostedFields.handleError(true, error);
                        }
                    );
            } else {
                e.preventDefault();
                e.stopPropagation();

                var hipayMethod = $('input[name="payment_method"]:checked')
                    .val()
                    .replace('hipayenterprise_','')
                    .replace('_', '-');

                if (hiPayInputControl.checkControl(hipayMethod)) {
                    hostedFields.processPayment();
                }else{
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $('input[name="payment_method"]:checked').offset().top
                    }, 1000);
                }
            }
        }
    };

    /**
     *
     * @param result
     * @returns {boolean}
     */
    function isCardTypeActivated(result) {
        return (hipay_config_current_cart.activatedCreditCard.includes(result.payment_product));
    }

    $(document.body).on('updated_checkout', function () {
        hostedFields.init();
    });

});
