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
     * @throws Exception
     */
    public function handleCreditCard($params)
    {
        return $this->handleHostedPayment($params);
    }

    /**
     * @param $params
     * @return string
     * @throws Exception
     */
    public function handleLocalPayment($params)
    {
        return $this->handleDirectOrder($params);
    }

    /**
     * @param $params
     * @return string
     * @throws Exception
     */
    private function handleHostedPayment($params)
    {
        $order = wc_get_order($params["order_id"]);

        $response = $this->api->requestHostedPaymentPage($order);

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
     * @throws Exception
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
                $reason = $response->getReason();
                $this->logs->logInfos('There was an error request new transaction: ' . $reason['message']);
                throw new Exception(
                    __('Sorry, we cannot process your payment.. Please try again.', "hipayenterprise")
                );
            case TransactionState::ERROR:
                $reason = $response->getReason();
                $this->logs->logInfos('There was an error request new transaction: ' . $reason['message']);
                throw new Exception(
                    __('Sorry, we cannot process your payment.. Please try again.', "hipayenterprise")
                );
            default:
                throw new Exception(
                    __('Sorry, we cannot process your payment.. Please try again.', "hipayenterprise")
                );
        }

        return $redirectUrl;
    }
}
