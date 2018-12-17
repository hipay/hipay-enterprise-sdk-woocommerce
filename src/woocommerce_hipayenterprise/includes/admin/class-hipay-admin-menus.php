<?php

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Admin_Menus
{

    private static $instance;

    /**
     * @var Hipay_Mapping_Category_Controller
     */
    private $categoriesMappingController;

    /**
     * @var Hipay_Mapping_Delivery_Controller
     */
    private $deliveryMappingController;

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'), 9);
        $this->categoriesMappingController = new Hipay_Mapping_Category_Controller();
        $this->deliveryMappingController = new Hipay_Mapping_Delivery_Controller();
    }

    /**
     * Add HiPay menu items
     */
    public function admin_menu()
    {
        add_menu_page(
            __("HiPay Enterprise", "hipayenterprise"),
            __("HiPay Enterprise", "hipayenterprise"),
            "manage_options",
            "hipay-settings",
            null,
            null,
            8
        );

        add_submenu_page(
            "hipay-settings",
            __("Mapping category", "hipayenterprise"),
            __("Mapping category", "hipayenterprise"),
            "manage_options",
            "hipay-mapping-category",
            array(
                $this,
                "mappingCategory")
        );
        add_submenu_page(
            "hipay-settings",
            __("Mapping delivery method", "hipayenterprise"),
            __("Mapping delivery method", "hipayenterprise"),
            "manage_options",
            "hipay-mapping-delivery-method",
            array(
                $this,
                "mappingDeliveryMethod")
        );
        remove_submenu_page("hipay-settings", "hipay-settings");
    }

    /**
     * Render mapping Category page
     */
    public function mappingCategory()
    {
        $this->categoriesMappingController->output();
    }

    /**
     * Render mapping Delivery method
     */
    public function mappingDeliveryMethod()
    {
        $this->deliveryMappingController->output();
    }

    public static function initHiPayAdminMenus()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}


