<?php

if (!defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}

class Hipay_Gateway_Abstract extends WC_Payment_Gateway
{

    const TEXT_DOMAIN = "hipay_enterprise";

    public $logs;

    public $settingsHandler;

    public $confHelper;

    protected $apiRequestHandler;

    public function __construct()
    {
        if (is_admin()) {
            $plugin_data = get_plugin_data(__FILE__);
            $this->plugin_version = $plugin_data['Version'];
        }

        load_plugin_textdomain(self::TEXT_DOMAIN, false, basename(dirname(__FILE__)) . '/languages');

        $this->confHelper = new Hipay_Config($this);

        $this->confHelper->getConfigHipay();

        $this->logs = new Hipay_Log($this);

        $this->apiRequestHandler = new Hipay_Api_Request_Handler($this);

        $this->settingsHandler = new Hipay_Settings_Handler($this);

        $this->addActions();
    }

    /**
     * @return Hipay_Api
     */
    public function getApi()
    {
        return $this->apiRequestHandler->getApi();
    }

    public function addActions()
    {
        add_filter('woocommerce_available_payment_gateways', array($this, 'available_payment_gateways'));
        add_action('woocommerce_api_wc_hipayenterprise', array($this, 'check_callback_response'));
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array($this, 'process_admin_options')
        );
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_settings'));
    }

    public function check_callback_response()
    {
        $transactionReference = (isset($_POST["transaction_reference"])) ? $_POST["transaction_reference"] : '';

        if (!Hipay_Helper::checkSignature($this)) {
            $this->logs->logErrors("Notify : Signature is wrong for Transaction $transactionReference.");
            header('HTTP/1.1 403 Forbidden');
            die('Bad Callback initiated - signature');
        }

        try {
            $notification = new Hipay_Notification($this, $_POST);
            $notification->processTransaction();
        } catch (Exception $e) {
            header("HTTP/1.0 500 Internal server error");
        }
    }

    /**
     * @param $methods
     *
     * @return mixed
     */
    public function available_payment_gateways($available_gateways)
    {
        global $woocommerce;

        if (isset($woocommerce->cart)) {
            foreach ($available_gateways as $id => $gateway) {
                if ($id == "hipayenterprise"
                    && !$gateway->isAvailableForCurrentCart()) {
                    unset($available_gateways [$id]);
                }
            }
        }

        return $available_gateways;
    }

    /**
     * Check if payment method is available for current cart
     *
     * TODO Utiliser la methode getActivatedPaymentByCountryAndCurrency
     * @return boolean
     */
    public function isAvailableForCurrentCart()
    {
        global $woocommerce;
        $settingsCreditCard = $this->confHelper->getPaymentCreditCard();
        $cartTotals = $woocommerce->cart->get_totals();
        foreach ($settingsCreditCard as $card => $conf) {
            if ($conf["activated"]
                && in_array(get_woocommerce_currency(), $conf["currencies"])
                && in_array($woocommerce->customer->get_billing_country(), $conf["countries"])
                && Hipay_Helper::isInAuthorizedAmount($conf, $cartTotals["total"])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $template
     * @param array $args
     */
    public function process_template($template, $type, $args = array())
    {
        extract($args);
        $file = WC_HIPAYENTERPRISE_PATH . 'includes/' . $type . '/template/' . $template;
        include $file;
    }
}
