<?php $classHostedSettings = $paymentCommon["operating_mode"] ==
"hosted_page" ? $classHostedSettings = "hidden " : ""; ?>

<h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
    <i class='dashicons dashicons-admin-generic'></i>
    <?php _e('Global settings', "hipayenterprise"); ?>
</h3>

<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2" for="operating_mode">
            <?php _e('Operating mode', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <select class="select form-control" name="operating_mode" id="operating_mode" style="">
                <option value="hosted_page" <?php if ($paymentCommon["operating_mode"] == OperatingMode::HOSTED_PAGE) {
                    echo " SELECTED";
                } ?>><?php _e('Hosted page', "hipayenterprise"); ?></option>
                <option value="hosted_fields" <?php if ($paymentCommon["operating_mode"] ==
                    OperatingMode::HOSTED_FIELDS) {
                    echo " SELECTED";
                } ?>><?php _e('Hosted Fields', "hipayenterprise"); ?></option>
            </select>
            <div class="help-block">
                <ul>
                    <li><?php _e(
                            "Hosted Fields: The customer completes his banking information directly on the merchant's site but the form fields are hosted by HiPay. 
                            This mode is only valid for credit cards.",
                            "hipayenterprise"
                        ); ?></li>
                    <li><?php _e(
                            "Hosted Page: The customer is redirected to a secured payment page hosted by HiPay.",
                            "hipayenterprise"
                        ); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row <?php if ($paymentCommon["operating_mode"] == OperatingMode::HOSTED_PAGE) {
        echo "hidden ";
    } ?>directPost_page_config ">
        <div class="col-lg-2"></div>
        <div class="col-lg-8">
            <div class="form-group">
                <label class="control-label col-lg-2" for="color">
                    <?php _e('Color', "hipayenterprise"); ?>
                </label>
                <div class="col-lg-8">
                    <input class="form-control color-picker" type="text" name="color" id="color" style=""
                        value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["color"]); ?>"
                        placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2" for="fontFamily">
                    <?php _e('Font Family', "hipayenterprise"); ?>
                </label>
                <div class="col-lg-8">
                    <input class="form-control" type="text" name="fontFamily" id="fontFamily" style="" value="<?php echo esc_textarea(
                               $paymentCommon["hosted_fields_style"]["base"]["fontFamily"]
                           ); ?>" placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2" for="fontSize">
                    <?php _e('Font Size', "hipayenterprise"); ?>
                </label>
                <div class="col-lg-8">
                    <input class="form-control" type="text" name="fontSize" id="fontSize" style="" value="<?php echo esc_textarea(
                               $paymentCommon["hosted_fields_style"]["base"]["fontSize"]
                           ); ?>" placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2" for="fontWeight">
                    <?php _e('Font Weight', "hipayenterprise"); ?>
                </label>
                <div class="col-lg-8">
                    <input class="form-control" type="text" name="fontWeight" id="fontWeight" style="" value="<?php echo esc_textarea(
                               $paymentCommon["hosted_fields_style"]["base"]["fontWeight"]
                           ); ?>" placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2" for="placeholderColor">
                    <?php _e('PlaceHolder color', "hipayenterprise"); ?>
                </label>
                <div class="col-lg-8">
                    <input class="form-control color-picker" type="text" name="placeholderColor" id="placeholderColor" style=""
                        value="<?php echo esc_textarea(
                               $paymentCommon["hosted_fields_style"]["base"]["placeholderColor"]
                           ); ?>" placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="caretColor">
                    <?php _e('Caret color', "hipayenterprise"); ?>
                </label>
                <div class="col-lg-8">
                    <input class="form-control color-picker" type="text" name="caretColor" id="caretColor" style="" value="<?php echo esc_textarea(
                               $paymentCommon["hosted_fields_style"]["base"]["caretColor"]
                           ); ?>" placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2" for="iconColor">
                    <?php _e('Icon color', "hipayenterprise"); ?>
                </label>
                <div class="col-lg-8">
                    <input class="form-control color-picker" type="text" name="iconColor" id="iconColor" style="" value="<?php echo esc_textarea(
                               $paymentCommon["hosted_fields_style"]["base"]["iconColor"]
                           ); ?>" placeholder="">
                </div>
            </div>
        </div>
    </div>
    <div class="row <?php if ($paymentCommon["operating_mode"] != OperatingMode::HOSTED_PAGE) {
        echo "hidden ";
    } ?>hosted_page_config">
        <div class="form-group">
            <label class="control-label col-lg-2" for="display_hosted_page">
                <?php _e('Display Hosted Page', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <select class="select form-control" name="display_hosted_page" id="display_hosted_page" style="">
                    <option value="redirect" <?php if ($paymentCommon["display_hosted_page"] == "redirect") {
                        echo " SELECTED";
                    } ?>><?php _e('Redirect', "hipayenterprise"); ?></option>
                    <option value="iframe" <?php if ($paymentCommon["display_hosted_page"] == "iframe") {
                        echo " SELECTED";
                    } ?>><?php _e('IFrame', "hipayenterprise"); ?></option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-2" for="display_card_selector">
                <?php _e('Display card selector', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <input class="form-control" type="checkbox" name="display_card_selector" id="display_card_selector"
                    style="" value="1" <?php if ($paymentCommon["display_card_selector"]) {
                    echo 'checked="checked"';
                } ?>>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-2" for="display_cancel_button">
                <?php _e('Display cancel button', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <select class="select form-control" name="display_cancel_button" id="display_cancel_button" style="">
                    <option value="1" <?php if ($paymentCommon["display_cancel_button"] == "1") {
                        echo " SELECTED";
                    } ?>><?php _e('Yes', "hipayenterprise"); ?></option>
                    <option value="0" <?php if ($paymentCommon["display_cancel_button"] == "0") {
                        echo " SELECTED";
                    } ?>><?php _e('No', "hipayenterprise"); ?></option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-2" for="css_url">
                <?php _e('CSS url', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <input class="form-control" type="text" name="css_url" id="css_url" style=""
                    value="<?php echo esc_textarea($paymentCommon["css_url"]); ?>" placeholder="">
                <div class="help-block"><?php _e(
                        "URL to your CSS (style sheet) to customize your hosted page or iFrame (Important: the HTTPS protocol is required).",
                        "hipayenterprise"
                    ); ?>
                </div>
            </div>
        </div>


    </div>

    <div class="form-group">
        <label class="control-label col-lg-2" for="capture_mode">
            <?php _e('Capture', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <select class="select form-control" name="capture_mode" id="capture_mode" style="">
                <option value="automatic" <?php if ($paymentCommon["capture_mode"] == "automatic") {
                    echo " SELECTED";
                } ?>><?php _e('Automatic', "hipayenterprise"); ?></option>
                <option value="manual" <?php if ($paymentCommon["capture_mode"] == "manual") {
                    echo " SELECTED";
                } ?>><?php _e('Manual', "hipayenterprise"); ?></option>
            </select>
            <div class="help-block">
                <ul>
                    <li>
                        <?php _e(
                            "Manual: if all transactions will be captured manually either from the Hipay Back office or from your admin in Woocommerce.",
                            "hipayenterprise"
                        ); ?>
                    </li>
                    <li>
                        <?php _e(
                            "Automatic: if all transactions will be captured automatically.",
                            "hipayenterprise"
                        ); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="skip_onhold">
            <?php _e('Skip on-hold status', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="skip_onhold" id="skip_onhold" style="" value="1" <?php if ($paymentCommon["skip_onhold"]) {
                echo 'checked="checked"';
            } ?>>
            <div class="help-block">
                <ul>
                    <li>
                        <?php _e(
                            "Indicates whether a command should go through the on-hold status during an automatic capture.",
                            "hipayenterprise"
                        ); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="card_token">
            <?php _e('Use Oneclick', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="card_token" id="card_token" value="1" <?php if ($paymentCommon["card_token"]) echo 'checked="checked"'; ?>>
        </div>
    </div>

    <div id="one_click_params" class="one_click_params" style="display: none">
        <div class="form-group">
            <div class="col-lg-2"></div>
            <label class="control-label col-lg-2" for="number_saved_cards_displayed">
            <span class="label-tooltip" data-toggle="tooltip" title="<?php _e('Maximum number of saved cards displayed by default.', "hipayenterprise"); ?>">
                <?php _e('Number of saved cards displayed', "hipayenterprise"); ?>
            </span>
            </label>
            <div class="col-lg-4">
                <input id="number_saved_cards_displayed" class="form-control" type="text" name="number_saved_cards_displayed"
                       value="<?php if ($paymentCommon["number_saved_cards_displayed"]) echo $paymentCommon["number_saved_cards_displayed"]; ?>">
                <p class="help-block align-left">
                    <span class="dashicons dashicons-warning"></span>
                    <?php _e("Leaving the field empty will display all the customer's saved cards.", "hipayenterprise"); ?>
                </p>
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-2"></div>
            <label class="control-label col-lg-2" for="switch_color_input">
            <span class="label-tooltip" data-toggle="tooltip" title="<?php _e('Color of card save button.', "hipayenterprise"); ?>">
                <?php _e('Save button color', "hipayenterprise"); ?>
            </span>
            </label>
            <div class="col-lg-4">
                <div class="color_inputs">
                    <input id="switch_color_input" class="form-control color-picker" type="text" name="switch_color_input"
                           value="<?php if ($paymentCommon["switch_color_input"]) echo $paymentCommon["switch_color_input"]; ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-2"></div>
            <label class="control-label col-lg-2" for="checkbox_color_input">
            <span class="label-tooltip" data-toggle="tooltip" title="<?php _e('Color of the selected saved card highlight.', "hipayenterprise"); ?>">
                <?php _e('Highlight color', "hipayenterprise"); ?>
            </span>
            </label>
            <div class="col-lg-4">
                <div class="color_inputs">
                    <input id="checkbox_color_input" class="form-control color-picker" type="text" name="checkbox_color_input"
                           value="<?php if ($paymentCommon["checkbox_color_input"]) echo $paymentCommon["checkbox_color_input"]; ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-2" for="activate_basket">
            <?php _e('Customer\'s cart sending', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="activate_basket" id="activate_basket" style="" value="1" <?php if ($paymentCommon["activate_basket"]) {
                echo 'checked="checked"';
            } ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-2" for="log_infos">
            <?php _e('Logs information', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="log_infos" id="log_infos" style="" value="1" <?php if ($paymentCommon["log_infos"]) {
                echo 'checked="checked"';
            } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="send_url_notification">
            <?php _e('Send url Notification', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="send_url_notification" id="send_url_notification" style=""
                value="1" <?php if ($paymentCommon["send_url_notification"]) {
                echo 'checked="checked"';
        } ?>>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="sdk_js_url">
            <?php _e('SDK js url', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="text" name="sdk_js_url" id="sdk_js_url" style=""
                value="<?php echo $paymentCommon["sdk_js_url"] ?>" />
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="activate_3d_secure">
            <?php _e('Activate 3-D Secure', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <select class="select form-control" name="activate_3d_secure" id="activate_3d_secure" style="">
                <option value="0" <?php if ($paymentCommon["activate_3d_secure"] == ThreeDS::THREE_D_S_DISABLED) {
                    echo " SELECTED";
                } ?>><?php _e('Deactivated', "hipayenterprise"); ?></option>
                <option value="1" <?php if ($paymentCommon["activate_3d_secure"] == ThreeDS::THREE_D_S_TRY_ENABLE_ALL) {
                    echo " SELECTED";
                } ?>><?php _e('Try to enable for all transactions', "hipayenterprise"); ?></option>
                <option value="2" <?php if ($paymentCommon["activate_3d_secure"] ==
                    ThreeDS::THREE_D_S_FORCE_ENABLE_ALL) {
                    echo " SELECTED";
                } ?>><?php _e('Force for all transactions', "hipayenterprise"); ?></option>
            </select>
        </div>
    </div>
</div>