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

use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo;
use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo;

use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Customer as CustomerInfo;
use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Purchase as PurchaseInfo;
use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Payment as PaymentInfo;
use \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo\Shipping as ShippingInfo;
use HiPay\Fullservice\Enum\ThreeDSTwo\NameIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\SuspiciousActivity;


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
class Hipay_Account_Info_Formatter extends Hipay_Api_Formatter_Abstact
{

    /**
     * @var WC_Order
     */
    protected $order;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var WC_Customer
     */
    protected $customer;


    /**
     * Hipay_Account_Info_Formatter constructor.
     *
     * @param $order
     * @param $params
     */
    public function __construct($order, $params)
    {
        $this->order = $order;
        $this->params = $params;
        $this->customer = null;

        if (is_user_logged_in()) {
            $this->customer = new WC_Customer(get_current_user_id());
        }
    }

    /**
     *
     * @return AccountInfo
     *
     * @throws Hipay_Payment_Exception
     */
    public function generate()
    {
        $accountInfo = new HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\AccountInfo();

        $this->mapRequest($accountInfo);

        return $accountInfo;
    }

    /**
     * @param AccountInfo $accountInfo
     * @return mixed|void
     * @throws Exception
     */
    public function mapRequest(&$accountInfo)
    {
        $accountInfo->customer = $this->getCustomerInfo();
        $accountInfo->purchase = $this->getPurchaseInfo();
        $accountInfo->payment = $this->getPaymentInfo();
        $accountInfo->shipping = $this->getShippingInfo();
    }

    /**
     * @return ShippingInfo
     */
    private function getShippingInfo()
    {
        $shippingInfo = new ShippingInfo();
        if ($this->customer !== null) {
            // #### SHIPPING USED DATE #### ////
            $shippingAddress = !empty($this->order->get_shipping_first_name()) ? $this->order->get_address('shipping') :
                $this->order->get_address('billing');

            $firstOrderWithShippingAddress = Hipay_Threeds_Helper::getFirstOrderWithShippingAddress($shippingAddress);

            if (!empty($firstOrderWithShippingAddress)) {
                $shippingInfo->shipping_used_date = (int)$firstOrderWithShippingAddress[0]->get_date_created()->format('Ymd');
            }
        }

        // #### NAME INDICATOR #### ////
        $billing = strtoupper($this->order->get_billing_first_name() . $this->order->get_billing_last_name());
        $shipping = strtoupper($this->order->get_shipping_first_name() . $this->order->get_shipping_last_name());

        $shippingInfo->name_indicator = NameIndicator::DIFFERENT;

        if ($shipping === "" || $shipping === $billing) {
            $shippingInfo->name_indicator = NameIndicator::IDENTICAL;
        }

        return $shippingInfo;
    }

    /**
     * @return CustomerInfo
     */
    private function getCustomerInfo()
    {
        $customerInfo = new CustomerInfo();
        if ($this->customer !== null) {
            $customerInfo->account_change = (int)date('Ymd', strtotime($this->customer->get_date_modified()));
            $customerInfo->opening_account_date = (int)date('Ymd', strtotime($this->customer->get_date_created()));
        }

        return $customerInfo;
    }

    /**
     * @return PurchaseInfo
     * @throws Exception
     */
    private function getPurchaseInfo()
    {
        $purchaseInfo = new PurchaseInfo();

        if ($this->customer !== null) {
            $sixMonthAgo = new \DateTime('6 months ago');
            $sixMonthAgo = $sixMonthAgo->format('Y-m-d');
            $twentyFourHoursAgo = new \DateTime('24 hours ago');
            $twentyFourHoursAgo = $twentyFourHoursAgo->format('Y-m-d H:i:s');
            $oneYearAgo = new \DateTime('1 years ago');
            $oneYearAgo = $oneYearAgo->format('Y-m-d H:i:s');

            $purchaseInfo->count = count(
                    Hipay_Threeds_Helper::getOrdersFromDate(
                        $this->customer->get_id(),
                        $sixMonthAgo)
                ) - 1;

            $purchaseInfo->card_stored_24h = Hipay_Transactions_Helper::nbAttemptCreateCard(
                $this->customer->get_id(),
                $twentyFourHoursAgo
            );

            $purchaseInfo->payment_attempts_24h = Hipay_Transactions_Helper::getNbPaymentAttempt(
                $this->customer->get_id(),
                $twentyFourHoursAgo,
                $this->cardPaymentProduct
            );

            $purchaseInfo->payment_attempts_1y = Hipay_Transactions_Helper::getNbPaymentAttempt(
                $this->customer->get_id(),
                $oneYearAgo,
                $this->cardPaymentProduct
            );
        }
        return $purchaseInfo;
    }

    /**
     * @return PaymentInfo
     * @throws Exception
     */
    private function getPaymentInfo()
    {
        $paymentInfo = new PaymentInfo();

        if ($this->customer !== null && isset($this->params["oneClick"]) && $this->params["oneClick"] && !empty($this->params["cardtoken"])) {

            $userToken = Hipay_Token_Helper::getToken(
                $this->customer->get_id(),
                $this->params["cardtoken"]
            );

            if (!empty($userToken->get_date_created())) {
                $paymentInfo->enrollment_date = (int)$userToken->get_date_created();
            }
        }
        return $paymentInfo;
    }
}
