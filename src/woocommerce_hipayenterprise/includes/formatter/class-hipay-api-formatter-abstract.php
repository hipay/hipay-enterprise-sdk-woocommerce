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

abstract class Hipay_Api_Formatter_Abstact
{
    protected $plugin;

    protected $order;

    public function __construct($plugin, $order)
    {
        $this->plugin = $plugin;
        $this->order = $order;
    }

    abstract public function generate();

    abstract protected function mapRequest(&$request);
}
