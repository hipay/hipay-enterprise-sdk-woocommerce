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
    'api_tab_module_configuration' => array(
        'title' => "<hr><i class='dashicons dashicons-admin-tools'></i> " . __('PLUGIN CONFIGURATION', 'hipayenterprise'),
        'type' => 'title',
    ),
    'account_details' => array(
        'type' => 'account_details',
    ),
);

