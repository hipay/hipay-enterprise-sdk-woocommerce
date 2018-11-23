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

return array(
    'api_tab_fraud' => array(
        'title' => "<hr>" . __('PAYMENT FRAUD EMAIL', 'hipayenterprise'),
        'type' => 'title',
        'description' => '<div id="message" class="updated woocommerce-message inline">' .
            __(
                'When a transaction is likely to be a fraud then an email is sent to the contact email ' .
                'from your shop as well as to an additional sender. Here you can configure ' .
                'the additional recipient email'
            ) .
            "</div>"
    ),
    'fraud_details' => array(
        'type' => 'fraud_details',
    ),
);
