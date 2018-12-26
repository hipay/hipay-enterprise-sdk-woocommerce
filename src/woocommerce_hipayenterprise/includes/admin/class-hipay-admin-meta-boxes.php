<?php

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Admin_Meta_Boxes
{

    private static $instance;

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30);
    }

    /**
     * Add WC Meta boxes.
     */
    public function add_meta_boxes()
    {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        foreach ( wc_get_order_types( 'order-meta-boxes' ) as $type ) {
            $order_type_object = get_post_type_object($type);
            add_meta_box( 'woocommerce-hipay-data', sprintf( __( '%s data', 'woocommerce' ), "HiPay Capture" ), 'WC_Meta_Box_Hipay::output', $type, 'normal', 'high' );
        }
    }


    public static function initHiPayAdminMetaBoxes()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}


