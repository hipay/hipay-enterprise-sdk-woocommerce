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
class Hipay_Bnpp4x extends Hipay_Gateway_Local_Abstract
{

    /**
     * Hipay_Bnpp4x constructor.
     */
    public function __construct()
    {
        $this->id = 'hipayenterprise_bnpp-4xcb';
        $this->paymentProduct = 'bnpp-4xcb';
        $this->method_title = __('Bnppf-4xcb', "hipayenterprise");
        $this->supports = array('products');
        $this->title = __('4x Carte Bancaire - BNP Personal Finance', "hipayenterprise");
        $this->method_description = __('4x Carte Bancaire - BNP Personal Finance', "hipayenterprise");

        parent::__construct();

        $this->init_form_fields();

        $this->init_settings();
    }

    /**
     *
     */
    public function payment_fields()
    {
        _e(
            'You will be redirected to an external payment page. Please do not refresh the page during the process.',
            "hipayenterprise"
        );
    }
}
