import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { CART_STORE_KEY, CHECKOUT_STORE_KEY } from '@woocommerce/block-data';

/**
 * PayPal v2 Button Component
 * Renders and manages the PayPal button integration for blocks checkout
 */
const PayPalButton = ({ config, billing, shippingData, onPaymentDataChange }) => {
    // Get cart totals from the store
    const cartTotals = useSelect((select) => {
        const store = select(CART_STORE_KEY);
        return store.getCartTotals();
    }, []);

    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [isReady, setIsReady] = useState(false);
    const [isProcessing, setIsProcessing] = useState(false);
    const buttonInstanceRef = useRef(null);
    const containerIdRef = useRef('paypal-field-' + Math.random().toString(36).substr(2, 9));
    const initializedRef = useRef(false);
    const onPaymentDataChangeRef = useRef(onPaymentDataChange);

    // Keep callback ref updated without triggering re-initialization
    useEffect(() => {
        onPaymentDataChangeRef.current = onPaymentDataChange;
    }, [onPaymentDataChange]);

    // Hide the WooCommerce Blocks Place Order button for PayPal v2
    useEffect(() => {
        // Target only the blocks checkout button
        const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
        
        if (placeOrderButton) {
            // Store original display value to restore later
            const originalDisplay = placeOrderButton.style.display || '';
            placeOrderButton.dataset.originalDisplay = originalDisplay;
            
            // Hide the button
            placeOrderButton.style.display = 'none';
            
            // Cleanup: restore button when component unmounts (user switches payment method)
            return () => {
                if (placeOrderButton) {
                    placeOrderButton.style.display = placeOrderButton.dataset.originalDisplay || '';
                }
            };
        }
    }, []);

    // Clean up on unmount
    useEffect(() => {
        return () => {
            if (buttonInstanceRef.current && typeof buttonInstanceRef.current.destroy === 'function') {
                try {
                    buttonInstanceRef.current.destroy();
                    buttonInstanceRef.current = null;
                } catch (e) {
                    console.warn('[HiPay PayPal] Error destroying button instance:', e);
                }
            }
            initializedRef.current = false;
        };
    }, []);

    // Initialize PayPal button (only once)
    useEffect(() => {
        // Don't initialize if already done, or if cart totals aren't available yet
        if (initializedRef.current || !cartTotals || !cartTotals.total_price) {
            return;
        }

        const initializePayPalButton = async () => {
            try {
                setIsLoading(true);
                setError(null);

                // Wait for container to be in DOM
                const checkContainer = () => {
                    const container = document.getElementById(containerIdRef.current);
                    if (!container) {
                        setTimeout(checkContainer, 100);
                        return;
                    }
                    setupButton();
                };

                const setupButton = () => {
                    // Load HiPay SDK if not already loaded
                    if (!window.HiPay) {
                        const script = document.createElement('script');
                        script.src = config.hostedFieldsUrl || 'https://libs.hipay.com/js/sdkjs.js';
                        script.async = true;
                        script.onload = () => createPayPalButton();
                        script.onerror = () => {
                            setError(__('Failed to load PayPal SDK', 'hipayenterprise'));
                            setIsLoading(false);
                        };
                        document.body.appendChild(script);
                    } else {
                        createPayPalButton();
                    }
                };

                checkContainer();
            } catch (err) {
                console.error('[HiPay PayPal] Initialization error:', err);
                setError(err.message || __('Failed to initialize PayPal', 'hipayenterprise'));
                setIsLoading(false);
            }
        };

        const createPayPalButton = () => {
            try {
                const credentials = config.sandbox_mode
                    ? {
                          username: config.api_tokenjs_username_test,
                          password: config.api_tokenjs_password_publickey_test,
                      }
                    : {
                          username: config.api_tokenjs_username_production,
                          password: config.api_tokenjs_password_publickey_production,
                      };

                if (!credentials.username || !credentials.password) {
                    throw new Error('PayPal credentials not configured');
                }

                const hipaySDK = new window.HiPay({
                    username: credentials.username,
                    password: credentials.password,
                    environment: config.sandbox_mode ? 'stage' : 'production',
                    lang: config.lang || 'en',
                });

                // Get amount from cart totals
                // WooCommerce blocks stores total_price in cents (string format)
                let amount = 0;
                if (cartTotals?.total_price) {
                    // total_price is in cents as a string, convert to decimal
                    amount = (parseFloat(cartTotals.total_price) / 100).toFixed(2);
                } else if (cartTotals?.total_items) {
                    // Fallback to total_items if available (also in cents)
                    amount = (parseFloat(cartTotals.total_items) / 100).toFixed(2);
                }

                // Ensure minimum amount
                if (parseFloat(amount) < 0.01) {
                    amount = '0.01';
                }

                const currency = cartTotals?.currency_code || 'EUR';

                const options = {
                    template: 'auto',
                    request: {
                        locale: config.locale || 'en_US',
                        currency: currency,
                        amount: String(amount),
                    },
                    paypalButtonStyle: {
                        shape: config.paypalConfig?.buttonShape || 'rect',
                        height: Number(config.paypalConfig?.buttonHeight || 40),
                        color: config.paypalConfig?.buttonColor || 'gold',
                        label: config.paypalConfig?.buttonLabel || 'paypal',
                    },
                    selector: containerIdRef.current,
                    canPayLater: Boolean(config.paypalConfig?.bnpl),
                };

                const buttonInstance = hipaySDK.create('paypal', options);

                if (!buttonInstance) {
                    throw new Error('Failed to create PayPal button instance');
                }

                // Handle payment authorization - use ref to avoid closure issues
                buttonInstance.on('paymentAuthorized', (response) => {
                    // Store payment data for submission using the current ref value
                    // Keys must match shortcode template (local-paypal.php hidden field names)
                    if (onPaymentDataChangeRef.current) {
                        onPaymentDataChangeRef.current({
                            paypalOrderId: response.orderID,
                            browserInfo: JSON.stringify(response.browser_info || {}),
                            method: 'paypal',
                            paymentmethod: 'paypal',
                            productlist: 'paypal',
                        });
                    }

                    // Automatically submit checkout after PayPal authorization
                    // Similar to shortcode version: checkoutForm.submit()
                    setIsProcessing(true);

                    // Small delay to ensure payment data is stored
                    setTimeout(() => {
                        const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
                        if (placeOrderButton) {
                            placeOrderButton.click();
                        } else {
                            setIsProcessing(false);
                        }
                    }, 100);
                });

                buttonInstance.on('error', (err) => {
                    console.error('[HiPay PayPal] Button error:', err);
                    setError(err.message || __('PayPal error occurred', 'hipayenterprise'));
                });

                buttonInstance.on('ready', () => {
                    setIsReady(true);
                    setIsLoading(false);
                });

                buttonInstanceRef.current = buttonInstance;
                initializedRef.current = true;
            } catch (err) {
                console.error('[HiPay PayPal] Button creation error:', err);
                setError(err.message || __('Failed to create PayPal button', 'hipayenterprise'));
                setIsLoading(false);
            }
        };

        initializePayPalButton();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [cartTotals?.total_price]);

    return (
        <div className="hipay-paypal-v2-container">
            {isLoading && (
                <div className="hipay-paypal-loading">
                    {__('Loading PayPal...', 'hipayenterprise')}
                </div>
            )}

            {isProcessing && (
                <div className="hipay-paypal-processing">
                    {__('Processing your PayPal payment...', 'hipayenterprise')}
                </div>
            )}

            {error && (
                <div className="hipay-error">
                    {error}
                </div>
            )}

            <div
                id={containerIdRef.current}
                className="hipay-paypal-button-container"
                style={{ minHeight: '50px', display: isProcessing ? 'none' : 'block' }}
            ></div>

            {!isReady && !error && !isLoading && !isProcessing && (
                <div className="hipay-paypal-info">
                    {__('Click the PayPal button above to complete your payment', 'hipayenterprise')}
                </div>
            )}
        </div>
    );
};

export default PayPalButton;
