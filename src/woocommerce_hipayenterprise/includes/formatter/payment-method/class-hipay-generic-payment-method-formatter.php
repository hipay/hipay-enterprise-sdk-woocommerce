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
class Hipay_Generic_Payment_Method_Formatter implements Hipay_Api_Formatter
{

    private $params;

    private $plugin;

    public function __construct($params, $plugin)
    {
        $this->params = $params;
        $this->plugin = $plugin;
    }

    public function generate()
    {
        $PMRequest = null;

        $method = $this->plugin->confHelper->getLocalPayment($this->params["paymentProduct"]);

        if (!empty($method["additionalFields"])) {
            $sdkClass = $method["additionalFields"]["sdkClass"];
            $PMRequest = new $sdkClass();

            $this->mapRequest($PMRequest);
        }
        return $PMRequest;
    }

    /**
     * hydrate object define in json config
     * @param mixed
     */
    public function mapRequest(&$PMRequest)
    {
        // we get all attributes
        $attributes = get_object_vars($PMRequest);

        $method = $this->plugin->confHelper->getLocalPayment($this->params["paymentProduct"]);

        foreach ($attributes as $attr => $value) {
            if (isset($method["additionalFields"]["defaultFieldsValue"][$attr])) {
                $PMRequest->{$attr} = $method["additionalFields"]["additionalFields"]["defaultFieldsValue"][$attr];
            } elseif (isset($this->params[$attr])) {
                $PMRequest->{$attr} = $this->params[$attr];
            }
        }
    }
}
