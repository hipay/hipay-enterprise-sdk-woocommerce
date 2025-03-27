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

use Picqer\Barcode\BarcodeGeneratorPNG;
/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Sisal extends Hipay_Gateway_Local_Abstract
{
    /**
     *
     * @var string HIPAY_SISAL_REFERENCE order item meta sisal reference
     */
    const HIPAY_SISAL_REFERENCE = 'hipay_sisal_reference';

    /**
     *
     * @var string HIPAY_SISAL_BARCODE order item meta sisal barcode
     */
    const HIPAY_SISAL_BARCODE = 'hipay_sisal_barcode';

    public function __construct()
    {
        $this->id = 'hipayenterprise_sisal';
        $this->paymentProduct = 'sisal';
        $this->method_title = __('HiPay Enterprise Sisal', "hipayenterprise");
        $this->title = __('Sisal', "hipayenterprise");
        $this->method_description = __('Sisal', "hipayenterprise");

        parent::__construct();

        add_action("woocommerce_thankyou_$this->id", array($this, 'thanks_page'));
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        add_action('woocommerce_view_order', array($this, 'thanks_page'));
    }

    /**
     * Sisal details for email template
     *
     * @param WC_Order  $order_
     * @param bool      $sent_to_admin
     * @param bool      $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($order->get_payment_method() === $this->id) {
            $this->makeSisalTemplateEmail($order);
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

            $order->update_meta_data(self::HIPAY_SISAL_REFERENCE, $referenceToPay["reference"]);
            $order->update_meta_data(self::HIPAY_SISAL_BARCODE, $referenceToPay["barCode"]);
            $order->save();
            $orderNote = __('Reference:', "hipayenterprise") . " " . $referenceToPay["reference"] . " " . __('Bar code:', "hipayenterprise") . " " . $referenceToPay["barCode"] . " ";
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
     * Sisal details display template
     *
     * @param int $order_id
     */
    public function thanks_page($order_id)
    {
        global $woocommerce;

        $order = new WC_Order($order_id);

        if ($order->get_payment_method() === $this->id) {
            $this->makeSisalTemplate($order);

            $woocommerce->cart->empty_cart();
        }
    }

    /**
     * Get Sisal payment data from order
     *
     * @param WC_Order $order
     * @return array|false Payment data or false if missing required data
     */
    private function getSisalData($order) {
        $reference = $order->get_meta(self::HIPAY_SISAL_REFERENCE);
        $barCode = $order->get_meta(self::HIPAY_SISAL_BARCODE);

        if (empty($reference) || empty($barCode)) {
            $this->logs->logErrors("Missing Sisal data for order " . $order->get_id());
            return false;
        }

        return [
            'reference' => $reference,
            'barCode' => $barCode,
            'barCodeImg' => esc_attr($this->generate_barcode_base64($barCode)),
            'sdkJsUrl' => $this->confHelper->getPaymentGlobal()["sdk_js_url"]
        ];
    }

    /**
     * Make Sisal template for order details
     * @param WC_Order $order
     */
    private function makeSisalTemplate($order) {
        $data = $this->getSisalData($order);
        if (!$data) {
            return;
        }

        $version = defined('HIPAY_PLUGIN_VERSION') ? HIPAY_PLUGIN_VERSION : '1.0.0';
        wp_register_script('hipay-sdk', $data['sdkJsUrl'], array('jquery'), $version, true);
        wp_enqueue_script('hipay-sdk');

        wp_localize_script('hipay-sdk', 'hipaySisalData', [
            'reference' => esc_js($data['reference']),
            'barCode' => esc_js($data['barCode']),
            'barCodeImg' => esc_js($data['barCodeImg']),
            'locale' => substr(get_locale(), 0, 2),
            'security' => wp_create_nonce('hipay_sisal_data')
        ]);

        $script = "
            document.addEventListener('DOMContentLoaded', function() {
                var hipaySdk = new HiPay({
                    username: 'hosted',
                    password: 'hosted',
                    environment: 'production',
                    lang: hipaySisalData.locale
                });
                
                hipaySdk.createReference('sisal', {
                    selector: 'referenceToPay',
                    reference: hipaySisalData.reference,
                    barCode: hipaySisalData.barCode
                });
            });
        ";

        wp_add_inline_script('hipay-sdk', $script);

        $this->process_template(
            'email/sisal.php',
            'frontend',
            $data
        );
    }

    /**
     * Make Sisal template for email order details
     * @param WC_Order $order
     */
    private function makeSisalTemplateEmail($order) {
        $data = $this->getSisalData($order);
        if (!$data) {
            return;
        }

        $this->process_template(
            'email/sisal.php',
            'frontend',
            $data
        );
    }

    private function generate_barcode_base64($barcode)
    {
        $generator = new BarcodeGeneratorPNG();
        $barcodeData = $generator->getBarcode($barcode, $generator::TYPE_CODE_128_C, 3, 100);
        $base64 = 'data:image/png;base64,' . base64_encode($barcodeData);

        return $base64;
    }

}