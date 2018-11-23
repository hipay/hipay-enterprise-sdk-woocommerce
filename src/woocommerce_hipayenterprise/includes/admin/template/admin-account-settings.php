<table class="form-table">
    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Sandbox', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Sandbox", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-checkbox" type="checkbox"
                       name="woocommerce_hipayenterprise_sandbox"
                       id="woocommerce_hipayenterprise_sandbox"
                    <?php if ($account["global"]["sandbox_mode"] == 1): ?>
                        checked="checked"
                    <?php endif; ?>
                       value="1">
                <p class="description"><?php _e(
                        "When in test mode, payment cards are not really charged. Enable this option for testing purposes only.",
                        Hipay_Gateway_Abstract::TEXT_DOMAIN
                    ); ?></p>
            </fieldset>
        </td>
    </tr>
</table>

<h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
    <hr>
    <i class='dashicons dashicons-admin-network'></i>
    <?php _e('Production configuration', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?>
</h3>

<table class="form-table">

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Username', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Username", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="text"
                       name="woocommerce_hipayenterprise_account_production_username"
                       id="woocommerce_hipayenterprise_account_production_username"
                       value="<?php echo esc_textarea($account["production"]["api_username_production"]); ?>">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Password', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Password", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="password"
                       name="woocommerce_hipayenterprise_account_production_password"
                       id="woocommerce_hipayenterprise_account_production_password"
                       value="<?php echo esc_textarea($account["production"]["api_password_production"]); ?>">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Secret passphrase', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Secret passphrase", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="password"
                       name="woocommerce_hipayenterprise_account_production_secret_passphrase"
                       id="woocommerce_hipayenterprise_account_production_secret_passphrase"
                       value="<?php echo esc_textarea($account["production"]["api_secret_passphrase_production"]); ?>">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Tokenization (Public)', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Tokenization (Public)", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="text"
                       name="woocommerce_hipayenterprise_account_production_tokenjs_username"
                       id="woocommerce_hipayenterprise_account_production_tokenjs_username"
                       value="<?php echo esc_textarea($account["production"]["api_tokenjs_username_production"]); ?>">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Password', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Password", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="text"
                       name="woocommerce_hipayenterprise_account_production_password_publickey"
                       id="woocommerce_hipayenterprise_account_production_password_publickey"
                       value="<?php echo esc_textarea(
                           $account["production"]["api_tokenjs_password_publickey_production"]
                       ); ?>">
            </fieldset>
        </td>
    </tr>
</table>

<h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
    <hr>
    <i class='dashicons dashicons-admin-network'></i>
    <?php _e('Sandbox configuration', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?>
</h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Username', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Username", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="text"
                       name="woocommerce_hipayenterprise_account_sandbox_username"
                       id="woocommerce_hipayenterprise_account_sandbox_username"
                       value="<?php echo esc_textarea($account["sandbox"]["api_username_sandbox"]); ?>">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Password', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Password", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="password"
                       name="woocommerce_hipayenterprise_account_sandbox_password"
                       id="woocommerce_hipayenterprise_account_sandbox_password"
                       value="<?php echo esc_textarea($account["sandbox"]["api_password_sandbox"]); ?>">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Secret passphrase', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Secret passphrase", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="password"
                       name="woocommerce_hipayenterprise_account_sandbox_secret_passphrase"
                       id="woocommerce_hipayenterprise_account_sandbox_secret_passphrase"
                       value="<?php echo esc_textarea($account["sandbox"]["api_secret_passphrase_sandbox"]); ?>">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Tokenization (Public)', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Tokenization (Public)", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="text"
                       name="woocommerce_hipayenterprise_account_sandbox_tokenjs_username"
                       id="woocommerce_hipayenterprise_account_sandbox_tokenjs_username"
                       value="<?php echo esc_textarea($account["sandbox"]["api_tokenjs_username_sandbox"]); ?>">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <th scope="row" class="titledesc"><?php _e('Password', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></th>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text"><span><?php _e("Password", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                </legend>
                <input class="input-text regular-input " type="text"
                       name="woocommerce_hipayenterprise_account_sandbox_password_publickey"
                       id="woocommerce_hipayenterprise_account_sandbox_password_publickey"
                       value="<?php echo esc_textarea(
                           $account["sandbox"]["api_tokenjs_password_publickey_sandbox"]
                       ); ?>">
            </fieldset>
        </td>
    </tr>
</table>
