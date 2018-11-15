<?php
if (! defined('ABSPATH')) {
    exit;
}

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
        foreach ($plugin->settingsHipay["payment"][$paymentMethodType] as $name => $conf) {
            if ($conf["activated"]
                && in_array($currency, $conf["currencies"])
                && in_array($country, $conf["countries"])
                && Hipay_Helper::isInAuthorizedAmount($conf, $orderTotal)) {
                $activatedPayment[$name] = $conf;
            }
        }
        return $activatedPayment;
    }
}
