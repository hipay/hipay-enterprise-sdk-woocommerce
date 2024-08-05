<h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
    <i class='dashicons dashicons-admin-network'></i>
    <?php _e('Plugin mode', "hipayenterprise"); ?>
</h3>

<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_sandbox">
            <?php _e('Sandbox', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="checkbox"
                   name="woocommerce_hipayenterprise_sandbox"
                   id="woocommerce_hipayenterprise_sandbox"
                <?php if ($account["global"]["sandbox_mode"] == 1): ?>
                    checked="checked"
                <?php endif; ?>
                   value="1">
            <div class="help-block"><?php _e(
                    "When in test mode, payment cards are not really charged. Enable this option for testing purposes only.",
                    "hipayenterprise"
                ); ?>
            </div>
        </div>
    </div>
</div>


<div class="panel">
    <h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
        <i class='dashicons dashicons-admin-network'></i>
        <?php _e('Production configuration', "hipayenterprise"); ?>
    </h3>

    <div class="form-horizontal">
        <div class="form-group">
            <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_production_username">
                <?php _e('Username', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <input class="form-control" type="text"
                       name="woocommerce_hipayenterprise_account_production_username"
                       id="woocommerce_hipayenterprise_account_production_username"
                       value="<?php echo esc_textarea($account["production"]["api_username_production"]); ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_production_password">
                <?php _e('Password', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <input class="form-control" type="password"
                       name="woocommerce_hipayenterprise_account_production_password"
                       id="woocommerce_hipayenterprise_account_production_password"
                       value="<?php echo esc_textarea($account["production"]["api_password_production"]); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_production_secret_passphrase">
                <?php _e('Secret passphrase', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <div class="uk-inline">
                    <input class="form-control" type="password"
                           name="woocommerce_hipayenterprise_account_production_secret_passphrase"
                           id="woocommerce_hipayenterprise_account_production_secret_passphrase"
                           value="<?php echo esc_textarea($account["production"]["api_secret_passphrase_production"]); ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_production_tokenjs_username">
                <?php _e('Tokenization (Public)', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <input class="form-control" type="text"
                       name="woocommerce_hipayenterprise_account_production_tokenjs_username"
                       id="woocommerce_hipayenterprise_account_production_tokenjs_username"
                       value="<?php echo esc_textarea($account["production"]["api_tokenjs_username_production"]); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_production_password_publickey">
                <?php _e('Password', "hipayenterprise"); ?>
            </label>
            <div class="col-lg-8">
                <input class="form-control" type="text"
                       name="woocommerce_hipayenterprise_account_production_password_publickey"
                       id="woocommerce_hipayenterprise_account_production_password_publickey"
                       value="<?php echo esc_textarea(
                           $account["production"]["api_tokenjs_password_publickey_production"]
                       ); ?>">
            </div>
        </div>
    </div>
</div>

<h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
    <i class='dashicons dashicons-admin-network'></i>
    <?php _e('Sandbox configuration', "hipayenterprise"); ?>
</h3>

<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_sandbox_username">
            <?php _e('Username', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_account_sandbox_username"
                   id="woocommerce_hipayenterprise_account_sandbox_username"
                   value="<?php echo esc_textarea($account["sandbox"]["api_username_sandbox"]); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_sandbox_password">
            <?php _e('Password', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="password"
                   name="woocommerce_hipayenterprise_account_sandbox_password"
                   id="woocommerce_hipayenterprise_account_sandbox_password"
                   value="<?php echo esc_textarea($account["sandbox"]["api_password_sandbox"]); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_sandbox_secret_passphrase">
            <?php _e('Secret passphrase', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="password"
                   name="woocommerce_hipayenterprise_account_sandbox_secret_passphrase"
                   id="woocommerce_hipayenterprise_account_sandbox_secret_passphrase"
                   value="<?php echo esc_textarea($account["sandbox"]["api_secret_passphrase_sandbox"]); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_sandbox_tokenjs_username">
            <?php _e('Tokenization (Public)', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_account_sandbox_tokenjs_username"
                   id="woocommerce_hipayenterprise_account_sandbox_tokenjs_username"
                   value="<?php echo esc_textarea($account["sandbox"]["api_tokenjs_username_sandbox"]); ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_account_sandbox_password_publickey">
            <?php _e('Password', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_account_sandbox_password_publickey"
                   id="woocommerce_hipayenterprise_account_sandbox_password_publickey"
                   value="<?php echo esc_textarea(
                       $account["sandbox"]["api_tokenjs_password_publickey_sandbox"]
                   ); ?>">
        </div>
    </div>
</div>

<table></table>
