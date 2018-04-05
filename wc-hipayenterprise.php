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

	class WC_HipayEnterprise extends WC_Payment_Gateway  {
		
		public function __construct() {

			global $woocommerce;
			global $wpdb;

			$this->id = 'hipayenterprise';
			$this->supports           = array(	'products',	'refunds',	);

			load_plugin_textdomain( $this->id, false, basename( dirname( __FILE__ ) ) . '/languages' ); 
			include_once( plugin_dir_path( __FILE__ ) . 'includes/payment_methods.php' );
			include_once( plugin_dir_path( __FILE__ ) . 'includes/base_config.php' );

			$this->plugin_table 									= $wpdb->prefix . 'woocommerce_hipayenterprise';
			$this->plugin_table_logs 								= $wpdb->prefix . 'woocommerce_hipayenterprise_logs';

			$this->has_fields 										= false;
			$this->method_title     								= __('HiPay Enterprise', $this->id );
			$this->init_form_fields();
			$this->init_settings();

			$this->title 											= __('Pay with HiPay Enterprise', $this->id );
			$this->description 										= __('Payment by credit card, debit card or local payments', $this->id );

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
					'woocommerce_hipayenterprise_methods_display_name'			=> $this->get_option( 'woocommerce_hipayenterprise_methods_display_name' ),
					'woocommerce_hipayenterprise_methods_payments' 				=> $this->get_option( 'woocommerce_hipayenterprise_methods_payments' ),
				)
			);

			if (!isset($this->method_details["woocommerce_hipayenterprise_methods_payments"]))
				$this->method_details["woocommerce_hipayenterprise_methods_payments"] = HIPAY_ENTERPRISE_PAYMENT_METHODS;
			
			add_action('woocommerce_api_wc_hipayenterprise', 						array($this, 'check_callback_response') );
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, 	array($this, 'process_admin_options'));
			add_action('woocommerce_update_options_payment_gateways_' . $this->id, 	array($this, 'save_account_details' ) );
			add_action('woocommerce_thankyou_hipayenterprise', 						array($this, 'thanks_page'));

			wp_enqueue_style('hipayenterprise-style', plugins_url( '/assets/css/style.css', __FILE__ ), array(),'all');
			
		}


		public function save_account_details() {

			$fraud = array();
			$fraud['woocommerce_hipayenterprise_fraud_copy_to'] 			= sanitize_email($_POST['woocommerce_hipayenterprise_fraud_copy_to']);
			$fraud['woocommerce_hipayenterprise_fraud_copy_method'] 		= sanitize_title($_POST['woocommerce_hipayenterprise_fraud_copy_method']);

			$currencies = array();
			$woocommerce_hipayenterprise_currencies_active   				= array_map( 'wc_clean', $_POST['woocommerce_hipayenterprise_currencies_active'] );
			$currencies['woocommerce_hipayenterprise_currencies_active']	= $woocommerce_hipayenterprise_currencies_active;
	
			$methods = array();

			$all_methods 		= json_decode(HIPAY_ENTERPRISE_PAYMENT_METHODS);
			$max_amount = 0;
			$min_amount = -1;
			$countries_list = array();
			$currencies_list = array();

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
					$all_methods_json .= $the_method->get_json() . ",";
				}

				if ($the_method->get_is_active()){
					if ($the_method->get_max_amount() > $max_amount) $max_amount = $the_method->get_max_amount();
					if ( ($the_method->get_min_amount() < $min_amount) || $min_amount > -1 ) $min_amount = $the_method->get_min_amount();
				}


			}

			$all_methods_json 	= substr($all_methods_json,0, -1) . "]";
 			$methods = array(
 				'woocommerce_hipayenterprise_methods_mode'		 				=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_mode']),
 				'woocommerce_hipayenterprise_methods_capture'		 			=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_capture']),
				'woocommerce_hipayenterprise_methods_3ds' 						=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_3ds']),
				'woocommerce_hipayenterprise_methods_oneclick'					=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_oneclick']),
				'woocommerce_hipayenterprise_methods_cart_sending'				=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_cart_sending']),
				'woocommerce_hipayenterprise_methods_keep_cart_onfail'			=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_keep_cart_onfail']),
				'woocommerce_hipayenterprise_methods_log_info'					=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_log_info']),
				'woocommerce_hipayenterprise_methods_hosted_css'				=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_hosted_css']),
				'woocommerce_hipayenterprise_methods_hosted_mode'				=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_hosted_mode']),
				'woocommerce_hipayenterprise_methods_hosted_card_selector'		=> sanitize_title($_POST['woocommerce_hipayenterprise_methods_hosted_card_selector']),

				'woocommerce_hipayenterprise_methods_display_name'				=> sanitize_text_field($_POST['woocommerce_hipayenterprise_methods_display_name']),
				'woocommerce_hipayenterprise_methods_payments' 					=> $all_methods_json,
 				'woocommerce_hipayenterprise_methods_payments_min_amount'		=> $min_amount,
 				'woocommerce_hipayenterprise_methods_payments_max_amount'		=> $max_amount,
 				'woocommerce_hipayenterprise_methods_payments_countries_list'	=> $countries_list,
 				'woocommerce_hipayenterprise_methods_payments_currencies_list'	=> $currencies_list,
			);	

			update_option( 'woocommerce_hipayenterprise_methods'	, $methods );
			update_option( 'woocommerce_hipayenterprise_currencies'	, $currencies );
			update_option( 'woocommerce_hipayenterprise_fraud'		, $fraud );

		}


		public function install() {

			global $wp_version;
			include( plugin_dir_path( __FILE__ ) . 'includes/database_config.php' );
		}

		
		function init_form_fields() {
			
			include( plugin_dir_path( __FILE__ ) . 'includes/form_fields.php' );		
		}


		function generate_methods_credit_card_settings_html() {

			ob_start();
			$woocommerce_hipayenterprise_methods_display_name = $this->method_details["woocommerce_hipayenterprise_methods_display_name"];
			if ($woocommerce_hipayenterprise_methods_display_name == "") $woocommerce_hipayenterprise_methods_display_name =  __( 'Credit Card', 'hipayenterprise' );

			$current_methods = [];
			$woocommerce_hipayenterprise_methods_payments_json = json_decode($this->method_details["woocommerce_hipayenterprise_methods_payments"]);
			foreach ($woocommerce_hipayenterprise_methods_payments_json as $key => $value) {
				$the_method = new HipayEnterprisePaymentMethodClass($value);	
				$current_methods[] = $the_method;			
			}
			
			?>

			<tr valign="top">
				<th scope="row" class="titledesc"><?php _e( 'Display name', 'hipayenterprise' ); ?></th>
				<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Display name", 'hipayenterprise');?></span></legend>
					<input class="input-text regular-input " type="text" name="woocommerce_hipayenterprise_methods_display_name" id="woocommerce_hipayenterprise_methods_display_name" style="" value="<?php echo $woocommerce_hipayenterprise_methods_display_name;?>" placeholder="<?php _e( 'Credit Card', 'hipayenterprise' ); ?>">
					<p class="description"><?php _e("Display name for payment by credit card on your checkout page.",'hipayenterprise');?></p>
				</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="">

					<?php
					$sel_btn = " credit_card_admin_menu_sel";
					foreach ($current_methods as $the_method) {
						if ((bool)$the_method->get_is_credit_card()) echo "<div data-id='".$the_method->get_key()."' class='credit_card_admin_menu".$sel_btn."'>" . __( $the_method->get_title(), 'hipayenterprise' ) . "<br></div>"; 					
						$sel_btn = "";
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
										<select multiple class="input-text regular-input woocommerce_hipayenterprise_methods_cc_countries_<?php echo $the_method->get_key();?>" name="woocommerce_hipayenterprise_methods_cc_countries[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_cc_countries[<?php echo $the_method->get_key();?>]">
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
										<select multiple class="input-text regular-input woocommerce_hipayenterprise_methods_cc_countries_available_<?php echo $the_method->get_key();?>" name="woocommerce_hipayenterprise_methods_cc_countries_available[<?php echo $the_method->get_key();?>]" id="woocommerce_hipayenterprise_methods_cc_countries_available[<?php echo $the_method->get_key();?>]">
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
						<option value="api" <?php if ($woocommerce_hipayenterprise_methods_mode == "api") echo " SELECTED";?> disabled><?php _e( 'Direct Post', 'hipayenterprise' ); ?> <?php _e( '(SOON)', 'hipayenterprise' ); ?></option>
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
						<option value="iframe" <?php if ($woocommerce_hipayenterprise_methods_hosted_mode == "iframe") echo " SELECTED";?> disabled><?php _e( 'IFrame', 'hipayenterprise' ); ?> <?php _e( '(SOON)', 'hipayenterprise' ); ?></option>
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
				$order = wc_get_order( $order_id );
				$order->add_order_note(__('refund:', 'hipayenterprise') . " " . $amount . " " . $reason );
				return true;
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
			require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

			$order = new WC_Order( $order_id );
			$order_total = $order->get_total();

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

			try {

				$config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env);
				$clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
				$gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
				$orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\HostedPaymentPageRequest();
				
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
				$orderRequest->language = "pt_PT";
				$orderRequest->payment_product = "cb";
				$orderRequest->description = $shop_title;
				$orderRequest->ipaddr = $_SERVER ['REMOTE_ADDR']; 
				$orderRequest->http_user_agent = $_SERVER ['HTTP_USER_AGENT'];
				//$orderRequest->basket = "[{'type':'good' , 'name':'teste produto','total_amount':'2.00'}]";
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

				//https://github.com/hipay/hipay-fullservice-sdk-php/blob/master/lib/HiPay/Fullservice/Gateway/Model/Cart/Item.php

				$orderRequest->authentication_indicator = $this->method_details['woocommerce_hipayenterprise_methods_3ds'];
				if ($this->method_details['woocommerce_hipayenterprise_methods_hosted_mode']=="redirect") 
					$orderRequest->template = "basic-js";
				else
					$orderRequest->template = "iframe-js";

				$orderRequest->display_selector = (int)$this->method_details['woocommerce_hipayenterprise_methods_hosted_card_selector'];
				if ($this->method_details['woocommerce_hipayenterprise_methods_hosted_css']!="") $orderRequest->css = $this->woocommerce_hipayenterprise_methods['woocommerce_hipayenterprise_methods_hosted_css'];

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
					if ($the_method->get_is_active() && $order_total <= $the_method->get_max_amount() && $order_total >= $the_method->get_min_amount() && (strpos($the_method->get_available_currencies(),$current_currency) !== false)  && (strpos($the_method->get_available_countries(),$current_billing_country) !== false))	{
						$available_methods[] = $the_method->get_key();
					}	
				}
				
				$orderRequest->payment_product_list = implode(",", $available_methods);
				$orderRequest->payment_product_category_list = '';
				//multi_use, time_limit_to_pay, token
				//firstname, lastname, billinginfo, shipping info
				$transaction = $gatewayClient->requestHostedPaymentPage($orderRequest);

				//$x = print_r($transaction,true);
				//$order->add_order_note($x);
				$order->add_order_note(__('Payment URL:', 'hipayenterprise') . " " . $transaction->getForwardUrl() );
		    	$logger = wc_get_logger(); $logger->debug( $transaction->getForwardUrl(), array( 'source' => 'hipayenterprise' ) );

				$wpdb->insert( $this->plugin_table, array( 'reference' => 0, 'order_id' => $order_id, 'amount' => $order_total ) );

				if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
					$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Payment created with url:","hipayenterprise") . " " . $transaction->getForwardUrl(), 'order_id' => $order_id, 'type' => 'INFO' ) );

		    	return array('result' 	=> 'success','redirect'	=> 	$transaction->getForwardUrl()     	);


			} catch (Exception $e) {
				$order->add_order_note($e->getMessage() );
				//if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
					$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Error on creation:","hipayenterprise") . " " . $e->getMessage(), 'order_id' => $order_id, 'type' => 'ERROR' ) );

				throw new Exception($e->getMessage());			    
			}
		

    	}



		private function hipay_enterprise_get_locale(){
			
			$locale = get_locale();
			if ( !in_array($locale, $this->authorized_languages) ) $locale = $this->default_language;
			return $locale;
		}



		function check_callback_response() {

			global $woocommerce;
			global $wpdb;

			$notification = $_POST;
			$notification_text = print_r($notification,true);

			//INSERT LOGS
			//$wpdb->insert( $this->plugin_table_logs, array( 'reference' => $result->generateResult->redirectUrl, 'order_id' => $order_id, 'amount' => $order_total ) );

			$order_id = $notification["order"]["id"];
			$order = new WC_Order( $order_id );	

			$token 					= $notification["token"];
			$state 					= $notification["state"];
			$message 				= $notification["message"];
			$status 				= $notification["status"];
			$test 					= $notification["test"];
			$transaction_reference	= $notification["transaction_reference"];

			$order->add_order_note( $status . ": " . $message . " " . $state . " (".$transaction_reference.$this->plugin_table.")");

			if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
				$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => $message . " " . __("Callback received for transation:","hipayenterprise") . " " . $transaction_reference, 'order_id' => $order_id, 'type' => 'INFO' ) );

			if ($state = "completed" && $status == 118){
				$order->update_status('processing', __("Payment successful for transaction", 'hipayenterprise' ) . " " . $transaction_reference, 0 );
				$wpdb->update( $this->plugin_table , array( 'captured' => 1, 'reference' => $transaction_reference, 'status' => $status,'operation' => $message ,'processed' => 1, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id, 'processed' => 0 ) );
				if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
					$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Payment captured and Order changed to processing status.","hipayenterprise"), 'order_id' => $order_id, 'type' => 'INFO' ) );
			} elseif ($state = "completed" && $status == 116 && $this->method_details["woocommerce_hipayenterprise_methods_capture"] == "manual"){
				$order->update_status('on-hold', __("Waiting for manual capture. Authorization successful for transaction.", 'hipayenterprise' ) . " " . $transaction_reference, 0 );
				$wpdb->update( $this->plugin_table , array( 'reference' => $transaction_reference, 'status' => $status,'operation' => $message ,'processed' => 0, 'processed_date' => date('Y-m-d H:i:s')), array('order_id' =>$order_id, 'processed' => 0 ) );				
				if ($this->method_details['woocommerce_hipayenterprise_methods_log_info'])
					$wpdb->insert( $this->plugin_table_logs, array( 'log_desc' => __("Payment authorized and Order changed to on-hold status, waiting for capture.","hipayenterprise"), 'order_id' => $order_id, 'type' => 'INFO' ) );
			}	
			return true;
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

    		if (in_array($current_currency, $plugin_method_settings['woocommerce_hipayenterprise_methods_payments_currencies_list']) && in_array($current_billing_country, $plugin_method_settings['woocommerce_hipayenterprise_methods_payments_countries_list']) ) {

				$currency_symbol = get_woocommerce_currency_symbol();
				$total_amount = $woocommerce->cart->get_total();
				$total_amount = str_replace($currency_symbol,"", $total_amount);
				$thousands_sep = wp_specialchars_decode(stripslashes(get_option( 'woocommerce_price_thousand_sep')), ENT_QUOTES);
				$total_amount = str_replace($thousands_sep,"", $total_amount);
				$decimals_sep = wp_specialchars_decode(stripslashes(get_option( 'woocommerce_price_decimal_sep')), ENT_QUOTES);
				if ( $decimals_sep != ".") $total_amount = str_replace($decimals_sep,".", $total_amount);
				$total_amount = floatval( preg_replace( '#[^\d.]#', '',  $total_amount) );

				if ($total_amount > $max_value || $total_amount < $min_value ) unset($methods['hipayenterprise']); 

			} else	{
				unset($methods['hipayenterprise']); 
			}
		}
		return $methods;
	}



	function add_hipayenterprise_gateway( $methods ) {

		$methods[] = 'WC_HipayEnterprise'; return $methods;
		
	}


	function update_stocks_cancelled_order_hipay_enterprise( $order_id,  $order  ){

		global $woocommerce;
		global $wpdb;
		
	}


	add_filter('woocommerce_available_payment_gateways', 'filter_hipayenterprise_gateway' );
	add_filter('woocommerce_payment_gateways', 'add_hipayenterprise_gateway' );
	add_action('woocommerce_order_status_pending_to_cancelled', 'update_stocks_cancelled_order_hipay_enterprise', 10, 2 );
	add_action('woocommerce_order_status_pending_to_failed', 'update_stocks_cancelled_order_hipay_enterprise', 10, 2 );

	//add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'capture_payment' ) );
	//add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'capture_payment' ) );

}
