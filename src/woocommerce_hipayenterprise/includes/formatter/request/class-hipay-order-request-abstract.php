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

use HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo;

if (!defined('ABSPATH')) {
    exit;
}

use \HiPay\Fullservice\Enum\ThreeDSTwo\DeviceChannel;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
abstract class Hipay_Order_Request_Abstract extends Hipay_Api_Formatter_Abstact
{

    protected $params;

    /**
     * Hipay_Request_Formatter_Abstract constructor.
     * @param $plugin
     * @param $params
     * @param bool $order
     */
    public function __construct($plugin, $params, $order = false)
    {
        parent::__construct($plugin, $order);
        $this->params = $params;
    }

    /**
     * Map Request (Hosted or direct Post)
     *
     * @param $orderRequest
     * @return mixed|void
     * @throws Hipay_Payment_Exception
     */
    public function mapRequest(&$orderRequest)
    {
        parent::mapRequest($orderRequest);
        $this->setCustomData($orderRequest, $this->order, $this->params);

        $product = $this->plugin->getPaymentProduct();
        $confProduct = $this->plugin->confHelper->getLocalPayment($product);

        if ($product === Gateway_Hipay::CREDIT_CARD_PAYMENT_PRODUCT) {
            $orderRequest->browser_info = $this->getBrowserInfo();
            $orderRequest->previous_auth_info = $this->getPreviousAuthInfo();
            $orderRequest->merchant_risk_statement = $this->getMerchantRiskStatement();
            $orderRequest->account_info = $this->getAccountInfo();
            $orderRequest->device_channel = DeviceChannel::BROWSER;
        }

        $orderRequest = apply_filters('hipay_wc_before_request', $orderRequest, $this->order);

        $orderRequest->orderid = $this->order->get_id() . '-' . time();

        if ($this->plugin->confHelper->getPaymentGlobal()["capture_mode"] === CaptureMode::AUTOMATIC
            || $this->params["forceSalesMode"]
        ) {
            $orderRequest->operation = "Sale";
        } else {
            $orderRequest->operation = "Authorization";
        }

        $orderRequest->description = $this->generateDescription();
        $orderRequest->amount = $this->order->get_total();
        $orderRequest->shipping = $this->order->get_total_shipping();
        $orderRequest->tax = $this->order->get_total_tax();
        $orderRequest->currency = $this->order->get_currency();
        $orderRequest->accept_url = $this->order->get_checkout_order_received_url();
        $orderRequest->decline_url = $this->order->get_cancel_order_url_raw();
        $orderRequest->pending_url = $this->order->get_checkout_order_received_url();
        $orderRequest->exception_url = $this->order->get_cancel_order_url_raw();

        if ((bool)$this->plugin->confHelper->getPaymentGlobal()["send_url_notification"]) {
            $orderRequest->notify_url = $this->getCallbackUrl();
        }

        $orderRequest->cancel_url = $this->order->get_cancel_order_url_raw();
        $orderRequest->customerBillingInfo = $this->getCustomerBillingInfo();
        $orderRequest->customerShippingInfo = $this->getCustomerShippingInfo();

        $orderRequest->firstname = $this->order->get_billing_first_name();
        $orderRequest->lastname = $this->order->get_billing_last_name();
        $orderRequest->email = $this->order->get_billing_email();
        $orderRequest->ipaddr = $this->order->get_customer_ip_address();
        $orderRequest->language = get_locale();
        $orderRequest->http_user_agent = $_SERVER ['HTTP_USER_AGENT'];
        $orderRequest->basket = $this->params["basket"];
        $orderRequest->delivery_information = $this->params["delivery_information"];
        $orderRequest->authentication_indicator = $this->params["authentication_indicator"];

        if (isset($confProduct['orderExpirationTime'])) {
            $orderRequest->expiration_limit = $confProduct['orderExpirationTime'];
        }

        if (isset($confProduct['merchantPromotion'])) {
            $orderRequest->payment_product_parameters = json_encode(
                array(
                    "merchantPromotion" => !empty($confProduct['merchantPromotion']) ?
                    $confProduct['merchantPromotion'] :
                    \HiPay\Fullservice\Helper\MerchantPromotionCalculator::calculate(
                        $product,
                        $orderRequest->amount
                    )
                )
            );
        }
    }

    /**
     *  Get Browser Information
     *
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo
     * @throws Hipay_Payment_Exception
     */
    private function getBrowserInfo()
    {
        $browserInfo = new Hipay_Browser_Info_Formatter($this->order, $this->params);
        return $browserInfo->generate();
    }

    /**
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement
     *
     * @throws Hipay_Payment_Exception
     */
    private function getMerchantRiskStatement()
    {
        $merchantRiskInfo = new Hipay_Merchant_Risk_Formatter($this->order, $this->params);
        return $merchantRiskInfo->generate();
    }

    /**
     *  Get Account Information
     *
     * @return PreviousAuthInfo
     *
     * @throws Hipay_Payment_Exception
     */
    private function getAccountInfo()
    {
        $accountInfo = new Hipay_Account_Info_Formatter($this->order, $this->params);
        return $accountInfo->generate();
    }

    /**
     *  Get Browser Information
     *
     * @return PreviousAuthInfo
     *
     * @throws Hipay_Payment_Exception
     */
    private function getPreviousAuthInfo()
    {
        $previousAuthInfo = new Hipay_Previous_Auth_Info_Formatter($this->order, $this->params);
        return $previousAuthInfo->generate();
    }


    /**
     * @return string
     */
    private function getCallbackUrl()
    {
        return site_url() . '/wc-api/WC_HipayEnterprise/';
    }

    /**
     * Return welll formed description
     *
     * @return string
     */
    protected function generateDescription()
    {
        $description = ''; // Initialize to blank
        $products = $this->order->get_items();
        foreach ($products as $product) {
            $description .= 'ref_' . $product ['product_id'] . ', ';
        }

        // If description exceeds 255 char, trim back to 255
        $max_length = 255;
        if (strlen($description) > $max_length) {
            $offset = ($max_length - 3) - strlen($description);
            $description = substr($description, 0, strrpos($description, ' ', $offset)) . '...';
        }

        return $description;
    }

    /**
     * Return mapped customer billing information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest|mixed
     * @throws Hipay_Payment_Exception
     */
    private function getCustomerBillingInfo()
    {

        $billingInfo = new Hipay_Customer_Billing_Info_Formatter(
            $this->order,
            (isset($this->params["paymentProduct"])) ? $this->params["paymentProduct"] : 0
        );

        return $billingInfo->generate();
    }

    /**
     * return mapped customer shipping information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    private function getCustomerShippingInfo()
    {
        $customerShippingInfo = new Hipay_Customer_Shipping_Info_Formatter($this->order);

        return $customerShippingInfo->generate();
    }
}
