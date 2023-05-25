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

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Admin_Capture extends Hipay_Admin_Page
{

    private static $instance;

    /**
     * Constructor
     */
    public function __construct()
    {
	    parent::__construct();

        add_action('woocommerce_order_item_add_action_buttons', array($this, 'add_action_buttons'));
        add_action('woocommerce_admin_order_totals_after_total', array($this, 'totals_after_total'));
        add_action('wp_ajax_woocommerce_capture_line_items', array($this, 'capture_line_items'));
        add_action('woocommerce_order_item_line_item_html', array($this, 'admin_order_item_line_items_html'), 10, 3);
        add_action(
            'woocommerce_admin_order_items_after_shipping',
            array($this, 'admin_order_items_after_shipping'),
            10,
            1
        );
        add_filter('woocommerce_data_stores', array($this, 'filter_woocommerce_data_stores'));
    }

    /**
     *  Add complement information for shipping item in order view
     *
     * @param string $order_id
     */
    public function admin_order_items_after_shipping($order_id)
    {
        $order = wc_get_order($order_id);
        if (Hipay_Order_Helper::get_total_captured($order) > 0) {
            $line_items_shipping = $order->get_items('shipping');
            foreach ($line_items_shipping as $item_id => $item) {
                if (Hipay_Order_Helper::get_total_captured_for_item($item_id, 'shipping', $order)) {
                    Hipay_Helper::process_template(
                        'html-order-shipping.php',
                        'admin',
                        array(
                            'item_id' => $item_id,
                            'item' => $item,
                            'order' => $order
                        )
                    );
                }
            }
        }
    }

    /**
     * Add complement information for an item in order view
     *
     * @param $item_id
     * @param null $item
     * @param string $order
     */
    public function admin_order_item_line_items_html($item_id, $item = null, $order = null)
    {
        if ($item) {
            Hipay_Helper::process_template(
                'html-order-item.php',
                'admin',
                array(
                    'item_id' => $item_id,
                    'item' => $item,
                    'order' => $order
                )
            );
        }
    }

    /**
     *  Add specific data-store for HiPay capture
     *
     * @param array $stores
     * @return array
     */
    public function filter_woocommerce_data_stores($stores)
    {
        $stores['order-capture'] = 'WC_Order_Capture_Data_Store_CPT';
        return $stores;
    }

    /**
     *  Action to save capture from view
     */
    public function capture_line_items()
    {
        ob_start();

        check_ajax_referer('order-item', 'security');

        if (!current_user_can('edit_shop_orders')) {
            wp_die(-1);
        }

        $order_id = absint($_POST['order_id']);
        $capture_amount = wc_format_decimal(
            sanitize_text_field(wp_unslash($_POST['capture_amount'])),
            wc_get_price_decimals()
        );
        $captured_amount = wc_format_decimal(
            sanitize_text_field(wp_unslash($_POST['captured_amount'])),
            wc_get_price_decimals()
        );
        $line_item_qtys = json_decode(sanitize_text_field(wp_unslash($_POST['line_item_qtys'])), true);
        $line_item_totals = json_decode(sanitize_text_field(wp_unslash($_POST['line_item_totals'])), true);
        $line_item_tax_totals = json_decode(sanitize_text_field(wp_unslash($_POST['line_item_tax_totals'])), true);
        $response_data = array();

        try {
            $order = wc_get_order($order_id);
            $max_capture = wc_format_decimal(
                $order->get_total() - Hipay_Order_Helper::get_total_captured($order),
                wc_get_price_decimals()
            );

            if (!$capture_amount || $max_capture < $capture_amount || 0 > $capture_amount) {
                throw new exception(__('Invalid capture amount', 'hipayenterprise'));
            }

            if ($captured_amount !==
                wc_format_decimal(Hipay_Order_Helper::get_total_captured($order), wc_get_price_decimals())) {
                throw new exception(__('Error processing capture. Please try again.', 'hipayenterprise'));
            }

            // Prepare line items which we are capturing.
            $line_items = array();
            $item_ids = array_unique(array_merge(array_keys($line_item_qtys, $line_item_totals)));

            foreach ($item_ids as $item_id) {
                $line_items[$item_id] = array(
                    'qty' => 0,
                    'capture_total' => 0,
                    'capture_tax' => array(),
                );
            }
            foreach ($line_item_qtys as $item_id => $qty) {
                $line_items[$item_id]['qty'] = max($qty, 0);
            }
            foreach ($line_item_totals as $item_id => $total) {
                $line_items[$item_id]['capture_total'] = wc_format_decimal($total);
            }
            foreach ($line_item_tax_totals as $item_id => $tax_totals) {
                $line_items[$item_id]['capture_tax'] = array_filter(array_map('wc_format_decimal', $tax_totals));
            }

            // Create the capture object.
            $capture = $this->create_capture_item(
                array(
                    'amount' => $capture_amount,
                    'order_id' => $order_id,
                    'line_items' => $line_items,
                    'capture_payment' => '1',
                )
            );

            if (is_wp_error($capture)) {
                throw new Exception($capture->get_error_message());
            }

            if (did_action('woocommerce_order_fully_captured')) {
                $response_data['status'] = 'fully_captured';
            }

            wp_send_json_success($response_data);

        } catch (Exception $e) {
            wp_send_json_error(array('error' => $e->getMessage()));
        }
    }


    /**
     * Create a new order capture programmatically.
     *
     * @throws Exception Throws exceptions when fail to create, but returns WP_Error instead.
     * @param array $args New Capture arguments.
     * @return Hipay_Order_Capture|WP_Error
     */
    public function create_capture_item($args = array())
    {
        $default_args = array(
            'amount' => 0,
            'order_id' => 0,
            'capture_id' => 0,
            'line_items' => array(),
            'capture_payment' => false,
        );

        try {
            $args = wp_parse_args($args, $default_args);
            $order = wc_get_order($args['order_id']);

            if (!$order) {
                throw new Exception(__('Invalid order ID.', 'hipayenterprise'));
            }

            $remaining_capture_amount = wc_format_decimal(
                $order->get_total() - Hipay_Order_Helper::get_total_captured($order),
                wc_get_price_decimals()
            );
            $remaining_capture_items = absint(
                $order->get_item_count() - Hipay_Order_Helper::get_item_count_captured('', $order)
            );

            $capture_item_count = 0;
            $capture = new Hipay_Order_Capture($args['capture_id']);

            if (0 > $args['amount'] || $args['amount'] > $remaining_capture_amount) {
                throw new Exception(__('Invalid capture amount.', 'hipayenterprise'));
            }

            $capture->set_currency($order->get_currency());
            $capture->set_amount($args['amount']);
            $capture->set_parent_id(absint($args['order_id']));
            $capture->set_captured_by(get_current_user_id() ? get_current_user_id() : 1);

            // Negative line items.
            if (count($args['line_items']) > 0) {
                $items = $order->get_items(array('line_item', 'fee', 'shipping'));

                foreach ($items as $item_id => $item) {
                    if (!isset($args['line_items'][$item_id])) {
                        continue;
                    }

                    $qty = isset($args['line_items'][$item_id]['qty']) ? $args['line_items'][$item_id]['qty'] : 0;
                    $capture_total = $args['line_items'][$item_id]['capture_total'];
                    $capture_tax = isset($args['line_items'][$item_id]['capture_tax']) ? array_filter(
                        (array)$args['line_items'][$item_id]['capture_tax']
                    ) : array();

                    if (empty($qty) && empty($capture_total) && empty($args['line_items'][$item_id]['capture_tax'])) {
                        continue;
                    }

                    $class = get_class($item);
                    $captured_item = new $class($item);
                    $captured_item->set_id(0);
                    $captured_item->add_meta_data('_captured_item_id', $item_id, true);
                    $captured_item->set_total(abs($capture_total));
                    $captured_item->set_taxes(
                        array(
                            'total' => $capture_tax,
                            'subtotal' => $capture_tax,
                        )
                    );

                    if (is_callable(array($captured_item, 'set_subtotal'))) {
                        $captured_item->set_subtotal(abs($capture_total));
                    }

                    if (is_callable(array($captured_item, 'set_quantity'))) {
                        $captured_item->set_quantity($qty);
                    }

                    $capture->add_item($captured_item);
                    $capture_item_count += $qty;
                }
            }

            $capture->update_taxes();
            $capture->calculate_totals(false);
            $capture->set_total($args['amount']);

            // this should remain after update_taxes(), as this will save the order, and write the current date to the db
            // so we must wait until the order is persisted to set the date.
            if (isset($args['date_created'])) {
                $capture->set_date_created($args['date_created']);
            }

            /**
             * Action hook to adjust refund before save.
             *
             * @since 3.0.0
             */
            do_action('woocommerce_create_capture', $capture, $args);

            if ($capture->save()) {
                if ($args['capture_payment']) {
                    $result = $this->manual_capture($order, $capture->get_amount());

                    if (is_wp_error($result)) {
                        $capture->delete();
                        return $result;
                    }

                    $capture->set_captured_payment(true);
                    $capture->save();
                }


                // Trigger notification emails.
                if (($remaining_capture_amount - $args['amount']) > 0 ||
                    ($order->has_free_item() && ($remaining_capture_items - $capture_item_count) > 0)) {
                    do_action('woocommerce_order_partially_captured', $order->get_id(), $capture->get_id());
                } else {
                    do_action('woocommerce_order_fully_captured', $order->get_id(), $capture->get_id());

                    //$parent_status = apply_filters('woocommerce_order_fully_captured_status', 'refunded', $order->get_id(), $capture->get_id());
                }
            }

            do_action('woocommerce_capture_created', $capture->get_id(), $args);
            do_action('woocommerce_order_captured', $order->get_id(), $capture->get_id());

        } catch (Exception $e) {
            if (isset($capture) && is_a($capture, 'Hipay_Order_Capture')) {
                wp_delete_post($capture->get_id(), true);
            }
            return new WP_Error('error', $e->getMessage());
        }

        return $capture;
    }


    /**
     * Try to capture the payment for an order via the gateway.
     *
     * @since 3.0.0
     * @throws Exception Throws exceptions when fail to refund, but returns WP_Error instead.
     * @param WC_Order $order Order instance.
     * @param string $amount Amount to refund.
     * @param string $reason Refund reason.
     * @return bool|WP_Error
     */
    function manual_capture($order, $amount, $reason = '')
    {
        try {
            if (!is_a($order, 'WC_Order')) {
                throw new Exception(__('Invalid order.', 'hipayenterprise'));
            }

            $gateway_controller = WC_Payment_Gateways::instance();
            $all_gateways = $gateway_controller->payment_gateways();
            $payment_method = $order->get_payment_method();
            $gateway = isset($all_gateways[$payment_method]) ? $all_gateways[$payment_method] : 0;

            if (!$gateway) {
                throw new Exception(__('The payment gateway for this order does not exist.', 'hipayenterprise'));
            }

            if (!$gateway->supports('captures')) {
                throw new Exception(
                    __('The payment gateway for this order does not support automatic captures.', 'hipayenterprise')
                );
            }

            $result = $gateway->process_capture($order->get_id(), $amount);

            if (!$result) {
                throw new Exception(
                    __(
                        'An error occurred while attempting to create the refund using the payment gateway API.',
                        'woocommerce'
                    )
                );
            }

            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            return true;

        } catch (Exception $e) {
            return new WP_Error('error', $e->getMessage());
        }
    }

    /**
     * Add additional button for capture in order view
     *
     * @param WC_Order $order
     */
    public function add_action_buttons($order)
    {
        if (WC()->payment_gateways()) {
            $payment_gateways = WC()->payment_gateways->payment_gateways();
        }

        if (
            isset($payment_gateways[$order->get_payment_method()]) &&
            $payment_gateways[$order->get_payment_method()]->supports('captures')
        ) {
            $payment_gateway = $payment_gateways[$order->get_payment_method()];


            if (!in_array($order->get_status(), array('completed', 'canceled'), true)
                && 0 < $order->get_total() - Hipay_Order_Helper::get_total_captured($order)) {
                Hipay_Helper::process_template(
                    'html-order-additional.php',
                    'admin',
                    array(
                        'payment_gateway' => $payment_gateway,
                        'order' => $order
                    )
                );
            }
        }
    }

    /**
     * Add line in summary detail
     */
    public function totals_after_total($order_id)
    {
        $order = wc_get_order($order_id);
        if (Hipay_Order_Helper::get_total_captured($order) > 0) {
            ?>
            <tr>
                <td class="label refunded-total"><?php esc_html_e('Captured', 'hipayenterprise'); ?>:</td>
                <td width="1%"></td>
                <td class="total refunded-total"><?php echo wc_price(
                        Hipay_Order_Helper::get_total_captured($order),
                        array('currency' => $order->get_currency())
                    ); ?></td
            </tr>
            <?php
        }
    }


    /**
     * @return Hipay_Admin_Capture
     */
    public static function initHiPayAdminCapture()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}


