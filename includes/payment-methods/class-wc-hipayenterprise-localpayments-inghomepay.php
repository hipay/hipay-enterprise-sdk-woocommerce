<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles ING Home'Pay payment method.
 * @extends WC_HipayEnterprise
 * @since 1.0.0
 */
class WC_HipayEnterprise_LocalPayments_Inghomepay extends WC_HipayEnterprise {

	public function __construct() {

		global $woocommerce;
		global $wpdb;

		$this->payment_code			= 'ing-homepay';		
		$this->id                   = 'hipayenterprise_inghomepay';
		$plugin_data 				= get_plugin_data( __FILE__ );

		load_plugin_textdomain( $this->id, false, basename( dirname( __FILE__ ) ) . '../../languages' ); 
		include_once( plugin_dir_path( __FILE__ ) . '../payment_methods.php' );
		include_once( plugin_dir_path( __FILE__ ) . '../base_config.php' );

		$this->method_title         = __("ING Home'Pay",'hipayenterprise');
		$this->supports             = array('products');
		$this->plugin_table 									= $wpdb->prefix . 'woocommerce_hipayenterprise';
		$this->plugin_table_logs 								= $wpdb->prefix . 'woocommerce_hipayenterprise_logs';
		$this->plugin_table_token								= $wpdb->prefix . 'woocommerce_hipayenterprise_token';
		$this->has_fields 										= true;

		$this->init_form_fields();
		$this->init_settings();

		$plugin_option =get_option( 'woocommerce_hipayenterprise_settings');

		$this->sandbox 											= $plugin_option["sandbox"];
		$this->account_production_private_username 				= $plugin_option['account_production_private_username'];
		$this->account_production_private_password 				= $plugin_option['account_production_private_password'];
		$this->account_production_private_passphrase 			= $plugin_option['account_production_private_passphrase'];
		$this->account_production_tokenization_username 		= $plugin_option['account_production_tokenization_username'];
		$this->account_production_tokenization_password 		= $plugin_option['account_production_tokenization_password'];
		$this->account_production_moto_username 				= $plugin_option['account_production_moto_username'];
		$this->account_production_moto_password 				= $plugin_option['account_production_moto_password'];
		$this->account_production_moto_passphrase 				= $plugin_option['account_production_moto_passphrase'];
		$this->account_test_private_username 					= $plugin_option['account_test_private_username'];
		$this->account_test_private_password 					= $plugin_option['account_test_private_password'];
		$this->account_test_private_passphrase 					= $plugin_option['account_test_private_passphrase'];
		$this->account_test_tokenization_username 				= $plugin_option['account_test_tokenization_username'];
		$this->account_test_tokenization_password 				= $plugin_option['account_test_tokenization_password'];
		$this->account_test_moto_username 						= $plugin_option['account_test_moto_username'];
		$this->account_test_moto_password 						= $plugin_option['account_test_moto_password'];
		$this->account_test_moto_passphrase 					= $plugin_option['account_test_moto_passphrase'];
		$this->account_proxy_host 								= $plugin_option['account_proxy_host'];
		$this->account_proxy_port 								= $plugin_option['account_proxy_port'];
		$this->account_proxy_username 							= $plugin_option['account_proxy_username'];
		$this->account_proxy_password 							= $plugin_option['account_proxy_password'];

		$this->payment_image 		= "";
		$this->icon 				= "";

		$this->title                = __("ING Home'Pay",'hipayenterprise');

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

		$this->method_enabled             = $this->method->get_is_active();
		add_action('woocommerce_receipt_' . 						$this->id, 	array( $this, 'receipt_page' ) );

	}


	    function process_payment( $order_id ) {
	    	
			global $woocommerce;
		    global $wpdb;

			$order = new WC_Order( $order_id );
			$order_total = $order->get_total();
			require plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php';

			$username 	= (!$this->sandbox) ? $this->account_production_private_username 	: $this->account_test_private_username;
			$password 	= (!$this->sandbox) ? $this->account_production_private_password	: $this->account_test_private_password;
			$passphrase = (!$this->sandbox) ? $this->account_production_private_passphrase: $this->account_test_private_passphrase;

			$env = ($this->sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

			$operation = "Sale";
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

				if ($this->method_details["woocommerce_hipayenterprise_methods_mode"] != "api"){
					$orderRequest->payment_product_list = $this->payment_code;
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




}
