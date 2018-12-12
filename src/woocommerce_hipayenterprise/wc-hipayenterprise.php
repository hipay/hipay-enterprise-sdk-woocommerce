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

    class WC_HipayEnterprise
    {

        const OPTION_PLUGIN_VERSION = "hipay_enterprise_version";

        /**
         * @var
         */
        private static $instance;

        /**
         *
         */
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
                WC_HipayEnterprise::loadClassesHipay();
                add_action('plugins_loaded', array($this, 'wc_hipay_gateway_load'), 0);
            }
        }


        /**
         * @param $currentPluginVersion
         */
        public function updatePlugin($currentPluginVersion) {
            //Update initial configuration
            $upgradeHelper = new Hipay_Upgrade_Helper();
            $upgradeHelper->upgrade($currentPluginVersion);

            // Update Plugin Version
            update_option( 'hipay_enterprise_version', WC_HIPAYENTERPRISE_VERSION );
        }

        /**
         * @param $currentPluginVersion
         */
        public function installPlugin() {
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

            load_plugin_textdomain("hipayenterprise",false,"woocommerce_hipayenterprise/languages/");
            WC_HipayEnterprise::loadClassesHipay();

            // Init Admin menus
            $menus = new Hipay_Admin_Menus();
            $postTypes = new Hipay_Admin_Post_Types();

            add_filter('woocommerce_payment_gateways', 'addGateway');

            $currentPluginVersion = get_option( 'hipay_enterprise_version' );
            if (!empty($currentPluginVersion)
                && WC_HIPAYENTERPRISE_VERSION !== $currentPluginVersion) {
                $this->updatePlugin($currentPluginVersion);
            } else if (empty($currentPluginVersion)) {
                $this->installPlugin();
            }


            /**
             * @param $methods
             * @return array
             */
            function addGateway($methods)
            {
                $methods[] = 'Gateway_Hipay';
                $methods[] = 'Hipay_Bnpp3x';
                $methods[] = 'Hipay_Bnpp4x';
                return $methods;
            }

        }

        /**
         *
         */
        public static function loadClassesHipay()
        {
            require_once(WC_HIPAYENTERPRISE_PATH . 'vendor/autoload.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/post/class-hipay-mapping-category.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/post/class-hipay-mapping-delivery.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-abstract.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-category-controller.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-delivery-controller.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-post-types.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-menus.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-upgrade-helper.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-hipay-gateway-abstract.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-hipay-gateway-local-abstract.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-gateway-hipay.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/gateways/local/class-hipay-bnpp3x.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/gateways/local/class-hipay-bnpp4x.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-log.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-notification.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-order-handler.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-assets.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-config.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-settings-handler.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-helper.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-api.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-helper-mapping.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/ThreeDS.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/OperatingMode.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/CaptureMode.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/SettingsField.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/formatter/class-hipay-api-formatter-abstract.php');
            require_once(WC_HIPAYENTERPRISE_PATH .
                'includes/formatter/request/class-hipay-request-formatter-abstract.php');
            require_once(WC_HIPAYENTERPRISE_PATH .
                'includes/formatter/request/class-hipay-hosted-payment-formatter.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-direct-post-formatter.php');
            require_once(WC_HIPAYENTERPRISE_PATH .
                'includes/formatter/info/class-hipay-customer-billing-info-formatter.php');
            require_once(WC_HIPAYENTERPRISE_PATH .
                'includes/formatter/info/class-hipay-customer-shipping-info-formatter.php');
            require_once(WC_HIPAYENTERPRISE_PATH .
                'includes/formatter/payment-method/class-hipay-card-token-formatter.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-api-request-handler.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/exceptions/class-hipay-payment-exception.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/exceptions/class-hipay-settings-exception.php');
        }
    }

    WC_HipayEnterprise::get_instance();
}
