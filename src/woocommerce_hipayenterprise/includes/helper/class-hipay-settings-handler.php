<?php
if (!defined('ABSPATH')) {
    exit;
}

class Hipay_Settings_Handler
{
    protected $plugin;

    protected $errors;

    /**
     * Hipay_Settings_Handler constructor.
     * @param $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->errors = array();
    }

    /**
     * @param $settings
     * @return bool
     */
    public function saveAccountSettings(&$settings)
    {
        $this->plugin->logs->logInfos("# saveAccountSettings");

        try {
            if (
                !empty($_POST['woocommerce_hipayenterprise_account_sandbox_username'])
                && empty($_POST['woocommerce_hipayenterprise_account_sandbox_password'])
            ) {
                $this->addError(__("If sandbox api username is filled sandbox api password is mandatory"));
            }

            $settings["account"]["global"] = array(
                "sandbox_mode" => sanitize_title($_POST['woocommerce_hipayenterprise_sandbox'])
            );

            if (
                !empty($_POST['woocommerce_hipayenterprise_account_sandbox_tokenjs_username'])
                && empty($_POST['woocommerce_hipayenterprise_account_sandbox_password_publickey'])
            ) {
                $this->addError(
                    __("If sandbox api TokenJS username is filled sandbox api TokenJS password is mandatory")
                );
            }

            if (
                !empty($_POST['woocommerce_hipayenterprise_account_production_username'])
                && empty($_POST['woocommerce_hipayenterprise_account_production_password'])
            ) {
                $this->addError(__("If production api username is filled production api password is mandatory"));
            }

            if (
                !empty($_POST['woocommerce_hipayenterprise_account_production_tokenjs_username'])
                && empty($_POST['woocommerce_hipayenterprise_account_production_password_publickey'])
            ) {
                $this->addError(
                    __("If production api TokenJS username is filled production api TokenJS password is mandatory")
                );
            }

            $this->handleErrors();

            $settings["account"]["global"] = array(
                "sandbox_mode" => sanitize_title($_POST['woocommerce_hipayenterprise_sandbox'])
            );

            $settings["account"]["sandbox"] = array(
                "api_username_sandbox" => $_POST['woocommerce_hipayenterprise_account_sandbox_username'],
                "api_password_sandbox" => $_POST['woocommerce_hipayenterprise_account_sandbox_password'],
                "api_secret_passphrase_sandbox" => $_POST['woocommerce_hipayenterprise_account_sandbox_secret_passphrase'],
                "api_tokenjs_username_sandbox" => $_POST['woocommerce_hipayenterprise_account_sandbox_tokenjs_username'],
                "api_tokenjs_password_publickey_sandbox" => $_POST['woocommerce_hipayenterprise_account_sandbox_password_publickey']
            );

            $settings["account"]["production"] = array(
                "api_username_production" => $_POST['woocommerce_hipayenterprise_account_production_username'],
                "api_password_production" => $_POST['woocommerce_hipayenterprise_account_production_password'],
                "api_secret_passphrase_production" => $_POST['woocommerce_hipayenterprise_account_production_secret_passphrase'],
                "api_tokenjs_username_production" => $_POST['woocommerce_hipayenterprise_account_production_tokenjs_username'],
                "api_tokenjs_password_publickey_production" => $_POST['woocommerce_hipayenterprise_account_production_password_publickey']
            );

            $settings["account"]["hash_algorithm"] = $this->plugin->confHelper->getHashAlgorithm();

            $this->plugin->logs->logInfos($settings);
            return true;
        } catch (Hipay_Settings_Exception $e) {
            $this->plugin->logs->logInfos($e);
            $settings["account"]["production"] = $this->plugin->confHelper->getAccountProduction();
            $settings["account"]["sandbox"] = $this->plugin->confHelper->getAccountSandbox();
            $settings["account"]["global"] = $this->plugin->confHelper->getAccount()["global"];
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
        }

        return false;
    }

