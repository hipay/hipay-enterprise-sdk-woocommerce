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

    protected $notifications;

    public function __construct()
    {
        if (is_admin()) {
            $plugin_data = get_plugin_data(__FILE__);
            $this->plugin_version = $plugin_data['Version'];
        }

        load_plugin_textdomain(self::TEXT_DOMAIN, false, basename(dirname(__FILE__)) . '/languages');

        $this->confHelper = new Hipay_Config();

        $this->confHelper->getConfigHipay();

        $this->logs = new Hipay_Log($this);

        $this->apiRequestHandler = new Hipay_Api_Request_Handler($this);

        $this->settingsHandler = new Hipay_Settings_Handler($this);

        if ( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '<=' ) ) {
            $this->notifications[] = __(sprintf('Your Woocommerce version (%s) is not compatible with HiPay module.Please upgrade to minimum version 3.0.0',WOOCOMMERCE_VERSION));
        }


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
        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array($this, 'process_admin_options')
        );
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_settings'));
    }



    /**
     * @param $methods
     *
     * @return mixed
     */
    public function available_payment_gateways($available_gateways)
    {
        if (isset(WC()->cart)) {
            foreach ($available_gateways as $id => $gateway) {
                if ($id == $this->id
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
     * @param $template
     * @param array $args
     */
    public function process_template($template, $type, $args = array())
    {
        extract($args);
        $file = WC_HIPAYENTERPRISE_PATH . 'includes/' . $type . '/template/' . $template;
        include $file;
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
}
