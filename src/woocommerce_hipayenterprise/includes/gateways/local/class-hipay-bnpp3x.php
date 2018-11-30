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
class Hipay_Bnpp3x extends Hipay_Gateway_Local_Abstract
{

    /**
     * Hipay_Bnpp3x constructor.
     */
    public function __construct()
    {
        $this->id = 'hipayenterprise_bnpp-3xcb';
        $this->paymentProduct = 'bnpp-3xcb';
        $this->method_title = __('HiPay Enterprise Bnppf-3xcb', "hipayenterprise");
        $this->method_description = __('3x Carte Bancaire - BNP Personal Finance', "hipayenterprise");
        parent::__construct();
    }

}
