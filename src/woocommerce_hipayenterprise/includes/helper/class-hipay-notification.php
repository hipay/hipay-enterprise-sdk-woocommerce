<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \HiPay\Fullservice\Enum\Transaction\TransactionStatus;

class Hipay_Notification {

	protected $transaction;

	protected $order;

	protected $plugin;

	public function __construct( $plugin, $data ) {
		$this->plugin      = $plugin;
		$this->transaction = ( new HiPay\Fullservice\Gateway\Mapper\TransactionMapper( $data ) )->getModelObjectMapped();
		$plugin->logs->logInfos( print_r( $this->transaction, true ) );

		// if cart_id exist or not
		if ( $this->transaction->getOrder() == null || $this->transaction->getOrder()->getId() == null ) {
			$plugin->logs->logErrors( 'Bad Callback initiated, no cart ID found ' );
			header( "HTTP/1.0 500 Internal server error" );
			die( 'No Order found in transaction' );
		}

		$this->order = new WC_Order( $this->transaction->getOrder()->getId() );

		if ( ! $this->order ) {
			$plugin->logs->logErrors( 'Bad Callback initiated, order could not be initiated ' );
			header( "HTTP/1.0 500 Internal server error" );
			die( 'Order is doesnt exist' );
		}
	}

	/**
	 * Process Transaction from HiPay Callback
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function processTransaction() {
		try {
			$this->plugin->logs->logInfos(
				"# ProcessTransaction for Order ID : " .
				$this->transaction->getOrder()->getId() .
				" and status " .
				$this->transaction->getStatus()
			);

			switch ( $this->transaction->getStatus() ) {
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
					$this->updateOrderStatus( _PS_OS_ERROR_ );
					break;
				case TransactionStatus::DENIED:
				case TransactionStatus::REFUSED:
					$this->updateOrderStatus(
						'canceled',
						0,
						0,
						__( "Transaction  refused. Order was cancelled with transaction:", 'hipayenterprise' ) );
					// Notify website admin for a challenged transaction
					//HipayMail::sendMailPaymentDeny($this->context, $this->module, $this->order);

					break;
				case TransactionStatus::AUTHORIZED_AND_PENDING:
					$this->updateOrderStatus( Configuration::get( 'HIPAY_OS_CHALLENGED', null, null, 1 ) );

					// Notify website admin for a challenged transaction
					// HipayMail::sendMailPaymentFraud($this->context, $this->module, $this->order);
					break;
				case TransactionStatus::AUTHENTICATION_REQUESTED:
				case TransactionStatus::AUTHORIZATION_REQUESTED:
				case TransactionStatus::PENDING_PAYMENT:
					//$this->updateOrderStatus(Configuration::get('HIPAY_OS_PENDING', null, null, 1));
					break;
				case TransactionStatus::EXPIRED:
					//$this->updateOrderStatus(Configuration::get('HIPAY_OS_EXPIRED', null, null, 1));
					break;
				case TransactionStatus::CANCELLED:
					$this->updateOrderStatus(
						'canceled',
						0,
						0,
						__( "Authorization cancelled. Order was cancelled with transaction:", 'hipayenterprise' ) );
					break;
				case TransactionStatus::AUTHORIZED: //116
					$this->updateOrderStatus(
						'on-hold',
						0,
						0,
						__( "Authorization successful for transaction.", 'hipayenterprise' ) );
					break;
				case TransactionStatus::CAPTURED: //118
				case TransactionStatus::CAPTURE_REQUESTED: //117
					$orderState = 'processing';
					if ( $this->transaction->getCapturedAmount() < $this->transaction->getAuthorizedAmount() ) {
						$this->updateOrderStatus(
							'on-hold',
							0,
							1,
							__( "Payment partially captured, amount:." . " " . $this->transaction->getCapturedAmount(),
								'hipayenterprise' ) . " " . $this->transaction->getTransactionReference(), 0 );
					} else {
						$this->updateOrderStatus(
							$orderState,
							1,
							1,
							__( "Payment successful for transaction",
								'hipayenterprise' ) . " " . $this->transaction->getTransactionReference() );

						$this->order->payment_complete( $this->transaction->getTransactionReference() );
						WC()->cart->empty_cart();
					}

					break;
				case TransactionStatus::PARTIALLY_CAPTURED: //119
					$this->updateOrderStatus(
						'on-hold',
						0,
						1,
						__( "Payment partially captured, amount:." . " " . $this->transaction->getCapturedAmount(),
							'hipayenterprise' ) . " " . $this->transaction->getTransactionReference(), 0 );
					break;
				case TransactionStatus::REFUND_REQUESTED: //124
				case TransactionStatus::REFUNDED: //125
				case TransactionStatus::PARTIALLY_REFUNDED: //126
					//$this->refundOrder();
					break;
				case TransactionStatus::CHARGED_BACK:
					//$this->updateOrderStatus(Configuration::get('HIPAY_OS_CHARGEDBACK', null, null, 1));
					break;
				case TransactionStatus::CAPTURE_REFUSED:
					//$this->updateOrderStatus(Configuration::get('HIPAY_OS_CAPTURE_REFUSED', null, null, 1));
					break;
			}

			return true;
		} catch ( Exception $e ) {
			$this->order->add_order_note( $e->getMessage() );
			$this->plugin->logs->logException( $e );
			throw new Exception( $e->getMessage() );
		}
	}

	/**
	 * @param $status
	 */
	public function updateOrderStatus($status, $captured, $processed, $message) {


		$this->order->update_status($status, $message, 0);

		$this->plugin->logs->loginfos($message);
	}
}
