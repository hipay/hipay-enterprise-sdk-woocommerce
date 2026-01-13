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

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base abstract class for HiPay payment method blocks integration
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link        https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
abstract class Hipay_Payment_Block_Abstract extends AbstractPaymentMethodType
{
    /**
     * @var Hipay_Gateway_Abstract
     */
    protected $gateway;

    /**
     * @var Hipay_Config
     */
    protected $confHelper;

    /**
     * Payment method name/id/slug
     *
     * @var string
     */
    protected $name;

    /**
     * Initialize the payment method type.
     */
    public function initialize()
    {
        $this->confHelper = new Hipay_Config();
        $gateway = $this->get_gateway();
        if ($gateway) {
            $this->settings = $gateway->settings;
        } else {
            $this->settings = [];
        }

        // Register payment method data with Store API
        add_action('woocommerce_blocks_loaded', array($this, 'register_payment_method_data'));

        // Enqueue frontend styles for checkout blocks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_block_styles'));
    }

    /**
     * Enqueue block styles for frontend checkout
     * Only loads CSS when blocks are actually being used
     */
    public function enqueue_block_styles()
    {
        // DISABLED: Do not auto-enqueue on all pages
        // CSS will be enqueued via get_payment_method_script_handles when blocks render
        // This prevents blocks CSS from affecting shortcode checkout
    }

    /**
     * Register payment method data schema with Store API
     */
    public function register_payment_method_data()
    {
        if (!function_exists('woocommerce_store_api_register_payment_requirements')) {
            return;
        }

        woocommerce_store_api_register_payment_requirements(
            array(
                'data_callback' => function() {
                    return array(
                        'hipay_token' => array(
                            'description' => __('HiPay token', 'hipayenterprise'),
                            'type' => 'string',
                            'required' => false,
                        ),
                        'hipay_device_fingerprint' => array(
                            'description' => __('Device fingerprint', 'hipayenterprise'),
                            'type' => 'string',
                            'required' => false,
                        ),
                        'hipay_browser_info' => array(
                            'description' => __('Browser info', 'hipayenterprise'),
                            'type' => 'string',
                            'required' => false,
                        ),
                        'hipay_save_card' => array(
                            'description' => __('Save card', 'hipayenterprise'),
                            'type' => 'string',
                            'required' => false,
                        ),
                        'hipay_use_saved_card' => array(
                            'description' => __('Use saved card', 'hipayenterprise'),
                            'type' => 'boolean',
                            'required' => false,
                        ),
                        'hipay_operating_mode' => array(
                            'description' => __('Operating mode', 'hipayenterprise'),
                            'type' => 'string',
                            'required' => false,
                        ),
                        'hipay_payment_product' => array(
                            'description' => __('Payment product', 'hipayenterprise'),
                            'type' => 'string',
                            'required' => false,
                        ),
                        'paypalOrderId' => array(
                            'description' => __('PayPal Order ID', 'hipayenterprise'),
                            'type' => 'string',
                            'required' => false,
                        ),
                    );
                },
            )
        );
    }

    /**
     * Get the gateway instance
     *
     * @return Hipay_Gateway_Abstract
     */
    abstract protected function get_gateway();

    /**
     * Returns if this payment method should be active.
     * If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active()
    {
        $gateway = $this->get_gateway();

        // First check if gateway is enabled
        if (!$gateway || 'yes' !== $gateway->enabled) {
            return false;
        }

        // Then check if it's available for the current cart
        // This matches the logic used in shortcode checkout
        if (isset(WC()->cart) && method_exists($gateway, 'isAvailableForCurrentCart')) {
            return $gateway->isAvailableForCurrentCart();
        }

        return true;
    }

    /**
     * Returns an array of script handles to enqueue in the frontend context.
     *
     * @return string[]
     */
    public function get_payment_method_script_handles()
    {
        $script_handles = [];

        $script_path = $this->get_script_path();

        // Check if the script file exists, if not, return empty array
        // This prevents errors when JavaScript assets haven't been built yet
        if (!file_exists($script_path)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HiPay Blocks: Script file not found: ' . $script_path . '. Please run "npm run build" in the plugin directory.');
            }
            return $script_handles;
        }

        $script_url = $this->get_script_url();
        $script_asset_path = $this->get_script_asset_path();

        if (file_exists($script_asset_path)) {
            $script_asset = require $script_asset_path;
        } else {
            $script_asset = [
                'dependencies' => [],
                'version' => WC_HIPAYENTERPRISE_VERSION
            ];
        }

        $script_handle = 'wc-hipayenterprise-' . $this->name . '-block';

        wp_register_script(
            $script_handle,
            $script_url,
            $script_asset['dependencies'],
            $script_asset['version'],
            true
        );

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                $script_handle,
                'hipayenterprise',
                WC_HIPAYENTERPRISE_PATH . 'languages/'
            );
        }

        $script_handles[] = $script_handle;

        return $script_handles;
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data()
    {
        // Enqueue blocks CSS ONLY when this method is called
        // This method is only called when blocks are rendering
        $this->enqueue_blocks_css_once();

        $gateway = $this->get_gateway();

        if (!$gateway) {
            return [
                'title' => '',
                'description' => '',
                'supports' => [],
                'icon' => '',
                'config' => [],
            ];
        }

        // Get payment config with error handling
        $config = [];
        try {
            $config = $this->get_payment_config();
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HiPay Blocks: Failed to get payment config for ' . $this->name . ' - ' . $e->getMessage());
            }
        }

        return [
            'title' => $gateway->title ?? '',
            'description' => $gateway->description ?? '',
            'supports' => array_filter($gateway->supports ?? [], [$gateway, 'supports']),
            'icon' => $gateway->icon ?? '',
            'config' => $config,
        ];
    }

    /**
     * Enqueue blocks CSS only once
     * This method is ONLY called when WooCommerce Blocks renders payment methods
     */
    private function enqueue_blocks_css_once()
    {
        static $enqueued = false;

        if ($enqueued) {
            return;
        }

        $blocks_style_path = WC_HIPAYENTERPRISE_PATH_ASSETS . 'css/blocks-editor.css';
        $blocks_style_url = WC_HIPAYENTERPRISE_URL_ASSETS . 'css/blocks-editor.css';

        if (file_exists($blocks_style_path)) {
            wp_enqueue_style(
                'wc-hipayenterprise-blocks-style',
                $blocks_style_url,
                array(),
                WC_HIPAYENTERPRISE_VERSION . '-v4-scoped'  // Cache bust for scoped CSS
            );
            $enqueued = true;

            // Debug log (only if WP_DEBUG is enabled)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('HiPay: Blocks CSS enqueued (blocks checkout active)');
            }
        }
    }

    /**
     * Get payment method specific configuration
     *
     * @return array
     */
    abstract protected function get_payment_config();

    /**
     * Get the file path to the built script.
     *
     * @return string
     */
    abstract protected function get_script_path();

    /**
     * Get the URL to the built script.
     *
     * @return string
     */
    abstract protected function get_script_url();

    /**
     * Get the file path to the built script asset file.
     *
     * @return string
     */
    protected function get_script_asset_path()
    {
        $script_path = $this->get_script_path();
        return str_replace('.js', '.asset.php', $script_path);
    }
}
