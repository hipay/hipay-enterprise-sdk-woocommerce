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
    exit; // Exit if accessed directly.
}

$who_captured = new WP_User($capture->get_captured_by());
?>
<tr class="refund <?php echo (!empty($class)) ? esc_attr($class) : ''; ?>"
    data-order_refund_id="<?php echo esc_attr($capture->get_id()); ?>">
    <td class="thumb">
        <div></div>
    </td>

    <td class="name">
        <?php
        if ($who_captured->exists()) {
            printf(
            /* translators: 1: refund id 2: refund date 3: username */
                esc_html__('Capture #%1$s - %2$s by %3$s', 'hipayenterprise'),
                esc_html($capture->get_id()),
                esc_html(wc_format_datetime($capture->get_date_created(), get_option('date_format') . ', ' . get_option('time_format'))),
                sprintf(
                    '<abbr class="refund_by" title="%1$s">%2$s</abbr>',
                    /* translators: 1: ID who refunded */
                    sprintf(esc_attr__('ID: %d', 'hipayenterprise'), absint($who_captured->ID)),
                    esc_html($who_captured->display_name)
                )
            );
        } else {
            printf(
            /* translators: 1: refund id 2: refund date */
                esc_html__('Capture #%1$s - %2$s', 'hipayenterprise'),
                esc_html($capture->get_id()),
                esc_html(wc_format_datetime($capture->get_date_created(), get_option('date_format') . ', ' . get_option('time_format')))
            );
        }
        ?>
        <?php if ($capture->get_reason()) : ?>
            <p class="description"><?php echo esc_html($capture->get_reason()); ?></p>
        <?php endif; ?>
        <input type="hidden" class="order_refund_id" name="order_refund_id[]"
               value="<?php echo esc_attr($capture->get_id()); ?>"/>
    </td>

    <td class="item_cost" width="1%">&nbsp;</td>
    <td class="quantity" width="1%">&nbsp;</td>

    <td class="line_cost" width="1%">
        <div class="view">
            <?php
            echo wp_kses_post(
                wc_price($capture->get_amount(), array('currency' => $capture->get_currency()))
            );
            ?>
        </div>
    </td>

    <?php
    if (wc_tax_enabled()) :
        $total_taxes = count($order->get_taxes());
        ?>
        <?php for ($i = 0; $i < $total_taxes; $i++) : ?>
        <td class="line_tax" width="1%"></td>
    <?php endfor; ?>
    <?php endif; ?>

    <td class="wc-order-edit-line-item">
    </td>
</tr>
