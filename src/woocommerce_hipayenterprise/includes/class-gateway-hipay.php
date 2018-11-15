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

            $this->logs = new Hipay_Log($this);

            $this->settingsHandler = new Hipay_Settings_Handler($this);

            $this->settingsHipay = $this->confHelper->getConfigHipay();

            $this->icon = WP_PLUGIN_URL .
                "/" .
                plugin_basename(dirname(__FILE__)) .
                '/assets/images/hipay_logo-' .
                $this->get_option('payment_image') .
                '.png';

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


            add_action('woocommerce_after_order_notes', 'custom_checkout_field_hipay_enterprise');
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

        public function payment_fields()
        {
            global $woocommerce;

            if ($this->method_details['operating_mode'] == OperatingMode::HOSTED_PAGE) {
                if ($this->method_details['display_hosted_page'] == "redirect") {
                    _e('You will be redirected to an external payment page. Please do not refresh the page during the process.', $this->id);
                } else {
                    _e('Pay with your credit card.', $this->id);
                }
            } elseif ($this->method_details['operating_mode'] == OperatingMode::DIRECT_POST) {
                $username = (!$this->sandbox) ? $this->account_production_tokenization_username : $this->account_test_tokenization_username;
                $password = (!$this->sandbox) ? $this->account_production_tokenization_password : $this->account_test_tokenization_password;
                $env = ($this->sandbox) ? "stage" : "production";
                $customer_id = get_current_user_id(); ?>
                <script type="text/javascript"
                        src="<?php echo plugins_url('/vendor/bower_components/hipay-fullservice-sdk-js/dist/strings.js', __FILE__); ?>"></script>
                <script type="text/javascript"
                        src="<?php echo plugins_url('/vendor/bower_components/hipay-fullservice-sdk-js/dist/card-js.min.js', __FILE__); ?>"></script>
                <script type="text/javascript"
                        src="<?php echo plugins_url('/vendor/bower_components/hipay-fullservice-sdk-js/dist/card-tokenize.js', __FILE__); ?>"></script>
                <script type="text/javascript"
                        src="<?php echo plugins_url('/vendor/bower_components/hipay-fullservice-sdk-js/dist/hipay-fullservice-sdk.min.js', __FILE__); ?>"></script>


                <form id="tokenizerForm" action="" enctype="application/x-www-form-urlencoded"
                      class="form-horizontal hipay-form-17" method="post" name="tokenizerForm" autocomplete="off">
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
                        <ul>
                            <li class="error" id="hiPay_error_message"></li>
                        </ul>

                    </div>

                    <div class="card-js " data-icon-colour="#158CBA">
                        <div class="card-number-wrapper">
                            <input id="hipay-card-number" class="card-number my-custom-class" name="card-number"
                                   type="tel" placeholder="<?php echo __('Card Number', 'hipayenterprise'); ?>"
                                   maxlength="19" x-autocompletetype="cc-number" autocompletetype="cc-number"
                                   autocorrect="off" spellcheck="off" autocapitalize="off">
                            <div class="card-type-icon"></div>
                            <div class="icon">
                                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="3px" width="24px"
                                     height="17px" viewBox="0 0 216 146" enable-background="new 0 0 216 146"
                                     xml:space="preserve"><g>
                                        <path class="svg"
                                              d="M182.385,14.258c-2.553-2.553-5.621-3.829-9.205-3.829H42.821c-3.585,0-6.653,1.276-9.207,3.829c-2.553,2.553-3.829,5.621-3.829,9.206v99.071c0,3.585,1.276,6.654,3.829,9.207c2.554,2.553,5.622,3.829,9.207,3.829H173.18c3.584,0,6.652-1.276,9.205-3.829s3.83-5.622,3.83-9.207V23.464C186.215,19.879,184.938,16.811,182.385,14.258z M175.785,122.536c0,0.707-0.258,1.317-0.773,1.834c-0.516,0.515-1.127,0.772-1.832,0.772H42.821c-0.706,0-1.317-0.258-1.833-0.773c-0.516-0.518-0.774-1.127-0.774-1.834V73h135.571V122.536z M175.785,41.713H40.214v-18.25c0-0.706,0.257-1.316,0.774-1.833c0.516-0.515,1.127-0.773,1.833-0.773H173.18c0.705,0,1.316,0.257,1.832,0.773c0.516,0.517,0.773,1.127,0.773,1.833V41.713z"
                                              style="fill: rgb(21, 140, 186);"></path>
                                        <rect class="svg" x="50.643" y="104.285" width="20.857" height="10.429"
                                              style="fill: rgb(21, 140, 186);"></rect>
                                        <rect class="svg" x="81.929" y="104.285" width="31.286" height="10.429"
                                              style="fill: rgb(21, 140, 186);"></rect>
                                    </g></svg>
                            </div>
                        </div>

                        <div class="name-wrapper">
                            <input id="hipay-the-card-name-id" class="name" name="card-holders-name"
                                   placeholder="<?php echo __('Name on card', 'hipayenterprise'); ?>">
                            <div class="icon">
                                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="4px" width="24px"
                                     height="16px" viewBox="0 0 216 146" enable-background="new 0 0 216 146"
                                     xml:space="preserve"><g>
                                        <path class="svg"
                                              d="M107.999,73c8.638,0,16.011-3.056,22.12-9.166c6.111-6.11,9.166-13.483,9.166-22.12c0-8.636-3.055-16.009-9.166-22.12c-6.11-6.11-13.484-9.165-22.12-9.165c-8.636,0-16.01,3.055-22.12,9.165c-6.111,6.111-9.166,13.484-9.166,22.12c0,8.637,3.055,16.01,9.166,22.12C91.99,69.944,99.363,73,107.999,73z"
                                              style="fill: rgb(21, 140, 186);"></path>
                                        <path class="svg"
                                              d="M165.07,106.037c-0.191-2.743-0.571-5.703-1.141-8.881c-0.57-3.178-1.291-6.124-2.16-8.84c-0.869-2.715-2.037-5.363-3.504-7.943c-1.466-2.58-3.15-4.78-5.052-6.6s-4.223-3.272-6.965-4.358c-2.744-1.086-5.772-1.63-9.085-1.63c-0.489,0-1.63,0.584-3.422,1.752s-3.815,2.472-6.069,3.911c-2.254,1.438-5.188,2.743-8.799,3.909c-3.612,1.168-7.237,1.752-10.877,1.752c-3.639,0-7.264-0.584-10.876-1.752c-3.611-1.166-6.545-2.471-8.799-3.909c-2.254-1.439-4.277-2.743-6.069-3.911c-1.793-1.168-2.933-1.752-3.422-1.752c-3.313,0-6.341,0.544-9.084,1.63s-5.065,2.539-6.966,4.358c-1.901,1.82-3.585,4.02-5.051,6.6s-2.634,5.229-3.503,7.943c-0.869,2.716-1.589,5.662-2.159,8.84c-0.571,3.178-0.951,6.137-1.141,8.881c-0.19,2.744-0.285,5.554-0.285,8.433c0,6.517,1.983,11.664,5.948,15.439c3.965,3.774,9.234,5.661,15.806,5.661h71.208c6.572,0,11.84-1.887,15.806-5.661c3.966-3.775,5.948-8.921,5.948-15.439C165.357,111.591,165.262,108.78,165.07,106.037z"
                                              style="fill: rgb(21, 140, 186);"></path>
                                    </g></svg>
                            </div>
                        </div>
                        <div class="expiry-container">
                            <div class="expiry-wrapper">
                                <div><input class="expiry" type="tel" placeholder="MM / YY" maxlength="7"
                                            x-autocompletetype="cc-exp" autocompletetype="cc-exp" autocorrect="off"
                                            spellcheck="off" autocapitalize="off" id="hipay-expiry"><input type="hidden"
                                                                                                           name="expiry-month"
                                                                                                           id="hipay-expiry-month"><input
                                            type="hidden" name="expiry-year" id="hipay-expiry-year"></div>
                                <div class="icon">
                                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="4px" width="24px"
                                         height="16px" viewBox="0 0 216 146" enable-background="new 0 0 216 146"
                                         xml:space="preserve"><path class="svg"
                                                                    d="M172.691,23.953c-2.062-2.064-4.508-3.096-7.332-3.096h-10.428v-7.822c0-3.584-1.277-6.653-3.83-9.206c-2.554-2.553-5.621-3.83-9.207-3.83h-5.213c-3.586,0-6.654,1.277-9.207,3.83c-2.554,2.553-3.83,5.622-3.83,9.206v7.822H92.359v-7.822c0-3.584-1.277-6.653-3.83-9.206c-2.553-2.553-5.622-3.83-9.207-3.83h-5.214c-3.585,0-6.654,1.277-9.207,3.83c-2.553,2.553-3.83,5.622-3.83,9.206v7.822H50.643c-2.825,0-5.269,1.032-7.333,3.096s-3.096,4.509-3.096,7.333v104.287c0,2.823,1.032,5.267,3.096,7.332c2.064,2.064,4.508,3.096,7.333,3.096h114.714c2.824,0,5.27-1.032,7.332-3.096c2.064-2.064,3.096-4.509,3.096-7.332V31.286C175.785,28.461,174.754,26.017,172.691,23.953z M134.073,13.036c0-0.761,0.243-1.386,0.731-1.874c0.488-0.488,1.113-0.733,1.875-0.733h5.213c0.762,0,1.385,0.244,1.875,0.733c0.488,0.489,0.732,1.114,0.732,1.874V36.5c0,0.761-0.244,1.385-0.732,1.874c-0.49,0.488-1.113,0.733-1.875,0.733h-5.213c-0.762,0-1.387-0.244-1.875-0.733s-0.731-1.113-0.731-1.874V13.036z M71.501,13.036c0-0.761,0.244-1.386,0.733-1.874c0.489-0.488,1.113-0.733,1.874-0.733h5.214c0.761,0,1.386,0.244,1.874,0.733c0.488,0.489,0.733,1.114,0.733,1.874V36.5c0,0.761-0.244,1.386-0.733,1.874c-0.489,0.488-1.113,0.733-1.874,0.733h-5.214c-0.761,0-1.386-0.244-1.874-0.733c-0.488-0.489-0.733-1.113-0.733-1.874V13.036z M165.357,135.572H50.643V52.143h114.714V135.572z"
                                                                    style="fill: rgb(21, 140, 186);"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="cvc-container">
                            <div class="cvc-wrapper"><input id="hipay-cvc" class="cvc" data-toggle="tooltip"
                                                            title="<?php echo __(
                                                                "3-digit security code usually found on the back of your card. American Express cards have a 4-digit code located on the front.",
                                                                "hipayenterprise"
                                                            ); ?>"
                                                            name="cvc" type="tel" placeholder="CVC" maxlength="3"
                                                            x-autocompletetype="cc-csc" autocompletetype="cc-csc"
                                                            autocorrect="off" spellcheck="off" autocapitalize="off">
                                <div class="icon">
                                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="3px" width="24px"
                                         height="17px" viewBox="0 0 216 146" enable-background="new 0 0 216 146"
                                         xml:space="preserve"><path class="svg"
                                                                    d="M152.646,70.067c-1.521-1.521-3.367-2.281-5.541-2.281H144.5V52.142c0-9.994-3.585-18.575-10.754-25.745c-7.17-7.17-15.751-10.755-25.746-10.755s-18.577,3.585-25.746,10.755C75.084,33.567,71.5,42.148,71.5,52.142v15.644h-2.607c-2.172,0-4.019,0.76-5.54,2.281c-1.521,1.52-2.281,3.367-2.281,5.541v46.929c0,2.172,0.76,4.019,2.281,5.54c1.521,1.52,3.368,2.281,5.54,2.281h78.214c2.174,0,4.02-0.76,5.541-2.281c1.52-1.521,2.281-3.368,2.281-5.54V75.607C154.93,73.435,154.168,71.588,152.646,70.067z M128.857,67.786H87.143V52.142c0-5.757,2.037-10.673,6.111-14.746c4.074-4.074,8.989-6.11,14.747-6.11s10.673,2.036,14.746,6.11c4.073,4.073,6.11,8.989,6.11,14.746V67.786z"
                                                                    style="fill: rgb(21, 140, 186);"></path></svg>
                                </div>
                            </div>

                        </div>
                    </div>
                    <input type="hidden" id="hipay_user_token" value='<?php echo $token_flag_json; ?>'>
                    <?php

                    if ($this->method_details['card_token'] == "1" && $customer_id > 0) {
                        ?>
                        <span class="custom-checkbox"><br><input id="saveTokenHipay" type="checkbox"
                                                                 name="saveTokenHipay" <?php if ($token_flag_json !=
                                "") {
                                echo "CHECKED";
                            } ?>><label
                                    for="saveTokenHipay"><?php echo __(
                                    "Save credit card (One click payment)",
                                    "hipayenterprise"
                                ); ?></label>
						        </span>
                        <?php
                    } ?>

                    <div><br>
                        <button type="submit" class="button alt" name="woocommerce_tokenize_order"
                                id="woocommerce_tokenize_order"
                                value="<?php echo __('Validate Credit Card', 'hipayenterprise'); ?>"
                                data-value="<?php echo __(
                                    'Validate Credit Card',
                                    'hipayenterprise'
                                ); ?>"><?php echo __(
                                'Validate Credit Card',
                                'hipayenterprise'
                            ); ?></button>
                    </div>
                </form>

                <div id="hipayPaymentInformations" style="display:none;">
                    <div
                            id="hipayPaymentInformationsTitle"><?php echo __(
                            "Validate your Payment Information",
                            'hipayenterprise'
                        ); ?></div>

                    <div id="hipayPaymentInformationsInfo"><?php echo __("Card Number", 'hipayenterprise'); ?>: <span
                                id="cardnumberinfo"></span></div>
                    <div id="hipayPaymentInformationsInfo"><?php echo __("Name on Card", 'hipayenterprise'); ?>: <span
                                id="nameoncardinfo"></span></div>
                    <div id="hipayPaymentInformationsInfo"><?php echo __("Expiry date", 'hipayenterprise'); ?>: <span
                                id="expirydateinfo"></span></div>

                    <div><br>
                        <button type="button" class="button alt" name="woocommerce_tokenize_order_reset"
                                id="woocommerce_tokenize_order_reset"
                                value="<?php echo __('Change Credit Card', 'hipayenterprise'); ?>"
                                data-value="<?php echo __(
                                    'Change Credit Card',
                                    'hipayenterprise'
                                ); ?>"><?php echo __(
                                'Change Credit Card',
                                'hipayenterprise'
                            ); ?></button>
                        <?php if ($token_flag_json != "") {
                            ?>
                            <button type="button" class="button alt" name="woocommerce_tokenize_order_delete"
                                    id="woocommerce_tokenize_order_delete"
                                    value="<?php echo __('Remove Credit Card', 'hipayenterprise'); ?>"
                                    data-value="<?php echo __(
                                        'Remove Credit Card',
                                        'hipayenterprise'
                                    ); ?>"><?php echo __(
                                    'Remove Credit Card',
                                    'hipayenterprise'
                                ); ?></button>
                            <?php
                        } ?>
                    </div>


                </div>

                <script>

                    jQuery(function ($) {


                        $('form[name="checkout"] input[name="payment_method"]').eq(0).prop('checked', true).attr('checked', 'checked');
                        usingGateway();

                        $('form[name="checkout"] input[type=radio][name=payment_method]').change(function () {
                            if (this.value == 'hipayenterprise') {
                                usingGateway();
                            } else {
                                if ($('#place_order').css('display') == 'none') $("#place_order").show();
                            }
                        });


                        $(".expiry").change(function () {
                            $("input[name='expiry-month']").val($(".expiry").val().replace(/\s/g, '').substring(0, 2));
                            $("input[name='expiry-year']").val($(".expiry").val().replace(/\s/g, '').substring(3, 5));
                        });


                        function usingGateway() {

                            if ($('form[name="checkout"] input[name="payment_method"]:checked').val() == 'hipayenterprise') {


                                if ($("#hipay_user_token").val() != "") {
                                    var tokenObj = JSON.parse($("#hipay_user_token").val());
                                    console.log(tokenObj.id);
                                    $("#cardnumberinfo").html(tokenObj.pan + " (" + tokenObj.brand + ")");
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

                                $('#woocommerce_tokenize_order_reset').click(function (e) {
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

                                $('#woocommerce_tokenize_order_delete').click(function (e) {
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


                                $('#woocommerce_tokenize_order').click(function (e) {
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
                                    HiPay.setTarget('<?php echo $env; ?>');
                                    HiPay.setCredentials('<?php echo $username; ?>', '<?php echo $password; ?>');
                                    HiPay.create(params,

                                        function (result) {

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

                                            $("#cardnumberinfo").html(result.pan + " (" + result.brand + ")");
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
                                            $("#hipay_direct_error").val("<?php echo __(
                                                'Error processing payment information.',
                                                'hipayenterprise'
                                            ); ?>");
                                            $("#hiPay_error_message").html("<?php echo __(
                                                'Please check your payment information.',
                                                'hipayenterprise'
                                            ); ?><br>");

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

                    if ($transaction->getStatus() == TransactionStatus::CAPTURED || $transaction->getStatus() == TransactionStatus::AUTHORIZED || $transaction->getStatus() == TransactionStatus::CAPTURE_REQUESTED) {
                        $order_flag = $wpdb->get_row("SELECT order_id FROM $this->plugin_table WHERE order_id = $order_id LIMIT 1");
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
                    && Hipay_Helper::isInAuthorizedAmount($conf,$cartTotals)) {
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
