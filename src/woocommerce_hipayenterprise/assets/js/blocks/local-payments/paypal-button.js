import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';

/**
 * PayPal v2 Button Component
 * Renders and manages the PayPal button integration for blocks checkout
 */
const PayPalButton = ({ config, billing, shippingData, cartTotals: cartTotalsProp, onPaymentDataChange }) => {
    // Get cart data from the store for real-time updates
    const { cartTotals, billingAddress, shippingAddress } = useSelect((select) => {
        const store = select(CART_STORE_KEY);
        return {
            cartTotals: store.getCartTotals(),
            billingAddress: store.getCustomerData()?.billingAddress || {},
            shippingAddress: store.getCustomerData()?.shippingAddress || {},
        };
    }, []);

    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [addressError, setAddressError] = useState(null);
    const [isReady, setIsReady] = useState(false);
    const [isProcessing, setIsProcessing] = useState(false);
    const buttonInstanceRef = useRef(null);
    const containerIdRef = useRef('paypal-field-' + Math.random().toString(36).substr(2, 9));
    const initializedRef = useRef(false);
    const onPaymentDataChangeRef = useRef(onPaymentDataChange);

    // Get i18n translations
    const i18n = {
        addressRequired: __('Shipping address is required for PayPal payment.', 'hipayenterprise'),
        invalidAddressPrefix: __('Invalid delivery address. Please check or correct the following fields: ', 'hipayenterprise'),
        unableToInitialize: __('Unable to initialize PayPal. Please check your shipping address.', 'hipayenterprise'),
        fieldNames: {
            zipCode: __('Postal Code', 'hipayenterprise'),
            city: __('City', 'hipayenterprise'),
            country: __('Country', 'hipayenterprise'),
            streetaddress: __('Street Address', 'hipayenterprise'),
        }
    };

    /**
     * Extract shipping address
     */
    const getShippingAddress = () => {
        if (domAddress && (domAddress.zipCode || domAddress.city || domAddress.streetaddress)) {
            return domAddress;
        }

        const hasShippingAddress = shippingAddress?.postcode || shippingAddress?.city || shippingAddress?.address_1;
        const address = hasShippingAddress ? shippingAddress : billingAddress;

        return {
            zipCode: address?.postcode || '',
            city: address?.city || '',
            country: address?.country || '',
            streetaddress: address?.address_1 || '',
            streetaddress2: address?.address_2 || '',
            firstname: address?.first_name || '',
            lastname: address?.last_name || '',
        };
    };

    const [domAddress, setDomAddress] = useState(null);
    const addressChangeTimeoutRef = useRef(null);

    const getAddressFromDOM = () => {
        const getValue = (id) => {
            const el = document.getElementById(id);
            if (!el) return '';
            const value = el.value;
            return (value && typeof value === 'string') ? value.trim() : '';
        };

        const findFieldValue = (patterns) => {
            for (const pattern of patterns) {
                let value = getValue(pattern);
                if (value) return value;

                const el = document.querySelector(`[id*="${pattern}"]`) ||
                           document.querySelector(`[name*="${pattern}"]`) ||
                           document.querySelector(`input[id$="${pattern}"]`) ||
                           document.querySelector(`select[id$="${pattern}"]`);
                if (el) {
                    const elValue = el.value;
                    if (elValue && typeof elValue === 'string') return elValue.trim();
                }
            }
            return '';
        };

        let postcode = findFieldValue(['shipping-postcode', 'billing-postcode', 'postcode']);
        let city = findFieldValue(['shipping-city', 'billing-city', 'city']);
        let country = findFieldValue(['shipping-country', 'billing-country', 'country']);
        let address1 = findFieldValue(['shipping-address_1', 'billing-address_1', 'address_1', 'address-1']);
        let address2 = findFieldValue(['shipping-address_2', 'billing-address_2', 'address_2', 'address-2']);
        let firstName = findFieldValue(['shipping-first_name', 'billing-first_name', 'first_name', 'first-name']);
        let lastName = findFieldValue(['shipping-last_name', 'billing-last_name', 'last_name', 'last-name']);

        if (!country && (billingAddress?.country || shippingAddress?.country)) {
            country = shippingAddress?.country || billingAddress?.country || '';
        }

        return {
            zipCode: postcode,
            city: city,
            country: country,
            streetaddress: address1,
            streetaddress2: address2,
            firstname: firstName,
            lastname: lastName,
        };
    };

    useEffect(() => {
        const handleAddressChange = (e) => {
            if (addressChangeTimeoutRef.current) {
                clearTimeout(addressChangeTimeoutRef.current);
            }

            addressChangeTimeoutRef.current = setTimeout(() => {
                const newAddress = getAddressFromDOM();
                console.log('[HiPay PayPal] DOM address changed:', newAddress);
                setDomAddress(newAddress);
            }, 300);
        };

        const fieldSelectors = [
            '#billing-postcode', '#billing-city', '#billing-country', '#billing-address_1',
            '#shipping-postcode', '#shipping-city', '#shipping-country', '#shipping-address_1'
        ];

        fieldSelectors.forEach(selector => {
            const field = document.querySelector(selector);
            if (field) {
                field.addEventListener('input', handleAddressChange);
                field.addEventListener('change', handleAddressChange);
            }
        });

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    fieldSelectors.forEach(selector => {
                        const field = document.querySelector(selector);
                        if (field && !field.dataset.hipayListening) {
                            field.dataset.hipayListening = 'true';
                            field.addEventListener('input', handleAddressChange);
                            field.addEventListener('change', handleAddressChange);
                        }
                    });
                }
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });

        const initialAddress = getAddressFromDOM();
        if (initialAddress.zipCode || initialAddress.city || initialAddress.streetaddress) {
            setDomAddress(initialAddress);
        }

        return () => {
            if (addressChangeTimeoutRef.current) {
                clearTimeout(addressChangeTimeoutRef.current);
            }
            observer.disconnect();
            fieldSelectors.forEach(selector => {
                const field = document.querySelector(selector);
                if (field) {
                    field.removeEventListener('input', handleAddressChange);
                    field.removeEventListener('change', handleAddressChange);
                }
            });
        };
    }, []);

    /**
     * Validate shipping address
     */
    const validateShippingAddress = (address) => {
        if (!address || typeof address !== 'object') {
            return {
                isValid: false,
                errorMessage: i18n.addressRequired
            };
        }

        const requiredFields = ['zipCode', 'city', 'country', 'streetaddress'];
        const missingFields = requiredFields.filter((field) => {
            const value = address[field];
            return !value || (typeof value === 'string' && value.trim() === '');
        });

        if (missingFields.length > 0) {
            const translatedFields = missingFields.map((field) => {
                return i18n.fieldNames[field] || field;
            });

            return {
                isValid: false,
                errorMessage: i18n.invalidAddressPrefix + translatedFields.join(', ') + '.'
            };
        }

        return { isValid: true };
    };

    useEffect(() => {
        onPaymentDataChangeRef.current = onPaymentDataChange;
    }, [onPaymentDataChange]);

    useEffect(() => {
        let placeOrderButton = null;
        let checkInterval = null;
        
        const hideButton = (button) => {
            if (button && button.style.display !== 'none') {
                const originalDisplay = button.style.display || '';
                button.dataset.originalDisplay = originalDisplay;
                
                button.style.display = 'none';
            }
        };
        
        const checkAndHideButton = () => {
            placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
            
            if (placeOrderButton) {
                hideButton(placeOrderButton);
                if (checkInterval) {
                    clearInterval(checkInterval);
                    checkInterval = null;
                }
            }
        };
        
        checkAndHideButton();
        
        if (!placeOrderButton) {
            checkInterval = setInterval(checkAndHideButton, 100);
            
            setTimeout(() => {
                if (checkInterval) {
                    clearInterval(checkInterval);
                    checkInterval = null;
                }
            }, 5000);
        }
        
        return () => {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
            
            const button = document.querySelector('.wc-block-components-checkout-place-order-button');
            if (button) {
                button.style.display = button.dataset.originalDisplay || '';
            }
        };
    }, []);

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

    // Re-initialize PayPal button when address changes (from DOM)
    useEffect(() => {
        if (!domAddress) return;

        const validationResult = validateShippingAddress(domAddress);

        console.log('[HiPay PayPal] Address changed, validating:', domAddress, validationResult);

        // If button was already initialized and address changed, we need to recreate it
        if (initializedRef.current && buttonInstanceRef.current) {
            if (!validationResult.isValid) {
                // Destroy existing button and show error
                try {
                    buttonInstanceRef.current.destroy();
                    buttonInstanceRef.current = null;
                } catch (e) {
                    console.warn('[HiPay PayPal] Error destroying button:', e);
                }
                initializedRef.current = false;
                setAddressError(validationResult.errorMessage);
                setIsReady(false);
            } else if (addressError) {
                // Address is now valid, reinitialize
                setAddressError(null);
                initializedRef.current = false;
            }
        } else if (!initializedRef.current) {
            // Not yet initialized, update validation state
            if (!validationResult.isValid) {
                setAddressError(validationResult.errorMessage);
                setIsLoading(false);
            } else {
                setAddressError(null);
            }
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [domAddress]);

    // Initialize PayPal button
    useEffect(() => {
        // Don't initialize if already done, or if cart totals aren't available yet
        if (initializedRef.current || !cartTotals || !cartTotals.total_price) {
            return;
        }

        const initializePayPalButton = async () => {
            try {
                setIsLoading(true);
                setError(null);
                setAddressError(null);

                // Validate shipping address before initializing
                const currentAddress = getShippingAddress();
                const validationResult = validateShippingAddress(currentAddress);

                if (!validationResult.isValid) {
                    console.log('[HiPay PayPal] Address validation failed:', validationResult.errorMessage);
                    setAddressError(validationResult.errorMessage);
                    setIsLoading(false);
                    return;
                }

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
                
                console.log('[HiPay PayPal] Cart totals:', cartTotals);
                
                if (cartTotals?.total_price) {
                    // total_price is in cents as a string, convert to decimal
                    amount = (parseFloat(cartTotals.total_price) / 100).toFixed(2);
                    console.log('[HiPay PayPal] Amount from total_price:', amount);
                } else if (cartTotals?.total_items) {
                    // Fallback to total_items if available (also in cents)
                    amount = (parseFloat(cartTotals.total_items) / 100).toFixed(2);
                    console.log('[HiPay PayPal] Amount from total_items:', amount);
                }

                // Ensure minimum amount
                if (parseFloat(amount) < 0.01) {
                    console.log('[HiPay PayPal] Amount below minimum, setting to 0.01');
                    amount = '0.01';
                }
                
                console.log('[HiPay PayPal] Final amount:', amount, 'Currency:', cartTotals?.currency_code);

                const currency = cartTotals?.currency_code || 'EUR';

                // Get current shipping address for SDK
                const shippingAddress = getShippingAddress();

                const options = {
                    template: 'auto',
                    request: {
                        locale: config.locale || 'en_US',
                        currency: currency,
                        amount: String(amount),
                        // Add customerShippingInformation for PayPal v2
                        customerShippingInformation: {
                            zipCode: shippingAddress.zipCode,
                            city: shippingAddress.city,
                            country: shippingAddress.country,
                            streetaddress: shippingAddress.streetaddress,
                            streetaddress2: shippingAddress.streetaddress2 || '',
                            firstname: shippingAddress.firstname || '',
                            lastname: shippingAddress.lastname || ''
                        }
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
    }, [cartTotals?.total_price, addressError]);

    return (
        <div className="hipay-paypal-v2-container">
            {isLoading && !addressError && (
                <div className="hipay-paypal-loading">
                    {__('Loading PayPal...', 'hipayenterprise')}
                </div>
            )}

            {isProcessing && (
                <div className="hipay-paypal-processing">
                    {__('Processing your PayPal payment...', 'hipayenterprise')}
                </div>
            )}

            {addressError && (
                <div className="hipay-error hipay-address-error">
                    {addressError}
                </div>
            )}

            {error && !addressError && (
                <div className="hipay-error">
                    {error}
                </div>
            )}

            <div
                id={containerIdRef.current}
                className="hipay-paypal-button-container"
                style={{ minHeight: '50px', display: (isProcessing || addressError) ? 'none' : 'block' }}
            ></div>

            {!isReady && !error && !addressError && !isLoading && !isProcessing && (
                <div className="hipay-paypal-info">
                    {__('Click the PayPal button above to complete your payment', 'hipayenterprise')}
                </div>
            )}
        </div>
    );
};

export default PayPalButton;
