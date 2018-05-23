<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles Giropay payment method.
 * @extends WC_HipayEnterprise
 * @since 1.0.0
 */
class WC_HipayEnterprise_LocalPayments_Giropay extends WC_HipayEnterprise {

	
	public function __construct() {

		global $woocommerce;
		global $wpdb;

		$this->payment_code			= 'giropay';		
		$this->id                   = 'hipayenterprise_giropay';
		$this->method_title         = __('Giropay','hipayenterprise');
		$this->supports             = array('products',	'refunds');

		$this->init_form_fields();
		$this->init_settings();

		$this->method_details 		= get_option( 'woocommerce_hipayenterprise_methods',
			array(
				'woocommerce_hipayenterprise_methods_capture'  				=> $this->get_option( 'woocommerce_hipayenterprise_methods_capture' ),
				'woocommerce_hipayenterprise_methods_3ds' 					=> $this->get_option( 'woocommerce_hipayenterprise_methods_3ds' ),
				'woocommerce_hipayenterprise_methods_mode' 					=> $this->get_option( 'woocommerce_hipayenterprise_methods_mode' ),
				'woocommerce_hipayenterprise_methods_hosted_mode' 			=> $this->get_option( 'woocommerce_hipayenterprise_methods_hosted_mode' ),
				'woocommerce_hipayenterprise_methods_hosted_css' 			=> $this->get_option( 'woocommerce_hipayenterprise_methods_hosted_css' ),
				'woocommerce_hipayenterprise_methods_hosted_card_selector'	=> $this->get_option( 'woocommerce_hipayenterprise_methods_hosted_card_selector' ),
				'woocommerce_hipayenterprise_methods_oneclick'				=> $this->get_option( 'woocommerce_hipayenterprise_methods_oneclick' ),
				'woocommerce_hipayenterprise_methods_cart_sending'			=> $this->get_option( 'woocommerce_hipayenterprise_methods_cart_sending' ),
				'woocommerce_hipayenterprise_methods_keep_cart_onfail'		=> $this->get_option( 'woocommerce_hipayenterprise_methods_keep_cart_onfail' ),
				'woocommerce_hipayenterprise_methods_log_info'				=> $this->get_option( 'woocommerce_hipayenterprise_methods_log_info' ),
				'woocommerce_hipayenterprise_methods_payments' 				=> $this->get_option( 'woocommerce_hipayenterprise_methods_payments' ),
			)
		);

		$this->method_details["woocommerce_hipayenterprise_methods_payments"] = str_replace("\'", "'", $this->method_details["woocommerce_hipayenterprise_methods_payments"]);
		$woocommerce_hipayenterprise_methods_payments_json = json_decode($this->method_details["woocommerce_hipayenterprise_methods_payments"]);
		
		foreach ($woocommerce_hipayenterprise_methods_payments_json as $key => $value) {
			if ($value->key == $this->payment_code ) $this->method = new HipayEnterprisePaymentMethodClass($value);	
		}

		$this->title                = __('Giropay','hipayenterprise');
		//$this->enabled              = $this->method->get_is_active();

	}




}
