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
 * WC Order Capture Data Store: Stored in CPT.
 *
 * This Data Store is from refund data store provided by woocomerce
 *
 * @version  3.0.0
 */
class WC_Order_Capture_Data_Store_CPT extends Abstract_WC_Order_Data_Store_CPT implements WC_Object_Data_Store_Interface, WC_Order_Refund_Data_Store_Interface
{

    /**
     * Data stored in meta keys, but not considered "meta" for an order.
     *
     * @since 3.0.0
     * @var array
     */
    protected $internal_meta_keys = array(
        '_order_currency',
        '_cart_discount',
        '_capture_amount',
        '_captured_by',
        '_captured_payment',
        '_capture_reason',
        '_cart_discount_tax',
        '_order_shipping',
        '_order_shipping_tax',
        '_order_tax',
        '_order_total',
        '_order_version',
        '_prices_include_tax',
        '_payment_tokens',
    );

    /**
     * Delete a capture - no trash is supported.
     *
     * @param WC_Order $order Order object.
     * @param array $args Array of args to pass to the delete method.
     */
    public function delete(&$order, $args = array())
    {
        $id = $order->get_id();

        if (!$id) {
            return;
        }

        wp_delete_post($id);
        $order->set_id(0);
        do_action('woocommerce_delete_order_capture', $id);
    }

    /**
     * Read capture data. Can be overridden by child classes to load other props.
     *
     * @param WC_Order $capture Capture object.
     * @param object $post_object Post object.
     * @since 3.0.0
     */
    protected function read_order_data(&$capture, $post_object)
    {
        parent::read_order_data($capture, $post_object);
        $id = $capture->get_id();
        $capture->set_props(
            array(
                'amount' => get_post_meta($id, '_capture_amount', true),
                'captured_by' => metadata_exists('post', $id, '_captured_by') ? get_post_meta($id, '_captured_by', true) : absint($post_object->post_author),
                'captured_payment' => wc_string_to_bool(get_post_meta($id, '_captured_payment', true)),
                'reason' => metadata_exists('post', $id, '_capture_reason') ? get_post_meta($id, '_capture_reason', true) : $post_object->post_excerpt,
            )
        );
    }

    /**
     * Helper method that updates all the post meta for an order based on it's settings in the WC_Order class.
     *
     * @param WC_Order $capture Capture object.
     * @since 3.0.0
     */
    protected function update_post_meta(&$capture)
    {
        parent::update_post_meta($capture);

        $updated_props = array();
        $meta_key_to_props = array(
            '_capture_amount' => 'amount',
            '_captured_by' => 'captured_by',
            '_captured_payment' => 'captured_payment',
            '_capture_reason' => 'reason',
        );

        $props_to_update = $this->get_props_to_update($capture, $meta_key_to_props);
        foreach ($props_to_update as $meta_key => $prop) {
            $value = $capture->{"get_$prop"}('edit');
            update_post_meta($capture->get_id(), $meta_key, $value);
            $updated_props[] = $prop;
        }

        do_action('woocommerce_order_capture_object_updated_props', $capture, $updated_props);
    }

    /**
     * Get a title for the new post type.
     *
     * @return string
     */
    protected function get_post_title()
    {
        return sprintf(
        /* translators: %s: Order date */
            __('Capture &ndash; %s', 'woocommerce'),
            strftime(_x('%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce')) // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment, WordPress.WP.I18n.UnorderedPlaceholdersText
        );
    }
}
