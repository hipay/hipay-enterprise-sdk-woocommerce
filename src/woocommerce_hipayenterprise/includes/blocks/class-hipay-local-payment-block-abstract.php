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
 * Base abstract class for HiPay local payment method blocks integration
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link        https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
abstract class Hipay_Local_Payment_Block_Abstract extends Hipay_Payment_Block_Abstract
{
    /**
     * Payment product identifier
     *
     * @var string
     */
    protected $paymentProduct;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Hook to intercept payment processing for blocks - use the same hook as credit cards
        add_filter('woocommerce_rest_checkout_process_payment_with_context', array($this, 'process_blocks_payment'), 10, 2);
    }

    /**
     * Process payment for blocks checkout
     *
     * @param PaymentContext $context Payment context
     * @param PaymentResult $result Payment result
     * @return PaymentResult
     */
    public function process_blocks_payment($context, $result)
    {
        if ($context->payment_method !== $this->name) {
            return $result;
        }

        // Get the order (it exists at this point during payment processing)
        if ($context->order_id) {
            $order = wc_get_order($context->order_id);
            if ($order) {
                $billingPhone = $order->get_billing_phone();

                if ($billingPhone) {
                    $_POST[$this->paymentProduct . '-phone'] = json_encode($billingPhone);
                }
            }
        }

        // Map any additional payment data from blocks
        // For PayPal, some keys should not be prefixed (they match shortcode template field names)
        // Note: WooCommerce Blocks converts keys to lowercase, so we need both versions
        $nonPrefixedKeys = [
            'paypalOrderId', 'paypalorderid', // PayPal Order ID (camelCase and lowercase)
            'browserInfo', 'browserinfo',      // Browser info (camelCase and lowercase)
            'method', 'paymentmethod', 'productlist'
        ];

        foreach ($context->payment_data as $key => $value) {
            if (in_array($key, $nonPrefixedKeys)) {
                // Don't prefix these keys - they match shortcode template field names
                // Map lowercase keys to proper camelCase names for PayPal
                $mappedKey = $key;
                if ($key === 'paypalorderid') {
                    $mappedKey = 'paypalOrderId';
                } elseif ($key === 'browserinfo') {
                    $mappedKey = 'browserInfo';
                }
                $_POST[$mappedKey] = $value;
            } else {
                // Prefix other keys with payment product
                $_POST[$this->paymentProduct . '-' . $key] = $value;
            }
        }

        // Special handling for PayPal v2: transform paypalOrderId into provider_data
        if ($this->paymentProduct === 'paypal' && isset($_POST['paypalOrderId'])) {
            $providerData = [
                'paypal_id' => (string) $_POST['paypalOrderId'],
            ];
            $_POST['provider_data'] = json_encode($providerData);

            // Store in a transient for retrieval in gateway process_payment
            // Use customer session ID as unique key, expires after 5 minutes
            $transient_key = 'hipay_provider_data_' . WC()->session->get_customer_id();
            set_transient($transient_key, json_encode($providerData), 300);
        }

        return $result;
    }

    /**
     * Get the gateway instance
     *
     * @return Hipay_Gateway_Local_Abstract|null
     */
    protected function get_gateway()
    {
        if (!isset($this->gateway)) {
            $gateways = WC()->payment_gateways->get_available_payment_gateways();
            $this->gateway = isset($gateways[$this->name]) ? $gateways[$this->name] : null;
        }
        return $this->gateway;
    }

    /**
     * Get payment method specific configuration
     *
     * @return array
     */
    protected function get_payment_config()
    {
        $gateway = $this->get_gateway();
        if (!$gateway) {
            return [];
        }

        $methodConf = $this->confHelper->getLocalPayment($this->paymentProduct);

        // If payment method config doesn't exist, return minimal config
        if (!$methodConf) {
            return [
                'paymentProduct' => $this->paymentProduct,
                'displayName' => '',
                'additionalFields' => [],
                'canManualCapture' => false,
                'canRefund' => false,
                'logo' => '',
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'restrictions' => [],
                'isPayPalV2' => false,
                'paypalV2Notice' => '',
            ];
        }

        // Get payment restrictions from config
        $paymentConfig = $this->confHelper->getPayment();
        $restrictions = [];

        if (isset($paymentConfig[Hipay_Config::KEY_LOCAL_PAYMENT][$this->paymentProduct])) {
            $conf = $paymentConfig[Hipay_Config::KEY_LOCAL_PAYMENT][$this->paymentProduct];
            if ($conf) {
                $restrictions = [
                    'countries' => $conf['countries'] ?? [],
                    'currencies' => $conf['currencies'] ?? [],
                    'minAmount' => $conf['minAmount'] ?? 0,
                    'maxAmount' => $conf['maxAmount'] ?? null,
                ];
            }
        }

        // Check if this is PayPal v2 and get configuration
        $isPayPalV2 = false;
        $paypalConfig = [];

        if ($this->paymentProduct === 'paypal') {
            try {
                // Try to get PayPal options from cache to determine version
                $paypalOptions = $gateway->getCachedPaypalOptions();
                $isPayPalV2 = !empty($paypalOptions['providerArchitectureVersion'])
                    && $paypalOptions['providerArchitectureVersion'] === 'v1'
                    && !empty($paypalOptions['payerId']);

                // Get PayPal button configuration
                if ($isPayPalV2) {
                    $paypalConfig = [
                        'buttonShape' => $methodConf['buttonShape'] ?? 'rect',
                        'buttonColor' => $methodConf['buttonColor'] ?? 'gold',
                        'buttonLabel' => $methodConf['buttonLabel'] ?? 'paypal',
                        'buttonHeight' => $methodConf['buttonHeight'] ?? 40,
                        'bnpl' => $methodConf['bnpl'] ?? false,
                        'i18n' => [
                            'addressRequired' => __('Shipping address is required for PayPal payment.', 'hipayenterprise'),
                            'invalidAddressPrefix' => __('Invalid delivery address. Please check or correct the following fields: ', 'hipayenterprise'),
                            'unableToInitialize' => __('Unable to initialize PayPal. Please check your shipping address.', 'hipayenterprise'),
                            'fieldNames' => [
                                'zipCode' => __('Postal Code', 'hipayenterprise'),
                                'city' => __('City', 'hipayenterprise'),
                                'country' => __('Country', 'hipayenterprise'),
                                'streetaddress' => __('Street Address', 'hipayenterprise'),
                            ]
                        ]
                    ];
                }
            } catch (Exception $e) {
                // Ignore errors checking PayPal version
            }
        }

        // Get HiPay credentials and configuration
        $account = $this->confHelper->getAccount();
        $isSandbox = isset($account['global']['sandbox_mode']) && $account['global']['sandbox_mode'];

        return [
            'paymentProduct' => $this->paymentProduct,
            'displayName' => $methodConf['displayName'] ?? '',
            'additionalFields' => $methodConf['additionalFields'] ?? [],
            'canManualCapture' => $methodConf['canManualCapture'] ?? false,
            'canRefund' => $methodConf['canRefund'] ?? false,
            'logo' => $methodConf['logo'] ?? '',
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restrictions' => $restrictions,
            'isPayPalV2' => $isPayPalV2,
            'paypalConfig' => $paypalConfig,
            'hostedFieldsUrl' => 'https://libs.hipay.com/js/sdkjs.js',
            'sandbox_mode' => $isSandbox,
            'api_tokenjs_username_test' => $account['sandbox']['api_tokenjs_username_sandbox'] ?? '',
            'api_tokenjs_password_publickey_test' => $account['sandbox']['api_tokenjs_password_publickey_sandbox'] ?? '',
            'api_tokenjs_username_production' => $account['production']['api_tokenjs_username_production'] ?? '',
            'api_tokenjs_password_publickey_production' => $account['production']['api_tokenjs_password_publickey_production'] ?? '',
            'lang' => substr(apply_filters('hipay_locale', get_locale()), 0, 2),
            'locale' => apply_filters('hipay_locale', get_locale()),
        ];
    }

    /**
     * Get the file path to the built script.
     *
     * @return string
     */
    protected function get_script_path()
    {
        return WC_HIPAYENTERPRISE_PATH_ASSETS . 'js/blocks/build/local-payments-block.js';
    }

    /**
     * Get the URL to the built script.
     *
     * @return string
     */
    protected function get_script_url()
    {
        return WC_HIPAYENTERPRISE_URL_ASSETS . 'js/blocks/build/local-payments-block.js';
    }
}