    /**
     * @param $settings
     * @return bool
     */
    public function savePaymentGlobal(&$settings)
    {
        $this->plugin->logs->logInfos("# SavePaymentGlobal");

        try {
            $this->handleErrors();

            $settings["payment"]["global"] = array(
                'operating_mode' => sanitize_title($_POST['operating_mode']),
                'capture_mode' => sanitize_title($_POST['capture_mode']),
                'activate_3d_secure' => sanitize_title($_POST['activate_3d_secure']),
                'log_infos' => sanitize_title($_POST['log_infos']),
                'card_token' => sanitize_title($_POST['card_token']),
                'activate_basket' => sanitize_title($_POST['activate_basket']),
                'css_url' => sanitize_title($_POST['css_url']),
                'display_hosted_page' => sanitize_title($_POST['display_hosted_page']),
                'display_card_selector' => sanitize_title($_POST['display_card_selector']),
                'send_url_notification' => sanitize_title($_POST['send_url_notification']),
                "hosted_fields_style" => array(
                    "base" => array(
                        "color" => $_POST['color'],
                        "fontFamily" => $_POST['fontFamily'],
                        "fontSize" => $_POST['fontSize'],
                        "fontWeight" => $_POST['fontWeight'],
                        "placeholderColor" => $_POST['placeholderColor'],
                        "caretColor" => $_POST['caretColor'],
                        "iconColor" => $_POST['iconColor'],
                    )
                )
            );

            $this->plugin->logs->logInfos($settings);

            return true;
        } catch (Hipay_Settings_Exception $e) {
            $this->plugin->logs->logInfos($e);
            $settings["payment"]["global"] = $this->plugin->confHelper->getPaymentGlobal();
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
        }

        return false;
    }

    /**
     * @param $settings
     * @return bool
     */
    public function saveFraudSettings(&$settings)
    {
        $this->plugin->logs->logInfos("# SaveFraudSettings");

        try {

            if(empty(sanitize_email($_POST['woocommerce_hipayenterprise_fraud_copy_to']))){
                $this->addError(__('"Copy to should" be a valid email'));
            }
            $this->handleErrors();

            $settings['fraud']['copy_to'] = sanitize_email(
                $_POST['woocommerce_hipayenterprise_fraud_copy_to']
            );
            $settings['fraud']['copy_method'] = sanitize_title(
                $_POST['woocommerce_hipayenterprise_fraud_copy_method']
            );

            $this->plugin->logs->logInfos($settings);

            return true;
        } catch (Hipay_Settings_Exception $e) {
            $this->plugin->logs->logInfos($e);
            $settings['fraud'] = $this->plugin->confHelper->getFraud();
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
        }
        return false;
    }

    /**
     * @param $settings
     * @return bool
     */
    public function saveCreditCardSettings(&$settings)
    {
        $this->plugin->logs->logInfos("# SaveCreditCardInformations");

        try {
            $this->handleErrors();

            $keySaved = array(
                "activated",
                "currencies",
                "countries",
                "minAmount",
                "maxAmount"
            );

            $methodsCreditCard = $this->plugin->confHelper->getPaymentCreditCard();
            foreach ($methodsCreditCard as $card => $conf) {
                foreach ($conf as $key => $value) {
                    if (in_array($key, $keySaved)) {
                        $settings["payment"]["credit_card"][$card][$key] = $_POST["woocommerce_hipayenterprise_methods_creditCard_" .
                        $key][$card];
                    } else {
                        $settings["payment"]["credit_card"][$card][$key] = $methodsCreditCard[$card][$key];
                    }
                }
            }

            $this->plugin->logs->logInfos($settings);

            return true;
        } catch (Hipay_Settings_Exception $e) {
            $this->plugin->logs->logInfos($e);
            $settings["payment"]["credit_card"] = $this->plugin->confHelper->getPaymentCreditCard();
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
        }

        return false;
    }

    /**
     * @param $settings
     * @param $methods
     * @return bool
     */
    public function saveLocalPaymentSettings(&$settings, $methods)
    {
        $this->plugin->logs->logInfos("# SaveLocalPaymentSettings");

        try {
            $this->handleErrors();

            $keySaved = array(
                "currencies",
                "countries",
                "minAmount",
                "maxAmount"
            );

            $settings = $this->plugin->confHelper->getLocalPayments();

            foreach ($settings[$methods] as $key => $value) {
                if (in_array($key, $keySaved)) {
                    $settings[$methods][$key] = $_POST["woocommerce_hipayenterprise_methods_" . $key][$methods];
                }
            }


            $this->plugin->logs->logInfos($settings);

            return true;
        } catch (Hipay_Settings_Exception $e) {
            $this->plugin->logs->logInfos($e);
            $settings = $this->plugin->confHelper->getLocalPayments();
        } catch (Exception $e) {
            $this->plugin->logs->logException($e);
        }

        return false;
    }

    /**
     * @return bool
     * @throws Hipay_Settings_Exception
     */
    private function handleErrors()
    {
        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                add_settings_error(__("HiPay"), null, $error);
            }
            $this->errors = array();

            throw new Hipay_Settings_Exception();
        }

        return true;
    }

    /**
     * @param $message
     */
    private function addError($message)
    {
        $this->errors[] = $message;
    }
}
