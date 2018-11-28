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
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
abstract class Hipay_Api_Formatter_Abstact
{
    /**
     * @var
     */
    protected $plugin;

    /**
     * @var
     */
    protected $order;

    /**
     * Hipay_Api_Formatter_Abstact constructor.
     * @param $plugin
     * @param $order
     */
    public function __construct($plugin, $order)
    {
        $this->plugin = $plugin;
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    abstract public function generate();

    /**
     * @param $request
     * @return mixed
     */
    abstract protected function mapRequest(&$request);
}
