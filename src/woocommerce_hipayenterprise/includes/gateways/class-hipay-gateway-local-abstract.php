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
class Hipay_Gateway_Local_Abstract extends Hipay_Gateway_Abstract
{

    public function __construct()
    {
        $this->supports = array('products');
        $this->has_fields = true;
        parent::__construct();
        $this->title = $this->confHelper->getLocalPayment(
            $this->paymentProduct
        )["displayName"][Hipay_Helper::getLanguage()];
        $this->init_form_fields();
        $this->init_settings();

        if ($this->isAvailable() && is_page() && is_checkout() && !is_order_received_page()) {

            wp_enqueue_script(
                'hipay-js-form-input-control',
                plugins_url('/assets/js/frontend/form-input-control.js', WC_HIPAYENTERPRISE_BASE_FILE),
                array(),
                'all',
                false
            );
        }

    }

    public function isAvailable()
    {
        return ('yes' === $this->enabled);
    }

    public function payment_fields()
    {
        if (empty($this->confHelper->getLocalPayment($this->paymentProduct)["additionalFields"])) {
            _e(
                'You will be redirected to an external payment page.' .
                ' Please do not refresh the page during the process.',
                "hipayenterprise"
            );
        } else {
            $this->process_template(
                'local-payment.php',
                'frontend',
                array(
                    'localPaymentName' => $this->paymentProduct,
                    'additionalFields' => $this->confHelper->getLocalPayment($this->paymentProduct)["additionalFields"]
                )
            );
        }
    }

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

    /**
     * @return false|string
     */
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

    /**
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        try {
            $this->logs->logInfos(" # Process Payment for  " . $order_id);

            $method = $this->confHelper->getLocalPayment($this->paymentProduct);

            $params = array(
                "order_id" => $order_id,
                "paymentProduct" => $this->paymentProduct
            );

            foreach ($method["additionalFields"]["formFields"] as $name => $field) {
                $params[$name] = Hipay_Helper::getPostData($this->paymentProduct . '-' . $name);
            }

            $redirectUrl = $this->apiRequestHandler->handleLocalPayment($params);

            return array(
                'result' => 'success',
                'redirect' => $redirectUrl,
            );

        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }
}
