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
class Hipay_Multibanco extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_multibanco';
        $this->paymentProduct = 'multibanco';
        $this->method_title = __('HiPay Enterprise Multibanco', "hipayenterprise");
        $this->title = __('Multibanco', "hipayenterprise");
        $this->method_description = __('Multibanco', "hipayenterprise");

        $this->icon = WC_HIPAYENTERPRISE_URL_ASSETS . '/images/multibanco.png';

        parent::__construct();
    }
}
