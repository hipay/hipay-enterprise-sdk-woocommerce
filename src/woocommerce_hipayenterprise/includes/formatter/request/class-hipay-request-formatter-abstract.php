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
abstract class Hipay_Request_Formatter_Abstract extends Hipay_Api_Formatter_Abstact
{
    /**
     * @var
     */
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
     * @param type $orderRequest
     */
    protected function mapRequest(&$orderRequest)
    {
        $orderRequest->orderid = $this->order->get_id() . '-' . time();
        if ($this->plugin->confHelper->getPaymentGlobal()["capture_mode"] === CaptureMode::AUTOMATIC) {
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
        $orderRequest->source = $this->getRequestSource();

        $orderRequest->customerBillingInfo = $this->getCustomerBillingInfo();
        $orderRequest->customerShippingInfo = $this->getCustomerShippingInfo();

        $orderRequest->firstname = $this->order->get_billing_first_name();
        $orderRequest->lastname = $this->order->get_billing_last_name();
        $orderRequest->email = $this->order->get_billing_email();
        $orderRequest->ipaddr = $_SERVER ['REMOTE_ADDR'];
        $orderRequest->language = $orderRequest->language = get_locale();
        $orderRequest->http_user_agent = $_SERVER ['HTTP_USER_AGENT'];
    }

    /**
     * @return string
     */
    private function getCallbackUrl()
    {
        return site_url() . '/wc-api/WC_HipayEnterprise/';
    }

    /**
     * @return false|mixed|string|void
     */
    private function getRequestSource()
    {
        $source = array(
            "source" => "CMS",
            "brand" => "Woocommerce",
            //"brand_version" => _PS_VERSION_,
            "integration_version" => $this->plugin->plugin_version
        );

        return json_encode($source);
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
     * Return mapped customer billing informations
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest
     */
    private function getCustomerBillingInfo()
    {

        $billingInfo = new Hipay_Customer_Billing_Info_Formatter(
            $this->plugin,
            $this->order,
            (isset($this->params["paymentProduct"])) ? $this->params["paymentProduct"] : false
        );

        return $billingInfo->generate();
    }

    /**
     * return mapped customer shipping informations
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    private function getCustomerShippingInfo()
    {
        $customerShippingInfo = new Hipay_Customer_Shipping_Info_Formatter($this->plugin, $this->order);

        return $customerShippingInfo->generate();
    }
}
