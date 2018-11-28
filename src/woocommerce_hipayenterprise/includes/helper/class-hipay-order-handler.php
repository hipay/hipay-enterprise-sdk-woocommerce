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
class Hipay_Order_Handler
{

    /**
     * @var WC_Order
     */
    private $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Complete order, add transaction ID and note.
     *
     * @param  string $txnId Transaction ID.
     * @param  string $note Payment note.
     */
    public function paymentComplete($txnId = '', $note = '')
    {
        if (
            !in_array($this->order->get_status(), array('completed', 'refunded'), true)
            && (int)$this->order->get_total_refunded() === 0
        ) {
            $this->order->add_order_note($note);
            $this->order->payment_complete($txnId);
            WC()->cart->empty_cart();
        }
    }

    /**
     * Hold order and add note.
     *
     * @param  string $reason Reason why the payment is on hold.
     */
    public function paymentOnHold($reason = '')
    {
        if (!in_array($this->order->get_status(), array('processing', 'completed', 'on-hold'), true)) {
            $this->order->update_status('on-hold', $reason);
            wc_reduce_stock_levels($this->order->get_id());
            WC()->cart->empty_cart();
        } else {
            $this->addNote($reason);
        }
    }

    /**
     * Failed order and add note.
     *
     * @param  string $reason Reason why the payment is Failed.
     */
    public function paymentFailed($reason = '')
    {
        $this->order->update_status('failed', $reason);
        WC()->cart->empty_cart();
    }

    /**
     * Refund order and add note.
     *
     * @param  string $reason Reason why the payment is refunded.
     */
    public function paymentRefunded($reason = '')
    {
        $this->order->update_status('refunded', $reason);
        WC()->cart->empty_cart();
    }

    /**
     * Partially refund order
     * Order is still to "On hold" status but a refund is created
     *
     * @param $amount
     * @param string $reason
     * @throws Exception
     */
    public function paymentPartiallyRefunded($amount, $reason = '')
    {
        if ($amount > 0) {
            $this->paymentOnHold($reason);

            $refund = array(
                "amount" => $amount,
                "reason" => $reason,
                "order_id" => $this->order->get_id()
            );

            wc_create_refund($refund);
            WC()->cart->empty_cart();
        }
    }

    /**
     * Add note to order
     *
     * @param $note
     */
    public function addNote($note)
    {
        $this->order->add_order_note($note);
    }
}
