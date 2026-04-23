import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';

/**
 * Apple Pay Button Component
 */
const ApplePayButton = ({ config, onPaymentDataChange }) => {
    const { cartTotals } = useSelect((select) => {
        const store = select(CART_STORE_KEY);
        return { cartTotals: store.getCartTotals() };
    }, []);

    const [isLoading, setIsLoading]       = useState(true);
    const [isProcessing, setIsProcessing] = useState(false);
    const [error, setError]               = useState(null);
    const [fieldsValid, setFieldsValid]   = useState(false);
    const instanceRef                     = useRef(null);
    const containerIdRef                = useRef('hipay-applepay-' + Math.random().toString(36).substr(2, 9));
    const initializedRef                = useRef(false);
    const onPaymentDataChangeRef        = useRef(onPaymentDataChange);

    useEffect(() => {
        onPaymentDataChangeRef.current = onPaymentDataChange;
    }, [onPaymentDataChange]);

    // Hide the WC blocks "Place Order" button — the Apple Pay button IS the trigger.
    useEffect(() => {
        let placeOrderButton = null;
        let checkInterval    = null;

        const hideButton = (btn) => {
            if (btn && btn.style.display !== 'none') {
                btn.dataset.originalDisplay = btn.style.display || '';
                btn.style.display = 'none';
            }
        };

        const checkAndHide = () => {
            placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
            if (placeOrderButton) {
                hideButton(placeOrderButton);
                clearInterval(checkInterval);
                checkInterval = null;
            }
        };

        checkAndHide();
        if (!placeOrderButton) {
            checkInterval = setInterval(checkAndHide, 100);
            setTimeout(() => { if (checkInterval) clearInterval(checkInterval); }, 5000);
        }

        return () => {
            if (checkInterval) clearInterval(checkInterval);
            const btn = document.querySelector('.wc-block-components-checkout-place-order-button');
            if (btn) btn.style.display = btn.dataset.originalDisplay || '';
        };
    }, []);

    // Destroy instance on unmount.
    useEffect(() => {
        return () => {
            if (instanceRef.current) {
                try { instanceRef.current.destroy(); } catch (e) {}
                instanceRef.current = null;
            }
            initializedRef.current = false;
        };
    }, []);

    useEffect(() => {
        const checkFields = () => {
            const checkout = document.querySelector('.wc-block-checkout');
            if (!checkout) {
                setFieldsValid(false);
                return;
            }
            const required = Array.from(
                checkout.querySelectorAll('[required], [aria-required="true"]')
            ).filter(el => el.type !== 'hidden' && el.offsetParent !== null);
            setFieldsValid(
                required.every(el =>
                    el.type === 'checkbox' ? el.checked : (el.value || '').trim().length > 0
                )
            );
        };
        checkFields();
        const interval = setInterval(checkFields, 500);
        return () => clearInterval(interval);
    }, []);

    // Initialize once cart totals are available.
    useEffect(() => {
        if (initializedRef.current || !cartTotals || !cartTotals.total_price) {
            return;
        }

        const multiBrowserEnabled = Boolean(config.multiBrowserEnabled);

        const waitForContainer = () => {
            const container = document.getElementById(containerIdRef.current);
            if (!container) {
                setTimeout(waitForContainer, 100);
                return;
            }
            loadSDKAndCreate();
        };

        const loadSDKAndCreate = () => {
            if (!window.HiPay) {
                const script    = document.createElement('script');
                script.src      = config.hostedFieldsUrl || 'https://libs.hipay.com/js/sdkjs.js';
                script.async    = true;
                script.onload   = createButton;
                script.onerror  = () => {
                    setError(__('Failed to load HiPay SDK', 'hipayenterprise'));
                    setIsLoading(false);
                };
                document.body.appendChild(script);
            } else {
                createButton();
            }
        };

        const createButton = () => {
            try {
                const hipay = window.HiPay({
                    username:    config.apiUsernameTokenJs,
                    password:    config.apiPasswordTokenJs,
                    environment: config.sandbox_mode ? 'stage' : 'production',
                    lang:        config.lang || 'en',
                });

                let amount = 0;
                if (cartTotals?.total_price) {
                    amount = (parseFloat(cartTotals.total_price) / 100).toFixed(2);
                }
                if (parseFloat(amount) < 0.01) {
                    amount = '0.01';
                }

                const request = {
                    countryCode:       config.countryCode || '',
                    currencyCode:      cartTotals?.currency_code || config.currency || 'EUR',
                    total: {
                        label:  config.shopName || 'Total',
                        amount: String(amount),
                    },
                    supportedNetworks: ['visa', 'masterCard', 'amex', 'maestro', 'cartesBancaires'],
                };

                const applePayStyle = {
                    type:  config.buttonType  || 'plain',
                    color: config.buttonStyle || 'black',
                };

                const createOptions = {
                    displayName:   config.shopName || '',
                    request:       request,
                    applePayStyle: applePayStyle,
                    selector:      containerIdRef.current,
                };
                if (multiBrowserEnabled && config.displayMode) {
                    createOptions.displayMode = config.displayMode;
                }
                const applePayInstance = hipay.create('paymentRequestButton', createOptions);

                if (!applePayInstance) {
                    setIsLoading(false);
                    setError(__('Apple Pay is not available on this device or browser.', 'hipayenterprise'));
                    return;
                }

                applePayInstance.on('paymentAuthorized', (hipayToken) => {
                    const paymentProduct = hipayToken.payment_product
                        ? hipayToken.payment_product.toLowerCase().replace(/ /g, '-')
                        : '';

                    if (onPaymentDataChangeRef.current) {
                        onPaymentDataChangeRef.current({
                            'applepay-card-token':      hipayToken.token,
                            'applepay-card-holder':     hipayToken.card_holder || '',
                            'applepay-payment-product': paymentProduct,
                        });
                    }

                    applePayInstance.completePaymentWithSuccess();

                    setIsProcessing(true);

                    setTimeout(() => {
                        const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
                        if (placeOrderButton) {
                            placeOrderButton.click();
                        } else {
                            setIsProcessing(false);
                        }
                    }, 100);
                });

                applePayInstance.on('cancel', () => {
                    applePayInstance.completePaymentWithFailure();
                    if (onPaymentDataChangeRef.current) {
                        onPaymentDataChangeRef.current(null);
                    }
                });

                applePayInstance.on('paymentUnauthorized', () => {
                    applePayInstance.completePaymentWithFailure();
                    setError(__('Apple Pay payment was not authorized. Please try again.', 'hipayenterprise'));
                    if (onPaymentDataChangeRef.current) {
                        onPaymentDataChangeRef.current(null);
                    }
                });

                instanceRef.current   = applePayInstance;
                initializedRef.current = true;
                setIsLoading(false);
            } catch (e) {
                if (e.message === 'HIPAY_PAYMENT_PRODUCT_NOT_AVAILABLE') {
                    setError(__('Apple Pay is not available for this merchant account.', 'hipayenterprise'));
                } else {
                    setError(e.message || __('Unable to initialize Apple Pay.', 'hipayenterprise'));
                }
                setIsLoading(false);
            }
        };

        waitForContainer();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [cartTotals?.total_price]);

    /**
     * Overlay click handler — only fires when required fields are incomplete.
     */
    const handleValidationOverlayClick = () => {
        const checkout = document.querySelector('.wc-block-checkout');
        if (!checkout) return;

        const required = Array.from(
            checkout.querySelectorAll('[required], [aria-required="true"]')
        ).filter(el => el.type !== 'hidden' && el.offsetParent !== null);

        const emptyFields = required.filter(el =>
            el.type === 'checkbox' ? !el.checked : !(el.value || '').trim()
        );

        emptyFields.forEach(el => {
            el.dispatchEvent(new Event('blur', { bubbles: true }));
        });

        if (emptyFields[0]) {
            emptyFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            emptyFields[0].focus();
        }
    };

    return (
        <div className="hipay-applepay-blocks-container">
            {isLoading && (
                <div className="hipay-applepay-loading">
                    {__('Loading Apple Pay...', 'hipayenterprise')}
                </div>
            )}

            {isProcessing && (
                <div className="hipay-applepay-processing">
                    {__('Processing your Apple Pay payment...', 'hipayenterprise')}
                </div>
            )}

            {error && (
                <div className="hipay-error">
                    {error}
                </div>
            )}

            <div style={{ position: 'relative' }}>
                <div
                    id={containerIdRef.current}
                    className="hipay-applepay-button-container"
                    style={{ width: '100%', maxWidth: '100%', minHeight: '44px', display: (isProcessing || error) ? 'none' : 'block' }}
                ></div>

                {!fieldsValid && !isLoading && !error && (
                    <div
                        onClick={handleValidationOverlayClick}
                        style={{
                            position: 'absolute',
                            top: 0, left: 0, right: 0, bottom: 0,
                            zIndex: 10,
                            cursor: 'pointer',
                        }}
                        aria-hidden="true"
                    />
                )}
            </div>
        </div>
    );
};

export default ApplePayButton;
