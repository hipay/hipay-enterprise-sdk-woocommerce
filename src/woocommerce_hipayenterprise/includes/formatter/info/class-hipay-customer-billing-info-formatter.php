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

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

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

    private $logs;

    private $params;

    /**
     * Hipay_Customer_Billing_Info_Formatter constructor.
     * @param $order
     * @param $payment_product
     * @param $params
     */
    public function __construct($order, $payment_product, $params)
    {
        $this->order = $order;
        $this->payment_product = $payment_product;
        $this->params = $params;
        $this->logs = new Hipay_Log($this);
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

        // Check phone by country according to payment method
        $phoneExceptionMessage = 'The format of the phone number must match %s phone.';
        switch ($this->payment_product) {
            case 'mbway':
                $customerBillingInfo->phone = isset($this->params['phone']) ? $this->params['phone']: null;
                $this->checkPhone(
                    $customerBillingInfo,
                    'PT',
                    sprintf($phoneExceptionMessage, 'a Portuguese'),
                    false
                );
                break;
            case 'bnpp-3xcb':
            case 'bnpp-4xcb':
                $this->checkPhone($customerBillingInfo, 'FR', sprintf($phoneExceptionMessage, 'a French'));
                break;
            case '3xcb':
            case '3xcb-no-fees':
            case '4xcb':
            case '4xcb-no-fees':
                switch ($customerBillingInfo->country) {
                    case 'FR':
                        $this->checkPhone(
                            $customerBillingInfo,
                            $customerBillingInfo->country,
                            sprintf($phoneExceptionMessage, 'a French')
                        );
                        break;
                    case 'IT':
                        $this->checkPhone(
                            $customerBillingInfo,
                            $customerBillingInfo->country,
                            sprintf($phoneExceptionMessage, 'an Italian')
                        );
                        break;
                    case 'BE':
                        $this->checkPhone(
                            $customerBillingInfo,
                            $customerBillingInfo->country,
                            sprintf($phoneExceptionMessage, 'a Belgian')
                        );
                        break;
                    case 'PT':
                        $this->checkPhone(
                            $customerBillingInfo,
                            $customerBillingInfo->country,
                            sprintf($phoneExceptionMessage, 'a Portuguese')
                        );
                        break;
                }
                break;
            case 'credit-long':
                switch ($customerBillingInfo->country) {
                    case 'FR':
                        $this->checkPhone(
                            $customerBillingInfo,
                            $customerBillingInfo->country,
                            sprintf($phoneExceptionMessage, 'a French')
                        );
                        break;
                    case 'IT':
                        $this->checkPhone(
                            $customerBillingInfo,
                            $customerBillingInfo->country,
                            sprintf($phoneExceptionMessage, 'an Italian')
                        );
                        break;
                    case 'PT':
                        $this->checkPhone(
                            $customerBillingInfo,
                            $customerBillingInfo->country,
                            sprintf($phoneExceptionMessage, 'a Portuguese')
                        );
                        break;
                }
                break;
        }
    }

    /**
     * @param CustomerBillingInfoRequest $customerBillingInfo
     * @param string $country
     * @param string $exceptionMessage
     * @param string $applyFormat Apply phone format
     */
    private function checkPhone(&$customerBillingInfo, $country, $exceptionMessage, $applyFormat = true)
    {
        $localizedException = new Hipay_Payment_Exception(
            __($exceptionMessage, 'hipayenterprise'),
            '',
            "fail"
        );
    
        try {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneNumberUtil->parse($customerBillingInfo->phone, $country);
    
            if (!$phoneNumberUtil->isValidNumber($phoneNumber)) {
                throw $localizedException;
            }

            if ($applyFormat) {
                $customerBillingInfo->phone = $phoneNumberUtil->format($phoneNumber, PhoneNumberFormat::E164);
            }
        } catch (NumberParseException $e) {
            $this->logs->logErrors($e->getMessage());
            throw $localizedException;
        } catch (Exception $e) {
            $this->logs->logErrors($e->getMessage());
            throw $localizedException;
        }
    }
}
