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
 * HiPay Bizum payment method blocks integration
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link        https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
final class Hipay_Bizum_Block extends Hipay_Local_Payment_Block_Abstract
{
    protected $name = 'hipayenterprise_bizum';

    protected $paymentProduct = 'bizum';

    protected function get_payment_config()
    {
        $config = parent::get_payment_config();

        if (!empty($config['additionalFields']['formFields'])) {
            $lang = substr(apply_filters('hipay_locale', get_locale()), 0, 2);
            foreach ($config['additionalFields']['formFields'] as $fieldName => &$field) {
                if (is_array($field['label'])) {
                    $field['label'] = $field['label'][$lang]
                        ?? $field['label']['en']
                        ?? reset($field['label']);
                }
            }
            unset($field);
        }

        $config['additionalFields']['helpText'] = __('The payment will need to be validated on your Bizum banking application.', 'hipayenterprise');

        return $config;
    }
}
