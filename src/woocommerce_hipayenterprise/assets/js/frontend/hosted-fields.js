jQuery(function ($) {

    var hostedFieldsInstance;

    var hostedFields = {

        checkout_form: $( 'form.checkout' ),

        init: function () {
            var self = this;

            this.checkout_form.on( 'change', '#billing_first_name, #billing_last_name', function(){
                $( document.body ).trigger( 'update_checkout' );
            });

            // Evenement plutot sur le onSubmit
            $(document.body).on('click', '#place_order', function (e) {
                self.submitOrder(e, self);
            });

            this.hipaySDK = HiPay({
                username: hipay_config.apiUsernameTokenJs,
                password: hipay_config.apiPasswordTokenJs,
                environment: hipay_config.environment,
                lang: 'fr'
            });

            var firstName = $('#billing_first_name').val();
            var lastName = $('#billing_last_name').val();

            this.configHostedFields = {
                selector: "hipayHF-container",
                multi_use: false,
                fields: {
                    cardHolder: {
                        selector: "hipay-card-holder",
                        defaultFirstname: firstName,
                        defaultLastname: lastName
                    },
                    cardNumber: {
                        selector: "hipay-card-number"
                    },
                    expiryDate: {
                        selector: "hipay-date-expiry"
                    },
                    cvc: {
                        selector: "hipay-cvc",
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

            this.initializeHostedFields();
        },

        /**
         * Initialize Hipay Hosted Field
         */
        initializeHostedFields: function () {
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
        processPayment: function (response) {
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
            if (hostedFields.isHipayHostedFieldsSelected()) {
                e.preventDefault();
                e.stopPropagation();
                hostedFieldsInstance.createToken()
                    .then(function (response) {
                            if (isCardTypeActivated(response)) {
                                hostedFields.applyTokenization(response);
                                hostedFields.processPayment(response);
                            } else {
                                hostedFields.handleError(true, hipay_config_i18n.activated_card_error);
                            }
                        },
                        function (error) {
                            hostedFields.handleError(true, error);
                        }
                    );
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
        if (hostedFields.containerExist()) {
            $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            hostedFields.init();
        }
    });

});
