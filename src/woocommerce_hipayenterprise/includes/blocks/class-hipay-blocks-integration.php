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

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main integration class for HiPay payment methods with WooCommerce Blocks
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link        https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Blocks_Integration
{
    /**
     * Instance of this class
     *
     * @var Hipay_Blocks_Integration|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return Hipay_Blocks_Integration
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks()
    {
        // Register payment methods with WooCommerce Blocks
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            array($this, 'register_payment_methods')
        );

        // Enqueue block editor assets
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
    }

    /**
     * Check if WooCommerce Blocks is available
     *
     * @return bool
     */
    public static function is_blocks_available()
    {
        return class_exists('Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry');
    }

    /**
     * Register payment methods with WooCommerce Blocks
     *
     * @param PaymentMethodRegistry $payment_method_registry
     */
    public function register_payment_methods(PaymentMethodRegistry $payment_method_registry)
    {
        try {
            // Register Credit Card payment method
            $payment_method_registry->register(new Hipay_Credit_Card_Block());
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HiPay Blocks: Failed to register credit card block - ' . $e->getMessage());
            }
        }

        // Register all local payment methods
        try {
            $this->register_local_payment_methods($payment_method_registry);
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HiPay Blocks: Failed to register local payment methods - ' . $e->getMessage());
            }
        }
    }

    /**
     * Register local payment methods
     *
     * @param PaymentMethodRegistry $payment_method_registry
     */
    private function register_local_payment_methods(PaymentMethodRegistry $payment_method_registry)
    {
        $local_methods = $this->get_local_payment_methods();

        foreach ($local_methods as $method_data) {
            try {
                $class_name = $method_data['class'];
                $file_path = $method_data['file'];

                // Check if file exists and load it
                if (file_exists($file_path)) {
                    require_once $file_path;

                    // Register the payment method if class exists
                    if (class_exists($class_name)) {
                        $payment_method_registry->register(new $class_name());
                    }
                }
            } catch (Exception $e) {
                // Silently fail for individual payment methods
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('HiPay Blocks: Failed to register ' . ($class_name ?? 'unknown') . ' - ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Get list of local payment methods to register
     *
     * @return array
     */
    private function get_local_payment_methods()
    {
        $blocks_path = WC_HIPAYENTERPRISE_PATH . 'includes/blocks/local/';

        return array(
            array(
                'class' => 'Hipay_Alma_3x_Block',
                'file' => $blocks_path . 'class-hipay-alma-3x-block.php',
            ),
            array(
                'class' => 'Hipay_Alma_4x_Block',
                'file' => $blocks_path . 'class-hipay-alma-4x-block.php',
            ),
            array(
                'class' => 'Hipay_Bancontact_Block',
                'file' => $blocks_path . 'class-hipay-bancontact-block.php',
            ),
            array(
                'class' => 'Hipay_Bnpp3x_Block',
                'file' => $blocks_path . 'class-hipay-bnpp3x-block.php',
            ),
            array(
                'class' => 'Hipay_Bnpp4x_Block',
                'file' => $blocks_path . 'class-hipay-bnpp4x-block.php',
            ),
            array(
                'class' => 'Hipay_Giropay_Block',
                'file' => $blocks_path . 'class-hipay-giropay-block.php',
            ),
            array(
                'class' => 'Hipay_Ideal_Block',
                'file' => $blocks_path . 'class-hipay-ideal-block.php',
            ),
            array(
                'class' => 'Hipay_Klarna_Block',
                'file' => $blocks_path . 'class-hipay-klarna-block.php',
            ),
            array(
                'class' => 'Hipay_Mbway_Block',
                'file' => $blocks_path . 'class-hipay-mbway-block.php',
            ),
            array(
                'class' => 'Hipay_Multibanco_Block',
                'file' => $blocks_path . 'class-hipay-multibanco-block.php',
            ),
            array(
                'class' => 'Hipay_Mybank_Block',
                'file' => $blocks_path . 'class-hipay-mybank-block.php',
            ),
            array(
                'class' => 'Hipay_Oney_3xcb_Block',
                'file' => $blocks_path . 'class-hipay-oney-3xcb-block.php',
            ),
            array(
                'class' => 'Hipay_Oney_3xcb_No_Fees_Block',
                'file' => $blocks_path . 'class-hipay-oney-3xcb-no-fees-block.php',
            ),
            array(
                'class' => 'Hipay_Oney_4xcb_Block',
                'file' => $blocks_path . 'class-hipay-oney-4xcb-block.php',
            ),
            array(
                'class' => 'Hipay_Oney_4xcb_No_Fees_Block',
                'file' => $blocks_path . 'class-hipay-oney-4xcb-no-fees-block.php',
            ),
            array(
                'class' => 'Hipay_Paypal_Block',
                'file' => $blocks_path . 'class-hipay-paypal-block.php',
            ),
            array(
                'class' => 'Hipay_Postfinance_Card_Block',
                'file' => $blocks_path . 'class-hipay-postfinance-card-block.php',
            ),
            array(
                'class' => 'Hipay_Postfinance_Efinance_Block',
                'file' => $blocks_path . 'class-hipay-postfinance-efinance-block.php',
            ),
            array(
                'class' => 'Hipay_Przelewy24_Block',
                'file' => $blocks_path . 'class-hipay-przelewy24-block.php',
            ),
            array(
                'class' => 'Hipay_Sdd_Block',
                'file' => $blocks_path . 'class-hipay-sdd-block.php',
            ),
            array(
                'class' => 'Hipay_Sisal_Block',
                'file' => $blocks_path . 'class-hipay-sisal-block.php',
            ),
            array(
                'class' => 'Hipay_Sofort_Uberweisung_Block',
                'file' => $blocks_path . 'class-hipay-sofort-uberweisung-block.php',
            ),
        );
    }

    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets()
    {
        // Enqueue editor-specific styles if needed
        $css_file = WC_HIPAYENTERPRISE_PATH_ASSETS . 'css/blocks-editor.css';

        if (file_exists($css_file)) {
            wp_enqueue_style(
                'hipay-blocks-editor',
                WC_HIPAYENTERPRISE_URL_ASSETS . 'css/blocks-editor.css',
                array(),
                WC_HIPAYENTERPRISE_VERSION
            );
        }
    }
}
