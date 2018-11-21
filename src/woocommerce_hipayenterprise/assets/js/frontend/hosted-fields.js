jQuery(function($){

    var hostedFields = {

        init: function(){
            var self = this;
            $(document.body).on('click', '#place_order', function(e) {
                self.submitOrder(e,self);
            });

            this.hipaySDK = HiPay({
                username: hipay_config.apiUsernameTokenJs,
                password: hipay_config.apiPasswordTokenJs,
                environment: hipay_config.environment,
                lang: 'fr'
            });

            var firstName = $( '#billing_first_name' ).val();
            var lastName  = $( '#billing_last_name' ).val();

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
        initializeHostedFields: function() {
            hostedFieldInstance = this.hipaySDK.create("card", this.configHostedFields);
            var self= this;

            hostedFieldInstance.on("change", function (data) {
                self.handleError(data.valid, data.error);
            });


        },

        /**
         *
         * @param valid
         * @param error
         */
        handleError: function(valid, error) {
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
        processPayment: function(response){
            $( 'form[name="checkout"]' ).submit();
        },

        /**
         * Apply tokenization result to form
         *
         * @param result
         */
        applyTokenization: function(result) {
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

            // set tokenization response
            $("#card-token").val(token);
            $("#card-brand").val(brand);
            $("#card-pan").val(pan);
            $("#card-holder").val(card_holder);
            $("#card-expiry-month").val(card_expiry_month);
            $("#card-expiry-year").val(card_expiry_year);
            $("#card-issuer").val(issuer);
            $("#card-country").val(country);
        },
        /**
         *
         * @param e
         * @param hostedFields
         */
        submitOrder: function(e, hostedFields){
            e.preventDefault();
            e.stopPropagation();
            hostedFieldInstance.createToken()
                .then(function (response) {
                    //TODO test card is activated
                        hostedFields.applyTokenization(response);
                        hostedFields.processPayment(response);
                    },
                    function (error) {
                        hostedFields.handleError(true, error);
                    }
                );
        },
    };

    hostedFields.init();

});