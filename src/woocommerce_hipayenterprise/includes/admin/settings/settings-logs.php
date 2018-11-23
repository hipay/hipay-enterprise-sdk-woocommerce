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
    'api_tab_logs' => array(
        'title' => "<hr>" . __('LAST 100 LOGS', 'hipayenterprise'),
        'type' => 'title',
        'description' => ''
    ),
    'logs_details' => array(
        'type' => 'logs_details',
    ),
);
