<?php
/*
Plugin Name: WooCommerce HiPay Enterprise
Plugin URI: https://hipay.com/en/
Description: WooCommerce Plugin for Hipay Enterprise.
Version: 1.0.0
Text Domain: hipayenterprise
Author: HiPay
Author URI: https://www.hipay.com
*/

if (!class_exists('WC_HipayEnterprise')) {
    define('WC_HIPAYENTERPRISE_VERSION', '1.0.0');
    define('WC_HIPAYENTERPRISE_PATH', plugin_dir_path(__FILE__));
    define('WC_HIPAYENTERPRISE_URL_ASSETS', plugin_dir_url(__FILE__) . 'assets/');
    define('WC_HIPAYENTERPRISE_PLUGIN_NAME', plugin_basename(__FILE__));
    define('WC_HIPAYENTERPRISE_BASE_FILE', __FILE__);

    require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-autoloader.php');

    class WC_HipayEnterprise
    {

        const OPTION_PLUGIN_VERSION = "hipay_enterprise_version";

        private static $instance;

        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            if (in_array(
                    'woocommerce/woocommerce.php',
                    apply_filters('active_plugins', get_option('active_plugins'))
                ) || in_array('woocommerce/woocommerce.php', array_keys(get_site_option('active_sitewide_plugins')))) {
                add_action('plugins_loaded', array($this, 'wc_hipay_gateway_load'), 0);
            }
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
         *
         */
        public function wc_hipay_gateway_load()
        {
            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }

            load_plugin_textdomain("hipayenterprise", false, "woocommerce_hipayenterprise/languages/");

            // Init Admin menus
            $menus = new Hipay_Admin_Menus();
            $postTypes = new Hipay_Admin_Post_Types();

            add_filter('woocommerce_payment_gateways', array($this, 'addGateway'));

            $currentPluginVersion = get_option('hipay_enterprise_version');
            if (!empty($currentPluginVersion)
                && WC_HIPAYENTERPRISE_VERSION !== $currentPluginVersion) {
                $this->updatePlugin($currentPluginVersion);
            } else if (empty($currentPluginVersion)) {
                $this->installPlugin();
            }
        }

        public function addGateway($methods)
        {
            $localMethod = Hipay_Autoloader::getLocalMethodsNames();
            $methods[] = 'Gateway_Hipay';
            return array_merge($methods, $localMethod);
        }
    }

    WC_HipayEnterprise::get_instance();
}
