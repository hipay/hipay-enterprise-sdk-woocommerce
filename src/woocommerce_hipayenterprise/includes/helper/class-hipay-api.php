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

class Hipay_Api
{

    protected $plugin;

    /**
     *
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * create gateway client from config and client provider
     *
     * @param bool $forceConfig
     * @return \HiPay\Fullservice\Gateway\Client\GatewayClient
     */
    private function createGatewayClient($forceConfig = false)
    {
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
     * @param $order
     * @param $params
     * @return \HiPay\Fullservice\Gateway\Model\Transaction|\HiPay\Fullservice\Model\AbstractModel
     * @throws Exception
     */
    public function requestDirectPost($order, $params)
    {
        try {
            $gatewayClient = $this->createGatewayClient();
            $this->iniParamsWithConfiguration($params);

            $directPostFormatter = new Hipay_Direct_Post_Formatter($this->plugin, $params, $order);
            $orderRequest = $directPostFormatter->generate();
            return $gatewayClient->requestNewOrder($orderRequest);

        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $order
     * @return string
     * @throws Exception
     */
    public function requestHostedPaymentPage($order)
    {
        try {
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

            $this->plugin->logs->logInfos("# RequestHostedPaymentPage " . $order->id);

            return $transaction->getForwardUrl();
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
            throw new Exception($e->getMessage());
        }
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
     */
    private function iniParamsWithConfiguration(&$params)
    {
        $params["basket"] = null;
        $params["delivery_informations"] = null;
        $params["iframe"] = $this->plugin->confHelper->getPaymentGlobal()["display_hosted_page"] ==
        "iframe" ? true : false;
        $params["authentication_indicator"] = $this->plugin->confHelper->getPaymentGlobal()["activate_3d_secure"];
    }
}
