<?php
if (!defined('ABSPATH')) {
    exit;
}

use HiPay\Fullservice\Enum\Helper\HashAlgorithm;

class Hipay_Config
{

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
        return $this->plugin->settings;
    }

    /**
     * Functions to init the configuration HiPay
     */
    public function initConfigHiPay()
    {
        // if config exist but empty, init new object for configHipay
        if (!$this->plugin->settings || empty($this->plugin->settings)) {
            $this->insertConfigHiPay();
            $this->plugin->init_settings();
        }
    }

    public function insertConfigHiPay()
    {
        $configFields = $this->getDefaultConfig();
        $configFields["payment"]["credit_card"] = $this->insertPaymentsConfig("creditCard/");
        $configFields["payment"]["local_payment"] = $this->insertPaymentsConfig("local/");

        update_option($this->plugin->get_option_key(), $configFields);
    }

    public function saveConfiguration($settings)
    {
        $configFields = array_merge($this->getConfigHipay(), $settings);

        update_option($this->plugin->get_option_key(), $configFields);
    }

    /**
     * Get base config value
     *
     * @return array
     */
    private function getDefaultConfig()
    {
        return array(
            "account" => array(
                "global" => array(
                    "sandbox_mode" => 1,
                    "host_proxy" => "",
                    "port_proxy" => "",
                    "user_proxy" => "",
                    "password_proxy" => ""
                ),
                "sandbox" => array(
                    "api_username_sandbox" => "",
                    "api_password_sandbox" => "",
                    "api_tokenjs_username_sandbox" => "",
                    "api_tokenjs_password_publickey_sandbox" => "",
                    "api_secret_passphrase_sandbox" => "",
                ),
                "production" => array(
                    "api_username_production" => "",
                    "api_password_production" => "",
                    "api_tokenjs_username_production" => "",
                    "api_tokenjs_password_publickey_production" => "",
                    "api_secret_passphrase_production" => "",
                ),
                "hash_algorithm" => array(
                    "production" => HashAlgorithm::SHA256,
                    "test" => HashAlgorithm::SHA256,
                    "production_moto" => HashAlgorithm::SHA256,
                    "test_moto" => HashAlgorithm::SHA256
                )
            ),
            "payment" => array(
                "global" => array(
                    "operating_mode" => OperatingMode::DIRECT_POST,
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
    public function getAccount()
    {
        return $this->getConfigHipay()["account"];
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
            $json = json_decode(file_get_contents($this->jsonFilesPath . $folderName . $file), true);
            $creditCard[$json["name"]] = $json["config"];
        }

        return $creditCard;
    }

}
