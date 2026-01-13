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
    protected $order;

    /**
     * Return  mapped delivery shipping information
     *
     * @param WC_Order|null $order Optional order object for blocks checkout
     * @return \HiPay\Fullservice\Gateway\Request\Info\DeliveryShippingInfoRequest|mixed
     * @throws Exception
     */
    public function generate($order = null)
    {
        $this->order = $order;

        // Try to get shipping method from order first (for blocks checkout)
        if ($order && $order->get_shipping_methods()) {
            $shippingMethods = $order->get_shipping_methods();
            $firstMethod = reset($shippingMethods);
            if ($firstMethod) {
                $methodId = $firstMethod->get_method_id();
            } else {
                $methodId = null;
            }
        } elseif (WC()->cart && count(WC()->cart->calculate_shipping()) > 0) {
            // Fall back to cart for classic checkout
            $methodId = WC()->cart->calculate_shipping()[0]->method_id;
        } else {
            // No shipping method available
            $methodId = null;
        }

        if ($methodId) {
            $this->mappedShipping = Hipay_Helper_Mapping::getHipayMappingFromDeliveryMethod($methodId);
        } else {
            // Default to "no mapping" but provide a default delivery date for Oney/Alma
            $this->mappedShipping = 1;
        }

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
        $today = new \Datetime();

        if ($this->mappedShipping != 1) {
            $daysDelay = (int)$this->mappedShipping[Hipay_Mapping_Delivery_Controller::ORDER_PREPARATION][0] +
                (int)$this->mappedShipping[Hipay_Mapping_Delivery_Controller::DELIVERY_ESTIMATED][0];
            $interval = new \DateInterval("P{$daysDelay}D");

            return $today->add($interval)->format("Y-m-d");
        }

        // Default to 7 days if no mapping is available (required for Oney payments)
        $interval = new \DateInterval("P7D");
        return $today->add($interval)->format("Y-m-d");
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

        // Default delivery method for Oney/Alma when no shipping mapping exists
        // Must be JSON with mode and shipping as per HiPay SDK DeliveryShippingInfoRequest
        return json_encode(array('mode' => 'CARRIER', 'shipping' => 'STANDARD'));
    }

    public static function initHiPayDeliveryFormatter()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
