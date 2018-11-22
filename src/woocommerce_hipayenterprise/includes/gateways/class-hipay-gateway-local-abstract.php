<?php

if (!defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}

class Hipay_Gateway_Local_Abstract extends Hipay_Gateway_Abstract
{

    protected $paymentProduct;

    public function admin_options()
    {
        parent::admin_options();
        $this->process_template(
            'admin-local-settings.php',
            'admin',
            array()
        );
    }

    /**
     * Initialise Gateway Settings Admin
     */
    public function init_form_fields()
    {
        $this->local = include(plugin_dir_path(__FILE__) . '../admin/settings/settings-local-method.php');
    }

    /**
     * Save Admin Settings
     */
    public function save_settings()
    {
        $settings = array();
        $this->settingsHandler->saveLocalPaymentSettings($settings, $this->paymentProduct);
        $this->confHelper->setConfigHiPay("payment", $settings, "local_payment");
    }

    public function generate_methods_local_payments_settings_html()
    {
        ob_start();
        $this->process_template(
            'admin-paymentlocal-settings.php',
            'admin',
            array(
                'configurationPaymentMethod' => $this->confHelper->getLocalPayment($this->paymentProduct),
                'method' => $this->paymentProduct
            )
        );

        return ob_get_clean();
    }

    public function process_payment($order_id)
    {
        try {
            $this->logs->logInfos(" # Process Payment for  " . $order_id);

            $redirectUrl = $this->apiRequestHandler->handleLocalPayment(
                array(
                    "order_id" => $order_id,
                    "paymentProduct" => $this->paymentProduct
                )
            );

            return array(
                'result' => 'success',
                'redirect' => $redirectUrl,
            );

        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }
}
