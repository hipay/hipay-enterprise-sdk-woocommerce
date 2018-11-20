<?php

if (!defined('ABSPATH')) {
    exit;
}

class Hipay_Customer_Shipping_Info_Formatter extends Hipay_Api_Formatter_Abstact
{

    /**
     * return mapped customer shipping informations
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    public function generate()
    {
        $customerShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest();

        $this->mapRequest($customerShippingInfo);

        return $customerShippingInfo;
    }

    /**
     * map prestashop shipping informations to request fields (Hpayment Post)
     * @param \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest $customerShippingInfo
     */
    protected function mapRequest(&$customerShippingInfo)
    {
        $customerShippingInfo->shipto_firstname = $this->order->get_shipping_first_name();
        $customerShippingInfo->shipto_lastname = $this->order->get_shipping_last_name();
        $customerShippingInfo->shipto_country = $this->order->get_shipping_country();
        $customerShippingInfo->shipto_streetaddress = $this->order->get_shipping_address_1();
        $customerShippingInfo->shipto_streetaddress2 = $this->order->get_shipping_address_2();
        $customerShippingInfo->shipto_city = $this->order->get_shipping_city();
        $customerShippingInfo->shipto_state = $this->order->get_shipping_state();
        $customerShippingInfo->shipto_zipcode = $this->order->get_shipping_postcode();
        $customerShippingInfo->shipto_phone = $this->order->get_billing_phone();
    }
}
