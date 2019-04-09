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
abstract class Hipay_Api_Formatter_Abstact implements Hipay_Api_Formatter
{
    protected $plugin;

    protected $order;

    /**
     * Hipay_Api_Formatter_Abstact constructor.
     * @param $plugin
     * @param $order
     */
    public function __construct($plugin, $order)
    {
        $this->plugin = $plugin;
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    abstract public function generate();

    /**
     * map prestashop order information to request fields
     * (shared information between Hpayment, Iframe, Direct Post and Maintenance )
     * @param type $request
     */
    public function mapRequest(&$request)
    {
        $request->source = $this->getRequestSource();
    }

    protected function setCustomData(&$request, $order, $params)
    {
        $iframe = 0;
        if (isset($params["iframe"]) && $params["iframe"]) {
            $iframe = 1;
        }

        $paymentCode = "hipay_hosted";
        if (isset($this->params["paymentProduct"])) {
            $paymentCode = $this->params["paymentProduct"];
        }

        $shippingDescription = $order->get_shipping_method();

        $customDataHipay = array(
            "shipping_description" => $shippingDescription,
            "payment_code" => $paymentCode,
            "display_iframe" => $iframe,
        );

        if (isset($this->params["createOneClick"]) && $this->params["createOneClick"]) {
            $customDataHipay["createOneClick"] = true;
        }

        $customDataHipay = apply_filters('hipay_wc_request_custom_data', $customDataHipay, $order, $params);

        $request->custom_data = json_encode($customDataHipay);
    }

    /**
     * @return false|mixed|string|void
     */
    private function getRequestSource()
    {
        $source = array(
            "source" => "CMS",
            "brand" => "Woocommerce",
            "brand_version" => WC()->version,
            "integration_version" => WC_HIPAYENTERPRISE_VERSION
        );

        return json_encode($source);
    }
}
