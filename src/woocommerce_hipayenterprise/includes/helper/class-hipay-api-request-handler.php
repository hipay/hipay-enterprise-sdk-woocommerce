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
     */
    public function initParamsDirectPost(&$params) {
        $params["deviceFingerprint"] = $_POST['ioBB'];
        $params["paymentProduct"] = !isset($params["paymentProduct"]) ? $_POST['payment-product'] : $params["paymentProduct"];
        $params["cardtoken"] = $_POST['card-token'];
        $params["method"] = $_POST['payment-product'];
        $params["authentication_indicator"] = $this->plugin->confHelper->getPaymentGlobal()["activate_3d_secure"];
    }

    /**
     * @param $params
     * @return string
     * @throws Hipay_Payment_Exception
     */
    public function handleCreditCard($params)
    {
        switch ($this->plugin->confHelper->getPaymentGlobal()["operating_mode"]) {
            case OperatingMode::DIRECT_POST:
                return $this->handleDirectOrder($params);
            case OperatingMode::HOSTED_PAGE:
                return $this->handleHostedPayment($params);
        }
    }

    /**
     * Return mapped payment method
     *
     * @param $params
     * @param bool $creditCard
     * @return \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod|mixed
     */
    private function getPaymentMethod($params, $creditCard = true)
    {
        $paymentMethod = new Hipay_Card_Token_Formatter($this->plugin, $params);
        return $paymentMethod->generate();
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

        $this->initParamsDirectPost($params);
        $params["paymentMethod"] = $this->getPaymentMethod($params);

        $response = $this->api->requestDirectPost($order, $params);

        $forwardUrl = $response->getForwardUrl();

        switch ($response->getState()) {
            case TransactionState::COMPLETED:
            case TransactionState::PENDING:
                $redirectUrl =  $order->get_checkout_order_received_url();
                break;
            case TransactionState::FORWARDING:
                $redirectUrl = $forwardUrl;
                break;
            case TransactionState::DECLINED:
                $reason = $response->getReason();
                $this->plugin->logs->logInfos('There was an error request new transaction: ' . $reason['message']);
                throw new Hipay_Payment_Exception(
                    __('Sorry, we cannot process your payment. Please try again.', "hipayenterprise")
                );
            case TransactionState::ERROR:
                $redirectUrl = $order->get_cancel_order_url_raw();
                $reason = $response->getReason();
                $this->plugin->logs->logInfos('There was an error request new transaction: ' . $reason['message']);
                throw new Hipay_Payment_Exception(
                    __('Sorry, an error occured during payment process.', "hipayenterprise"),
                    $redirectUrl
                );
            default:
                throw new Hipay_Payment_Exception(
                    __('Sorry, we cannot process your payment. Please try again.', "hipayenterprise")
                );
        }

        return $redirectUrl;
    }
}
