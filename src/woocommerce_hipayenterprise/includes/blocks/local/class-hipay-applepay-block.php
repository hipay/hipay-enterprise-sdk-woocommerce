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
 * HiPay Apple Pay payment method blocks integration.
 */
final class Hipay_Applepay_Block extends Hipay_Local_Payment_Block_Abstract
{
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'hipayenterprise_applepay';

    /**
     * Payment product identifier.
     *
     * @var string
     */
    protected $paymentProduct = 'applepay';

    /**
     * Get Apple Pay-specific payment configuration.
     *
     * @return array
     */
    protected function get_payment_config()
    {
        $config      = parent::get_payment_config();
        $account     = $this->confHelper->getAccount();
        $credentials = Hipay_Helper::getApplePayTokenJsCredentials($account, $this->confHelper->isSandbox());
        $apiUsername = $credentials['username'];
        $apiPassword = $credentials['password'];

        $methodConf      = $this->confHelper->getLocalPayment($this->paymentProduct) ?: [];
        $customerCountry = WC()->customer ? WC()->customer->get_billing_country() : '';
        $countryCode     = !empty($customerCountry) ? $customerCountry : WC()->countries->get_base_country();

        $config['isApplePay']        = true;
        $config['apiUsernameTokenJs'] = $apiUsername;
        $config['apiPasswordTokenJs'] = $apiPassword;
        $config['buttonType']         = $methodConf['buttonType'] ?? 'plain';
        $config['buttonStyle']        = $methodConf['buttonStyle'] ?? 'white';
        $config['shopName']           = get_bloginfo('name');
        $config['currency']           = get_woocommerce_currency();
        $config['countryCode']        = $countryCode;

        return $config;
    }
}
