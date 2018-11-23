<?php
if (!defined('ABSPATH')) {
    exit;
}

use \HiPay\Fullservice\Enum\Transaction\TransactionStatus;

class Hipay_Notification
{

    protected $transaction;

    protected $orderHandler;

    protected $plugin;

    protected $order;

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
                default:
                    $orderState = 'skip';
                    break;
                case TransactionStatus::BLOCKED:
                case TransactionStatus::CHARGED_BACK:
                    $this->orderHandler->paymentFailed("Charged back");
                case TransactionStatus::DENIED:
                case TransactionStatus::REFUSED:
                    $this->orderHandler->paymentFailed(
                        __("Transaction  refused. Order was cancelled with transaction:", 'hipayenterprise')
                    );
                    break;
                case TransactionStatus::AUTHORIZED_AND_PENDING:
                    $this->orderHandler->paymentOnHold("Payment challenged");
                    Hipay_Helper::sendEmailFraud($this->transaction->getOrder()->getId(), $this->plugin);
                    break;
                case TransactionStatus::AUTHENTICATION_REQUESTED:
                case TransactionStatus::AUTHORIZATION_REQUESTED:
                case TransactionStatus::PENDING_PAYMENT:
                    $this->orderHandler->paymentOnHold("pending payment");
                    break;
                case TransactionStatus::EXPIRED:
                case TransactionStatus::CANCELLED:
                    $this->orderHandler->paymentFailed(
                        __("Authorization cancelled. Order was cancelled with transaction:", 'hipayenterprise')
                    );
                    break;
                case TransactionStatus::AUTHORIZED: //116
                    $this->orderHandler->paymentOnHold(
                        __("Authorization successful for transaction.", 'hipayenterprise')
                    );
                    break;
                case TransactionStatus::CAPTURED: //118
                case TransactionStatus::CAPTURE_REQUESTED: //117
                    if ($this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount()) {
                        $this->orderHandler->paymentOnHold(
                            __(
                                "Payment partially captured, amount:." . " " . $this->transaction->getCapturedAmount(),
                                'hipayenterprise'
                            ) . " " . $this->transaction->getTransactionReference()
                        );
                    } else {
                        if ($this->order->get_status() == 'on-hold') {
                            $this->orderHandler->paymentComplete(
                                $this->transaction->getTransactionReference(),
                                "Payment complete"
                            );
                        }
                    }

                    break;
                case TransactionStatus::PARTIALLY_CAPTURED: //119
                    $this->orderHandler->paymentOnHold(
                        __(
                            "Payment partially captured, amount:." . " " . $this->transaction->getCapturedAmount(),
                            'hipayenterprise'
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
                    $this->orderHandler->paymentOnHold(
                        __(
                            "Payment partially refunded, amount:." . " " . $this->transaction->getRefundedAmount(),
                            'hipayenterprise'
                        ) . " " . $this->transaction->getTransactionReference()
                    );
                    break;
                case TransactionStatus::CAPTURE_REFUSED:
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
