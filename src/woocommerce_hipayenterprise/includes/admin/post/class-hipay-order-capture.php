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
 * Object for HiPay Capture
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Order_Capture extends WC_Abstract_Order
{

    /**
     * Which data store to load.
     *
     * @var string
     */
    protected $data_store_name = 'order-capture';

    /**
     * This is the name of this object type.
     *
     * @var string
     */
    protected $object_type = 'order_capture';

    /**
     * Stores product data.
     *
     * @var array
     */
    protected $extra_data = array(
        'amount' => '',
        'reason' => '',
        'captured_by' => 0,
        'catpured_payment' => false,
    );

    /**
     * Get internal type (post type.)
     *
     * @return string
     */
    public function get_type()
    {
        return 'shop_order_capture';
    }

    /**
     * Get status - always completed for capture.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     * @return string
     */
    public function get_status($context = 'view')
    {
        return 'completed';
    }

    /**
     * Get a title for the new post type.
     */
    public function get_post_title()
    {
        // @codingStandardsIgnoreStart
        return sprintf(__('Invoice &ndash; %s', 'hipayenterprise'), strftime(_x('%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'hipayenterprise')));
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get refunded amount.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     * @return int|float
     */
    public function get_amount($context = 'view')
    {
        return $this->get_prop('amount', $context);
    }

    /**
     * Get capture reason.
     *
     * @since 2.2
     * @param  string $context What the value is for. Valid values are view and edit.
     * @return int|float
     */
    public function get_reason($context = 'view')
    {
        return $this->get_prop('reason', $context);
    }

    /**
     * Get ID of user who did the capture.
     *
     * @since 3.0
     * @param  string $context What the value is for. Valid values are view and edit.
     * @return int
     */
    public function get_captured_by($context = 'view')
    {
        return $this->get_prop('captured_by', $context);
    }

    /**
     * Return if the payment was captured via API.
     *
     * @since  3.3
     * @param  string $context What the value is for. Valid values are view and edit.
     * @return bool
     */
    public function get_captured_payment($context = 'view')
    {
        return $this->get_prop('captured_payment', $context);
    }

    /**
     * Get formatted captured amount.
     *
     * @since 2.4
     * @return string
     */
    public function get_formatted_capture_amount()
    {
        return apply_filters('woocommerce_formatted_refund_amount', wc_price($this->get_amount(), array('currency' => $this->get_currency())), $this);
    }

    /**
     * Set captured amount.
     *
     * @param string $value Value to set.
     * @throws WC_Data_Exception Exception if the amount is invalid.
     */
    public function set_amount($value)
    {
        $this->set_prop('amount', wc_format_decimal($value));
    }

    /**
     * Set capture reason.
     *
     * @param string $value Value to set.
     * @throws WC_Data_Exception Exception if the amount is invalid.
     */
    public function set_reason($value)
    {
        $this->set_prop('reason', $value);
    }

    /**
     * Set captured by.
     *
     * @param int $value Value to set.
     * @throws WC_Data_Exception Exception if the amount is invalid.
     */
    public function set_captured_by($value)
    {
        $this->set_prop('captured_by', absint($value));
    }

    /**
     * Set if the payment was captured via API.
     *
     * @since 3.3
     * @param bool $value Value to set.
     */
    public function set_captured_payment($value)
    {
        $this->set_prop('captured_payment', (bool)$value);
    }

    /**
     * Magic __get method for backwards compatibility.
     *
     * @param string $key Value to get.
     * @return mixed
     */
    public function __get($key)
    {
        wc_doing_it_wrong($key, 'Capture properties should not be accessed directly.', '3.0');
        /**
         * Maps legacy vars to new getters.
         */
        if ('reason' === $key) {
            return $this->get_reason();
        } elseif ('capture_amount' === $key) {
            return $this->get_amount();
        }
        return parent::__get($key);
    }

    /**
     * Gets an capture from the database.
     *
     * @deprecated 3.0
     * @param int $id (default: 0).
     * @return bool
     */
    public function get_capture($id = 0)
    {
        if (!$id) {
            return false;
        }

        $result = get_post($id);

        if ($result) {
            $this->populate($result);
            return true;
        }

        return false;
    }

    /**
     * Get capture amount.
     *
     * @deprecated 3.0
     * @return int|float
     */
    public function get_capture_amount()
    {
        return $this->get_amount();
    }

    /**
     * Get capture reason.
     *
     * @deprecated 3.0
     * @return int|float
     */
    public function get_capture_reason()
    {
        return $this->get_reason();
    }
}
