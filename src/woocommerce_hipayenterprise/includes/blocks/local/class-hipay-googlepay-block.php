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
 * HiPay Google Pay payment method blocks integration.
 */
final class Hipay_Googlepay_Block extends Hipay_Local_Payment_Block_Abstract
{
    /**
     * @var string
     */
    protected $name = 'hipayenterprise_googlepay';

    /**
     * @var string
     */
    protected $paymentProduct = 'googlepay';

    /**
     * @return array
     */
    protected function get_payment_config()
    {
        $config  = parent::get_payment_config();
        $account = $this->confHelper->getAccount();
        $sandbox = (bool) $this->confHelper->isSandbox();
        $env     = $sandbox ? ($account['sandbox'] ?? []) : ($account['production'] ?? []);
        $suffix  = $sandbox ? 'sandbox' : 'production';

        $customerCountry = WC()->customer ? WC()->customer->get_billing_country() : '';

        $config['isGooglePay']         = true;
        $config['apiUsernameTokenJs']  = $env['api_tokenjs_username_' . $suffix] ?? '';
        $config['apiPasswordTokenJs']  = $env['api_tokenjs_password_publickey_' . $suffix] ?? '';
        $config['environment']         = $sandbox ? 'stage' : 'production';
        $config['shopName']            = get_bloginfo('name');
        $config['currency']            = get_woocommerce_currency();
        $config['countryCode']         = !empty($customerCountry) ? $customerCountry : WC()->countries->get_base_country();

        return $config;
    }
}
