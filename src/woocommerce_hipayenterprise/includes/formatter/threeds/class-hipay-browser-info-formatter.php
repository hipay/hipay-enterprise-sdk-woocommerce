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
class Hipay_Browser_Info_Formatter extends Hipay_Api_Formatter_Abstact
{

    /**
     * @var WC_Order
     */
    protected $order;

    /**
     * @var array
     */
    protected $params;


    /**
     * Hipay_Browser_Info_Formatter constructor.
     *
     * @param $order
     * @param $params
     */
    public function __construct($order, $params)
    {
        $this->order = $order;
        $this->params = $params;
    }

    /**
     *
     * @return \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo
     *
     * @throws Hipay_Payment_Exception
     */
    public function generate()
    {
        $browserInfo = new \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo();

        $this->mapRequest($browserInfo);

        return $browserInfo;
    }


    /**
     * Map browser info
     *
     * @param $browserInfo \HiPay\Fullservice\Gateway\Model\Request\ThreeDSTwo\BrowserInfo
     * @throws Hipay_Payment_Exception
     */
    public function mapRequest(&$browserInfo)
    {
        $browserInfo->ipaddr = $this->order->get_customer_ip_address();
        $browserInfo->http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;

        $browserInfo->javascript_enabled = isset($this->params['browser_info']) && ($this->params['browser_info'] !== false);

        if (isset($this->params['browser_info'])) {
            $browserInfo->java_enabled = isset($this->params['browser_info']->java_enabled) ? $this->params['browser_info']->java_enabled : null;
            $browserInfo->language = isset($this->params['browser_info']->language) ? $this->params['browser_info']->language : null;
            $browserInfo->color_depth = isset($this->params['browser_info']->color_depth) ? $this->params['browser_info']->color_depth : null;
            $browserInfo->screen_height = isset($this->params['browser_info']->screen_height) ? $this->params['browser_info']->screen_height : null;
            $browserInfo->screen_width = isset($this->params['browser_info']->screen_width) ? $this->params['browser_info']->screen_width : null;
            $browserInfo->timezone = isset($this->params['browser_info']->timezone) ? $this->params['browser_info']->timezone : null;
            $browserInfo->http_user_agent = isset($this->params['browser_info']->http_user_agent) ? $this->params['browser_info']->http_user_agent : null;
        }
    }
}
