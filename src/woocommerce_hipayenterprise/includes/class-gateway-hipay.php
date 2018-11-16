<?php

if (!defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
use \HiPay\Fullservice\Enum\Transaction\TransactionStatus;

/**
 *
 * WC_Gateway_Hipay
 *
 * @extends     WC_Payment_Gateway
 */
if (!class_exists('WC_Gateway_Hipay')) {
    class Gateway_Hipay extends WC_Payment_Gateway
    {
        public $logs;

        public $settings_name = 'hipay_config';

        public $settingsHandler;

        protected $api;

        public function __construct()
        {
            global $woocommerce;

            $this->id = 'hipayenterprise';
            $this->supports = array(
                'products',
                'refunds'
            );

            if (is_admin()) {
                $plugin_data = get_plugin_data(__FILE__);
                $this->plugin_version = $plugin_data['Version'];
            }

            load_plugin_textdomain($this->id, false, basename(dirname(__FILE__)) . '/languages');

            $this->has_fields = true;

            $this->method_title = __('HiPay Enterprise', $this->id);
            $this->method_description = __(
                'Local and international payments using Hipay Enterprise.',
                'hipayenterprise'
            );

            $this->init_form_fields();

            $this->init_settings();

            $this->addActions();

            $this->confHelper = new Hipay_Config($this);

            $this->api = new Hipay_Api($this);

            $this->logs = new Hipay_Log($this);

            $this->settingsHandler = new Hipay_Settings_Handler($this);

            $this->settingsHipay = $this->confHelper->getConfigHipay();

            $this->icon = WC_HIPAYENTERPRISE_URL_ASSETS . '/images/credit_card.png';

            $this->title = __('Pay by Credit Card', $this->id);

            // TODO faire une classe asset front
            wp_enqueue_style(
                'hipayenterprise-style',
                plugins_url('/assets/css/style.css', __FILE__),
                array(),
                'all'
            );
            wp_enqueue_style(
                'hipayenterprise-card-style',
                plugins_url('/assets/css/card-js.min.css', __FILE__),
                array(),
                'all'
            );
        }

        /**
         *
         */
        public function addActions()
        {
            add_filter('woocommerce_available_payment_gateways', array($this, 'available_payment_gateways'));

            add_action(
                'woocommerce_order_status_pending_to_cancelled',
                'update_stocks_cancelled_order_hipay_enterprise',
                10,
                2
            );
            add_action(
                'woocommerce_order_status_pending_to_failed',
                'update_stocks_cancelled_order_hipay_enterprise',
                10,
                2
            );
            add_action(
                'woocommerce_order_status_on-hold_to_cancelled',
                'update_stocks_cancelled_order_hipay_enterprise',
                10,
                2
            );
            add_action(
                'woocommerce_order_status_on-hold_to_failed',
                'update_stocks_cancelled_order_hipay_enterprise',
                10,
                2
            );
            add_action(
                'woocommerce_order_status_processing_to_cancelled',
                'update_stocks_cancelled_order_hipay_enterprise',
                10,
                2
            );
            add_action(
                'woocommerce_order_status_processing_to_failed',
                'update_stocks_cancelled_order_hipay_enterprise',
                10,
                2
            );

            add_action(
                'woocommerce_order_status_on-hold_to_processing',
                'update_status_order_hipay_enterprise',
                10,
                2
            );
            add_action(
                'woocommerce_order_status_on-hold_to_completed',
                'update_status_order_hipay_enterprise',
                10,
                2
            );


            add_action('woocommerce_after_order_notes', array($this,'custom_checkout_field_hipay_enterprise'));
            add_action('woocommerce_api_wc_hipayenterprise', array($this, 'check_callback_response'));
            add_action(
                'woocommerce_update_options_payment_gateways_' . $this->id,
                array($this, 'process_admin_options')
            );
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_settings'));
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        }

        /**
         * Save Admin Settings
         */
        public function save_settings()
        {
            $settings = array();
            $this->settingsHandler->saveFraudSettings($settings);
            $this->settingsHandler->saveCreditCardSettings($settings);
            $this->settingsHandler->savePaymentGlobal($settings);
            $this->settingsHandler->saveLocalPaymentSettings($settings);

            update_option($this->settings_name, $settings);
        }

        /**
         *  Get detail field for payment method
         */
        public function payment_fields()
        {
            $paymentGlobal = $this->confHelper->getPaymentGlobal();
            if ($paymentGlobal['operating_mode'] == OperatingMode::HOSTED_PAGE) {
                if ($paymentGlobal['display_hosted_page'] == "redirect") {
                    _e('You will be redirected to an external payment page. Please do not refresh the page during the process.', $this->id);
                } else {
                    _e('Pay with your credit card.', $this->id);
                }
            }
        }

        /**
         * Initialise Gateway Settings Admin
         */
        public function init_form_fields()
        {
            $this->form_fields = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-general.php');
            $this->fraud = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-fraud.php');
            $this->methods = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-methods.php');
            $this->faqs = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-faq.php');
            $this->log = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-logs.php');
        }

        /**
         *
         */
        public function generate_methods_credit_card_settings_html()
        {
            ob_start();
            $this->process_template(
                'admin-creditcard-settings.php',
                array(
                    'configurationPaymentMethod' => $this->confHelper->getPaymentCreditCard(),
                    'methods' => 'creditCard'
                )
            );

            return ob_get_clean();
        }

        /**
         *
         */
        public function generate_methods_local_payments_settings_html()
        {
            ob_start();
            $this->process_template(
                'admin-creditcard-settings.php',
                array(
                    'configurationPaymentMethod' => $this->confHelper->getLocalPayment(),
                    'methods' => 'local'
                )
            );

            return ob_get_clean();
        }

        /**
         * @return string
         */
        public function generate_methods_global_settings_html()
        {
            ob_start();
            $this->process_template(
                'admin-global-settings.php',
                array(
                    'paymentCommon' => $this->confHelper->getPaymentGlobal()
                )
            );

            return ob_get_clean();
        }

        /**
         * @return string
         */
        public function generate_faqs_details_html()
        {
            ob_start();
            include(plugin_dir_path(__FILE__) . 'includes/faqs.php');

            return ob_get_clean();
        }

        /**
         * @return string
         */
        public function generate_currencies_details_html()
        {
            ob_start();
            $this->process_template(
                'admin-currencies-settings.php',
                array(
                    'woocommerce_currencies' => get_woocommerce_currencies(),
                )
            );

            return ob_get_clean();
        }

        /**
         * @return string
         */
        public function generate_logs_details_html()
        {
            ob_start();
            $this->process_template('admin-logs-settings.php', array());

            return ob_get_clean();
        }

        /**
         * @return string
         */
        public function generate_fraud_details_html()
        {
            ob_start();
            $this->process_template(
                'admin-fraud-settings.php',
                array(
                    'fraud' => $this->confHelper->getFraud()
                )
            );

            return ob_get_clean();
        }


        public function process_refund($order_id, $amount = null, $reason = '')
        {
            global $wpdb;
            global $woocommerce;

            require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

            $order = wc_get_order($order_id);
            $order->add_order_note(__('Request refund through Hipay Enterprise for amount:', 'hipayenterprise') . " " . $amount . " " . $order->get_currency() . " and reason: " . $reason);

            $username = (!$this->sandbox) ? $this->account_production_private_username : $this->account_test_private_username;
            $password = (!$this->sandbox) ? $this->account_production_private_password : $this->account_test_private_password;
            $passphrase = (!$this->sandbox) ? $this->account_production_private_passphrase : $this->account_test_private_passphrase;
            $env = ($this->sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

            try {
                $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env);
                $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
                $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

                $transactionId = $wpdb->get_row("SELECT reference FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");

                if (!isset($transactionId->reference)) {
                    throw new Exception(__("No transaction reference found.", 'hipayenterprise'));
                } else {
                    $maintenanceResult = $gatewayClient->requestMaintenanceOperation("refund", $transactionId->reference, $amount);
                    $maintenanceResultDump = print_r($maintenanceResult, true);

                    if ($maintenanceResult->getStatus() == "124") {
                        return true;
                    }
                }
                return false;
            } catch (Exception $e) {
                throw new Exception(__("Error processing the Refund:", 'hipayenterprise') . " " . $e->getMessage());
            }
        }

        /**
         *
         */
        public function admin_options()
        {
            $this->process_template('admin-general-settings.php');
        }

        /**
         * @param $template
         * @param array $args
         */
        public function process_template($template, $args = array())
        {
            extract($args);
            $file = WC_HIPAYENTERPRISE_PATH . 'includes/admin/template/' . $template;
            include $file;
        }


        public function thanks_page($order_id)
        {
            global $woocommerce;
        }


        /**
         *
         *  Process payment
         *
         * @param int $order_id
         * @return array
         * @throws Exception
         */
        public function process_payment($order_id)
        {
            try {
                $order = wc_get_order($order_id);

                $response = $this->api->requestHostedPaymentPage($order);
                $redirect = esc_url_raw($response);

                if ($this->confHelper->getPaymentGlobal()["display_hosted_page"] == "iframe") {
                    $redirect = $order->get_checkout_payment_url(true);
                }

                return array(
                    'result'   => 'success',
                    'redirect' => $redirect,
                );
            } catch (Exception $e) {
                wc_add_notice( __( 'Sorry, we cannot process your payment.. Please try again.', 'woocommerce-gateway-hipay' ), 'error' );
                $this->logs->logException($e);
                return array(
                    'result'   => 'fail',
                    'redirect' => '',
                );
            }
        }

        /**
         *
         * @param $order_id
         */
        public function receipt_page($order_id)
        {
            global $wpdb;
            $order = wc_get_order($order_id);
            $payment_url = $wpdb->get_row("SELECT url FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");

            if (!isset($payment_url->url)) {
                $order->get_cancel_order_url_raw();
            } elseif ($this->method_details["operating_mode"] == OperatingMode::DIRECT_POST && $payment_url->url == "") {
                echo __("We have received your order payment. We will process the order as soon as we get the payment confirmation.", "hipayenterprise");
            } else {
                echo '<div id="wc_hipay_iframe_container"><iframe id="wc_hipay_iframe" name="wc_hipay_iframe" width="100%" height="475" style="border: 0;" src="' .
                    $payment_url->url .
                    '" allowfullscreen="" frameborder="0"></iframe></div>' .
                    PHP_EOL;
            }
        }


        public static function reset_stock_levels($order)
        {
            global $woocommerce;

            $products = $order->get_items();
            foreach ($products as $product) {
                $qt = $product['qty'];
                $product_id = $product['product_id'];
                $variation_id = (int)$product['variation_id'];

                if ($variation_id > 0) {
                    $pv = new WC_Product_Variation($variation_id);
                    if ($pv->managing_stock()) {
                        $pv->increase_stock($qt);
                    } else {
                        $p = new WC_Product($product_id);
                        $p->increase_stock($qt);
                    }
                } else {
                    $p = new WC_Product($product_id);
                    $p->increase_stock($qt);
                }
            }
        }


        public static function get_order_information($order_id)
        {
            global $woocommerce;

            require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

            $plugin_option = get_option('woocommerce_hipayenterprise_settings');

            $username = (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_username'] : $plugin_option['account_test_private_username'];
            $password = (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_password'] : $plugin_option['account_test_private_password'];
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
                throw new Exception(
                    __(
                        "Error getting order information.",
                        'hipayenterprise'
                    ) . " " . $e->getMessage()
                );
            }
        }

        public function check_callback_response()
        {
            try {
                $notification = new WC_HipayEnterprise_Notification($this, $_POST);
                $notification->processTransaction();
            } catch (Exception $e) {
                header("HTTP/1.0 500 Internal server error");
            }
        }

        //@TODO delete
        public function update_stocks_cancelled_order_hipay_enterprise($order_id, $order)
        {
            global $wpdb;

            $cur_payment_method = get_post_meta($order_id, '_payment_method', true);
            if ($cur_payment_method == 'hipayenterprise') {
                $stock_flag = $wpdb->get_row(
                    "SELECT stocks FROM " .
                    $wpdb->prefix .
                    "woocommerce_hipayenterprise WHERE order_id = $order_id LIMIT 1"
                );
                if (isset($stock_flag->stocks) && $stock_flag->stocks == 1) {
                    WC_HipayEnterprise::reset_stock_levels($order);
                }
            }
        }

        /**
         * Check if payment method is available for current cart
         *
         * TODO Utiliser la methode getActivatedPaymentByCountryAndCurrency
         * @return boolean
         */
        public function isAvailableForCurrentCart()
        {
            global $woocommerce;
            $settingsCreditCard = $this->confHelper->getPaymentCreditCard();
            $cartTotals = $woocommerce->cart->get_totals();
            foreach ($settingsCreditCard as $card => $conf) {
                if ($conf["activated"]
                    && in_array(get_woocommerce_currency(), $conf["currencies"])
                    && in_array($woocommerce->customer->get_billing_country(), $conf["countries"])
                    && Hipay_Helper::isInAuthorizedAmount($conf, $cartTotals["total"])) {
                    return true;
                }
            }
            return false;
        }

        /**
         * @param $methods
         *
         * @return mixed
         */
        public function available_payment_gateways($available_gateways)
        {
            global $woocommerce;

            if (isset($woocommerce->cart)) {
                foreach ($available_gateways as $id => $gateway) {
                    if ($id == "hipayenterprise"
                        && !$gateway->isAvailableForCurrentCart()) {
                        unset($available_gateways [$id]);
                    }
                }
            }

            return $available_gateways;
        }

        public function custom_checkout_field_hipay_enterprise($checkout)
        {
            echo "<div id='hipay_dp' style='display:none;'>";
            woocommerce_form_field(
                'hipay_token',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_token')
            );
            woocommerce_form_field(
                'hipay_brand',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_brand')
            );
            woocommerce_form_field(
                'hipay_pan',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_pan')
            );
            woocommerce_form_field(
                'hipay_card_holder',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_card_holder')
            );
            woocommerce_form_field(
                'hipay_card_expiry_month',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_card_expiry_month')
            );
            woocommerce_form_field(
                'hipay_card_expiry_year',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_card_expiry_year')
            );
            woocommerce_form_field(
                'hipay_issuer',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_issuer')
            );
            woocommerce_form_field(
                'hipay_country',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_country')
            );
            woocommerce_form_field(
                'hipay_multiuse',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_multiuse')
            );
            woocommerce_form_field(
                'hipay_direct_error',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_direct_error')
            );

            woocommerce_form_field(
                'hipay_delete_info',
                array(
                    'type' => 'text',
                ),
                $checkout->get_value('hipay_delete_info')
            );

            echo "</div>";
        }

        public function update_status_order_hipay_enterprise($order_id, $order)
        {
            global $woocommerce;

            $cur_payment_method = get_post_meta($order_id, '_payment_method', true);
            $captured_flag = "";
            if ($cur_payment_method == 'hipayenterprise') {
                $order = new WC_Order($order_id);
                //capture status in db

//                if ($captured_flag->captured == 1) {
//                    return true;
//                }

                require plugin_dir_path(__FILE__) . 'vendor/autoload.php';
                require plugin_dir_path(__FILE__) . 'includes/operations.php';
                $plugin_option = get_option('woocommerce_hipayenterprise_settings');
                $username = (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_username'] : $plugin_option['account_test_private_username'];
                $password = (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_password'] : $plugin_option['account_test_private_password'];
                $passphrase = (!$plugin_option['sandbox']) ? $plugin_option['account_production_private_passphrase'] : $plugin_option['account_test_private_passphrase'];
                $env = ($plugin_option['sandbox']) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;
                $env_endpoint = ($plugin_option['sandbox']) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENDPOINT_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENDPOINT_PROD;
                $order_total = $order->get_total();

                try {
                    $res = HipayEnterpriseWooOperation::get_details_by_order(
                        $username,
                        $password,
                        $passphrase,
                        $env_endpoint,
                        $order_id
                    );

                    if ($res->transaction->captured_amount < $order_total) {

                        //try to capture amount (total or partial)
                        $amount_to_capture = $order_total - $res->transaction->captured_amount;
                        $order->add_order_note(
                            __(
                                'Try to capture amount:',
                                'hipayenterprise'
                            ) . " " . $amount_to_capture . " " . $res->transaction->currency
                        );
                        $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration(
                            $username,
                            $password,
                            $env
                        );
                        $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
                        $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

                        $operationResult = $gatewayClient->requestMaintenanceOperation(
                            "capture",
                            $captured_flag->reference,
                            $amount_to_capture
                        );
                        $order->add_order_note(
                            __(
                                'Capture amount:',
                                'hipayenterprise'
                            ) . " " . $amount_to_capture . " " . $res->transaction->currency . " " . __(
                                'returned',
                                'hipayenterprise'
                            ) . " " . $operationResult->getStatus() . " " . $operationResult->getMessage()
                        );
                    }
                } catch (Exception $e) {
                    $order->add_order_note($e->getMessage());
                }
            }
        }
    }
}
