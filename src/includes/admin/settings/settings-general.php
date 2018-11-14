<?php
if (!defined('ABSPATH')) {
    exit;
}

return array(
    'api_tab_module_configuration' => array(
        'title' => "<hr><i class='dashicons dashicons-admin-tools'></i> " . __('PLUGIN CONFIGURATION', 'hipayenterprise'),
        'type' => 'title',
    ),
    'enabled' => array(
        'title' => __('Enable/Disable', 'hipayenterprise'),
        'type' => 'checkbox',
        'label' => __('Activate Hipay Enterprise payments', 'hipayenterprise'),
        'default' => 'no'
    ),
    'sandbox' => array(
        'title' => __('Sandbox', 'hipayenterprise'),
        'type' => 'checkbox',
        'label' => __('When in test mode, payment cards are not really charged. Enable this option for testing purposes only.', 'hipayenterprise'),
        'default' => 'no'
    ),
    'api_tab_account_production_configuration' => array(
        'title' => "<p><hr><i class='dashicons dashicons-admin-network'></i> " . __('PRODUCTION ACCOUNT CONFIGURATION', 'hipayenterprise') . "</p>",
        'type' => 'title',
        'description' => '<div id="message" class="updated woocommerce-message inline">' . __('Generated in your HiPay Enterprise back office (https://merchant.hipay-tpp.com) via "Integration” => “Security Settings” => “Api credentials” => “Credentials accessibility”, these API credentials are required to use the HiPay Enterprise module.<br>You must generate public and private credentials. You can also set specific credentials for your Mail Order to Order payments. If they are defined then they will be used when making your payments via the back office') . "</div>"
    ),

    'api_tab_account_production_configuration_1' => array(
        'title' => __('Account (Private)', 'hipayenterprise') . "<hr>",
        'type' => 'title',
    ),
    'account_production_private_username' => array(
        'title' => __('Username', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_production_private_password' => array(
        'title' => __('Password', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_production_private_passphrase' => array(
        'title' => __('Secret Passphrase', 'hipayenterprise'),
        'type' => 'text',
    ),
    'api_tab_account_production_configuration_2' => array(
        'title' => __('Tokenization (Public)', 'hipayenterprise') . "<hr>",
        'type' => 'title',
    ),
    'account_production_tokenization_username' => array(
        'title' => __('Username', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_production_tokenization_password' => array(
        'title' => __('Password', 'hipayenterprise'),
        'type' => 'text',
    ),
    'api_tab_account_production_configuration_3' => array(
        'title' => __('MO/TO account credentials', 'hipayenterprise') . "<hr>",
        'type' => 'title',
    ),
    'account_production_moto_username' => array(
        'title' => __('Username', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_production_moto_password' => array(
        'title' => __('Password', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_production_moto_passphrase' => array(
        'title' => __('Secret Passphrase', 'hipayenterprise'),
        'type' => 'text',
    ),
    'api_tab_account_test_configuration' => array(
        'title' => "<br><hr><i class='dashicons dashicons-admin-network'></i> " . __('SANDBOX CONFIGURATION', 'hipayenterprise') . "</p>",
        'type' => 'title',
        'description' => '<div id="message" class="updated woocommerce-message inline">' . __('Generated in your HiPay Enterprise back office (https://stage-merchant.hipay-tpp.com/) via "Integration” => “Security Settings” => “Api credentials” => “Credentials accessibility”, these API credentials are required to use the HiPay Enterprise module.<br>You must generate public and private credentials. You can also set specific credentials for your Mail Order to Order payments. If they are defined then they will be used when making your payments via the back office') . "</div>"
    ),
    'api_tab_account_test_configuration_1' => array(
        'title' => __('Account (Private)', 'hipayenterprise') . "<hr>",
        'type' => 'title',
    ),
    'account_test_private_username' => array(
        'title' => __('Username', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_test_private_password' => array(
        'title' => __('Password', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_test_private_passphrase' => array(
        'title' => __('Secret Passphrase', 'hipayenterprise'),
        'type' => 'text',
    ),
    'api_tab_account_test_configuration_2' => array(
        'title' => __('Tokenization (Public)', 'hipayenterprise') . "<hr>",
        'type' => 'title',
    ),
    'account_test_tokenization_username' => array(
        'title' => __('Username', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_test_tokenization_password' => array(
        'title' => __('Password', 'hipayenterprise'),
        'type' => 'text',
    ),
    'api_tab_account_test_configuration_3' => array(
        'title' => __('MO/TO account credentials', 'hipayenterprise') . "<hr>",
        'type' => 'title',
    ),
    'account_test_moto_username' => array(
        'title' => __('Username', 'hipayenterprise'),
        'type' => 'text',

    ),
    'account_test_moto_password' => array(
        'title' => __('Password', 'hipayenterprise'),
        'type' => 'text',

    ),
    'account_test_moto_passphrase' => array(
        'title' => __('Secret Passphrase', 'hipayenterprise'),
        'type' => 'text',

    ),
    'api_tab_account_technical_configuration' => array(
        'title' => "<p><hr><i class='dashicons dashicons-admin-network'></i> " . __('TECHNICAL CONFIGURATION', 'hipayenterprise') . "</p>",
        'type' => 'title',
        'description' => '<div id="message" class="updated woocommerce-message inline">' . __('If your server is behind a proxy, populate its information so that calls to the HiPay gateway can work..') . "</div>"
    ),
    'api_tab_account_technical_configuration_1' => array(
        'title' => __('Proxy Settings', 'hipayenterprise') . "<hr>",
        'type' => 'title',
    ),
    'account_proxy_host' => array(
        'title' => __('Secret Passphrase', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_proxy_port' => array(
        'title' => __('Secret Passphrase', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_proxy_username' => array(
        'title' => __('Username', 'hipayenterprise'),
        'type' => 'text',
    ),
    'account_proxy_password' => array(
        'title' => __('Password', 'hipayenterprise'),
        'type' => 'text',
    ),
);

