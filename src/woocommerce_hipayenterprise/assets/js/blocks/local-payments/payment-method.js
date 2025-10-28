import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { usePaymentMethodRefresh } from '../utils/payment-method-refresh';
import PayPalButton from './paypal-button';

const LocalPaymentComponent = ({
    eventRegistration,
    emitResponse,
    settings,
    billing,
    shippingData,
    cartData
}) => {
    // Monitor country changes and refresh payment methods
    usePaymentMethodRefresh();
    const { onPaymentSetup } = eventRegistration;
    const [formData, setFormData] = useState({});
    const [errors, setErrors] = useState({});
    const [paypalPaymentData, setPaypalPaymentData] = useState(null);

    const config = settings.config || {};
    const additionalFields = config.additionalFields || {};

    // Handle payment processing
    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            try {
                // For PayPal v2, check if we have PayPal payment data
                if (config.isPayPalV2) {
                    if (!paypalPaymentData || !paypalPaymentData.paypalOrderId) {
                        return {
                            type: emitResponse.responseTypes.ERROR,
                            message: __('Please complete PayPal authorization', 'hipayenterprise'),
                        };
                    }

                    // Return PayPal payment data
                    return {
                        type: emitResponse.responseTypes.SUCCESS,
                        meta: {
                            paymentMethodData: paypalPaymentData,
                        },
                    };
                }

                // For regular local payments, validate form fields
                const validationErrors = validateFields();
                if (Object.keys(validationErrors).length > 0) {
                    setErrors(validationErrors);
                    return {
                        type: emitResponse.responseTypes.ERROR,
                        message: __('Please fill in all required fields', 'hipayenterprise'),
                    };
                }

                // Prepare payment data
                const paymentMethodData = {
                    hipay_payment_product: config.paymentProduct,
                    ...formData,
                };

                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData,
                    },
                };
            } catch (error) {
                console.error('Payment setup error:', error);
                return {
                    type: emitResponse.responseTypes.ERROR,
                    message: error.message || __('Payment failed', 'hipayenterprise'),
                };
            }
        });

        return unsubscribe;
    }, [onPaymentSetup, formData, config.paymentProduct, config.isPayPalV2, paypalPaymentData]);

    const validateFields = () => {
        const validationErrors = {};

        if (additionalFields.formFields) {
            Object.entries(additionalFields.formFields).forEach(([fieldName, fieldConfig]) => {
                if (fieldConfig.required && !formData[fieldName]) {
                    validationErrors[fieldName] = __('This field is required', 'hipayenterprise');
                }
            });
        }

        return validationErrors;
    };

    const handleFieldChange = (fieldName, value) => {
        setFormData(prev => ({
            ...prev,
            [fieldName]: value
        }));

        // Clear error for this field
        if (errors[fieldName]) {
            setErrors(prev => {
                const newErrors = { ...prev };
                delete newErrors[fieldName];
                return newErrors;
            });
        }
    };

    const renderField = (fieldName, fieldConfig) => {
        const fieldValue = formData[fieldName] || '';

        switch (fieldConfig.type) {
            case 'text':
            case 'email':
            case 'tel':
                return (
                    <div key={fieldName} className="hipay-field">
                        <label htmlFor={`hipay-${fieldName}`}>
                            {fieldConfig.label}
                            {fieldConfig.required && <span className="required">*</span>}
                        </label>
                        <input
                            type={fieldConfig.type}
                            id={`hipay-${fieldName}`}
                            value={fieldValue}
                            placeholder={fieldConfig.placeholder || ''}
                            onChange={(e) => handleFieldChange(fieldName, e.target.value)}
                            required={fieldConfig.required}
                            className={errors[fieldName] ? 'has-error' : ''}
                        />
                        {errors[fieldName] && (
                            <span className="error-message">{errors[fieldName]}</span>
                        )}
                    </div>
                );

            case 'select':
                return (
                    <div key={fieldName} className="hipay-field">
                        <label htmlFor={`hipay-${fieldName}`}>
                            {fieldConfig.label}
                            {fieldConfig.required && <span className="required">*</span>}
                        </label>
                        <select
                            id={`hipay-${fieldName}`}
                            value={fieldValue}
                            onChange={(e) => handleFieldChange(fieldName, e.target.value)}
                            required={fieldConfig.required}
                            className={errors[fieldName] ? 'has-error' : ''}
                        >
                            <option value="">{__('Select an option', 'hipayenterprise')}</option>
                            {fieldConfig.options && fieldConfig.options.map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>
                        {errors[fieldName] && (
                            <span className="error-message">{errors[fieldName]}</span>
                        )}
                    </div>
                );

            case 'checkbox':
                return (
                    <div key={fieldName} className="hipay-field hipay-checkbox">
                        <label>
                            <input
                                type="checkbox"
                                id={`hipay-${fieldName}`}
                                checked={!!fieldValue}
                                onChange={(e) => handleFieldChange(fieldName, e.target.checked)}
                            />
                            {fieldConfig.label}
                            {fieldConfig.required && <span className="required">*</span>}
                        </label>
                        {errors[fieldName] && (
                            <span className="error-message">{errors[fieldName]}</span>
                        )}
                    </div>
                );

            default:
                return null;
        }
    };

    // Handle PayPal v2
    if (config.isPayPalV2) {
        return (
            <div className="hipay-local-payment-block hipay-paypal-v2">
                {settings.description && (
                    <p className="hipay-description">{settings.description}</p>
                )}

                <PayPalButton
                    config={config}
                    billing={billing}
                    shippingData={shippingData}
                    onPaymentDataChange={setPaypalPaymentData}
                />
            </div>
        );
    }

    return (
        <div className="hipay-local-payment-block">
            {settings.description && (
                <p className="hipay-description">{settings.description}</p>
            )}

            {additionalFields.helpText && (
                <div className="hipay-help-text">
                    <p>{additionalFields.helpText}</p>
                </div>
            )}

            {additionalFields.formFields && (
                <div className="hipay-form-fields">
                    {Object.entries(additionalFields.formFields).map(([fieldName, fieldConfig]) =>
                        renderField(fieldName, fieldConfig)
                    )}
                </div>
            )}

            {Object.keys(errors).length > 0 && (
                <div className="hipay-error">
                    {__('Please correct the errors above', 'hipayenterprise')}
                </div>
            )}
        </div>
    );
};

export default LocalPaymentComponent;
