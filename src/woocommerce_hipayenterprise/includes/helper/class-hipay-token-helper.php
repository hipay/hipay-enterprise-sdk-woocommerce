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
        "gateway_id",
        "force_cvv"
    );

    /**
     * @param $values
     * @throws Exception
     */
    public static function createToken($values)
    {
        if (!Hipay_Helper::allArrayKeyExists(self::$tokenKeys, $values)) {
            throw new Exception("Invalid create token values");
        }

        $dateCreated = new \DateTime('now');
        $dateCreated = $dateCreated->format('Ymd');

        $token = self::cardExists($values["pan"], $values["brand"], $values["user_id"]);
        $token->set_token($values["token"]);
        $token->set_pan($values["pan"]);
        $token->set_expiry_year($values["expiry_year"]);
        $token->set_expiry_month($values["expiry_month"]);
        $token->set_card_type($values["brand"]);
        $token->set_card_holder($values["card_holder"]);
        $token->set_user_id($values["user_id"]);
        $token->set_gateway_id($values["gateway_id"]);
        $token->set_payment_product($values["payment_product"]);
        $token->set_force_cvv($values["force_cvv"]);
        $token->set_date_created($dateCreated);

        $token->save();
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
                $params["force_cvv"] = $token->get_force_cvv();
            } else {
                throw new Hipay_Payment_Exception(__("Invalid Card token", 'hipayenterprise'));
            }
        }
    }

    /**
     * @param $pan
     * @param $brand
     * @param $customerId
     * @return WC_Payment_Token_CC_HiPay
     */
    private static function cardExists($pan, $brand, $customerId)
    {
        $tokens = WC_Payment_Tokens::get_customer_tokens($customerId);

        foreach ($tokens as $token) {
            if ($token->get_pan() == $pan && strtolower($token->get_card_type()) == strtolower($brand)) {
                return $token;
            }
        }

        return new WC_Payment_Token_CC_HiPay();
    }


    /**
     *  Get Customer token by token value
     *
     * @param $customerId
     * @param $userToken
     * @return WC_Payment_Token
     * @throws Exception
     */
    public static function getToken($customerId, $userToken)
    {
        $tokens = WC_Payment_Tokens::get_customer_tokens($customerId);

        foreach ($tokens as $token) {
            if ($token->get_token() === $userToken) {
                return $token;
            }
        }

        throw new Exception("Invalid token values");
    }
}
