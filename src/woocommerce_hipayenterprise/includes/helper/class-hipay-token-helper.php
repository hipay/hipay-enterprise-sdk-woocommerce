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
class Hipay_Token_Helper
{

    public static $tokenKeys = array(
        "token",
        "pan",
        "expiry_year",
        "expiry_month",
        "brand",
        "card_holder",
        "user_id",
        "gateway_id"
    );

    /**
     * @param HiPay\Fullservice\Gateway\Model\Transaction $transaction
     * @param WC_Order $order
     * @throws Exception
     */
    public static function createTokenFromTransaction($transaction, $order)
    {
        $values = array();
        $values["token"] = $transaction->getPaymentMethod()->getToken();
        $values["pan"] = $transaction->getPaymentMethod()->getPan();
        $values["expiry_year"] = $transaction->getPaymentMethod()->getCardExpiryYear();
        $values["expiry_month"] = $transaction->getPaymentMethod()->getCardExpiryMonth();
        $values["brand"] = $transaction->getPaymentMethod()->getBrand();
        $values["card_holder"] = $transaction->getPaymentMethod()->getCardHolder();
        $values["payment_product"] = $transaction->getPaymentProduct();
        $values["user_id"] = $order->get_user_id();
        $values["gateway_id"] = $order->get_payment_method();

        self::createToken($values);
    }

    /**
     * @param $values
     * @throws Exception
     */
    public static function createToken($values)
    {
        if (in_array(array_keys($values), self::$tokenKeys)) {
            throw new Exception("Invalid create token values");
        }

        if (!self::cardExists($values["pan"], $values["brand"], $values["user_id"])) {
            $token = new WC_Payment_Token_CC_HiPay();
            $token->set_token($values["token"]);
            $token->set_pan($values["pan"]);
            $token->set_expiry_year($values["expiry_year"]);
            $token->set_expiry_month($values["expiry_month"]);
            $token->set_card_type($values["brand"]);
            $token->set_card_holder($values["card_holder"]);
            $token->set_user_id($values["user_id"]);
            $token->set_gateway_id($values["gateway_id"]);
            $token->set_payment_product($values["payment_product"]);

            $token->save();
        }
    }

    /**
     * @param $tokenId
     * @param $params
     * @throws Hipay_Payment_Exception
     */
    public static function handleTokenForm($tokenId, &$params)
    {
        if ($tokenId !== "new") {
            $token = WC_Payment_Tokens::get($tokenId);

            if ($token !== null && $token->get_user_id() === get_current_user_id()) {
                $params["cardtoken"] = $token->get_token();
                $params["paymentProduct"] = $token->get_payment_product();
                $params["card_holder"] = $token->get_card_holder();
                $params["oneClick"] = true;
            } else {
                throw new Hipay_Payment_Exception(__("Invalid Card token"));
            }
        }
    }

    /**
     * @param $pan
     * @param $brand
     * @param $customerId
     * @return bool
     */
    private static function cardExists($pan, $brand, $customerId)
    {
        $tokens = WC_Payment_Tokens::get_customer_tokens($customerId);

        foreach ($tokens as $token) {
            if ($token->get_pan() == $pan && strtolower($token->get_card_type()) == strtolower($brand)) {
                return true;
            }
        }

        return false;
    }
}
