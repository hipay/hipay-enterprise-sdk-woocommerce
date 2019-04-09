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

/**
 *
 * Delivery shipping information request formatter
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2017 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-prestashop/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-prestashop
 */
class Hipay_Delivery_Formatter implements Hipay_Api_Formatter
{
    /**
     * The single instance of the class.
     */
    protected static $instance = null;

    protected $mappedShipping;

    /**
     * Return  mapped delivery shipping information
     *
     * @return \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest|mixed
     * @throws Exception
     */
    public function generate()
    {
        $this->mappedShipping = Hipay_Helper_Mapping::getHipayMappingFromDeliveryMethod(
            WC()->cart->calculate_shipping()[0]->method_id
        );

        $deliveryShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest();

        $this->mapRequest($deliveryShippingInfo);

        return $deliveryShippingInfo;
    }

    /**
     * Map  delivery shipping information to request fields (Hpayment Post)
     *
     * @param \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest $deliveryShippingInfo
     * @return mixed|void
     * @throws Exception
     */
    public function mapRequest(&$deliveryShippingInfo)
    {
        $deliveryShippingInfo->delivery_date = $this->calculateEstimatedDate();
        $deliveryShippingInfo->delivery_method = $this->getMappingShippingMethod();
    }

    /**
     * According to the mapping, provide a approximated date delivery
     *
     * @return null|string format YYYY-MM-DD
     * @throws Exception
     */
    private function calculateEstimatedDate()
    {
        if ($this->mappedShipping != 1) {
            $today = new \Datetime();
            $daysDelay = $this->mappedShipping[Hipay_Mapping_Delivery_Controller::ORDER_PREPARATION][0] +
                $this->mappedShipping[Hipay_Mapping_Delivery_Controller::DELIVERY_ESTIMATED][0];
            $interval = new \DateInterval("P{$daysDelay}D");

            return $today->add($interval)->format("Y-m-d");
        }
        return null;
    }

    /**
     * Provide a delivery Method compatible with gateway
     *
     * @return null|string
     */
    private function getMappingShippingMethod()
    {
        if ($this->mappedShipping != 1) {
            return json_encode(
                array(
                    'mode' => $this->mappedShipping[Hipay_Mapping_Delivery_Controller::MODE][0],
                    'shipping' => $this->mappedShipping[Hipay_Mapping_Delivery_Controller::SHIPPING][0]
                )
            );
        }
        return null;
    }

    public static function initHiPayDeliveryFormatter()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
