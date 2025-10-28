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

use HiPay\Fullservice\Enum\Helper\HashAlgorithm;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Config
{

    const OPTION_KEY = "hipay_enterprise";

    const KEY_LOCAL_PAYMENT = 'local_payment';

    const KEY_CREDIT_CARD = "credit_card";

    const KEY_PAYMENT = "payment";

    /**
     * @var array
     */
    private $configHipay = array();

    /**
     * @var string
     */
    private $jsonFilesPath;

    /**
     * Hipay_Config constructor.
     */
    public function __construct()
    {
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
    public function initConfigHiPay()
    {
        $this->configHipay = get_option(self::OPTION_KEY, array());
    }

    /**
     *
     */
    public function insertConfigHiPay()
    {
        $configFields = $this->getDefaultConfig();
        $configFields["payment"]["credit_card"] = $this->insertPaymentsConfig("creditCard/");
        $configFields["payment"]["local_payment"] = $this->insertPaymentsConfig("local/");

        update_option(self::OPTION_KEY, $configFields);
        update_option('hipay_enterprise_version', WC_HIPAYENTERPRISE_VERSION);
    }

    /**
     * Override all Hipay configuration
     *
     * @param $newConfiguration
     */
    public function update_option($newConfiguration)
    {
        update_option(self::OPTION_KEY, $newConfiguration);
    }

    /**
     * @param $settings
     */
    public function saveConfiguration($settings)
    {
        $configFields = array_merge($this->getConfigHipay(), $settings);

        update_option(self::OPTION_KEY, $configFields);
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
                    "stage" => HashAlgorithm::SHA256,
                )
            ),
            "payment" => array(
                "global" => array(
                    "operating_mode" => OperatingMode::HOSTED_FIELDS,
                    "iframe_hosted_page_template" => "basic-js",
                    "display_card_selector" => 0,
                    "display_hosted_page" => "redirect",
                    "display_cancel_button" => 0,
                    "hosted_fields_style" => array(
                        "base" => array(
                            "color" => "#000000",
                            "fontFamily" => "Roboto",
                            "fontSize" => "15px",
                            "fontWeight" => "400",
                            "placeholderColor" => "",
                            "caretColor" => "#A50979",
                            "iconColor" => "#A50979",
                        )
                    ),
                    "css_url" => "",
                    "activate_3d_secure" => ThreeDS::THREE_D_S_DISABLED,
                    "sdk_js_url" => 'https://libs.hipay.com/js/sdkjs.js',
                    "capture_mode" => "automatic",
                    "activate_basket" => 0,
                    "card_token" => 0,
                    'number_saved_cards_displayed' => '',
                    'switch_color_input' => '#02A17B',
                    'checkbox_color_input' => '#02A17B',
                    SettingsField::PAYMENT_GLOBAL_LOGS_INFOS => 1,
                    "send_url_notification" => 1,
                    "ccDisplayName" => array("fr" => "Carte de crédit", "en" => "Credit card"),
                    "skip_onhold" => 0
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
     * @return bool
     */
    public function isSandbox()
    {
        return (bool)$this->getAccount()["global"]["sandbox_mode"];
    }

    /**
     * @return bool
     */
    public function isOneClick()
    {
        return (bool)$this->getPaymentGlobal()["card_token"];
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
    public function getAccountProduction()
    {
        return $this->getConfigHipay()["account"]["production"];
    }

    /**
     * @return mixed
     */
    public function getHashAlgorithm()
    {
        return $this->getConfigHipay()["account"]["hash_algorithm"];
    }

    /**
     * @return mixed
     */
    public function getAccountSandbox()
    {
        return $this->getConfigHipay()["account"]["sandbox"];
    }

    /**
     * @return mixed
     */
    public function getPayment()
    {
        return $this->getConfigHipay()["payment"];
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
    public function getHostedFieldsStyle()
    {
        return $this->getPaymentGlobal()["hosted_fields_style"]["base"];
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
    public function getLocalPayments()
    {
        return $this->getConfigHipay()["payment"][self::KEY_LOCAL_PAYMENT];
    }

    /**
     * @param $paymentId
     * @return mixed|null
     */
    public function getLocalPayment($paymentId)
    {
        $config = $this->getConfigHipay();
        if (isset($config["payment"][self::KEY_LOCAL_PAYMENT][$paymentId])) {
            return $config["payment"][self::KEY_LOCAL_PAYMENT][$paymentId];
        }
        return null;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setHashAlgorithm($value)
    {
        return $this->setConfigHiPay("account", $value, "hash_algorithm");
    }

    /**
     * Save a specific key of the module config
     *
     * @param $key
     * @param $value
     * @param null $child
     */
    public function setConfigHiPay($key, $value, $child = null)
    {
        $conf = $this->getConfigHipay();

        if (isset($child)) {
            $conf[$key][$child] = $value;
        } else {
            $conf[$key] = $value;
        }

        update_option(self::OPTION_KEY, $conf);
    }

    /**
     * init local config
     *
     * @param $folderName
     * @return array
     */
    public function insertPaymentsConfig($folderName)
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

            $sdkConfig = \HiPay\Fullservice\Data\PaymentProduct\Collection::getItem($json["name"]);

            if ($sdkConfig !== null) {
                // Array merge gives priority to the last array over the first when keys are in both tables
                $creditCard[$json["name"]] = array_merge($sdkConfig->toArray(), $json["config"]);
            }
        }

        return $creditCard;
    }

    /**
     *
     * Check basket requirements for HiPay Platform compliance
     *
     * @param $notifications
     */
    public function checkBasketRequirements(&$notifications)
    {
        if ($this->getPaymentGlobal()["activate_basket"]) {
            $categoriesMappingController = new Hipay_Mapping_Category_Controller();
            $deliveryMappingController = new Hipay_Mapping_Delivery_Controller();

            if (empty($categoriesMappingController->getAllMappingCategories())) {
                $notifications[] = __('You need to map your product categories.', "hipayenterprise");
            }

            if (empty($deliveryMappingController->getAllDeliveryMapping())) {
                $notifications[] = __('You need to map your carriers.', "hipayenterprise");
            }
        }
    }
}
