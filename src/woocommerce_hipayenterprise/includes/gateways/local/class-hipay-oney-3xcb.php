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
class Hipay_Oney_3xcb extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_3xcb';
        $this->paymentProduct = '3xcb';
        $this->method_title = __('HiPay Enterprise 3x Carte Bancaire avec frais - Oney', "hipayenterprise");
        $this->title = __('3x Carte Bancaire - Oney', "hipayenterprise");
        $this->method_description = __('3x Carte Bancaire - Oney', "hipayenterprise");

        parent::__construct();
    }
}
