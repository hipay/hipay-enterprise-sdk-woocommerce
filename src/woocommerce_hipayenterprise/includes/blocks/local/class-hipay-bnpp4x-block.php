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
 * HiPay Bnpp4x payment method blocks integration
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link        https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
final class Hipay_Bnpp4x_Block extends Hipay_Local_Payment_Block_Abstract
{
    /**
     * Payment method name/id/slug
     *
     * @var string
     */
    protected $name = 'hipayenterprise_bnpp_4x';

    /**
     * Payment product identifier
     *
     * @var string
     */
    protected $paymentProduct = 'bnpp-4x';
    
    /**
     * Override to add SDK widget flag for BNPP methods
     */
    protected function get_payment_config()
    {
        $config = parent::get_payment_config();
        
        // BNPP 4x needs SDK widget to render the payment selector
        $config['needsSDKWidget'] = true;
        
        return $config;
    }
}
