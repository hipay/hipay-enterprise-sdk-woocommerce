jQuery(function($){

    var hostedFields = {
        container: '.payment_method_' + braintree_hosted_fields_vars.gateway_id,

        init: function(){
            $(document.body).on('click', '#place_order', this.submit_order());

            this.hipaySDK = HiPay({
                username: hipay_config.apiUsernameTokenJs,
                password: hipay_config.apiPasswordTokenJs,
                environment: hipay_config.environment,
                lang: 'fr'
            });

            this.configHostedFields = {
                selector: "hipayHF-container",
                multi_use: false,
                fields: {
                    cardHolder: {
                        selector: "hipay-card-holder",
                        // defaultFirstname: config.defaultFirstname,
                        // defaultLastname: config.defaultLastname
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
                // styles: {
                //     base: config.style.base
                // }
            };

            this.initializeHostedFields();
        },

        /**
         *
         */
        initializeHostedFields: function() {
            hipayHostedFields = this.hipaySDK.create("card", this.configHostedFields);

            hipayHostedFields.on("change", function (data) {
                handleError(data.valid, data.error);
            });

            function handleError(valid, error) {
                if (error) {
                    $("#error-js").show();
                    document.getElementById("error-js").innerHTML = '<i class="material-icons"></i>' + error;
                } else {
                    $("#error-js").hide();
                }
            }
        },

        /**
         *
         * @param e
         */
        submit_order: function(e){
            e.preventDefault();
            hipayHostedFields.createToken()
                .then(function (response) {
                        //creditCardToken = response.token;

                        var brand = "";
                        if (response.hasOwnProperty("domestic_network")) {
                            brand = response.domestic_network;
                        } else {
                            brand = response.brand;
                        }

                        //creditCardType = brand;
                        //placeOrder();

                        //creditCardToken = "";
                    },
                    function (error) {
                        this.handleError(error);
                    }
                );
        },

        /**
         *
         * @param e
         */
        tokenize: function(e){

        },

    };


    hostedFields.init();


});