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
    // Exit if accessed directly
}

/**
 *
 * @author      HiPay <support.tpp@hipay.com>
 * @copyright   Copyright (c) 2018 - HiPay
 * @license     https://github.com/hipay/hipay-enterprise-sdk-woocommerce/blob/master/LICENSE.md
 * @link    https://github.com/hipay/hipay-enterprise-sdk-woocommerce
 */
class Hipay_Gateway_Alma_Abstract extends Hipay_Gateway_Local_Abstract
{
    /**
     * Default minimum and maximum amounts for Alma payments
     */
    private static $ALMA_DEFAULT_MIN_AMOUNT = 50;
    private static $ALMA_DEFAULT_MAX_AMOUNT = 2000;

    public function __construct()
    {
        parent::__construct();
    }

    public function is_available()
    {
        if ($this->enabled === 'no') {
            return false;
        }

        $total = WC()->cart ? WC()->cart->total : 0;

        return $this->getMinMaxByPaymentProduct($total, $this->paymentProduct);
    }

    /**
     * Get Alma payment products min and max amount limits
     *
     * @return array
     */
    protected function getAlmaMaxMinAmount()
    {
        try {

            $almaProducts = [
                'alma-3x' => ['min' => self::$ALMA_DEFAULT_MIN_AMOUNT, 'max' => self::$ALMA_DEFAULT_MAX_AMOUNT],
                'alma-4x' => ['min' => self::$ALMA_DEFAULT_MIN_AMOUNT, 'max' => self::$ALMA_DEFAULT_MAX_AMOUNT]
            ];

            foreach (array_keys($almaProducts) as $productCode) {
                $products = $this->getCachedPaymentProducts($productCode);

                if (!empty($products)) {
                    foreach ($products as $product) {
                        if ($product['code'] === $productCode) {
                            $installments = substr($product['code'], -2, 1);

                            if (isset($product['options'])) {
                                $minKey = "basketAmountMin{$installments}x";
                                $maxKey = "basketAmountMax{$installments}x";

                                if (isset($product['options'][$minKey], $product['options'][$maxKey])) {
                                    $almaProducts[$productCode] = [
                                        'min' => (float)$product['options'][$minKey],
                                        'max' => (float)$product['options'][$maxKey]
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            return $almaProducts;

        } catch (Exception $e) {
            $this->logs->logException($e);
            return [
                'alma-3x' => ['min' => self::$ALMA_DEFAULT_MIN_AMOUNT, 'max' => self::$ALMA_DEFAULT_MAX_AMOUNT],
                'alma-4x' => ['min' => self::$ALMA_DEFAULT_MIN_AMOUNT, 'max' => self::$ALMA_DEFAULT_MAX_AMOUNT]
            ];
        }
    }

    /**
     * Generate HTML for local payment methods settings
     *
     * @return string HTML content
     */
    public function generate_methods_local_payments_settings_html()
    {
        ob_start();
        $this->process_template(
            'admin-paymentlocal-settings.php',
            'admin',
            [
                'configurationPaymentMethod' => $this->confHelper->getLocalPayment($this->paymentProduct),
                'method' => $this->paymentProduct,
                'almaProducts' => $this->getAlmaMaxMinAmount()
            ]
        );

        return ob_get_clean();
    }
}
