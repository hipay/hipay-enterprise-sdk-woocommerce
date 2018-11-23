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
class Hipay_Direct_Post_Formatter extends Hipay_Request_Formatter_Abstract
{
    /**
     * @var
     */
    private $paymentProduct;

    /**
     * @var
     */
    private $paymentMethod;

    /**
     * Hipay_Direct_Post_Formatter constructor.
     * @param $plugin
     * @param $params
     * @param bool $order
     */
    public function __construct($plugin, $params, $order = false)
    {
        parent::__construct($plugin, $params, $order);
        $this->paymentProduct = $params["paymentProduct"];
        $this->paymentMethod = $params["paymentMethod"];
    }

    /**
     * Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    public function generate()
    {
        $orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();

        $this->mapRequest($orderRequest);

        return $orderRequest;
    }

    /**
     * Map order
     *
     * @param $orderRequest
     */
    protected function mapRequest(&$orderRequest)
    {
        parent::mapRequest($orderRequest);

        $orderRequest->payment_product = $this->paymentProduct;
        $orderRequest->paymentMethod = $this->paymentMethod;
    }
}