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
 * HiPay Credit Card payment method blocks integration
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link        https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
final class Hipay_Credit_Card_Block extends Hipay_Payment_Block_Abstract
{
    /**
     * Payment method name/id/slug
     *
     * @var string
     */
    protected $name = 'hipayenterprise_credit_card';

    /**
     * Constructor
     */
    public function __construct()
    {
        // Hook to intercept payment processing for blocks
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

        error_log('HiPay Blocks: Payment context data: ' . print_r($context->payment_data, true));

        // Map block payment data to expected $_POST keys
        $mapping = [
            'hipay_token' => 'card-token',
            'hipay_payment_product' => 'card-payment_product',
            'hipay_brand' => 'card-brand',
            'hipay_pan' => 'card-pan',
            'hipay_card_expiry_month' => 'card-card_expiry_month',
            'hipay_card_expiry_year' => 'card-card_expiry_year',
            'hipay_card_holder' => 'card-holder', // Note: different from card-card_holder
            'hipay_device_fingerprint' => 'card-device_fingerprint',
            'hipay_browser_info' => 'card-browser_info',
            'hipay_save_card' => 'card-multi_use',
            'hipay_use_saved_card' => 'hipay_use_saved_card',
            'hipay_operating_mode' => 'hipay_operating_mode',
        ];

        // Set the payment data in $_POST with the correct keys
        foreach ($context->payment_data as $key => $value) {
            if (isset($mapping[$key])) {
                $mappedKey = $mapping[$key];
                $_POST[$mappedKey] = $value;
                error_log("HiPay Blocks: Mapped \$_POST[$mappedKey] = $value");

                // Also set the card-card_* variants for fields used in token saving
                if ($key === 'hipay_card_holder') {
                    $_POST['card-card_holder'] = $value;
                } elseif ($key === 'hipay_brand') {
                    $_POST['card-brand'] = $value;
                    // If no payment_product provided, derive it from brand
                    if (empty($_POST['card-payment_product']) && !empty($value)) {
                        $_POST['card-payment_product'] = $value;
                        error_log("HiPay Blocks: Derived payment_product from brand: $value");
                    }
                } elseif ($key === 'hipay_pan') {
                    $_POST['card-pan'] = $value;
                } elseif ($key === 'hipay_card_expiry_month') {
                    $_POST['card-card_expiry_month'] = $value;
                } elseif ($key === 'hipay_card_expiry_year') {
                    $_POST['card-card_expiry_year'] = $value;
                } elseif ($key === 'hipay_payment_product') {
                    $_POST['card-payment_product'] = $value;
                }
            } else {
                $_POST[$key] = $value;
                error_log("HiPay Blocks: Set \$_POST[$key] = $value");
            }
        }

        // Don't set a default payment_product - let HiPay determine it from the card token
        if (empty($_POST['card-payment_product'])) {
            error_log("HiPay Blocks: payment_product is empty, will be determined by HiPay API from card token");
        }

        error_log('HiPay Blocks: Final $_POST after mapping: ' . print_r($_POST, true));

        return $result;
    }

