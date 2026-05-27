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
class Hipay_Bancomat_Pay extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_bancomatpay';
        $this->paymentProduct = 'bancomatpay';
        $this->method_title = __('HiPay Enterprise Bancomat Pay', "hipayenterprise");
        $this->title = __('Bancomat Pay', "hipayenterprise");
        $this->method_description = __('Bancomat Pay', "hipayenterprise");

        parent::__construct();

        add_action('woocommerce_before_thankyou', array($this, 'pending_section'));
        add_action('wp_ajax_hipay_check_order_status', array($this, 'check_order_status'));
        add_action('wp_ajax_nopriv_hipay_check_order_status', array($this, 'check_order_status'));
    }

    public function payment_fields()
    {
        $this->process_template(
            'bancomat-pay.php',
            'frontend',
            array(
                'localPaymentName'   => $this->paymentProduct,
                'informativeMessage' => __('The payment will need to be validated on your Bancomat Pay application.', 'hipayenterprise'),
            )
        );
    }

    /**
     * Render the pending/success/failed status section at the top of the
     * order-received page and enqueue the polling script.
     *
     * Hooked to woocommerce_before_thankyou so it appears above everything else.
     *
     * @param int $order_id
     */
    public function pending_section($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_payment_method() !== $this->id) {
            return;
        }

        $pendingStatuses = array('pending', 'on-hold');
        if (!in_array($order->get_status(), $pendingStatuses, true)) {
            return;
        }

        wp_enqueue_style(
            'hipay-bancomat-pay-pending',
            plugins_url('/assets/css/frontend/bancomat-pay-pending.css', WC_HIPAYENTERPRISE_BASE_FILE),
            array(),
            WC_HIPAYENTERPRISE_VERSION
        );

        wp_enqueue_script(
            'hipay-bancomat-pay-polling',
            plugins_url('/assets/js/frontend/local-payment-bancomat-pay.js', WC_HIPAYENTERPRISE_BASE_FILE),
            array('jquery'),
            WC_HIPAYENTERPRISE_VERSION,
            true
        );

        wp_localize_script('hipay-bancomat-pay-polling', 'hipayBancomatPayConfig', array(
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('hipay_check_order_status'),
            'orderId'     => $order_id,
            'orderKey'    => $order->get_order_key(),
            'orderStatus' => $order->get_status(),
        ));

        $this->process_template(
            'bancomat-pay-pending.php',
            'frontend',
            array('order' => $order)
        );
    }

    /**
     * AJAX handler — returns the current WooCommerce order status.
     * Used by the front-end polling script on the pending page.
     */
    public function check_order_status()
    {
        check_ajax_referer('hipay_check_order_status', 'nonce');

        $order_id  = isset($_POST['order_id'])  ? absint($_POST['order_id'])           : 0;
        $order_key = isset($_POST['order_key']) ? sanitize_text_field($_POST['order_key']) : '';

        $order = wc_get_order($order_id);

        if (!$order || !hash_equals($order->get_order_key(), $order_key)) {
            wp_send_json_error(array('message' => 'Invalid order'), 403);
        }

        wp_send_json_success(array(
            'status' => $order->get_status(),
        ));
    }
}
