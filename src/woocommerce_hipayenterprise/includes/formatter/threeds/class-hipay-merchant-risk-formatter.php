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

use HiPay\Fullservice\Enum\ThreeDSTwo\DeliveryTimeFrame;
use  HiPay\Fullservice\Enum\ThreeDSTwo\ShippingIndicator;
use HiPay\Fullservice\Enum\ThreeDSTwo\PurchaseIndicator;
use \HiPay\Fullservice\Enum\ThreeDSTwo\ReorderIndicator;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Merchant_Risk_Formatter extends Hipay_Api_Formatter_Abstact
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
     * Hipay_Browser_Info_Formatter constructor.
     *
     * @param $order
     * @param $params
     */
    public function __construct($order, $params)
    {
        $this->order = $order;
        $this->params = $params;
    }

    /**
     *
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement
     *
     * @throws Hipay_Payment_Exception
     */
    public function generate()
    {
        $merchantRiskStatement = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement();

        $this->mapRequest($merchantRiskStatement);

        return $merchantRiskStatement;
    }


    /**
     * Map Merchant risk statement
     *
     * @param \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\MerchantRiskStatement $merchantRiskStatement
     *
     * @throws Hipay_Payment_Exception
     */
    public function mapRequest(&$merchantRiskStatement)
    {
        if ($this->containsVirtualItem()) {
            $merchantRiskStatement->email_delivery_address = $this->order->get_billing_email();
            $merchantRiskStatement->delivery_time_frame = DeliveryTimeFrame::ELECTRONIC_DELIVERY;
        }

        $merchantRiskStatement->purchase_indicator = $this->getPurchaseIndicator();

        if (is_user_logged_in()) {
            $merchantRiskStatement->reorder_indicator = $this->isCartAlreadyOrdered();
        }

        $merchantRiskStatement->shipping_indicator = $this->getShippingIndicator();
    }

    /**
     * @return mixed
     */
    private function isCartAlreadyOrdered()
    {
        $carts = array();
        $cartItems = WC()->cart->get_cart_contents();

        foreach ($cartItems as $key => $value) {
            $carts[$value["product_id"]] = $value["quantity"];
        }

        if (Hipay_Threeds_Helper::existsSameOrder(get_current_user_id(), $this->order->get_id(), $carts)) {
            return ReorderIndicator::REORDERED;
        }

        return ReorderIndicator::FIRST_TIME_ORDERED;
    }


    /**
     * Check merchandise availability
     *
     * @return int \HiPay\Fullservice\Enum\ThreeDSTwo\PurchaseIndicator
     */
    private function getPurchaseIndicator()
    {
        $cartItems = WC()->cart->get_cart_contents();
        foreach ($cartItems as $key => $value) {
            $product = wc_get_product($value["product_id"]);
            if ('no' !== $product->get_backorders()) {
                return \HiPay\Fullservice\Enum\ThreeDSTwo\PurchaseIndicator::FUTURE_AVAILABILITY;
            }
        }

        return \HiPay\Fullservice\Enum\ThreeDSTwo\PurchaseIndicator::MERCHANDISE_AVAILABLE;
    }


    /**
     *  Check if current cart contains virtual or downloadable product
     *
     * @return bool
     */
    private function containsVirtualItem()
    {
        $cartItems = WC()->cart->get_cart_contents();
        foreach ($cartItems as $key => $value) {
            $product = wc_get_product($value["product_id"]);
            if ($product->get_virtual() || $product->get_downloadable()) {
                return true;
            }
        }
        return false;
    }

    /**
     *  Check if current cart contains some virtual or downloadable product
     *
     * @return bool
     */
    private function containsOnlyVirtualItem()
    {
        $cartItems = WC()->cart->get_cart_contents();
        foreach ($cartItems as $key => $value) {
            $product = wc_get_product($value["product_id"]);
            if ($product->get_virtual() || $product->get_downloadable()) {
                continue;
            }
            return false;
        }
        return true;
    }

    /**
     * Get the shipping indicator
     *
     * @return int ShippingIndicator
     */
    private function getShippingIndicator()
    {
        if ($this->containsOnlyVirtualItem()) {
            return ShippingIndicator::DIGITAL_GOODS;
        }

        if (!Hipay_Threeds_Helper::areDifferentAddresses($this->order->data['shipping'], $this->order->data['billing'])) {
            return ShippingIndicator::SHIP_TO_CARDHOLDER_BILLING_ADDRESS;
        } elseif (!is_user_logged_in()) {
            return ShippingIndicator::SHIP_TO_DIFFERENT_ADDRESS;
        } elseif (get_current_user_id() > 0 && Hipay_Threeds_Helper::isVerifiedAddress($this->order->data['shipping'])) {
            return ShippingIndicator::SHIP_TO_VERIFIED_ADDRESS;
        }

        return ShippingIndicator::SHIP_TO_DIFFERENT_ADDRESS;
    }
}
