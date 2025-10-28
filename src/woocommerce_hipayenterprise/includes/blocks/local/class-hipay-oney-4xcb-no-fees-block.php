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
 * HiPay Oney_4xcb_No_Fees payment method blocks integration
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link        https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
final class Hipay_Oney_4xcb_No_Fees_Block extends Hipay_Local_Payment_Block_Abstract
{
    /**
     * Payment method name/id/slug
     *
     * @var string
     */
    protected $name = 'hipayenterprise_4xcb_no_fees';

    /**
     * Payment product identifier
     *
     * @var string
     */
    protected $paymentProduct = '4xcb-no-fees';
}
