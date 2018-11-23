<?php $classHostedSettings = $paymentCommon["operating_mode"] ==
"hosted_page" ? $classHostedSettings = "hidden " : ""; ?>
<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Operating mode', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e("Operating Mode", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span></legend>
            <select class="select " name="operating_mode"
                    id="operating_mode" style="">
                <option
                        value="hosted_page" <?php if ($paymentCommon["operating_mode"] == OperatingMode::HOSTED_PAGE) {
                    echo " SELECTED";
                } ?>><?php _e('Hosted page', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
                <option
                        value="direct_post" <?php if ($paymentCommon["operating_mode"] == OperatingMode::DIRECT_POST) {
                    echo " SELECTED";
                } ?>><?php _e('Direct Post', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
            </select>
            <p class="description"><?php _e(
                    "Api if the customer will fill his bank information directly on merchants OR Hosted if the customer is redirected to a secured payment page hosted by HiPay.",
                    Hipay_Gateway_Abstract::TEXT_DOMAIN
                ); ?></p>
        </fieldset>
    </td>
</tr>


<tr valign="top"
    class="<?php if ($paymentCommon["operating_mode"] != OperatingMode::HOSTED_PAGE) {
        echo "hidden ";
    } ?>hosted_page_config">
    <th scope="row" class="titledesc"><?php _e('Display Hosted Page', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e("Display Hosted Page", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span></legend>
            <select class="select " name="display_hosted_page"
                    id="display_hosted_page" style="">
                <option
                        value="redirect" <?php if ($paymentCommon["display_hosted_page"] == "redirect") {
                    echo " SELECTED";
                } ?>><?php _e('Redirect', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
                <option
                        value="iframe" <?php if ($paymentCommon["display_hosted_page"] == "iframe") {
                    echo " SELECTED";
                } ?>><?php _e('IFrame', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
            </select>
        </fieldset>
    </td>
</tr>


<tr valign="top"
    class="<?php if ($paymentCommon["operating_mode"] != "hosted_page") {
        echo "hidden ";
    } ?>hosted_page_config">
    <th scope="row" class="titledesc"><?php _e('Display card selector', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Display card selector', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span></legend>
            <input class="" type="checkbox" name="display_card_selector"
                   id="display_card_selector" style=""
                   value="1" <?php if ($paymentCommon["display_card_selector"]) {
                echo 'checked="checked"';
            } ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php if ($paymentCommon["operating_mode"] != "hosted_page") {
        echo "hidden ";
    } ?>hosted_page_config">
    <th scope="row" class="titledesc"><?php _e('CSS url', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("CSS Url", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="css_url"
                   id="css_url" style=""
                   value="<?php echo esc_textarea($paymentCommon["css_url"]); ?>"
                   placeholder="">
            <p class="description"><?php _e(
                    "URL to your CSS (style sheet) to customize your hosted page or iFrame (Important: the HTTPS protocol is required).",
                    Hipay_Gateway_Abstract::TEXT_DOMAIN
                ); ?></p>
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('Color', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Color", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
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
    <th scope="row" class="titledesc"><?php _e('Font Family', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e(
                        "Font Family",
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?></span>
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
    <th scope="row" class="titledesc"><?php _e('Font Size', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e(
                        "Font Size",
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?></span>
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
    <th scope="row" class="titledesc"><?php _e('Font Weight', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e(
                        "Font Weight",
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?></span>
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
    <th scope="row" class="titledesc"><?php _e('PlaceHodler color', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e(
                        "PlaceHodler color",
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="placeholderColor"
                   id="placeholderColor" style=""
                   value="<?php echo esc_textarea(
                       $paymentCommon["hosted_fields_style"]["base"]["placeholderColor"]
                   ); ?>"
                   placeholder="">
        </fieldset>
    </td>
</tr>

<tr valign="top"
    class="<?php echo $classHostedSettings; ?>directPost_page_config">
    <th scope="row" class="titledesc"><?php _e('Caret color', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e(
                        "Caret Color",
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?></span>
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
    <th scope="row" class="titledesc"><?php _e('Icon color', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e(
                        "Icon Color",
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?></span>
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
    <th scope="row" class="titledesc"><?php _e('Capture', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Capture", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
            </legend>
            <select class="select " name="capture_mode"
                    id="capture_mode" style="">
                <option
                        value="automatic" <?php if ($paymentCommon["capture_mode"] == "automatic") {
                    echo " SELECTED";
                } ?>><?php _e('Automatic', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
                <option
                        value="manual" <?php if ($paymentCommon["capture_mode"] == "manual") {
                    echo " SELECTED";
                } ?>><?php _e('Manual', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
            </select>
            <p class="description"><?php _e(
                    "Manual if all transactions will be captured manually either from the Hipay Back office or from your admin in Woocommerce OR Automatic if all transactions will be captured automatically.",
                    Hipay_Gateway_Abstract::TEXT_DOMAIN
                ); ?></p>
        </fieldset>
    </td>
</tr>

<tr valign="top" class="hidden">
    <th scope="row" class="titledesc"><?php _e('Use Oneclick', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e(
                        'Use Oneclick',
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?></span>
            </legend>
            <input class="" type="checkbox" name="card_token"
                   id="card_token" style=""
                   value="1" <?php if ($paymentCommon["card_token"]) {
                echo 'checked="checked"';
            } ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top" class="hidden">
    <th scope="row" class="titledesc"><?php _e('Customer\'s cart sending', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Customer\'s cart sending', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span></legend>
            <input class="" type="checkbox" name="activate_basket"
                   id="activate_basket" style=""
                   value="1" <?php if ($paymentCommon["activate_basket"]) {
                echo 'checked="checked"';
            } ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Logs information', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Logs information', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span></legend>
            <input class="" type="checkbox" name="log_infos"
                   id="log_infos" style=""
                   value="1" <?php if ($paymentCommon["log_infos"]) {
                echo 'checked="checked"';
            } ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Send url Notification', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Send url Notification', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span></legend>
            <input class="" type="checkbox" name="send_url_notification"
                   id="log_infos" style=""
                   value="1" <?php if ($paymentCommon["send_url_notification"]) {
                echo 'checked="checked"';
            } ?>>
            <br>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Activate 3-D Secure', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e("Activate 3-D Secure", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span></legend>
            <select class="select " name="activate_3d_secure"
                    id="activate_3d_secure" style="">
                <option
                        value="0" <?php if ($paymentCommon["activate_3d_secure"] == ThreeDS::THREE_D_S_DISABLED) {
                    echo " SELECTED";
                } ?>><?php _e('Deactivated', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
                <option
                        value="1" <?php if ($paymentCommon["activate_3d_secure"] == ThreeDS::THREE_D_S_TRY_ENABLE_ALL) {
                    echo " SELECTED";
                } ?>><?php _e('Try to enable for all transactions', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
                <option
                        value="2" <?php if ($paymentCommon["activate_3d_secure"] ==
                    ThreeDS::THREE_D_S_FORCE_ENABLE_ALL) {
                    echo " SELECTED";
                } ?>><?php _e('Force for all transactions', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></option>
            </select>
            <p class="description"></p>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th colspan="2" align="right"><?php submit_button(); ?></th>
</tr>
