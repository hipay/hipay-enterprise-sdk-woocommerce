<?php

if (!defined('ABSPATH')) {
    exit;
}

use HiPay\Fullservice\Enum\Transaction\TransactionState;

class Hipay_Api_Request_Handler
{

    private $api;

    private $plugin;

    /**
     * Hipay_Api_Request_Handler constructor.
     * @param $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->api = new Hipay_Api($plugin);
    }

    /**
     * @return Hipay_Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param $params
     * @return string
     * @throws Hipay_Payment_Exception
     */
    public function handleCreditCard($params)
    {
        return $this->handleHostedPayment($params);
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
     * @return string
     * @throws Hipay_Payment_Exception
     */
    private function handleHostedPayment($params)
    {
        $order = wc_get_order($params["order_id"]);

        try {
            $response = $this->api->requestHostedPaymentPage($order);
        } catch (Exception $e) {
            throw new Hipay_Payment_Exception(
                __('Sorry, we cannot process your payment.. Please try again.', "hipayenterprise")
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
     * @return string
     * @throws Hipay_Payment_Exception
     */
    private function handleDirectOrder($params)
    {
        $order = wc_get_order($params["order_id"]);

        $response = $this->api->requestDirectPost($order, $params);
        $forwardUrl = $response->getForwardUrl();
        $redirectUrl = '';

        switch ($response->getState()) {
            case TransactionState::COMPLETED:
            case TransactionState::PENDING:
                break;
            case TransactionState::FORWARDING:
                $redirectUrl = $forwardUrl;
                break;
            case TransactionState::DECLINED:
                $redirectUrl = $order->get_cancel_order_url_raw();
                $reason = $response->getReason();
                $this->plugin->logs->logInfos('There was an error request new transaction: ' . $reason['message']);
                throw new Hipay_Payment_Exception(
                    __('Sorry, we cannot process your payment.. Please try again.', "hipayenterprise"),
                    $redirectUrl
                );
            case TransactionState::ERROR:
                $redirectUrl = $order->get_cancel_order_url_raw();
                $reason = $response->getReason();
                $this->plugin->logs->logInfos('There was an error request new transaction: ' . $reason['message']);
                throw new Hipay_Payment_Exception(
                    __('Sorry, we cannot process your payment.. Please try again.', "hipayenterprise"),
                    $redirectUrl
                );
            default:
                throw new Hipay_Payment_Exception(
                    __('Sorry, we cannot process your payment.. Please try again.', "hipayenterprise")
                );
        }

        return $redirectUrl;
    }
}
