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

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Admin_Post_Types
{
    /*
     * @var Hipay_Admin_Post_Types
     */
    private static $instance;

    /**
     * @var string
     */
    const POST_TYPE_MAPPING_CATEGORIES = 'hipay_mapping_cat';

    /**
     * @var string
     */
    const POST_TYPE_MAPPING_DELIVERY = 'hipay_mapping_del';

    /**
     * @var string
     */
    const POST_TYPE_TRANSACTION = 'hipay_transactions';

    /**
     *
     */
    const POST_TYPE_OPERATION = 'hipay_operations';

    /**
     * Hook in tabs.
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_post'));
        add_filter('wc_order_statuses', array($this, 'custom_order_status'));
        add_filter('woocommerce_valid_order_statuses_for_payment_complete', array($this, 'valid_order_statuses_for_payment_complete'));
    }

    /**
     * @param $order_statuses
     * @return mixed
     */
    public function valid_order_statuses_for_payment_complete($order_statuses)
    {
        return array_merge(
            array(
                "partial-captured",
                "partial-refunded"
            ),
            $order_statuses);
    }

    /**
     *  Add custom order status for Hipay partial  status
     *
     * @param $order_statuses
     * @return mixed
     */
    public function custom_order_status($order_statuses)
    {
        $order_statuses['wc-partial-captured'] = _x('Partially captured (HiPay)', 'Order status', 'woocommerce');
        $order_statuses['wc-partial-refunded'] = _x('Partially refunded (HiPay)', 'Order status', 'woocommerce');
        return $order_statuses;
    }

    /**
     * Register HiPay Post types
     */
    public function register_post()
    {
        // Register Mapping Categories
        register_post_type(
            self::POST_TYPE_MAPPING_CATEGORIES,
            array(
                'label' => __('HiPay Mapping Categories', 'hipayenterprise'),
                'description' => __('HiPay Mapping Categories', 'hipayenterprise'),
                'labels' => array(
                    'name' => __('HiPay Mapping Categories', 'hipayenterprise'),
                ),
                'public' => false,
                'capabilities' => array(
                    'create_posts' => false
                ),
                'map_meta_cap' => true,
                'show_in_menu' => 'false',
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => false,
                'show_ui' => true
            )
        );
        // Register Mapping delivery
        register_post_type(
            self::POST_TYPE_MAPPING_DELIVERY,
            array(
                'label' => __('HiPay Delivery Mapping', 'hipayenterprise'),
                'description' => __('HiPay Delivery Mapping', 'hipayenterprise'),
                'labels' => array(
                    'name' => __('HiPay Delivery Mapping', 'hipayenterprise'),
                ),
                'public' => false,
                'capabilities' => array(
                    'create_posts' => false
                ),
                'map_meta_cap' => true,
                'show_in_menu' => 'false',
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => false,
                'show_ui' => true
            )
        );

        // Register HiPay transaction
        register_post_type(
            self::POST_TYPE_TRANSACTION,
            array(
                'label' => __('HiPay Transactions', 'hipayenterprise'),
                'description' => __('HiPay Transactions', 'hipayenterprise'),
                'labels' => array(
                    'name' => __('HiPay Transactions', 'hipayenterprise'),
                ),
                'public' => false,
                'capabilities' => array(
                    'create_posts' => false
                ),
                'map_meta_cap' => true,
                'show_in_menu' => 'false',
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => false,
                'show_ui' => true
            )
        );


        // Register HiPay Operation
        register_post_type(
            self::POST_TYPE_OPERATION,
            array(
                'label' => __('HiPay Operations', 'hipayenterprise'),
                'description' => __('HiPay Operations', 'hipayenterprise'),
                'labels' => array(
                    'name' => __('HiPay Operations', 'hipayenterprise'),
                ),
                'public' => false,
                'capabilities' => array(
                    'create_posts' => false
                ),
                'map_meta_cap' => true,
                'show_in_menu' => 'false',
                'show_in_nav_menus' => false,
                'show_in_admin_bar' => false,
                'show_ui' => true
            )
        );

        // Register Capture
        wc_register_order_type(
            'shop_order_capture',
            apply_filters(
                'woocommerce_register_post_type_shop_order_capture',
                array(
                    'label' => __('Captures', 'woocommerce'),
                    'capability_type' => 'shop_order',
                    'public' => false,
                    'hierarchical' => false,
                    'supports' => false,
                    'exclude_from_orders_screen' => false,
                    'add_order_meta_boxes' => false,
                    'exclude_from_order_count' => true,
                    'exclude_from_order_views' => false,
                    'exclude_from_order_reports' => false,
                    'exclude_from_order_sales_reports' => true,
                    'class_name' => 'Hipay_Order_Capture',
                    'rewrite' => false,
                )
            )
        );


        // Add Post status for order
        register_post_status('wc-partial-captured',
            array(
                'label' => 'Partially captured (HiPay)',
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop('Partially captured  <span class="count">(%s)</span>', 'Partially captured  <span class="count">(%s)</span>')
            )
        );
        register_post_status('wc-partial-refunded',
            array(
                'label' => 'Partially refunded (HiPay)',
                'public' => true,
                'exclude_from_search' => false,
                'show_in_admin_all_list' => true,
                'show_in_admin_status_list' => true,
                'label_count' => _n_noop('Partially refunded  <span class="count">(%s)</span>', 'Partially captured  <span class="count">(%s)</span>')
            )
        );
    }

    /**
     * @return Hipay_Admin_Post_Types
     */
    public static function initHiPayCustomPostTypes()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
