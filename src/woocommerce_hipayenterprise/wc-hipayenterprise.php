<?php
/*
Plugin Name: WooCommerce HiPay Enterprise
Plugin URI: https://hipay.com/en/payment-solution-enterprise
Description: WooCommerce Plugin for Hipay Enterprise.
Version: 1.0.0
Text Domain: hipayenterprise
Author: Hi-Pay Portugal
Author URI: https://www.hipaycomprafacil.com
*/

if (!class_exists('WC_HipayEnterprise')) {
    define('WC_HIPAYENTERPRISE_VERSION', '1.0.0');
    define('WC_HIPAYENTERPRISE_PATH', plugin_dir_path(__FILE__));
    define('WC_HIPAYENTERPRISE_URL_ASSETS', plugin_dir_url(__FILE__) . 'assets/');
    define('WC_HIPAYENTERPRISE_PLUGIN_NAME', plugin_basename(__FILE__));
    define('WC_HIPAYENTERPRISE_BASE_FILE', __FILE__);

    class WC_HipayEnterprise
    {

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
                add_action('plugins_loaded', array($this, 'wc_hipay_gateway_load'), 0);
            }
        }

        /**
         *
         */
        public function wc_hipay_gateway_load()
        {
            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }

            add_filter('woocommerce_payment_gateways', 'wc_hipay_add_gateway');

            /**
             * @param $methods
             * @return array
             */
            function wc_hipay_add_gateway($methods)
            {
                WC_HipayEnterprise::loadClassesHipay();
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
