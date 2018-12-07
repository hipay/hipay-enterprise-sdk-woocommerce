<?php

defined( 'ABSPATH' ) || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Admin_Post_Types
{

    /**
     * @var string
     */
    const POST_TYPE_MAPPING_CATEGORIES = 'hipay_mapping_cat';

    /**
     * @var string
     */
    const POST_TYPE_MAPPING_DELIVERY = 'hipay_mapping_del';

    /**
     * Hook in tabs.
     */
    public function __construct()
    {
        add_action('init', array($this, 'register_post'));
    }

    /**
     * Register HiPay Post types
     */
    public function register_post()
    {
        // Register Mapping Categories
        register_post_type( self::POST_TYPE_MAPPING_CATEGORIES, array (
            'label' => __( 'HiPay Mapping Categories', 'hipayenterprise' ),
            'description' => __( 'HiPay Mapping Categories', 'hipayenterprise' ),
            'labels' => array (
                'name' => __( 'HiPay Mapping Categories', 'hipayenterprise' ),
            ),
            'public' => false,
            'capabilities' => array (
                'create_posts' => false
            ),
            'map_meta_cap' => true,
            'show_in_menu' => 'false',
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'show_ui' => true
        ) );
        // Register Mapping delivery
        register_post_type( self::POST_TYPE_MAPPING_DELIVERY, array (
            'label' => __( 'HiPay Delivery Mapping', 'hipayenterprise' ),
            'description' => __( 'HiPay Delivery Mapping', 'hipayenterprise' ),
            'labels' => array (
                'name' => __( 'HiPay Delivery Mapping', 'hipayenterprise' ),
            ),
            'public' => false,
            'capabilities' => array (
                'create_posts' => false
            ),
            'map_meta_cap' => true,
            'show_in_menu' => 'false',
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'show_ui' => true
        ) );
    }
}
