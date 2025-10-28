import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { usePaymentMethodRefresh } from '../utils/payment-method-refresh';

const CreditCardComponent = ({
    eventRegistration,
    emitResponse,
    settings,
    billing
}) => {
    // Monitor country changes and refresh payment methods
    usePaymentMethodRefresh();
    const { onPaymentSetup, onCheckoutValidation } = eventRegistration;
    const [hostedFieldsInstance, setHostedFieldsInstance] = useState(null);
    const [isHostedFieldsReady, setIsHostedFieldsReady] = useState(false);
    const [selectedCard, setSelectedCard] = useState('new');
    const [saveCard, setSaveCard] = useState(false);
    const [errors, setErrors] = useState({});
    const isInitializingRef = useRef(false);

    const config = settings.config || {};
    const isHostedFields = config.operating_mode === 'hosted_fields';
    const isHostedPage = config.operating_mode === 'hosted_page';

    // Get billing info for cardHolder field
    const firstName = billing?.billingAddress?.first_name || '';
    const lastName = billing?.billingAddress?.last_name || '';

    // Initialize or re-initialize when billing name changes
    useEffect(() => {
        // Only initialize on actual checkout, not in editor
        const isEditor = document.body.classList.contains('block-editor-page');
        if (!isHostedFields || isEditor || isInitializingRef.current) {
            return;
        }

        isInitializingRef.current = true;

        // If instance exists, destroy it first
        if (hostedFieldsInstance) {
            try {
                if (typeof hostedFieldsInstance.destroy === 'function') {
                    hostedFieldsInstance.destroy();
                }
            } catch (e) {
                console.warn('HiPay: Error destroying hosted fields instance', e);
            }
            setHostedFieldsInstance(null);
            setIsHostedFieldsReady(false);
        }

        // Clear all field containers like shortcode does
        const selectors = ['cardHolder', 'cardNumber', 'expiryDate', 'cvc'];
        selectors.forEach(field => {
            const el = document.getElementById(`hipay-card-field-${field}`);
            if (el) {
                el.innerHTML = '';
            }
        });

        // Small delay to ensure cleanup is complete
        setTimeout(() => {
            initializeHostedFields();
        }, 100);

        // Cleanup on unmount only
        return () => {
            if (hostedFieldsInstance) {
                try {
                    if (typeof hostedFieldsInstance.destroy === 'function') {
                        hostedFieldsInstance.destroy();
                    }
                } catch (e) {
                    console.warn('HiPay: Error destroying hosted fields instance', e);
                }
            }
        };
    }, [isHostedFields, firstName, lastName]);

    const initializeHostedFields = () => {
        // Validate credentials before attempting to load
        const credentials = config.sandbox_mode
            ? {
                  username: config.api_tokenjs_username_test,
                  password: config.api_tokenjs_password_publickey_test,
              }
            : {
                  username: config.api_tokenjs_username_production,
                  password: config.api_tokenjs_password_publickey_production,
              };

        // Check if credentials are available
        if (!credentials.username || !credentials.password) {
            console.warn('HiPay: Credentials not configured. Please configure HiPay settings.');
            setErrors({ general: __('Payment method not configured', 'hipayenterprise') });
            return;
        }

        // Load HiPay SDK script if not already loaded
        if (!window.HiPay) {
            const script = document.createElement('script');
            script.src = config.hostedFieldsUrl;
            script.async = true;
            script.onload = () => setupHostedFields();
            script.onerror = () => {
                console.error('HiPay: Failed to load SDK');
                setErrors({ general: __('Failed to load payment form', 'hipayenterprise') });
            };
            document.body.appendChild(script);
        } else {
            setupHostedFields();
        }
    };

    const setupHostedFields = () => {
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

            // Double-check credentials
            if (!credentials.username || !credentials.password) {
                throw new Error('HIPAY_MISSING_CREDENTIALS');
            }

            // Wait for DOM elements to be available
            const checkAndSetup = () => {
                const cardHolderEl = document.getElementById('hipay-card-field-cardHolder');
                if (!cardHolderEl) {
                    // DOM not ready yet, try again
                    setTimeout(checkAndSetup, 100);
                    return;
                }

                const instance = window.HiPay({
                    username: credentials.username,
                    password: credentials.password,
                    environment: config.sandbox_mode ? 'stage' : 'production',
                    lang: document.documentElement.lang || 'en',
                });

                // Inject base stylesheet for consistent styling
                instance.injectBaseStylesheet();

                const options = {
                    fields: {
                        cardHolder: {
                            selector: 'hipay-card-field-cardHolder',
                            placeholder: config.i18n?.card_holder || 'Card Holder',
                            defaultFirstname: firstName,
                            defaultLastname: lastName,
                        },
                        cardNumber: {
                            selector: 'hipay-card-field-cardNumber',
                            placeholder: config.i18n?.card_number || 'Card Number',
                        },
                        expiryDate: {
                            selector: 'hipay-card-field-expiryDate',
                            placeholder: config.i18n?.expiry_date || 'MM/YY',
                        },
                        cvc: {
                            selector: 'hipay-card-field-cvc',
                            placeholder: config.i18n?.cvv || 'CVV',
                        },
                    },
                    styles: {
                        base: {
                            fontFamily: config.fontFamily || 'Roboto, sans-serif',
                            color: config.color || '#000000',
                            fontSize: config.fontSize || '15px',
                            fontWeight: config.fontWeight || '400',
                            placeholderColor: config.placeholderColor || '#999999',
                            caretColor: config.caretColor || '#000000',
                            iconColor: config.iconColor || '#00ADE9',
                        },
                    },
                };

                // Create returns the card instance directly (synchronously)
                const cardInstance = instance.create('card', options);

                if (cardInstance) {
                    // Set up event handler for when fields are ready
                    cardInstance.on('ready', () => {
                        setIsHostedFieldsReady(true);
                        isInitializingRef.current = false;

                        // Force fields to be visible after ready event
                        setTimeout(() => {
                            const fields = ['cardHolder', 'cardNumber', 'expiryDate', 'cvc'];
                            fields.forEach(field => {
                                const el = document.getElementById(`hipay-card-field-${field}`);
                                if (el) {
                                    el.style.opacity = '1';
                                    el.style.visibility = 'visible';
                                    el.style.display = 'block';
                                    // Also target child elements (inputs and iframes)
                                    const children = el.querySelectorAll('*');
                                    children.forEach(child => {
                                        child.style.opacity = '1';
                                        child.style.visibility = 'visible';
                                        child.style.display = 'block';
                                    });
                                }
                            });
                        }, 100);
                    });

                    cardInstance.on('error', (error) => {
                        console.error('HiPay: Card instance error', error);
                        setErrors({ general: __('Payment form error', 'hipayenterprise') });
                        isInitializingRef.current = false;
                    });

                    setHostedFieldsInstance(cardInstance);
                } else {
                    console.error('HiPay: Failed to create card instance');
                    setErrors({ general: __('Failed to initialize payment form', 'hipayenterprise') });
                    isInitializingRef.current = false;
                }
            };

            checkAndSetup();
        } catch (error) {
            console.error('HiPay Hosted Fields initialization error:', error);
            setErrors({ general: __('Failed to initialize payment form', 'hipayenterprise') });
            isInitializingRef.current = false;
        }
    };

    // Handle payment processing
    useEffect(() => {
        const unsubscribe = onPaymentSetup(async () => {
            try {
                if (selectedCard !== 'new' && config.savedCards?.length) {
                    // Use saved card
                    return {
                        type: emitResponse.responseTypes.SUCCESS,
                        meta: {
                            paymentMethodData: {
                                hipay_token: selectedCard,
                                hipay_use_saved_card: true,
                            },
                        },
                    };
                }

                if (isHostedPage) {
                    // Hosted page mode - just proceed, redirect will happen server-side
                    return {
                        type: emitResponse.responseTypes.SUCCESS,
                        meta: {
                            paymentMethodData: {
                                hipay_operating_mode: 'hosted_page',
                            },
                        },
                    };
                }

                if (isHostedFields && hostedFieldsInstance) {
                    // Verify the instance has getPaymentData method
                    if (typeof hostedFieldsInstance.getPaymentData !== 'function') {
                        console.error('HiPay: Card instance not ready or invalid');
                        return {
                            type: emitResponse.responseTypes.ERROR,
                            message: __('Payment form not ready. Please try again.', 'hipayenterprise'),
                        };
                    }

                    // Get token from hosted fields
                    const tokenResponse = await hostedFieldsInstance.getPaymentData();

                    if (tokenResponse && tokenResponse.token) {
                        // Serialize browser_info if it's an object
                        const browserInfo = tokenResponse.browser_info
                            ? (typeof tokenResponse.browser_info === 'string'
                                ? tokenResponse.browser_info
                                : JSON.stringify(tokenResponse.browser_info))
                            : '{}';

                        // Build payment data object - send ALL fields from tokenResponse
                        // This matches the classic checkout behavior
                        const paymentData = {
                            hipay_token: tokenResponse.token,
                            hipay_payment_product: tokenResponse.payment_product || '',
                            hipay_brand: tokenResponse.brand || '',
                            hipay_pan: tokenResponse.pan || '',
                            hipay_card_expiry_month: tokenResponse.card_expiry_month || '',
                            hipay_card_expiry_year: tokenResponse.card_expiry_year || '',
                            hipay_card_holder: tokenResponse.card_holder || '',
                            hipay_device_fingerprint: tokenResponse.device_fingerprint || '',
                            hipay_browser_info: browserInfo,
                            hipay_save_card: saveCard ? 'true' : 'false',
                        };

                        return {
                            type: emitResponse.responseTypes.SUCCESS,
                            meta: {
                                paymentMethodData: paymentData,
                            },
                        };
                    } else {
                        throw new Error(__('Failed to tokenize card', 'hipayenterprise'));
                    }
                }

                return {
                    type: emitResponse.responseTypes.ERROR,
                    message: __('Payment initialization failed', 'hipayenterprise'),
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
    }, [
        onPaymentSetup,
        selectedCard,
        hostedFieldsInstance,
        saveCard,
        isHostedFields,
        isHostedPage,
    ]);

    // Render saved cards
    if (config.savedCards && config.savedCards.length > 0 && config.canSaveCards) {
        return (
            <div className="hipay-credit-card-block">
                {settings.description && (
                    <p className="hipay-description">{settings.description}</p>
                )}

                <div className="hipay-saved-cards">
                    <label>
                        <input
                            type="radio"
                            name="hipay-card-choice"
                            value="new"
                            checked={selectedCard === 'new'}
                            onChange={(e) => setSelectedCard(e.target.value)}
                        />
                        {config.i18n?.use_new_card || __('Use a new card', 'hipayenterprise')}
                    </label>

                    {config.savedCards.map((card) => (
                        <label key={card.token} className="hipay-saved-card-option">
                            <input
                                type="radio"
                                name="hipay-card-choice"
                                value={card.token}
                                checked={selectedCard === card.token}
                                onChange={(e) => setSelectedCard(e.target.value)}
                            />
                            <span className={`hipay-card-brand hipay-card-${card.brand}`}></span>
                            {card.pan} - {card.card_holder}
                        </label>
                    ))}
                </div>

                {selectedCard === 'new' && isHostedFields && (
                    <div className="hipay-hosted-fields">
                        {renderHostedFields()}
                    </div>
                )}

                {errors.general && (
                    <div className="hipay-error">{errors.general}</div>
                )}
            </div>
        );
    }

    // Render regular payment form
    return (
        <div className="hipay-credit-card-block">
            {settings.description && (
                <p className="hipay-description">{settings.description}</p>
            )}

            {isHostedPage && (
                <div className="hipay-hosted-page-notice">
                    <p>{__('You will be redirected to complete your payment', 'hipayenterprise')}</p>
                </div>
            )}

            {isHostedFields && renderHostedFields()}

            {config.canSaveCards && selectedCard === 'new' && (
                <div className="hipay-save-card">
                    <label>
                        <input
                            type="checkbox"
                            checked={saveCard}
                            onChange={(e) => setSaveCard(e.target.checked)}
                        />
                        {config.i18n?.save_card || __('Save card for future payments', 'hipayenterprise')}
                    </label>
                </div>
            )}

            {errors.general && (
                <div className="hipay-error">{errors.general}</div>
            )}
        </div>
    );

    function renderHostedFields() {
        return (
            <div id="hipayHF-card-form-container">
                <div className="hipay-form-row">
                    <div className="hipay-field-container">
                        <div className="hipay-field" id="hipay-card-field-cardHolder"></div>
                    </div>
                </div>
                <div className="hipay-form-row">
                    <div className="hipay-field-container">
                        <div className="hipay-field" id="hipay-card-field-cardNumber"></div>
                    </div>
                </div>
                <div className="hipay-form-row">
                    <div className="hipay-field-container hipay-field-container-half">
                        <div className="hipay-field" id="hipay-card-field-expiryDate"></div>
                    </div>
                    <div className="hipay-field-container hipay-field-container-half">
                        <div className="hipay-field" id="hipay-card-field-cvc"></div>
                    </div>
                </div>
            </div>
        );
    }
};

export default CreditCardComponent;
