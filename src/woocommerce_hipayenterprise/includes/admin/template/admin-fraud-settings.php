<h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
    <i class='dashicons dashicons-admin-users'></i>
    <?php _e('Fraud', "hipayenterprise"); ?>
</h3>

<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_fraud_copy_to">
            <?php _e('E-mail', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_fraud_copy_to"
                   id="woocommerce_hipayenterprise_fraud_copy_to" style=""
                   value="<?php echo esc_textarea($fraud["copy_to"]); ?>" placeholder="">
            <div class="help-block">
                <?php _e(
                    "Enter a valid email, during a transaction challenged an email will be sent to this address.",
                    "hipayenterprise"
                ); ?>
            </div>
        </div>
    </div>
</div>


