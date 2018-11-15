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

        public $settingsHandler;

        public $confHelper;

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

            $this->confHelper = new Hipay_Config($this);

            $this->init_form_fields();

            $this->init_settings();

            $this->confHelper->initConfigHiPay();

            $this->addActions();

            $this->logs = new Hipay_Log($this);

            $this->settingsHandler = new Hipay_Settings_Handler($this);

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


            add_action('woocommerce_after_order_notes', array($this, 'custom_checkout_field_hipay_enterprise'));
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
            $this->settingsHandler->saveAccountSettings($settings);
            $this->settingsHandler->saveFraudSettings($settings);
            $this->settingsHandler->saveCreditCardSettings($settings);
            $this->settingsHandler->savePaymentGlobal($settings);
            $this->settingsHandler->saveLocalPaymentSettings($settings);

            $this->confHelper->saveConfiguration($settings);
        }

        /**
         * Process template
         */
        public function payment_fields()
        {
            $paymentGlobal = $this->confHelper->getPaymentGlobal();
            if ($paymentGlobal['operating_mode'] == OperatingMode::HOSTED_PAGE) {
                if ($paymentGlobal['display_hosted_page'] == "redirect") {
                    _e(
                        'You will be redirected to an external payment page. Please do not refresh the page during the process.',
                        $this->id
                    );
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
            $this->account = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-account.php');
            $this->fraud = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-fraud.php');
            $this->methods = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-methods.php');
            $this->faqs = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-faq.php');
            $this->log = include(plugin_dir_path(__FILE__) . 'admin/settings/settings-logs.php');
        }

        /**
         *
         */
        public function generate_account_details_html()
        {
            ob_start();
            $this->process_template(
                'admin-account-settings.php',
                array(
                    'account' => $this->confHelper->getAccount()
                )
            );

            return ob_get_clean();
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
            $order->add_order_note(
                __('Request refund through Hipay Enterprise for amount:', 'hipayenterprise') .
                " " .
                $amount .
                " " .
                $order->get_currency() .
                " and reason: " .
                $reason
            );

            $username = (!$this->sandbox) ? $this->account_production_private_username : $this->account_test_private_username;
            $password = (!$this->sandbox) ? $this->account_production_private_password : $this->account_test_private_password;
            $passphrase = (!$this->sandbox) ? $this->account_production_private_passphrase : $this->account_test_private_passphrase;
            $env = ($this->sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

            try {
                $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env);
                $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
                $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);

                $transactionId = $wpdb->get_row(
                    "SELECT reference FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1"
                );

                if (!isset($transactionId->reference)) {
                    throw new Exception(__("No transaction reference found.", 'hipayenterprise'));
                } else {
                    $maintenanceResult = $gatewayClient->requestMaintenanceOperation(
                        "refund",
                        $transactionId->reference,
                        $amount
                    );
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


        public function process_payment($order_id)
        {
            global $woocommerce;
            global $wpdb;

            $token = $_POST["hipay_token"];
            $order = new WC_Order($order_id);


            if ($this->method_details["operating_mode"] == OperatingMode::DIRECT_POST && $token == "") {
                return;
            } elseif ($this->method_details["operating_mode"] == OperatingMode::DIRECT_POST && $token != "") {
                $hipay_direct_error = $_POST["hipay_direct_error"];
                if ($hipay_direct_error != "") {
                    throw new Exception($hipay_direct_error, 1);
                }

                $brand = $_POST["hipay_brand"];
                $customer_id = $order->get_user_id();
            }

            $order_total = $order->get_total();
            require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

            $username = (!$this->sandbox) ? $this->account_production_private_username : $this->account_test_private_username;
            $password = (!$this->sandbox) ? $this->account_production_private_password : $this->account_test_private_password;
            $passphrase = (!$this->sandbox) ? $this->account_production_private_passphrase : $this->account_test_private_passphrase;

            $env = ($this->sandbox) ? HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_STAGE : HiPay\Fullservice\HTTP\Configuration\Configuration::API_ENV_PRODUCTION;

            $operation = "Sale";
            if ($this->method_details['capture_mode'] == CaptureMode::MANUAL) {
                $operation = "Authorization";
            }
            $callback_url = site_url() . '/wc-api/WC_HipayEnterprise/?order=' . $order_id;
            $current_currency = get_woocommerce_currency();
            $current_billing_country = $woocommerce->customer->get_billing_country();
            $billing_email = $woocommerce->customer->get_billing_email();
            $shop_title = get_bloginfo('name');

            $request_source = '{"source":"CMS","brand":"Woocommerce","brand_version":"' .
                $woocommerce->version .
                '","integration_version":"' .
                $this->plugin_version .
                '"}';

            try {
                $config = new \HiPay\Fullservice\HTTP\Configuration\Configuration($username, $password, $env);
                $clientProvider = new \HiPay\Fullservice\HTTP\SimpleHTTPClient($config);
                $gatewayClient = new \HiPay\Fullservice\Gateway\Client\GatewayClient($clientProvider);
                if ($this->method_details["operating_mode"] == OperatingMode::DIRECT_POST) {
                    $orderRequest = new \HiPay\Fullservice\Gateway\Request\Order\OrderRequest();
                    $orderRequest->paymentMethod = new \HiPay\Fullservice\Gateway\Request\PaymentMethod\CardTokenPaymentMethod(
                    );
                    $orderRequest->paymentMethod->cardtoken = $token;
                    $orderRequest->paymentMethod->eci = 7;
                    $orderRequest->paymentMethod->authentication_indicator = $this->method_details['activate_3d_secure'];
                    $orderRequest->payment_product = $brand;
                } else {
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
                if ($this->method_details["activate_basket"]) {
                    $orderRequest->description = "";
                    $products = $order->get_items();
                    foreach ($products as $product) {
                        //$variation_id = (int)$product['variation_id'];
                        $p = new WC_Product($product['product_id']);
                        $orderRequest->description .= $product['qty'] . "x " . $p->get_title() . ', ';
                    }
                    $orderRequest->description = substr($orderRequest->description, 0, -2);
                } else {
                    $orderRequest->description = __("Order #", 'hipayenterprise') . $order_id . " " . __(
                            'at',
                            'hipayenterprise'
                        ) . " " . $shop_title;
                }
                $orderRequest->ipaddr = $_SERVER ['REMOTE_ADDR'];
                $orderRequest->http_user_agent = $_SERVER ['HTTP_USER_AGENT'];
                $shipping = $woocommerce->cart->get_cart_shipping_total();
                $currency_symbol = get_woocommerce_currency_symbol();
                $shipping = str_replace($currency_symbol, "", $shipping);
                $thousands_sep = wp_specialchars_decode(
                    stripslashes(get_option('woocommerce_price_thousand_sep')),
                    ENT_QUOTES
                );
                $shipping = str_replace($thousands_sep, "", $shipping);
                $decimals_sep = wp_specialchars_decode(
                    stripslashes(get_option('woocommerce_price_decimal_sep')),
                    ENT_QUOTES
                );
                if ($decimals_sep != ".") {
                    $shipping = str_replace($decimals_sep, ".", $shipping);
                }
                $shipping = floatval(preg_replace('#[^\d.]#', '', $shipping));
                $orderRequest->shipping = $shipping;
                $orderRequest->tax = 0;


                if ($this->method_details["operating_mode"] != OperatingMode::DIRECT_POST) {
                    $orderRequest->authentication_indicator = $this->method_details['activate_3d_secure'];

                    if ($this->method_details['display_hosted_page'] == "redirect") {
                        $orderRequest->template = HiPay\Fullservice\Enum\Transaction\Template::BASIC_JS;
                    } else {
                        $orderRequest->template = "iframe-js";
                    }

                    $orderRequest->display_selector = (int)$this->method_details['display_card_selector'];
                    $orderRequest->multi_use = (int)$this->method_details['card_token'];
                    if ($this->method_details['css_url'] != "") {
                        $orderRequest->css = $this->woocommerce_hipayenterprise_methods['css_url'];
                    }
                }

                //check max min amount
                $all_methods = json_decode($this->method_details['woocommerce_hipayenterprise_methods_payments']);
                $max_amount = 0;
                $min_amount = -1;
                $countries_list = array();
                $currencies_list = array();
                $available_methods = array();

                foreach ($all_methods as $key => $value) {
                    $the_method = new HipayEnterprisePaymentMethodClass($value);
                    //check currency, country and amount
                    if ($the_method->get_is_active() &&
                        $the_method->get_is_credit_card() &&
                        $order_total <= $the_method->get_max_amount() &&
                        $order_total >= $the_method->get_min_amount() &&
                        (strpos(
                                $the_method->get_available_currencies(),
                                $current_currency
                            ) !== false) &&
                        (strpos(
                                $the_method->get_available_countries(),
                                $current_billing_country
                            ) !== false)) {
                        $available_methods[] = $the_method->get_key();
                    }
                }
                if ($this->method_details["operating_mode"] != OperatingMode::DIRECT_POST) {
                    $orderRequest->payment_product_list = implode(",", $available_methods);
                    $orderRequest->payment_product_category_list = '';
                }

                $orderRequest->email = $order->get_billing_email();

                $customerBillingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerBillingInfoRequest();
                $customerBillingInfo->firstname = $order->get_billing_first_name();
                $customerBillingInfo->lastname = $order->get_billing_last_name();
                $customerBillingInfo->email = $order->get_billing_email();
                $customerBillingInfo->country = $order->get_billing_country();
                $customerBillingInfo->streetaddress = $order->get_billing_address_1();
                $customerBillingInfo->streetaddress2 = $order->get_billing_address_2();
                $customerBillingInfo->city = $order->get_billing_city();
                $customerBillingInfo->state = $order->get_billing_state();
                $customerBillingInfo->zipcode = $order->get_billing_postcode();
                $orderRequest->customerBillingInfo = $customerBillingInfo;

                $customerShippingInfo = new \HiPay\Fullservice\Gateway\Request\Info\CustomerShippingInfoRequest();
                $customerShippingInfo->firstname = $order->get_shipping_first_name();
                $customerShippingInfo->lastname = $order->get_shipping_last_name();
                $customerShippingInfo->country = $order->get_shipping_country();
                $customerShippingInfo->streetaddress = $order->get_shipping_address_1();
                $customerShippingInfo->streetaddress2 = $order->get_shipping_address_2();
                $customerShippingInfo->city = $order->get_shipping_city();
                $customerShippingInfo->state = $order->get_shipping_state();
                $customerShippingInfo->zipcode = $order->get_shipping_postcode();
                $orderRequest->customerShippingInfo = $customerShippingInfo;

                $orderRequest->shipto_firstname = $order->get_shipping_first_name();
                $orderRequest->shipto_lastname = $order->get_shipping_last_name();
                $orderRequest->shipto_streetaddress = $order->get_shipping_address_1();
                $orderRequest->shipto_streetaddress2 = $order->get_shipping_address_2();
                $orderRequest->shipto_city = $order->get_shipping_city();
                $orderRequest->shipto_country = $order->get_shipping_country();
                $orderRequest->shipto_state = $order->get_shipping_state();
                $orderRequest->shipto_postcode = $order->get_shipping_postcode();

                if ($this->method_details["operating_mode"] != OperatingMode::DIRECT_POST) {
                    $transaction = $gatewayClient->requestHostedPaymentPage($orderRequest);
                    $redirectUrl = $transaction->getForwardUrl();
                    if ($redirectUrl != "") {
                        $order->add_order_note(__('Payment URL:', 'hipayenterprise') . " " . $redirectUrl);


                        $order_flag = $wpdb->get_row(
                            "SELECT order_id FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1"
                        );
                        if (isset($order_flag->order_id)) {
                            SELF::reset_stock_levels($order);
                            wc_reduce_stock_levels($order_id);
                            $wpdb->update(
                                $this->plugin_table,
                                array('amount' => $order_total, 'stocks' => 1, 'url' => $redirectUrl),
                                array('order_id' => $order_id)
                            );
                        } else {
                            wc_reduce_stock_levels($order_id);
                            $wpdb->insert(
                                $this->plugin_table,
                                array(
                                    'reference' => 0,
                                    'order_id' => $order_id,
                                    'amount' => $order_total,
                                    'stocks' => 1,
                                    'url' => $redirectUrl
                                )
                            );
                        }

                        if ($this->method_details['display_hosted_page'] == "iframe") {
                            return array(
                                'result' => 'success',
                                'redirect' => $order->get_checkout_payment_url(true)
                            );
                        } else {
                            return array('result' => 'success', 'redirect' => $redirectUrl);
                        }
                    } else {

                        throw new Exception(__('Error generating payment url.', 'hipayenterprise'));
                    }
                } else {
                    $transaction = $gatewayClient->requestNewOrder($orderRequest);
                    $redirectUrl = $transaction->getForwardUrl();

                    if ($transaction->getStatus() == TransactionStatus::CAPTURED ||
                        $transaction->getStatus() == TransactionStatus::AUTHORIZED ||
                        $transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED) {
                        $order_flag = $wpdb->get_row(
                            "SELECT order_id FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1"
                        );
                        if (isset($order_flag->order_id)) {
                            SELF::reset_stock_levels($order);
                            wc_reduce_stock_levels($order_id);
                            $wpdb->update(
                                $this->plugin_table,
                                array('amount' => $order_total, 'stocks' => 1, 'url' => $redirectUrl),
                                array('order_id' => $order_id)
                            );
                        } else {
                            wc_reduce_stock_levels($order_id);
                            $wpdb->insert(
                                $this->plugin_table,
                                array(
                                    'reference' => 0,
                                    'order_id' => $order_id,
                                    'amount' => $order_total,
                                    'stocks' => 1,
                                    'url' => $redirectUrl
                                )
                            );
                        }


                        return array(
                            'result' => 'success',
                            'redirect' => $order->get_checkout_order_received_url()
                        );
                    } else {
                        $reason = $transaction->getReason();
                        $order->add_order_note(__('Error:', 'hipayenterprise') . " " . $reason['message']);
                        throw new Exception(
                            __(
                                'Error processing payment:',
                                'hipayenterprise'
                            ) . " " . $reason['message']
                        );
                    }
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }


        public function receipt_page($order_id)
        {
            global $wpdb;

            $order = wc_get_order($order_id);
            $payment_url = $wpdb->get_row("SELECT url FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");

            if (!isset($payment_url->url)) {
                $order->get_cancel_order_url_raw();
            } elseif ($this->method_details["operating_mode"] == OperatingMode::DIRECT_POST &&
                $payment_url->url == "") {
                echo __(
                    "We have received your order payment. We will process the order as soon as we get the payment confirmation.",
                    "hipayenterprise"
                );
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
                    && Hipay_Helper::isInAuthorizedAmount($conf, $cartTotals)) {
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
                    if ($id == "hipayenterprise") {
                        if (!$gateway->isAvailableForCurrentCart()) {
                            unset($available_gateways [$id]);
                        }
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

            if ($cur_payment_method == 'hipayenterprise') {
                $order = new WC_Order($order_id);
                //capture status in db

                if ($captured_flag->captured == 1) {
                    return true;
                }

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
