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
        if (
            in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
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
        //TODO : DELETE
        $this->updatePlugin($currentPluginVersion);
        if (!empty($currentPluginVersion)
            && WC_HIPAYENTERPRISE_VERSION !== $currentPluginVersion) {
            $this->updatePlugin($currentPluginVersion);
        } else if (empty($currentPluginVersion)) {
            $this->installPlugin();
        }

        load_plugin_textdomain("hipayenterprise", false, "woocommerce_hipayenterprise/languages/");

        // Init Admin menus
        Hipay_Admin_Menus::initHiPayAdminMenus();
        Hipay_Admin_Post_Types::initHiPayCustomPostTypes();
        //Hipay_Admin_Meta_Boxes::initHiPayAdminMetaBoxes();
        Hipay_Admin_Capture::initHiPayAdminCapture();

        add_filter('woocommerce_payment_gateways', array($this, 'addGateway'));
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
        $localMethod = Hipay_Autoloader::getLocalMethodsNames();
        $methods[] = 'Gateway_Hipay';
        return array_merge($methods, $localMethod);
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
