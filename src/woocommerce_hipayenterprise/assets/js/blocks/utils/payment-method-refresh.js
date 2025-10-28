import { useEffect, useRef } from '@wordpress/element';
import { useSelect, dispatch } from '@wordpress/data';
import { CART_STORE_KEY, CHECKOUT_STORE_KEY } from '@woocommerce/block-data';

/**
 * Custom hook that monitors billing/shipping address changes
 * and invalidates payment methods when country changes
 */
export const usePaymentMethodRefresh = () => {
    const prevCountry = useRef(null);

    const { billingCountry, shippingCountry } = useSelect((select) => {
        const store = select(CART_STORE_KEY);
        const customerData = store.getCustomerData();

        return {
            billingCountry: customerData.billingAddress?.country || '',
            shippingCountry: customerData.shippingAddress?.country || '',
        };
    }, []);

    useEffect(() => {
        const currentCountry = billingCountry || shippingCountry;

        // If country has changed, invalidate payment methods
        if (prevCountry.current !== null && prevCountry.current !== currentCountry) {
            // Force checkout to re-validate payment methods
            if (typeof dispatch !== 'undefined') {
                try {
                    const checkoutStore = dispatch(CHECKOUT_STORE_KEY);
                    // Trigger a checkout update to re-evaluate payment methods
                    if (checkoutStore && typeof checkoutStore.__internalSetIdle === 'function') {
                        checkoutStore.__internalSetIdle();
                    }
                } catch (e) {
                    console.warn('[HiPay] Could not refresh payment methods:', e);
                }
            }
        }

        prevCountry.current = currentCountry;
    }, [billingCountry, shippingCountry]);
};
