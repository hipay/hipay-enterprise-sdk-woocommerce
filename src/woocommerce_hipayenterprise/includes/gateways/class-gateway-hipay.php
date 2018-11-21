<?php

if (!defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}

/**
 *
 * WC_Gateway_Hipay
 *
 * @extends     WC_Payment_Gateway
 */
if (!class_exists('WC_Gateway_Hipay')) {
    class Gateway_Hipay extends Hipay_Gateway_Abstract
    {

        public function __construct()
        {
            $this->id = 'hipayenterprise_credit_card';

            $this->supports = array(
                'products',
                'refunds'
            );

            $this->has_fields = true;

            $this->icon = WC_HIPAYENTERPRISE_URL_ASSETS . '/images/credit_card.png';

            $this->title = __('Pay by Credit Card', $this->id);

            $this->method_title = __('HiPay Enterprise Credit Card', $this->id);

            $this->method_description = __(
                'Local and international payments using Hipay Enterprise.',
                'hipayenterprise'
            );

            parent::__construct();

            $this->init_form_fields();

            $this->init_settings();

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
            parent::addActions();
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            add_action('woocommerce_after_order_notes', array($this, 'custom_checkout_field_hipay_enterprise'));
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
            $settings["payment"]["local_payment"] = $this->confHelper->getLocalPayments();

            $this->confHelper->saveConfiguration($settings);
        }

        /**
         *  Get detail field for payment method
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
            $this->account = include(plugin_dir_path(__FILE__) . '../admin/settings/settings-account.php');
            $this->fraud = include(plugin_dir_path(__FILE__) . '../admin/settings/settings-fraud.php');
            $this->methods = include(plugin_dir_path(__FILE__) . '../admin/settings/settings-methods.php');
            $this->faqs = include(plugin_dir_path(__FILE__) . '../admin/settings/settings-faq.php');
            $this->log = include(plugin_dir_path(__FILE__) . '../admin/settings/settings-logs.php');
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

        /**
         *
         */
        public function admin_options()
        {
            parent::admin_options();
            $this->process_template('admin-general-settings.php');
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
                $this->logs->logInfos(" # Process Payment for  " . $order_id);

                $redirect = $this->apiRequestHandler->handleCreditCard(array("order_id" => $order_id));

                return array(
                    'result' => 'success',
                    'redirect' => $redirect,
                );
            } catch (Hipay_Payment_Exception $e) {
                wc_add_notice(
                    __('Sorry, we cannot process your payment.. Please try again.', 'woocommerce-gateway-hipay'),
                    'error'
                );
                $this->logs->logException($e);
                return array(
                    'result' => 'fail',
                    'redirect' => $e->getRedirectUrl(),
                );
            }
        }

        /**
         * Process Hipay Receipt page
         *
         * @param $order_id
         */
        public function receipt_page($order_id)
        {
            try {
                $order = wc_get_order($order_id);
                $paymentUrl = $order->get_meta("_hipay_pay_url");

                if (empty($paymentUrl)) {
                    $this->logs->logInfos(" # No payment Url " . $order_id);
                    $this->generate_error_receipt();
                } else {
                    $this->logs->logInfos(" # Receipt_page " . $order_id);

                    switch ($this->confHelper->getPaymentGlobal()["operating_mode"]) {
                        case OperatingMode::DIRECT_POST:
                            $this->generate_common_receipt();
                            break;
                        case  OperatingMode::HOSTED_PAGE:
                            if ($this->confHelper->getPaymentGlobal()["display_hosted_page"] = "iframe") {
                                $this->generate_iframe_page($paymentUrl);
                            }
                            break;
                    }
                }
            } catch (Exception $e) {
                $this->generate_error_receipt();
                $this->logs->logException($e);
            }
        }

        /**
         *  Generate HTML for error in iframe request
         *
         */
        private function generate_error_receipt()
        {
            echo __(
                "Sorry, we cannot process your payment.. Please try again.",
                "hipayenterprise"
            );
        }

        /**
         *  Generate HTML for direct integration
         *
         */
        private function generate_common_receipt()
        {
            echo __(
                "We have received your order payment. We will process the order as soon as we get the payment confirmation.",
                "hipayenterprise"
            );
        }

        /**
         *  Generate HTML for iframe integration
         *
         * @param $paymentUrl
         */
        private function generate_iframe_page($paymentUrl)
        {
            echo '<div id="wc_hipay_iframe_container">
                    <iframe id="wc_hipay_iframe" name="wc_hipay_iframe" width="100%" height="475" style="border: 0;" src="' .
                esc_html($paymentUrl) .
                '" allowfullscreen="" frameborder="0"></iframe>
                  </div>';
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
    }
}
