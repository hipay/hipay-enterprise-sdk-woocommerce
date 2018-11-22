<?php $classHostedSettings = $paymentCommon["operating_mode"] == "hosted_page" ? $classHostedSettings = "hidden ": ""; ?>
<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Operating mode', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e("Operating Mode", 'hipayenterprise'); ?></span></legend>
            <select class="select " name="operating_mode"
                    id="operating_mode" style="">
                <option
                        value="hosted_page" <?php if ($paymentCommon["operating_mode"] == "hosted_page") echo " SELECTED"; ?>><?php _e('Hosted page', 'hipayenterprise'); ?></option>
                <option
                        value="direct_post" <?php if ($paymentCommon["operating_mode"] == "direct_post") echo " SELECTED"; ?>><?php _e('Direct Post', 'hipayenterprise'); ?></option>
            </select>
            <p class="description"><?php _e("Api if the customer will fill his bank information directly on merchants OR Hosted if the customer is redirected to a secured payment page hosted by HiPay.", 'hipayenterprise'); ?></p>
        </fieldset>
    </td>
</tr>


<tr valign="top"
    class="<?php if ($paymentCommon["operating_mode"] != "hosted_page") echo "hidden "; ?>hosted_page_config">
    <th scope="row" class="titledesc"><?php _e('Display Hosted Page', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e("Display Hosted Page", 'hipayenterprise'); ?></span></legend>
            <select class="select " name="display_hosted_page"
                    id="display_hosted_page" style="">
                <option
                        value="redirect" <?php if ($paymentCommon["display_hosted_page"] == "redirect") echo " SELECTED"; ?>><?php _e('Redirect', 'hipayenterprise'); ?></option>
                <option
                        value="iframe" <?php if ($paymentCommon["display_hosted_page"] == "iframe") echo " SELECTED"; ?>><?php _e('IFrame', 'hipayenterprise'); ?></option>
            </select>
        </fieldset>
    </td>
</tr>


<tr valign="top"
    class="<?php if ($paymentCommon["operating_mode"] != "hosted_page") echo "hidden "; ?>hosted_page_config">
    <th scope="row" class="titledesc"><?php _e('Display card selector', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Display card selector', 'hipayenterprise'); ?></span></legend>
            <input class="" type="checkbox" name="display_card_selector"
                   id="display_card_selector" style=""
                   value="1" <?php if ($paymentCommon["display_card_selector"]) echo 'checked="checked"'; ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php if ($paymentCommon["operating_mode"] != "hosted_page") echo "hidden "; ?>hosted_page_config">
    <th scope="row" class="titledesc"><?php _e('CSS url', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("CSS Url", 'hipayenterprise'); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="css_url"
                   id="css_url" style=""
                   value="<?php echo esc_textarea($paymentCommon["css_url"]); ?>"
                   placeholder="">
            <p class="description"><?php _e("URL to your CSS (style sheet) to customize your hosted page or iFrame (Important: the HTTPS protocol is required).", 'hipayenterprise'); ?></p>
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('Color', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Color", 'hipayenterprise'); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="color"
                   id="color" style=""
                   value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["color"]); ?>"
                   placeholder="">
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('Font Family', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Font Family", 'hipayenterprise'); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="fontFamily"
                   id="fontFamily" style=""
                   value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["fontFamily"]); ?>"
                   placeholder="">
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('Font Size', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Font Size", 'hipayenterprise'); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="fontSize"
                   id="fontSize" style=""
                   value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["fontSize"]); ?>"
                   placeholder="">
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('Font Weight', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Font Weight", 'hipayenterprise'); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="fontWeight"
                   id="fontWeight" style=""
                   value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["fontWeight"]); ?>"
                   placeholder="">
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('PlaceHodler color', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("PlaceHodler color", 'hipayenterprise'); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="placeholderColor"
                   id="placeholderColor" style=""
                   value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["placeholderColor"]); ?>"
                   placeholder="">
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('Caret color', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Caret Color", 'hipayenterprise'); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="caretColor"
                   id="caretColor" style=""
                   value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["caretColor"]); ?>"
                   placeholder="">
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('Icon color', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Icon Color", 'hipayenterprise'); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="iconColor"
                   id="iconColor" style=""
                   value="<?php echo esc_textarea($paymentCommon["hosted_fields_style"]["base"]["iconColor"]); ?>"
                   placeholder="">
        </fieldset>
    </td>
</tr>


<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Capture', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Capture", 'hipayenterprise'); ?></span>
            </legend>
            <select class="select " name="capture_mode"
                    id="capture_mode" style="">
                <option
                        value="automatic" <?php if ($paymentCommon["capture_mode"] == "automatic") echo " SELECTED"; ?>><?php _e('Automatic', 'hipayenterprise'); ?></option>
                <option
                        value="manual" <?php if ($paymentCommon["capture_mode"] == "manual") echo " SELECTED"; ?>><?php _e('Manual', 'hipayenterprise'); ?></option>
            </select>
            <p class="description"><?php _e("Manual if all transactions will be captured manually either from the Hipay Back office or from your admin in Woocommerce OR Automatic if all transactions will be captured automatically.", 'hipayenterprise'); ?></p>
        </fieldset>
    </td>
</tr>

<tr valign="top" class="hidden">
    <th scope="row" class="titledesc"><?php _e('Use Oneclick', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Use Oneclick', 'hipayenterprise'); ?></span>
            </legend>
            <input class="" type="checkbox" name="card_token"
                   id="card_token" style=""
                   value="1" <?php if ($paymentCommon["card_token"]) echo 'checked="checked"'; ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top" class="hidden">
    <th scope="row" class="titledesc"><?php _e('Customer\'s cart sending', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Customer\'s cart sending', 'hipayenterprise'); ?></span></legend>
            <input class="" type="checkbox" name="activate_basket"
                   id="activate_basket" style=""
                   value="1" <?php if ($paymentCommon["activate_basket"]) echo 'checked="checked"'; ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Logs information', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Logs information', 'hipayenterprise'); ?></span></legend>
            <input class="" type="checkbox" name="log_infos"
                   id="log_infos" style=""
                   value="1" <?php if ($paymentCommon["log_infos"]) echo 'checked="checked"'; ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Send url Notification', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Send url Notification', 'hipayenterprise'); ?></span></legend>
            <input class="" type="checkbox" name="send_url_notification"
                   id="log_infos" style=""
                   value="1" <?php if ($paymentCommon["send_url_notification"]) echo 'checked="checked"'; ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Activate 3-D Secure', 'hipayenterprise'); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e("Activate 3-D Secure", 'hipayenterprise'); ?></span></legend>
            <select class="select " name="activate_3d_secure"
                    id="activate_3d_secure" style="">
                <option
                        value="0" <?php if ($paymentCommon["activate_3d_secure"] == "0") echo " SELECTED"; ?>><?php _e('Deactivated', 'hipayenterprise'); ?></option>
                <option
                        value="1" <?php if ($paymentCommon["activate_3d_secure"] == "1") echo " SELECTED"; ?>><?php _e('Try to enable for all transactions', 'hipayenterprise'); ?></option>
                <option
                        value="2" <?php if ($paymentCommon["activate_3d_secure"] == "2") echo " SELECTED"; ?>><?php _e('Force for all transactions', 'hipayenterprise'); ?></option>
            </select>
            <p class="description"></p>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th colspan="2" align="right"><?php submit_button(); ?></th>
</tr>
