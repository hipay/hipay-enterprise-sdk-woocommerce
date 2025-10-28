import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import { getSetting } from '@woocommerce/settings';
import CreditCardComponent from './payment-method';

const settings = getSetting('hipayenterprise_credit_card_data', {});

const defaultLabel = __('Credit Card', 'hipayenterprise');
const label = decodeEntities(settings.title) || defaultLabel;

/**
 * Content component for HiPay Credit Card payment method
 */
const Content = (props) => {
    return <CreditCardComponent {...props} settings={settings} />;
};

/**
 * Label component
 */
const Label = (props) => {
    const { PaymentMethodLabel } = props.components;
    return <PaymentMethodLabel text={label} />;
};

/**
 * HiPay Credit Card payment method config object
 */
const HiPayCreditCard = {
    name: 'hipayenterprise_credit_card',
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: ({ billingAddress, cartTotals, shippingAddress }) => {
        // Get credit card restrictions from settings
        const restrictions = settings?.config?.creditCardRestrictions || {};

        // If no restrictions are defined, allow payment
        if (Object.keys(restrictions).length === 0) {
            return true;
        }

        // Check if at least one credit card type is available for current context
        const country = billingAddress?.country || shippingAddress?.country || '';
        const currency = cartTotals?.currency_code || '';
        const total = parseFloat(cartTotals?.total_price || 0) / 100; // Convert from cents

        for (const cardType in restrictions) {
            const cardRestrictions = restrictions[cardType];

            // Check country restriction
            if (cardRestrictions.countries && cardRestrictions.countries.length > 0) {
                if (!cardRestrictions.countries.includes(country)) {
                    continue; // This card type not available for this country
                }
            }

            // Check currency restriction
            if (cardRestrictions.currencies && cardRestrictions.currencies.length > 0) {
                if (!cardRestrictions.currencies.includes(currency)) {
                    continue; // This card type not available for this currency
                }
            }

            // Check amount restrictions
            if (cardRestrictions.minAmount && total < cardRestrictions.minAmount) {
                continue; // Amount too low for this card type
            }
            if (cardRestrictions.maxAmount && total > cardRestrictions.maxAmount) {
                continue; // Amount too high for this card type
            }

            // If we reach here, at least one card type is available
            return true;
        }

        // No card types are available for current context
        return false;
    },
    ariaLabel: label,
    supports: {
        features: settings.supports || ['products'],
    },
};

registerPaymentMethod(HiPayCreditCard);
