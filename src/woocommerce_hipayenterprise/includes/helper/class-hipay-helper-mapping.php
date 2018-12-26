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
    public static function getWcCategories()
    {
        $args = array(
            'orderby' => "name",
            'order' => "asc",
        );

        return get_terms('product_cat', $args);
    }

    /**
     * @return array|int|WP_Error
     */
    public static function getWcDeliveryMethods()
    {
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


    /**
     *  Get correspondance from mapping for Category
     *
     * @param $termID
     * @return int
     */
    public static function getHipayCategoryFromMapping($termID)
    {
        return self::getMappingForPostType(
            Hipay_Admin_Post_Types::POST_TYPE_MAPPING_CATEGORIES,
            Hipay_Mapping_Category_Controller::ID_WC_CATEGORY,
            $termID,
            Hipay_Mapping_Category_Controller::ID_HIPAY_CATEGORY);
    }

    /**
     *  Get the correspondance for delivery method
     *
     * @param $termID
     * @return int
     */
    public static function getHipayMappingFromDeliveryMethod($termID)
    {
        return self::getMappingForPostType(
            Hipay_Admin_Post_Types::POST_TYPE_MAPPING_DELIVERY,
            Hipay_Mapping_Delivery_Controller::ID_WC_DELIVERY_METHOD,
            $termID
        , '');
    }

    /**
     *
     * @param string $postType
     * @param string $termFilter
     * @param string $termID
     * @param string $meta
     * @return int
     */
    private static function getMappingForPostType($postType, $termFilter, $termID, $meta)
    {
        $mapping = get_posts(
            array(
                'post_type' => $postType,
                'post_status' => 'any',
                'numberposts' => '-1',
                'meta_key' => $termFilter,
                'meta_value' => $termID
            )
        );
        return (count($mapping) > 0) ? get_post_meta($mapping[0]->ID, $meta) : 1;
    }

}
