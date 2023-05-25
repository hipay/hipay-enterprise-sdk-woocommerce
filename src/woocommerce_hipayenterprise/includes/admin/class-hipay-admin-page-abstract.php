<?php

/**
 * HiPay Enterprise SDK WooCommerce
 *
 * 2023 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2023 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 */

defined('ABSPATH') || exit;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2023 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
abstract class Hipay_Admin_Page
{
	/**
	 * Hipay_Admin_Page constructor.
	 */
	public function __construct()
	{
		Wc_Hipay_Admin_Assets::get_instance();
	}
}