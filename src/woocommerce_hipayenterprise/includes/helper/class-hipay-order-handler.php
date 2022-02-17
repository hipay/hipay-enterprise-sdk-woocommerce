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

    private $plugin;

    /**
     * @var Hipay_Admin_Capture
     */
    protected $adminCapture;

    /**
     * Hipay_Order_Handler constructor.
     * @param $order
     * @param $plugin Hipay_Gateway_Abstract
     */
    public function __construct($order, $plugin)
    {
        $this->order = $order;
        $this->plugin = $plugin;
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
        $this->plugin->logs->logInfos("### paymentComplete : ".$txnId." ".$this->order->get_id());

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
            $this->plugin->logs->logInfos("### paymentComplete change status : ".$txnId." ".$this->order->get_id());
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
        $this->plugin->logs->logInfos("### paymentOnHold : ".$this->order->get_id());

        if (!in_array($this->order->get_status(), array('processing', 'completed', 'on-hold'), true)) {
            $this->plugin->logs->logInfos("### paymentOnHold change status: ".$this->order->get_id());
            $this->order->update_status('on-hold', $reason);
            wc_reduce_stock_levels($this->order->get_id());
            WC()->cart->empty_cart();
        } else {
            $this->plugin->logs->logInfos("### paymentOnHold add Note: ".$this->order->get_id());
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
        $this->plugin->logs->logInfos("### paymentFailed change status: ".$this->order->get_id());

        $this->order->update_status('failed', $reason);
        WC()->cart->empty_cart();
    }

    /**
     * Cancel order and add note.
     *
     * @param  string $reason Reason why the payment is Canceled.
     */
    public function paymentCancelled($reason = '')
    {
        $this->plugin->logs->logInfos("### paymentCancelled change status: ".$this->order->get_id());

        $this->order->update_status('cancelled', $reason);
        WC()->cart->empty_cart();
    }

    /**
     * Expire order and add note.
     *
     * @param  string $reason Reason why the payment is Expired.
     */
    public function paymentExpired($reason = '')
    {
        $this->plugin->logs->logInfos("### paymentExpired change status: ".$this->order->get_id());

        $this->order->update_status('expired', $reason);
        WC()->cart->empty_cart();
    }


    /**
     * Refund order and add note.
     *
     * @param  string $reason Reason why the payment is refunded.
     */
    public function paymentRefunded($reason = '')
    {
        $this->plugin->logs->logInfos("### paymentRefunded change status: ".$this->order->get_id());

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
        $this->plugin->logs->logInfos("### paymentPartiallyRefunded change status: ".$this->order->get_id());

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


    public function handleStatusChange($statusTo, $statusFrom){
        switch($statusTo){
            case "cancelled":
                $this->handleCancel($statusFrom);
                break;
            default:
                break;
        }
    }

    private function handleCancel($statusFrom){
        if($statusFrom == "pending" ||
            $statusFrom == "on-hold") {
            try {
                $this->plugin->process_cancel($this->order->get_id());
            } catch (Exception $e) {
                $displayMsg = __("There was an error on the cancellation of the HiPay transaction. You can see and cancel the transaction directly from HiPay's BackOffice",
                    "hipayenterprise");
                $displayMsg .= " (https://merchant.hipay-tpp.com/default/auth/login)\n";
                $displayMsg .= __("Message was : ", "hipayenterprise") . $e->getMessage();
                $displayMsg .= "\n";
                $displayMsg .= __('Transaction ID: ', "hipayenterprise") . $this->order->get_transaction_id() . "\n";

            }
        } else {
            $displayMsg = __("The HiPay transaction was not canceled because it's status doesn't allow cancellation. You can see and cancel the transaction directly from HiPay's BackOffice");
            $displayMsg .= " (https://merchant.hipay-tpp.com/default/auth/login)";
        }

        if(!empty($displayMsg)){
            $this->addNote($displayMsg);
        }

    }
}
