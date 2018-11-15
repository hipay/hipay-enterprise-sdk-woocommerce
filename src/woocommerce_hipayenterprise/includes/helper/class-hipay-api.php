<?php
if (! defined('ABSPATH')) {
    exit;
}

class Hipay_Api
{

    private $configHipay = array();

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
     * @param type $moduleInstance
     * @param boolean $moto
     * @param boolean|string $forceConfig
     * @return \HiPay\Fullservice\Gateway\Client\GatewayClient
     */
    private function createGatewayClient()
    {
        $proxy = array();

        // Todo a voir après rework Etienne ( Il est enregistré en base 'no' ... )
        $sandbox = false;
        $sandbox = $this->plugin->settings["sandbox"] == 'yes' ? true : false;

        if ($this->plugin->settingsHipay["host_proxy"] !== "") {
            $proxy = array(
                "host" => $this->plugin->settingsHipay["host_proxy"],
                "port" => $this->plugin->settingsHipay["port_proxy"],
                "user" => $this->plugin->settingsHipay["user_proxy"],
                "password" => $this->plugin->settingsHipay["password_proxy"]
            );
        }

        //TODO Revoir le nom des champs et la variables settings à utiliser après rework Etienne
        $username = ($sandbox) ? $this->plugin->settings["account_test_private_username"]
            : $this->plugin->settings["account_production_private_username"];
        $password = ($sandbox) ? $this->plugin->settings["account_test_private_password"]
            : $this->plugin->settings["account_production_private_password"];

        $env = ($sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE
            : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

        $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env, null, $proxy);

        //Instantiate client provider with configuration object
        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);

        //Create your gateway client
        return new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
    }

    /**
     * @throws Exception
     */
    public function requestHostedPaymentPage($order)
    {
        try {
            $gatewayClient = $this->createGatewayClient();

            $params = array();
            $this->iniParamsWithConfiguration($params);

            $activatedPayment =  Hipay_Helper::getActivatedPaymentByCountryAndCurrency(
                $this->plugin,
                "credit_card",
                $order->get_billing_country(),
                $order->get_currency(),
                $order->get_total()
            );
            $params["productlist"] = join(",", array_keys($activatedPayment));

            $hostedPaymentFormatter = new Hipay_Hosted_Payment_Formatter($this->plugin, $params, $order);
            $orderRequest = $hostedPaymentFormatter->generate();

            $transaction = $gatewayClient->requestHostedPaymentPage($orderRequest);

            return $transaction->getForwardUrl();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Init params send to the api caller
     *
     */
    private function iniParamsWithConfiguration(&$params) {
        $params["basket"] = null;
        $params["delivery_informations"] = null;
        $params["iframe"] = $this->plugin->confHelper->getPaymentGlobal()["display_hosted_page"] == "iframe" ? true : false ;
        $params["authentication_indicator"] =  $this->plugin->confHelper->getPaymentGlobal()["activate_3d_secure"];
    }

//    public function requestDirectPost($order) {
//        //Create your gateway client
//        $gatewayClient = $this->createGatewayClient();
//
//$transaction = $gatewayClient->requestNewOrder($order);
//$redirectUrl = $transaction->getForwardUrl();
//
//if ($transaction->getStatus() == TransactionStatus::CAPTURED || $transaction->getStatus() == TransactionStatus::AUTHORIZED || $transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED) {
//$order_flag = $wpdb->get_row("SELECT order_id FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");
//if (isset($order_flag->order_id)) {
//SELF::reset_stock_levels($order);
//wc_reduce_stock_levels($order_id);
//$wpdb->update(
//$this->plugin_table,
//array('amount' => $order_total, 'stocks' => 1, 'url' => $redirectUrl),
//array('order_id' => $order_id)
//);
//} else {
//    wc_reduce_stock_levels($order_id);
//    $wpdb->insert(
//        $this->plugin_table,
//        array(
//            'reference' => 0,
//            'order_id' => $order_id,
//            'amount' => $order_total,
//            'stocks' => 1,
//            'url' => $redirectUrl
//        )
//    );
//}
//
//
//return array(
//    'result' => 'success',
//    'redirect' => $order->get_checkout_order_received_url()
//);
//} else {
//    $reason = $transaction->getReason();
//    $order->add_order_note(__('Error:', 'hipayenterprise') . " " . $reason['message']);
//    throw new Exception(
//        __(
//            'Error processing payment:',
//            'hipayenterprise'
//        ) . " " . $reason['message']
//    );
//}
//}
//    }


}
