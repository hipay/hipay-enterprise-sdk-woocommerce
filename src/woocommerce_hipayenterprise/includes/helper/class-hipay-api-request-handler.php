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

use HiPay\Fullservice\Enum\Transaction\TransactionState;
use HiPay\Fullservice\Enum\Transaction\Operation;
use HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Api_Request_Handler
{

    /**
     * @var Hipay_Api
     */
    private $api;

    /**
     * @var Hipay_Gateway_Abstract
     */
    private $plugin;

    /**
     * @var Hipay_Cart_Formatter|null|Wc_Hipay_Admin_Assets
     */
    protected $cartFormatter;

    /**
     * @var Hipay_Delivery_Formatter|null
     */
    protected $deliveryFormatter;

    /**
     * Hipay_Api_Request_Handler constructor.
     * @param $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->api = new Hipay_Api($plugin);
        $this->cartFormatter = Hipay_Cart_Formatter::initHiPayCartFormatter();
        $this->deliveryFormatter = Hipay_Delivery_Formatter::initHiPayDeliveryFormatter();
    }

    /**
     * @return Hipay_Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     *  Handle process payment for an Operating mode
     *
     * @param $params
     * @return string
     * @throws Hipay_Payment_Exception
     * @throws Exception
     */
    public function handleCreditCard($params)
    {

        $mode = $this->plugin->confHelper->getPaymentGlobal()["operating_mode"];

        if (isset($params["oneClick"]) && $params["oneClick"]) {
            $mode = OperatingMode::HOSTED_FIELDS;
        }

        if ($mode == OperatingMode::HOSTED_FIELDS) {
            return $this->handleDirectOrder($params, true);
        } else if ($mode == OperatingMode::HOSTED_PAGE) {
            return $this->handleHostedPayment($params);
        }
    }

    /**
     * @param $params
     * @return string
     * @throws Hipay_Payment_Exception
     */
    public function handleLocalPayment($params)
    {
        return $this->handleDirectOrder($params);
    }

    /**
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function handleCancel($params)
    {
        $order = wc_get_order($params["order_id"]);

        $displayMsg = null;
        $orderHandler = new Hipay_Order_Handler($order, $this->plugin);

        if ($params['transaction_reference'] === false || empty($params['transaction_reference'])) {
            $displayMsg = __("The HiPay transaction was not canceled because no transaction reference exists. You can see and cancel the transaction directly from HiPay's BackOffice",
                "hipayenterprise");
            $displayMsg .= " (https://merchant.hipay-tpp.com/default/auth/login)";
        } else {
            // If current transaction status is cancelled, it means we are currently handling the 115 notification from HiPay,
            // and the transaction is already cancelled
            if (!Hipay_Transactions_Helper::isTransactionCancelled($order->get_id())) {
                try {
                    $result = $this->api->requestMaintenance($params);

                    if (!in_array($result->getStatus(), array(TransactionStatus::AUTHORIZATION_CANCELLATION_REQUESTED, TransactionStatus::CANCELLED))) {
                        $displayMsg = __("There was an error on the cancellation of the HiPay transaction. You can see and cancel the transaction directly from HiPay's BackOffice",
                            "hipayenterprise");
                        $displayMsg .= " (https://merchant.hipay-tpp.com/default/auth/login)";
                        $status = $result->getStatus();
                        $transactionRef = $result->getTransactionReference();
                    } else {
                        $orderHandler->addNote(Hipay_Helper::formatOrderData($result));
                    }
                } catch (Exception $e) {
                    $displayMsg = __("There was an error on the cancellation of the HiPay transaction. You can see and cancel the transaction directly from HiPay's BackOffice",
                        "hipayenterprise");
                    $displayMsg .= " (https://merchant.hipay-tpp.com/default/auth/login)\n";
                    $displayMsg .= __("Message was : ", "hipayenterprise") . '[' . preg_replace("/\r|\n/", "", $e->getMessage()) . ']';

                    $transactionRef = $order->get_transaction_id();
                }
            }
        }

        if (!empty($displayMsg)) {
            $displayMsg .= "\n";
            $displayMsg .= empty($transactionRef) ? "" : __('Transaction ID: ', "hipayenterprise") . $transactionRef . "\n";
            $displayMsg .= empty($status) ? "" : __('HiPay status: ', "hipayenterprise") . $status . "\n";

            $orderHandler->addNote($displayMsg);
        }
    }

    /**
     * Handle maintenance request
     *
     * @param $mode
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function handleMaintenance($mode, $params = array())
    {
        try {
            $order = wc_get_order($params["order_id"]);
            if ($mode != Operation::CANCEL && in_array($order->get_status(), array('pending', 'failed', 'cancelled'), true)) {
                throw new Exception(
                    __(
                        "Maintenance operation is not allowed according to the order status.",
                        "hipayenterprise"
                    )
                );
            }

            switch ($mode) {
                case Operation::CAPTURE:
                    $params["operation"] = Operation::CAPTURE;
                    $this->api->requestMaintenance($params);
                    break;
                case Operation::REFUND:
                    $params["operation"] = Operation::REFUND;
                    $this->api->requestMaintenance($params);
                    break;
                case Operation::ACCEPT_CHALLENGE:
                    $params["operation"] = Operation::ACCEPT_CHALLENGE;
                    $this->api->requestMaintenance($params);
                    break;
                case Operation::DENY_CHALLENGE:
                    $params["operation"] = Operation::DENY_CHALLENGE;
                    $this->api->requestMaintenance($params);
                    break;
                case Operation::CANCEL:
                    $params["operation"] = Operation::CANCEL;
                    $this->handleCancel($params);
                    break;

                default:
                    $this->plugin->logs->logInfos("# Unknown maintenance operation");
            }
            return true;
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
            throw $e;
        }
    }

    /**
     * @param $params
     * @return string
     * @throws Hipay_Payment_Exception
     */
    private function handleHostedPayment($params)
    {
        $order = wc_get_order($params["order_id"]);

        $this->initParamsHostedPayment($params);

        try {
            $response = $this->api->requestHostedPaymentPage($order, $params);
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
            throw new Hipay_Payment_Exception(
                __('An error occured, process has been cancelled..', "hipayenterprise"),
                $order->get_cancel_order_url_raw(),
                "success"
            );
        }

        if ($this->plugin->confHelper->getPaymentGlobal()["display_hosted_page"] == "iframe") {
            $order->update_meta_data('_hipay_pay_url', esc_url_raw($response));
            $order->save();
            $redirect = $order->get_checkout_payment_url(true);
        } else {
            $redirect = esc_url_raw($response);
        }

        return $redirect;
    }

    /**
     * @param $params
     * @param bool $cc
     * @return string
     * @throws Hipay_Payment_Exception
     */
    private function handleDirectOrder($params, $cc = false)
    {
        $order = wc_get_order($params["order_id"]);
        $this->initParamsDirectPost($params);
        $params["paymentMethod"] = $this->getPaymentMethod($params, $cc);

        try {
            $response = $this->api->requestDirectPost($order, $params);
        } catch (Hipay_Payment_Exception $hpe) {
            throw $hpe;
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
            throw new Hipay_Payment_Exception(
                __('An error occured, process has been cancelled.', "hipayenterprise"),
                $order->get_cancel_order_url_raw(),
                "success"
            );
        }

        $forwardUrl = $response->getForwardUrl();

        switch ($response->getState()) {
            case TransactionState::COMPLETED:
            case TransactionState::PENDING:
                $redirectUrl = $order->get_checkout_order_received_url();
                break;
            case TransactionState::FORWARDING:
                $redirectUrl = $forwardUrl;
                break;
            case TransactionState::DECLINED:
                $reason = $response->getReason();
                $this->plugin->logs->logErrors('There was an error requesting new transaction: ' . $reason['message']);
                throw new Hipay_Payment_Exception(
                    __(
                        'Sorry, your payment has been declined. Please try again with an other means of payment.',
                        "hipayenterprise"
                    ),
                    '',
                    "fail"
                );
            case TransactionState::ERROR:
                $redirectUrl = $order->get_cancel_order_url_raw();
                $reason = $response->getReason();
                $this->plugin->logs->logErrors('There was an error requesting new transaction: ' . $reason['message']);
                throw new Hipay_Payment_Exception(
                    __('An error occured, process has been cancelled.', "hipayenterprise"),
                    $redirectUrl,
                    "success"
                );
            default:
                throw new Hipay_Payment_Exception(
                    __('An error occured, process has been cancelled.', "hipayenterprise")
                );
        }

        return $redirectUrl;
    }

    /**
     * Return mapped payment method
     *
     * @param $params
     * @param $cc
     * @return \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod|mixed|null
     */
    private function getPaymentMethod($params, $cc)
    {
        if ($cc) {
            $paymentMethod = new Hipay_Card_Token_Formatter($params);
        } else {
            $paymentMethod = new Hipay_Generic_Payment_Method_Formatter($params, $this->plugin);
        }

        return $paymentMethod->generate();
    }

    private function iniParamsWithConfiguration(&$params)
    {
        if ($this->plugin->confHelper->getPaymentGlobal()["activate_basket"]) {
            $params["basket"] = $this->cartFormatter->generate();
            if (count(WC()->cart->calculate_shipping()) > 0) {
                $params["delivery_information"] = $this->deliveryFormatter->generate();
            }
        }

        $params["authentication_indicator"] = $this->plugin->confHelper->getPaymentGlobal()["activate_3d_secure"];
    }

    private function initParamsDirectPost(&$params)
    {
        $this->iniParamsWithConfiguration($params);
    }

    private function initParamsHostedPayment(&$params)
    {
        $this->iniParamsWithConfiguration($params);
        $params["iframe"] = $this->plugin->confHelper->getPaymentGlobal()["display_hosted_page"] === "iframe";
    }
}
