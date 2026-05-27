/**
 * HiPay Enterprise SDK WooCommerce - Apple Pay frontend integration
 *
 * Initializes the HiPay paymentRequestButton component for Apple Pay on the
 * WooCommerce classic checkout. Hides the standard "Place Order" button while
 * the Apple Pay button is active and restores it when another payment method
 * is selected.
 *
 * Configuration is injected via wp_localize_script as `hipay_config_applepay`.
 */
(function ($) {
    'use strict';

    var applePayInstance = null;
    var submitButtonClone = null;
    var unhandledRejectionListener = null;
    var currentContainerId = null;
    var validationOverlay = null;

    /**
     * Returns true when the Apple Pay payment option is currently selected.
     */
    function isApplePaySelected() {
        return $('input[name="payment_method"]:checked').val() === 'hipayenterprise_applepay';
    }

    /**
     * Returns true when we are on the order-pay page
     */
    function isOrderPayPage() {
        return Boolean((window.hipay_config_applepay || {}).isOrderPayPage);
    }

    /**
     * Hide the WooCommerce "Place Order" button when Apple Pay is active.
     */
    function hidePlaceOrderButton() {
        if (isOrderPayPage()) {
            $('#place_order').hide();
        } else {
            var $btn = $('#payment .place-order .button');
            if ($btn.length) {
                submitButtonClone = $btn.clone(true);
                $btn.remove();
            }
        }
    }

    /**
     * Restore the WooCommerce "Place Order" button.
     */
    function showPlaceOrderButton() {
        if (isOrderPayPage()) {
            $('#place_order').show();
        } else {
            var $container = $('#payment .place-order');
            if ($container.length && !$container.find('.button').length && submitButtonClone) {
                $container.append(submitButtonClone);
                submitButtonClone = null;
            }
        }
    }

    /**
     * Remove the unhandled-rejection listener if one is registered.
     */
    function removeUnhandledRejectionListener() {
        if (unhandledRejectionListener) {
            window.removeEventListener('unhandledrejection', unhandledRejectionListener);
            unhandledRejectionListener = null;
        }
    }

    /**
     * Returns true when every visible required input in $row has a value.
     */
    function isRowFilled($row) {
        var filled = true;
        $row.find('input, select, textarea').not('[type="hidden"]').each(function () {
            var val = $(this).is(':checkbox')
                ? $(this).is(':checked')
                : ($(this).val() || '').trim().length > 0;
            if (!val) {
                filled = false;
                return false; // break .each
            }
        });
        return filled;
    }

    /**
     * Returns true when all visible required checkout fields are filled.
     */
    function areRequiredFieldsFilled() {
        var $form = $('form.woocommerce-checkout');
        if (!$form.length) {
            return true;
        }

        var allFilled = true;

        $form.find('.validate-required').each(function () {
            if (!$(this).is(':visible')) {
                return; // skip rows hidden by WooCommerce (e.g. state for some countries)
            }
            if (!isRowFilled($(this))) {
                allFilled = false;
                return false; // break .each
            }
        });

        return allFilled;
    }

    /**
     * Show validation errors
     */
    function validateCheckout() {
        var $form = $('form.woocommerce-checkout');
        if (!$form.length) {
            return;
        }

        $form.find('.validate-required').each(function () {
            var $row = $(this);
            if (!$row.is(':visible')) {
                return;
            }
            if (!isRowFilled($row)) {
                $row.addClass('woocommerce-invalid woocommerce-invalid-required-field');
                $row.removeClass('woocommerce-validated');
            }
        });

        var $firstError = $form.find('.woocommerce-invalid:visible').first();
        if ($firstError.length) {
            $('html, body').animate(
                { scrollTop: $firstError.offset().top - 100 },
                400,
                function () {
                    $firstError.find('input:visible, select:visible').first().focus();
                }
            );
        }
    }

    /**
     * Update the pointer-events state of the validation overlay.
     */
    function updateValidationOverlay() {
        if (!validationOverlay || !validationOverlay.parentNode) {
            return;
        }
        validationOverlay.style.pointerEvents = areRequiredFieldsFilled() ? 'none' : 'auto';
    }

    /**
     * Destroy the existing Apple Pay instance and clean up the container.
     */
    function destroyApplePayInstance() {
        removeUnhandledRejectionListener();
        hideTosNotice();
        $(document.body).off('input.applepay change.applepay');
        validationOverlay = null;
        currentContainerId = null;
        if (applePayInstance) {
            try {
                applePayInstance.destroy();
            } catch (e) {
            }
            applePayInstance = null;
        }

        const wrapper = document.getElementById('apple-pay-button-container');
        if (wrapper) {
            wrapper.innerHTML = '';
        }
    }

    /**
     * Returns true when a WooCommerce terms-and-conditions checkbox is present.
     */
    function isTosRequired() {
        return $('#terms').length > 0;
    }

    /**
     * Returns true when the TOS checkbox is not present or is checked.
     */
    function isTosAccepted() {
        return !isTosRequired() || $('#terms').is(':checked');
    }

    /**
     * Show the TOS notice and hide the Apple Pay button container.
     */
    function showTosNotice() {
        var config = window.hipay_config_applepay || {};
        var message = config.tosNoticeMessage || 'Please accept the terms and conditions to use Apple Pay.';
        $('#apple-pay-tos-notice').text(message).show();
        $('#apple-pay-button-container').hide();
    }

    /**
     * Hide the TOS notice and restore the Apple Pay button container.
     */
    function hideTosNotice() {
        $('#apple-pay-tos-notice').hide();
        $('#apple-pay-button-container').show();
    }

    /**
     * Show an error message in the Apple Pay error container.
     *
     * @param {string} message
     */
    function showError(message) {
        var $error = $('#error-js-applepay');
        $error.text(message).show();
        $('html, body').animate({ scrollTop: $error.offset().top - 100 }, 400);
    }

    /**
     * Apple Pay error handling.
     *
     * @param {Error|*} error
     */
    function handleApplePayError(error) {
        var message = (error && (error.message || String(error))) || '';
        if (message === 'HIPAY_PAYMENT_PRODUCT_NOT_AVAILABLE') {
            $('li.wc_payment_method.payment_method_hipayenterprise_applepay').hide();
            showPlaceOrderButton();
        } else {
            showError(message || 'Unable to initialize Apple Pay.');
        }
    }

    /**
     * Create and mount the Apple Pay button using the HiPay JS SDK.
     */
    function initApplePayInstance() {
        if (!window.HiPay) {
            return;
        }

        const config = window.hipay_config_applepay || {};

        const multiBrowserEnabled = Boolean(config.multiBrowserEnabled);
        if (!multiBrowserEnabled && (!window.ApplePaySession || !window.ApplePaySession.canMakePayments())) {
            $('li.wc_payment_method.payment_method_hipayenterprise_applepay').hide();
            return;
        }

        if (!isTosAccepted()) {
            showTosNotice();
            return;
        }
        hideTosNotice();
        hidePlaceOrderButton();
        const countryCode = $('#billing_country').val() || config.countryCode || '';

        removeUnhandledRejectionListener();
        unhandledRejectionListener = function (event) {
            const reason = event.reason;
            const message = (reason && (reason.message || String(reason))) || '';

            if (message === 'HIPAY_PAYMENT_PRODUCT_NOT_AVAILABLE') {
                event.preventDefault();
                removeUnhandledRejectionListener();
                handleApplePayError(reason);
            } else if (message.indexOf('HIPAY_SELECTOR_NOT_FOUND') === 0) {
                event.preventDefault();
                removeUnhandledRejectionListener();
            }
        };
        window.addEventListener('unhandledrejection', unhandledRejectionListener);

        var wrapper = document.getElementById('apple-pay-button-container');
        if (!wrapper) {
            removeUnhandledRejectionListener();
            return;
        }
        wrapper.innerHTML = '';
        currentContainerId = 'hipay-applepay-' + Math.random().toString(36).substr(2, 9);
        var innerContainer = document.createElement('div');
        innerContainer.id = currentContainerId;
        wrapper.appendChild(innerContainer);


        wrapper.style.position = 'relative';
        validationOverlay = document.createElement('div');
        validationOverlay.style.cssText =
            'position:absolute;top:0;left:0;width:100%;height:100%;z-index:10;cursor:pointer;';
        validationOverlay.addEventListener('click', validateCheckout);
        wrapper.appendChild(validationOverlay);
        updateValidationOverlay();

        $(document.body).on(
            'input.applepay change.applepay',
            'form.woocommerce-checkout input, form.woocommerce-checkout select, form.woocommerce-checkout textarea',
            updateValidationOverlay
        );

        try {
            var hipay = HiPay({
                username:    config.apiUsernameTokenJs,
                password:    config.apiPasswordTokenJs,
                environment: config.environment,
                lang:        config.lang || 'en',
            });

            var request = {
                countryCode:      countryCode,
                currencyCode:     config.currency || 'EUR',
                total: {
                    label:  config.shopName || 'Total',
                    amount: String(config.amount || '0.01'),
                },
                supportedNetworks: ['visa', 'masterCard', 'amex', 'maestro', 'cartesBancaires'],
            };

            var applePayStyle = {
                type:  config.buttonType  || 'plain',
                color: config.buttonStyle || 'black',
            };


            var createOptions = {
                displayName: config.shopName || '',
                request:     request,
                applePayStyle: applePayStyle,
                selector:    currentContainerId,
            };
            if (multiBrowserEnabled) {
                createOptions.multiBrowsers = true;
                if (config.displayMode) {
                    createOptions.displayMode = config.displayMode;
                }
            }
            var created = hipay.create('paymentRequestButton', createOptions);

            Promise.resolve(created)
                .then(function (instance) {
                    if (!instance) {
                        $('li.wc_payment_method.payment_method_hipayenterprise_applepay').hide();
                        return;
                    }
                    applePayInstance = instance;

                    applePayInstance.on('paymentAuthorized', function (hipayToken) {
                        var paymentProduct = hipayToken.payment_product
                            ? hipayToken.payment_product.toLowerCase().replace(/ /g, '-')
                            : '';
                        $('#applepay-card-token').val(hipayToken.token);
                        $('#applepay-card-holder').val(hipayToken.card_holder || '');
                        $('#applepay-payment-product').val(paymentProduct);
                        applePayInstance.completePaymentWithSuccess();
                        $('form.woocommerce-checkout').trigger('submit');
                    });

                    applePayInstance.on('cancel', function () {
                        applePayInstance.completePaymentWithFailure();
                    });

                    applePayInstance.on('paymentUnauthorized', function () {
                        applePayInstance.completePaymentWithFailure();
                        showError('Apple Pay payment was not authorized. Please try again.');
                    });
                })
                .catch(function (error) {
                    removeUnhandledRejectionListener();
                    handleApplePayError(error);
                });

        } catch (e) {
            removeUnhandledRejectionListener();
            handleApplePayError(e);
        }
    }

    /**
     * Called when the selected payment method changes.
     */
    function handlePaymentMethodChange() {
        if (isApplePaySelected()) {
            destroyApplePayInstance();
            initApplePayInstance();
        } else {
            destroyApplePayInstance();
            showPlaceOrderButton();
        }
    }

    /**
     * wait for the checkout to be ready before binding events.
     */
    $(function () {
        // React to payment method changes
        $(document.body).on('change', 'input[name="payment_method"]', handlePaymentMethodChange);

        // Re-evaluate when the TOS checkbox is toggled.
        $(document.body).on('change', '#terms', function () {
            if (isApplePaySelected()) {
                destroyApplePayInstance();
                initApplePayInstance();
            }
        });

        $(document.body).on('updated_checkout', function () {
            if (isApplePaySelected()) {
                destroyApplePayInstance();
                initApplePayInstance();
            } else {
                destroyApplePayInstance();
            }
        });
    });

}(jQuery));
