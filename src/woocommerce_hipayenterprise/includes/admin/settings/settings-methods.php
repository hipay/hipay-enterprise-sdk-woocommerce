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
    'api_tab_methods_1' => array(
        'title' => "<p><hr>" . __('GLOBAL SETTINGS', 'hipayenterprise') . "</p>",
        'type' => 'title',
    ),
    'methods_global_settings' => array(
        'type' => 'methods_global_settings',
    ),
    'api_tab_methods_2' => array(
        'title' => "<p><hr>" . __('CREDIT CARD', 'hipayenterprise') . "</p>",
        'type' => 'title',
    ),
    'methods_credit_card_settings' => array(
        'type' => 'methods_credit_card_settings',
    ),
);

