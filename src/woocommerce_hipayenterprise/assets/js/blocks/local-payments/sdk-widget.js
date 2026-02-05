import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * HiPay SDK Widget Component
 * For local payment methods that need HiPay SDK to render their UI
 * (e.g., Multibanco, Sisal, etc.)
 */
const SDKWidget = ({ config, paymentProduct, cartTotals }) => {
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const widgetInstanceRef = useRef(null);
    const containerIdRef = useRef('hipay-widget-' + paymentProduct + '-' + Math.random().toString(36).substr(2, 9));
    const initializedRef = useRef(false);

    // Clean up on unmount
    useEffect(() => {
        return () => {
            if (widgetInstanceRef.current && typeof widgetInstanceRef.current.destroy === 'function') {
                try {
                    widgetInstanceRef.current.destroy();
                    widgetInstanceRef.current = null;
                } catch (e) {
                    console.warn('[HiPay Widget] Error destroying widget instance:', e);
                }
            }
            initializedRef.current = false;
        };
    }, []);

    // Initialize SDK widget
    useEffect(() => {
        // Don't initialize if already done, or if cart totals aren't available yet
        if (initializedRef.current || !cartTotals || !cartTotals.total_price) {
            return;
        }

        const initializeWidget = async () => {
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
                    setupWidget();
                };

                const setupWidget = () => {
                    // Load HiPay SDK if not already loaded
                    if (!window.HiPay) {
                        const script = document.createElement('script');
                        script.src = config.hostedFieldsUrl || 'https://libs.hipay.com/js/sdkjs.js';
                        script.async = true;
                        script.onload = () => createWidget();
                        script.onerror = () => {
                            setError(__('Failed to load HiPay SDK', 'hipayenterprise'));
                            setIsLoading(false);
                        };
                        document.body.appendChild(script);
                    } else {
                        createWidget();
                    }
                };

                checkContainer();
            } catch (err) {
                console.error('[HiPay Widget] Initialization error:', err);
                setError(err.message || __('Failed to initialize payment widget', 'hipayenterprise'));
                setIsLoading(false);
            }
        };

        const createWidget = () => {
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
                    throw new Error('HiPay credentials not configured');
                }

                const hipaySDK = new window.HiPay({
                    username: credentials.username,
                    password: credentials.password,
                    environment: config.sandbox_mode ? 'stage' : 'production',
                    lang: config.lang || 'en',
                });

                // Get amount from cart totals
                let amount = 0;
                if (cartTotals?.total_price) {
                    // total_price is in cents, convert to decimal
                    amount = (parseFloat(cartTotals.total_price) / 100).toFixed(2);
                } else if (cartTotals?.total_items) {
                    amount = (parseFloat(cartTotals.total_items) / 100).toFixed(2);
                }

                // Ensure minimum amount
                if (parseFloat(amount) < 0.01) {
                    amount = '0.01';
                }

                console.log('[HiPay Widget] Creating widget for:', paymentProduct, 'Amount:', amount);

                const options = {
                    template: 'auto',
                    request: {
                        amount: String(amount),
                    },
                    selector: containerIdRef.current,
                };

                // Add styles if available
                if (config.styles) {
                    options.styles = config.styles;
                }

                const widgetInstance = hipaySDK.create(paymentProduct, options);

                if (!widgetInstance) {
                    throw new Error('Failed to create widget instance');
                }

                widgetInstance.on('ready', () => {
                    setIsLoading(false);
                    console.log('[HiPay Widget] Widget ready for:', paymentProduct);
                });

                widgetInstance.on('error', (err) => {
                    console.error('[HiPay Widget] Widget error:', err);
                    setError(err.message || __('Widget error occurred', 'hipayenterprise'));
                    setIsLoading(false);
                });

                widgetInstanceRef.current = widgetInstance;
                initializedRef.current = true;
            } catch (err) {
                console.error('[HiPay Widget] Widget creation error:', err);
                setError(err.message || __('Failed to create payment widget', 'hipayenterprise'));
                setIsLoading(false);
            }
        };

        initializeWidget();
    }, [cartTotals?.total_price, paymentProduct]);

    return (
        <div className="hipay-sdk-widget-container">
            {isLoading && (
                <div className="hipay-widget-loading">
                    {__('Loading payment method...', 'hipayenterprise')}
                </div>
            )}

            {error && (
                <div className="hipay-error">
                    {error}
                </div>
            )}

            <div
                id={containerIdRef.current}
                className="hipay-widget-field-container"
                style={{ minHeight: '50px' }}
            ></div>
        </div>
    );
};

export default SDKWidget;

