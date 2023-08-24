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
class Hipay_Hosted_Payment_Formatter extends Hipay_Order_Request_Abstract
{
    /**
     * @var string
     */
    protected $productList;

    /**
     * @var boolean
     */
    protected $iframe;

    /**
     * Hipay_Hosted_Payment_Formatter constructor.
     * @param $plugin
     * @param $params
     * @param bool $order
     */
    public function __construct($plugin, $params, $order = false)
    {
        parent::__construct($plugin, $params, $order);
        $this->iframe = $params["iframe"];
        $this->productList = $params["productlist"];
    }

    /**
     * Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest
     */
    public function generate()
    {
        $orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest();

        $this->mapRequest($orderRequest);

        return $orderRequest;
    }

    /**
     * Map order
     *
     * @param type $orderRequest
     * @return mixed|void
     */
    public function mapRequest(&$orderRequest)
    {
        parent::mapRequest($orderRequest);

        $orderRequest->template = (!$this->iframe) ? "basic-js" : "iframe-js";
        $orderRequest->css = $this->plugin->confHelper->getPaymentGlobal()["css_url"];
        $orderRequest->display_selector = $this->plugin->confHelper->getPaymentGlobal()["display_card_selector"];
        $orderRequest->payment_product_list = $this->productList;
        $orderRequest->payment_product_category_list = '';
        $orderRequest->multi_use = isset($this->params["createOneClick"]) && $this->params["createOneClick"];
        $orderRequest->display_cancel_button = $this->plugin->confHelper->getPaymentGlobal()["display_cancel_button"];
    }
}
