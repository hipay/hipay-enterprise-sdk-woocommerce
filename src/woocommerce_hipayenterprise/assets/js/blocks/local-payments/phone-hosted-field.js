import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
    getOrCreateHiPayClient,
    getPhoneHostedFieldBlockReason,
    resolveHiPaySdkErrorMessage,
} from './phone-eligibility';

/**
 * PhoneHostedField — renders the HiPay Hosted Fields phone widget inside the blocks checkout
 */
const PhoneHostedField = ({
    config,
    paymentProduct,
    orderAmount,
    billingCountry,
    instanceRef,
    onEmptyChange,
}) => {
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const containerIdRef = useRef(
        'hipayHF-container-' + paymentProduct + '-blocks'
    );

    const destroyInstance = () => {
        if (instanceRef.current && typeof instanceRef.current.destroy === 'function') {
            try {
                instanceRef.current.destroy();
            } catch (e) {
                // ignore
            }
        }
        instanceRef.current = null;
    };

    useEffect(() => {
        const blockReason = getPhoneHostedFieldBlockReason(paymentProduct, {
            orderAmount,
            billingCountry,
        });

        if (blockReason) {
            destroyInstance();
            setIsLoading(false);
            setError(blockReason);
            return undefined;
        }

        setIsLoading(true);
        setError(null);

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
            const script = document.createElement('script');
            script.src = config.hostedFieldsUrl || 'https://libs.hipay.com/js/sdkjs.js';
            script.async = true;
            script.onload = createInstance;
            script.onerror = () => {
                setError(__('Failed to load HiPay SDK', 'hipayenterprise'));
                setIsLoading(false);
            };
            document.body.appendChild(script);
        };

        const createInstance = () => {
            try {
                destroyInstance();

                const hipaySDK = getOrCreateHiPayClient(config);

                const createOptions = {
                    template: 'auto',
                    selector: containerIdRef.current,
                    styles: {
                        base: {
                            fontFamily: config.fontFamily,
                            color: config.color,
                            fontSize: config.fontSize,
                            fontWeight: config.fontWeight,
                            placeholderColor: config.placeholderColor,
                            caretColor: config.caretColor,
                            iconColor: config.iconColor,
                        },
                    },
                };

                if (typeof orderAmount === 'number' && !Number.isNaN(orderAmount)) {
                    createOptions.request = { amount: String(orderAmount) };
                }

                const instance = hipaySDK.create(paymentProduct, createOptions);

                if (!instance) {
                    throw new Error('Failed to create HiPay instance');
                }

                instance.on('ready', () => setIsLoading(false));
                if (typeof onEmptyChange === 'function') {
                    instance.on('inputChange', (data) => {
                        onEmptyChange(Boolean(data && data.validity && data.validity.empty));
                    });
                }
                instance.on('error', (err) => {
                    setError(
                        resolveHiPaySdkErrorMessage(
                            err,
                            __('Phone field error', 'hipayenterprise')
                        )
                    );
                    setIsLoading(false);
                });

                instanceRef.current = instance;
            } catch (err) {
                setError(
                    resolveHiPaySdkErrorMessage(
                        err,
                        __('Failed to initialize phone field', 'hipayenterprise')
                    )
                );
                setIsLoading(false);
            }
        };

        waitForContainer();

        return () => {
            destroyInstance();
        };
    }, [paymentProduct, orderAmount, billingCountry]);

    return (
        <div className="hipay-phone-hosted-field">
            {isLoading && !error && (
                <div className="hipay-widget-loading">
                    {__('Loading phone field…', 'hipayenterprise')}
                </div>
            )}
            {error && (
                <div className="hipay-error">{error}</div>
            )}
            {!error && (
                <div
                    id={containerIdRef.current}
                    className="hipay-container-hosted-fields"
                    style={{ minHeight: '50px' }}
                />
            )}
        </div>
    );
};

export default PhoneHostedField;
