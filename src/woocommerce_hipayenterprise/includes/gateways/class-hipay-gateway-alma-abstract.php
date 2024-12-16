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
    // Exit if accessed directly
}

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Gateway_Alma_Abstract extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        parent::__construct();
    }

    public function is_available()
    {
        if ($this->enabled === 'no') {
            return false;
        }

        $total = WC()->cart ? WC()->cart->total : 0;

        return $this->getMinMaxByPaymentProduct($total, $this->paymentProduct);
    }
}
