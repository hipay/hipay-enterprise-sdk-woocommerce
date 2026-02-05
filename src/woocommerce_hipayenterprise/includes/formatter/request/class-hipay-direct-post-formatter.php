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
class Hipay_Direct_Post_Formatter extends Hipay_Order_Request_Abstract
{
    /**
     * @var
     */
    private $paymentProduct;

    /**
     * @var
     */
    private $paymentMethod;

    /**
     * @var
     */
    private $cardHolder;

    /**
     * Hipay_Direct_Post_Formatter constructor.
     * @param $plugin
     * @param $params
     * @param bool $order
     */
    public function __construct($plugin, $params, $order = false)
    {
        parent::__construct($plugin, $params, $order);
        $this->paymentProduct = $params["paymentProduct"];
        $this->paymentMethod = $params["paymentMethod"];
        $this->cardHolder = isset($params["card_holder"]) ? $params["card_holder"] : '';
    }

    /**
     * Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest|mixed
     * @throws Hipay_Payment_Exception
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
     * @return mixed|void
     * @throws Hipay_Payment_Exception
     */
    public function mapRequest(&$orderRequest)
    {
        parent::mapRequest($orderRequest);

        // CRITICAL: Only set payment_product if it has a valid value
        // When using card tokens, the HiPay API can determine the payment product from the token
        if (!empty($this->paymentProduct)) {
            $orderRequest->payment_product = $this->paymentProduct;
        } else {
            // Set to null (not empty string!) so RequestSerializer skips it
            // RequestSerializer only includes scalar values (bool, int, float, string)
            // null is NOT scalar, so it won't be included in the API request
            error_log('HiPay: payment_product is empty, setting to null to omit from API request');
            $orderRequest->payment_product = null;
        }

        $orderRequest->paymentMethod = $this->paymentMethod;
        $orderRequest->device_fingerprint = $this->params["deviceFingerprint"];
        if (isset($this->params["provider_data"]) && !empty($this->params["provider_data"])) {
            $orderRequest->provider_data = $this->params["provider_data"];
        }
        $this->getCustomerNames($orderRequest);
    }

    /**
     * Get correct Names for transaction, must be equivalent to the card holder(only for Amex)
     *
     * @param $orderRequest
     */
    public function getCustomerNames(&$orderRequest)
    {
        if ($this->paymentProduct === "american-express") {
            $names = explode(' ', trim($this->cardHolder));
            $orderRequest->firstname = $names[0];
            $orderRequest->lastname = trim(preg_replace('/' . $names[0] . '/', "", $this->cardHolder, 1));
        }
    }
}
