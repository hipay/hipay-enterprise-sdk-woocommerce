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
class Hipay_Previous_Auth_Info_Formatter extends Hipay_Api_Formatter_Abstact
{

    /**
     * @var WC_Order
     */
    protected $order;

    /**
     * @var array
     */
    protected $params;


    /**
     * Hipay_Browser_Info_Formatter constructor.
     *
     * @param $order
     * @param $params
     */
    public function __construct($order, $params)
    {
        $this->order = $order;
        $this->params = $params;
    }

    /**
     *
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo
     *
     * @throws Hipay_Payment_Exception
     */
    public function generate()
    {
        $previousAuthInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\PreviousAuthInfo();

        $this->mapRequest($previousAuthInfo);

        return $previousAuthInfo;
    }


    /**
     * Map Previous Auth info
     *
     * @param PreviousAuthInfo $previousAuthInfo
     *
     */
    public function mapRequest(&$previousAuthInfo)
    {
        if (is_user_logged_in()) {
            $lastOrder = Hipay_Threeds_Helper::getLastOrder(get_current_user_id(), $this->order->get_id());
            if (!empty($lastOrder)) {
                $transactionID = $lastOrder[0]->get_transaction_id();
                if (empty($transactionID)) {
                    $transactionID = Hipay_Transactions_Helper::getTransactionReference($lastOrder[0]->get_id());
                }
                $previousAuthInfo->transaction_reference = $transactionID;
            }
        }
    }
}
