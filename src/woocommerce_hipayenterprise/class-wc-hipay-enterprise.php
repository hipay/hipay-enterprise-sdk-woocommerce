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
class WC_HipayEnterprise
{

    const OPTION_PLUGIN_VERSION = "hipay_enterprise_version";

    private static $instance;

    public function __construct()
    {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
            || in_array('woocommerce/woocommerce.php', array_keys(get_site_option('active_sitewide_plugins')))
        ) {
            add_action('plugins_loaded', array($this, 'initPlugin'), 0);
        }
    }

    public function initPlugin()
    {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        $currentPluginVersion = get_option('hipay_enterprise_version');

        if (!empty($currentPluginVersion) && WC_HIPAYENTERPRISE_VERSION !== $currentPluginVersion) {
            $this->updatePlugin($currentPluginVersion);
        } elseif (empty($currentPluginVersion)) {
            $this->installPlugin();
        }

        load_plugin_textdomain("hipayenterprise", false, "woocommerce_hipayenterprise/languages/");

        // Init Admin menus
        Hipay_Admin_Menus::initHiPayAdminMenus();
        Hipay_Admin_Post_Types::initHiPayCustomPostTypes();
        //Hipay_Admin_Meta_Boxes::initHiPayAdminMetaBoxes();
        Hipay_Admin_Capture::initHiPayAdminCapture();

        add_filter('woocommerce_payment_gateways', array($this, 'addGateway'));
        add_action('woocommerce_order_status_changed', array($this, 'handleStatusChange'), 10, 4);

        add_filter(
            'woocommerce_payment_methods_list_item',
            array('WC_Payment_Token_CC_HiPay', 'wc_get_account_saved_payment_methods_list_item_cc_hipay'),
            10,
            2
        );

        add_filter(
            'woocommerce_payment_gateway_get_saved_payment_method_option_html',
            array('WC_Payment_Token_CC_HiPay', 'wc_get_get_saved_payment_method_option_html_hipay'),
            10,
            2
        );

        add_filter(
            'wc_payment_gateway_form_saved_payment_methods_html',
            array('WC_Payment_Token_CC_HiPay', 'wc_get_form_saved_payment_methods_html_hipay'),
            10,
            2
        );

        // Init Plugin Update handling
        new Hipay_Admin_Plugin_Update_Handler($currentPluginVersion, WC_HIPAYENTERPRISE_PLUGIN_NAME);
    }

    /**
     * @param $currentPluginVersion
     */
    public function updatePlugin($currentPluginVersion)
    {
        //Update initial configuration
        $upgradeHelper = new Hipay_Upgrade_Helper();
        $upgradeHelper->upgrade($currentPluginVersion);

        // Update Plugin Version
        update_option('hipay_enterprise_version', WC_HIPAYENTERPRISE_VERSION);
    }

    /**
     * @param $currentPluginVersion
     */
    public function installPlugin()
    {
        //Update initial configuration
        $upgradeHelper = new Hipay_Upgrade_Helper();
        $upgradeHelper->install();
    }

    /**
     * @param $methods
     * @return array
     */
    public function addGateway($methods)
    {
        $config = new Hipay_Config();

        $localMethod = Hipay_Autoloader::getLocalMethodsNames();
        $methods[] = 'Gateway_Hipay';
        return array_merge($methods, $localMethod);
    }

    /**
     * Handles order status change for HiPay plugin
     *
     * @param $orderId
     * @param $statusFrom
     * @param $statusTo
     * @param $order
     */
    public function handleStatusChange($orderId, $statusFrom, $statusTo, $order)
    {
        $payment_method = $order->get_payment_method();

        // Cancel payment transaction if HiPay gateway is used by order
        if (preg_match("/^hipayenterprise/", $payment_method)) {
            $gateway = new Hipay_Gateway_Abstract();
            $orderHandler = new Hipay_Order_Handler($order, $gateway);
            $orderHandler->handleStatusChange($statusTo, $statusFrom);
        }
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
