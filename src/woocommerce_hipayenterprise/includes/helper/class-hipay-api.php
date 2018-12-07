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

use \HiPay\Fullservice\HTTP\Configuration\Configuration;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Api
{

    /**
     * @var Hipay_Gateway_Abstract
     */
    protected $plugin;

    /**
     * Hipay_Api constructor.
     * @param $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * create gateway client from config and client provider
     *
     * @param bool $forceConfig : Configuration::API_ENV_STAGE | Configuration::API_ENV_PRODUCTION | false
     * @return \HiPay\Fullservice\Gateway\Client\GatewayClient
     */
    private function createGatewayClient($forceConfig = false)
    {
        //@TODO implements proxy configuration
        $proxy = array();

        if (!$forceConfig) {
            $sandbox = $this->plugin->confHelper->isSandbox();
        } else {
            $sandbox = ($forceConfig === Configuration::API_ENV_STAGE);
        }

        $username = ($sandbox) ? $this->plugin->confHelper->getAccount()["sandbox"]["api_username_sandbox"]
            : $this->plugin->confHelper->getAccount()["production"]["api_username_production"];
        $password = ($sandbox) ? $this->plugin->confHelper->getAccount()["sandbox"]["api_password_sandbox"]
            : $this->plugin->confHelper->getAccount()["production"]["api_password_production"];

        $env = ($sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE
            : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

        $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env, null, $proxy);

        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        return new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
    }

    /**
     * Create Direct Post request and send it
     *
     * @param $order
     * @param $params
     * @return \HiPay\Fullservice\Gateway\Model\Transaction|\HiPay\Fullservice\Model\AbstractModel
     * @throws Exception
     */
    public function requestDirectPost($order, $params)
    {
            $this->plugin->logs->logInfos("# requestDirectPost " . $order->id);

            $gatewayClient = $this->createGatewayClient();
            $this->iniParamsWithConfiguration($params);

            $directPostFormatter = new Hipay_Direct_Post_Formatter($this->plugin, $params, $order);
            $orderRequest = $directPostFormatter->generate();

            $this->plugin->logs->logRequest($orderRequest);

            return $gatewayClient->requestNewOrder($orderRequest);
    }

    /**
     * Create Hosted Page request and send it
     *
     * @param $order
     * @return string
     */
    public function requestHostedPaymentPage($order)
    {
        $gatewayClient = $this->createGatewayClient();

        $params = array();
        $this->iniParamsWithConfiguration($params);

        $activatedPayment = Hipay_Helper::getActivatedPaymentByCountryAndCurrency(
                $this->plugin,
                "credit_card",
                $order->get_billing_country(),
                $order->get_currency(),
                $order->get_total()
            );

        $params["productlist"] = join(",", array_keys($activatedPayment));

        $hostedPaymentFormatter = new Hipay_Hosted_Payment_Formatter($this->plugin, $params, $order);
        $orderRequest = $hostedPaymentFormatter->generate();

        $this->plugin->logs->logRequest($orderRequest);
        $transaction = $gatewayClient->requestHostedPaymentPage($orderRequest);

        $this->plugin->logs->logInfos("# RequestHostedPaymentPage " . $order->get_id());

        return $transaction->getForwardUrl();
    }

    /**
     * Get Security Settings form Backend Hipay
     *
     * @param $plateform
     * @return \HiPay\Fullservice\Model\AbstractModel|mixed
     * @throws Exception
     */
    public function getSecuritySettings($plateform)
    {
        try {
            $gatewayClient = $this->createGatewayClient($plateform);

            $response = $gatewayClient->requestSecuritySettings();

            $this->plugin->logs->logInfos("# RequestSecuritySettings for ${plateform}");

            return $response;
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Init params send to the api caller
     *
     * @param $params
     */
    private function iniParamsWithConfiguration(&$params)
    {
        $params["basket"] = null;
        $params["delivery_informations"] = null;
        $params["iframe"] = $this->plugin->confHelper->getPaymentGlobal()["display_hosted_page"] === "iframe";
        $params["authentication_indicator"] = $this->plugin->confHelper->getPaymentGlobal()["activate_3d_secure"];
    }
}
