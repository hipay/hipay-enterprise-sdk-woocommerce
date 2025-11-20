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
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Paypal extends Hipay_Gateway_Local_Abstract
{
    /**
     * Minimum amount for PayPal transactions.
     *
     * @var float
     */
    const MINIMUM_AMOUNT = 0.01;

    /**
     * Payment product code.
     *
     * @var string
     */
    protected $paymentProduct = 'paypal';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = 'hipayenterprise_paypal';
        $this->method_title = __('HiPay Enterprise Paypal', 'hipayenterprise');
        $this->title = __('Paypal', 'hipayenterprise');
        $this->method_description = __('Paypal', 'hipayenterprise');

        parent::__construct();

        $paymentProductConfig = $this->confHelper->getLocalPayment($this->paymentProduct);

        if ($this->isPaypalV2()) {
            $this->enqueuePaypalScripts($paymentProductConfig);
        }
    }

    /**
     * Enqueue PayPal scripts and localize required data.
     *
     * @param array $paymentProductConfig
     */
    protected function enqueuePaypalScripts(array $paymentProductConfig)
    {
        if (!is_admin()) { // Check if not in the admin area
            wp_enqueue_script(
                'hipay-js-front-paypal',
                plugins_url('/assets/js/frontend/local-payment-paypal.js', WC_HIPAYENTERPRISE_BASE_FILE),
                [],
                'all',
                true
            );

            wp_localize_script(
                'hipay-js-front-paypal',
                'hipay_config_paypal',
                $this->getPaypalScriptData($paymentProductConfig)
            );

            wp_localize_script(
                'hipay-js-front-paypal',
                'paypal_version',
                ['v2' => $this->isPaypalV2()]
            );

        }
    }

    /**
     * Get PayPal script data.
     *
     * @param array $paymentProductConfig
     * @return array
     */
    protected function getPaypalScriptData(array $paymentProductConfig)
    {
        return [
            'apiUsernameTokenJs' => $this->username,
            'apiPasswordTokenJs' => $this->password,
            'lang' => substr(apply_filters('hipay_locale', get_locale()), 0, 2),
            'environment' => $this->sandbox ? 'stage' : 'production',
            'fontFamily' => $this->confHelper->getHostedFieldsStyle()['fontFamily'],
            'color' => $this->confHelper->getHostedFieldsStyle()['color'],
            'fontSize' => $this->confHelper->getHostedFieldsStyle()['fontSize'],
            'fontWeight' => $this->confHelper->getHostedFieldsStyle()['fontWeight'],
            'placeholderColor' => $this->confHelper->getHostedFieldsStyle()['placeholderColor'],
            'caretColor' => $this->confHelper->getHostedFieldsStyle()['caretColor'],
            'iconColor' => $this->confHelper->getHostedFieldsStyle()['iconColor'],
            'buttonShape' => $paymentProductConfig['buttonShape'],
            'buttonColor' => $paymentProductConfig['buttonColor'],
            'buttonLabel' => $paymentProductConfig['buttonLabel'],
            'buttonHeight' => $paymentProductConfig['buttonHeight'],
            'bnpl' => $paymentProductConfig['bnpl'],
            'amount' => $this->getCartAmount(),
            'currency' => get_woocommerce_currency(),
            'locale' => apply_filters('hipay_locale', get_locale()),
            'isOrderPayPage' => $this->isOrderPayPage()
        ];
    }

    /**
     * Payment fields.
     */
    public function payment_fields()
    {
        $this->process_template(
            $this->getLocalPaymentMethodTemplate(),
            'frontend',
            [
                'localPaymentName' => $this->paymentProduct,
                'additionalFields' => $this->confHelper->getLocalPayment($this->paymentProduct)['additionalFields'],
            ]
        );
    }

    /**
     * Get local payment method template.
     *
     * @return string
     * @throws Exception
     */
    private function getLocalPaymentMethodTemplate()
    {
        return $this->isPaypalV2() ? 'local-paypal.php' : 'local-payment.php';
    }

    /**
     * Check if paypal order id exists
     *
     * @return bool
     */
    private function hasPaypalOrderId()
    {
        return $this->paymentProduct === 'paypal' && !(empty(Hipay_Helper::getPostData('paypalOrderId')));
    }

    /**
     * Process payment.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        if ($this->hasPaypalOrderId()) {
            $configLocalPayment = $this->confHelper->getLocalPayment($this->paymentProduct);
            return $this->processPaypalV2Payment($order_id, $configLocalPayment);
        }

        return parent::process_payment($order_id);
    }

    /**
     * Process PayPal v2 payment.
     *
     * @param int $order_id
     * @param array $configLocalPayment
     * @return array
     */
    protected function processPaypalV2Payment($order_id, array $configLocalPayment)
    {
        try {
            $this->logs->logInfos(" # Process Payment for  " . $order_id);

            $providerData = [
                'paypal_id' => (string) Hipay_Helper::getPostData('paypalOrderId'),
            ];

            $params = [
                'order_id' => $order_id,
                'paymentProduct' => $this->paymentProduct,
                'forceSalesMode' => $this->forceSalesMode($configLocalPayment),
                'deviceFingerprint' => Hipay_Helper::getPostData($this->paymentProduct . '-device_fingerprint'),
                'phone' => json_decode(Hipay_Helper::getPostData($this->paymentProduct . '-phone')),
                'provider_data' => (string) json_encode($providerData),
            ];

            if (is_array($configLocalPayment['additionalFields']['formFields'])) {
                $params = array_merge($params, $this->getAdditionalFields($configLocalPayment));
            }

            $response = $this->apiRequestHandler->handleLocalPayment($params);

            return [
                'result' => 'success',
                'redirect' => $response['redirectUrl'],
            ];
        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    /**
     * Get additional fields from the configuration.
     *
     * @param array $configLocalPayment
     * @return array
     */
    protected function getAdditionalFields(array $configLocalPayment)
    {
        $additionalFields = [];

        foreach ($configLocalPayment['additionalFields']['formFields'] as $name => $field) {
            $additionalFields[$name] = Hipay_Helper::getPostData($this->paymentProduct . '-' . $name);
        }

        return $additionalFields;
    }

    /**
     * Check if it's PayPal v2.
     *
     * @return bool
     * @throws Exception
     */
    protected function isPaypalV2()
    {
        $paypalOptions = $this->getCachedPaypalOptions();

        return !empty($paypalOptions['providerArchitectureVersion'])
            && $paypalOptions['providerArchitectureVersion'] === 'v1'
            && !empty($paypalOptions['payerId']);
    }

    /**
     * Generate HTML for local payment methods settings
     *
     * @return string HTML content
     */
    public function generate_methods_local_payments_settings_html()
    {
        ob_start();
        $this->process_template(
            'admin-paymentlocal-settings.php',
            'admin',
            [
                'configurationPaymentMethod' => $this->confHelper->getLocalPayment($this->paymentProduct),
                'method' => $this->paymentProduct,
                'isPayPalV2' => $this->isPaypalV2(),
            ]
        );

        return ob_get_clean();
    }

    /**
     * Get amount to be paid in order pay page
     *
     * @return float
     */
    protected function getOrderPayAmount() {
        global $wp;
        if (isset($wp->query_vars['order-pay']) && !empty($wp->query_vars['order-pay'])) {
            $order_id = absint($wp->query_vars['order-pay']);
            $order = wc_get_order($order_id);
            if ($order) {
                return $order->get_total();
            }
        }
        return 0;
    }

    /**
     * Get cart amount with proper validation and fallback handling
     *
     * @return float
     */
    protected function getCartAmount() {
        if ($this->isOrderPayPage()) {
            return $this->getOrderPayAmount();
        }

        if (!WC()->cart) {
            return self::MINIMUM_AMOUNT;
        }

        // Calculate cart totals if not already calculated
        if (!WC()->cart->totals_are_calculated) {
            WC()->cart->calculate_totals();
        }

        // Get cart total
        $total = WC()->cart->get_total('edit');

        if (is_numeric($total) && $total > 0) {
            return floatval($total);
        }

        // Fallback: minimum amount for PayPal
        return self::MINIMUM_AMOUNT;
    }

}