    /**
     * Get the gateway instance
     *
     * @return Gateway_Hipay|null
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

        $paymentGlobal = $this->confHelper->getPaymentGlobal();
        $account = $this->confHelper->getAccount();
        $isSandbox = isset($account['global']['sandbox_mode']) && $account['global']['sandbox_mode'];
        $hostedFieldsStyle = $this->confHelper->getHostedFieldsStyle();

        // Get billing country - handle admin context where customer might be null
        $billingCountry = '';
        if (WC()->customer) {
            $billingCountry = WC()->customer->get_billing_country();
        }

        // Get cart total - handle admin context where cart might be null
        $cartTotal = 0;
        if (WC()->cart) {
            $totals = WC()->cart->get_totals();
            $cartTotal = isset($totals['total']) ? $totals['total'] : 0;
        }

        // Get activated credit cards
        $activatedCreditCards = [];
        if ($billingCountry) {
            $activatedCreditCards = Hipay_Helper::getActivatedPaymentByCountryAndCurrency(
                $gateway,
                "credit_card",
                $billingCountry,
                get_woocommerce_currency(),
                $cartTotal,
                false
            );
        }

        // Get all credit card restrictions for dynamic checking in JavaScript
        $creditCardRestrictions = [];
        $paymentConfig = $this->confHelper->getPayment();
        if (isset($paymentConfig['credit_card']) && is_array($paymentConfig['credit_card'])) {
            foreach ($paymentConfig['credit_card'] as $cardType => $conf) {
                if (!empty($conf['activated'])) {
                    $creditCardRestrictions[$cardType] = [
                        'countries' => $conf['countries'] ?? [],
                        'currencies' => $conf['currencies'] ?? [],
                        'minAmount' => $conf['minAmount'] ?? 0,
                        'maxAmount' => $conf['maxAmount'] ?? null,
                    ];
                }
            }
        }

        // Get saved cards for logged-in users
        $savedCards = [];
        if (is_user_logged_in() && $gateway->supports('tokenization')) {
            $tokens = $gateway->get_tokens();
            if (is_array($tokens)) {
                foreach ($tokens as $token) {
                    if (!$token->get_authorized()) {
                        continue;
                    }

                    $tokenData = $token->get_data();
                    $savedCards[] = [
                        'token' => $token->get_token(),
                        'brand' => strtolower($token->get_card_type()),
                        'pan' => str_replace('*', 'x', $tokenData['pan'] ?? ''),
                        'card_expiry_month' => $tokenData['expiry_month'] ?? '',
                        'card_expiry_year' => $tokenData['expiry_year'] ?? '',
                        'card_holder' => $tokenData['card_holder'] ?? ''
                    ];
                }
            }
        }

        return [
            'operating_mode' => $paymentGlobal['operating_mode'] ?? '',
            'api_tokenjs_username_production' => $account['production']['api_tokenjs_username_production'] ?? '',
            'api_tokenjs_password_publickey_production' => $account['production']['api_tokenjs_password_publickey_production'] ?? '',
            'api_tokenjs_username_test' => $account['sandbox']['api_tokenjs_username_sandbox'] ?? '',
            'api_tokenjs_password_publickey_test' => $account['sandbox']['api_tokenjs_password_publickey_sandbox'] ?? '',
            'sandbox_mode' => $isSandbox,
            'display_card_selector' => $paymentGlobal['display_card_selector'] ?? false,
            'card_token' => $paymentGlobal['card_token'] ?? false,
            'display_hosted_page' => $paymentGlobal['display_hosted_page'] ?? '',
            'color' => $hostedFieldsStyle['color'] ?? '#000000',
            'fontFamily' => $hostedFieldsStyle['fontFamily'] ?? 'Roboto, sans-serif',
            'fontSize' => $hostedFieldsStyle['fontSize'] ?? '15px',
            'fontWeight' => $hostedFieldsStyle['fontWeight'] ?? '400',
            'placeholderColor' => $hostedFieldsStyle['placeholderColor'] ?? '#999999',
            'caretColor' => $hostedFieldsStyle['caretColor'] ?? '#000000',
            'iconColor' => $hostedFieldsStyle['iconColor'] ?? '#00ADE9',
            'ccDisplayName' => $paymentGlobal['ccDisplayName'] ?? [],
            'activatedCreditCards' => $activatedCreditCards,
            'creditCardRestrictions' => $creditCardRestrictions,
            'savedCards' => $savedCards,
            'canSaveCards' => is_user_logged_in() && $gateway->supports('tokenization'),
            'hostedFieldsUrl' => $paymentGlobal['sdk_js_url'] ?? 'https://libs.hipay.com/js/sdkjs.js',
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'i18n' => [
                'card_holder' => __('Card Holder', 'hipayenterprise'),
                'card_number' => __('Card Number', 'hipayenterprise'),
                'expiry_date' => __('Expiry Date', 'hipayenterprise'),
                'cvv' => __('CVV', 'hipayenterprise'),
                'save_card' => __('Save card for future payments', 'hipayenterprise'),
                'use_saved_card' => __('Use a saved card', 'hipayenterprise'),
                'use_new_card' => __('Use a new card', 'hipayenterprise'),
            ]
        ];
    }

    /**
     * Get the file path to the built script.
     *
     * @return string
     */
    protected function get_script_path()
    {
        return WC_HIPAYENTERPRISE_PATH_ASSETS . 'js/blocks/build/credit-card-block.js';
    }

    /**
     * Get the URL to the built script.
     *
     * @return string
     */
    protected function get_script_url()
    {
        return WC_HIPAYENTERPRISE_URL_ASSETS . 'js/blocks/build/credit-card-block.js';
    }
}
