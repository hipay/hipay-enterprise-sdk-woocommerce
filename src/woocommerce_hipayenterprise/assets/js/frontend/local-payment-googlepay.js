(function ($) {
    'use strict';

    var METHOD_ID = 'hipayenterprise_googlepay';
    var CONTAINER_ID = 'hipay-googlepay';
    var SUPPORTED_NETWORKS = ['visa', 'mastercard', 'maestro', 'cb'];

    var PROBE_ID = 'hipay-googlepay-probe';
    var PROBE_TIMEOUT_MS = 12000;

    var instance = null;
    var instanceKey = null;
    var probe = null;
    var probeTimer = null;
    var eligibility = { key: null, status: null };
    var generation = 0;

    function getConfig() {
        return window.hipay_config_googlepay || {};
    }

    function isOrderPayPage() {
        return Boolean(getConfig().isOrderPayPage);
    }

    function $methodItem() {
        return $('li.wc_payment_method.payment_method_' + METHOD_ID);
    }

    function isGooglePaySelected() {
        return $('input[name="payment_method"]:checked').val() === METHOD_ID;
    }

    function syncPlaceOrderButton() {
        $(document.body).toggleClass(
            'hipay-googlepay-selected',
            isGooglePaySelected() && $methodItem().hasClass('hipay-googlepay-ready')
        );
    }

    function showError(message) {
        var $error = $('#error-js-googlepay');
        if (!$error.length) {
            return;
        }
        $error.text(message).show();
        if ($error.is(':visible')) {
            $('html, body').animate({ scrollTop: $error.offset().top - 100 }, 400);
        }
    }

    function hideError() {
        $('#error-js-googlepay').hide().text('');
    }

    function isTosAccepted() {
        return $('#terms').length === 0 || $('#terms').is(':checked');
    }

    function syncTosNotice() {
        var $container = $('#' + CONTAINER_ID);
        var $notice = $('#google-pay-tos-notice');

        if (isTosAccepted()) {
            $notice.hide();
            $container.show();
        } else {
            $notice.text(getConfig().tosNoticeMessage || '').show();
            $container.hide();
        }
    }

    function hideMethod() {
        $methodItem().removeClass('hipay-googlepay-ready');

        if (isGooglePaySelected()) {
            var $other = $('input[name="payment_method"]').not('[value="' + METHOD_ID + '"]').first();
            if ($other.length) {
                $other.prop('checked', true).trigger('click').trigger('change');
            }
        }
        syncPlaceOrderButton();
    }

    function showMethod() {
        $methodItem().addClass('hipay-googlepay-ready');
        syncPlaceOrderButton();
    }

    function destroyInstance() {
        generation++;
        if (instance) {
            try {
                instance.destroy();
            } catch (e) {
            }
            instance = null;
        }
        instanceKey = null;

        var container = document.getElementById(CONTAINER_ID);
        if (container) {
            container.innerHTML = '';
        }
    }

    function destroyProbe() {
        clearTimeout(probeTimer);
        probeTimer = null;
        if (probe) {
            try {
                probe.destroy();
            } catch (e) {
            }
            probe = null;
        }
        $('#' + PROBE_ID + '-wrapper').remove();
    }

    function clearPaymentFields() {
        $('#googlepay-card-token, #googlepay-card-holder, #googlepay-payment-product, ' +
            '#googlepay-browser-info').val('');
    }

    function getBrowserInfo(hipayToken) {
        var fromToken = hipayToken && hipayToken.browser_info;
        if (fromToken) {
            return typeof fromToken === 'string' ? fromToken : JSON.stringify(fromToken);
        }

        return JSON.stringify({
            java_enabled: typeof navigator.javaEnabled === 'function' ? navigator.javaEnabled() : false,
            javascript_enabled: true,
            language: navigator.language,
            color_depth: window.screen.colorDepth,
            screen_height: window.screen.height,
            screen_width: window.screen.width,
            timezone: String(new Date().getTimezoneOffset()),
            http_user_agent: navigator.userAgent
        });
    }

    function submitCheckout() {
        var $form = isOrderPayPage() ? $('#order_review') : $('form.woocommerce-checkout');
        $form.trigger('submit');
    }

    function onPaymentAuthorized(hipayToken) {
        if (!isTosAccepted()) {
            showError(getConfig().tosNoticeMessage || '');
            return;
        }

        hideError();

        var paymentProduct = hipayToken.payment_product
            ? String(hipayToken.payment_product).toLowerCase().replace(/ /g, '-')
            : '';

        $('#googlepay-card-token').val(hipayToken.token || '');
        $('#googlepay-card-holder').val(hipayToken.card_holder || '');
        $('#googlepay-payment-product').val(paymentProduct);
        $('#googlepay-browser-info').val(getBrowserInfo(hipayToken));
        if (hipayToken.device_fingerprint) {
            $('#googlepay-device_fingerprint').val(hipayToken.device_fingerprint);
        }

        submitCheckout();
    }

    function bindInstanceEvents(current, myGeneration, key) {
        var isCurrent = function () {
            return myGeneration === generation;
        };

        current.on('ready', function () {
            if (isCurrent()) {
                eligibility = { key: key, status: 'ready' };
                showMethod();
            }
        });

        current.on('unavailable', function (event) {
            if (isCurrent()) {
                console.info('[HiPay Google Pay] unavailable', event && event.code);
                eligibility = { key: key, status: 'unavailable' };
                hideMethod();
            }
        });

        current.on('paymentAuthorized', function (hipayToken) {
            if (isCurrent()) {
                onPaymentAuthorized(hipayToken || {});
            }
        });

        current.on('paymentCanceled', function () {});

        current.on('paymentFailed', function (event) {
            if (!isCurrent()) {
                return;
            }
            console.warn('[HiPay Google Pay] paymentFailed', event && event.code, event && event.details);
            showError(getConfig().errorMessage || '');
        });

        current.on('warning', function (event) {
            console.warn('[HiPay Google Pay] warning', event);
        });

        current.on('error', function (event) {
            console.error('[HiPay Google Pay] error', event);
        });
    }

    function getRequest() {
        var config = getConfig();
        var $wrapper = $('.hipay-googlepay-wrapper');

        return {
            amount: String($wrapper.data('amount') || config.amount || '0.01'),
            currencyCode: String($wrapper.data('currency') || config.currency || '').toUpperCase(),
            countryCode: String($('#billing_country').val() || $wrapper.data('country') || config.countryCode || '')
                .toUpperCase(),
            supportedNetworks: SUPPORTED_NETWORKS
        };
    }

    function getEligibilityKey(request) {
        return [request.amount, request.currencyCode, request.countryCode].join('|');
    }

    function createWallet(selector, request) {
        var config = getConfig();
        var hipay = new window.HiPay({
            username: config.apiUsernameTokenJs,
            password: config.apiPasswordTokenJs,
            environment: config.environment,
            lang: config.lang || 'en'
        });

        return Promise.resolve(hipay.create('wallet', {
            selector: selector,
            provider: 'googlepay',
            displayName: config.shopName || '',
            existingPaymentMethodRequired: false,
            request: request
        }));
    }

    function applyEligibility(status) {
        if (status === 'unavailable') {
            hideMethod();
        } else {
            showMethod();
        }
    }

    function runProbe(request) {
        var key = getEligibilityKey(request);

        if (eligibility.key === key && eligibility.status) {
            applyEligibility(eligibility.status);
            return;
        }

        var myGeneration = ++generation;
        var settle = function (status) {
            if (myGeneration !== generation) {
                return;
            }
            eligibility = { key: key, status: status };
            applyEligibility(status);
            destroyProbe();
        };

        $('<div id="' + PROBE_ID + '-wrapper" aria-hidden="true" ' +
            'style="position:absolute;left:-9999px;width:320px;height:48px;overflow:hidden;">' +
            '<div id="' + PROBE_ID + '" style="width:320px;height:48px;"></div></div>').appendTo(document.body);

        probeTimer = setTimeout(function () {
            settle('unavailable');
        }, PROBE_TIMEOUT_MS);

        try {
            createWallet(PROBE_ID, request)
                .then(function (wallet) {
                    if (myGeneration !== generation || !wallet) {
                        if (wallet && typeof wallet.destroy === 'function') {
                            try { wallet.destroy(); } catch (e) { /* ignore */ }
                        }
                        if (myGeneration === generation) {
                            settle('unavailable');
                        }
                        return;
                    }
                    probe = wallet;
                    wallet.on('ready', function () { settle('ready'); });
                    wallet.on('unavailable', function (event) {
                        console.info('[HiPay Google Pay] unavailable', event && event.code);
                        settle('unavailable');
                    });
                })
                .catch(function (error) {
                    console.warn('[HiPay Google Pay] probe failed', error);
                    settle('unavailable');
                });
        } catch (e) {
            console.warn('[HiPay Google Pay] probe failed', e);
            settle('unavailable');
        }
    }

    function createInstance(request) {
        var myGeneration = ++generation;
        var key = getEligibilityKey(request);

        try {
            createWallet(CONTAINER_ID, request)
                .then(function (wallet) {
                    if (myGeneration !== generation) {
                        if (wallet && typeof wallet.destroy === 'function') {
                            try { wallet.destroy(); } catch (e) { /* ignore */ }
                        }
                        return;
                    }
                    if (!wallet) {
                        hideMethod();
                        return;
                    }
                    instance = wallet;
                    instanceKey = key;
                    bindInstanceEvents(wallet, myGeneration, key);
                })
                .catch(function (error) {
                    console.warn('[HiPay Google Pay] initialization failed', error);
                    if (myGeneration === generation) {
                        hideMethod();
                    }
                });
        } catch (e) {
            console.warn('[HiPay Google Pay] initialization failed', e);
            hideMethod();
        }
    }

    function init() {
        hideError();

        var container = document.getElementById(CONTAINER_ID);
        var canReuse = Boolean(
            instance && container && container.childElementCount > 0 &&
            window.HiPay && isGooglePaySelected() && instanceKey === getEligibilityKey(getRequest())
        );

        if (canReuse) {
            showMethod();
            syncTosNotice();
            return;
        }

        destroyInstance();
        destroyProbe();
        syncTosNotice();

        if (!window.HiPay || !container) {
            syncPlaceOrderButton();
            return;
        }

        var request = getRequest();

        if (isGooglePaySelected()) {
            if (eligibility.key === getEligibilityKey(request) && eligibility.status === 'ready') {
                showMethod();
            } else {
                syncPlaceOrderButton();
            }
            createInstance(request);
        } else {
            syncPlaceOrderButton();
            runProbe(request);
        }
    }

    $(function () {
        if (!$('#' + CONTAINER_ID).length && !$methodItem().length) {
            return;
        }

        $(document.body).on('change', 'input[name="payment_method"]', function () {
            if (isGooglePaySelected() && !instance) {
                destroyProbe();
                destroyInstance();
                if (window.HiPay) {
                    createInstance(getRequest());
                }
            } else if (!isGooglePaySelected() && instance) {
                destroyInstance();
            }
            syncPlaceOrderButton();
        });

        $(document.body).on('change', '#terms', syncTosNotice);

        $(document.body).on('updated_checkout', init);

        $(document.body).on('checkout_error', function () {
            clearPaymentFields();
            if (isGooglePaySelected()) {
                init();
            }
        });

        if (isOrderPayPage() || $('form.woocommerce-checkout').length === 0) {
            init();
        } else {
            setTimeout(function () {
                if (!instance) {
                    init();
                }
            }, 1500);
        }
    });

}(jQuery));
