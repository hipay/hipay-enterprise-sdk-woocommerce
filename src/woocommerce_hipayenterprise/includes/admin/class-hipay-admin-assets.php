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
class Wc_Hipay_Admin_Assets
{

    /**
     * The single instance of the class.
     *
     * @var Wc_Hipay_Admin_Assets|null
     */
    protected static $instance = null;

    /**
     * Get Instance
     */
    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();

            add_action('admin_enqueue_scripts', array(self::$instance, 'admin_enqueue_scripts'));
        }
        return self::$instance;
    }

    /**
     * Enqueue Style and Script for Woocommerce Order page
     */
    private function enqueue_script_order_page()
    {
        wp_enqueue_script(
            'hipay-js-admin-meta-boxes',
            plugins_url('assets/js/admin/hipay-meta-boxes-order.js', WC_HIPAYENTERPRISE_BASE_FILE),
            array(),
            WC_HIPAYENTERPRISE_VERSION,
            true
        );

        wp_localize_script(
            'hipay-js-admin-meta-boxes',
            'hipay_config_i18n',
            array(
                "msg_confirm_capture" => __("Are-you sure to capture these elements ?", "hipayenterprise")
            )
        );

        wp_register_style(
            'hipay-admin-css',
            plugins_url('assets/css/admin/hipay-admin.css', WC_HIPAYENTERPRISE_BASE_FILE),
            array(),
            WC_HIPAYENTERPRISE_VERSION
        );
        wp_enqueue_style('hipay-admin-css');
    }

    /**
     * Enqueue Script and Style for Hipay pages
     */
    public function admin_enqueue_scripts()
    {
        $isHipaySectionSettings = isset($_GET['section']) && preg_match("/^hipayenterprise/", $_GET['section']);
        $isHipayPageSettings = isset($_GET['page']) && preg_match("/^hipay/", $_GET['page']);
        if (is_admin()) {
            $this->enqueue_script_order_page();

            if ($isHipaySectionSettings || $isHipayPageSettings) {
                wp_register_style(
                    'wc_hipay_admin_multi_css',
                    plugins_url('assets/css/admin/multi.min.css', WC_HIPAYENTERPRISE_BASE_FILE),
                    array(),
                    WC_HIPAYENTERPRISE_VERSION
                );
                wp_enqueue_style('wc_hipay_admin_multi_css');
                wp_register_style(
                    'hipay-boostrap-css',
                    plugins_url('assets/css/admin/bootstrap.min.css', WC_HIPAYENTERPRISE_BASE_FILE),
                    array(),
                    WC_HIPAYENTERPRISE_VERSION
                );

                wp_enqueue_style('hipay-boostrap-css');

                wp_enqueue_script('accordion');
                wp_enqueue_script(
                    'hipay-js-admin-multi',
                    plugins_url('assets/js/admin/multi.min.js', WC_HIPAYENTERPRISE_BASE_FILE),
                    array(),
                    WC_HIPAYENTERPRISE_VERSION,
                    true
                );
                wp_enqueue_script(
                    'hipay-js-admin',
                    plugins_url('assets/js/admin/hipay-admin.js', WC_HIPAYENTERPRISE_BASE_FILE),
                    array(),
                    WC_HIPAYENTERPRISE_VERSION,
                    true
                );
                wp_enqueue_script(
                    'hipay-js-boostrap',
                    plugins_url('assets/js/admin/bootstrap.min.js', WC_HIPAYENTERPRISE_BASE_FILE),
                    array(),
                    WC_HIPAYENTERPRISE_VERSION,
                    true
                );
            }
        }
    }
}

Wc_Hipay_Admin_Assets::get_instance();
