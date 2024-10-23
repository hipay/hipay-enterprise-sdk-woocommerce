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
class Hipay_Available_Payment
{

    protected $confHelper;

    private static $instance = null;
    private $apiUsername;
    private $apiPassword;
    private $authorizationHeader;
    private $baseUrl;


    /**
     * Hipay_Api constructor.
     * @param $confHelper
     */
    public function __construct($confHelper)
    {
        $this->confHelper = $confHelper;
        $this->setCredentialsAndUrl();
        $this->generateAuthorizationHeader();
    }

    public static function getInstance($confHelper)
    {
        if (self::$instance === null) {
            self::$instance = new self($confHelper);
        }
        return self::$instance;
    }

    private function setCredentialsAndUrl()
    {
        $sandbox = $this->confHelper->isSandbox();
        $this->apiUsername = ($sandbox) ? $this->confHelper->getAccount()["sandbox"]["api_username_sandbox"]
            : $this->confHelper->getAccount()["production"]["api_username_production"];
        $this->apiPassword  = ($sandbox) ? $this->confHelper->getAccount()["sandbox"]["api_password_sandbox"]
            : $this->confHelper->getAccount()["production"]["api_password_production"];
        $this->baseUrl = ($sandbox) ? 'https://stage-secure-gateway.hipay-tpp.com/rest/v2/'
            : 'https://secure-gateway.hipay-tpp.com/rest/v2/';
    }

    private function generateAuthorizationHeader()
    {
        $credentials = $this->apiUsername . ':' . $this->apiPassword;
        $encodedCredentials = base64_encode($credentials);
        $this->authorizationHeader = 'Basic ' . $encodedCredentials;
    }

    public function getAvailablePaymentProducts(
        $paymentProduct = 'paypal',
        $eci = '7',
        $operation = '4',
        $withOptions = 'true'
    ) {
        $url = $this->baseUrl . 'available-payment-products.json';
        $url .= '?eci=' . urlencode($eci);
        $url .= '&operation=' . urlencode($operation);
        $url .= '&payment_product=' . urlencode($paymentProduct);
        $url .= '&with_options=' . urlencode($withOptions);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $this->authorizationHeader,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
