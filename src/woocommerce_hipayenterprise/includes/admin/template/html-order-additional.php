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
?>
<button type="button" class="button capture-items <?php if(!$payment_gateway->supports('partialCaptures')) { ?> capture-complete-only <?php } ?>"><?php esc_html_e('Capture', 'hipayenterprise'); ?></button>


<!-- MOVED INTO ITEMS AFTER DOM READY       -->
<div id="order_captures">
    <table class="woocommerce_order_items">
        <tbody>
        <?php
        $captures = Hipay_Order_Helper::get_captures($order);
        if ($captures) {
            foreach ($captures as $capture) {
                include 'html-order-capture.php';
            }
            do_action('woocommerce_admin_order_items_after_captures', $order->get_id());
        }
        ?>
        </tbody>
    </table>
</div>

<!-- MOVED INTO ITEMS DOM AFTER DOM READY       -->
<div id="hipay-capture-items" class="wc-order-data-row wc-order-capture-items wc-order-data-row-toggle"
     style="display: none;">
    <table class="wc-order-totals">
        <tr>
            <td class="label"><?php esc_html_e('Amount already captured', 'hipayenterprise'); ?>:</td>
            <td class="total">
                <?php echo wc_price(Hipay_Order_Helper::get_total_captured($order), array('currency' => $order->get_currency())); // WPCS: XSS ok. ?></td>
        </tr>
        <tr>
            <td class="label"><?php esc_html_e('Total available to capture', 'hipayenterprise'); ?>:</td>
            <td class="total"><?php echo wc_price($order->get_total() - Hipay_Order_Helper::get_total_captured($order), array('currency' => $order->get_currency())); // WPCS: XSS ok. ?></td>
        </tr>
        <tr>
            <td class="label"><label
                        for="capture_amount"><?php esc_html_e('Capture amount', 'hipayenterprise'); ?>
                    :</label></td>
            <td class="total">
                <input type="text" id="capture_amount" name="capture_amount" class="wc_input_price"/>
                <div class="clear"></div>
            </td>
        </tr>
    </table>
    <div class="clear"></div>
    <div class="refund-actions">
        <?php
        $refund_amount = '<span class="wc-order-refund-amount">' . wc_price(0, array('currency' => $order->get_currency())) . '</span>';
        $gateway_name = false !== $payment_gateway ? (!empty($payment_gateway->method_title) ? $payment_gateway->method_title : $payment_gateway->get_title()) : __('Payment gateway', 'hipayenterprise');

        if (false !== $payment_gateway) {
            echo '<button type="button" class="button button-primary do-api-capture">' . sprintf(esc_html__('Capture %1$s via %2$s', 'hipayenterprise'), wp_kses_post($refund_amount), esc_html($gateway_name)) . '</button>';
        }
        ?>
        <?php /* translators: refund amount  */ ?>
        <button type="button"
                class="button cancel-action"><?php esc_html_e('Cancel', 'hipayenterprise'); ?></button>
        <input type="hidden" id="captured_amount" name="captured_amount"
               value="<?php echo esc_attr(Hipay_Order_Helper::get_total_captured($order)); ?>"/>
        <input type="hidden" id="captured_amount_remaining" name="captured_amount_remaining"
               value="<?php echo esc_attr($order->get_total() - Hipay_Order_Helper::get_total_captured($order)); ?>"/>
        <div class="clear"></div>
    </div>
</div>
