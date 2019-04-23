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
class WC_Payment_Token_CC_HiPay extends WC_Payment_Token
{
    protected $type = 'CC_HiPay';

    protected $extra_data = array(
        'pan' => '',
        'expiry_year' => '',
        'expiry_month' => '',
        'card_type' => '',
        'card_holder' => '',
        'payment_product' => '',
        'force_cvv' => false
    );

    public function get_display_name($deprecated = '')
    {
        $display = sprintf(
            __('%1$s %2$s (expires %3$s/%4$s)', 'hipayenterprise'),
            wc_get_credit_card_type_label($this->get_card_type()),
            $this->get_pan(),
            $this->get_expiry_month(),
            substr($this->get_expiry_year(), 2)
        );
        return $display;
    }

    protected function get_hook_prefix()
    {
        return 'woocommerce_payment_token_cc_hipay_get_';
    }

    public function validate()
    {
        if (false === parent::validate()) {
            return false;
        }

        if (!$this->get_card_holder('edit')) {
            return false;
        }

        if (!$this->get_pan('edit')) {
            return false;
        }

        if (!$this->get_expiry_year('edit')) {
            return false;
        }

        if (!$this->get_expiry_month('edit')) {
            return false;
        }

        if (!$this->get_card_type('edit')) {
            return false;
        }

        if (4 !== strlen($this->get_expiry_year('edit'))) {
            return false;
        }

        if (2 !== strlen($this->get_expiry_month('edit'))) {
            return false;
        }

        return true;
    }

    public function get_card_holder($context = 'view')
    {
        return $this->get_prop('card_holder', $context);
    }

    public function set_card_holder($card_holder)
    {
        $this->set_prop('card_holder', $card_holder);
    }

    public function get_card_type($context = 'view')
    {
        return $this->get_prop('card_type', $context);
    }

    public function set_card_type($type)
    {
        $this->set_prop('card_type', $type);
    }

    public function get_expiry_year($context = 'view')
    {
        return $this->get_prop('expiry_year', $context);
    }

    public function set_expiry_year($year)
    {
        $this->set_prop('expiry_year', $year);
    }

    public function get_expiry_month($context = 'view')
    {
        return $this->get_prop('expiry_month', $context);
    }

    public function set_expiry_month($month)
    {
        $this->set_prop('expiry_month', str_pad($month, 2, '0', STR_PAD_LEFT));
    }

    public function get_pan($context = 'view')
    {
        return $this->get_prop('pan', $context);
    }

    public function set_pan($pan)
    {
        $this->set_prop('pan', $pan);
    }

    public function get_payment_product($context = 'view')
    {
        return $this->get_prop('payment_product', $context);
    }

    public function set_payment_product($pan)
    {
        $this->set_prop('payment_product', $pan);
    }

    public function get_force_cvv($context = 'view')
    {
        return (bool)$this->get_prop('force_cvv', $context);
    }

    public function set_force_cvv($force_cvv)
    {
        $this->set_prop('force_cvv', $force_cvv);
    }

    public static function wc_get_account_saved_payment_methods_list_item_cc_hipay($item, $payment_token)
    {
        if ('cc_hipay' !== strtolower($payment_token->get_type())) {
            return $item;
        }

        $card_type = $payment_token->get_card_type();
        $item['method']['last4'] = $payment_token->get_pan();
        $item['method']['brand'] = (!empty($card_type) ? ucfirst($card_type) : esc_html__(
            'Credit card',
            'woocommerce'
        ));
        $item['expires'] = $payment_token->get_expiry_month() . '/' . substr($payment_token->get_expiry_year(), -2);

        return $item;
    }


    public function wc_get_get_saved_payment_method_option_html_hipay($html, $token)
    {

        $cvvUpdateForm = "";

        if ($token->get_force_cvv()) {
            ob_start();

            Hipay_Helper::process_template(
                'force-cvv-oc.php',
                'frontend',
                array(
                    "token" => $token
                )
            );

            $cvvUpdateForm = ob_get_contents();
            ob_end_clean();
        }

        return $html . $cvvUpdateForm;
    }
}

add_filter(
    'woocommerce_payment_methods_list_item',
    array('WC_Payment_Token_CC_HiPay', 'wc_get_account_saved_payment_methods_list_item_cc_hipay'),
    10,
    2
);

add_filter(
    'woocommerce_payment_gateway_get_saved_payment_method_option_html',
    array('WC_Payment_Token_CC_HiPay', 'wc_get_get_saved_payment_method_option_html_hipay'),
    10,
    2
);
