export const SUPPORTED_NETWORKS = ['visa', 'mastercard', 'maestro', 'cb'];

const PROBE_TIMEOUT_MS = 12000;
const DEFAULT_SDK_URL = 'https://libs.hipay.com/js/sdkjs.js';

let sdkPromise = null;
const probeCache = new Map();

export const loadSdk = (url) => {
    if (window.HiPay) {
        return Promise.resolve();
    }

    if (!sdkPromise) {
        sdkPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = url || DEFAULT_SDK_URL;
            script.async = true;
            script.onload = () => resolve();
            script.onerror = () => {
                sdkPromise = null;
                reject(new Error('Failed to load HiPay SDK'));
            };
            document.body.appendChild(script);
        });
    }

    return sdkPromise;
};

export const formatAmount = (cartTotals) => {
    const minorUnit = Number.isInteger(cartTotals?.currency_minor_unit) ? cartTotals.currency_minor_unit : 2;
    const total = parseFloat(cartTotals?.total_price || 0) / Math.pow(10, minorUnit);

    if (!(total > 0)) {
        return '0.01';
    }

    return total.toFixed(minorUnit);
};

export const buildRequest = (config, cartTotals, country) => ({
    amount: formatAmount(cartTotals),
    currencyCode: String(cartTotals?.currency_code || config.currency || '').toUpperCase(),
    countryCode: String(country || config.countryCode || '').toUpperCase(),
    supportedNetworks: SUPPORTED_NETWORKS,
});

export const createWallet = (config, selector, request) => {
    const hipay = new window.HiPay({
        username: config.apiUsernameTokenJs,
        password: config.apiPasswordTokenJs,
        environment: config.environment,
        lang: config.lang || 'en',
    });

    return Promise.resolve(
        hipay.create('wallet', {
            selector,
            provider: 'googlepay',
            displayName: config.shopName || '',
            existingPaymentMethodRequired: false,
            request,
        })
    );
};

export const getBrowserInfo = (hipayToken) => {
    const fromToken = hipayToken && hipayToken.browser_info;
    if (fromToken) {
        return typeof fromToken === 'string' ? fromToken : JSON.stringify(fromToken);
    }

    return JSON.stringify({
        java_enabled: typeof navigator.javaEnabled === 'function' ? navigator.javaEnabled() : false,
        javascript_enabled: true,
        language: navigator.language,
        color_depth: window.screen.colorDepth,
        screen_height: window.screen.height,
        screen_width: window.screen.width,
        timezone: String(new Date().getTimezoneOffset()),
        http_user_agent: navigator.userAgent,
    });
};

export const probeEligibility = (config, request) => {
    const key = [request.amount, request.currencyCode, request.countryCode].join('|');

    if (probeCache.has(key)) {
        return probeCache.get(key);
    }

    const promise = new Promise((resolve) => {
        const probeId = 'hipay-googlepay-probe-' + Math.random().toString(36).substr(2, 9);
        let wallet = null;
        let settled = false;
        let timer = null;

        const wrapper = document.createElement('div');
        wrapper.setAttribute('aria-hidden', 'true');
        wrapper.style.cssText = 'position:absolute;left:-9999px;width:320px;height:48px;overflow:hidden;';
        const container = document.createElement('div');
        container.id = probeId;
        container.style.cssText = 'width:320px;height:48px;';
        wrapper.appendChild(container);

        const settle = (available) => {
            if (settled) {
                return;
            }
            settled = true;
            clearTimeout(timer);
            if (wallet && typeof wallet.destroy === 'function') {
                try { wallet.destroy(); } catch (e) { /* nothing to clean up */ }
            }
            wrapper.remove();
            resolve(available);
        };

        timer = setTimeout(() => settle(false), PROBE_TIMEOUT_MS);

        loadSdk(config.hostedFieldsUrl)
            .then(() => {
                document.body.appendChild(wrapper);
                return createWallet(config, probeId, request);
            })
            .then((instance) => {
                if (settled) {
                    if (instance && typeof instance.destroy === 'function') {
                        try { instance.destroy(); } catch (e) { /* ignore */ }
                    }
                    return;
                }
                if (!instance) {
                    settle(false);
                    return;
                }
                wallet = instance;
                wallet.on('ready', () => settle(true));
                wallet.on('unavailable', (event) => {
                    console.info('[HiPay Google Pay] unavailable', event && event.code);
                    settle(false);
                });
            })
            .catch((error) => {
                console.warn('[HiPay Google Pay] probe failed', error);
                settle(false);
            });
    });

    probeCache.set(key, promise);

    return promise;
};
