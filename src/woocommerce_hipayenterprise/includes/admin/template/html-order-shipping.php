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
<tr class="shipping <?php echo (!empty($class)) ? esc_attr($class) : ''; ?>"
    data-order_item_id="<?php echo esc_attr($item_id); ?>">
    <td class="thumb">
        <div></div>
    </td>
    <td class="name"></td>
    <td class="item_cost" width="1%">
        <div class="view"><?php if ($captured = Hipay_Order_Helper::get_total_captured_for_item($item_id, 'shipping', $order)) {
                echo '<small class="captured">' . esc_html__("Captured with HiPay", "hipayenterprise") . '</small>';
            }
            ?>
        </div>
    </td>
    <td class="quantity" width="1%">&nbsp;</td>
    <td class="line_cost" width="1%">
        <div class="view">
            <?php
            $captured = Hipay_Order_Helper::get_total_captured_for_item($item_id, 'shipping', $order);
            if ($captured) {
                echo '<small class="captured">' . wc_price($captured, array('currency' => $order->get_currency())) . '</small>';
            }
            ?>
        </div>
    </td>

    <?php
    if (wc_tax_enabled()) {
        $order_taxes = $order->get_taxes();
        foreach ($order_taxes as $tax_item) {
            $tax_item_id = $tax_item->get_rate_id();
            $tax_item_total = isset($tax_data['total'][$tax_item_id]) ? $tax_data['total'][$tax_item_id] : '';
            ?>
            <td class="line_tax" width="1%">
                <div class="view">
                    <?php
                    $captured = Hipay_Order_Helper::get_tax_captured_for_item($item_id, $tax_item_id, 'shipping', $order);
                    if ($captured) {
                        echo '<small class="captured">' . wc_price($captured, array('currency' => $order->get_currency())) . '</small>';
                    }
                    ?>
                </div>
            </td>
            <?php
        }
    }
    ?>
    <td class="wc-order-edit-line-item"></td>
</tr>
