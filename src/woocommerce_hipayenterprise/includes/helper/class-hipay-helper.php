<?php
if (!defined('ABSPATH')) {
    exit;
}

use \HiPay\Fullservice\Helper\Signature;
use \HiPay\Fullservice\HTTP\Configuration\Configuration;

class Hipay_Helper
{
    protected $plugin;

    /**
     * Check if cart amount is authorized
     *
     * @param array $conf
     * @param double $totals
     * @return bool
     */
    public static function isInAuthorizedAmount($conf, $total)
    {
        $minAmount = $conf["minAmount"]["EUR"];
        $maxAmount = $conf["maxAmount"]["EUR"];

        if ((($maxAmount != 0 && $total > $maxAmount) || (!empty($maxAmount)))
            || ($minAmount != 0 && $total < $minAmount)) {
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
     * @return array
     */
    public static function getActivatedPaymentByCountryAndCurrency(
        $plugin,
        $paymentMethodType,
        $country,
        $currency,
        $orderTotal = 1
    ) {
        $activatedPayment = array();
        foreach ($plugin->confHelper->getPayment()[$paymentMethodType] as $name => $conf) {
            if ($conf["activated"]
                && in_array($currency, $conf["currencies"])
                && in_array($country, $conf["countries"])
                && Hipay_Helper::isInAuthorizedAmount($conf, $orderTotal)) {
                $activatedPayment[$name] = $conf;
            }
        }
        return $activatedPayment;
    }

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

}
