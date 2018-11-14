<?php
if (!defined('ABSPATH')) {
    exit;
}

return array(
    'api_tab_logs' => array(
        'title' => "<hr>" . __('LAST 100 LOGS', 'hipayenterprise'),
        'type' => 'title',
        'description' => ''
    ),
    'logs_details' => array(
        'type' => 'logs_details',
    ),
);
