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


use \HiPay\Fullservice\Enum\Transaction\TransactionStatus;
use \HiPay\Fullservice\Helper\Signature;
use \HiPay\Fullservice\HTTP\Configuration\Configuration;

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Upgrade_Helper
{
    public $confHelper;

    private $logs;

    private $configHipay;

    /*
     *
     */
    public function __construct()
    {
        $this->confHelper = new Hipay_Config();
        $this->configHipay = $this->confHelper->getConfigHipay();
        $this->logs = new Hipay_Log($this);
    }

    /**
     * Process configuration installation
     *
     */
    public function install()
    {
        try {
            $this->logs->logInfos("Install configuration HiPay plugin upgrade for  : " . WC_HIPAYENTERPRISE_VERSION);
            $this->confHelper->insertConfigHiPay();
            $this->logs->logInfos("Hipay Plugin configuration is now in version : " . WC_HIPAYENTERPRISE_VERSION);
        }  catch (\Exception $e) {
            $this->logs->logException($e);
            $this->logs->logInfos("[ERROR] Hipay Plugin configuration is in version : " . WC_HIPAYENTERPRISE_VERSION);
        }
    }

    /**
     * Process upgrade for new version
     *
     * @param string $currentPluginVersion Current version for the plugin
     */
    public function upgrade($currentPluginVersion)
    {
        try {
            $this->logs->logInfos("Begin plugin upgrade from  : " . $currentPluginVersion);

            $this->updateConfigFromFile();
            $this->confHelper->update_option($this->configHipay);

            $this->logs->logInfos("Hipay Plugin configuration is now in version : " . WC_HIPAYENTERPRISE_VERSION);
        }  catch (\Exception $e) {
            $this->logs->logException($e);
            $this->logs->logInfos("[ERROR] Hipay Plugin configuration is in version : " . WC_HIPAYENTERPRISE_VERSION);
        }
    }

    /**
     *  Process update for Credit Card configuration
     *
     */
    private function processUpdateConfigForCreditCard() {
        $paymentConfigCreditCard = $this->confHelper->insertPaymentsConfig("creditCard/");

        $this->logs->logInfos($paymentConfigCreditCard);
        $this->applyDiffFromDefaultConfig($this->configHipay,
            $paymentConfigCreditCard,
            Hipay_Config::KEY_CREDIT_CARD);


    }
    /**
     * Process update for local payment
     *
     */
    private function processUpdateConfigForLocalPayment() {
        $paymentConfigLocal = $this->confHelper->insertPaymentsConfig("local/");

        $this->logs->logInfos($paymentConfigLocal);
        $this->applyDiffFromDefaultConfig($this->configHipay,
            $paymentConfigLocal,
            Hipay_Config::KEY_LOCAL_PAYMENT);

    }

    /**
     * Update HiPay base configuration
     */
    public function updateConfigFromFile()
    {
        $this->logs->logInfos("# Begin Update config from Payment Config files for local");
        $this->processUpdateConfigForLocalPayment();
        $this->logs->logInfos("# End Update config from Payment Config files for local" .
            print_r($this->configHipay,true));

        $this->logs->logInfos("# Begin Update config from Payment Config files for Credit Card ");
        $this->processUpdateConfigForCreditCard();
        $this->logs->logInfos("# End Update config from Payment Config files for Credit Card" .
            print_r($this->configHipay,true));
    }

    /**
     * Merge old configuration and new configuration from HiPay
     *
     * @param $configHipay
     * @param $paymentMethod
     * @param $paymentMethodType
     */
    private function applyDiffFromDefaultConfig(&$configHipay, $paymentMethod, $paymentMethodType)
    {
        // Add new payment Method
        $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType] = array_merge(
            $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType],
            array_diff_key($paymentMethod, $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType])
        );

        // remove deprecated payment method
        foreach (array_diff_key($configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType], $paymentMethod) as $removeKey => $item) {
            unset($configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$removeKey]);
        }

        foreach ($paymentMethod as $key => $value) {
            // Add new properties to payment method
            $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$key] = array_merge(
                $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$key],
                array_diff_key($paymentMethod[$key], $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$key])
            );

            // Remove old properties
            $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$key] = array_diff_key(
                $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$key],
                array_diff_key($configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$key], $paymentMethod[$key])
            );

            // Preserve saved parameters in Database, only parameters not in $keepParameters[$key] will be override
            $replace = array_diff_key($paymentMethod[$key], $this->getPropertiesNoOverride());

            // Override properties
            $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$key] = array_replace(
                $configHipay[Hipay_Config::KEY_PAYMENT][$paymentMethodType][$key],
                $replace
            );
        }
    }

    /**
     *  Get config properties where we keep configuration saved
     *
     * @return array
     */
    private function getPropertiesNoOverride()
    {
        return  array(
            "currencies" => "",
            "countries" => "",
            "minAmount" => "",
            "maxAmount" => "",
            "frontPosition" => "",
            "activated" => ""
        );
    }
}
