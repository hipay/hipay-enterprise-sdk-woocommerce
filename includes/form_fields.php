<?php
// BASE

$this->form_fields = array(
'api_tab_module_configuration' => array(
	'title'       => "<hr><i class='dashicons dashicons-admin-tools'></i> ".__( 'PLUGIN CONFIGURATION', 'hipayenterprise' ) ,
	'type'        => 'title',
),

'enabled' => array(
				'title' => __( 'Enable/Disable', 'hipayenterprise' ),
				'type' => 'checkbox',
				'label' => __( 'Activate Hipay Enterprise payments', 'hipayenterprise' ),
				'default' => 'yes'
			),

'sandbox' => array(
				'title' => __( 'Sandbox', 'hipayenterprise' ),
				'type' => 'checkbox',
				'label' => __( 'When in test mode, payment cards are not really charged. Enable this option for testing purposes only.', 'hipayenterprise' ),
				'default' => 'no'
			),


'api_tab_account_production_configuration' => array(
	'title'       => "<p><hr><i class='dashicons dashicons-admin-network'></i> ".__( 'PRODUCTION ACCOUNT CONFIGURATION', 'hipayenterprise' )."</p>",
	'type'        => 'title',
	'description' => '<div id="message" class="updated woocommerce-message inline">' . __('Generated in your HiPay Enterprise back office (https://merchant.hipay-tpp.com) via "Integration” => “Security Settings” => “Api credentials” => “Credentials accessibility”, these API credentials are required to use the HiPay Enterprise module.<br>You must generate public and private credentials. You can also set specific credentials for your Mail Order to Order payments. If they are defined then they will be used when making your payments via the back office')."</div>"
),


'api_tab_account_production_configuration_1' => array(
	'title'       => __( 'Account (Private)', 'hipayenterprise' ) . "<hr>",
	'type'        => 'title',
),

	'account_production_private_username' => array(
					'title' => __( 'Username', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_production_private_password' => array(
					'title' => __( 'Password', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_production_private_passphrase' => array(
					'title' => __( 'Secret Passphrase', 'hipayenterprise' ),
					'type' => 'text',
					
				),


'api_tab_account_production_configuration_2' => array(
	'title'       => __( 'Tokenization (Public)', 'hipayenterprise' ) . "<hr>",
	'type'        => 'title',
),

	'account_production_tokenization_username' => array(
					'title' => __( 'Username', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_production_tokenization_password' => array(
					'title' => __( 'Password', 'hipayenterprise' ),
					'type' => 'text',
					
				),

'api_tab_account_production_configuration_3' => array(
	'title'       => __( 'MO/TO account credentials', 'hipayenterprise' ) . "<hr>",
	'type'        => 'title',
),

	'account_production_moto_username' => array(
					'title' => __( 'Username', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_production_moto_password' => array(
					'title' => __( 'Password', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_production_moto_passphrase' => array(
					'title' => __( 'Secret Passphrase', 'hipayenterprise' ),
					'type' => 'text',
					
				),


'api_tab_account_test_configuration' => array(
	'title'       => "<br><hr><i class='dashicons dashicons-admin-network'></i> ".__( 'SANDBOX CONFIGURATION', 'hipayenterprise' )."</p>",
	'type'        => 'title',
	'description' => '<div id="message" class="updated woocommerce-message inline">' . __('Generated in your HiPay Enterprise back office (https://stage-merchant.hipay-tpp.com/) via "Integration” => “Security Settings” => “Api credentials” => “Credentials accessibility”, these API credentials are required to use the HiPay Enterprise module.<br>You must generate public and private credentials. You can also set specific credentials for your Mail Order to Order payments. If they are defined then they will be used when making your payments via the back office')."</div>"
),



'api_tab_account_test_configuration_1' => array(
	'title'       => __( 'Account (Private)', 'hipayenterprise' ) . "<hr>",
	'type'        => 'title',
),


	'account_test_private_username' => array(
					'title' => __( 'Username', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_test_private_password' => array(
					'title' => __( 'Password', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_test_private_passphrase' => array(
					'title' => __( 'Secret Passphrase', 'hipayenterprise' ),
					'type' => 'text',
					
				),


'api_tab_account_test_configuration_2' => array(
	'title'       => __( 'Tokenization (Public)', 'hipayenterprise' ) . "<hr>",
	'type'        => 'title',
),

	'account_test_tokenization_username' => array(
					'title' => __( 'Username', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_test_tokenization_password' => array(
					'title' => __( 'Password', 'hipayenterprise' ),
					'type' => 'text',
					
				),



'api_tab_account_test_configuration_3' => array(
	'title'       => __( 'MO/TO account credentials', 'hipayenterprise' ) . "<hr>",
	'type'        => 'title',
),

	'account_test_moto_username' => array(
					'title' => __( 'Username', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_test_moto_password' => array(
					'title' => __( 'Password', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_test_moto_passphrase' => array(
					'title' => __( 'Secret Passphrase', 'hipayenterprise' ),
					'type' => 'text',
					
				),

'api_tab_account_technical_configuration' => array(
	'title'       => "<p><hr><i class='dashicons dashicons-admin-network'></i> ".__( 'TECHNICAL CONFIGURATION', 'hipayenterprise' ) ."</p>",
	'type'        => 'title',
	'description' => '<div id="message" class="updated woocommerce-message inline">' . __('If your server is behind a proxy, populate its information so that calls to the HiPay gateway can work..')."</div>"
),


'api_tab_account_technical_configuration_1' => array(
	'title'       => __( 'Proxy Settings', 'hipayenterprise' ) . "<hr>",
	'type'        => 'title',
),

	'account_proxy_host' => array(
					'title' => __( 'Secret Passphrase', 'hipayenterprise' ),
					'type' => 'text',
					
				),
	'account_proxy_port' => array(
					'title' => __( 'Secret Passphrase', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_proxy_username' => array(
					'title' => __( 'Username', 'hipayenterprise' ),
					'type' => 'text',
					
				),

	'account_proxy_password' => array(
					'title' => __( 'Password', 'hipayenterprise' ),
					'type' => 'text',
					
				),

);


// PAYMENT METHODS

$this->methods = array(

'api_tab_methods_1' => array(
	'title'       => "<p><hr>".__( 'GLOBAL SETTINGS', 'hipayenterprise' ) ."</p>",
	'type'        => 'title',
	
),

	'methods_global_settings' => array(
		'type'        => 'methods_global_settings',
	),


'api_tab_methods_2' => array(
	'title'       => "<p><hr>".__( 'CREDIT CARD', 'hipayenterprise' ) ."</p>",
	'type'        => 'title',
	
),

	'methods_credit_card_settings' => array(
		'type'        => 'methods_credit_card_settings',
	),


'api_tab_methods_3' => array(
	'title'       => "<p><hr>".__( 'LOCAL PAYMENT', 'hipayenterprise' ) ."</p>",
	'type'        => 'title',
	
),			

	'api_tab_methods_local_disclaimer' => array(
		'title'       => "" ,
		'type'        => 'title',
		'description' => __('Coming soon.')
	),

	//'methods_local_payments_settings' => array(
	//	'type'        => 'methods_local_payments_settings',
	//),
);


//FRAUD

$this->fraud = array(
	'api_tab_fraud' => array(
		'title'       => "<hr>".__( 'PAYMENT FRAUD EMAIL', 'hipayenterprise' ) ,
		'type'        => 'title',
		'description' => '<div id="message" class="updated woocommerce-message inline">' . __('When a transaction is likely to be a fraud then an email is sent to the contact email from your shop as well as to an additional sender. Here you can configure the additional recipient email')."</div>"
	),


	'fraud_details' => array(
		'type'        => 'fraud_details',
	),
);

//CURRENCIES

$this->currencies = array(
	'api_tab_fraud' => array(
		'title'       => "<hr>".__( 'ACTIVE CURRENCIES SELECTION', 'hipayenterprise' ) ,
		'type'        => 'title',
		'description' => '<div id="message" class="updated woocommerce-message inline">' . __('Activate the currencies available on your Woocommerce website and then procceed to Payment Methods configuration.')."</div>"
	),


	'currencies_details' => array(
		'type'        => 'currencies_details',
	),
);


//FAQS

$this->faqs = array(
	'api_tab_faqs' => array(
		'title'       => "<hr>".__( 'FAQ', 'hipayenterprise' ) ,
		'type'        => 'title',
		'description' => ''
	),

	'faqs_details' => array(
		'type'        => 'faqs_details',
	),
);


//LOGS
//TODO: LIST ALL & CLEAR LIST
$this->logs = array(
	'api_tab_logs' => array(
		'title'       => "<hr>".__( 'LAST 100 LOGS', 'hipayenterprise' ) ,
		'type'        => 'title',
		'description' => ''
	),

	'logs_details' => array(
		'type'        => 'logs_details',
	),	

);
