<?php

use HiPay\Fullservice\Enum\Transaction\ECI;

abstract class Hipay_Request_Formatter_Abstract extends Hipay_Api_Formatter_Abstact
{
    protected $params;

    public function __construct($plugin, $params, $order = false)
    {
        parent::__construct($plugin, $order);
        $this->params = $params;
    }

    /**
     * Map Request (Hosted or direct Post)
     *
     * @param type $order
     */
    protected function mapRequest(&$orderRequest)
    {
        $orderRequest->orderid = $this->order->id;
        if ($this->settings["payment"]["global"]["capture_mode"] === CaptureMode::AUTOMATIC) {
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
        $orderRequest->cancel_url = $this->order->get_cancel_order_url_raw();
        $orderRequest->notify_url = $this->getCallbackUrl();
        $orderRequest->source = $this->getRequestSource();

        $orderRequest->customerBillingInfo = $this->getCustomerBillingInfo();
        $orderRequest->customerShippingInfo = $this->getCustomerShippingInfo();

        $orderRequest->firstname = $this->order->get_billing_first_name();
        $orderRequest->lastname = $this->order->get_billing_last_name();
        $orderRequest->email = $this->order->get_billing_email();
        //$order->cid = (int)$this->customer->id;
        $orderRequest->ipaddr = $_SERVER ['REMOTE_ADDR'];
        $orderRequest->language =  $orderRequest->language = get_locale();
        $orderRequest->http_user_agent = $_SERVER ['HTTP_USER_AGENT'];

        //        $order->basket = $this->params["basket"];
//        $order->delivery_information = $this->params["delivery_informations"];
//        $order->authentication_indicator = $this->params["authentication_indicator"];
        //$this->setCustomData($orderRequest, $this->cart, $this->params);
    }

    /**
     * @return string
     */
    private function getCallbackUrl()
    {
        return site_url() . '/wc-api/WC_HipayEnterprise/?order=' . $this->order->id;
    }

    private function getRequestSource()
    {
        $source = array(
            "source" => "CMS",
            "brand" => "Woocommerce",
            //"brand_version" => _PS_VERSION_,
            "integration_version" =>  $this->plugin->plugin_version
        );

        return json_encode($source);
    }

    /**
     * Return welll formed description
     *
     * @param type $order
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
        $customerBillingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest();
        $customerBillingInfo->firstname = $this->order->get_billing_first_name();
        $customerBillingInfo->lastname = $this->order->get_billing_last_name();
        $customerBillingInfo->email = $this->order->get_billing_email();
        $customerBillingInfo->country = $this->order->get_billing_country();
        $customerBillingInfo->streetaddress = $this->order->get_billing_address_1();
        $customerBillingInfo->streetaddress2 = $this->order->get_billing_address_2();
        $customerBillingInfo->city = $this->order->get_billing_city();
        $customerBillingInfo->state = $this->order->get_billing_state();
        $customerBillingInfo->zipcode = $this->order->get_billing_postcode();

        //Todo use specific formatter
        //$billingInfo = new CustomerBillingInfoFormatter($this->module, $this->cart, $this->params["method"]);

        return $customerBillingInfo;
    }

    /**
     * return mapped customer shipping informations
     * @return \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest
     */
    private function getCustomerShippingInfo()
    {
        $customerShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest();
        $customerShippingInfo->firstname = $this->order->get_shipping_first_name();
        $customerShippingInfo->lastname = $this->order->get_shipping_last_name();
        $customerShippingInfo->country = $this->order->get_shipping_country();
        $customerShippingInfo->streetaddress = $this->order->get_shipping_address_1();
        $customerShippingInfo->streetaddress2 = $this->order->get_shipping_address_2();
        $customerShippingInfo->city = $this->order->get_shipping_city();
        $customerShippingInfo->state = $this->order->get_shipping_state();
        $customerShippingInfo->zipcode = $this->order->get_shipping_postcode();

        return $customerShippingInfo;
    }
}
