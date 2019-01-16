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
class Hipay_Paypal extends Hipay_Gateway_Local_Abstract
{

    /**
     * Hipay_Giropay constructor.
     */
    public function __construct()
    {
        $this->id = 'hipayenterprise_paypal';
        $this->paymentProduct = 'paypal';
        $this->method_title = __('HiPay Enterprise Paypal', "hipayenterprise");
        $this->title = __('Paypal', "hipayenterprise");
        $this->method_description = __('Paypal', "hipayenterprise");

        parent::__construct();
    }
}
