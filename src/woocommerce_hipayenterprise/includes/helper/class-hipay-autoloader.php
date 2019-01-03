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
class Hipay_Autoloader
{

    public static $classArray = array(
        WC_HIPAYENTERPRISE_PATH . 'vendor/autoload.php',
        WC_HIPAYENTERPRISE_PATH . 'class-wc-hipay-enterprise.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/data-stores/class-hipay-order-capture-data-store-cpt.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/post/class-hipay-mapping-category.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/post/class-hipay-mapping-delivery.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/post/class-hipay-order-capture.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-abstract.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-category-controller.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-delivery-controller.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-post-types.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-menus.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-capture.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-upgrade-helper.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-hipay-gateway-abstract.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-hipay-gateway-local-abstract.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-gateway-hipay.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-log.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-notification.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-order-handler.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-order-helper.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-assets.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-config.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-settings-handler.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-helper.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-helper.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-transactions.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-operations.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-api.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-helper-mapping.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/ThreeDS.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/OperatingMode.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/CaptureMode.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/SettingsField.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/interface-hipay-api-formatter.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/class-hipay-api-formatter-abstract.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/cart/class-hipay-cart-formatter.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/cart/class-hipay-delivery-formatter.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-request-formatter-abstract.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-request-formatter-abstract.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-maintenance-formatter-abstract.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-hosted-payment-formatter.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-direct-post-formatter.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/info/class-hipay-customer-billing-info-formatter.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/info/class-hipay-customer-shipping-info-formatter.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/formatter/payment-method/class-hipay-card-token-formatter.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-api-request-handler.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/exceptions/class-hipay-payment-exception.php',
        WC_HIPAYENTERPRISE_PATH . 'includes/helper/exceptions/class-hipay-settings-exception.php'
    );

    /**
     * Load all needed classes
     */
    public static function loader()
    {
        foreach (Hipay_Autoloader::$classArray as $class) {
            require_once($class);
        }

        foreach (Hipay_Autoloader::getLocalMethodsFiles() as $file) {
            require_once($file);
        }
    }

    /**
     * Get Local Methods Class name from file
     *
     * @return array
     */
    public static function getLocalMethodsNames()
    {
        $methods = array();

        foreach (Hipay_Autoloader::getLocalMethodsFiles() as $file) {
            $methods[] = Hipay_Autoloader::getClassNameFromFile($file);
        }

        return $methods;
    }

    /**
     * Get all local methods class file from directory
     *
     * @return array
     */
    private static function getLocalMethodsFiles()
    {
        $filePaths = array();

        $dir = new RecursiveDirectoryIterator(WC_HIPAYENTERPRISE_PATH . '/includes/gateways/local');
        $iterator = new RecursiveIteratorIterator($dir);
        foreach ($iterator as $file) {
            $fileName = $file->getFilename();
            if (preg_match('%\.php$%', $fileName)) {
                $filePaths[] = $file->getPathname();
            }
        }

        return $filePaths;
    }

    /**
     * Get Class name from a file
     *
     * @param $path
     * @return mixed|string
     */
    private static function getClassNameFromFile($path)
    {
        //Grab the contents of the file
        $contents = file_get_contents($path);

        //Start with a blank namespace and class
        $class = "";
        $nextStringIsClass = false;
        //Go through each token and evaluate it as necessary
        foreach (token_get_all($contents) as $token) {

            if (is_array($token) && $token[0] == T_CLASS) {
                $nextStringIsClass = true;
            }

            if (is_array($token) && $nextStringIsClass && $token[0] == T_STRING) {

                //Store the token's value as the class name
                $class = $token[1];

                //Got what we need, stop here
                break;
            }
        }

        return $class;
    }
}

// autoload loader
spl_autoload_register('Hipay_Autoloader::loader');
