<?php
if (! defined('ABSPATH')) {
    exit;
}

class WC_HipayEnterprise_Config
{

    private $configHipay = array();

    protected $plugin;


    /**
     *
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->jsonFilesPath = dirname(__FILE__) . "/../../paymentConfigFiles/";
    }


    /**
     *  Get Config Hipay
     *
     * @return array
     */
    public function getConfigHipay()
    {
        if (empty($this->configHipay)) {
            $this->initConfigHiPay();
        }

        return $this->configHipay;
    }

    /**
     * Functions to init the configuration HiPay
     */
    private function initConfigHiPay()
    {
        $this->configHipay = array_merge($this->plugin->settings, get_option($this->plugin->settings_name, array()));

        // if config exist but empty, init new object for configHipay
        if (!$this->configHipay || empty($this->configHipay)) {
            $this->insertConfigHiPay();
            $this->configHipay = array_merge($this->plugin->settings, get_option($this->plugin->settings_name, array()));
        }
    }

    public function insertConfigHiPay() {
        $configFields = $this->getDefaultConfig();
        $configFields["payment"]["credit_card"] = $this->insertPaymentsConfig("creditCard/");
        $configFields["payment"]["local_payment"] = $this->insertPaymentsConfig("local/");

        update_option($this->plugin->settings_name, $configFields);
    }

    /**
     * Get base config value
     *
     * @return array
     */
    private function getDefaultConfig()
    {
        return array(
            "payment" => array(
                "global" => array(
                    "operating_mode" => ApiMode::DIRECT_POST,
                    "iframe_hosted_page_template" => "basic-js",
                    "display_card_selector" => 0,
                    "display_hosted_page" => "redirect",
                    "css_url" => "",
                    "activate_3d_secure" => ThreeDS::THREE_D_S_DISABLED,
                    "capture_mode" => "automatic",
                    "activate_basket" => 0,
                    "card_token" => 0,
                    "log_infos" => 1,
                    "regenerate_cart_on_decline" => 1
                ),
                "credit_card" => array(),
                "local_payment" => array()
            ),
            "fraud" => array(
                "send_payment_fraud_email_copy_to" => "",
                "send_payment_fraud_email_copy_method" => "bcc"
            )
        );
    }

    /**
     * @return mixed
     */
    public function getPaymentGlobal()
    {
        return $this->getConfigHipay()["payment"]["global"];
    }

    /**
     * @return mixed
     */
    public function getPaymentCreditCard()
    {
        return $this->getConfigHipay()["payment"]["credit_card"];
    }

    /**
     * @return mixed
     */
    public function getFraud()
    {
        return $this->getConfigHipay()["fraud"];
    }

    /**
     * @return mixed
     */
    public function getLocalPayment()
    {
        return $this->getConfigHipay()["payment"]["local_payment"];
    }

    /**
     * init local config
     *
     * @return array
     */
    private function insertPaymentsConfig($folderName)
    {
        $creditCard = array();

        $files = scandir($this->jsonFilesPath . $folderName);

        foreach ($files as $file) {
            $creditCard = array_merge($creditCard, $this->addPaymentConfig($file, $folderName));
        }

        return $creditCard;
    }

    /**
     * Add specific payment config from JSON file
     *
     * @param $file
     * @param $folderName
     * @return array
     */
    private function addPaymentConfig($file, $folderName)
    {
        $creditCard = array();

        if (preg_match('/(.*)\.json/', $file) == 1) {
            $json = json_decode(file_get_contents($this->jsonFilesPath . $folderName . $file),true);
            $creditCard[$json["name"]] = $json["config"];
        }

        return $creditCard;
    }

}
