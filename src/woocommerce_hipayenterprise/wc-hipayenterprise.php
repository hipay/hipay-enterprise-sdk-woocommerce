<?php
/*
Plugin Name: WooCommerce HiPay Enterprise
Plugin URI: https://hipay.com/en/
Description: WooCommerce Plugin for Hipay Enterprise.
Version: 1.6.0
Text Domain: hipayenterprise
Author: HiPay
Author URI: https://www.hipay.com
*/

if (!class_exists('WC_HipayEnterprise')) {
    define('WC_HIPAYENTERPRISE_VERSION', '1.6.1');
    define('WC_HIPAYENTERPRISE_NAME', 'WooCommerce HiPay Enterprise');
    define('WC_HIPAYENTERPRISE_PATH', plugin_dir_path(__FILE__));
    define('WC_HIPAYENTERPRISE_URL_ASSETS', plugin_dir_url(__FILE__) . 'assets/');
    define('WC_HIPAYENTERPRISE_PLUGIN_NAME', plugin_basename(__FILE__));
    define('WC_HIPAYENTERPRISE_BASE_FILE', __FILE__);

    require_once(WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-autoloader.php');

    WC_HipayEnterprise::get_instance();
}
