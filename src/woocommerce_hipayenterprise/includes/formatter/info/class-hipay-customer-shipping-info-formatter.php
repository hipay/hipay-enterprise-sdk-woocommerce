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
class Hipay_Customer_Shipping_Info_Formatter implements Hipay_Api_Formatter
{

    protected $order;

    /**
     * Hipay_Customer_Shipping_Info_Formatter constructor.
     * @param $order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * return mapped customer shipping information
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    public function generate()
    {
        $customerShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest();

        $this->mapRequest($customerShippingInfo);

        return $customerShippingInfo;
    }

    /**
     * Map shipping information to request fields (Hpayment Post)
     *
     * @param \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest $customerShippingInfo
     */
    public function mapRequest(&$customerShippingInfo)
    {
        $shippingInfo = $this->getShippingInfo();

        $customerShippingInfo->shipto_firstname = $shippingInfo["shipto_firstname"];
        $customerShippingInfo->shipto_lastname = $shippingInfo["shipto_lastname"];
        $customerShippingInfo->shipto_country = $shippingInfo["shipto_country"];
        $customerShippingInfo->shipto_streetaddress = $shippingInfo["shipto_streetaddress"];
        $customerShippingInfo->shipto_streetaddress2 = $shippingInfo["shipto_streetaddress2"];
        $customerShippingInfo->shipto_city = $shippingInfo["shipto_city"];
        $customerShippingInfo->shipto_state = $shippingInfo["shipto_state"];
        $customerShippingInfo->shipto_zipcode = $shippingInfo["shipto_zipcode"];
        $customerShippingInfo->shipto_phone = $shippingInfo["shipto_phone"];
        $customerShippingInfo->shipto_gender = 'U';
        $customerShippingInfo->shipto_msisdn = '0000000000';
    }

    /**
     * @return array
     */
    private function getShippingInfo()
    {
        $shippingInfo = array();

        if (empty($this->order->get_shipping_first_name())) {
            $shippingInfo["shipto_firstname"] = $this->order->get_billing_first_name();
            $shippingInfo["shipto_lastname"] = $this->order->get_billing_last_name();
            $shippingInfo["shipto_country"] = $this->order->get_billing_country();
            $shippingInfo["shipto_streetaddress"] = $this->order->get_billing_address_1();
            $shippingInfo["shipto_streetaddress2"] = $this->order->get_billing_address_2();
            $shippingInfo["shipto_city"] = $this->order->get_billing_city();
            $shippingInfo["shipto_state"] = $this->order->get_billing_state();
            $shippingInfo["shipto_zipcode"] = $this->order->get_billing_postcode();
            $shippingInfo["shipto_phone"] = $this->order->get_billing_phone();
        } else {
            $shippingInfo["shipto_firstname"] = $this->order->get_shipping_first_name();
            $shippingInfo["shipto_lastname"] = $this->order->get_shipping_last_name();
            $shippingInfo["shipto_country"] = $this->order->get_shipping_country();
            $shippingInfo["shipto_streetaddress"] = $this->order->get_shipping_address_1();
            $shippingInfo["shipto_streetaddress2"] = $this->order->get_shipping_address_2();
            $shippingInfo["shipto_city"] = $this->order->get_shipping_city();
            $shippingInfo["shipto_state"] = $this->order->get_shipping_state();
            $shippingInfo["shipto_zipcode"] = $this->order->get_shipping_postcode();
            $shippingInfo["shipto_phone"] = $this->order->get_billing_phone();
        }

        return $shippingInfo;
    }
}
