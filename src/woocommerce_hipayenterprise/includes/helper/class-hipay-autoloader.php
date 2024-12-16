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

    public static $staticClasses = array(
	    'WC_HipayEnterprise' => WC_HIPAYENTERPRISE_PATH . 'class-wc-hipay-enterprise.php',
        'WC_Order_Capture_Data_Store_CPT' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/data-stores/class-hipay-order-capture-data-store-cpt.php',
	    'Hipay_Mapping_Category' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/post/class-hipay-mapping-category.php',
	    'Hipay_Mapping_Delivery' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/post/class-hipay-mapping-delivery.php',
	    'Hipay_Order_Capture' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/post/class-hipay-order-capture.php',
	    'Wc_Hipay_Admin_Assets' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-assets.php',
	    'Hipay_Admin_Capture' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-capture.php',
	    'Hipay_Admin_Menus' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-menus.php',
//	    'Hipay_Admin_Meta_Boxes' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-meta-boxes.php',
	    'Hipay_Admin_Page' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-page-abstract.php',
	    'Hipay_Admin_Plugin_Update_Handler' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-plugin-update.php',
	    'Hipay_Admin_Post_Types' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-admin-post-types.php',
        'Hipay_Mapping_Abstract' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-abstract.php',
        'Hipay_Mapping_Category_Controller' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-category-controller.php',
        'Hipay_Mapping_Delivery_Controller' => WC_HIPAYENTERPRISE_PATH . 'includes/admin/class-hipay-mapping-delivery-controller.php',

	    'Hipay_Cart_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/cart/class-hipay-cart-formatter.php',
	    'Hipay_Delivery_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/cart/class-hipay-delivery-formatter.php',
	    'Hipay_Customer_Billing_Info_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/info/class-hipay-customer-billing-info-formatter.php',
	    'Hipay_Customer_Shipping_Info_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/info/class-hipay-customer-shipping-info-formatter.php',
	    'Hipay_Card_Token_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/payment-method/class-hipay-card-token-formatter.php',
	    'Hipay_Generic_Payment_Method_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/payment-method/class-hipay-generic-payment-method-formatter.php',
	    'Hipay_Direct_Post_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-direct-post-formatter.php',
	    'Hipay_Hosted_Payment_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-hosted-payment-formatter.php',
	    'Hipay_Maintenance_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-maintenance-formatter-abstract.php',
        'Hipay_Available_Payment_Product_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-available-payment-product-formatter.php',
	    'Hipay_Order_Request_Abstract' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/request/class-hipay-order-request-abstract.php',
	    'Hipay_Account_Info_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/threeds/class-hipay-account-info-formatter.php',
	    'Hipay_Browser_Info_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/threeds/class-hipay-browser-info-formatter.php',
	    'Hipay_Merchant_Risk_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/threeds/class-hipay-merchant-risk-formatter.php',
	    'Hipay_Previous_Auth_Info_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/threeds/class-hipay-previous-auth-info-formatter.php',
	    'Hipay_Api_Formatter_Abstact' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/class-hipay-api-formatter-abstract.php',
	    'Hipay_Api_Formatter' => WC_HIPAYENTERPRISE_PATH . 'includes/formatter/interface-hipay-api-formatter.php',

	    'Gateway_Hipay' => WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-gateway-hipay.php',
	    'Hipay_Gateway_Abstract' => WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-hipay-gateway-abstract.php',
	    'Hipay_Gateway_Local_Abstract' => WC_HIPAYENTERPRISE_PATH . 'includes/gateways/class-hipay-gateway-local-abstract.php',

	    'CaptureMode' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/CaptureMode.php',
	    'CardPaymentProduct' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/CardPaymentProduct.php',
	    'OperatingMode' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/OperatingMode.php',
	    'SettingsField' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/SettingsField.php',
	    'ThreeDS' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/enums/ThreeDS.php',
	    'Hipay_Payment_Exception' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/exceptions/class-hipay-payment-exception.php',
	    'Hipay_Settings_Exception' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/exceptions/class-hipay-settings-exception.php',

	    'Hipay_Api' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-api.php',
	    'Hipay_Api_Request_Handler' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-api-request-handler.php',
	    'Hipay_Config' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-config.php',
	    'Hipay_Helper' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-helper.php',
	    'Hipay_Helper_Mapping' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-helper-mapping.php',
	    'Hipay_Log' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-log.php',
	    'Hipay_Notification' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-notification.php',
	    'Hipay_Operations_Helper' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-operations.php',
	    'Hipay_Order_Handler' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-order-handler.php',
	    'Hipay_Order_Helper' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-order-helper.php',
	    'WC_Payment_Token_CC_HiPay' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-payment-token-cc-hipay.php',
	    'Hipay_Settings_Handler' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-settings-handler.php',
	    'Hipay_Threeds_Helper' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-threeds-helper.php',
	    'Hipay_Token_Helper' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-token-helper.php',
	    'Hipay_Transactions_Helper' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-transactions.php',
	    'Hipay_Upgrade_Helper' => WC_HIPAYENTERPRISE_PATH . 'includes/helper/class-hipay-upgrade-helper.php',
    );

	public static $localMethodClasses;


	/**
     * Load all needed classes
     */
    public static function loader($class)
    {
		if(!empty(Hipay_Autoloader::$staticClasses[$class])) {
			require_once( Hipay_Autoloader::$staticClasses[ $class ] );
		} else if(!empty(Hipay_Autoloader::$localMethodClasses[$class])) {
			require_once( Hipay_Autoloader::$localMethodClasses[ $class ] );
		}

    }

    /**
     * Get Local Methods Class name from file
     *
     * @return array
     */
    public static function getLocalMethodsNames()
    {
		$methodNames = array_keys(Hipay_Autoloader::$localMethodClasses);
		sort($methodNames);
        return $methodNames;
    }

    /**
     * Get all local methods class file from directory
     *
     * @return array
     */
    public static function getLocalMethodsClasses()
    {
        $filePaths = array();

        $dir = new RecursiveDirectoryIterator(WC_HIPAYENTERPRISE_PATH . '/includes/gateways/local');
        $iterator = new RecursiveIteratorIterator($dir);
        foreach ($iterator as $file) {
            $fileName = $file->getFilename();
            if (preg_match('%^class-[a-z|1-9|-]*\.php%', $fileName)) {
                $filePath = $file->getPathname();
				$className = Hipay_Autoloader::getClassNameFromFile($filePath);
                $filePaths[$className] = $filePath;
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

require_once(WC_HIPAYENTERPRISE_PATH . 'vendor/autoload.php');
if(empty(Hipay_Autoloader::$localMethodClasses)){
	Hipay_Autoloader::$localMethodClasses = Hipay_Autoloader::getLocalMethodsClasses();
}

// autoload loader
spl_autoload_register('Hipay_Autoloader::loader');
