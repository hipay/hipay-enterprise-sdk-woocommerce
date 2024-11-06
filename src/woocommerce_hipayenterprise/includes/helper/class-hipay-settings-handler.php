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

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Settings_Handler
{
    /**
     * @var Hipay_Gateway_Abstract
     */
    protected $plugin;

    /**
     * @var array
     */
    protected $errors;

    /**
     * Hipay_Settings_Handler constructor.
     * @param Hipay_Gateway_Abstract $plugin
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
            if (!empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_account_sandbox_username'))
                && empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_account_sandbox_password'))
            ) {
                $this->addError(
                    __("If sandbox api username is filled sandbox api password is mandatory", "hipayenterprise")
                );
            }

            $settings["account"]["global"] = array(
                "sandbox_mode" => sanitize_title(Hipay_Helper::getPostData('woocommerce_hipayenterprise_sandbox'))
            );

            if (!empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_account_sandbox_tokenjs_username'))
                && empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_account_sandbox_password_publickey'))
            ) {
                $this->addError(
                    __(
                        "If sandbox api TokenJS username is filled sandbox api TokenJS password is mandatory",
                        "hipayenterprise"
                    )
                );
            }

            if (!empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_account_production_username'))
                && empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_account_production_password'))
            ) {
                $this->addError(
                    __("If production api username is filled production api password is mandatory", "hipayenterprise")
                );
            }

            if (!empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_account_production_tokenjs_username'))
                && empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_account_production_password_publickey'))
            ) {
                $this->addError(
                    __(
                        "If production api TokenJS username is filled production api TokenJS password is mandatory",
                        "hipayenterprise"
                    )
                );
            }

            $this->handleErrors();

            $settings["account"]["global"] = array(
                "sandbox_mode" => Hipay_Helper::getPostData('woocommerce_hipayenterprise_sandbox')
            );

            $settings["account"]["sandbox"] = array(
                "api_username_sandbox" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_sandbox_username'
                ),
                "api_password_sandbox" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_sandbox_password'
                ),
                "api_secret_passphrase_sandbox" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_sandbox_secret_passphrase'
                ),
                "api_tokenjs_username_sandbox" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_sandbox_tokenjs_username'
                ),
                "api_tokenjs_password_publickey_sandbox" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_sandbox_password_publickey'
                )
            );

            $settings["account"]["production"] = array(
                "api_username_production" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_production_username'
                ),
                "api_password_production" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_production_password'
                ),
                "api_secret_passphrase_production" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_production_secret_passphrase'
                ),
                "api_tokenjs_username_production" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_production_tokenjs_username'
                ),
                "api_tokenjs_password_publickey_production" => Hipay_Helper::getPostData(
                    'woocommerce_hipayenterprise_account_production_password_publickey'
                )
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

            $settings["payment"]["global"] = $this->plugin->confHelper->getPaymentGlobal();

            $configFormData = array(
                'operating_mode' => sanitize_title(Hipay_Helper::getPostData('operating_mode')),
                'capture_mode' => sanitize_title(Hipay_Helper::getPostData('capture_mode')),
                'activate_3d_secure' => sanitize_title(Hipay_Helper::getPostData('activate_3d_secure')),
                'sdk_js_url' => esc_url_raw(Hipay_Helper::getPostData('sdk_js_url')),
                'log_infos' => sanitize_title(Hipay_Helper::getPostData('log_infos')),
                'card_token' => sanitize_title(Hipay_Helper::getPostData('card_token')),
                'activate_basket' => sanitize_title(Hipay_Helper::getPostData('activate_basket')),
                'css_url' => esc_url_raw(Hipay_Helper::getPostData('css_url')),
                'display_hosted_page' => sanitize_title(Hipay_Helper::getPostData('display_hosted_page')),
                'display_cancel_button' => sanitize_title(Hipay_Helper::getPostData('display_cancel_button')),
                'display_card_selector' => sanitize_title(Hipay_Helper::getPostData('display_card_selector')),
                'send_url_notification' => sanitize_title(Hipay_Helper::getPostData('send_url_notification')),
                'skip_onhold' => sanitize_title(Hipay_Helper::getPostData('skip_onhold')),
                "hosted_fields_style" => array(
                    "base" => array(
                        "color" => Hipay_Helper::getPostData('color'),
                        "fontFamily" => Hipay_Helper::getPostData('fontFamily'),
                        "fontSize" => Hipay_Helper::getPostData('fontSize'),
                        "fontWeight" => Hipay_Helper::getPostData('fontWeight'),
                        "placeholderColor" => Hipay_Helper::getPostData('placeholderColor'),
                        "caretColor" => Hipay_Helper::getPostData('caretColor'),
                        "iconColor" => Hipay_Helper::getPostData('iconColor'),
                    )
                )
            );

            $settings["payment"]["global"] = array_replace($settings["payment"]["global"], $configFormData);

            $settings["payment"]["global"]['ccDisplayName'] = array_replace(
                $settings["payment"]["global"]['ccDisplayName'],
                Hipay_Helper::getPostData('ccDisplayName')
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
            if (!empty(Hipay_Helper::getPostData('woocommerce_hipayenterprise_fraud_copy_to'))
                && empty(sanitize_email(Hipay_Helper::getPostData('woocommerce_hipayenterprise_fraud_copy_to')))
            ) {
                $this->addError(__('Email should be valid', "hipayenterprise"));
            }
            $this->handleErrors();

            $settings['fraud']['copy_to'] = sanitize_email(
                Hipay_Helper::getPostData('woocommerce_hipayenterprise_fraud_copy_to')
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
        $this->plugin->logs->logInfos("# SaveCreditCardInformation");

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
                        $settings["payment"][Hipay_Config::KEY_CREDIT_CARD][$card][$key] = Hipay_Helper::getPostData(
                            "woocommerce_hipayenterprise_methods_creditCard_" . $key . "_" . $card
                        );
                    } else {
                        $settings["payment"][Hipay_Config::KEY_CREDIT_CARD][$card][$key] = $methodsCreditCard[$card][$key];
                    }
                }
            }

            $this->plugin->logs->logInfos($settings);

            return true;
        } catch (Hipay_Settings_Exception $e) {
            $this->plugin->logs->logInfos($e);
            $settings["payment"][Hipay_Config::KEY_CREDIT_CARD] = $this->plugin->confHelper->getPaymentCreditCard();
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
                "maxAmount",
                "displayName",
                "orderExpirationTime",
                "merchantPromotion",
                "merchantId",
                "buttonShape",
                "buttonLabel",
                "buttonColor",
                "buttonHeight",
                "bnpl"
            );

            $settings = $this->plugin->confHelper->getLocalPayments();

            foreach ($settings[$methods] as $key => $value) {
                if (in_array($key, $keySaved)) {
                    $value = Hipay_Helper::getPostData(
                        "woocommerce_hipayenterprise_methods_" . $key . "_" . $methods
                    );

                    if ($key == 'displayName') {
                        $lang = Hipay_Helper::getLanguage();
                        if (isset($value[$lang])) {
                            $settings[$methods][$key][$lang] = $value[$lang];
                        }
                    } else {
                        $settings[$methods][$key] = $value;
                    }
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
                $this->plugin->add_error($error);
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
