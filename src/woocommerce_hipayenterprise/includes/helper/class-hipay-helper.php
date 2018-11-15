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
     * @param array $totals
     * @return bool
     */
    public static function isInAuthorizedAmount($conf, $totals)
    {
        $minAmount = $conf["minAmount"]["EUR"];
        $maxAmount = $conf["maxAmount"]["EUR"];
        $totalCart = $totals["total"];

        if ((($maxAmount != 0 && $totalCart > $maxAmount) || (!empty($maxAmount)))
            || ($minAmount != 0 && $totalCart < $minAmount)) {
            return false;
        }
        return true;
    }
}
