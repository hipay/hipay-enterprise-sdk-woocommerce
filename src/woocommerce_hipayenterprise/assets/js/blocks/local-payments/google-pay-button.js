import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import { loadSdk, buildRequest, createWallet, getBrowserInfo } from './googlepay-sdk';

const GooglePayButton = ({ config, country, onPaymentDataChange }) => {
    const { cartTotals } = useSelect((select) => {
        const store = select(CART_STORE_KEY);
        return { cartTotals: store.getCartTotals() };
    }, []);

    const [isLoading, setIsLoading] = useState(true);
    const [isProcessing, setIsProcessing] = useState(false);
    const [error, setError] = useState(null);
    const [fieldsValid, setFieldsValid] = useState(false);
    const instanceRef = useRef(null);
    const containerIdRef = useRef('hipay-googlepay-' + Math.random().toString(36).substr(2, 9));
    const onPaymentDataChangeRef = useRef(onPaymentDataChange);

    useEffect(() => {
        onPaymentDataChangeRef.current = onPaymentDataChange;
    }, [onPaymentDataChange]);

    useEffect(() => {
        let checkInterval = null;

        const hideButton = () => {
            const btn = document.querySelector('.wc-block-components-checkout-place-order-button');
            if (btn) {
                if (btn.style.display !== 'none') {
                    btn.dataset.originalDisplay = btn.style.display || '';
                    btn.style.display = 'none';
                }
                return true;
            }
            return false;
        };

        if (!hideButton()) {
            checkInterval = setInterval(() => {
                if (hideButton()) {
                    clearInterval(checkInterval);
                    checkInterval = null;
                }
            }, 100);
            setTimeout(() => { if (checkInterval) clearInterval(checkInterval); }, 5000);
        }

        return () => {
            if (checkInterval) clearInterval(checkInterval);
            const btn = document.querySelector('.wc-block-components-checkout-place-order-button');
            if (btn) btn.style.display = btn.dataset.originalDisplay || '';
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
            ).filter((el) => el.type !== 'hidden' && el.offsetParent !== null);
            setFieldsValid(
                required.every((el) =>
                    el.type === 'checkbox' ? el.checked : (el.value || '').trim().length > 0
                )
            );
        };
        checkFields();
        const interval = setInterval(checkFields, 500);
        return () => clearInterval(interval);
    }, []);

    const totalPrice = cartTotals?.total_price;
    const currencyCode = cartTotals?.currency_code;

    useEffect(() => {
        if (!totalPrice) {
            return undefined;
        }

        let cancelled = false;
        const destroyInstance = () => {
            if (instanceRef.current) {
                try { instanceRef.current.destroy(); } catch (e) { /* nothing to clean up */ }
                instanceRef.current = null;
            }
        };

        setIsLoading(true);
        setError(null);

        const waitForContainer = () => new Promise((resolve) => {
            const check = () => {
                if (cancelled) {
                    resolve(null);
                    return;
                }
                const container = document.getElementById(containerIdRef.current);
                if (container) {
                    container.innerHTML = '';
                    resolve(container);
                } else {
                    setTimeout(check, 100);
                }
            };
            check();
        });

        loadSdk(config.hostedFieldsUrl)
            .then(waitForContainer)
            .then((container) => {
                if (!container || cancelled) {
                    return null;
                }
                return createWallet(
                    config,
                    containerIdRef.current,
                    buildRequest(config, cartTotals, country)
                );
            })
            .then((wallet) => {
                if (!wallet) {
                    return;
                }
                if (cancelled) {
                    try { wallet.destroy(); } catch (e) { /* ignore */ }
                    return;
                }
                instanceRef.current = wallet;

                wallet.on('ready', () => {
                    if (!cancelled) setIsLoading(false);
                });

                wallet.on('unavailable', (event) => {
                    console.info('[HiPay Google Pay] unavailable', event && event.code);
                    if (!cancelled) {
                        setIsLoading(false);
                        setError(__('Google Pay is not available on this device or browser.', 'hipayenterprise'));
                    }
                });

                wallet.on('paymentAuthorized', (hipayToken) => {
                    const token = hipayToken || {};
                    const paymentProduct = token.payment_product
                        ? String(token.payment_product).toLowerCase().replace(/ /g, '-')
                        : '';

                    if (onPaymentDataChangeRef.current) {
                        onPaymentDataChangeRef.current({
                            'googlepay-card-token': token.token || '',
                            'googlepay-card-holder': token.card_holder || '',
                            'googlepay-payment-product': paymentProduct,
                            'googlepay-browser-info': getBrowserInfo(token),
                            'googlepay-device_fingerprint': token.device_fingerprint || '',
                        });
                    }

                    setError(null);
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

                wallet.on('paymentCanceled', () => {
                    if (onPaymentDataChangeRef.current) {
                        onPaymentDataChangeRef.current(null);
                    }
                });

                wallet.on('paymentFailed', (event) => {
                    console.warn('[HiPay Google Pay] paymentFailed', event && event.code, event && event.details);
                    if (onPaymentDataChangeRef.current) {
                        onPaymentDataChangeRef.current(null);
                    }
                    if (!cancelled) {
                        setIsProcessing(false);
                        setError(__('Google Pay payment failed. Please try again or use another payment method.', 'hipayenterprise'));
                    }
                });

                wallet.on('warning', (event) => console.warn('[HiPay Google Pay] warning', event));
                wallet.on('error', (event) => console.error('[HiPay Google Pay] error', event));
            })
            .catch((e) => {
                console.warn('[HiPay Google Pay] initialization failed', e);
                if (!cancelled) {
                    setIsLoading(false);
                    setError(__('Unable to initialize Google Pay.', 'hipayenterprise'));
                }
            });

        return () => {
            cancelled = true;
            destroyInstance();
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [totalPrice, currencyCode, country]);

    const handleValidationOverlayClick = () => {
        const checkout = document.querySelector('.wc-block-checkout');
        if (!checkout) return;

        const required = Array.from(
            checkout.querySelectorAll('[required], [aria-required="true"]')
        ).filter((el) => el.type !== 'hidden' && el.offsetParent !== null);

        const emptyFields = required.filter((el) =>
            el.type === 'checkbox' ? !el.checked : !(el.value || '').trim()
        );

        emptyFields.forEach((el) => {
            el.dispatchEvent(new Event('blur', { bubbles: true }));
        });

        if (emptyFields[0]) {
            emptyFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            emptyFields[0].focus();
        }
    };

    return (
        <div className="hipay-googlepay-blocks-container">
            {isLoading && (
                <div className="hipay-googlepay-loading">
                    {__('Loading Google Pay...', 'hipayenterprise')}
                </div>
            )}

            {isProcessing && (
                <div className="hipay-googlepay-processing">
                    {__('Processing your Google Pay payment...', 'hipayenterprise')}
                </div>
            )}

            {error && (
                <div className="hipay-error">
                    {error}
                </div>
            )}

            <div style={{ position: 'relative' }}>
                {/* The SDK needs an empty container with an explicit height (minimum 240 x 40 px) */}
                <div
                    id={containerIdRef.current}
                    className="hipay-googlepay-button-container"
                    style={{
                        width: '100%',
                        minWidth: '240px',
                        height: '48px',
                        display: isProcessing ? 'none' : 'block',
                    }}
                ></div>

                {!fieldsValid && !isLoading && !isProcessing && (
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

export default GooglePayButton;
