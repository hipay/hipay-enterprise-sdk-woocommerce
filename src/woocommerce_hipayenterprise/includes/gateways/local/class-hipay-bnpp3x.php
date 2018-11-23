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

class Hipay_Bnpp3x extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_bnpp-3xcb';
        $this->paymentProduct = 'bnpp-3xcb';
        $this->method_title = __('Bnppf-3xcb', Hipay_Gateway_Abstract::TEXT_DOMAIN);
        $this->supports = array('products');
        $this->title = __('3x Carte Bancaire - BNP Personal Finance', Hipay_Gateway_Abstract::TEXT_DOMAIN);
        $this->method_description = __('3x Carte Bancaire - BNP Personal Finance', Hipay_Gateway_Abstract::TEXT_DOMAIN);

        parent::__construct();

        $this->init_form_fields();

        $this->init_settings();
    }

    public function payment_fields()
    {
        _e(
            'You will be redirected to an external payment page. Please do not refresh the page during the process.',
            Hipay_Gateway_Abstract::TEXT_DOMAIN
        );
    }
}
