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
     * @var Hipay_Delivery_Formatter|null
     */
    protected $operationsHelper;


    /**
     * Hipay_Api constructor.
     * @param $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->operationsHelper = Hipay_Operations_Helper::initHiPayOperationsHelper($plugin);
    }

    /**
     * Create Direct Post request and send it
     *
     * @param $order
     * @param $params
     * @return \HiPay\Fullservice\Gateway\Model\Transaction|\HiPay\Fullservice\Model\AbstractModel
     * @throws Hipay_Payment_Exception
     */
    public function requestDirectPost($order, $params)
    {
        $this->plugin->logs->logInfos("# requestDirectPost " . $order->get_id());

        $gatewayClient = $this->createGatewayClient();

        $directPostFormatter = new Hipay_Direct_Post_Formatter($this->plugin, $params, $order);
        $orderRequest = $directPostFormatter->generate();

        $this->plugin->logs->logRequest($orderRequest);

        return $gatewayClient->requestNewOrder($orderRequest);
    }

    /**
     * Request capture or refund to HiPay API
     *
     * @param $params
     * @return \HiPay\Fullservice\Gateway\Model\Operation|\HiPay\Fullservice\Model\AbstractModel
     * @throws Exception
     */
    public function requestMaintenance($params)
    {
        $order = wc_get_order($params["order_id"]);
        $this->plugin->logs->logInfos("# RequestMaintenance " . $order->get_id());

        $gatewayClient = $this->createGatewayClient();
        $params["transaction_reference"] = $order->get_transaction_id();

        $maintenanceFormatter = new Hipay_Maintenance_Formatter($this->plugin, $params, $order);

        $maintenanceRequest = $maintenanceFormatter->generate();
        $this->plugin->logs->logRequest($maintenanceRequest);

        $transaction = $gatewayClient->requestMaintenanceOperation(
            $params["operation"],
            $params["transaction_reference"],
            $maintenanceRequest->amount,
            null,
            $maintenanceRequest
        );

        $this->plugin->logs->logInfos("# RequestMaintenance - SaveOperation" . $order->get_id());
        $this->operationsHelper->saveOperation(
            $params["order_id"],
            $transaction,
            $params["operation"],
            $maintenanceRequest->operation_id
        );

        return $transaction;
    }

    /**
     * Create Hosted Page request and send it
     *
     * @param $order
     * @param $params
     * @return string
     * @throws Exception
     */
    public function requestHostedPaymentPage($order, $params)
    {
        $gatewayClient = $this->createGatewayClient();

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
     * @param $platform
     * @return \HiPay\Fullservice\Model\AbstractModel|mixed
     * @throws Exception
     */
    public function getSecuritySettings($platform)
    {
        try {
            $gatewayClient = $this->createGatewayClient($platform);

            $response = $gatewayClient->requestSecuritySettings();

            $this->plugin->logs->logInfos("# RequestSecuritySettings for ${$platform}");

            return $response;
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * create gateway client from config and client provider
     *
     * @param bool $forceConfig : Configuration::API_ENV_STAGE | Configuration::API_ENV_PRODUCTION | false
     * @return \HiPay\Fullservice\Gateway\Client\GatewayClient
     * @throws Exception
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

        $env = ($sandbox) ? Configuration::API_ENV_STAGE : Configuration::API_ENV_PRODUCTION;

        $config = new Configuration(
            array(
                'apiUsername' => $username,
                'apiPassword' => $password,
                'apiEnv' => $env,
                'apiHTTPHeaderAccept' => 'application/json',
                'proxy' => $proxy,
                'hostedPageV2' => true
            )
        );

        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        return new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
    }
}
