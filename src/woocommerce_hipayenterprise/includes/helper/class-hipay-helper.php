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
class Hipay_Helper
{
    /**
     * Check if order amount is between minAmount and maxAmount
     *
     * @param $conf
     * @param $total
     * @return bool
     */
    public static function isInAuthorizedAmount($conf, $total)
    {
        $minAmount = $conf["minAmount"]["EUR"];
        $maxAmount = $conf["maxAmount"]["EUR"];

        if ((!empty($maxAmount) && $maxAmount != 0 && $total > $maxAmount)
            || (!empty($minAmount) && $minAmount != 0 && $total < $minAmount)) {
            return false;
        }

        return true;
    }

    /**
     * @param $plugin
     * @param $paymentMethodType
     * @param $country
     * @param $currency
     * @param int $orderTotal
     * @param bool $allConfiguration
     * @return array
     */
    public static function getActivatedPaymentByCountryAndCurrency(
        $plugin,
        $paymentMethodType,
        $country,
        $currency,
        $orderTotal = 1,
        $allConfiguration = true
    ) {
        $activatedPayment = array();
        if ($paymentMethodType == Gateway_Hipay::CREDIT_CARD_PAYMENT_PRODUCT) {
            foreach ($plugin->confHelper->getPayment()[$paymentMethodType] as $name => $conf) {
                if ($conf["activated"] && self::isPaymentMethodAuthorized($conf, $currency, $country, $orderTotal)) {
                    if ($allConfiguration) {
                        $activatedPayment[$name] = $conf;
                    } else {
                        $activatedPayment[] = $name;
                    }
                }
            }
        } else {
            $conf = $plugin->confHelper->getPayment()[Hipay_Config::KEY_LOCAL_PAYMENT][$paymentMethodType];
            if (self::isPaymentMethodAuthorized($conf, $currency, $country, $orderTotal)) {
                $activatedPayment[$paymentMethodType] = $conf;
            }
        }
        return $activatedPayment;
    }

    /**
     * @param $conf
     * @param $currency
     * @param $country
     * @param $orderTotal
     * @return bool
     */
    private static function isPaymentMethodAuthorized($conf, $currency, $country, $orderTotal)
    {
        return (empty($conf["currencies"]) || in_array($currency, $conf["currencies"]))
            && (empty($conf["countries"]) || in_array($country, $conf["countries"]))
            && Hipay_Helper::isInAuthorizedAmount($conf, $orderTotal);
    }

    /**
     * Check notification signature
     * @param $plugin
     * @return bool
     */
    public static function checkSignature($plugin)
    {
        if ($plugin->confHelper->isSandbox()) {
            $passphrase = $plugin->confHelper->getAccount()["sandbox"]["api_secret_passphrase_sandbox"];
            $environment = Configuration::API_ENV_STAGE;
        } else {
            $passphrase = $plugin->confHelper->getAccount()["production"]["api_secret_passphrase_production"];
            $environment = Configuration::API_ENV_PRODUCTION;
        }

        $hashAlgorithm = $plugin->confHelper->getAccount()["hash_algorithm"][$environment];

        $isValidSignature = Signature::isValidHttpSignature($passphrase, $hashAlgorithm);

        if (!$isValidSignature && !Signature::isSameHashAlgorithm($passphrase, $hashAlgorithm)) {
            $plugin->logs->logInfos(
                "# Signature is not valid. Hash is not the same. Try to synchronize for {$environment}"
            );

            try {
                if (self::existCredentialForPlateform($plugin, $environment)) {
                    $hashAlgorithmAccount = $plugin->getApi()->getSecuritySettings($environment);
                    if ($hashAlgorithm != $hashAlgorithmAccount->getHashingAlgorithm()) {
                        $configHash = $plugin->confHelper->getHashAlgorithm();
                        $configHash[$environment] = $hashAlgorithmAccount->getHashingAlgorithm();
                        $plugin->confHelper->setHashAlgorithm($configHash);
                        $plugin->logs->logInfos("# Hash Algorithm is now synced for {$environment}");
                        $isValidSignature = Signature::isValidHttpSignature(
                            $passphrase,
                            $hashAlgorithmAccount->getHashingAlgorithm()
                        );
                    }
                }
            } catch (Exception $e) {
                $plugin->logs->logErrors(sprintf("Update hash failed for %s", $environment));
            }
        }

        return $isValidSignature;
    }

    /**
     * Test if credentials are filled for plateform ( If no exists then no synchronization )
     *
     * @param $plugin
     * @param $platform
     * @return bool True if Credentials are filled
     */
    public static function existCredentialForPlateform($plugin, $platform)
    {
        switch ($platform) {
            case Configuration::API_ENV_PRODUCTION:
                $exist = !empty($plugin->confHelper->getAccountProduction()["api_username_production"]);
                break;
            case Configuration::API_ENV_STAGE:
                $exist = !empty($plugin->confHelper->getAccountSandbox()["api_username_sandbox"]);
                break;
            default:
                $exist = false;
                break;
        }

        return $exist;
    }


