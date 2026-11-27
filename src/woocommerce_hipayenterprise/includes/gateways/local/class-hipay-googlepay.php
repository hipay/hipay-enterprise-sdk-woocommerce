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
 * Google Pay payment gateway for HiPay Enterprise.
 */
class Hipay_Googlepay extends Hipay_Gateway_Local_Abstract
{
    /**
     * Technical minimum amount accepted by Google Pay.
     *
     * @var float
     */
    const MINIMUM_AMOUNT = 0.01;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id = 'hipayenterprise_googlepay';
        $this->paymentProduct = 'googlepay';
        $this->method_title = __('HiPay Enterprise Google Pay', 'hipayenterprise');
        $this->title = __('Google Pay', 'hipayenterprise');
        $this->method_description = __('Google Pay', 'hipayenterprise');

        parent::__construct();

        if (!is_admin() && !$this->is_blocks_checkout()) {
            add_action('wp_enqueue_scripts', function () {
                if ((is_checkout() || is_add_payment_method_page()) && !is_order_received_page()) {
                    $this->enqueueGooglePayScripts();
                }
            });
        }
    }

    protected function enqueueGooglePayScripts()
    {
        if (!$this->isEnabledInSettings()) {
            return;
        }

        $dependencies = ['jquery'];
        if (wp_script_is('hipay-js-hosted-fields-sdk', 'registered')) {
            $dependencies[] = 'hipay-js-hosted-fields-sdk';
        }

        wp_enqueue_script(
            'hipay-js-front-googlepay',
            plugins_url('/assets/js/frontend/local-payment-googlepay.js', WC_HIPAYENTERPRISE_BASE_FILE),
            $dependencies,
            filemtime(WC_HIPAYENTERPRISE_PATH . 'assets/js/frontend/local-payment-googlepay.js'),
            true
        );

        wp_localize_script(
            'hipay-js-front-googlepay',
            'hipay_config_googlepay',
            $this->getGooglePayScriptData()
        );
    }

    /**
     * @return array
     */
    protected function getGooglePayScriptData()
    {
        $account = $this->confHelper->getAccount();
        $sandbox = (bool) $this->sandbox;
        $env     = $sandbox ? ($account['sandbox'] ?? []) : ($account['production'] ?? []);
        $suffix  = $sandbox ? 'sandbox' : 'production';

        $customerCountry = $this->getCurrentPaymentContext()['country'];

        return [
            'apiUsernameTokenJs' => $env['api_tokenjs_username_' . $suffix] ?? '',
            'apiPasswordTokenJs' => $env['api_tokenjs_password_publickey_' . $suffix] ?? '',
            'environment'        => $sandbox ? 'stage' : 'production',
            'lang'               => substr(apply_filters('hipay_locale', get_locale()), 0, 2),
            'shopName'           => get_bloginfo('name'),
            'amount'             => $this->getFormattedAmount(),
            'currency'           => get_woocommerce_currency(),
            'countryCode'        => !empty($customerCountry) ? $customerCountry : WC()->countries->get_base_country(),
            'isOrderPayPage'     => $this->isOrderPayPage(),
            'tosNoticeMessage'   => __('Please accept the terms and conditions to use Google Pay.', 'hipayenterprise'),
            'errorMessage'       => __('Google Pay payment failed. Please try again or use another payment method.', 'hipayenterprise'),
        ];
    }

    /**
     * @return string
     */
    protected function getFormattedAmount()
    {
        $total = (float) $this->getCurrentPaymentContext()['total'];

        if ($total <= 0) {
            return (string) self::MINIMUM_AMOUNT;
        }

        return number_format($total, wc_get_price_decimals(), '.', '');
    }

    public function payment_fields()
    {
        $context = $this->getCurrentPaymentContext();

        $this->process_template(
            'local-googlepay.php',
            'frontend',
            [
                'localPaymentName' => $this->paymentProduct,
                'amount'           => $this->getFormattedAmount(),
                'currency'         => $context['currency'],
                'country'          => $context['country'],
            ]
        );
    }

    /**
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        try {
            $this->logs->logInfos(" # Process Google Pay Payment for " . $order_id);

            $this->assertOrderEligibility($order_id);

            $rawPaymentProduct = Hipay_Helper::getPostData('googlepay-payment-product');
            $paymentProduct    = $this->normalizePaymentProduct($rawPaymentProduct);

            $this->logs->logInfos(
                " # Google Pay payment_product — raw: " . var_export($rawPaymentProduct, true)
                . " | resolved: " . var_export($paymentProduct, true)
            );

            if (empty($paymentProduct)) {
                throw new Hipay_Payment_Exception(
                    __('An error occured, process has been cancelled.', 'hipayenterprise')
                );
            }

            $browserInfo = Hipay_Helper::getPostData('googlepay-browser-info');
            if (empty($browserInfo)) {
                $browserInfo = Hipay_Helper::getPostData('googlepay-browser_info');
            }

            $params = [
                'order_id'          => $order_id,
                'paymentProduct'    => $paymentProduct,
                'forceSalesMode'    => true,
                'cardtoken'         => Hipay_Helper::getPostData('googlepay-card-token'),
                'card_holder'       => Hipay_Helper::getPostData('googlepay-card-holder'),
                'deviceFingerprint' => Hipay_Helper::getPostData('googlepay-device_fingerprint'),
                'browser_info'      => !empty($browserInfo) ? json_decode($browserInfo) : null,
                'isGooglePay'       => true,
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
     * @param string|null $network
     * @return string Empty string when the network is not supported
     */
    protected function normalizePaymentProduct($network)
    {
        $key = strtolower(str_replace(' ', '-', trim((string) $network)));

        return preg_match('/^[a-z0-9_-]+$/', $key) ? $key : '';
    }
}
