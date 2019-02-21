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
class Hipay_Oney_4xcb_No_Fees extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_4xcb_no_fees';
        $this->paymentProduct = '4xcb-no-fees';
        $this->method_title = __('HiPay Enterprise 4x Carte Bancaire sans frais - Oney', "hipayenterprise");
        $this->title = __('4x Carte Bancaire sans frais - Oney', "hipayenterprise");
        $this->method_description = __('4x Carte Bancaire sans frais - Oney', "hipayenterprise");

        parent::__construct();
    }
}
