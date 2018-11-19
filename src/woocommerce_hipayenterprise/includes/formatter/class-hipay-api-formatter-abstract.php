<?php
/**
 * HiPay Enterprise SDK Prestashop
 *
 * 2017 HiPay
 *
 * NOTICE OF LICENSE
 *
 * @author    HiPay <support.tpp@hipay.com>
 * @copyright 2017 HiPay
 * @license   https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 */

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
