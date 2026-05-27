import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * PhoneHostedField — renders the HiPay Hosted Fields phone widget inside the blocks checkout
 */
const PhoneHostedField = ({ config, paymentProduct, instanceRef }) => {
    const [isLoading, setIsLoading]   = useState(true);
    const [error, setError]           = useState(null);
    const containerIdRef              = useRef(
        'hipayHF-container-' + paymentProduct + '-blocks'
    );
    const initializedRef              = useRef(false);

    // Destroy on unmount
    useEffect(() => {
        return () => {
            if (instanceRef.current && typeof instanceRef.current.destroy === 'function') {
                try {
                    instanceRef.current.destroy();
                } catch (e) {
                    // ignore
                }
            }
            instanceRef.current   = null;
            initializedRef.current = false;
        };
    }, []);

    useEffect(() => {
        if (initializedRef.current) return;

        const waitForContainer = () => {
            const el = document.getElementById(containerIdRef.current);
            if (!el) {
                setTimeout(waitForContainer, 100);
                return;
            }
            loadAndCreate();
        };

        const loadAndCreate = () => {
            if (window.HiPay) {
                createInstance();
                return;
            }
            const script    = document.createElement('script');
            script.src      = config.hostedFieldsUrl || 'https://libs.hipay.com/js/sdkjs.js';
            script.async    = true;
            script.onload   = createInstance;
            script.onerror  = () => {
                setError(__('Failed to load HiPay SDK', 'hipayenterprise'));
                setIsLoading(false);
            };
            document.body.appendChild(script);
        };

        const createInstance = () => {
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

                const hipaySDK = new window.HiPay({
                    username   : credentials.username,
                    password   : credentials.password,
                    environment: config.sandbox_mode ? 'stage' : 'production',
                    lang       : config.lang || 'en',
                });

                const instance = hipaySDK.create(paymentProduct, {
                    template: 'auto',
                    selector: containerIdRef.current,
                    styles  : {
                        base: {
                            fontFamily      : config.fontFamily,
                            color           : config.color,
                            fontSize        : config.fontSize,
                            fontWeight      : config.fontWeight,
                            placeholderColor: config.placeholderColor,
                            caretColor      : config.caretColor,
                            iconColor       : config.iconColor,
                        },
                    },
                });

                if (!instance) {
                    throw new Error('Failed to create HiPay instance');
                }

                instance.on('ready', () => setIsLoading(false));
                instance.on('error', (err) => {
                    setError(err.message || __('Phone field error', 'hipayenterprise'));
                    setIsLoading(false);
                });

                instanceRef.current    = instance;
                initializedRef.current = true;
            } catch (err) {
                setError(err.message || __('Failed to initialize phone field', 'hipayenterprise'));
                setIsLoading(false);
            }
        };

        waitForContainer();
    }, [paymentProduct]);

    return (
        <div className="hipay-phone-hosted-field">
            {isLoading && (
                <div className="hipay-widget-loading">
                    {__('Loading phone field…', 'hipayenterprise')}
                </div>
            )}
            {error && (
                <div className="hipay-error">{error}</div>
            )}
            <div
                id={containerIdRef.current}
                className="hipay-container-hosted-fields"
                style={{ minHeight: '50px' }}
            />
        </div>
    );
};

export default PhoneHostedField;
