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
class Hipay_Sdd extends Hipay_Gateway_Local_Abstract
{

    public function __construct()
    {
        $this->id = 'hipayenterprise_sdd';
        $this->paymentProduct = 'sdd';
        $this->method_title = __('HiPay Enterprise SEPA Direct Debit', "hipayenterprise");
        $this->title = __('SEPA Direct Debit', "hipayenterprise");
        $this->method_description = __('SEPA Direct Debit', "hipayenterprise");

        parent::__construct();
    }

    private static function fakeStringTranslations()
    {
        $strings = array(
            __("Gender", "hipayenterprise"),
            __("Firstname", "hipayenterprise"),
            __("Lastname", "hipayenterprise"),
            __("Bank name", "hipayenterprise"),
            __("Bank name", "hipayenterprise"),
            __("IBAN", "hipayenterprise"),
            __("BIC", "hipayenterprise"),
        );
    }
}
