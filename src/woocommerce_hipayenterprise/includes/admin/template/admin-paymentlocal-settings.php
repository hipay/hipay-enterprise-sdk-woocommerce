<table>
    <tr valign="top">
        <td valign="top"
            align="right"><?php _e('Minimum order amount', "hipayenterprise"); ?></td>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e("Minimum order amount", "hipayenterprise"); ?></span>
                </legend>
                <input class="input-text regular-input " type="text"
                       name="woocommerce_hipayenterprise_methods_minAmount[<?php echo $method; ?>][EUR]"
                       id="woocommerce_hipayenterprise_methods_minAmount" style=""
                       value="<?php echo $configurationPaymentMethod["minAmount"]["EUR"]; ?>"
                       placeholder="">
            </fieldset>
        </td>
    </tr>

    <tr valign="top">
        <td align="right"><?php _e('Maximum order amount', "hipayenterprise"); ?></td>
        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e("Maximum order amount", "hipayenterprise"); ?></span>
                </legend>
                <input class="input-text regular-input " type="text"
                       name="woocommerce_hipayenterprise_methods_maxAmount[<?php echo $method; ?>][EUR]"
                       id="woocommerce_hipayenterprise_methods_maxAmount" style=""
                       value="<?php echo $configurationPaymentMethod["maxAmount"]["EUR"]; ?>"
                       placeholder="">
            </fieldset>
        </td>
    </tr>


    <tr valign="top">
        <td valign="top"
            align="right"><?php _e('Currencies', "hipayenterprise"); ?></td>

        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e("Currencies", "hipayenterprise"); ?></span></legend>
                <?php if ($configurationPaymentMethod["currencySelectorReadOnly"]): ?>
                    <?php foreach ($configurationPaymentMethod["currencies"] as $currency): ?>
                        <span class="label-value col-lg-2"><?php echo $currency ?></span>
                        <input
                                type="hidden" value="<?php echo $currency ?>"
                                name="woocommerce_hipayenterprise_methods_currencies[<?php echo $method; ?>][]"
                        />
                    <?php endforeach; ?>
                <?php endif; ?>
            </fieldset>
        </td>

    </tr>

    <tr valign="top">
        <td valign="top" align="right"
            style='vertical-align:top;'><?php _e('Countries', "hipayenterprise"); ?></td>

        <td class="forminp">
            <fieldset>
                <legend class="screen-reader-text">
                    <span><?php _e("Countries", "hipayenterprise"); ?></span></legend>
                <?php if ($configurationPaymentMethod["countrySelectorReadOnly"]): ?>
                    <?php foreach ($configurationPaymentMethod["countries"] as $country): ?>
                        <span class="label-value col-lg-2"><?php echo $country ?></span>
                        <input
                                type="hidden" value="<?php echo $country ?>"
                                name="woocommerce_hipayenterprise_methods_countries[<?php echo $method; ?>][]"
                        />
                    <?php endforeach; ?>
                <?php endif; ?>
            </fieldset>
        </td>

    </tr>

</table>
