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
    public function initParamsDirectPost(&$params)
    {
        $params["deviceFingerprint"] = Hipay_Helper::getPostData('ioBB');
        $params["paymentProduct"] = !isset($params["paymentProduct"]) ? Hipay_Helper::getPostData('payment-product')
            : $params["paymentProduct"];
        $params["cardtoken"] = Hipay_Helper::getPostData('card-token');
        $params["card_holder"] = Hipay_Helper::getPostData('card-holder');
        $params["method"] = Hipay_Helper::getPostData('payment-product');
        $params["authentication_indicator"] = $this->plugin->confHelper->getPaymentGlobal()["activate_3d_secure"];
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
        if  ($this->plugin->confHelper->getPaymentGlobal()["operating_mode"] == OperatingMode::HOSTED_FIELDS) {
            return $this->handleDirectOrder($params);
        } else if  ($this->plugin->confHelper->getPaymentGlobal()["operating_mode"] == OperatingMode::HOSTED_PAGE) {
                return $this->handleHostedPayment($params);
        }
    }

    /**
     * Return mapped payment method
     *
     * @param $params
     * @return \HiPay\Fullservice\Gateway\Request\Order\OrderRequest
     */
    private function getPaymentMethod($params)
    {
        $paymentMethod = new Hipay_Card_Token_Formatter($params);
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
     * @return string
     * @throws Hipay_Payment_Exception
     */
    private function handleDirectOrder($params)
    {
        $order = wc_get_order($params["order_id"]);

        $this->initParamsDirectPost($params);
        $params["paymentMethod"] = $this->getPaymentMethod($params);

        try {
            $response = $this->api->requestDirectPost($order, $params);
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
}
