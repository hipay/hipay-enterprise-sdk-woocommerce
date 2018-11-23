<div id="accordion-faq" class="accordion-container">
    <ul>
        <li class="control-section accordion-section" id="accordion-section-title_tagline-0">
            <h3 title="" tabindex="0" class="accordion-section-title">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e(
                    'How do I get my HiPay API credentials ?',
                    Hipay_Gateway_Abstract::TEXT_DOMAIN
                ); ?>
            </h3>
            <ul class="accordion-section-content">
                <li>
                    <?php _e('You need to generate', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?>
                    <strong>
                        <?php _e('API credentials', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?>
                    </strong>
                    <?php _e(
                        'to send requests to the HiPay Enterprise platform. To do so, go to the "Integration" section of your HiPay Enterprise back office, then to "Security Settings".',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <?php _e('To be sure that your credentials have the proper accessibility', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <?php _e('Scroll down to "Api credentials".', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?>
                </li>
                <li>
                    <?php _e(
                        'Click on the edit icon next to the credentials you want to use.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <strong>
                        <?php _e(
                            'Private credentials',
                            Hipay_Gateway_Abstract::TEXT_DOMAIN
                        ); ?>
                    </strong>
                </li>
                <li>
                    <?php _e(
                        'Your credentials must be granted to',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <strong>
                        <?php _e(
                            'Order',
                            Hipay_Gateway_Abstract::TEXT_DOMAIN
                        ); ?>
                    </strong>
                </li>
                <li>
                    <?php _e(
                        'Create a payment page',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Process an order through the API',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Get transaction informations',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <strong>
                        <?php _e(
                            'Maintenance',
                            Hipay_Gateway_Abstract::TEXT_DOMAIN
                        ); ?>
                    </strong>
                </li>
                <li>
                    <?php _e(
                        'Capture',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Refund',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Accept/Deny',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Cancel',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Finalize',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <strong>
                        <?php _e(
                            'Public credentials',
                            Hipay_Gateway_Abstract::TEXT_DOMAIN
                        ); ?>
                    </strong>
                </li>
                <li>
                    <?php _e(
                        'Your credentials must be granted to',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <strong>
                        <?php _e(
                            'Tokenization',
                            Hipay_Gateway_Abstract::TEXT_DOMAIN
                        ); ?>
                    </strong>
                </li>
                <li>
                    <?php _e(
                        'Tokenize a card',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <strong>
                        <?php _e(
                            'Order',
                            Hipay_Gateway_Abstract::TEXT_DOMAIN
                        ); ?>
                    </strong>
                </li>
                <li>
                    <?php _e(
                        'Get transaction details with public credentials',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Process an order through the API with public credentials',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Create a payment page with public credentials',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
        </li>
        <li class="control-section accordion-section" id="accordion-section-title_tagline-1">
            <h3 title="" tabindex="1" class="accordion-section-title">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e(
                    'How do I fill in the API IDs in the module ?',
                    Hipay_Gateway_Abstract::TEXT_DOMAIN
                ); ?>
            </h3>
            <ul class="accordion-section-content">
                <li>
                    <?php _e(
                        'In the “HiPay Enterprise Credit Card” payment method configuration, go to “Plugin Settings”.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'If your module is in Test mode, you can specify the IDs in the Test area. If it is in Production mode, do the same in the Production area.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Enter the corresponding username, password and secret passphrase.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Public credentials are mandatory if you do not use the payment form hosted by HiPay.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'After specifying these identifiers, make a test payment to check that they are valid and that they have the proper rights.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
        </li>
        <li class="control-section accordion-section" id="accordion-section-title_tagline-1">
            <h3 title="" tabindex="1" class="accordion-section-title">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e(
                    'Why do orders never reach the “On hold” status?',
                    Hipay_Gateway_Abstract::TEXT_DOMAIN
                ); ?>
            </h3>
            <ul class="accordion-section-content">
                <li>
                    <?php _e(
                        'First, check if the notification URL is correctly entered in your HiPay Enterprise back office.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'In your HiPay Enterprise back office, in the "Integration" section , click on "Notifications".',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Notification URL: http: // www.[Your-domain.com] /wc-api/WC_HipayEnterprise',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Request method: HTTP POST',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'I want to be notified for the following transaction statuses: ALL',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
            <ul class="accordion-section-content">
                <li>
                    <?php _e(
                        'Then make a test payment.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'From the "Notifications" section of your HiPay Enterprise back office, in the transaction details, you can also check the status of the call.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'If notifications are sent, there may be an internal module error.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'To check if an error occurred during the notification, check the hipay-error and hipay-callback logs.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
        </li>
        <li class="control-section accordion-section" id="accordion-section-title_tagline-1">
            <h3 title="" tabindex="1" class="accordion-section-title">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e(
                    'What to do when payment errors occur ?',
                    Hipay_Gateway_Abstract::TEXT_DOMAIN
                ); ?>
            </h3>
            <ul class="accordion-section-content">
                <li>
                    <?php _e(
                        'Make sure that your credentials are correctly set and that the module is in the mode you want (Test or Production).',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Check that the related payment methods are activated in your contract(s).',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Check the version of the installed module, and upgrade the module if the version is old.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Check HiPay logs to see if any errors appear. Then send these logs to the HiPay Support team.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
                <li>
                    <?php _e(
                        'Check that your servers are not behind a proxy. If so, provide the proxy information in the module configuration.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
        </li>
        <li class="control-section accordion-section" id="accordion-section-title_tagline-1">
            <h3 title="" tabindex="1" class="accordion-section-title">
                <span class="dashicons dashicons-editor-help"></span>
                <?php _e(
                    'How come my payment method(s) do(es) not appear in the order funnel ?',
                    Hipay_Gateway_Abstract::TEXT_DOMAIN
                ); ?>
            </h3>
            <ul class="accordion-section-content">
                <li>
                    <?php _e(
                        'Check in the HiPay module configuration that the payment method(s) is/are enabled for test countries and currencies.',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?>
                </li>
            </ul>
        </li>
    </ul>
</div>

<script>
    jquery(function ($) {
        $("#accordion-faq").accordion();
    });
</script>
