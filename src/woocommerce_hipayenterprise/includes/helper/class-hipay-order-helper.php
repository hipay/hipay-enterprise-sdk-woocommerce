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
class Hipay_Order_Helper
{

    /**
     *  Get total captured
     *
     * @param $order
     * @return float
     */
    public static function get_total_captured($order)
    {
        global $wpdb;

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM( postmeta.meta_value )
            FROM $wpdb->postmeta AS postmeta
            INNER JOIN $wpdb->posts AS posts ON ( posts.post_type = 'shop_order_capture' AND posts.post_parent = %d )
            WHERE postmeta.meta_key = '_capture_amount'
            AND postmeta.post_id = posts.ID",
                $order->get_id()
            )
        );
        return floatval($total);
    }

    /**
     * Get the captured amount for a line item.
     *
     * @param  int $item_id ID of the item we're checking.
     * @param  string $item_type Type of the item we're checking, if not a line_item.
     * @return int
     */
    public static function get_qty_captured_for_item($item_id, $item_type = 'line_item', $order)
    {
        return self::get_sum_value($item_id, $item_type = 'line_item', $order, 'qty');
    }

    /**
     * Get order captures CPT.
     *
     * @since 2.2
     * @return array of WC_Order_Capture objects
     */
    public static function get_captures($order)
    {
        return wc_get_orders(
            array(
                'type' => 'shop_order_capture',
                'parent' => $order->get_id(),
                'limit' => -1,
            )
        );
    }

    /**
     *  Get value on captured item
     *
     * @param $item_id
     * @param string $item_type
     * @param WC_Order $order
     * @param count|total|qty $type
     * @return float|int
     */
    private static function get_sum_value($item_id, $item_type = 'line_item', $order, $type)
    {
        $total = 0;
        foreach (self::get_captures($order) as $capture) {
            foreach ($capture->get_items($item_type) as $captured_item) {
                if ($type == 'count') {
                    $total += abs($captured_item->get_quantity());
                } else if (absint($captured_item->get_meta('_captured_item_id')) === $item_id) {
                    if ($type == 'total') {
                        $total += $captured_item->get_total();
                    } else if ($type = 'qty') {
                        $total += abs($captured_item->get_quantity());
                    }
                }
            }
        }
        return $total;
    }

    /**
     * Get the captured amount for a line item.
     *
     * @param  int $item_id ID of the item we're checking.
     * @param  string $item_type Type of the item we're checking, if not a line_item.
     * @return int
     */
    public static function get_total_captured_for_item($item_id, $item_type = 'line_item', $order)
    {
        return self::get_sum_value($item_id, $item_type, $order, 'total');
    }

    /**
     * Get the refunded tax amount for a line item.
     *
     * @param  int $item_id ID of the item we're checking.
     * @param  int $tax_id ID of the tax we're checking.
     * @param  string $item_type Type of the item we're checking, if not a line_item.
     * @return double
     */
    public static function get_tax_captured_for_item($item_id, $tax_id, $item_type = 'line_item', $order)
    {
        $total = 0;
        foreach (self::get_captures($order) as $capture) {
            foreach ($capture->get_items($item_type) as $captured_item) {
                $captured_item_id = (int)$captured_item->get_meta('_captured_item_id');
                if ($captured_item_id === $item_id) {
                    $taxes = $captured_item->get_taxes();
                    $total += isset($taxes['total'][$tax_id]) ? (float)$taxes['total'][$tax_id] : 0;
                    break;
                }
            }
        }
        return wc_round_tax_total($total);
    }

    /**
     * Gets the count of order items of a certain type that have been refunded.
     *
     * @since  2.4.0
     * @param string $item_type Item type.
     * @return string
     */
    public static function get_item_count_captured($item_type = '', $order)
    {
        if (empty($item_type)) {
            $item_type = array('line_item');
        }
        if (!is_array($item_type)) {
            $item_type = array($item_type);
        }
        $count = self::get_sum_value(null, $item_type = 'line_item', $order, 'qty');

        return apply_filters('woocommerce_get_item_count_captured', $count, $item_type, $order);
    }
}
