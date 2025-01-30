'use strict';
jQuery(document).ready(($) => {
    // Separation of Concerns and Modularization
    const checkoutUtils = (() => {
        const cacheSelectors = () => ({
            checkoutForm: isAddPaymentPage() ? $('#add_payment_method') : $('form.checkout'),
            submitButton: $('#payment .place-order .button').clone(),
            paypalField: $('#paypal-field')
        });

        const isAddPaymentPage = () => $('#add_payment_method').length > 0;

        const getSelectedMethod = () => {
            const selectedValue = $('input[name="payment_method"]:checked').val();
            return selectedValue ?
                selectedValue
                    .replace('hipayenterprise_', '')
                    .replace(/_/g, '-')
                : '';
        };

        const handleSubmitButton = (method, selectors) => {
            const { submitButton } = selectors;
            const placeOrderButton = $('#payment .place-order .button');

            if (method === 'paypal' && paypal_version?.v2 !== null) {
                placeOrderButton.remove();
            } else if (!placeOrderButton.length) {
                $('#payment .place-order').append(submitButton);
            }
        };

        const destroyMethods = (methodsInstance) => {
            Object.values(methodsInstance).forEach((method) => {
                if (method) {
                    method.destroy();
                }
            });
            Object.keys(methodsInstance).forEach((key) => delete methodsInstance[key]);
        };

        return {
            cacheSelectors,
            isAddPaymentPage,
            getSelectedMethod,
            handleSubmitButton,
            destroyMethods
        };
    })();

    const paypalIntegration = (() => {
        let methodsInstance = {};

        const init = (selectors) => {
            const { paypalField } = selectors;
            const method = checkoutUtils.getSelectedMethod();
            checkoutUtils.handleSubmitButton(method, selectors);

            if (method === 'paypal' && paypal_version?.v2 !== null) {
                methodsInstance[method] = createPaypalInstance(method);
                handlePaypalEvents(methodsInstance[method], selectors.checkoutForm);
            }
        };

        const createPaypalInstance = (method) => {
            const hipaySDK = new HiPay({
                username: hipay_config_paypal.apiUsernameTokenJs,
                password: hipay_config_paypal.apiPasswordTokenJs,
                environment: hipay_config_paypal.environment,
                lang: hipay_config_paypal.lang
            });

            const options = {
                template: 'auto',
                request: {
                    locale: hipay_config_paypal.locale,
                    currency: hipay_config_paypal.currency,
                    amount: Number(hipay_config_paypal.amount)
                },
                paypalButtonStyle: {
                    shape: hipay_config_paypal.buttonShape,
                    height: Number(hipay_config_paypal.buttonHeight || 40),
                    color: hipay_config_paypal.buttonColor,
                    label: hipay_config_paypal.buttonLabel
                },
                selector: 'paypal-field',
                canPayLater: Boolean(hipay_config_paypal.bnpl)
            };

            return hipaySDK.create(method, options);
        };

        const handlePaypalEvents = (instancePaypalButton, checkoutForm) => {
            instancePaypalButton.on('paymentAuthorized', (hipayToken) => {
                $("#paypal-orderId").val(hipayToken.orderID);
                $("#paypal-payment-product").val('paypal');
                $("#paypal-browserInfo").val(JSON.stringify(hipayToken.browser_info));
                $("#paypal-paymentmethod").val('paypal');
                $("#paypal-productlist").val('paypal');
                processPayment(checkoutForm);
            });
        };

        const processPayment = (checkoutForm) => {
            checkoutForm.submit();
        };

        const updateMethods = (selectors) => {
            checkoutUtils.destroyMethods(methodsInstance);

            if ($('input[name="payment_method"]:checked').length) {
                init(selectors);
            }
        };

        return {
            init,
            updateMethods
        };
    })();

    const checkoutEventHandlers = (() => {
        let pageLoaded = false;
        const selectedMethod = checkoutUtils.getSelectedMethod();
        const selectors = checkoutUtils.cacheSelectors();

        const handlePaymentMethodChange = () => {
            if (selectedMethod !== 'paypal') {
                paypalIntegration.updateMethods(selectors);
            }
            pageLoaded = true;
        };

        const handleOrderReviewUpdate = () => {
            paypalIntegration.updateMethods(selectors);
            pageLoaded = true;
            checkoutUtils.handleSubmitButton(checkoutUtils.getSelectedMethod(), selectors);
        };

        const bindEvents = () => {
            $(document.body).on('payment_method_selected', () => {
                if (pageLoaded) {
                    paypalIntegration.updateMethods(selectors);
                }
            });

            $(document).ajaxComplete((event, xhr, settings) => {
                if (settings.url?.includes('update_order_review')) {
                    handleOrderReviewUpdate();
                }
            });
        };

        return {
            bindEvents
        };
    })();

    // Initialize the checkout process
    checkoutEventHandlers.bindEvents();
});