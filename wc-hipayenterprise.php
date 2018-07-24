<?php
/*
Plugin Name: WooCommerce HiPay Enterprise
Plugin URI: https://hipay.com/en/payment-solution-enterprise
Description: WooCommerce Plugin for Hipay Enterprise.
Version: 1.0.0
Text Domain: hipayenterprise
Author: Hi-Pay Portugal
Author URI: https://www.hipaycomprafacil.com
*/

add_action('plugins_loaded', 'woocommerce_hipayenterprise_init', 0);

function woocommerce_hipayenterprise_init() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }

    define( 'WC_HIPAYENTERPRISE_VERSION', '1.0.0' );

	class WC_HipayEnterprise extends WC_Payment_Gateway  {
		

		public function __construct() {

			global $woocommerce;
			global $wpdb;

			$this->id 												= 'hipayenterprise';
			$this->supports           								= array(	'products',	'refunds',	);
			$this->woocommerce_version								= $woocommerce->version;
			$plugin_data 											= get_plugin_data( __FILE__ );
    		$this->plugin_version 									= $plugin_data['Version'];

			load_plugin_textdomain( $this->id, false, basename( dirname( __FILE__ ) ) . '/languages' ); 
			include_once( plugin_dir_path( __FILE__ ) . 'includes/payment_methods.php' );
			include_once( plugin_dir_path( __FILE__ ) . 'includes/base_config.php' );

			$this->plugin_table 									= $wpdb->prefix . 'woocommerce_hipayenterprise';
			$this->plugin_table_logs 								= $wpdb->prefix . 'woocommerce_hipayenterprise_logs';
			$this->plugin_table_token								= $wpdb->prefix . 'woocommerce_hipayenterprise_token';
			$this->has_fields 										= true;
			$this->method_title     								= __('HiPay Enterprise', $this->id );
			$this->method_description 								= __('Local and international payments using Hipay Enterprise.','hipayenterprise');

			$this->init_form_fields();
			$this->init_settings();

			$this->sandbox 											= $this->get_option('sandbox');

			$this->account_production_private_username 				= $this->get_option('account_production_private_username');
			$this->account_production_private_password 				= $this->get_option('account_production_private_password');
			$this->account_production_private_passphrase 			= $this->get_option('account_production_private_passphrase');
			$this->account_production_tokenization_username 		= $this->get_option('account_production_tokenization_username');
			$this->account_production_tokenization_password 		= $this->get_option('account_production_tokenization_password');
			$this->account_production_moto_username 				= $this->get_option('account_production_moto_username');
			$this->account_production_moto_password 				= $this->get_option('account_production_moto_password');
			$this->account_production_moto_passphrase 				= $this->get_option('account_production_moto_passphrase');
			$this->account_test_private_username 					= $this->get_option('account_test_private_username');
			$this->account_test_private_password 					= $this->get_option('account_test_private_password');
			$this->account_test_private_passphrase 					= $this->get_option('account_test_private_passphrase');
			$this->account_test_tokenization_username 				= $this->get_option('account_test_tokenization_username');
			$this->account_test_tokenization_password 				= $this->get_option('account_test_tokenization_password');
			$this->account_test_moto_username 						= $this->get_option('account_test_moto_username');
			$this->account_test_moto_password 						= $this->get_option('account_test_moto_password');
			$this->account_test_moto_passphrase 					= $this->get_option('account_test_moto_passphrase');
			$this->account_proxy_host 								= $this->get_option('account_proxy_host');
			$this->account_proxy_port 								= $this->get_option('account_proxy_port');
			$this->account_proxy_username 							= $this->get_option('account_proxy_username');
			$this->account_proxy_password 							= $this->get_option('account_proxy_password');

			$this->payment_image 		= $this->get_option('payment_image');
			$this->icon 				= WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/assets/images/hipay_logo-'.$this->payment_image.'.png';

			
			$this->fraud_details 			= get_option( 'woocommerce_hipayenterprise_fraud',
				array(
					'woocommerce_hipayenterprise_fraud_copy_to'   	=> $this->get_option( 'woocommerce_hipayenterprise_fraud_copy_to' ),
					'woocommerce_hipayenterprise_fraud_copy_method' => $this->get_option( 'woocommerce_hipayenterprise_fraud_copy_method' ),
				)
			);

			$this->list_of_currencies 		= get_woocommerce_currencies();
			$this->currencies_details 		= get_option( 'woocommerce_hipayenterprise_currencies',
				array(
					'woocommerce_hipayenterprise_currencies_active' => $this->get_option( 'woocommerce_hipayenterprise_currencies_active' ),
					
				)
			);	

			$this->method_details 			= get_option( 'woocommerce_hipayenterprise_methods',
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
					'woocommerce_hipayenterprise_methods_payment_image'			=> $this->get_option( 'woocommerce_hipayenterprise_methods_payment_image' ),
					'woocommerce_hipayenterprise_methods_payments' 				=> $this->get_option( 'woocommerce_hipayenterprise_methods_payments' ),
				)
			);

			$this->title 			= __('Pay by Credit Card', $this->id );

			if ($this->method_details['woocommerce_hipayenterprise_methods_hosted_mode'] == "redirect" && $this->method_details['woocommerce_hipayenterprise_methods_mode'] == "hosted_page")
				$this->description 	= __('You will be redirected to an external payment page. Please do not refresh the page during the process.', $this->id );
			else
				$this->description 	= "";

			if ($this->method_details['woocommerce_hipayenterprise_methods_payment_image'] != "")
				$this->icon 				= WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/assets/images/'.$this->method_details['woocommerce_hipayenterprise_methods_payment_image'];
			else
				$this->icon 				= "";

			if (!isset($this->method_details["woocommerce_hipayenterprise_methods_payments"]) || $this->method_details["woocommerce_hipayenterprise_methods_payments"] == "" )
				$this->method_details["woocommerce_hipayenterprise_methods_payments"] = HIPAY_ENTERPRISE_PAYMENT_METHODS;

			$this->method_details["woocommerce_hipayenterprise_methods_payments"] = str_replace("\'", "'", $this->method_details["woocommerce_hipayenterprise_methods_payments"]);
			
			require_once( plugin_dir_path( __FILE__ ) . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-paypal.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-inghomepay.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-ideal.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-giropay.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-belfius.php' );
			require_once( plugin_dir_path( __FILE__ ) . 'includes/payment-methods/class-wc-hipayenterprise-localpayments-multibanco.php' );
			
			add_action('woocommerce_api_wc_hipayenterprise', 						array($this, 'check_callback_response') );
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, 	array($this, 'process_admin_options'));
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, 	array($this, 'save_account_details' ) );
			add_action('woocommerce_receipt_' . 						$this->id, 	array($this, 'receipt_page' ) );

			wp_enqueue_style('hipayenterprise-style', plugins_url( '/assets/css/style.css', __FILE__ ), array(),'all');
			wp_enqueue_style('hipayenterprise-card-style', plugins_url( '/assets/css/card-js.min.css', __FILE__ ), array(),'all');
			
		}

		public function save_account_details() {

			$fraud = array();
			$fraud['woocommerce_hipayenterprise_fraud_copy_to'] 			= sanitize_email($_POST['woocommerce_hipayenterprise_fraud_copy_to']);
			$fraud['woocommerce_hipayenterprise_fraud_copy_method'] 		= sanitize_title($_POST['woocommerce_hipayenterprise_fraud_copy_method']);

			$currencies = array();
			$woocommerce_hipayenterprise_currencies_active   				= array_map( 'wc_clean', $_POST['woocommerce_hipayenterprise_currencies_active'] );
			$currencies['woocommerce_hipayenterprise_currencies_active']	= $woocommerce_hipayenterprise_currencies_active;
	
			$methods = array();

			$all_methods 		= str_replace("\'", "'", HIPAY_ENTERPRISE_PAYMENT_METHODS);
			$all_methods 		= json_decode($all_methods);

			$max_amount = 0;
			$min_amount = -1;
			$countries_list = array();
			$currencies_list = array();
			$local_payments_filter = array();

			$all_methods_json 	= "[";
			foreach ($all_methods as $key => $value) {
				$the_method = new HipayEnterprisePaymentMethodClass($value);	
				//$current_methods[] = $the_method;	
				if ((bool)$the_method->get_is_credit_card()) {
					$the_method->set_is_active($_POST['woocommerce_hipayenterprise_methods_cc_activated'][$the_method->get_key()]);
					$the_method->set_max_amount($_POST['woocommerce_hipayenterprise_methods_cc_max_amount'][$the_method->get_key()]);
					$the_method->set_min_amount($_POST['woocommerce_hipayenterprise_methods_cc_min_amount'][$the_method->get_key()]);
					$the_method->set_available_currencies($_POST['woocommerce_hipayenterprise_methods_cc_currencies'][$the_method->get_key()]);
					$the_method->set_available_countries($_POST['woocommerce_hipayenterprise_methods_cc_countries_available_list'][$the_method->get_key()]);
					$temp_list = explode(",",$_POST['woocommerce_hipayenterprise_methods_cc_countries_available_list'][$the_method->get_key()]);
					if ($the_method->get_is_active()) $countries_list = array_unique(array_filter(array_merge($countries_list,$temp_list )));
					$temp_list = explode(",",$the_method->get_available_currencies());
					if ($the_method->get_is_active()) $currencies_list = array_unique(array_filter(array_merge($currencies_list,$temp_list )));
					$all_methods_json .= $the_method->get_json() . ",";
				} else {
					$the_method->set_is_active($_POST['woocommerce_hipayenterprise_methods_lp_activated'][$the_method->get_key()]);

					$the_method->set_max_amount($_POST['woocommerce_hipayenterprise_methods_lp_max_amount'][$the_method->get_key()]);
					$the_method->set_min_amount($_POST['woocommerce_hipayenterprise_methods_lp_min_amount'][$the_method->get_key()]);
					$the_method->set_available_currencies($_POST['woocommerce_hipayenterprise_methods_lp_currencies'][$the_method->get_key()]);
					$the_method->set_available_countries($_POST['woocommerce_hipayenterprise_methods_lp_countries_available_list'][$the_method->get_key()]);

					$temp_list = explode(",",$_POST['woocommerce_hipayenterprise_methods_lp_countries_available_list'][$the_method->get_key()]);
 					$local_payments_filter[$the_method->get_key()]["available_countries"] 	= $temp_list;
					$temp_list = explode(",",$the_method->get_available_currencies());
					$local_payments_filter[$the_method->get_key()]["available_currencies"] 	= $temp_list;
					$local_payments_filter[$the_method->get_key()]["max_amount"] 			= $the_method->get_max_amount();
					$local_payments_filter[$the_method->get_key()]["enabled"] 				= $the_method->get_is_active();
					$local_payments_filter[$the_method->get_key()]["min_amount"] 			= $the_method->get_min_amount();
					$all_methods_json .= $the_method->get_json() . ",";
				}

				if ($the_method->get_is_active() && $the_method->get_is_credit_card()){
					if ($the_method->get_max_amount() > $max_amount) $max_amount = $the_method->get_max_amount();
					if ( ($the_method->get_min_amount() < $min_amount) || $min_amount > -1 ) $min_amount = $the_method->get_min_amount();
				}


			}

			$all_methods_json 	= substr($all_methods_json,0, -1) . "]";
 			$methods = array(
 				'woocommerce_hipayenterprise_methods_mode'		 					=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_mode']),
 				'woocommerce_hipayenterprise_methods_capture'		 				=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_capture']),
				'woocommerce_hipayenterprise_methods_3ds' 							=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_3ds']),
				'woocommerce_hipayenterprise_methods_oneclick'						=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_oneclick']),
				'woocommerce_hipayenterprise_methods_cart_sending'					=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_cart_sending']),
				'woocommerce_hipayenterprise_methods_keep_cart_onfail'				=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_keep_cart_onfail']),
				'woocommerce_hipayenterprise_methods_log_info'						=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_log_info']),
				'woocommerce_hipayenterprise_methods_hosted_css'					=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_hosted_css']),
				'woocommerce_hipayenterprise_methods_hosted_mode'					=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_hosted_mode']),
				'woocommerce_hipayenterprise_methods_hosted_card_selector'			=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_hosted_card_selector']),
				'woocommerce_hipayenterprise_methods_payment_image'					=> $_POST['woocommerce_hipayenterprise_methods_payment_image'],
				'woocommerce_hipayenterprise_methods_payments' 						=> $all_methods_json,
 				'woocommerce_hipayenterprise_methods_payments_min_amount'			=> $min_amount,
 				'woocommerce_hipayenterprise_methods_payments_max_amount'			=> $max_amount,
 				'woocommerce_hipayenterprise_methods_payments_countries_list'		=> $countries_list,
 				'woocommerce_hipayenterprise_methods_payments_currencies_list'		=> $currencies_list,
 				'woocommerce_hipayenterprise_methods_payments_local_payments_filter'=> $local_payments_filter,
			);	

			update_option( 'woocommerce_hipayenterprise_methods'	, $methods );
			update_option( 'woocommerce_hipayenterprise_currencies'	, $currencies );
			update_option( 'woocommerce_hipayenterprise_fraud'		, $fraud );

		}

		public function payment_fields()
		{
			global $woocommerce;
			global $wpdb;

			if ($this->method_details['woocommerce_hipayenterprise_methods_mode'] == "hosted_page"){
				if ($this->method_details['woocommerce_hipayenterprise_methods_hosted_mode'] == "redirect")
					_e('You will be redirected to an external payment page. Please do not refresh the page during the process.', $this->id );
				else
					_e('Pay with your credit card.', $this->id );

			}
			elseif ( $this->method_details['woocommerce_hipayenterprise_methods_mode'] == "api") {	


				$username 	= (!$this->sandbox) ? $this->account_production_tokenization_username 	: $this->account_test_tokenization_username;
				$password 	= (!$this->sandbox) ? $this->account_production_tokenization_password	: $this->account_test_tokenization_password;
				$env = ($this->sandbox) ? "stage" : "production";
				$customer_id = get_current_user_id();

				if ($customer_id > 0) {
					$token_flag = $wpdb->get_row( "SELECT id,brand,pan,card_holder,card_expiry_month,card_expiry_year,issuer,country,token FROM $this->plugin_table_token WHERE customer_id = $customer_id LIMIT 1");
					if (isset($token_flag->id) ){
						if ( strlen($token_flag->card_expiry_month)<2) $token_flag->card_expiry_month = "0" . $token_flag->card_expiry_month;
						if ( strlen($token_flag->card_expiry_year)<4)  $token_flag->card_expiry_year  = "20" . $token_flag->card_expiry_year;
						$token_flag_json = json_encode($token_flag);
						
					}
				}
			?>
					<script type="text/javascript" src="<?php echo plugins_url( '/vendor/bower_components/hipay-fullservice-sdk-js/dist/strings.js', __FILE__ ) ;?>"></script>
					<script type="text/javascript" src="<?php echo plugins_url( '/vendor/bower_components/hipay-fullservice-sdk-js/dist/card-js.min.js', __FILE__ ) ;?>"></script>
					<script type="text/javascript" src="<?php echo plugins_url( '/vendor/bower_components/hipay-fullservice-sdk-js/dist/card-tokenize.js', __FILE__ ) ;?>"></script>
					<script type="text/javascript" src="<?php echo plugins_url( '/vendor/bower_components/hipay-fullservice-sdk-js/dist/hipay-fullservice-sdk.min.js', __FILE__ ) ;?>"></script>



					<form id="tokenizerForm" action="" enctype="application/x-www-form-urlencoded" class="form-horizontal hipay-form-17" method="post" name="tokenizerForm" autocomplete="off">
						<input type="hidden" id="ioBB" name="ioBB" value="">	

					<script type="text/javascript">
						
					var io_operation = 'ioBegin';
					// io_bbout_element_id should refer to the hidden field in your form that contains the blackbox
					var io_bbout_element_id = 'ioBB';

					var io_install_stm = false; // do not try to download activex control
					var io_exclude_stm = 12;  // do not attempt to instantiate an activex control
					// installed by another customer
					var io_install_flash = false; // do not force installation of Flash Player
					var io_install_rip = true; // do attempt to collect real ip

					// uncomment any of the below to signal an error when ActiveX or Flash is not present
					//var io_install_stm_error_handler = "redirectActiveX();";
					var io_flash_needs_update_handler = "";
					var io_install_flash_error_handler = "";

					</script>
					<script type="text/javascript" src="https://mpsnare.iesnare.com/snare.js" async></script>					
					<script type="text/javascript">
					    var i18nFieldIsMandatory = "Field is mandatory";
					    var i18nBadIban = "This is not a correct IBAN";
					    var i18nBadBic = "This is not a correct BIC";
					    var i18nBadCC = "This is not a correct credit card number";
					    var i18nBadCPF = "This is not a correct CPF";
					    var i18nBadCPNCURP = "This is not a correct CPN/CURP";
					    var i18nBadRequest = "An error occurred with the request.";
					    var i18nCardNumber = "Card Number";
					    var i18nNameOnCard = "name on card";
					    var i18nDate = "MM / YY";
					    var i18nCVC = "CVC";
					    var i18nTokenisationError416 = "The expiration date is incorrect. Please enter a date higher than the current date";
					    var i18nCVCTooltip = "3-digit security code usually found on the back of your card. American Express cards have a 4-digit code located on the front.";
					    var i18nCVCLabel = "What is CVC ?";
					    var i18nCardNumberLocal = "Card Number";
					    var i18nNameOnCardLocal = "name on card";
					    var i18nDateLocal = "MM / YY";
					    var i18nCVCLabelLocal = "What is CVC ?";
					    var i18nCVCTooltipLocal = "3-digit security code usually found on the back of your card. American Express cards have a 4-digit code located on the front.";
					</script>

	                    <div id="error-js" style="" class="alert alert-danger">
				        	<ul><li class="error" id="hiPay_error_message"></li></ul>

						</div>
					    
					    <div class="card-js " data-icon-colour="#158CBA">
					    	<div class="card-number-wrapper">
					    		<input id="hipay-card-number" class="card-number my-custom-class" name="card-number" type="tel" placeholder="<?php echo __('Card Number','hipayenterprise');?>" maxlength="19" x-autocompletetype="cc-number" autocompletetype="cc-number" autocorrect="off" spellcheck="off" autocapitalize="off">
					    		<div class="card-type-icon"></div>
					    		<div class="icon"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="3px" width="24px" height="17px" viewBox="0 0 216 146" enable-background="new 0 0 216 146" xml:space="preserve"><g><path class="svg" d="M182.385,14.258c-2.553-2.553-5.621-3.829-9.205-3.829H42.821c-3.585,0-6.653,1.276-9.207,3.829c-2.553,2.553-3.829,5.621-3.829,9.206v99.071c0,3.585,1.276,6.654,3.829,9.207c2.554,2.553,5.622,3.829,9.207,3.829H173.18c3.584,0,6.652-1.276,9.205-3.829s3.83-5.622,3.83-9.207V23.464C186.215,19.879,184.938,16.811,182.385,14.258z M175.785,122.536c0,0.707-0.258,1.317-0.773,1.834c-0.516,0.515-1.127,0.772-1.832,0.772H42.821c-0.706,0-1.317-0.258-1.833-0.773c-0.516-0.518-0.774-1.127-0.774-1.834V73h135.571V122.536z M175.785,41.713H40.214v-18.25c0-0.706,0.257-1.316,0.774-1.833c0.516-0.515,1.127-0.773,1.833-0.773H173.18c0.705,0,1.316,0.257,1.832,0.773c0.516,0.517,0.773,1.127,0.773,1.833V41.713z" style="fill: rgb(21, 140, 186);"></path><rect class="svg" x="50.643" y="104.285" width="20.857" height="10.429" style="fill: rgb(21, 140, 186);"></rect><rect class="svg" x="81.929" y="104.285" width="31.286" height="10.429" style="fill: rgb(21, 140, 186);"></rect></g></svg></div>
					    	</div>

					    	<div class="name-wrapper">
					    		<input id="hipay-the-card-name-id" class="name" name="card-holders-name" placeholder="<?php echo __('Name on card','hipayenterprise');?>">
					    		<div class="icon"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="4px" width="24px" height="16px" viewBox="0 0 216 146" enable-background="new 0 0 216 146" xml:space="preserve"><g><path class="svg" d="M107.999,73c8.638,0,16.011-3.056,22.12-9.166c6.111-6.11,9.166-13.483,9.166-22.12c0-8.636-3.055-16.009-9.166-22.12c-6.11-6.11-13.484-9.165-22.12-9.165c-8.636,0-16.01,3.055-22.12,9.165c-6.111,6.111-9.166,13.484-9.166,22.12c0,8.637,3.055,16.01,9.166,22.12C91.99,69.944,99.363,73,107.999,73z" style="fill: rgb(21, 140, 186);"></path><path class="svg" d="M165.07,106.037c-0.191-2.743-0.571-5.703-1.141-8.881c-0.57-3.178-1.291-6.124-2.16-8.84c-0.869-2.715-2.037-5.363-3.504-7.943c-1.466-2.58-3.15-4.78-5.052-6.6s-4.223-3.272-6.965-4.358c-2.744-1.086-5.772-1.63-9.085-1.63c-0.489,0-1.63,0.584-3.422,1.752s-3.815,2.472-6.069,3.911c-2.254,1.438-5.188,2.743-8.799,3.909c-3.612,1.168-7.237,1.752-10.877,1.752c-3.639,0-7.264-0.584-10.876-1.752c-3.611-1.166-6.545-2.471-8.799-3.909c-2.254-1.439-4.277-2.743-6.069-3.911c-1.793-1.168-2.933-1.752-3.422-1.752c-3.313,0-6.341,0.544-9.084,1.63s-5.065,2.539-6.966,4.358c-1.901,1.82-3.585,4.02-5.051,6.6s-2.634,5.229-3.503,7.943c-0.869,2.716-1.589,5.662-2.159,8.84c-0.571,3.178-0.951,6.137-1.141,8.881c-0.19,2.744-0.285,5.554-0.285,8.433c0,6.517,1.983,11.664,5.948,15.439c3.965,3.774,9.234,5.661,15.806,5.661h71.208c6.572,0,11.84-1.887,15.806-5.661c3.966-3.775,5.948-8.921,5.948-15.439C165.357,111.591,165.262,108.78,165.07,106.037z" style="fill: rgb(21, 140, 186);"></path></g></svg></div></div><div class="expiry-container"><div class="expiry-wrapper"><div><input class="expiry" type="tel" placeholder="MM / YY" maxlength="7" x-autocompletetype="cc-exp" autocompletetype="cc-exp" autocorrect="off" spellcheck="off" autocapitalize="off" id="hipay-expiry"><input type="hidden" name="expiry-month" id="hipay-expiry-month"><input type="hidden" name="expiry-year" id="hipay-expiry-year"></div>
					    		<div class="icon"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="4px" width="24px" height="16px" viewBox="0 0 216 146" enable-background="new 0 0 216 146" xml:space="preserve"><path class="svg" d="M172.691,23.953c-2.062-2.064-4.508-3.096-7.332-3.096h-10.428v-7.822c0-3.584-1.277-6.653-3.83-9.206c-2.554-2.553-5.621-3.83-9.207-3.83h-5.213c-3.586,0-6.654,1.277-9.207,3.83c-2.554,2.553-3.83,5.622-3.83,9.206v7.822H92.359v-7.822c0-3.584-1.277-6.653-3.83-9.206c-2.553-2.553-5.622-3.83-9.207-3.83h-5.214c-3.585,0-6.654,1.277-9.207,3.83c-2.553,2.553-3.83,5.622-3.83,9.206v7.822H50.643c-2.825,0-5.269,1.032-7.333,3.096s-3.096,4.509-3.096,7.333v104.287c0,2.823,1.032,5.267,3.096,7.332c2.064,2.064,4.508,3.096,7.333,3.096h114.714c2.824,0,5.27-1.032,7.332-3.096c2.064-2.064,3.096-4.509,3.096-7.332V31.286C175.785,28.461,174.754,26.017,172.691,23.953z M134.073,13.036c0-0.761,0.243-1.386,0.731-1.874c0.488-0.488,1.113-0.733,1.875-0.733h5.213c0.762,0,1.385,0.244,1.875,0.733c0.488,0.489,0.732,1.114,0.732,1.874V36.5c0,0.761-0.244,1.385-0.732,1.874c-0.49,0.488-1.113,0.733-1.875,0.733h-5.213c-0.762,0-1.387-0.244-1.875-0.733s-0.731-1.113-0.731-1.874V13.036z M71.501,13.036c0-0.761,0.244-1.386,0.733-1.874c0.489-0.488,1.113-0.733,1.874-0.733h5.214c0.761,0,1.386,0.244,1.874,0.733c0.488,0.489,0.733,1.114,0.733,1.874V36.5c0,0.761-0.244,1.386-0.733,1.874c-0.489,0.488-1.113,0.733-1.874,0.733h-5.214c-0.761,0-1.386-0.244-1.874-0.733c-0.488-0.489-0.733-1.113-0.733-1.874V13.036z M165.357,135.572H50.643V52.143h114.714V135.572z" style="fill: rgb(21, 140, 186);"></path></svg></div></div></div><div class="cvc-container"><div class="cvc-wrapper"><input id="hipay-cvc" class="cvc" data-toggle="tooltip" title="<?php echo __("3-digit security code usually found on the back of your card. American Express cards have a 4-digit code located on the front.","hipayenterprise");?>" name="cvc" type="tel" placeholder="CVC" maxlength="3" x-autocompletetype="cc-csc" autocompletetype="cc-csc" autocorrect="off" spellcheck="off" autocapitalize="off">
					    		<div class="icon"><svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="3px" width="24px" height="17px" viewBox="0 0 216 146" enable-background="new 0 0 216 146" xml:space="preserve"><path class="svg" d="M152.646,70.067c-1.521-1.521-3.367-2.281-5.541-2.281H144.5V52.142c0-9.994-3.585-18.575-10.754-25.745c-7.17-7.17-15.751-10.755-25.746-10.755s-18.577,3.585-25.746,10.755C75.084,33.567,71.5,42.148,71.5,52.142v15.644h-2.607c-2.172,0-4.019,0.76-5.54,2.281c-1.521,1.52-2.281,3.367-2.281,5.541v46.929c0,2.172,0.76,4.019,2.281,5.54c1.521,1.52,3.368,2.281,5.54,2.281h78.214c2.174,0,4.02-0.76,5.541-2.281c1.52-1.521,2.281-3.368,2.281-5.54V75.607C154.93,73.435,154.168,71.588,152.646,70.067z M128.857,67.786H87.143V52.142c0-5.757,2.037-10.673,6.111-14.746c4.074-4.074,8.989-6.11,14.747-6.11s10.673,2.036,14.746,6.11c4.073,4.073,6.11,8.989,6.11,14.746V67.786z" style="fill: rgb(21, 140, 186);"></path></svg></div>
					    	</div>

					    </div></div>
						<input type="hidden" id="hipay_user_token" value='<?php echo $token_flag_json;?>'>				    	
						<?php

						if ($this->method_details['woocommerce_hipayenterprise_methods_oneclick'] == "1" && $customer_id > 0) {	?>
								<span class="custom-checkbox"><br><input id="saveTokenHipay" type="checkbox" name="saveTokenHipay" <?php if ($token_flag_json!="") echo "CHECKED";?>><label for="saveTokenHipay"><?php echo __("Save credit card (One click payment)","hipayenterprise");?></label>
						        </span>
						<?php
						}	?>        

					    <div><br><button type="submit" class="button alt" name="woocommerce_tokenize_order" id="woocommerce_tokenize_order" value="<?php echo __('Validate Credit Card','hipayenterprise');?>" data-value="<?php echo __('Validate Credit Card','hipayenterprise');?>"><?php echo __('Validate Credit Card','hipayenterprise');?></button></div>
						</form>

						<div id="hipayPaymentInformations" style="display:none;">
							<div id="hipayPaymentInformationsTitle"><?php echo __("Validate your Payment Information",'hipayenterprise');?></div>
							
							<div id="hipayPaymentInformationsInfo"><?php echo __("Card Number",'hipayenterprise');?>: <span id="cardnumberinfo"></span></div>
							<div id="hipayPaymentInformationsInfo"><?php echo __("Name on Card",'hipayenterprise');?>: <span id="nameoncardinfo"></span></div>
							<div id="hipayPaymentInformationsInfo"><?php echo __("Expiry date",'hipayenterprise');?>: <span id="expirydateinfo"></span></div>

							<div><br>
								<button type="button" class="button alt" name="woocommerce_tokenize_order_reset" id="woocommerce_tokenize_order_reset" value="<?php echo __('Change Credit Card','hipayenterprise');?>" data-value="<?php echo __('Change Credit Card','hipayenterprise');?>"><?php echo __('Change Credit Card','hipayenterprise');?></button>
								<?php if ($token_flag_json!="") {	?>
									<button type="button" class="button alt" name="woocommerce_tokenize_order_delete" id="woocommerce_tokenize_order_delete" value="<?php echo __('Remove Credit Card','hipayenterprise');?>" data-value="<?php echo __('Remove Credit Card','hipayenterprise');?>"><?php echo __('Remove Credit Card','hipayenterprise');?></button>
								<?php }	?>
							</div>


						</div>

					<script>

						jQuery(function($) {     


                            $('form[name="checkout"] input[name="payment_method"]').eq(0).prop('checked', true).attr( 'checked', 'checked' );
							usingGateway();

						    $('form[name="checkout"] input[type=radio][name=payment_method]').change(function() {
						        if (this.value == 'hipayenterprise') {
						            usingGateway();
						        } else {
						        	if ($('#place_order').css('display') == 'none') $("#place_order").show();
						        }
						    });


							$( ".expiry" ).change(function() {
							    $("input[name='expiry-month']").val($( ".expiry" ).val().replace(/\s/g,'').substring(0,2));
							    $("input[name='expiry-year']").val($( ".expiry" ).val().replace(/\s/g,'').substring(3,5));
							});


							function usingGateway(){

						  		if($('form[name="checkout"] input[name="payment_method"]:checked').val() == 'hipayenterprise' ){


						  			if ($("#hipay_user_token").val()!=""){
						  				var tokenObj = JSON.parse($("#hipay_user_token").val());
						  				console.log(tokenObj.id);
								        $("#cardnumberinfo").html(tokenObj.pan + " ("+tokenObj.brand+")");
								        $("#nameoncardinfo").html(tokenObj.card_holder);
								        $("#expirydateinfo").html(tokenObj.card_expiry_month + " / " + tokenObj.card_expiry_year);

										if ($('#saveTokenHipay').attr('checked'))
											multiuse = '1';
										else
											multiuse = '0';

								        $("#hipay_token").val(tokenObj.token);
								        $("#hipay_brand").val(tokenObj.brand);
								        $("#hipay_pan").val(tokenObj.pan);
								        $("#hipay_card_holder").val(tokenObj.card_holder);
								        $("#hipay_card_expiry_month").val(tokenObj.card_expiry_month);
								        $("#hipay_card_expiry_year").val(tokenObj.card_expiry_year);
								        $("#hipay_issuer").val(tokenObj.issuer);
								        $("#hipay_country").val(tokenObj.country);
								        $("#hipay_multiuse").val(multiuse);
								        $("#hipay_direct_error").val("");

						  				$('#tokenizerForm').hide();
										$('#hipayPaymentInformations').show();
										$("#place_order").show();
						  			} else {
						  				$("#place_order").hide();
						  			}
									
									$('#woocommerce_tokenize_order_reset').click(function(e){
										e.preventDefault();
								        $("#hipay_token").val("");
								        $("#hiPay_error_message").html("");
								        $("#hipay_brand").val("");
								        $("#hipay_pan").val("");
								        $("#hipay_card_holder").val("");
								        $("#hipay_card_expiry_month").val("");
								        $("#hipay_card_expiry_year").val("");
								        $("#hipay_issuer").val("");
								        $("#hipay_country").val("");
								        $("#hipay_multiuse").val("");
								        $("#cardnumberinfo").html("");
								        $("#nameoncardinfo").html("");
								        $("#expirydateinfo").html("");
								        $("#place_order").hide();	
										$('#tokenizerForm').show();
										$('#hipayPaymentInformations').hide();
										return false;
									});	

									$('#woocommerce_tokenize_order_delete').click(function(e){
										e.preventDefault();
										$("#hipay_delete_info").val($("#hipay_token").val());
										$("#hipay_user_token").val("");
								        $("#hipay_token").val("");
								        $("#hiPay_error_message").html("");
								        $("#hipay_brand").val("");
								        $("#hipay_pan").val("");
								        $("#hipay_card_holder").val("");
								        $("#hipay_card_expiry_month").val("");
								        $("#hipay_card_expiry_year").val("");
								        $("#hipay_issuer").val("");
								        $("#hipay_country").val("");
								        $("#hipay_delete_info").val();
								        $("#hipay_multiuse").val("");
								        $("#cardnumberinfo").html("");
								        $("#nameoncardinfo").html("");
								        $("#expirydateinfo").html("");
								        $("#place_order").hide();	
										$('#tokenizerForm').show();
										$('#hipayPaymentInformations').hide();
										return false;
									});


									$('#woocommerce_tokenize_order').click(function(e){
										e.preventDefault();
										$("#hiPay_error_message").html("");
										if ($('#saveTokenHipay').attr('checked'))
											multiuse = '1';
										else
											multiuse = '0';

										var params = {
										    card_number: $('#hipay-card-number').val(),
										    cvc: $('#hipay-cvc').val(),
										    card_expiry_month: $("input[name='expiry-month']").val(),
										    card_expiry_year: $("input[name='expiry-year']").val(),
										    card_holder: $('#hipay-the-card-name-id').val(),
										    multi_use: multiuse,
										 };
										HiPay.setTarget('<?php echo $env;?>'); 
										HiPay.setCredentials('<?php echo $username;?>', '<?php echo $password;?>');
										HiPay.create(params,

												function(result) {

													if ($('#saveTokenHipay').attr('checked'))
														multiuse = '1';
													else
														multiuse = '0';
											        $("#hipay_token").val(result.token);
											        $("#hipay_brand").val(result.brand);
											        $("#hipay_pan").val(result.pan);
											        $("#hipay_card_holder").val(result.card_holder);
											        $("#hipay_card_expiry_month").val(result.card_expiry_month);
											        $("#hipay_card_expiry_year").val(result.card_expiry_year);
											        $("#hipay_issuer").val(result.issuer);
											        $("#hipay_country").val(result.country);
											        $("#hipay_multiuse").val(multiuse);
											        $("#hipay_direct_error").val("");

											        $("#cardnumberinfo").html(result.pan + " ("+result.brand+")");
											        $("#nameoncardinfo").html(result.card_holder);
											        $("#expirydateinfo").html(result.card_expiry_month + " / " + result.card_expiry_year);

											        $("#place_order").show();	
											     	$('#tokenizerForm').hide();
													$('#hipayPaymentInformations').show();
											        return false;					        
											    }, 

											    function (errors) {

											        $("#hipay_token").val("");
											        $("#hipay_brand").val("");
											        $("#hipay_pan").val("");
											        $("#hipay_card_holder").val("");
											        $("#hipay_card_expiry_month").val("");
											        $("#hipay_card_expiry_year").val("");
											        $("#hipay_issuer").val("");
											        $("#hipay_country").val("");
											        $("#hipay_multiuse").val("");
											        $("#cardnumberinfo").html("");
											        $("#nameoncardinfo").html("");
											        $("#expirydateinfo").html("");
											        $("#hipay_direct_error").val("<?php echo __('Error processing payment information.','hipayenterprise');?>");
											        $("#hiPay_error_message").html("<?php echo __('Please check your payment information.','hipayenterprise');?><br>");
											        
											        return false;				        

											    }
					    

											  );

   										return false;

									});

    							} 
    						}


						});




					</script><br>



		<?php						             
			}	

		}

		
	
		function init_form_fields() {
			
			include( plugin_dir_path( __FILE__ ) . 'includes/database_config.php' );
			include( plugin_dir_path( __FILE__ ) . 'includes/form_fields.php' );		
		}


		function generate_methods_credit_card_settings_html() {

			ob_start();

			$current_methods = [];
			$woocommerce_hipayenterprise_methods_payments_json = json_decode($this->method_details["woocommerce_hipayenterprise_methods_payments"]);
			foreach ($woocommerce_hipayenterprise_methods_payments_json as $key => $value) {
				$the_method = new HipayEnterprisePaymentMethodClass($value);	
				$current_methods[] = $the_method;			
			}
			$woocommerce_hipayenterprise_methods_payment_image = $this->method_details["woocommerce_hipayenterprise_methods_payment_image"];
			$payment_images_directory = plugin_dir_path( __FILE__ ) . 'assets/images/';
			$scanned_directory = array_diff(scandir($payment_images_directory), array('..', '.','index.php'));
						
			?>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Payment Image', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Payment Image", 'hipayenterprise');?></span></legend>
					<select class="select " name="woocommerce_hipayenterprise_methods_payment_image" id="woocommerce_hipayenterprise_methods_payment_image" style="">
						<option value=""><?php _e( 'None', 'hipayenterprise' ); ?></option>
						<?php
						foreach ($scanned_directory as $key => $value) {	
							$payment_image_path_part 	= pathinfo($payment_images_directory . $value);
							$payment_image_filename 	= str_replace("_", " + " , $payment_image_path_part['filename']);
							?>
							<option value="<?php echo $value;?>" <?php if ($woocommerce_hipayenterprise_methods_payment_image == $value) echo " SELECTED";?>><?php echo $payment_image_filename;?></option>
						<?php
						}
						?>				
					</select>
					<p class="description"><?php _e("Api if the customer will fill his bank information directly on merchants OR Hosted if the customer is redirected to a secured payment page hosted by HiPay.",'hipayenterprise');?></p>
				</fieldset>
				</td>
			</tr>


			<tr valign="top">
				<th scope="row" class="">

					<?php
					$sel_btn = " credit_card_admin_menu_sel";
					foreach ($current_methods as $the_method) {
						if ((bool)$the_method->get_is_credit_card()) {
							echo "<div data-id='".$the_method->get_key()."' class='credit_card_admin_menu".$sel_btn."'>" . __( $the_method->get_title(), 'hipayenterprise' ) . "<br></div>"; 					
							$sel_btn = "";
						}
					}
					?>
					 
				</th>
				<td class="forminp" valign="top">
				<fieldset>

					<?php
					$sel_btn = "";
					foreach ($current_methods as $the_method) {
						if ((bool)$the_method->get_is_credit_card()) {
							echo "<div data-id='".$the_method->get_key()."' class='credit_card_admin_config_".$the_method->get_key()." credit_card_admin_config".$sel_btn."'>";
							echo "<b>".  __( $the_method->get_title(), 'hipayenterprise' ) . "</b><hr>"; 
							?>	
								<table>

								<tr valign="top">
									<td align="right"><?php _e( 'Activated', 'hipayenterprise' ); ?></td>
									<td class="forminp">
										<fieldset>
											<legend class="screen-reader-text"><span><?php _e( 'Use Oneclick', 'hipayenterprise' ); ?></span></legend>
											<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_cc_activated[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_cc_activated" style="" value="1" <?php if ($the_method->get_is_active()) echo 'checked="checked"';?>> <br>
										</fieldset>
									</td>
								</tr>

								<tr valign="top">
									<td valign="top" align="right"><?php _e( 'Minimum order amount', 'hipayenterprise' ); ?></td>
									<td class="forminp">
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e("Minimum order amount", 'hipayenterprise');?></span></legend>
										<input class="input-text regular-input " type="text" name="woocommerce_hipayenterprise_methods_cc_min_amount[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_cc_min_amount" style="" value="<?php echo $the_method->get_min_amount();?>" placeholder="">
									</fieldset>
									</td>
								</tr>

								<tr valign="top">
									<td align="right"><?php _e( 'Maximum order amount', 'hipayenterprise' ); ?></td>
									<td class="forminp">
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e("Maximum order amount", 'hipayenterprise');?></span></legend>
										<input class="input-text regular-input " type="text" name="woocommerce_hipayenterprise_methods_cc_max_amount[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_cc_max_amount" style="" value="<?php echo $the_method->get_max_amount();?>" placeholder="">
									</fieldset>
									</td>
								</tr>


								<tr valign="top">
									<td valign="top" align="right"><?php _e( 'Currencies', 'hipayenterprise' ); ?></td>
									
									<td class="forminp">
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e("Currencies", 'hipayenterprise');?></span></legend>
										<?php
										$authorized_currencies = array();
										$available_currencies = array();
										if ($the_method->get_authorized_currencies() != "") $authorized_currencies = explode(",", $the_method->get_authorized_currencies());
										if ($the_method->get_available_currencies() != "") $available_currencies = explode(",", $the_method->get_available_currencies());
										foreach ($this->currencies_details["woocommerce_hipayenterprise_currencies_active"] as $keyc => $valuec) {
											if (empty($authorized_currencies) || array_search($keyc, $authorized_currencies) !== false ){
												echo '<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_cc_currencies['. $the_method->get_key().']['. $keyc.']" id="woocommerce_hipayenterprise_methods_cc_currencies" style="" value="1"';
												if (array_search($keyc, $available_currencies) !== false )														
													echo ' checked="checked"';
												echo "><span style='padding-right:18px;'>" . $this->list_of_currencies[$keyc] . "</span>";
											}
										}	
										?>
									</fieldset>
									</td>

								</tr>

								<tr valign="top">
									<td valign="top" align="right" style='vertical-align:top;'><?php _e( 'Countries', 'hipayenterprise' ); ?></td>
									
									<td class="forminp">
									<fieldset>
										<div style="float:left;">	
										<span><?php _e("Available Countries", 'hipayenterprise');?></span><br>
										<select multiple class="input-text woocommerce_hipayenterprise_methods_cc_countries regular-input woocommerce_hipayenterprise_methods_cc_countries_<?php echo $the_method->get_key();?>" name="woocommerce_hipayenterprise_methods_cc_countries[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_cc_countries[<?php echo $the_method->get_key();?>]">
											<?php
											$authorized_countries_list = array();	
											$authorized_countries = array();
											$available_countries = array();
											if ($the_method->get_authorized_countries() != "") $authorized_countries = explode(",", $the_method->get_authorized_countries());
											if ($the_method->get_available_countries() != "") $available_countries = explode(",", $the_method->get_available_countries());

											$countries_wc   = new WC_Countries();
			    							$countries   = $countries_wc->__get('countries');
			    
			    							foreach ($countries as $keycc => $valuecc) {
			    								if (empty($authorized_countries) || array_search($keycc, $authorized_countries) !== false ){
			    									if (array_search($keycc, $available_countries) !== false )
			    										$authorized_countries_list[$keycc] = $valuecc;
			    									else
			    										echo "<option value='".$keycc."'>" . $valuecc . "</option>";
			    								}
			    							}
											?>
										</select>		
										</div>
										<div style='float:left;margin:0 30px;vertical-align:middle;padding-top:30px;'>
											<div class="dashicons dashicons-controls-forward is_pointer add_country" data-id="<?php echo $the_method->get_key();?>"></div><br>	
											<div class="dashicons dashicons-controls-back is_pointer rem_country" data-id="<?php echo $the_method->get_key();?>"></div>	
										</div>
										<div style="float:left;">	
										<span><?php _e("Authorized Countries", 'hipayenterprise');?></span><br>											
										<select multiple class="input-text woocommerce_hipayenterprise_methods_cc_countries_available regular-input woocommerce_hipayenterprise_methods_cc_countries_available_<?php echo $the_method->get_key();?>" name="woocommerce_hipayenterprise_methods_cc_countries_available[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_cc_countries_available[<?php echo $the_method->get_key();?>]">
											<?php
											$input_countries_list = "";
			    							foreach ($authorized_countries_list as $keycc => $valuecc) {
			    								echo "<option value='".$keycc."'>" . $valuecc . "</option>";
			    								$input_countries_list .= $keycc . ",";
			    							}
											?>
										</select>		
										<input type="hidden" class="woocommerce_hipayenterprise_methods_cc_countries_available_list<?php echo $the_method->get_key();?>" id="woocommerce_hipayenterprise_methods_cc_countries_available_list[<?php echo $the_method->get_key();?>]" name="woocommerce_hipayenterprise_methods_cc_countries_available_list[<?php echo $the_method->get_key();?>]" value="<?php echo $input_countries_list;?>">
									</div>
									</fieldset>
									</td>

								</tr>


								</table>
							<?php									
							echo "</div>"; 					
							$sel_btn = " hidden";
						}
					}
					?>

				</fieldset>
				</td>
			</tr>


			<tr valign="top">
				<th colspan="2" align="right"><?php submit_button(); ?></th>
			</tr>


			<script type="text/javascript">
				jQuery(function() {

					jQuery('.add_country').click( function(){
						$id = jQuery(this).attr("data-id");

						jQuery('.woocommerce_hipayenterprise_methods_cc_countries_' + $id + ' :selected').each(function(){
							jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_list' + $id ).val(jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_list' + $id ).val() + jQuery(this).val() + ",");
    					});
						
						jQuery('.woocommerce_hipayenterprise_methods_cc_countries_' + $id + ' option:selected').remove().appendTo('.woocommerce_hipayenterprise_methods_cc_countries_available_' + $id).removeAttr('selected');
						//add to list
						return false;
					});

					jQuery('.rem_country').click( function(){
						$id = jQuery(this).attr("data-id");

						jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_' + $id + ' :selected').each(function(){
							$countries_list = jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_list' + $id ).val();
							$countries_list = $countries_list.replace(jQuery(this).val() + ",","");
							$countries_list = jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_list' + $id ).val($countries_list);
    					});

						jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_' + $id + ' option:selected').remove().appendTo('.woocommerce_hipayenterprise_methods_cc_countries_' + $id).removeAttr('selected');
						return false;
					});

					jQuery('.credit_card_admin_menu').click( function(){
						$id = jQuery(this).attr("data-id");
						jQuery('.credit_card_admin_menu').removeClass("credit_card_admin_menu_sel");
						jQuery(this).addClass("credit_card_admin_menu_sel");
						jQuery('.credit_card_admin_config').addClass("hidden");
						jQuery('.credit_card_admin_config_'+$id).removeClass("hidden");
						return false;
					});


				});
			</script>

			<?php			
			return ob_get_clean();

		}



		function generate_methods_local_payments_settings_html() {

			ob_start();

			$current_methods = [];
			$woocommerce_hipayenterprise_methods_payments_json = json_decode($this->method_details["woocommerce_hipayenterprise_methods_payments"]);
			foreach ($woocommerce_hipayenterprise_methods_payments_json as $key => $value) {
				$the_method = new HipayEnterprisePaymentMethodClass($value);	
				$current_methods[] = $the_method;			
			}					
			?>

			<tr valign="top">
				<th scope="row" class="">

					<?php
					$sel_btn = " local_payment_admin_menu_sel";
					foreach ($current_methods as $the_method) {
						if ((bool)$the_method->get_is_local_payment()) {
							echo "<div data-id='".$the_method->get_key()."' class='local_payment_admin_menu".$sel_btn."'>" . __( $the_method->get_title(), 'hipayenterprise' ) . "<br></div>"; 					
							$sel_btn = "";
						}
					}
					?>
					 
				</th>
				<td class="forminp" valign="top">
				<fieldset>

					<?php
					$sel_btn = "";
					foreach ($current_methods as $the_method) {
						if ((bool)$the_method->get_is_local_payment()) {
							echo "<div data-id='".$the_method->get_key()."' class='local_payment_admin_config_".$the_method->get_key()." local_payment_admin_config".$sel_btn."'>";
							echo "<b>".  __( $the_method->get_title(), 'hipayenterprise' ) . "</b><hr>"; 
							?>	
								<table>

								<tr valign="top">
									<td align="right"><?php _e( 'Activated', 'hipayenterprise' ); ?></td>
									<td class="forminp">
										<fieldset>
											<legend class="screen-reader-text"><span><?php _e( 'Use Oneclick', 'hipayenterprise' ); ?></span></legend>
											<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_lp_activated[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_lp_activated" style="" value="1" <?php if ($the_method->get_is_active()) echo 'checked="checked"';?>> <br>
										</fieldset>
									</td>
								</tr>

								<tr valign="top">
									<td valign="top" align="right"><?php _e( 'Minimum order amount', 'hipayenterprise' ); ?></td>
									<td class="forminp">
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e("Minimum order amount", 'hipayenterprise');?></span></legend>
										<input class="input-text regular-input " type="text" name="woocommerce_hipayenterprise_methods_lp_min_amount[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_lp_min_amount" style="" value="<?php echo $the_method->get_min_amount();?>" placeholder="">
									</fieldset>
									</td>
								</tr>

								<tr valign="top">
									<td align="right"><?php _e( 'Maximum order amount', 'hipayenterprise' ); ?></td>
									<td class="forminp">
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e("Maximum order amount", 'hipayenterprise');?></span></legend>
										<input class="input-text regular-input " type="text" name="woocommerce_hipayenterprise_methods_lp_max_amount[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_lp_max_amount" style="" value="<?php echo $the_method->get_max_amount();?>" placeholder="">
									</fieldset>
									</td>
								</tr>


								<tr valign="top">
									<td valign="top" align="right"><?php _e( 'Currencies', 'hipayenterprise' ); ?></td>
									
									<td class="forminp">
									<fieldset>
										<legend class="screen-reader-text"><span><?php _e("Currencies", 'hipayenterprise');?></span></legend>
										<?php
										$authorized_currencies = array();
										$available_currencies = array();
										if ($the_method->get_authorized_currencies() != "") $authorized_currencies = explode(",", $the_method->get_authorized_currencies());
										if ($the_method->get_available_currencies() != "") $available_currencies = explode(",", $the_method->get_available_currencies());
										foreach ($this->currencies_details["woocommerce_hipayenterprise_currencies_active"] as $keyc => $valuec) {
											if (empty($authorized_currencies) || array_search($keyc, $authorized_currencies) !== false ){
												echo '<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_lp_currencies['. $the_method->get_key().']['. $keyc.']" id="woocommerce_hipayenterprise_methods_lp_currencies" style="" value="1"';
												if (array_search($keyc, $available_currencies) !== false )														
													echo ' checked="checked"';
												echo "><span style='padding-right:18px;'>" . $this->list_of_currencies[$keyc] . "</span>";
											}
										}	
										?>
									</fieldset>
									</td>

								</tr>

								<tr valign="top">
									<td valign="top" align="right" style='vertical-align:top;'><?php _e( 'Countries', 'hipayenterprise' ); ?></td>
									
									<td class="forminp">
									<fieldset>
										<div style="float:left;">	
										<span><?php _e("Available Countries", 'hipayenterprise');?></span><br>
										<select multiple class="input-text woocommerce_hipayenterprise_methods_lp_countries regular-input woocommerce_hipayenterprise_methods_lp_countries_<?php echo $the_method->get_key();?>" name="woocommerce_hipayenterprise_methods_lp_countries[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_lp_countries[<?php echo $the_method->get_key();?>]">
											<?php
											$authorized_countries_list = array();	
											$authorized_countries = array();
											$available_countries = array();
											if ($the_method->get_authorized_countries() != "") $authorized_countries = explode(",", $the_method->get_authorized_countries());
											if ($the_method->get_available_countries() != "") $available_countries = explode(",", $the_method->get_available_countries());

											$countries_wc   = new WC_Countries();
			    							$countries   = $countries_wc->__get('countries');
			    
			    							foreach ($countries as $keycc => $valuecc) {
			    								if (empty($authorized_countries) || array_search($keycc, $authorized_countries) !== false ){
			    									if (array_search($keycc, $available_countries) !== false )
			    										$authorized_countries_list[$keycc] = $valuecc;
			    									else
			    										echo "<option value='".$keycc."'>" . $valuecc . "</option>";
			    								}
			    							}
											?>
										</select>		
										</div>
										<div style='float:left;margin:0 30px;vertical-align:middle;padding-top:30px;'>
											<div class="dashicons dashicons-controls-forward is_pointer add_country_lp" data-id="<?php echo $the_method->get_key();?>"></div><br>	
											<div class="dashicons dashicons-controls-back is_pointer rem_country_lp" data-id="<?php echo $the_method->get_key();?>"></div>	
										</div>
										<div style="float:left;">	
										<span><?php _e("Authorized Countries", 'hipayenterprise');?></span><br>											
										<select multiple class="input-text woocommerce_hipayenterprise_methods_lp_countries_available regular-input woocommerce_hipayenterprise_methods_lp_countries_available_<?php echo $the_method->get_key();?>" name="woocommerce_hipayenterprise_methods_lp_countries_available[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_lp_countries_available[<?php echo $the_method->get_key();?>]">
											<?php
											$input_countries_list = "";
			    							foreach ($authorized_countries_list as $keycc => $valuecc) {
			    								echo "<option value='".$keycc."'>" . $valuecc . "</option>";
			    								$input_countries_list .= $keycc . ",";
			    							}
											?>
										</select>		
										<input type="hidden" class="woocommerce_hipayenterprise_methods_lp_countries_available_list<?php echo $the_method->get_key();?>" id="woocommerce_hipayenterprise_methods_lp_countries_available_list[<?php echo $the_method->get_key();?>]" name="woocommerce_hipayenterprise_methods_lp_countries_available_list[<?php echo $the_method->get_key();?>]" value="<?php echo $input_countries_list;?>">
									</div>
									</fieldset>
									</td>

								</tr>


								</table>
							<?php									
							echo "</div>"; 					
							$sel_btn = " hidden";
						}
					}
					?>

				</fieldset>
				</td>
			</tr>



			<script type="text/javascript">
				jQuery(function() {

					jQuery('.add_country_lp').click( function(){
						$id = jQuery(this).attr("data-id");

						jQuery('.woocommerce_hipayenterprise_methods_lp_countries_' + $id + ' :selected').each(function(){
							jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_list' + $id ).val(jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_list' + $id ).val() + jQuery(this).val() + ",");
    					});
						
						jQuery('.woocommerce_hipayenterprise_methods_lp_countries_' + $id + ' option:selected').remove().appendTo('.woocommerce_hipayenterprise_methods_lp_countries_available_' + $id).removeAttr('selected');
						//add to list
						return false;
					});

					jQuery('.rem_country_lp').click( function(){
						$id = jQuery(this).attr("data-id");

						jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_' + $id + ' :selected').each(function(){
							$countries_list = jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_list' + $id ).val();
							$countries_list = $countries_list.replace(jQuery(this).val() + ",","");
							$countries_list = jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_list' + $id ).val($countries_list);
    					});

						jQuery('.woocommerce_hipayenterprise_methods_lp_countries_available_' + $id + ' option:selected').remove().appendTo('.woocommerce_hipayenterprise_methods_lp_countries_' + $id).removeAttr('selected');
						return false;
					});

					jQuery('.local_payment_admin_menu').click( function(){
						$id = jQuery(this).attr("data-id");
						jQuery('.local_payment_admin_menu').removeClass("local_payment_admin_menu_sel");
						jQuery(this).addClass("local_payment_admin_menu_sel");
						jQuery('.local_payment_admin_config').addClass("hidden");
						jQuery('.local_payment_admin_config_'+$id).removeClass("hidden");
						return false;
					});


				});
			</script>

			<?php			
			return ob_get_clean();

		}




		function generate_methods_global_settings_html() {

			ob_start();
			$woocommerce_hipayenterprise_methods_mode 					= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_mode"]);
			$woocommerce_hipayenterprise_methods_capture 				= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_capture"]);
			$woocommerce_hipayenterprise_methods_3ds 					= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_3ds"]);
			$woocommerce_hipayenterprise_methods_oneclick 				= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_oneclick"]);
			$woocommerce_hipayenterprise_methods_cart_sending 			= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_cart_sending"]);
			$woocommerce_hipayenterprise_methods_keep_cart_onfail 		= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_keep_cart_onfail"]);
			$woocommerce_hipayenterprise_methods_log_info 				= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_log_info"]);
			$woocommerce_hipayenterprise_methods_hosted_mode			= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_hosted_mode"]);
			$woocommerce_hipayenterprise_methods_hosted_card_selector	= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_hosted_card_selector"]);
			$woocommerce_hipayenterprise_methods_hosted_css				= esc_textarea($this->method_details["woocommerce_hipayenterprise_methods_hosted_css"]);
			?>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Operating mode', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Operating Mode", 'hipayenterprise');?></span></legend>
					<select class="select " name="woocommerce_hipayenterprise_methods_mode" id="woocommerce_hipayenterprise_methods_mode" style="">
						<option value="hosted_page" <?php if ($woocommerce_hipayenterprise_methods_mode == "hosted_page") echo " SELECTED";?>><?php _e( 'Hosted page', 'hipayenterprise' ); ?></option>
						<option value="api" <?php if ($woocommerce_hipayenterprise_methods_mode == "api") echo " SELECTED";?>><?php _e( 'Direct Post', 'hipayenterprise' ); ?></option>
					</select>
					<p class="description"><?php _e("Api if the customer will fill his bank information directly on merchants OR Hosted if the customer is redirected to a secured payment page hosted by HiPay.",'hipayenterprise');?></p>
				</fieldset>
				</td>
			</tr>


			<tr valign="top" class="<?php if ($woocommerce_hipayenterprise_methods_mode != "hosted_page") echo "hidden ";?>hosted_page_config">
				<th scope="row" class="titledesc"><?php _e( 'Display Hosted Page', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Display Hosted Page", 'hipayenterprise');?></span></legend>
					<select class="select " name="woocommerce_hipayenterprise_methods_hosted_mode" id="woocommerce_hipayenterprise_methods_hosted_mode" style="">
						<option value="redirect" <?php if ($woocommerce_hipayenterprise_methods_hosted_mode == "redirect") echo " SELECTED";?>><?php _e( 'Redirect', 'hipayenterprise' ); ?></option>
						<option value="iframe" <?php if ($woocommerce_hipayenterprise_methods_hosted_mode == "iframe") echo " SELECTED";?>><?php _e( 'IFrame', 'hipayenterprise' ); ?></option>
					</select>
				</fieldset>
				</td>
			</tr>


			<tr valign="top" class="<?php if ($woocommerce_hipayenterprise_methods_mode != "hosted_page") echo "hidden ";?>hosted_page_config">
				<th scope="row" class="titledesc"><?php _e( 'Display card selector', 'hipayenterprise' ); ?></th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e( 'Display card selector', 'hipayenterprise' ); ?></span></legend>
						<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_hosted_card_selector" id="woocommerce_hipayenterprise_methods_hosted_card_selector" style="" value="1" <?php if ($woocommerce_hipayenterprise_methods_hosted_card_selector) echo 'checked="checked"';?>> <br>
					</fieldset>
				</td>
			</tr>

			<tr valign="top" class="<?php if ($woocommerce_hipayenterprise_methods_mode != "hosted_page") echo "hidden ";?>hosted_page_config">
				<th scope="row" class="titledesc"><?php _e( 'CSS url', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("CSS Url", 'hipayenterprise');?></span></legend>
					<input class="input-text regular-input " type="text" name="woocommerce_hipayenterprise_methods_hosted_css" id="woocommerce_hipayenterprise_methods_hosted_css" style="" value="<?php echo $woocommerce_hipayenterprise_methods_hosted_css;?>" placeholder="">
					<p class="description"><?php _e("URL to your CSS (style sheet) to customize your hosted page or iFrame (Important: the HTTPS protocol is required).",'hipayenterprise');?></p>
				</fieldset>
				</td>
			</tr>



			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Capture', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Capture", 'hipayenterprise');?></span></legend>
					<select class="select " name="woocommerce_hipayenterprise_methods_capture" id="woocommerce_hipayenterprise_methods_capture" style="">
						<option value="automatic" <?php if ($woocommerce_hipayenterprise_methods_capture == "automatic") echo " SELECTED";?>><?php _e( 'Automatic', 'hipayenterprise' ); ?></option>
						<option value="manual" <?php if ($woocommerce_hipayenterprise_methods_capture == "manual") echo " SELECTED";?>><?php _e( 'Manual', 'hipayenterprise' ); ?></option>
					</select>
					<p class="description"><?php _e("Manual if all transactions will be captured manually either from the Hipay Back office or from your admin in Woocommerce OR Automatic if all transactions will be captured automatically.",'hipayenterprise');?></p>
				</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Use Oneclick', 'hipayenterprise' ); ?></th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e( 'Use Oneclick', 'hipayenterprise' ); ?></span></legend>
						<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_oneclick" id="woocommerce_hipayenterprise_methods_oneclick" style="" value="1" <?php if ($woocommerce_hipayenterprise_methods_oneclick) echo 'checked="checked"';?>> <br>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Customer\'s cart sending', 'hipayenterprise' ); ?></th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e( 'Customer\'s cart sending', 'hipayenterprise' ); ?></span></legend>
						<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_cart_sending" id="woocommerce_hipayenterprise_methods_cart_sending" style="" value="1" <?php if ($woocommerce_hipayenterprise_methods_cart_sending) echo 'checked="checked"';?>> <br>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Keep cart when payment fails', 'hipayenterprise' ); ?></th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e( 'Keep cart when payment fails', 'hipayenterprise' ); ?></span></legend>
						<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_keep_cart_onfail" id="woocommerce_hipayenterprise_methods_keep_cart_onfail" style="" value="1" <?php if ($woocommerce_hipayenterprise_methods_keep_cart_onfail) echo 'checked="checked"';?>> <br>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Logs information', 'hipayenterprise' ); ?></th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php _e( 'Logs information', 'hipayenterprise' ); ?></span></legend>
						<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_log_info" id="woocommerce_hipayenterprise_methods_log_info" style="" value="1" <?php if ($woocommerce_hipayenterprise_methods_log_info) echo 'checked="checked"';?>> <br>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Activate 3-D Secure', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Activate 3-D Secure", 'hipayenterprise');?></span></legend>
					<select class="select " name="woocommerce_hipayenterprise_methods_3ds" id="woocommerce_hipayenterprise_methods_3ds" style="">
						<option value="0" <?php if ($woocommerce_hipayenterprise_methods_3ds == "0") echo " SELECTED";?>><?php _e( 'Deactivated', 'hipayenterprise' ); ?></option>
						<option value="1" <?php if ($woocommerce_hipayenterprise_methods_3ds == "1") echo " SELECTED";?>><?php _e( 'Try to enable for all transactions', 'hipayenterprise' ); ?></option>
						<option value="2" <?php if ($woocommerce_hipayenterprise_methods_3ds == "2") echo " SELECTED";?>><?php _e( 'Force for all transactions', 'hipayenterprise' ); ?></option>
					</select>
					<p class="description"></p>
				</fieldset>
				</td>
			</tr>			

			<tr valign="top">
				<th colspan="2" align="right"><?php submit_button(); ?></th>
			</tr>


			<script type="text/javascript">
				jQuery(function() {

					jQuery('#woocommerce_hipayenterprise_methods_mode').change( function(){

						if (jQuery(this).val() == "hosted_page"){
							jQuery(".hosted_page_config").removeClass("hidden").show();
						} else {
							jQuery(".hosted_page_config").hide();
						}

						return false;
					});

					jQuery('.rem_country').click( function(){
						$id = jQuery(this).attr("data-id");

						jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_' + $id + ' :selected').each(function(){
							$countries_list = jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_list' + $id ).val();
							$countries_list = $countries_list.replace(jQuery(this).val() + ",","");
							$countries_list = jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_list' + $id ).val($countries_list);
    					});

						jQuery('.woocommerce_hipayenterprise_methods_cc_countries_available_' + $id + ' option:selected').remove().appendTo('.woocommerce_hipayenterprise_methods_cc_countries_' + $id).removeAttr('selected');
						return false;
					});

					jQuery('.credit_card_admin_menu').click( function(){
						$id = jQuery(this).attr("data-id");
						jQuery('.credit_card_admin_menu').removeClass("credit_card_admin_menu_sel");
						jQuery(this).addClass("credit_card_admin_menu_sel");
						jQuery('.credit_card_admin_config').addClass("hidden");
						jQuery('.credit_card_admin_config_'+$id).removeClass("hidden");
						return false;
					});


				});
			</script>

			<?php
			return ob_get_clean();

		}


		function generate_faqs_details_html() {

			ob_start();
			include( plugin_dir_path( __FILE__ ) . 'includes/faqs.php' );
			return ob_get_clean();

		}



		function generate_currencies_details_html() {

			global $woocommerce;
			$currency_list = $this->list_of_currencies;  
			$woocommerce_hipayenterprise_currencies_active 		= $this->currencies_details["woocommerce_hipayenterprise_currencies_active"];
			$current_currency = get_woocommerce_currency();
			if ($woocommerce_hipayenterprise_currencies_active==null) $woocommerce_hipayenterprise_currencies_active = [];	
			ob_start();
			?>
			<tr valign="top">
				<th colspan="2" align="right"><?php submit_button(); ?></th>
			</tr>
			<?php
			foreach ($currency_list as $key => $value) {
				if (array_key_exists($key, $woocommerce_hipayenterprise_currencies_active) || ($key == $current_currency)) {
			?>	
					<tr valign="top">
						<td class="forminp" style="width:20px;">
								<input class="" type="checkbox" name="woocommerce_hipayenterprise_currencies_active[<?php echo $key; ?>]" id="woocommerce_hipayenterprise_currencies_active[<?php echo $key; ?>]" style="" value="1" <?php if (array_key_exists($key, $woocommerce_hipayenterprise_currencies_active)) echo 'checked="checked"';?>> <br>						
						</td>
						<td class="forminp">
							<?php echo $value; ?> - [<?php echo $key; ?>]
						</td>
					</tr>
			<?php
					unset($currency_list[$key]);
				}
			}
			foreach ($currency_list as $key => $value) {
			?>	
				<tr valign="top">
					<td class="forminp" style="width:20px;">
							<input class="" type="checkbox" name="woocommerce_hipayenterprise_currencies_active[<?php echo $key; ?>]" id="woocommerce_hipayenterprise_currencies_active[<?php echo $key; ?>]" style="" value="1"> <br>						
					</td>
					<td class="forminp">
						<?php echo $value; ?> - [<?php echo $key; ?>]
					</td>
				</tr>
			<?php	
			}
			return ob_get_clean();

		}

		function generate_logs_details_html() {

			global $wpdb;
			ob_start();
			?>
			<table class="wc_emails widefat" cellspacing="0"><tbody>
				<tr>
					<th class="wc-email-settings-table-name"><?php echo __("TYPE","hipayenterprise");?></th>
					<th class="wc-email-settings-table-name"><?php echo __("ORDER","hipayenterprise");?></th>
					<th class="wc-email-settings-table-name"><?php echo __("DATE","hipayenterprise");?></th>
					<th class="wc-email-settings-table-name"><?php echo __("DESCRIPTION","hipayenterprise");?></th>
				</tr>

				<?php
				$logs_list = $wpdb->get_results( "SELECT id,create_date,log_desc,order_id,type FROM $this->plugin_table_logs ORDER BY id DESC LIMIT 100");
				foreach ($logs_list as $value) {
				?>	
					<tr>
						<td ><?php echo $value->type;?></td>
						<td ><?php echo $value->order_id;?></td>
						<td ><?php echo $value->create_date;?></td>
						<td ><?php echo $value->log_desc;?></td>
					</tr>
				<?php	
				}
				?>
			</tbody></table>
			<?php
			return ob_get_clean();

		}

		function generate_fraud_details_html() {

			ob_start();
			$woocommerce_hipayenterprise_fraud_copy_to 		= esc_textarea($this->fraud_details["woocommerce_hipayenterprise_fraud_copy_to"]);
			$woocommerce_hipayenterprise_fraud_copy_method 	= esc_textarea($this->fraud_details["woocommerce_hipayenterprise_fraud_copy_method"]);
			?>
			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Copy To', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Copy To", 'hipayenterprise');?></span></legend>
					<input class="input-text regular-input " type="text" name="woocommerce_hipayenterprise_fraud_copy_to" id="woocommerce_hipayenterprise_fraud_copy_to" style="" value="<?php echo $woocommerce_hipayenterprise_fraud_copy_to;?>" placeholder="">
					<p class="description"><?php _e("Enter a valid email, during a transaction challenged an email will be sent to this address.",'hipayenterprise');?></p>
				</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Copy Method', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Copy Method", 'hipayenterprise');?></span></legend>
					<select class="select " name="woocommerce_hipayenterprise_fraud_copy_method" id="woocommerce_hipayenterprise_fraud_copy_method" style="">
						<option value="bcc"<?php if ($woocommerce_hipayenterprise_fraud_copy_method == "bcc") echo " SELECTED";?>><?php _e( 'Bcc', 'hipayenterprise' ); ?></option>
						<option value="separate_email"<?php if ($woocommerce_hipayenterprise_fraud_copy_method == "separate_email") echo " SELECTED";?>><?php _e( 'Separate email', 'hipayenterprise' ); ?></option>
					</select>
					<p class="description"><?php _e("Select Bcc if the recipient will be in copy of the email or Separate email for sending two emails.",'hipayenterprise');?></p>
				</fieldset>
				</td>
			</tr>


			<?php
			return ob_get_clean();

		}


		function process_refund( $order_id, $amount = null, $reason = '' ) {

			global $wpdb;
			global $woocommerce;

			require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

			$order = wc_get_order( $order_id );
			$order->add_order_note(__('Request refund through Hipay Enterprise for amount:', 'hipayenterprise') . " " . $amount . " " . $order->get_currency() . " and reason: " . $reason );
			
			$username 	= (!$this->sandbox) ? $this->account_production_private_username 	: $this->account_test_private_username;
			$password 	= (!$this->sandbox) ? $this->account_production_private_password	: $this->account_test_private_password;
			$passphrase = (!$this->sandbox) ? $this->account_production_private_passphrase: $this->account_test_private_passphrase;
			$env = ($this->sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

			try {

				$config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env);
				$clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
				$gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

				$transactionId = $wpdb->get_row( "SELECT reference FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");

				if (!isset($transactionId->reference) ){	
					throw new Exception(__("No transaction reference found.",'hipayenterprise'));
				} else	{
					$maintenanceResult = $gatewayClient->requestMaintenanceOperation("refund",$transactionId->reference,$amount);
					$maintenanceResultDump = print_r($maintenanceResult,true);
					if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
						$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => $maintenanceResultDump, 'order_id' => $order_id, 'type' => 'INFO' ) );
					if ($maintenanceResult->getStatus() == "124")
						return true;
				}
				return false;

			} catch (Exception $e) {
				throw new Exception(__("Error processing the Refund:",'hipayenterprise') . " " . $e->getMessage() );
				
			}	

		}


		public function admin_options() {

		    global $wpdb;

			$curl_active = false;
			$simplexml_active = false;
			$https_active = false;
			
			if (extension_loaded('curl')) 
				$curl_active = true;
			if (extension_loaded('simplexml')) 
				$simplexml_active = true;

			if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') $https_active = true;			
				
			?>
			<h3><?php _e('Payments with HiPay Enterprise', 'hipayenterprise'); ?></h3>
			<p></p>

			<table class="wc_emails widefat" cellspacing="0">
			<tbody>
			<tr>
				<td class="wc-email-settings-table-status">
					<?php
					if ($curl_active){ ?>
						<span class="dashicons dashicons-yes"></span>
					<?php
					} else	{ ?>
						<span class="dashicons dashicons-no"></span>
					<?php
					}	?>
				</td>

				<td class="wc-email-settings-table-name"><?php _e( 'cURL Extension', 'hipayenterprise' ); ?></td>
				
				<td>
                    <?php
					if (!$curl_active) 
						_e( 'Please install and activate cURL extension.', 'hipayenterprise' );       
					else
						_e( 'cURL Extension is correcly installed.', 'hipayenterprise' );       

					?>
				</td>
			</tr>

			<tr>
				<td class="wc-email-settings-table-status">
					<?php
					if ($simplexml_active){ ?>
						<span class="dashicons dashicons-yes"></span>
					<?php
					} else	{ ?>
						<span class="dashicons dashicons-no"></span>
					<?php
					}	?>
				</td>
				<td class="wc-email-settings-table-name"><?php _e( 'SimpleXML Extension', 'hipayenterprise' ); ?></td>
				<td>
                    <?php
					if (!$simplexml_active) 
						_e( 'Please install and activate SimpleXML Extension.', 'hipayenterprise' );       
					else
						_e( 'SimpleXML Extension is correcly installed.', 'hipayenterprise' );       
					?>
				</td>
			</tr>

			<?php
			if ($this->sandbox == "yes") {
			?>	
				<tr>
					<td class="wc-email-settings-table-status">
						<span class="dashicons dashicons-warning" style="color:orange;"></span>
					</td>
					<td class="wc-email-settings-table-name"><?php _e( 'Mode TEST activated', 'hipayenterprise' ); ?></td>
					<td>
	                    <?php
						_e( 'This plugin is configured to use a Test Account.', 'hipayenterprise' );       
						?>
					</td>
				</tr>
			<?php
			}
			?>
			<tr>
				<td class="wc-email-settings-table-status">
					<span class="dashicons <?php echo  $https_active ? 'dashicons-lock' : 'dashicons-warning';?>" <?php echo (!$https_active) ? 'style="color:orange"' : '';?>;></span>
				</td>
				<td class="wc-email-settings-table-name"><?php _e( 'SSL Certificate', 'hipayenterprise' ); ?></td>
				<td>
	                <?php
					if (!$https_active) _e( 'You need a SSL Certificate to process credit card paymets using HiPay.', 'hipayenterprise' );       
					?>
				</td>
			</tr>

			<tr>
				<td class="wc-email-settings-table-status">
					<span class="dashicons dashicons-wordpress"></span>
				</td>
				<td class="wc-email-settings-table-name"><?php _e( 'Woocommerce REST API', 'hipayenterprise' ); ?></td>
				<td>
	                <?php
					_e( 'Please ensure you have Woocommerce REST API activated.', 'hipayenterprise' );       
					?>
				</td>
			</tr>


			</tbody></table>




		    <div class="wrap">
		         
		        <h2 class="nav-tab-wrapper">
		            <a href="#accounts" id="accounts-tab" class="nav-tab hipayenterprise-tab" data-toggle="accounts"><i class="dashicons dashicons-admin-generic"></i> <?php _e("Plugin Settings");?></a>
		            <a href="#methods" id="methods-tab" class="nav-tab hipayenterprise-tab" data-toggle="methods"><span class="dashicons dashicons-cart"></span> <?php _e("Payment Methods");?></a>
		            <a href="#fraud" id="fraud-tab" class="nav-tab hipayenterprise-tab" data-toggle="fraud"><span class="dashicons dashicons-warning"></span> <?php _e("Fraud");?></a>
		            <a href="#currencies" id="currencies-tab" class="nav-tab hipayenterprise-tab" data-toggle="currencies"><span class="dashicons dashicons-category"></span> <?php _e("Currencies");?></a>
		            <a href="#faqs" id="faqs-tab" class="nav-tab hipayenterprise-tab" data-toggle="faqs"><span class="dashicons dashicons-admin-comments"></span> <?php _e("FAQ");?></a>
		            <a href="#logs" id="logs-tab" class="nav-tab hipayenterprise-tab" data-toggle="logs"><span class="dashicons dashicons-admin-page"></span> <?php _e("LOGS");?></a>
		        </h2>
		         

				<div id="accounts" class="hidden hipayenterprise-tab-content">	
				<table class="form-table">
				<?php
				$this->generate_settings_html($this->form_fields);
				?>
				</table>
				</div>
				<div id="methods" class="hidden hipayenterprise-tab-content">	
				<table class="form-table">
				<?php
				$this->generate_settings_html($this->methods);
				?>
				</table>
				</div>
				<div id="fraud" class="hidden hipayenterprise-tab-content">	
				<table class="form-table">
				<?php
				$this->generate_settings_html($this->fraud);
				?>
				</table>
				</div>
				<div id="faqs" class="hidden hipayenterprise-tab-content">	
				<table class="form-table">
				<?php
				$this->generate_settings_html($this->faqs);
				?>
				</table>
				</div>
				<div id="logs" class="hidden hipayenterprise-tab-content">	
				<table class="form-table">
				<?php
				$this->generate_settings_html($this->logs);
				?>
				</table>
				</div>
				<div id="currencies" class="hidden hipayenterprise-tab-content">	
				<table class="form-table">
				<?php
				$this->generate_settings_html($this->currencies);
				?>
				</table>
				</div>

		         
		    </div><!-- /.wrap -->

			<script type="text/javascript">
				jQuery(function() {


					$hipayTab = window.location.hash;
					if ($hipayTab == ""){
						jQuery("#accounts").removeClass("hidden").show(111);
						jQuery("#accounts-tab").addClass("nav-tab-active");
					} else {
						jQuery($hipayTab).removeClass("hidden").show(111);
						jQuery($hipayTab+"-tab").addClass("nav-tab-active");
					}	
					jQuery('.hipayenterprise-tab').click( function(event){
						event.preventDefault();
						var tab = jQuery(this).attr('data-toggle');
						window.location.hash = tab;
						jQuery('.hipayenterprise-tab-content').hide();
						jQuery("#"+tab).removeClass("hidden").show(111);
						jQuery('.hipayenterprise-tab').removeClass("nav-tab-active");
						jQuery(this).addClass("nav-tab-active");
						return false;
					});
				});
			</script>
	
			<?php
		}


		function thanks_page($order_id) {

			global $woocommerce;

		}


	    function process_payment( $order_id ) {

			global $woocommerce;
		    global $wpdb;

		    $token =  $_POST["hipay_token"];
			$order = new WC_Order( $order_id );


		    if ($this->method_details["woocommerce_hipayenterprise_methods_mode"] == "api" && $token == "") {
				return;
		    } elseif ( $this->method_details["woocommerce_hipayenterprise_methods_mode"] == "api" && $token != "") {

			    $hipay_direct_error =  $_POST["hipay_direct_error"];
			    if ($hipay_direct_error != "") 	throw new Exception($hipay_direct_error, 1);

			    $brand =  $_POST["hipay_brand"];
			    $hipay_delete_token =  $_POST["hipay_delete_info"];
			    $user_multiuse =  $_POST["hipay_multiuse"];
			    $customer_id = $order->get_user_id();

				if ( $customer_id > 0 && $hipay_delete_token != "") {
					$wpdb->delete( $this->plugin_table_token, array( 'customer_id' => $customer_id, 'token' => $hipay_delete_token ) );
				}	


				if ( $this->method_details["woocommerce_hipayenterprise_methods_oneclick"] == "1" && $customer_id > 0 && $user_multiuse == "1" && $token != "") {

				    $issuer =  $_POST["hipay_issuer"];
				    $pan =  $_POST["hipay_pan"];
				    $card_expiry_month =  $_POST["hipay_card_expiry_month"];
				    $card_expiry_year =  $_POST["hipay_card_expiry_year"];
				    $card_holder =  $_POST["hipay_card_holder"];
				    $country =  $_POST["hipay_country"];
					$direct_post_eci = 7;
					$token_flag = $wpdb->get_row( "SELECT id FROM $this->plugin_table_token WHERE customer_id = $customer_id LIMIT 1");
					if (isset($token_flag->id) ){
						$wpdb->update( $this->plugin_table_token, array( 'brand' => $brand , 'pan' => $pan, 'card_holder' => $card_holder, 'card_expiry_month' => $card_expiry_month, 'card_expiry_year' => $card_expiry_year, 'token' => $token, 'issuer' => $issuer, 'country' => $country ), array('customer_id' => $customer_id ) );
						$direct_post_eci = 9;
					} else	{
						$wpdb->insert( $this->plugin_table_token, array( 'customer_id' => $customer_id, 'brand' => $brand , 'pan' => $pan, 'card_holder' => $card_holder, 'card_expiry_month' => $card_expiry_month, 'card_expiry_year' => $card_expiry_year, 'token' => $token, 'issuer' => $issuer, 'country' => $country ) );
					}
				}	
		    } 		    	

			$order_total = $order->get_total();
			require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

			$username 	= (!$this->sandbox) ? $this->account_production_private_username 	: $this->account_test_private_username;
			$password 	= (!$this->sandbox) ? $this->account_production_private_password	: $this->account_test_private_password;
			$passphrase = (!$this->sandbox) ? $this->account_production_private_passphrase: $this->account_test_private_passphrase;

			$env = ($this->sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

			$operation = "Sale";
			if ($this->method_details['woocommerce_hipayenterprise_methods_capture'] == "manual") $operation = "Authorization";
			$callback_url = site_url().'/wc-api/WC_HipayEnterprise/?order=' . $order_id;
			$current_currency = get_woocommerce_currency();
			$current_billing_country = $woocommerce->customer->get_billing_country();
			$billing_email = $woocommerce->customer->get_billing_email();
			$shop_title = get_bloginfo( 'name' );
			
			$request_source = '{"source":"CMS","brand":"Woocommerce","brand_version":"'.$this->woocommerce_version.'","integration_version":"'.$this->plugin_version.'"}';

			try {

				$config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env);
				$clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
				$gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
				if ($this->method_details["woocommerce_hipayenterprise_methods_mode"] == "api"){
					$orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();
					$orderRequest->paymentMethod = new \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod(); 
					$orderRequest->paymentMethod->cardtoken = $token;
					if ($user_multiuse == "0")
						$orderRequest->paymentMethod->eci = 7;
					else
						$orderRequest->paymentMethod->eci = $direct_post_eci;
					$orderRequest->paymentMethod->authentication_indicator = $this->method_details['woocommerce_hipayenterprise_methods_3ds'];
					$orderRequest->payment_product = $brand;

				}else{
					$orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest();
					$orderRequest->payment_product = "";
				}
				

				$orderRequest->orderid = $order_id;
				$orderRequest->operation = $operation;
				$orderRequest->currency = $current_currency;
				$orderRequest->amount = $order_total;
				$orderRequest->accept_url = $order->get_checkout_order_received_url();
				$orderRequest->decline_url = $order->get_cancel_order_url_raw();
				$orderRequest->pending_url = $order->get_checkout_order_received_url();
				$orderRequest->exception_url = $order->get_cancel_order_url_raw();
				$orderRequest->cancel_url = $order->get_cancel_order_url_raw();
				$orderRequest->notify_url = $callback_url;
				$orderRequest->language = get_locale();
				$orderRequest->source = $request_source;
				if ($this->method_details["woocommerce_hipayenterprise_methods_cart_sending"]) {

					$orderRequest->description = "";
					$products = $order->get_items();
					foreach ( $products as $product ) 
					{
						//$variation_id = (int)$product['variation_id'];
						$p = New WC_Product( $product['product_id']);
						$orderRequest->description .= $product['qty'] . "x " . $p->get_title() . ', ';
					}
					$orderRequest->description 	= substr($orderRequest->description,0, -2);
				}
				else 
				{
					$orderRequest->description = __("Order #",'hipayenterprise') . $order_id . " " . __( 'at','hipayenterprise') . " " . $shop_title;
				}
				$orderRequest->ipaddr = $_SERVER ['REMOTE_ADDR']; 
				$orderRequest->http_user_agent = $_SERVER ['HTTP_USER_AGENT'];
				$shipping = $woocommerce->cart->get_cart_shipping_total();
				$currency_symbol = get_woocommerce_currency_symbol();	
				$shipping = str_replace($currency_symbol,"", $shipping);
				$thousands_sep = wp_specialchars_decode(stripslashes(get_option( 'woocommerce_price_thousand_sep')), ENT_QUOTES);
				$shipping = str_replace($thousands_sep,"", $shipping);
				$decimals_sep = wp_specialchars_decode(stripslashes(get_option( 'woocommerce_price_decimal_sep')), ENT_QUOTES);
				if ( $decimals_sep != ".") $shipping = str_replace($decimals_sep,".", $shipping);
				$shipping = floatval( preg_replace( '#[^\d.]#', '',  $shipping) );
				$orderRequest->shipping = $shipping;
				$orderRequest->tax =0; 


				if ($this->method_details["woocommerce_hipayenterprise_methods_mode"] != "api"){

					$orderRequest->authentication_indicator = $this->method_details['woocommerce_hipayenterprise_methods_3ds'];

					if ($this->method_details['woocommerce_hipayenterprise_methods_hosted_mode']=="redirect") 
						$orderRequest->template = "basic-js";
					else
						$orderRequest->template = "iframe-js";

					$orderRequest->display_selector = (int)$this->method_details['woocommerce_hipayenterprise_methods_hosted_card_selector'];
					$orderRequest->multi_use 		= (int)$this->method_details['woocommerce_hipayenterprise_methods_oneclick'];
					if ($this->method_details['woocommerce_hipayenterprise_methods_hosted_css']!="") $orderRequest->css = $this->woocommerce_hipayenterprise_methods['woocommerce_hipayenterprise_methods_hosted_css'];
				}
					
				//check max min amount
				$all_methods 		= json_decode($this->method_details['woocommerce_hipayenterprise_methods_payments']);
				$max_amount = 0;
				$min_amount = -1;
				$countries_list = array();
				$currencies_list = array();
				$available_methods = array();

				foreach ($all_methods as $key => $value) {
					$the_method = new HipayEnterprisePaymentMethodClass($value);
					//check currency, country and amount
					if ($the_method->get_is_active() && $the_method->get_is_credit_card() && $order_total <= $the_method->get_max_amount() && $order_total >= $the_method->get_min_amount() && (strpos($the_method->get_available_currencies(),$current_currency) !== false)  && (strpos($the_method->get_available_countries(),$current_billing_country) !== false))	{
						$available_methods[] = $the_method->get_key();
					}	
				}
				if ($this->method_details["woocommerce_hipayenterprise_methods_mode"] != "api"){
					$orderRequest->payment_product_list = implode(",", $available_methods);
					$orderRequest->payment_product_category_list = '';
				}
					
				$orderRequest->email 		= $order->get_billing_email();

				$customerBillingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest();
				$customerBillingInfo->firstname = $order->get_billing_first_name();
				$customerBillingInfo->lastname 	= $order->get_billing_last_name();
				$customerBillingInfo->email 	= $order->get_billing_email();
				$customerBillingInfo->country 	= $order->get_billing_country();
				$customerBillingInfo->streetaddress = $order->get_billing_address_1();
				$customerBillingInfo->streetaddress2 	= $order->get_billing_address_2();
				$customerBillingInfo->city 	= $order->get_billing_city();
				$customerBillingInfo->state 	= $order->get_billing_state();
				$customerBillingInfo->zipcode 	= $order->get_billing_postcode();
				$orderRequest->customerBillingInfo 	= $customerBillingInfo;

				$customerShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest();
				$customerShippingInfo->firstname = $order->get_shipping_first_name();
				$customerShippingInfo->lastname 	= $order->get_shipping_last_name();
				$customerShippingInfo->country 	= $order->get_shipping_country();
				$customerShippingInfo->streetaddress = $order->get_shipping_address_1();
				$customerShippingInfo->streetaddress2 	= $order->get_shipping_address_2();
				$customerShippingInfo->city 	= $order->get_shipping_city();
				$customerShippingInfo->state 	= $order->get_shipping_state();
				$customerShippingInfo->zipcode 	= $order->get_shipping_postcode();
				$orderRequest->customerShippingInfo 	= $customerShippingInfo;

				$orderRequest->shipto_firstname 	= $order->get_shipping_first_name();
				$orderRequest->shipto_lastname 	= $order->get_shipping_last_name();
				$orderRequest->shipto_streetaddress 	= $order->get_shipping_address_1();
				$orderRequest->shipto_streetaddress2 	= $order->get_shipping_address_2();
				$orderRequest->shipto_city 	= $order->get_shipping_city();
				$orderRequest->shipto_country 	= $order->get_shipping_country();
				$orderRequest->shipto_state 	= $order->get_shipping_state();
				$orderRequest->shipto_postcode 	= $order->get_shipping_postcode();
 				
				if ($this->method_details["woocommerce_hipayenterprise_methods_mode"] != "api"){
 					$transaction = $gatewayClient->requestHostedPaymentPage($orderRequest);			
					$redirectUrl = $transaction->getForwardUrl();				
					if ($redirectUrl != ""){
						$order->add_order_note(__('Payment URL:', 'hipayenterprise') . " " . $redirectUrl );
				    	if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
							$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __('Payment URL:', 'hipayenterprise') . " " . $redirectUrl, 'order_id' => $order_id, 'type' => 'INFO' ) );

						$order_flag = $wpdb->get_row( "SELECT order_id FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");
						if (isset($order_flag->order_id) ){
							SELF::reset_stock_levels($order);
							wc_reduce_stock_levels( $order_id );
							$wpdb->update( $this->plugin_table, array( 'amount' => $order_total , 'stocks' => 1, 'url' => $redirectUrl ), array('order_id' => $order_id ) );
						} else	{
							wc_reduce_stock_levels( $order_id );
							$wpdb->insert( $this->plugin_table, array( 'reference' => 0, 'order_id' => $order_id, 'amount' => $order_total , 'stocks' => 1, 'url' => $redirectUrl ) );
						}
						
						if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
							$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Payment created with url:","hipayenterprise") . " " . $redirectUrl, 'order_id' => $order_id, 'type' => 'INFO' ) );

						if ($this->method_details['woocommerce_hipayenterprise_methods_hosted_mode'] == "iframe")
							return array(
								'result'   => 'success',
								'redirect' => $order->get_checkout_payment_url( true )
							);
						else
					    	return array('result' => 'success','redirect' =>  $redirectUrl );


				    } else {
				    	if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
							$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __('Error generating payment url.','hipayenterprise'), 'order_id' => $order_id, 'type' => 'ERROR' ) );
						throw new Exception(__('Error generating payment url.','hipayenterprise'));			    
				    }	
				} else {

 					$transaction = $gatewayClient->requestNewOrder($orderRequest);			
					$redirectUrl = $transaction->getForwardUrl();

 					if ($transaction->getStatus() == "118" || $transaction->getStatus() == "117" || $transaction->getStatus() == "116") {					

						$order_flag = $wpdb->get_row( "SELECT order_id FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");
						if (isset($order_flag->order_id) ){
							SELF::reset_stock_levels($order);
							wc_reduce_stock_levels( $order_id );
							$wpdb->update( $this->plugin_table, array( 'amount' => $order_total , 'stocks' => 1, 'url' => $redirectUrl ), array('order_id' => $order_id ) );
						} else	{
							wc_reduce_stock_levels( $order_id );
							$wpdb->insert( $this->plugin_table, array( 'reference' => 0, 'order_id' => $order_id, 'amount' => $order_total , 'stocks' => 1, 'url' => $redirectUrl ) );
						}


						return array(
							'result'   => 'success',
							'redirect' => $order->get_checkout_order_received_url()
						);

					} else {

						$reason = $transaction->getReason();
						$order->add_order_note(__('Error:', 'hipayenterprise') . " " . $reason['message'] );
						throw new Exception(__('Error processing payment:' ,'hipayenterprise') . " " . $reason['message']);			    

					}	


				}

			} catch (Exception $e) {
				if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
					$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Error on creation:","hipayenterprise") . " " . $e->getMessage(), 'order_id' => $order_id, 'type' => 'ERROR' ) );
				throw new Exception($e->getMessage());			    
			}

    	}


		public function receipt_page( $order_id ) {
			global $wpdb;

			$order 			= wc_get_order( $order_id );
			$payment_url 	= $wpdb->get_row("SELECT url FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");

			if (!isset($payment_url->url) )	

					$order->get_cancel_order_url_raw();

			elseif ($this->method_details["woocommerce_hipayenterprise_methods_mode"] == "api" && $payment_url->url == "")

				echo __("We have received your order payment. We will process the order as soon as we get the payment confirmation.","hipayenterprise");	

			else		

				echo '<div id="wc_hipay_iframe_container"><iframe id="wc_hipay_iframe" name="wc_hipay_iframe" width="100%" height="475" style="border: 0;" src="'.$payment_url->url.'" allowfullscreen="" frameborder="0"></iframe></div>' . PHP_EOL;

		}


    	static function reset_stock_levels($order){

			global $woocommerce;
			global $wpdb;

			$products = $order->get_items();
			foreach ( $products as $product ) 
			{

				$qt = $product['qty'];
				$product_id = $product['product_id'];
				$variation_id = (int)$product['variation_id'];
				
				if ($variation_id > 0 ) {
					$pv = New WC_Product_Variation( $variation_id );
					if ($pv->managing_stock()){
						$pv->increase_stock($qt);
					} else {
						$p = New WC_Product( $product_id );
						$p->increase_stock($qt);
					}

				} else {
					$p = New WC_Product( $product_id );
					$p->increase_stock($qt);
				}
			}
    	}


    	static function get_order_information($order_id){

			global $wpdb;
			global $woocommerce;

			require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
			
			$plugin_option =get_option( 'woocommerce_hipayenterprise_settings');

			$username 	= (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_username'] 	 : $plugin_option['account_test_private_username'];
			$password 	= (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_password']	 : $plugin_option['account_test_private_password'];
			$passphrase = (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_passphrase'] : $plugin_option['account_test_private_passphrase'];

			$env = ($plugin_option['sandbox']) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

			try {

				$config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env);
				$clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
				$gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

				//$maintenanceResult = new \HiPay\Fullservice\Gateway\Mapper\TransactionMapper();
				$maintenanceResult = $gatewayClient->requestOrderTransactionInformation($order_id);
				return $maintenanceResult;

			} catch (Exception $e) {
				throw new Exception(__("Error getting order information.",'hipayenterprise') . " " . $e->getMessage() );
				
			}	



    	}

		function check_callback_response() {

			global $woocommerce;
			global $wpdb;

			$notification = $_POST;
			$notification_text = print_r($notification,true);

			$state 					= $notification["state"];
			$message 				= $notification["message"];
			$status 				= $notification["status"];
			$test 					= $notification["test"];
			$transaction_reference	= $notification["transaction_reference"];
			$order_id 				= $notification["order"]["id"];
			$authorizedAmount		= $notification["authorized_amount"];
			$capturedAmount			= $notification["captured_amount"];
			$currency				= $notification["currency"];

			try {
				$order = new WC_Order( $order_id );	

				if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
						$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Callback content:","hipayenterprise") . " " . $notification_text, 'order_id' => $order_id, 'type' => 'INFO' ) );

				if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
					$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => $message . " " . __("Callback received for transation:","hipayenterprise") . " " . $transaction_reference, 'order_id' => $order_id, 'type' => 'INFO' ) );



				if ($state = "completed" && $status == 118){

					$token 					= $notification["payment_method"]["token"];
					$brand 					= $notification["payment_method"]["brand"];
					$pan 					= $notification["payment_method"]["pan"];
					$card_holder 			= $notification["payment_method"]["card_holder"];
					$card_expiry_month 		= $notification["payment_method"]["card_expiry_month"];
					$card_expiry_year 		= $notification["payment_method"]["card_expiry_year"];
					$issuer 				= $notification["payment_method"]["issuer"];
					$country 				= $notification["payment_method"]["country"];	
					
					if ($capturedAmount == $authorizedAmount){

						$wpdb->update( $this->plugin_table , array( 'captured' => 1, 'reference' => $transaction_reference, 'status' => $status,'operation' => $message ,'processed' => 1, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id, 'processed' => 0 ) );
						$order->update_status('processing', __("Payment successful for transaction", 'hipayenterprise' ) . " " . $transaction_reference, 0 );
						if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
							$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Payment captured and Order changed to processing status.","hipayenterprise"), 'order_id' => $order_id, 'type' => 'INFO' ) );
					} else {
						$wpdb->update( $this->plugin_table , array( 'reference' => $transaction_reference, 'status' => $status,'operation' => $message ,'processed' => 1, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id, 'processed' => 0 ) );
						$order->update_status('on-hold', __("Payment partially captured, amount:." ." " . $capturedAmount . " " . $currency, 'hipayenterprise' ) . " " . $transaction_reference, 0 );
						if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
							$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Payment partially captured, amount:." ." " . $capturedAmount. " " . $currency,"hipayenterprise"), 'order_id' => $order_id, 'type' => 'INFO' ) );
					}				

				} elseif ($state = "completed" && $status == 116 && $this->method_details["woocommerce_hipayenterprise_methods_capture"] == "manual"){
					$wpdb->update( $this->plugin_table , array( 'reference' => $transaction_reference, 'status' => $status,'operation' => $message ,'processed' => 0, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id, 'processed' => 0 ) );				
					$order->update_status('on-hold', __("Waiting for manual capture. Authorization successful for transaction.", 'hipayenterprise' ) . " " . $transaction_reference, 0 );
					if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
						$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Payment authorized and Order changed to on-hold status, waiting for capture.","hipayenterprise"), 'order_id' => $order_id, 'type' => 'INFO' ) );
				} elseif ($status==113) {
					//reset_stock_levels($order);
					$wpdb->update( $this->plugin_table , array( 'reference' => $transaction_reference, 'status' => $status,'operation' => $message ,'processed' => 0, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id, 'processed' => 0 ) );				
					$order->update_status('cancelled', __("Authorization failed. Order was cancelled with transaction:", 'hipayenterprise' ) . " " . $transaction_reference, 0 );
					if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
						$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Authorization failed. Order was cancelled.","hipayenterprise"), 'order_id' => $order_id, 'type' => 'INFO' ) );
				} elseif ($status==115) {
					//reset_stock_levels($order);
					$wpdb->update( $this->plugin_table , array( 'reference' => $transaction_reference, 'status' => $status,'operation' => $message ,'processed' => 0, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id, 'processed' => 0 ) );				
					$order->update_status('cancelled', __("Authorization cancelled. Order was cancelled with transaction:", 'hipayenterprise' ) . " " . $transaction_reference, 0 );
					if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
						$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Authorization cancelled. Order was cancelled.","hipayenterprise"), 'order_id' => $order_id, 'type' => 'INFO' ) );
				} else{
					if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
						$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => $status . ": " . $message . " (" . $state . " ".$transaction_reference.")", 'order_id' => $order_id, 'type' => 'INFO' ) );
				}	
				return true;
				
			} catch (Exception $e) {
				$order->add_order_note($e->getMessage() );
				if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
					$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Error on creation:","hipayenterprise") . " " . $e->getMessage(), 'order_id' => $order_id, 'type' => 'ERROR' ) );

				throw new Exception($e->getMessage());			    

				
			}


		}

	}	


	function filter_hipayenterprise_gateway( $methods ) {
		
		global $woocommerce;
		global $wpdb;

		if (isset($woocommerce->cart)){

			$current_currency = get_woocommerce_currency();
			$current_billing_country = $woocommerce->customer->get_billing_country();
			$plugin_method_settings =get_option( 'woocommerce_hipayenterprise_methods');
			$min_value = $plugin_method_settings['woocommerce_hipayenterprise_methods_payments_min_amount'];
			$max_value = $plugin_method_settings['woocommerce_hipayenterprise_methods_payments_max_amount'];

			$currency_symbol = get_woocommerce_currency_symbol();
			$total_amount = $woocommerce->cart->get_total();
			$total_amount = str_replace($currency_symbol,"", $total_amount);
			$thousands_sep = wp_specialchars_decode(stripslashes(get_option( 'woocommerce_price_thousand_sep')), ENT_QUOTES);
			$total_amount = str_replace($thousands_sep,"", $total_amount);
			$decimals_sep = wp_specialchars_decode(stripslashes(get_option( 'woocommerce_price_decimal_sep')), ENT_QUOTES);
			if ( $decimals_sep != ".") $total_amount = str_replace($decimals_sep,".", $total_amount);
			$total_amount = floatval( preg_replace( '#[^\d.]#', '',  $total_amount) );

    		if (in_array($current_currency, $plugin_method_settings['woocommerce_hipayenterprise_methods_payments_currencies_list']) && in_array($current_billing_country, $plugin_method_settings['woocommerce_hipayenterprise_methods_payments_countries_list']) ) {
					if ($total_amount > $max_value || $total_amount < $min_value ) unset($methods['hipayenterprise']); 
			} else	{
				unset($methods['hipayenterprise']); 
			}

			$local_payment_filter = $plugin_method_settings['woocommerce_hipayenterprise_methods_payments_local_payments_filter'];
			foreach ($local_payment_filter as $key => $value) {
	    		
				if ($value["enabled"] == 0 ) {
					unset($methods['hipayenterprise_'.$key]);
	    		} elseif (in_array($current_currency, $value['available_currencies']) && in_array($current_billing_country, $value['available_countries']) ) {
						if ($total_amount > $value['max_amount'] || $total_amount < $value['min_amount'] ) unset($methods['hipayenterprise_'.$key]); 
				} else	{
					unset($methods['hipayenterprise_'.$key]); 
				}
						
			}

		}

		return $methods;
	}


	function add_hipayenterprise_gateway( $methods ) {

		$methods[] = 'WC_HipayEnterprise'; 
		$methods[] = 'WC_HipayEnterprise_LocalPayments_Paypal'; 
		$methods[] = 'WC_HipayEnterprise_LocalPayments_Belfius'; 
		$methods[] = 'WC_HipayEnterprise_LocalPayments_Multibanco'; 
		$methods[] = 'WC_HipayEnterprise_LocalPayments_Giropay'; 
		$methods[] = 'WC_HipayEnterprise_LocalPayments_Inghomepay'; 
		$methods[] = 'WC_HipayEnterprise_LocalPayments_Ideal'; 

		return $methods;
		
	}


	function update_stocks_cancelled_order_hipay_enterprise( $order_id,  $order  ){

		global $woocommerce;
		global $wpdb;

		$cur_payment_method = get_post_meta( $order_id, '_payment_method', true );
		if ( $cur_payment_method == 'hipayenterprise' ) {
			$stock_flag = $wpdb->get_row("SELECT stocks FROM ".$wpdb->prefix ."woocommerce_hipayenterprise WHERE order_id = $order_id LIMIT 1");
			if (isset($stock_flag->stocks) && $stock_flag->stocks == 1)	
					WC_HipayEnterprise::reset_stock_levels($order);
		}
	}


	function update_status_order_hipay_enterprise( $order_id,  $order  ){

		global $woocommerce;
		global $wpdb;

		$cur_payment_method = get_post_meta( $order_id, '_payment_method', true );

		if ( $cur_payment_method == 'hipayenterprise' ) {
			$order = new WC_Order( $order_id );	
			//capture status in db
			$captured_flag = $wpdb->get_row("SELECT captured,reference FROM ".$wpdb->prefix ."woocommerce_hipayenterprise WHERE order_id = $order_id LIMIT 1");
			if ($captured_flag->captured == 1) return true;	

			require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';		
			require plugin_dir_path( __FILE__ ) . 'includes/operations.php';		
			$plugin_option =get_option( 'woocommerce_hipayenterprise_settings');
			$username 	= (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_username'] 	 : $plugin_option['account_test_private_username'];
			$password 	= (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_password']	 : $plugin_option['account_test_private_password'];
			$passphrase = (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_passphrase'] : $plugin_option['account_test_private_passphrase'];
			$env = ($plugin_option['sandbox']) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;
			$env_endpoint = ($plugin_option['sandbox']) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENDPOINT_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENDPOINT_PROD;
			$order_total = $order->get_total();

			try {
				$res = HipayEnterpriseWooOperation::get_details_by_order($username,$password,$passphrase,$env_endpoint,$order_id);
				
				if ( $res->transaction->captured_amount < $order_total ){

					//try to capture amount (total or partial)
					$amount_to_capture = $order_total - $res->transaction->captured_amount;
					$order->add_order_note( __('Try to capture amount:','hipayenterprise') . " " . $amount_to_capture . " " . $res->transaction->currency );			
					$config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env);
					$clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
					$gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

					$operationResult = $gatewayClient->requestMaintenanceOperation("capture",$captured_flag->reference,$amount_to_capture);
					$order->add_order_note( __('Capture amount:','hipayenterprise') . " " . $amount_to_capture . " " . $res->transaction->currency  . " " . __('returned','hipayenterprise') . " " . $operationResult->getStatus() . " " . $operationResult->getMessage());			
					if ( $operationResult->getCapturedAmount() == $operationResult->getauthorizedAmount())			
						$wpdb->update( $wpdb->prefix ."woocommerce_hipayenterprise" , array( 'captured' => 1, 'status' => $operationResult->getStatus()  ,'operation' => $operationResult->getMessage()  , 'processed' => 1, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id,'captured' => 0 ) );
				}
				else {

					$wpdb->update( $wpdb->prefix ."woocommerce_hipayenterprise" , array( 'captured' => 1, 'processed' => 1, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id,'captured' => 0 ) );

				}
				
			} catch (Exception $e) {
				$order->add_order_note( $e->getMessage());
			}

		}

	}


	add_filter('woocommerce_available_payment_gateways', 'filter_hipayenterprise_gateway' );
	add_filter('woocommerce_payment_gateways', 'add_hipayenterprise_gateway' );
	add_action('woocommerce_order_status_pending_to_cancelled', 'update_stocks_cancelled_order_hipay_enterprise', 10, 2 );
	add_action('woocommerce_order_status_pending_to_failed', 'update_stocks_cancelled_order_hipay_enterprise', 10, 2 );
	add_action('woocommerce_order_status_on-hold_to_cancelled', 'update_stocks_cancelled_order_hipay_enterprise', 10, 2 );
	add_action('woocommerce_order_status_on-hold_to_failed', 'update_stocks_cancelled_order_hipay_enterprise', 10, 2 );
	add_action('woocommerce_order_status_processing_to_cancelled', 'update_stocks_cancelled_order_hipay_enterprise', 10, 2 );
	add_action('woocommerce_order_status_processing_to_failed', 'update_stocks_cancelled_order_hipay_enterprise', 10, 2 );

	add_action('woocommerce_order_status_on-hold_to_processing', 'update_status_order_hipay_enterprise', 10, 2 );
	add_action('woocommerce_order_status_on-hold_to_completed', 'update_status_order_hipay_enterprise', 10, 2 );

	function custom_checkout_field_hipay_enterprise( $checkout ) {

		echo "<div id='hipay_dp' style='display:none;'>";
	    woocommerce_form_field( 'hipay_token', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_token' ));
	    woocommerce_form_field( 'hipay_brand', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_brand' ));
	    woocommerce_form_field( 'hipay_pan', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_pan' ));
	    woocommerce_form_field( 'hipay_card_holder', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_card_holder' ));
	    woocommerce_form_field( 'hipay_card_expiry_month', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_card_expiry_month' ));
	    woocommerce_form_field( 'hipay_card_expiry_year', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_card_expiry_year' ));
	    woocommerce_form_field( 'hipay_issuer', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_issuer' ));
	    woocommerce_form_field( 'hipay_country', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_country' ));
	    woocommerce_form_field( 'hipay_multiuse', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_multiuse' ));
	    woocommerce_form_field( 'hipay_direct_error', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_direct_error' ));

	    woocommerce_form_field( 'hipay_delete_info', array(
	        'type'          => 'text',
	        ), $checkout->get_value( 'hipay_delete_info' ));

	    echo "</div>";

	}
	add_action( 'woocommerce_after_order_notes', 'custom_checkout_field_hipay_enterprise' );

}