    /**
     * Send email to admin and BCC when fraud transaction is detected
     *
     * @param $orderId
     * @param $plugin
     */
    public static function sendEmailFraud($orderId, $plugin)
    {
        $subject = sprintf(
            __('A payment transaction is awaiting validation for the order %s', "hipayenterprise"),
            $orderId
        );
        $urlAdmin = admin_url('post.php?post=' . $orderId . '&action=edit');
        $listEmails[] = get_option('admin_email');

        $settingsFraud = $plugin->confHelper->getFraud();
        if ($settingsFraud['copy_to']) {
            $listEmails[] = $settingsFraud['copy_to'];
        }

        foreach ($listEmails as $email) {
            self::sendEmail(
                $email,
                $subject,
                sprintf(
                    __(
                        'You can accept or decline this transaction by visiting the administration of your store. <a href="%s">here</a>',
                        "hipayenterprise"
                    ),
                    $urlAdmin
                )
            );
        }
    }

    /**
     * Send Email with default wordpress template
     *
     * @param $to
     * @param $subject
     * @param $message
     */
    public static function sendEmail($to, $subject, $message)
    {
        $mailer = WC()->mailer();
        $mailer->send($to, $subject, $mailer->wrap_message($subject, $message));
    }


    /**
     * Format and Add Message about transaction
     *
     * @param $transaction
     * @return string
     */
    public static function formatOrderData($transaction)
    {
        $message = "";
        switch ($transaction->getStatus()) {
            case TransactionStatus::CAPTURED: //118
            case TransactionStatus::CAPTURE_REQUESTED: //117
                $message .= __('Registered notification from HiPay about captured amount of ', "hipayenterprise") .
                    $transaction->getCapturedAmount() .
                    "\n";
                break;
            case TransactionStatus::REFUND_REQUESTED: //124
            case TransactionStatus::REFUNDED: //125
                $message .= __('Registered notification from HiPay about refunded amount of ', 'hipayenterprise') .
                    $transaction->getRefundedAmount() .
                    "\n";
                break;
            case TransactionStatus::AUTHORIZATION_CANCELLATION_REQUESTED: //175
                $message .= __('Transaction cancellation requested', 'hipayenterprise') . "\n";
                break;
            case TransactionStatus::CANCELLED: //115
                $message .= __('Transaction cancelled', 'hipayenterprise') . "\n";
                break;
            default:
                $message .= __('Registered notification ', "hipayenterprise") . $transaction->getStatus() .
                    "\n";
                break;
        }

        $message .= __('Order total amount :', "hipayenterprise") . $transaction->getAuthorizedAmount() . "\n";
        $message .= "\n";
        $message .= __('Transaction ID: ', "hipayenterprise") . $transaction->getTransactionReference() . "\n";
        $message .= __('HiPay status: ', "hipayenterprise") . $transaction->getStatus() . "\n";


        return $message;
    }

    /**
     * Get HTTP POST data
     *
     * @param $index
     * @param mixed $default
     * @return mixed
     */
    public static function getPostData($index, $default = null)
    {
        if (isset($_POST[$index])) {
            return wc_clean(wp_unslash($_POST[$index]));
        }

        // Check for blocks payment data (hipay_* format)
        // For credit card: card-* → hipay_*
        if (strpos($index, 'card-') === 0) {
            $blocks_key = str_replace('card-', 'hipay_', $index);
            if (isset($_POST[$blocks_key])) {
                return wc_clean(wp_unslash($_POST[$blocks_key]));
            }
        }

        // For local payments: paymentmethod-field → hipay_field
        if (preg_match('/^([a-z_]+)-(.+)$/', $index, $matches)) {
            $field_name = $matches[2];
            $blocks_key = 'hipay_' . $field_name;
            if (isset($_POST[$blocks_key])) {
                return wc_clean(wp_unslash($_POST[$blocks_key]));
            }
        }

        return $default;
    }


    /**
     * Get Language
     *
     * @return string
     */
    public static function getLanguage()
    {
        return substr(apply_filters('hipay_locale', get_locale()), 0, 2);
    }

    /**
     * @param $template
     * @param array $args
     */
    public static function process_template($template, $type, $args = array())
    {
        extract($args);
        $file = WC_HIPAYENTERPRISE_PATH . 'includes/' . $type . '/template/' . $template;
        include $file;
    }

    /**
     * @param $needles
     * @param $haystack
     * @return bool
     */
    public static function allArrayKeyExists($needles, $haystack)
    {

        foreach ($needles as $needle) {
            if (!array_key_exists($needle, $haystack)) {
                return false;
            }
        }

        return true;
    }
}
