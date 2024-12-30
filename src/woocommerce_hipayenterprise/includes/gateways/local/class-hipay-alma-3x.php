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
class Hipay_Alma_3x extends Hipay_Gateway_Alma_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_alma_3x';
        $this->paymentProduct = 'alma-3x';
        $this->method_title = __('HiPay Enterprise Alma 3x', "hipayenterprise");
        $this->title = __('Alma 3x', "hipayenterprise");
        $this->method_description = __('Alma 3x', "hipayenterprise");

        parent::__construct();
    }
}
