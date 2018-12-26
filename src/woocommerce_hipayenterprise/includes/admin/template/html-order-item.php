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
$product = $item->get_product();
$row_class = apply_filters('woocommerce_admin_html_order_item_class', !empty($class) ? $class : '', $item, $order);
?>
<tr class="item hipay-captured <?php echo esc_attr($row_class); ?>"
    data-order_item_id="<?php echo esc_attr($item_id); ?>">
    <td class="thumb"></td>
    <td class="name"></td>
    <td class="item_cost">
        <div class="view">
            <?php
            if ($captured_qty = Hipay_Order_Helper::get_qty_captured_for_item($item_id, 'line_item', $order)) {
                echo '<small class="captured">' . esc_html__("Captured with HiPay", "woocommerce") . '</small>';
            }
            ?>
        </div>
    </td>
    <td class="quantity" width="1%">
        <div class="view">
            <?php
            if ($captured_qty = Hipay_Order_Helper::get_qty_captured_for_item($item_id, 'line_item', $order)) {
                echo '<small class="captured">' . ($captured_qty) . '</small>';
            }
            ?>
        </div>
    </td>
    <td class="line_cost" width="1%" data-sort-value="<?php echo esc_attr($item->get_total()); ?>">
        <div class="view">
            <?php
            if ($captured = Hipay_Order_Helper::get_total_captured_for_item($item_id, 'line_item', $order)) {
                echo '<small class="captured">' . wc_price($captured, array('currency' => $order->get_currency())) . '</small>';
            }
            ?>
        </div>
    </td>

    <?php
    if (($tax_data = $item->get_taxes()) && wc_tax_enabled()) {
        foreach ($order->get_taxes() as $tax_item) {
            $tax_item_id = $tax_item->get_rate_id();
            $tax_item_total = isset($tax_data['total'][$tax_item_id]) ? $tax_data['total'][$tax_item_id] : '';
            $tax_item_subtotal = isset($tax_data['subtotal'][$tax_item_id]) ? $tax_data['subtotal'][$tax_item_id] : '';
            ?>
            <td class="line_tax" width="1%">
                <div class="view">
                    <?php
                    if ($captured = Hipay_Order_Helper::get_tax_captured_for_item($item_id, $tax_item_id, 'line_item', $order)) {
                        echo '<small class="captured">' . wc_price($captured, array('currency' => $order->get_currency())) . '</small>';
                    }
                    ?>
                </div>
            </td>
            <?php
        }
    }
    ?>
    <td class="wc-order-edit-line-item" width="1%"></td>
</tr>
