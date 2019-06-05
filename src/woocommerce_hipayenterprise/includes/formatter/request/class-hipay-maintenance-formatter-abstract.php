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
class Hipay_Maintenance_Formatter extends Hipay_Api_Formatter_Abstact
{

    protected $params;

    protected $operationsHelper;

    protected $cartMaintenanceFormatter;

    protected $transactionsHelper;

    /**
     * Hipay_Request_Formatter_Abstract constructor.
     * @param $plugin
     * @param $params
     * @param bool $order
     */
    public function __construct($plugin, $params, $order = false)
    {
        parent::__construct($plugin, $order);
        $this->params = $params;
        $this->operationsHelper = Hipay_Operations_Helper::initHiPayOperationsHelper($plugin);
        $this->cartMaintenanceFormatter = Hipay_Cart_Formatter::initHiPayCartFormatter();
        $this->transactionsHelper = Hipay_Transactions_Helper::initHiPayTransactionsHelper($plugin, $order);
    }

    /**
     * Generate request data before API call
     *
     * @return \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest|mixed
     */
    public function generate()
    {
        $maintenanceRequest = new \HiPay\Fullservice\Gateway\Request\Maintenance\MaintenanceRequest();

        $this->mapRequest($maintenanceRequest);

        return $maintenanceRequest;
    }


    /**
     * @param WC_Order_Refund $itemOperation
     * @return float
     */
    private function calculateTotalForAnItem($itemOperation)
    {
        $items = $itemOperation->get_items();

        $totalAmount = -1 * ($itemOperation->get_shipping_total() + $itemOperation->get_shipping_tax());
        foreach ($items as $key => $item) {
            $totalAmount -= (float)$item->get_total() + $item->get_total_tax();
        }

        return $totalAmount;
    }

    /**
     * Map maintenance Request
     *
     * @param $maintenanceRequest
     * @return mixed|void
     */
    public function mapRequest(&$maintenanceRequest)
    {
        parent::mapRequest($maintenanceRequest);
        $this->setCustomData($maintenanceRequest, $this->order, $this->params);

        $maintenanceRequest->amount = (isset($this->params["amount"])) ? $this->params["amount"] : 0.01;
        $maintenanceRequest->operation = (isset($this->params["operation"])) ? $this->params["operation"] : false;

        $transactionAttempt = $this->operationsHelper->getNbOperationAttempt(
            $maintenanceRequest->operation,
            $this->order->get_id()
        );

        $maintenanceRequest->operation_id = $this->generateOperationId(
            $this->order,
            $maintenanceRequest->operation,
            $transactionAttempt
        );

        if ($this->params["operation"] == \HiPay\Fullservice\Enum\Transaction\Operation::REFUND
            || $this->params["operation"] == \HiPay\Fullservice\Enum\Transaction\Operation::CAPTURE) {

            $authorizedBasket = $this->transactionsHelper->getOriginalBasket($this->order->get_id());
            $captureWithoutBasket = $this->transactionsHelper->existCaptureWithoutBasket($this->order->get_id());
            if ($authorizedBasket && !$captureWithoutBasket) {
                if ($this->params["operation"] == \HiPay\Fullservice\Enum\Transaction\Operation::REFUND) {
                    $itemOperation = reset($this->order->get_refunds());
                    $totalAmount = $this->calculateTotalForAnItem($itemOperation);
                } else if ($this->params["operation"] == \HiPay\Fullservice\Enum\Transaction\Operation::CAPTURE) {
                    $itemOperation = reset(Hipay_Order_Helper::get_captures($this->order));
                    $totalAmount = round(abs($this->calculateTotalForAnItem($itemOperation)), 2);
                }

                // Full Refund or Capture
                if ($this->order->get_total() == $this->params["amount"]) {
                    $maintenanceRequest->basket = json_encode($authorizedBasket);
                    // Partial Refund or Capture
                } else if (
                    ($this->params["amount"] - 0.01 <= $totalAmount) &&
                    ($totalAmount <= ($this->params["amount"] + 0.01))
                ) {
                    $maintenanceRequest->basket = $this->cartMaintenanceFormatter->generate(
                        $itemOperation->get_items(),
                        $this->params["operation"],
                        $itemOperation,
                        $authorizedBasket
                    );
                }
            }
        }
    }

    /**
     * Generate an operation id for HiPay compliance
     *
     * @param $order
     * @param $operation
     * @param $transactionAttempt
     * @return string
     */
    public function generateOperationId($order, $operation, $transactionAttempt)
    {
        return $order->id . '-' . $operation . '-' . ($transactionAttempt + 1);
    }
}
