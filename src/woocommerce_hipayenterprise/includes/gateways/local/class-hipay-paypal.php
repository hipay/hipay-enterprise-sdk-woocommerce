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
     * Payment product code.
     *
     * @var string
     */
    protected $paymentProduct = 'paypal';

    /**
     * Flag to track if PayPal scripts have been localized.
     *
     * @var bool
     */
    private $scriptsLocalized = false;

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
        
        // Note: Script enqueuing and localization moved to payment_fields()
        // This ensures proper checkout type detection and cart availability
    }

    /**
     * Enqueue PayPal script for classic checkout only (not blocks).
     */
    protected function enqueuePaypalScript()
    {
        if (!is_admin()) {
            // Only enqueue classic script if NOT using blocks checkout
            // Blocks checkout uses its own React component (paypal-button.js)
            if (!$this->is_blocks_checkout()) {
                wp_enqueue_script(
                    'hipay-js-front-paypal',
                    plugins_url('/assets/js/frontend/local-payment-paypal.js', WC_HIPAYENTERPRISE_BASE_FILE),
                    [],
                    'all',
                    true
                );
            }
        }
    }
    
    /**
     * Check if current page is using blocks checkout
     * 
     * @return bool
     */
    protected function is_blocks_checkout()
    {
        // Check if we're on a checkout page with blocks
        if (function_exists('has_block') && is_checkout()) {
            global $post;
            if ($post && has_block('woocommerce/checkout', $post)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Localize PayPal scripts with current cart/order data.
     * Called from payment_fields() to ensure cart is loaded.
     *
     * @param array $paymentProductConfig
     */
    protected function localizePaypalScripts(array $paymentProductConfig)
    {
        if ($this->scriptsLocalized || is_admin()) {
            return;
        }

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

        $this->scriptsLocalized = true;
    }

    /**
     * Get PayPal script data.
     *
     * @param array $paymentProductConfig
     * @return array
     */
    protected function getPaypalScriptData(array $paymentProductConfig)
    {
        // Get cart total - ensure it's a clean numeric value
        $amount = 0;
        if ($this->isOrderPayPage()) {
            $amount = $this->getOrderPayAmount();
        } elseif (WC()->cart) {
            // Get total as float, remove any formatting
            $total = WC()->cart->get_total('');
            $amount = is_numeric($total) ? floatval($total) : 0;
        }
        
        // Ensure minimum amount of 0.01
        if ($amount < 0.01) {
            $amount = 0.01;
        }
        
        // Format to 2 decimal places
        $amount = number_format($amount, 2, '.', '');

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
            'amount' => $amount,
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
        if ($this->isPaypalV2()) {
            // Enqueue script first (only for classic checkout, not blocks)
            $this->enqueuePaypalScript();
            
            // Then localize with cart data
            $paymentProductConfig = $this->confHelper->getLocalPayment($this->paymentProduct);
            $this->localizePaypalScripts($paymentProductConfig);
        }

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
}
