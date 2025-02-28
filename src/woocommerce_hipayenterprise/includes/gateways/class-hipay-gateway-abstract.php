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
    // Exit if accessed directly
}

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Gateway_Abstract extends WC_Payment_Gateway
{

    const TEXT_DOMAIN = "hipayenterprise";

    /**
     * @var Hipay_Log
     */
    public $logs;

    /**
     * @var Hipay_Settings_Handler
     */
    public $settingsHandler;

    /**
     * @var Hipay_Config
     */
    public $confHelper;

    /**
     * @var
     */
    public $plugin_version;

    /**
     * @var Hipay_Api_Request_Handler
     */
    protected $apiRequestHandler;

    /**
     * @var
     */
    protected $notifications;

    /**
     * @var
     */
    protected $paymentProduct;

    /**
     * @var
     */
    protected $username;

    /**
     * @var
     */
    protected $password;

    /**
     * @var
     */
    protected $sandbox;

    /**
     * Cache duration in seconds (5 minutes default)
     */
    const CACHE_DURATION = 300;

    /**
     * Hipay_Gateway_Abstract constructor.
     */
    public function __construct()
    {
        if (is_admin() && function_exists('get_plugin_data')) {
            $plugin_data = get_plugin_data(__FILE__);
            $this->plugin_version = $plugin_data['Version'];
        }

        $this->confHelper = new Hipay_Config();

        $this->confHelper->getConfigHipay();

        $this->logs = new Hipay_Log($this);

        $this->apiRequestHandler = new Hipay_Api_Request_Handler($this);

        $this->settingsHandler = new Hipay_Settings_Handler($this);

        if (version_compare(WC()->version, '3.0.0', '<=')) {
            $this->notifications[] = __(
                sprintf(
                    'Your Woocommerce version (%s) is not compatible with HiPay module.Please upgrade to minimum version 3.0.0',
                    WC()->version
                )
            );
        }

        $this->addActions();

        wp_enqueue_script(
            'hipay-js-hosted-fields-sdk',
            $this->confHelper->getPaymentGlobal()["sdk_js_url"],
            array(),
            'all',
            array('in_footer' => false)
        );

        wp_enqueue_script(
            'hipay-js-front',
            plugins_url('/assets/js/frontend/hosted-fields.js', WC_HIPAYENTERPRISE_BASE_FILE),
            array(),
            'all',
            true
        );

        $this->sandbox = $this->confHelper->getAccount()["global"]["sandbox_mode"];
        $this->username = ($this->sandbox) ? $this->confHelper->getAccount()["sandbox"]["api_tokenjs_username_sandbox"]
            : $this->confHelper->getAccount()["production"]["api_tokenjs_username_production"];
        $this->password = ($this->sandbox) ? $this->confHelper->getAccount()["sandbox"]["api_tokenjs_password_publickey_sandbox"]
            : $this->confHelper->getAccount()["production"]["api_tokenjs_password_publickey_production"];

        wp_localize_script(
            'hipay-js-front',
            'hipay_config',
            array(
                "apiUsernameTokenJs" => $this->username,
                "apiPasswordTokenJs" => $this->password,
                "lang" => substr(get_locale(), 0, 2),
                "environment" => $this->sandbox ? "stage" : "production",
                "fontFamily" => $this->confHelper->getHostedFieldsStyle()["fontFamily"],
                "color" => $this->confHelper->getHostedFieldsStyle()["color"],
                "fontSize" => $this->confHelper->getHostedFieldsStyle()["fontSize"],
                "fontWeight" => $this->confHelper->getHostedFieldsStyle()["fontWeight"],
                "placeholderColor" => $this->confHelper->getHostedFieldsStyle()["placeholderColor"],
                "caretColor" => $this->confHelper->getHostedFieldsStyle()["caretColor"],
                "iconColor" => $this->confHelper->getHostedFieldsStyle()["iconColor"],
                'useOneClick' => $this->getOneClickOptions()
            )
        );

        $paypalOptions = $this->getCachedPaypalOptions();

        wp_localize_script(
            'hipay-js-front',
            'paypal_version',
            ['v2' => !empty($paypalOptions['providerArchitectureVersion'])
                && $paypalOptions['providerArchitectureVersion'] === 'v1'
                && !empty($paypalOptions['payerId'])]
        );
    }

    /**
     * @return bool|array
     */
    protected function getOneClickOptions()
    {
        if (!is_user_logged_in() || !$this->confHelper->isOneClick()) {
            return false;
        }

        $options = [];
        $options['card_count'] = $this->confHelper->getPaymentGlobal()['number_saved_cards_displayed'] ?? time();
        $options['switch_color'] = $this->confHelper->getPaymentGlobal()['switch_color_input'] ?? '#02A17B';
        $options['checkbox_color'] = $this->confHelper->getPaymentGlobal()['checkbox_color_input'] ?? '#02A17B';

        return $options;
    }

    /**
     * @return string
     */
    public function getPaymentProduct()
    {
        return $this->paymentProduct;
    }

    /**
     * @return Hipay_Api
     */
    public function getApi()
    {
        return $this->apiRequestHandler->getApi();
    }

    /**
     * Add common action callback
     */
    public function addActions()
    {
        add_filter('woocommerce_available_payment_gateways', array($this, 'available_payment_gateways'));
        add_filter('woocommerce_payment_methods_list_item', array($this, 'custom_payment_methods_list_item'), 10, 2);

        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array($this, 'process_admin_options')
        );
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_settings'));
    }

    /**
     * @param $available_gateways
     * @return mixed
     */
    public function available_payment_gateways($available_gateways)
    {
        if (isset(WC()->cart)) {
            foreach ($available_gateways as $id => $gateway) {
                if ($id == $this->id && !$gateway->isAvailableForCurrentCart()) {
                    unset($available_gateways [$id]);
                }
            }
        }
        return $available_gateways;
    }

    /**
     * Hide "Make default" button for specific payment methods
     *
     * @param array $list_item
     * @param WC_Payment_Token $payment_token
     * @return array
     */
    public function custom_payment_methods_list_item($list_item, $payment_token) {
        // Check if this is your HiPay payment method
        if ($payment_token->get_gateway_id() === $this->id) {
            if (isset($list_item['actions']['default'])) {
                unset($list_item['actions']['default']);
            }
        }
        return $list_item;
    }

    /**
     * Check if payment method is available for current cart
     *
     * @return boolean
     */
    public function isAvailableForCurrentCart()
    {
        $cartTotals = WC()->cart->get_totals();
        $activatedPayments = Hipay_Helper::getActivatedPaymentByCountryAndCurrency(
            $this,
            $this->paymentProduct,
            WC()->customer->get_billing_country(),
            get_woocommerce_currency(),
            $cartTotals["total"]
        );

        return !empty($activatedPayments);
    }


    /**
     * @param Hipay_Payment_Exception $e
     * @return array
     */
    protected function handlePaymentError(Hipay_Payment_Exception $e)
    {
        wc_add_notice(
            $e->getMessage(),
            'error'
        );

        $this->logs->logException($e);

        return array(
            'result' => $e->getType(),
            'redirect' => $e->getRedirectUrl(),
        );
    }

    /**
     * @param $template
     * @param $type
     * @param array $args
     */
    public function process_template($template, $type, $args = array())
    {
        extract($args);
        $file = WC_HIPAYENTERPRISE_PATH . 'includes/' . $type . '/template/' . $template;
        include $file;
    }

    /**
     * @param int $order_id
     * @param null $amount
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function process_refund($order_id, $amount = null, $reason = "")
    {
        try {
            $this->logs->logInfos(" # Process Refund for  " . $order_id);

            $redirect = $this->apiRequestHandler->handleMaintenance(
                \HiPay\Fullservice\Enum\Transaction\Operation::REFUND,
                array(
                    "order_id" => $order_id,
                    "amount" => (float)$amount
                )
            );

            return array(
                'result' => 'success',
                'redirect' => $redirect,
            );
        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    /**
     * Manual Capture
     *
     * @param $order_id
     * @param null $amount
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function process_capture($order_id, $amount = null, $reason = "")
    {
        try {
            $this->logs->logInfos(" # Process Manual Capture for  " . $order_id);

            $redirect = $this->apiRequestHandler->handleMaintenance(
                \HiPay\Fullservice\Enum\Transaction\Operation::CAPTURE,
                array(
                    "order_id" => $order_id,
                    "amount" => (float)$amount
                )
            );

            $this->logs->logInfos(" # End Process Manual Capture for  " . $order_id);
            return array(
                'result' => 'success',
                'redirect' => $redirect,
            );
        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    /**
     * Order cancel
     *
     * @param $order_id
     * @param null $amount
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function process_cancel($orderId)
    {
        try {
            $this->logs->logInfos(" # Process Cancel for  " . $orderId);

            $order = wc_get_order($orderId);

            $redirect = $this->apiRequestHandler->handleMaintenance(
                \HiPay\Fullservice\Enum\Transaction\Operation::CANCEL,
                array(
                    "order_id" => $orderId,
                    "transaction_reference" => $order->get_transaction_id(),
                )
            );

            $this->logs->logInfos(" # End Process Cancel for  " . $orderId);
            return array(
                'result' => 'success',
                'redirect' => $redirect,
            );
        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    /**
     * Get cached payment products
     *
     * @param string $paymentProduct Payment product code
     * @return array
     */
    protected function getCachedPaymentProducts($paymentProduct)
    {
        $cacheKey = sprintf(
            'hipay_payment_products_%s_%s',
            $paymentProduct,
            $this->sandbox ? 'sandbox' : 'production'
        );

        $cachedData = get_transient($cacheKey);

        if (!empty($cachedData) && is_array($cachedData)) {
            $this->logs->logInfos("Retrieved payment products from cache for: " . $paymentProduct);
            return $cachedData;
        }

        try {
            $products = $this->apiRequestHandler->getApi()->requestAvailablePayment([
                'payment_product' => $paymentProduct,
                'with_options' => true
            ]);

            $productsArray = [];
            foreach ($products as $product) {
                if ($product->getCode() === $paymentProduct && !empty($product->getOptions())) {
                    $productsArray[] = [
                        'code' => $product->getCode(),
                        'options' => $product->getOptions()
                    ];
                }
            }

            if (!empty($productsArray)) {
                set_transient($cacheKey, $productsArray, self::CACHE_DURATION);
                $this->logs->logInfos("Cached payment products for: " . $paymentProduct);
            }

            return $productsArray;

        } catch (Exception $e) {
            $this->logs->logException($e);
            return [];
        }
    }

    /**
     * Get PayPal options (now using unified caching)
     */
    protected function getCachedPaypalOptions()
    {
        $products = $this->getCachedPaymentProducts('paypal');

        if (!empty($products)) {
            foreach ($products as $product) {
                if ($product['code'] === 'paypal') {
                    return $product['options'];
                }
            }
        }

        return [];
    }

}
