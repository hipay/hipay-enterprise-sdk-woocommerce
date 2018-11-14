<?php
if (! defined('ABSPATH')) {
    exit;
}

class WC_HipayEnterprise_Settings_Handler
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
     * @param $settings
     * @return bool
     */
    public function savePaymentGlobal(&$settings)
    {
        $this->plugin->logs->logInfos("# SavePaymentGlobal");

        try {
            $settings["payment"]["global"] = array(
                'operating_mode' => sanitize_title($_POST['operating_mode']),
                'capture_mode' => sanitize_title($_POST['capture_mode']),
                'activate_3d_secure' => sanitize_title($_POST['activate_3d_secure']),
                'log_infos' => sanitize_title($_POST['log_infos']),
                'card_token' => sanitize_title($_POST['card_token']),
                'activate_basket' => sanitize_title($_POST['activate_basket']),
                'regenerate_cart_on_decline' => sanitize_title($_POST['regenerate_cart_on_decline']),
                'css_url' => sanitize_title($_POST['css_url']),
                'display_hosted_page' => sanitize_title($_POST['display_hosted_page']),
                'display_card_selector' => sanitize_title($_POST['display_card_selector']),
            );

            $this->plugin->logs->logInfos($settings);
            return true;
        } catch (Exception $e) {
            $this->plugin->log->logException($e);
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
            $settings['fraud']['woocommerce_hipayenterprise_fraud_copy_to'] = sanitize_email($_POST['woocommerce_hipayenterprise_fraud_copy_to']);
            $settings['fraud']['woocommerce_hipayenterprise_fraud_copy_method'] = sanitize_title($_POST['woocommerce_hipayenterprise_fraud_copy_method']);

            $this->plugin->logs->logInfos($settings);
            return true;
        } catch (Exception $e) {
            $this->plugin->log->logException($e);
        }

        return false;
    }

    public function saveCreditCardSettings(&$settings)
    {
        $this->plugin->logs->logInfos("# SaveCreditCardInformations");

        try {
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
                        $settings["payment"]["credit_card"][$card][$key] = $_POST["woocommerce_hipayenterprise_methods_creditCard_" . $key][$card];
                    } else {
                        $settings["payment"]["credit_card"][$card][$key] = $methodsCreditCard[$card][$key];
                    }
                }
            }

            $this->plugin->logs->logInfos($settings);

            return true;
        } catch (Exception $e) {
            $this->plugin->log->logException($e);
        }

        return false;
    }

    public function saveLocalPaymentSettings(&$settings)
    {
        $this->plugin->logs->logInfos("# SaveLocalPaymentSettings");

        try {
            $keySaved = array(
                "activated",
                "currencies",
                "countries",
                "minAmount",
                "maxAmount"
            );

            $methodsLocalPayment= $this->plugin->confHelper->getLocalPayment();
            foreach ($methodsLocalPayment as $card => $conf) {
                foreach ($conf as $key => $value) {
                    if (in_array($key, $keySaved)) {
                        $settings["payment"]["local_payment"][$card][$key] = $_POST["woocommerce_hipayenterprise_methods_local_" . $key][$card];
                    } else {
                        $settings["payment"]["local_payment"][$card][$key] = $methodsLocalPayment[$card][$key];
                    }
                }
            }

            $this->plugin->logs->logInfos($settings);

            return true;
        } catch (Exception $e) {
            $this->plugin->log->logException($e);
        }

        return false;
    }
}
