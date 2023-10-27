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
class Hipay_Multibanco extends Hipay_Gateway_Local_Abstract
{

    /**
     *
     * @var string HIPAY_MULTIBANCO_ENTITY order item meta multibanco entity
     */
    const HIPAY_MULTIBANCO_ENTITY = 'hipay_multibanco_entity';

    /**
     *
     * @var string HIPAY_MULTIBANCO_REFERENCE order item meta multibanco reference
     */
    const HIPAY_MULTIBANCO_REFERENCE = 'hipay_multibanco_reference';

    /**
     *
     * @var string HIPAY_MULTIBANCO_AMOUNT order item meta multibanco amount
     */
    const HIPAY_MULTIBANCO_AMOUNT = 'hipay_multibanco_amount';

    /**
     *
     * @var string HIPAY_MULTIBANCO_EXPIRATION_DATE order item meta multibanco expiration date
     */
    const HIPAY_MULTIBANCO_EXPIRATION_DATE = 'hipay_multibanco_expirationDate';

    private $entity;
    private $reference;
    private $amount;
    private $expirationDate;

    public function __construct()
    {

        $this->id = 'hipayenterprise_multibanco';
        $this->paymentProduct = 'multibanco';
        $this->method_title = __('HiPay Enterprise Multibanco', "hipayenterprise");
        $this->title = __('Multibanco', "hipayenterprise");
        $this->method_description = __('Multibanco', "hipayenterprise");

        parent::__construct();

        add_action("woocommerce_thankyou_$this->id", array($this, 'thanks_page'));
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        add_action('woocommerce_view_order', array($this, 'thanks_page'));
    }

    /**
     * multibanco details for email template
     *
     * @param WC_Order  $order_
     * @param bool      $sent_to_admin
     * @param bool      $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($order->get_payment_method() === $this->id) {
            $this->makeMultibancoTemplate($order);
        }
    }

    /**
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        try {
            $order = new WC_Order($order_id);

            $this->logs->logInfos("# Process Payment for $order_id");

            $method = $this->confHelper->getLocalPayment($this->paymentProduct);

            $params = array(
                "order_id" => $order_id,
                "paymentProduct" => $this->paymentProduct,
                "forceSalesMode" => true,
                "deviceFingerprint" => Hipay_Helper::getPostData($this->paymentProduct . '-device_fingerprint')
            );

            $response = $this->apiRequestHandler->handleLocalPayment($params, true);
            $referenceToPay = json_decode($response["additional_data"]->getReferenceToPay(), true);

            $order->update_meta_data(self::HIPAY_MULTIBANCO_ENTITY, $referenceToPay["entity"]);
            $order->update_meta_data(self::HIPAY_MULTIBANCO_REFERENCE, $referenceToPay["reference"]);
            $order->update_meta_data(self::HIPAY_MULTIBANCO_AMOUNT, $referenceToPay["amount"]);
            $order->update_meta_data(self::HIPAY_MULTIBANCO_EXPIRATION_DATE, $referenceToPay["expirationDate"]);
            $order->save();
            $orderNote = __('Entity:', "hipayenterprise") . " " . $referenceToPay["entity"] . " " . __('Reference:', "hipayenterprise") . " " . $referenceToPay["reference"] . " " . __('Amount:', "hipayenterprise") . " " . $referenceToPay["amount"] . " " . __('Expiration Date:', "hipayenterprise") . " " . $referenceToPay["expirationDate"] . " ";
            $order->add_order_note($orderNote);

            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_order_received_url()
            );
        } catch (Hipay_Payment_Exception $e) {
            return $this->handlePaymentError($e);
        }
    }

    /**
     * Multibanco details display template
     *
     * @param int $order_id
     */
    public function thanks_page($order_id)
    {
        global $woocommerce;

        $order = new WC_Order($order_id);

        if ($order->get_payment_method() === $this->id) {
            $this->makeMultibancoTemplate($order);

            $woocommerce->cart->empty_cart();
        }
    }

    /**
     * Make Multibanco template for order details
     * @param WC_Order $order
     */
    private function makeMultibancoTemplate($order)
    {
        $this->entity = $order->get_meta(self::HIPAY_MULTIBANCO_ENTITY);
        $this->reference = $order->get_meta(self::HIPAY_MULTIBANCO_REFERENCE);
        $this->amount = $order->get_meta(self::HIPAY_MULTIBANCO_AMOUNT);
        $this->expirationDate = $order->get_meta(self::HIPAY_MULTIBANCO_EXPIRATION_DATE);

        $this->process_template(
            'multibanco.php',
            'frontend',
            array(
                    'entity' => $this->entity,
                    'reference' => $this->reference,
                    'amount' => $this->amount,
                    'expirationDate' => $this->expirationDate,
                    'logo' => $this->getMultibancoIconUrl(),
                )
        );
    }

    /**
     * Return Multibanco logo URL
     * @return url
     */
    private function getMultibancoIconUrl()
    {
        return WC_HIPAYENTERPRISE_URL_ASSETS . 'local_payments_images/multibanco.png';
    }
}
