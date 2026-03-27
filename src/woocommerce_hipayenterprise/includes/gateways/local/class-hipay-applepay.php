<?php
/**
 * HiPay Enterprise SDK WooCommerce
 *
 * 2018 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2018 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Apple Pay payment gateway for HiPay Enterprise.
 */
class Hipay_Applepay extends Hipay_Gateway_Local_Abstract
{
    /**
     * Minimum fallback amount when cart total cannot be determined.
     *
     * @var float
     */
    const MINIMUM_AMOUNT = 0.01;

    /**
     * Payment product code.
     *
     * @var string
     */
    protected $paymentProduct = 'applepay';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = 'hipayenterprise_applepay';
        $this->method_title = __('HiPay Enterprise Apple Pay', 'hipayenterprise');
        $this->title = __('Apple Pay', 'hipayenterprise');
        $this->method_description = __('Apple Pay', 'hipayenterprise');

        parent::__construct();

        $paymentProductConfig = $this->confHelper->getLocalPayment($this->paymentProduct);

        if (!is_admin() && $paymentProductConfig
            && (is_checkout() || is_add_payment_method_page())
            && !is_order_received_page()) {
            $this->enqueueApplePayScripts($paymentProductConfig);
        }
    }

    /**
     * Enqueue Apple Pay frontend scripts with localized configuration.
     *
     * @param array $paymentProductConfig
     */
    protected function enqueueApplePayScripts(array $paymentProductConfig)
    {
        wp_enqueue_script(
            'hipay-js-front-applepay',
            plugins_url('/assets/js/frontend/local-payment-applepay.js', WC_HIPAYENTERPRISE_BASE_FILE),
            ['jquery'],
            filemtime(WC_HIPAYENTERPRISE_PATH . 'assets/js/frontend/local-payment-applepay.js'),
            true
        );

        wp_localize_script(
            'hipay-js-front-applepay',
            'hipay_config_applepay',
            $this->getApplePayScriptData($paymentProductConfig)
        );
    }

    /**
     * Build the data object passed to the frontend JS.
     *
     * @param array $paymentProductConfig
     * @return array
     */
    protected function getApplePayScriptData(array $paymentProductConfig)
    {
        $account     = $this->confHelper->getAccount();
        $sandbox     = (bool) $this->sandbox;
        $credentials = Hipay_Helper::getApplePayTokenJsCredentials($account, $sandbox);
        $apiUsername = $credentials['username'];
        $apiPassword = $credentials['password'];

        $customerCountry = WC()->customer ? WC()->customer->get_billing_country() : '';
        $countryCode     = !empty($customerCountry) ? $customerCountry : WC()->countries->get_base_country();

        return [
            'apiUsernameTokenJs' => $apiUsername,
            'apiPasswordTokenJs' => $apiPassword,
            'environment'        => $sandbox ? 'stage' : 'production',
            'lang'               => substr(apply_filters('hipay_locale', get_locale()), 0, 2),
            'buttonType'         => $paymentProductConfig['buttonType'] ?? 'plain',
            'buttonStyle'        => $paymentProductConfig['buttonStyle'] ?? 'black',
            'shopName'           => get_bloginfo('name'),
            'amount'             => $this->getCartAmount(),
            'currency'           => get_woocommerce_currency(),
            'countryCode'        => $countryCode,
            'isOrderPayPage'     => $this->isOrderPayPage(),
            'tosNoticeMessage'   => __('Please accept the terms and conditions to use Apple Pay.', 'hipayenterprise'),
        ];
    }

    /**
     * Render the Apple Pay payment form.
     */
    public function payment_fields()
    {
        $this->process_template(
            'local-applepay.php',
            'frontend',
            ['localPaymentName' => $this->paymentProduct]
        );
    }

    /**
     * Process the Apple Pay payment.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        try {
            $this->logs->logInfos(" # Process Apple Pay Payment for " . $order_id);

            $tokenPaymentProduct = Hipay_Helper::getPostData('applepay-payment-product');
            $resolvedPaymentProduct = !empty($tokenPaymentProduct)
                ? strtolower(str_replace(' ', '-', $tokenPaymentProduct))
                : '';

            $this->logs->logInfos(
                " # Apple Pay payment_product — raw: " . var_export($tokenPaymentProduct, true)
                . " | resolved: " . var_export($resolvedPaymentProduct, true)
            );

            $params = [
                'order_id'       => $order_id,
                'paymentProduct' => $resolvedPaymentProduct,
                'forceSalesMode'    => $this->forceSalesMode(),
                'cardtoken'         => Hipay_Helper::getPostData('applepay-card-token'),
                'card_holder'       => Hipay_Helper::getPostData('applepay-card-holder'),
                'deviceFingerprint' => Hipay_Helper::getPostData('applepay-device_fingerprint'),
                'isApplePay'        => true,
            ];

            $response = $this->apiRequestHandler->handleCreditCard($params);

            return [
                'result'   => 'success',
                'redirect' => $response['redirectUrl'],
            ];
        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    /**
     * Get the cart total amount, with order-pay page and fallback handling.
     *
     * @return float
     */
    protected function getCartAmount()
    {
        if ($this->isOrderPayPage()) {
            global $wp;
            if (isset($wp->query_vars['order-pay']) && !empty($wp->query_vars['order-pay'])) {
                $order = wc_get_order(absint($wp->query_vars['order-pay']));
                if ($order) {
                    return $order->get_total();
                }
            }
        }

        if (!WC()->cart) {
            return self::MINIMUM_AMOUNT;
        }

        $total = WC()->cart->get_total('edit');

        return is_numeric($total) && $total > 0 ? floatval($total) : self::MINIMUM_AMOUNT;
    }
}
