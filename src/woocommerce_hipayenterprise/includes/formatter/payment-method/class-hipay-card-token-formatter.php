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
class Hipay_Card_Token_Formatter implements Hipay_Api_Formatter
{

    private $cardToken;

    private $authenticationIndicator;

    /**
     * Hipay_Card_Token_Formatter constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->cardToken = $params["cardtoken"];
        $this->authenticationIndicator = $params['authentication_indicator'];
    }

    /**
     *  Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod
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
    public function mapRequest(&$cardTokenRequest)
    {
        $cardTokenRequest->cardtoken = $this->cardToken;
        $cardTokenRequest->authentication_indicator = $this->authenticationIndicator;
    }
}
