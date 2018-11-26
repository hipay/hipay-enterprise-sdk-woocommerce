<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Copy To', "hipayenterprise"); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Copy To", "hipayenterprise"); ?></span>
            </legend>
            <input class="input-text regular-input " type="text"
                   name="woocommerce_hipayenterprise_fraud_copy_to"
                   id="woocommerce_hipayenterprise_fraud_copy_to" style=""
                   value="<?php echo esc_textarea($fraud["copy_to"]); ?>" placeholder="">
            <p class="description"><?php _e(
                    "Enter a valid email, during a transaction challenged an email will be sent to this address.",
                    "hipayenterprise"
                ); ?></p>
        </fieldset>
    </td>
</tr>

<tr valign="top">
    <th scope="row" class="titledesc"><?php _e('Copy Method', "hipayenterprise"); ?></th>
    <td class="forminp">
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e("Copy Method", "hipayenterprise"); ?></span>
            </legend>
            <select class="select " name="woocommerce_hipayenterprise_fraud_copy_method"
                    id="woocommerce_hipayenterprise_fraud_copy_method" style="">
                <option
                        value="bcc"<?php if (esc_textarea($fraud["copy_method"]) == "bcc") {
                    echo " SELECTED";
                } ?>><?php _e('Bcc', "hipayenterprise"); ?></option>
                <option
                        value="separate_email"<?php if (esc_textarea($fraud["copy_method"]) == "separate_email") {
                    echo " SELECTED";
                } ?>><?php _e('Separate email', "hipayenterprise"); ?></option>
            </select>
            <p class="description"><?php _e(
                    "Select Bcc if the recipient will be in copy of the email or Separate email for sending two emails.",
                    "hipayenterprise"
                ); ?></p>
        </fieldset>
    </td>
</tr>

