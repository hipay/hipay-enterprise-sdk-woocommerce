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
class Hipay_Customer_Billing_Info_Formatter implements Hipay_Api_Formatter
{

    private $payment_product;

    protected $order;

    /**
     * Hipay_Customer_Billing_Info_Formatter constructor.
     * @param $order
     * @param $payment_product
     */
    public function __construct($order, $payment_product)
    {
        $this->order = $order;
        $this->payment_product = $payment_product;
    }

    /**
     * return mapped customer billing information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest|mixed
     * @throws Hipay_Payment_Exception
     */
    public function generate()
    {
        $customerBillingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest();

        $this->mapRequest($customerBillingInfo);

        return $customerBillingInfo;
    }

    /**
     * Map billing information to request fields (Hpayment Post)
     *
     * @param $customerBillingInfo
     * @return mixed|void
     * @throws Hipay_Payment_Exception
     */
    public function mapRequest(&$customerBillingInfo)
    {
        $customerBillingInfo->firstname = $this->order->get_billing_first_name();
        $customerBillingInfo->lastname = $this->order->get_billing_last_name();
        $customerBillingInfo->email = $this->order->get_billing_email();
        $customerBillingInfo->country = $this->order->get_billing_country();
        $customerBillingInfo->streetaddress = $this->order->get_billing_address_1();
        $customerBillingInfo->streetaddress2 = $this->order->get_billing_address_2();
        $customerBillingInfo->city = $this->order->get_billing_city();
        $customerBillingInfo->state = $this->order->get_billing_state();
        $customerBillingInfo->zipcode = $this->order->get_billing_postcode();
        $customerBillingInfo->phone = $this->order->get_billing_phone();
        $customerBillingInfo->gender = 'U';


        if ($this->payment_product == 'klarnainvoice') {
            $customerBillingInfo->gender = 'F';
            $customerBillingInfo->house_number = 1;
            $customerBillingInfo->birthdate = '19700101';
        }

        if ($this->payment_product == 'bnpp-3xcb' || $this->payment_product == 'bnpp-4xcb') {
            $customerBillingInfo->phone = preg_replace('/^(\+33)|(33)/', '0', $customerBillingInfo->phone);
        }

        if (
            preg_match('/^[0-9]{1}xcb(.*)/', $this->payment_product) &&
            !preg_match('"(0|\\+33|0033)[1-9][0-9]{8}"', $customerBillingInfo->phone)
        ) {
            throw new Hipay_Payment_Exception(
                __(
                    "Wrong phone number format, Facily Pay require a valid french phone number (0123465789|+33123465789).",
                    "hipayenterprise"
                ),
                '',
                "fail"
            );
        }
    }
}
