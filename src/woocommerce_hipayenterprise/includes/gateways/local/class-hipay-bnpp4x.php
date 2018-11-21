<?php
if (!defined('ABSPATH')) {
    exit;
}

class Hipay_Bnpp4x extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_bnpp-4xcb';
        $this->paymentProduct = 'bnpp-4xcb';
        $this->method_title = __('Bnppf-4xcb', Hipay_Gateway_Abstract::TEXT_DOMAIN);
        $this->supports = array('products');
        $this->title = __('4x Carte Bancaire - BNP Personal Finance', Hipay_Gateway_Abstract::TEXT_DOMAIN);
        $this->method_description = __('4x Carte Bancaire - BNP Personal Finance', Hipay_Gateway_Abstract::TEXT_DOMAIN);

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
