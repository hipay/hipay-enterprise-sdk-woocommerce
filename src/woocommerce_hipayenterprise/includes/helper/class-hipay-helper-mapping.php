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


use \HiPay\Fullservice\Enum\Transaction\TransactionStatus;
use \HiPay\Fullservice\Helper\Signature;
use \HiPay\Fullservice\HTTP\Configuration\Configuration;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Helper_Mapping
{
    /**
     * @return array|int|WP_Error
     */
    public static function getWcCategories() {
        $args = array(
            'orderby'    => "name",
            'order'      => "asc",
        );

        return get_terms( 'product_cat', $args );
    }

    /**
     * @return array|int|WP_Error
     */
    public static function getWcDeliveryMethods() {
        return WC()->shipping->get_shipping_methods();
    }

    /**
     * Return all HiPay Categories provided by HiPay
     *
     * @return array
     */
    public static function getHipayCategories()
    {
        return HiPay\Fullservice\Data\Category\Collection::getItems();
    }

    /**
     * Return all Hipay carriers provided by HiPay
     *
     * @return array
     */
    public static function getHipayCarriers()
    {
        return HiPay\Fullservice\Data\DeliveryMethod\CollectionModeShipping::getItems();
    }

}
