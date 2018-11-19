<?php

if (!defined('ABSPATH')) {
    exit;
}

class Hipay_Direct_Post_Formatter extends Hipay_Request_Formatter_Abstract
{

    private $paymentProduct;

    public function __construct($plugin, $params, $order = false)
    {
        parent::__construct($plugin, $params, $order);
        $this->paymentProduct = $params["paymentProduct"];
    }

    /**
     * Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    public function generate()
    {
        $orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();

        $this->mapRequest($orderRequest);

        return $orderRequest;
    }

    /**
     * Map order
     *
     * @param $orderRequest
     */
    protected function mapRequest(&$orderRequest)
    {
        parent::mapRequest($orderRequest);

        $orderRequest->payment_product = $this->paymentProduct;
    }
}
