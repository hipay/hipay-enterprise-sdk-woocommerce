import { useState, useEffect, useRef, useMemo } from '@wordpress/element';
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
    const [errors, setErrors] = useState({});
    const isInitializingRef = useRef(false);
    const initTimeoutRef = useRef(null);
    const effectIdRef = useRef(0);

    const config = settings.config || {};
    const isHostedFields = config.operating_mode === 'hosted_fields';
    const isHostedPage = config.operating_mode === 'hosted_page';
    const savedCards = config.canSaveCards && Array.isArray(config.savedCards) ? config.savedCards : [];
    const useOneClick = !!config.canSaveCards;
    const hasSavedCards = savedCards.length > 0;

    // Get billing info for cardHolder field
    const firstName = billing?.billingAddress?.first_name || '';
    const lastName = billing?.billingAddress?.last_name || '';

    const [debouncedName, setDebouncedName] = useState({ firstName, lastName });
    useEffect(() => {
        const t = setTimeout(() => {
            setDebouncedName({ firstName, lastName });
        }, 600);
        return () => clearTimeout(t);
    }, [firstName, lastName]);

    const nameRef = useRef(debouncedName);
    nameRef.current = debouncedName;
    const nameKey = debouncedName.firstName + '|' + debouncedName.lastName;

    const cartTotal = (billing?.cartTotal?.value || 0) / 100;

    const allowedBrands = useMemo(() => {
        const restrictions = config.creditCardRestrictions || {};
        if (Object.keys(restrictions).length === 0) {
            return null;
        }
        return Object.entries(restrictions)
            .filter(([, r]) => {
                if (r.minAmount && cartTotal < r.minAmount) return false;
                if (r.maxAmount && cartTotal > r.maxAmount) return false;
                return true;
            })
            .map(([brand]) => brand);
    }, [cartTotal, config.creditCardRestrictions]);

    const savedCardsListenersRef = useRef(null);
    const allowedBrandsRef = useRef(allowedBrands);
    allowedBrandsRef.current = allowedBrands;
    const allowedBrandsKey = allowedBrands ? allowedBrands.slice().sort().join(',') : '';

    // Initialize or re-initialize when billing name changes
    useEffect(() => {
        // Only initialize on actual checkout, not in editor
        const isEditor = document.body.classList.contains('block-editor-page');
        if (!isHostedFields || isEditor || isInitializingRef.current) {
            return;
        }

        isInitializingRef.current = true;
        const effectId = ++effectIdRef.current;

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
        if (savedCardsListenersRef.current) {
            savedCardsListenersRef.current.abort();
            savedCardsListenersRef.current = null;
        }
        const selectors = ['cardHolder', 'cardNumber', 'expiryDate', 'cvc'];
        selectors.forEach(field => {
            const el = document.getElementById(`hipay-card-field-${field}`);
            if (el) {
                el.innerHTML = '';
            }
        });
        ['hipay-card-saved-cards', 'hipay-saved-card-btn'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.innerHTML = '';
            }
        });

        // Small delay to ensure cleanup is complete
        clearTimeout(initTimeoutRef.current);
        initTimeoutRef.current = setTimeout(() => {
            initTimeoutRef.current = null;
            if (effectId !== effectIdRef.current) return;
            initializeHostedFields(effectId);
        }, 100);

        return () => {
            clearTimeout(initTimeoutRef.current);
            initTimeoutRef.current = null;
            isInitializingRef.current = false;
            if (savedCardsListenersRef.current) {
                savedCardsListenersRef.current.abort();
                savedCardsListenersRef.current = null;
            }
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
    }, [isHostedFields, allowedBrandsKey, nameKey]);

    const initializeHostedFields = (effectId) => {
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
            script.onload = () => {
                if (effectId !== effectIdRef.current) return;
                setupHostedFields(effectId);
            };
            script.onerror = () => {
                console.error('HiPay: Failed to load SDK');
                setErrors({ general: __('Failed to load payment form', 'hipayenterprise') });
            };
            document.body.appendChild(script);
        } else {
            setupHostedFields(effectId);
        }
    };

    const setupHostedFields = (effectId) => {
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
                if (effectId !== effectIdRef.current) return;

                const cardHolderEl = document.getElementById('hipay-card-field-cardHolder');
                if (!cardHolderEl) {
                    // DOM not ready yet, try again
                    setTimeout(checkAndSetup, 100);
                    return;
                }

                if (cardHolderEl.innerHTML.trim() !== '') {
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
                    ...(allowedBrandsRef.current?.length > 0 && {
                        brand: allowedBrandsRef.current,
                    }),
                    ...(useOneClick && {
                        one_click: {
                            enabled: true,
                            ...(config.savedCardsDisplayCount > 0 && {
                                cards_display_count: Number(config.savedCardsDisplayCount),
                            }),
                            cards: savedCards.filter(
                                (card) => !allowedBrandsRef.current?.length || allowedBrandsRef.current.includes(card.brand)
                            ),
                        },
                    }),
                    fields: {
                        ...(useOneClick && {
                            savedCards: { selector: 'hipay-card-saved-cards' },
                            savedCardButton: { selector: 'hipay-saved-card-btn' },
                        }),
                        cardHolder: {
                            selector: 'hipay-card-field-cardHolder',
                            placeholder: config.i18n?.card_holder || 'Card Holder',
                            defaultFirstname: nameRef.current.firstName,
                            defaultLastname: nameRef.current.lastName,
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
                            helpButton: true,
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

                if (effectId !== effectIdRef.current) return;

                // Create returns the card instance directly (synchronously)
                const cardInstance = instance.create('card', options);

                if (cardInstance) {
                    // Set up event handler for when fields are ready
                    cardInstance.on('ready', () => {
                        if (hasSavedCards) {
                            const cardForm = document.getElementById('hipayHF-card-form-container');
                            const payOtherCardButton = document.getElementById('pay-other-card');
                            if (cardForm) {
                                savedCardsListenersRef.current?.abort();
                                const controller = new AbortController();
                                savedCardsListenersRef.current = controller;
                                const { signal } = controller;
                                cardForm.classList.add('hidden');
                                payOtherCardButton?.addEventListener('click', () => {
                                    cardForm.classList.toggle('hidden');
                                }, { signal });
                                Array.from(document.getElementsByClassName('saved-card')).forEach((el) => {
                                    el.addEventListener('click', () => cardForm.classList.add('hidden'), { signal });
                                });
                            }
                        }
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

                    cardInstance.on('helpButtonToggled', (data) => {
                        const el = document.querySelector(`[data-hipay-id='hipay-help-${data.element}']`);
                        if (!el) return;
                        el.classList.toggle('hipay-visible');
                        el.innerHTML = el.innerHTML.trim() ? '' : data.message;
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
                            ...(tokenResponse.multi_use && { hipay_multi_use: '1' }),
                            ...(tokenResponse.one_click && { hipay_one_click: '1' }),
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
        hostedFieldsInstance,
        isHostedFields,
        isHostedPage,
    ]);

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

            {errors.general && (
                <div className="hipay-error">{errors.general}</div>
            )}
        </div>
    );

    function renderHostedFields() {
        return (
            <div id="hipayHF-container-card">
                {useOneClick && (
                    <div className="one-click hipay-form-row">
                        <div className="hipay-field-container">
                            <div className="hipay-saved-cards-field" id="hipay-card-saved-cards"></div>
                        </div>
                    </div>
                )}
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
                <div className="hipay-form-row">
                    <div data-hipay-id="hipay-help-cvc"></div>
                </div>
                {useOneClick && (
                    <div className="hipay-form-row">
                        <div className="hipay-field-container hipay-saved-card">
                            <div className="hipay-saved-cards-field" id="hipay-saved-card-btn"></div>
                        </div>
                    </div>
                )}
            </div>
            </div>
        );
    }
};

export default CreditCardComponent;
