<?php
if (!defined('ABSPATH')) {
    exit;
}

return array(
    'api_tab_methods_1' => array(
        'title' => "<p><hr>" . __('GLOBAL SETTINGS', 'hipayenterprise') . "</p>",
        'type' => 'title',
    ),
    'methods_global_settings' => array(
        'type' => 'methods_global_settings',
    ),
    'api_tab_methods_2' => array(
        'title' => "<p><hr>" . __('CREDIT CARD', 'hipayenterprise') . "</p>",
        'type' => 'title',
    ),
    'methods_credit_card_settings' => array(
        'type' => 'methods_credit_card_settings',
    ),
    'api_tab_methods_3' => array(
        'title' => "<p><hr>" . __('LOCAL PAYMENTS', 'hipayenterprise') . "</p>",
        'type' => 'title',
    ),
    'methods_local_payments_settings' => array(
        'type' => 'methods_local_payments_settings',
    ),
);

