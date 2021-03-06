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
class Hipay_Threeds_Helper
{
    /**
     * Get last transaction ref from user
     * @param $customer_user_id
     * @param $currentOrderId
     * @return string|null
     * @throws Exception
     */
    public static function getLastOrderTransactionRef($customer_user_id, $currentOrderId)
    {
        $order_statuses = array('wc-on-hold', 'wc-processing', 'wc-completed');

        $query = new WC_Order_Query(
            array(
                'limit' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
                'customer_id' => $customer_user_id,
                'post_status' => $order_statuses,
                'exclude' => array($currentOrderId),
            )
        );

        $orders = $query->get_orders();

        foreach ($orders as $order) {
            if ($order->get_transaction_id() != "") {
                return $order->get_transaction_id();
            }
        }

        return null;
    }

    /**
     * @param $customer_user_id
     * @param $orderID
     * @param $carts
     * @return bool
     */
    public static function existsSameOrder($customer_user_id, $orderID, $carts)
    {
        $order_statuses = array('on-hold', 'processing', 'completed', 'refunded');
        $customerOrders = wc_get_orders(array(
            'exclude' => array($orderID),
            'customer_id' => $customer_user_id,
            'status' => $order_statuses,
            'orderby' => 'date',
            'order' => 'DESC',
        ));

        foreach ($customerOrders as $order) {
            $sameOrder = false;
            $items = $order->get_items('line_item');

            foreach ($items as $item) {
                if (isset($carts[$item->get_product_id()])
                    && $carts[$item->get_product_id()] == $item->get_quantity()) {
                    $sameOrder = true;
                } else {
                    $sameOrder = false;
                    break;
                }
            }

            if ($sameOrder) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $shipping
     * @param $billing
     * @return bool
     */
    public static function areDifferentAddresses($shipping, $billing)
    {
        $shippingSerialized = self::serializeAddress($shipping);
        $billingSerialized = self::serializeAddress($billing);

        return strcmp($shippingSerialized, $billingSerialized) !== 0;
    }

    /**
     * @param $address
     * @return string
     */
    private static function serializeAddress($address)
    {
        return serialize(
            array(
                'firstname' => $address['first_name'],
                'lastname' => $address['last_name'],
                'street' => $address['address_1'],
                'city' => $address['city'],
                'postcode' => $address['postcode'],
                'country' => $address['country'],
                'company' => $address['company'],
            )
        );
    }

    /**
     *  Check account adress and current adress
     *
     * @param $currentAddress
     * @return bool
     */
    public static function isVerifiedAddress($currentAddress)
    {
        $order = self::getFirstOrderWithShippingAddress($currentAddress);

        return !empty($order);
    }

    /**
     *
     * @param $customer_user_id
     * @param $date
     *
     * @return array
     * @throws Exception
     */
    public static function getOrdersFromDate($customer_user_id, $date)
    {
        $order_statuses = array('wc-on-hold', 'wc-processing', 'wc-completed');
        $query = new WC_Order_Query(
            array(
                'limit' => -1,
                'return' => 'ids',
                'customer_id' => $customer_user_id,
                'date_created' => '>=' . $date,
                'post_status' => $order_statuses
            )
        );
        return $query->get_orders();
    }

    /**
     * @param $shippingAddress
     * @return array
     */
    public static function getFirstOrderWithShippingAddress($shippingAddress)
    {
        $shippingAddress = implode(' ', $shippingAddress);
        $order_statuses = array('wc-on-hold', 'wc-processing', 'wc-completed');

        return wc_get_orders(array(
            'meta_key' => '_shipping_address_index',
            'meta_value' => $shippingAddress,
            'post_status' => $order_statuses,
            'orderby' => 'date',
            'order' => 'ASC',
            'numberposts' => 1
        ));
    }
}
