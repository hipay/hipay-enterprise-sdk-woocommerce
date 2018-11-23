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

class ThreeDS
{
    const THREE_D_S_DISABLED = 0;

    const THREE_D_S_TRY_ENABLE_ALL = 1;

    const THREE_D_S_TRY_ENABLE_RULES = 2;

    const THREE_D_S_FORCE_ENABLE_ALL = 3;

    const THREE_D_S_FORCE_ENABLE_RULES = 4;
}
