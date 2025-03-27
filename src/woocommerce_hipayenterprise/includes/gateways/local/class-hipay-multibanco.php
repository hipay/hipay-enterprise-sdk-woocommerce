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
            $this->makeMultibancoTemplateEmail($order);
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

            $params = array(
                "order_id" => $order_id,
                "paymentProduct" => $this->paymentProduct,
                "forceSalesMode" => true,
                "deviceFingerprint" => Hipay_Helper::getPostData($this->paymentProduct . '-device_fingerprint')
            );

            $response = $this->apiRequestHandler->handleLocalPayment($params, true);
            $referenceToPay = $response["additional_data"]->getReferenceToPay();
            if (is_string($referenceToPay)) {
                $referenceToPay = json_decode($referenceToPay, true);
            }

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
     * Get Multibanco payment data from order
     *
     * @param WC_Order $order
     * @return array|false Payment data or false if missing required data
     */
    private function getMultibancoData($order) {
        $entity = $order->get_meta(self::HIPAY_MULTIBANCO_ENTITY);
        $reference = $order->get_meta(self::HIPAY_MULTIBANCO_REFERENCE);
        $amount = $order->get_meta(self::HIPAY_MULTIBANCO_AMOUNT);
        $expirationDate = $order->get_meta(self::HIPAY_MULTIBANCO_EXPIRATION_DATE);

        if (empty($entity) || empty($reference)) {
            $this->logs->logError("Missing Multibanco data for order " . $order->get_id());
            return false;
        }

        return [
            'entity' => $entity,
            'reference' => $reference,
            'amount' => $amount,
            'expirationDate' => $expirationDate
        ];
    }

    /**
     * Make Multibanco template for order details
     *
     * @param WC_Order $order
     */
    private function makeMultibancoTemplate($order) {
        $data = $this->getMultibancoData($order);
        if (!$data) {
            return;
        }

        // Register and enqueue HiPay SDK
        $version = defined('HIPAY_PLUGIN_VERSION') ? HIPAY_PLUGIN_VERSION : '1.0.0';
        wp_register_script('hipay-sdk', $this->confHelper->getPaymentGlobal()["sdk_js_url"], array(), $version, true);
        wp_enqueue_script('hipay-sdk');

        wp_localize_script('hipay-sdk', 'hipayMultibancoData', [
            'reference' => esc_js($data['reference']),
            'entity' => esc_js($data['entity']),
            'amount' => esc_js($data['amount']),
            'expirationDate' => esc_js($data['expirationDate']),
            'locale' => substr(get_locale(), 0, 2),
            'security' => wp_create_nonce('hipay_multibanco_data')
        ]);

        // Add inline script to initialize the reference
        $script = "
            document.addEventListener('DOMContentLoaded', function() {
                var hipaySdk = new HiPay({
                    username: 'hosted',
                    password: 'hosted',
                    environment: 'production',
                    lang: hipayMultibancoData.locale
                });
                
                hipaySdk.createReference('multibanco', {
                    selector: 'referenceToPay',
                    reference: hipayMultibancoData.reference,
                    entity: hipayMultibancoData.entity,
                    amount: hipayMultibancoData.amount,
                    expirationDate: hipayMultibancoData.expirationDate,
                });
            });
        ";

        wp_add_inline_script('hipay-sdk', $script);

        // Process the template
        $this->process_template(
            'multibanco.php',
            'frontend',
            $data
        );
    }

    /**
     * Make Multibanco template for email order details
     *
     * @param WC_Order $order
     */
    private function makeMultibancoTemplateEmail($order) {
        $data = $this->getMultibancoData($order);
        if (!$data) {
            return;
        }

        // Process the email template
        $this->process_template(
            'email/multibanco.php',
            'frontend',
            $data
        );
    }
}