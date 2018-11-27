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

use \HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Notification
{

    /**
     * @var \HiPay\Fullservice\Model\AbstractModel
     */
    protected $transaction;

    /**
     * @var Hipay_Order_Handler
     */
    protected $orderHandler;

    /**
     * @var Hipay_Gateway_Abstract
     */
    protected $plugin;

    /**
     * @var bool|WC_Order|WC_Refund
     */
    protected $order;

    /**
     * Hipay_Notification constructor.
     * @param Hipay_Gateway_Abstract $plugin
     * @param $data
     */
    public function __construct($plugin, $data)
    {
        $this->plugin = $plugin;
        $this->transaction = (new HiPay\Fullservice\Gateway\Mapper\TransactionMapper($data))->getModelObjectMapped();
        $plugin->logs->logInfos(print_r($this->transaction, true));

        // if cart_id exist or not
        if ($this->transaction->getOrder() == null || $this->transaction->getOrder()->getId() == null) {
            $plugin->logs->logErrors('Bad Callback initiated, no cart ID found ');
            header("HTTP/1.0 500 Internal server error");
            die('No Order found in transaction');
        }

        $orderId = strtok($this->transaction->getOrder()->getId(), "-");

        $this->order = wc_get_order($orderId);

        if (!$this->order) {
            $plugin->logs->logErrors('Bad Callback initiated, order could not be initiated ');
            header("HTTP/1.0 500 Internal server error");
            die('Order is doesnt exist');
        }

        $this->orderHandler = new Hipay_Order_Handler($this->order);
    }

    /**
     * Process Transaction from HiPay Callback
     *
     * @return bool
     * @throws Exception
     */
    public function processTransaction()
    {
        try {
            $this->plugin->logs->logInfos(
                "# ProcessTransaction for Order ID : " .
                $this->transaction->getOrder()->getId() .
                " and status " .
                $this->transaction->getStatus()
            );

            $this->orderHandler->addNote(Hipay_Helper::formatOrderData($this->transaction));
            switch ($this->transaction->getStatus()) {
                case TransactionStatus::CREATED:
                case TransactionStatus::CARD_HOLDER_ENROLLED:
                case TransactionStatus::CARD_HOLDER_NOT_ENROLLED:
                case TransactionStatus::UNABLE_TO_AUTHENTICATE:
                case TransactionStatus::CARD_HOLDER_AUTHENTICATED:
                case TransactionStatus::AUTHENTICATION_ATTEMPTED:
                case TransactionStatus::COULD_NOT_AUTHENTICATE:
                case TransactionStatus::AUTHENTICATION_FAILED:
                case TransactionStatus::COLLECTED:
                case TransactionStatus::ACQUIRER_FOUND:
                case TransactionStatus::ACQUIRER_NOT_FOUND:
                case TransactionStatus::RISK_ACCEPTED:
                case TransactionStatus::CAPTURE_REFUSED:
                    break;
                case TransactionStatus::BLOCKED:
                case TransactionStatus::CHARGED_BACK:
                    $this->orderHandler->paymentFailed("Charged back");
                    break;
                case TransactionStatus::DENIED:
                case TransactionStatus::REFUSED:
                    $this->orderHandler->paymentFailed(
                        __(
                            "Transaction  refused. Order was cancelled with transaction:",
                            "hipayenterprise"
                        )
                    );
                    break;
                case TransactionStatus::AUTHORIZED_AND_PENDING:
                    $this->orderHandler->paymentOnHold("Payment challenged");
                    Hipay_Helper::sendEmailFraud($this->order->get_id(), $this->plugin);
                    break;
                case TransactionStatus::AUTHENTICATION_REQUESTED:
                case TransactionStatus::AUTHORIZATION_REQUESTED:
                case TransactionStatus::PENDING_PAYMENT:
                    $this->orderHandler->paymentOnHold("pending payment");
                    break;
                case TransactionStatus::EXPIRED:
                case TransactionStatus::CANCELLED:
                    $this->orderHandler->paymentFailed(
                        __(
                            "Authorization cancelled. Order was cancelled with transaction:",
                            "hipayenterprise"
                        )
                    );
                    break;
                case TransactionStatus::AUTHORIZED: //116
                    $this->orderHandler->paymentOnHold(
                        __("Authorization successful for transaction.", "hipayenterprise")
                    );
                    break;
                case TransactionStatus::CAPTURED: //118
                case TransactionStatus::CAPTURE_REQUESTED: //117
                    if ($this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount()) {
                        $this->orderHandler->paymentOnHold(
                            __(
                                "Payment partially captured, amount:." . " " . $this->transaction->getCapturedAmount(),
                                "hipayenterprise"
                            ) . " " . $this->transaction->getTransactionReference()
                        );
                    } else {
                        $this->orderHandler->paymentComplete(
                            $this->transaction->getTransactionReference(),
                            "Payment complete"
                        );
                    }
                    break;
                case TransactionStatus::PARTIALLY_CAPTURED: //119
                    $this->orderHandler->paymentOnHold(
                        __(
                            "Payment partially captured, amount:." . " " . $this->transaction->getCapturedAmount(),
                            "hipayenterprise"
                        ) . " " . $this->transaction->getTransactionReference()
                    );
                    break;
                case TransactionStatus::REFUND_REQUESTED: //124
                    $this->orderHandler->addNote(__("Refund requested"));
                    break;
                case TransactionStatus::REFUNDED: //125
                    $this->orderHandler->paymentRefunded("Payment refunded");
                    break;
                case TransactionStatus::PARTIALLY_REFUNDED: //126
                    $this->orderHandler->paymentPartiallyRefunded(
                        $this->transaction->getRefundedAmount() - $this->order->get_total_refunded(),
                        __(
                            "Payment partially refunded, amount:." . " " . $this->transaction->getRefundedAmount(),
                            "hipayenterprise"
                        ) . " " . $this->transaction->getTransactionReference()
                    );
                    break;
                default:
                    break;
            }

            return true;
        } catch (Exception $e) {
            $this->orderHandler->addNote($e->getMessage());
            $this->plugin->logs->logException($e);
            throw new Exception($e->getMessage());
        }
    }
}
