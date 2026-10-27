import { __ } from '@wordpress/i18n';

/**
 * Aligns with sdkjs payment-products/configs/bizum.config.json (minAmount).
 */
export const BIZUM_SDK_MIN_AMOUNT = 0.5;

/**
 * @returns {string|null} User-facing message when the phone HF must not be initialized.
 */
export function getPhoneHostedFieldBlockReason(paymentProduct, { orderAmount, billingCountry }) {
    if (paymentProduct === 'bizum') {
        if (billingCountry && billingCountry !== 'ES') {
            return __(
                'Bizum is only available for billing addresses in Spain.',
                'hipayenterprise'
            );
        }
        if (
            typeof orderAmount === 'number'
            && !Number.isNaN(orderAmount)
            && orderAmount < BIZUM_SDK_MIN_AMOUNT
        ) {
            return __('The order amount is too low for Bizum.', 'hipayenterprise');
        }
    }

    return null;
}

export function resolveHiPaySdkErrorMessage(err, fallback) {
    if (!err) {
        return fallback;
    }
    if (typeof err === 'string') {
        return err;
    }
    const message = err.message || err.error || err.description;
    if (message) {
        if (String(message).includes('HIPAY_PAYMENT_PRODUCT_NOT_ELIGIBLE')) {
            return __(
                'This payment method is not available for your country, currency or order amount.',
                'hipayenterprise'
            );
        }
        return String(message);
    }
    return fallback;
}

let sharedHiPayClient = null;

export function getOrCreateHiPayClient(config) {
    if (sharedHiPayClient) {
        return sharedHiPayClient;
    }

    const credentials = config.sandbox_mode
        ? {
              username: config.api_tokenjs_username_test,
              password: config.api_tokenjs_password_publickey_test,
          }
        : {
              username: config.api_tokenjs_username_production,
              password: config.api_tokenjs_password_publickey_production,
          };

    sharedHiPayClient = new window.HiPay({
        username   : credentials.username,
        password   : credentials.password,
        environment: config.sandbox_mode ? 'stage' : 'production',
        lang       : config.lang || 'en',
    });

    if (typeof sharedHiPayClient.injectBaseStylesheet === 'function') {
        sharedHiPayClient.injectBaseStylesheet();
    }

    return sharedHiPayClient;
}
