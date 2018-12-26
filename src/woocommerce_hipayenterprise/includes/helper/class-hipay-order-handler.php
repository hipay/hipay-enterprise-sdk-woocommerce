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

    /**
     * @var Hipay_Admin_Capture
     */
    protected $adminCapture;

    public function __construct($order)
    {
        $this->order = $order;
        $this->adminCapture = Hipay_Admin_Capture::initHiPayAdminCapture();
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
            if (in_array($this->order->get_status(),array('partial-captured', 'partial-refunded'))) {
                $this->order->update_status('on-hold');
            }

            $this->order->add_order_note($note);
            $this->order->payment_complete($txnId);
            WC()->cart->empty_cart();
        }
    }


    /**
     *  Payment update status order to custom status
     *
     * @param $status
     * @param string $reason
     */
    public function paymentUpdateStatus($status, $reason = '')
    {
        if (!in_array($this->order->get_status(), array('completed'), true)) {
            $this->order->update_status($status, $reason);
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
     * Partially capture order ( from HiPay BO)
     *
     * @param HiPay\Fullservice\Gateway\Mapper\TransactionMapper $transaction
     * @param string $reason
     * @throws Exception
     */
    public function paymentPartiallyCaptured($transaction, $reason = '')
    {
        if ( $transaction->getCapturedAmount() > 0) {
            $this->paymentUpdateStatus('partial-captured', $reason);

            // Test if capture is from HiPay BO
            if ($transaction->getOperation() == null && $transaction->getAttemptId() < 2) {
                $capture = array(
                    "amount" => $transaction->getCapturedAmount(),
                    "reason" => $reason,
                    "order_id" => $this->order->get_id()
                );

                $this->adminCapture->create_capture_item($capture);
                WC()->cart->empty_cart();
            }
        }
    }

    /**
     * Partially refund order
     * Order is still to "On hold" status but a refund is created
     *
     * @param \HiPay\Fullservice\Gateway\Model\Transaction $transaction
     * @param $amount
     * @param string $reason
     * @throws Exception
     */
    public function paymentPartiallyRefunded($transaction, $amount, $reason = '')
    {
        if ($amount > 0) {
            $this->paymentUpdateStatus('partial-refunded', $reason);

            if ($transaction->getOperation() == null && $transaction->getAttemptId() < 2) {
                $refund = array(
                    "amount" => $amount,
                    "reason" => $reason,
                    "order_id" => $this->order->get_id()
                );

                wc_create_refund($refund);
                WC()->cart->empty_cart();
            }

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
