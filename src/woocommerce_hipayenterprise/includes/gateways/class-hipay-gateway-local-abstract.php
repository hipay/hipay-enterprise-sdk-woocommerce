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

        $methodConf = $this->confHelper->getLocalPayment($this->paymentProduct);

        if (!empty($methodConf["displayName"][Hipay_Helper::getLanguage()])) {
            $this->title = $methodConf["displayName"][Hipay_Helper::getLanguage()];
        } else {
            $this->title = $methodConf["displayName"]['en'];
        }

        if (isset($methodConf["logo"]) && file_exists(WC_HIPAYENTERPRISE_PATH_ASSETS . "local_payments_images/" . $methodConf["logo"])) {
            $this->icon = WC_HIPAYENTERPRISE_URL_ASSETS . "local_payments_images/" . $methodConf["logo"];
        }

        $this->init_form_fields();
        $this->init_settings();

        if ($methodConf["canManualCapture"]) {
            $this->supports[] = "captures";
        }

        if ($methodConf["canManualCapturePartially"]) {
            $this->supports[] = "partialCaptures";
        }

        if ($methodConf["canRefund"]) {
            $this->supports[] = "refunds";
        }

        if($this->isAvailable()) {
            add_action('wp_print_scripts', array($this, 'localize_scripts'), 5);
        }
    }

    public function isAvailable()
    {
        return 'yes' === $this->enabled;
    }

    public function payment_fields()
    {
        $this->process_template(
            'local-payment.php',
            'frontend',
            array(
                'localPaymentName' => $this->paymentProduct,
                'additionalFields' => $this->confHelper->getLocalPayment($this->paymentProduct)["additionalFields"]
            )
        );
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
            [
                'configurationPaymentMethod' => $this->confHelper->getLocalPayment($this->paymentProduct),
                'method' => $this->paymentProduct
            ]
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
                "paymentProduct" => Hipay_Helper::getPostData($this->paymentProduct.'-payment_product', $this->paymentProduct),
                "forceSalesMode" => $this->forceSalesMode(),
                "deviceFingerprint" => Hipay_Helper::getPostData($this->paymentProduct.'-device_fingerprint'),
                "phone" => json_decode(Hipay_Helper::getPostData($this->paymentProduct.'-phone'))
            );

            if (is_array($method["additionalFields"]["formFields"])) {
                foreach ($method["additionalFields"]["formFields"] as $name => $field) {
                    $params[$name] = Hipay_Helper::getPostData($this->paymentProduct . '-' . $name);
                }
            }

            $response = $this->apiRequestHandler->handleLocalPayment($params);

            return array(
                'result' => 'success',
                'redirect' => $response["redirectUrl"],
            );
        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    protected function forceSalesMode()
    {
        return !$this->confHelper->getLocalPayment($this->paymentProduct)["canManualCapture"];
    }

    /**
     * Get min and max amount by payment product
     *
     * @param $total
     * @param $technicalCode
     * @return bool
     */
    protected function getMinMaxByPaymentProduct($total, $technicalCode)
    {
        try {
            $products = $this->getCachedPaymentProducts($technicalCode);

            foreach ($products as $product) {
                if ($product['code'] === $technicalCode) {
                    $options = $product['options'];
                    $installments = substr($product['code'], -2, 1);
                    $minKey = "basketAmountMin{$installments}x";
                    $maxKey = "basketAmountMax{$installments}x";

                    if (isset($options[$minKey], $options[$maxKey])) {
                        return $total >= (float)$options[$minKey] && $total <= (float)$options[$maxKey];
                    }
                }
            }
        } catch (Exception $e) {
            $this->logs->logException($e);
        }

        return false;
    }

    protected function getOperatingMode()
    {
        return $this->confHelper->getPaymentGlobal()['operating_mode'];
    }

    public function localize_scripts()
    {
        if (is_page() &&
        (is_checkout() || is_add_payment_method_page()) &&
        !is_order_received_page()) {
            wp_localize_script(
                'hipay-js-front',
                'hipay_hosted_fields_data',
                array(
                    "amount" => WC()->cart->get_totals()["total"],
                )
            );
        }
    }
}
