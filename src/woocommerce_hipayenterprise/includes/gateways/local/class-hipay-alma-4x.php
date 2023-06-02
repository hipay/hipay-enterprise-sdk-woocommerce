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
class Hipay_Alma_4x extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_alma_4x';
        $this->paymentProduct = 'alma-4x';
        $this->method_title = __('HiPay Enterprise Alma 4x', "hipayenterprise");
        $this->title = __('Alma 4x', "hipayenterprise");
        $this->method_description = __('Alma 4x', "hipayenterprise");

        parent::__construct();
    }
}
