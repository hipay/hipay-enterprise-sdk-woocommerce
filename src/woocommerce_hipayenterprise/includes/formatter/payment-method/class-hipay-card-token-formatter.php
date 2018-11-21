<?php

if (!defined('ABSPATH')) {
    exit;
}

class Hipay_Card_Token_Formatter
{

    private $cardToken;

    private $authenticationIndicator;

    public function __construct($plugin, $params, $order = false)
    {
        $this->cardToken = $params["cardtoken"];
        $this->authenticationIndicator = $params['authentication_indicator'];
    }

    /**
     * Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    public function generate()
    {
        $cardTokenRequest = new \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod();

        $this->mapRequest($cardTokenRequest);

        return $cardTokenRequest;
    }

    /**
     * Map order
     *
     * @param $orderRequest
     */
    protected function mapRequest(&$cardTokenRequest)
    {
        $cardTokenRequest->cardtoken = $this->cardToken;
        $cardTokenRequest->authentication_indicator = $this->authenticationIndicator;
    }
}
