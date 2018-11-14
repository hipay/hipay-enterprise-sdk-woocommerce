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
    define('WC_HIPAYENTERPRISE_ASSETS', plugin_dir_url(__FILE__) . 'assets/');
    define('WC_HIPAYENTERPRISE_PLUGIN_NAME', plugin_basename(__FILE__));
    define( 'WC_HIPAYENTERPRISE_BASE_FILE', __FILE__ );

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
            if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || in_array('woocommerce/woocommerce.php', array_keys(get_site_option('active_sitewide_plugins')))) {
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
                $methods[] = 'WC_Gateway_Hipay';
//                $methods[] = 'WC_HipayEnterprise_LocalPayments_Paypal';
//                $methods[] = 'WC_HipayEnterprise_LocalPayments_Belfius';
//                $methods[] = 'WC_HipayEnterprise_LocalPayments_Multibanco';
//                $methods[] = 'WC_HipayEnterprise_LocalPayments_Giropay';
//                $methods[] = 'WC_HipayEnterprise_LocalPayments_Inghomepay';
//                $methods[] = 'WC_HipayEnterprise_LocalPayments_Ideal';
                return $methods;
            }
        }

        /**
         *
         */
        public static function loadClassesHipay()
        {
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/class-wc-gateway-hipay.php');
//            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-paypal.php');
//            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-inghomepay.php');
//            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-ideal.php');
//            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-giropay.php');
//            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-belfius.php');
//            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-multibanco.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'vendor/autoload.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-wc-hipay-log.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-wc-hipay-notification.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-wc-hipay-admin-assets.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-wc-hipay-config.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-wc-hipay-settings-handler.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/ThreeDS.php');
            require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/ApiMode.php');
        }
    }

    WC_HipayEnterprise::get_instance();
}