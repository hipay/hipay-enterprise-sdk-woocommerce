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
class Hipay_Astropay_Bbva_Bancomer extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_bbva_bancomer';
        $this->paymentProduct = 'bbva-bancomer';
        $this->method_title = __('HiPay Enterprise BBVA Bancomer', "hipayenterprise");
        $this->title = __('BBVA Bancomer', "hipayenterprise");
        $this->method_description = __('BBVA Bancomer - Astropay', "hipayenterprise");

        parent::__construct();
    }
}
