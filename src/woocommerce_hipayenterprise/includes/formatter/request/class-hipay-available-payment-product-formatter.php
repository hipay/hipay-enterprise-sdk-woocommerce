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
class Hipay_Available_Payment_Product_Formatter extends Hipay_Api_Formatter_Abstact
{
    /**
     * @var array
     */
    private $params;

    /**
     * Hipay_Available_Payment_Product_Formatter constructor.
     * @param $plugin
     * @param $params
     */
    public function __construct($plugin, $params)
    {
        parent::__construct($plugin, $params);
        $this->params = $params;
    }

    /**
     * Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\AvailablePaymentProductRequest
     */
    public function generate()
    {
        $paymentProduct = new \HiPay\Fullservice\Gateway\Request\Info\AvailablePaymentProductRequest();

        $this->mapRequest($paymentProduct);

        return $paymentProduct;
    }

    /**
     * Map request
     *
     * @param type $paymentProduct
     * @return mixed|void
     */
    public function mapRequest(&$paymentProduct)
    {
        parent::mapRequest($paymentProduct);
        $paymentProduct->payment_product = $this->params["payment_product"];
        $paymentProduct->with_options = $this->params["with_options"];
    }
}
