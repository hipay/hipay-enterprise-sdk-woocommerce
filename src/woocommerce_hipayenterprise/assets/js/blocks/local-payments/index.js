import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';
import LocalPaymentComponent from './payment-method';
import { BIZUM_SDK_MIN_AMOUNT } from './phone-eligibility';
import { buildRequest, probeEligibility } from './googlepay-sdk';

// Get all HiPay local payment methods
const hipayLocalMethods = [
    'hipayenterprise_applepay',
    'hipayenterprise_googlepay',
    'hipayenterprise_alma_3x',
    'hipayenterprise_alma_4x',
    'hipayenterprise_bancomatpay',
    'hipayenterprise_bancontact',
    'hipayenterprise_bizum',
    'hipayenterprise_bnpp3x',
    'hipayenterprise_bnpp4x',
    'hipayenterprise_giropay',
    'hipayenterprise_ideal',
    'hipayenterprise_klarna',
    'hipayenterprise_mbway',
    'hipayenterprise_multibanco',
    'hipayenterprise_mybank',
    'hipayenterprise_3xcb',
    'hipayenterprise_3xcb_no_fees',
    'hipayenterprise_4xcb',
    'hipayenterprise_4xcb_no_fees',
    'hipayenterprise_paypal',
    'hipayenterprise_postfinance_card',
    'hipayenterprise_postfinance_efinance',
    'hipayenterprise_przelewy24',
    'hipayenterprise_sdd',
    'hipayenterprise_sisal',
    'hipayenterprise_sofort_uberweisung',
];

// Register each local payment method
hipayLocalMethods.forEach((methodName) => {
    const settings = getSetting(`${methodName}_data`, null);

    // Only register if settings exist (method is enabled)
    if (settings) {
        const label = decodeEntities(settings.title) || methodName;

        const Label = (props) => {
            const { PaymentMethodLabel } = props.components;
            return <PaymentMethodLabel text={label} />;
        };

        const Content = (props) => {
            // Pass all props including billing, shippingData, and cartData
            return <LocalPaymentComponent {...props} settings={settings} cartData={props} />;
        };

        const isApplePay = methodName === 'hipayenterprise_applepay';
        const isGooglePay = methodName === 'hipayenterprise_googlepay';

        const checkRestrictions = (args) => {
            const { billingAddress, cartTotals, shippingAddress } = args;
            if (isApplePay && !settings?.config?.multiBrowserEnabled) {
                if (!window.ApplePaySession || !window.ApplePaySession.canMakePayments()) {
                    return true;
                }
            }

            // Get restrictions from settings
            const restrictions = settings?.config?.restrictions || {};

            // If no restrictions, allow payment
            if (!restrictions || Object.keys(restrictions).length === 0) {
                return true;
            }

            const country = billingAddress?.country || shippingAddress?.country || '';
            const currency = cartTotals?.currency_code || '';
            const total = parseFloat(cartTotals?.total_price || 0) / 100; // Convert from cents

            // Check country restriction
            if (restrictions.countries && restrictions.countries.length > 0) {
                if (!restrictions.countries.includes(country)) {
                    return false;
                }
            }

            // Check currency restriction
            if (currency && restrictions.currencies && restrictions.currencies.length > 0) {
                if (!restrictions.currencies.includes(currency)) {
                    return false;
                }
            }

            // Check amount restrictions (Bizum: align with sdkjs min 0.5 EUR)
            const minAmount = methodName === 'hipayenterprise_bizum'
                ? Math.max(restrictions.minAmount || 0, BIZUM_SDK_MIN_AMOUNT)
                : (restrictions.minAmount || 0);
            if (minAmount > 0 && total < minAmount) {
                return false;
            }
            if (restrictions.maxAmount && total > restrictions.maxAmount) {
                return false;
            }

            return true;
        };

        const paymentMethodConfig = {
            name: methodName,
            label: <Label />,
            content: <Content />,
            edit: <Content />,
            canMakePayment: (args) => {
                if (!checkRestrictions(args)) {
                    return false;
                }

                if (isGooglePay) {
                    const { billingAddress, cartTotals, shippingAddress } = args;
                    const gpConfig = settings?.config || {};
                    const country = billingAddress?.country || shippingAddress?.country || gpConfig.countryCode;

                    return probeEligibility(gpConfig, buildRequest(gpConfig, cartTotals, country));
                }

                return true;
            },
            ariaLabel: label,
            supports: {
                features: settings.supports || ['products'],
            },
        };

        registerPaymentMethod(paymentMethodConfig);
    }
});
