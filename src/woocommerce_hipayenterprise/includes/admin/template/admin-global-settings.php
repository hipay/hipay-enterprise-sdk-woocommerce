<?php $classHostedSettings = $paymentCommon["operating_mode"] == "hosted_page" ? $classHostedSettings = "hidden " : ""; ?>

<h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
    <i class='dashicons dashicons-admin-generic'></i>
    <?php _e('Global settings', "hipayenterprise"); ?>
</h3>

<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Operating mode', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <select class="select form-control" name="operating_mode"
                    id="operating_mode" style="">
                <option
                        value="hosted_page" <?php if ($paymentCommon["operating_mode"] == OperatingMode::HOSTED_PAGE) {
                    echo " SELECTED";
                } ?>><?php _e('Hosted page', "hipayenterprise"); ?></option>
                <option
                        value="hosted_fields" <?php if ($paymentCommon["operating_mode"] == OperatingMode::HOSTED_FIELDS) {
                    echo " SELECTED";
                } ?>><?php _e('Hosted Fields', "hipayenterprise"); ?></option>
            </select>
            <div class="help-block">
                <ul>
                    <li><?php _e("Hosted Fields: The customer completes his banking information directly on the merchant's site but the form fields are hosted by HiPay. 
                            This mode is only valid for credit cards.", "hipayenterprise"); ?></li>
                    <li><?php _e("Hosted Page: The customer is redirected to a secured payment page hosted by HiPay.",
                            "hipayenterprise"
                        ); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row <?php if ($paymentCommon["operating_mode"] == OperatingMode::HOSTED_PAGE) { echo "hidden "; }?>directPost_page_config ">
        <div class="col-lg-2"></div>
        <div class="col-lg-8">
            <div class="form-group">
                <label class="control-label col-lg-2"><?php _e('Color', "hipayenterprise"); ?></label>
                <div class="col-lg-8">
                    <input class="form-control" type="text"
                           name="color"
                           id="color" style=""
                           value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["color"]); ?>"
                           placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2"><?php _e('Font Family', "hipayenterprise"); ?></label>
                <div class="col-lg-8">
                    <input class="form-control" type="text"
                           name="fontFamily"
                           id="fontFamily" style=""
                           value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["fontFamily"]); ?>"
                           placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2"><?php _e('Font Size', "hipayenterprise"); ?></label>
                <div class="col-lg-8">
                    <input class="form-control" type="text"
                           name="fontSize"
                           id="fontSize" style=""
                           value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["fontSize"]); ?>"
                           placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2"><?php _e('Font Weight', "hipayenterprise"); ?></label>
                <div class="col-lg-8">
                    <input class="form-control" type="text"
                           name="fontWeight"
                           id="fontWeight" style=""
                           value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["fontWeight"]); ?>"
                           placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2"><?php _e('PlaceHodler color', "hipayenterprise"); ?></label>
                <div class="col-lg-8">
                    <input class="form-control" type="text"
                           name="placeholderColor"
                           id="placeholderColor" style=""
                           value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["placeholderColor"]); ?>"
                           placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2"><?php _e('Caret color', "hipayenterprise"); ?></label>
                <div class="col-lg-8">
                    <input class="form-control" type="text"
                           name="caretColor"
                           id="caretColor" style=""
                           value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["caretColor"]); ?>"
                           placeholder="">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2"><?php _e('Icon color', "hipayenterprise"); ?></label>
                <div class="col-lg-8">
                    <input class="form-control" type="text"
                           name="iconColor"
                           id="iconColor" style=""
                           value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["iconColor"]); ?>"
                           placeholder="">
                </div>
            </div>
        </div>
    </div>
    <div class="row <?php if ($paymentCommon["operating_mode"] != OperatingMode::HOSTED_PAGE) { echo "hidden "; } ?>hosted_page_config">
        <div class="form-group">
            <label class="control-label col-lg-2"><?php _e('Display Hosted Page', "hipayenterprise"); ?></label>
            <div class="col-lg-8">
                <select class="select form-control" name="display_hosted_page"
                        id="display_hosted_page" style="">
                    <option
                            value="redirect" <?php if ($paymentCommon["display_hosted_page"] == "redirect") {
                        echo " SELECTED";
                    } ?>><?php _e('Redirect', "hipayenterprise"); ?></option>
                    <option
                            value="iframe" <?php if ($paymentCommon["display_hosted_page"] == "iframe") {
                        echo " SELECTED";
                    } ?>><?php _e('IFrame', "hipayenterprise"); ?></option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-2"><?php _e('Display card selector', "hipayenterprise"); ?></label>
            <div class="col-lg-8">
                <input class="form-control" type="checkbox" name="display_card_selector"
                       id="display_card_selector" style=""
                       value="1" <?php if ($paymentCommon["display_card_selector"]) {
                    echo 'checked="checked"';
                } ?>>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-2"><?php _e('CSS url', "hipayenterprise"); ?></label>
            <div class="col-lg-8">
                <input class="form-control" type="text"
                       name="css_url"
                       id="css_url" style=""
                       value="<?php echo esc_textarea($paymentCommon["css_url"]); ?>"
                       placeholder="">
                <div class="help-block"><?php _e(
                        "URL to your CSS (style sheet) to customize your hosted page or iFrame (Important: the HTTPS protocol is required).",
                        "hipayenterprise"
                    ); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Capture', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <select class="select form-control" name="capture_mode"
                    id="capture_mode" style="">
                <option
                        value="automatic" <?php if ($paymentCommon["capture_mode"] == "automatic") {
                    echo " SELECTED";
                } ?>><?php _e('Automatic', "hipayenterprise"); ?></option>
                <option
                        value="manual" <?php if ($paymentCommon["capture_mode"] == "manual") {
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
    <div class="form-group hidden">
        <label class="control-label col-lg-2"><?php _e('Use Oneclick', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="card_token"
                   id="card_token" style=""
                   value="1" <?php if ($paymentCommon["card_token"]) {
                echo 'checked="checked"';
            } ?>>
        </div>
    </div>

    <div class="form-group hidden">
        <label class="control-label col-lg-2"><?php _e('Customer\'s cart sending', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="activate_basket"
                   id="activate_basket" style=""
                   value="1" <?php if ($paymentCommon["activate_basket"]) {
                echo 'checked="checked"';
            } ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Logs information', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="log_infos"
                   id="log_infos" style=""
                   value="1" <?php if ($paymentCommon["log_infos"]) {
                echo 'checked="checked"';
            } ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Send url Notification', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox" name="send_url_notification"
                   id="log_infos" style=""
                   value="1" <?php if ($paymentCommon["send_url_notification"]) {
                echo 'checked="checked"';
            } ?>>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Activate 3-D Secure', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <select class="select form-control" name="activate_3d_secure"
                    id="activate_3d_secure" style="">
                <option
                        value="0" <?php if ($paymentCommon["activate_3d_secure"] == ThreeDS::THREE_D_S_DISABLED) {
                    echo " SELECTED";
                } ?>><?php _e('Deactivated', "hipayenterprise"); ?></option>
                <option
                        value="1" <?php if ($paymentCommon["activate_3d_secure"] == ThreeDS::THREE_D_S_TRY_ENABLE_ALL) {
                    echo " SELECTED";
                } ?>><?php _e('Try to enable for all transactions', "hipayenterprise"); ?></option>
                <option
                        value="3" <?php if ($paymentCommon["activate_3d_secure"] ==
                    ThreeDS::THREE_D_S_FORCE_ENABLE_ALL) {
                    echo " SELECTED";
                } ?>><?php _e('Force for all transactions', "hipayenterprise"); ?></option>
            </select>
        </div>
    </div>
</div>

