<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Display name', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_methods_displayName_<?php echo $method; ?>[<?php echo substr(get_locale(), 0, 2); ?>]"
                   id="woocommerce_hipayenterprise_methods_displayName<?php echo $method; ?>"
                   style=""
                   value="<?php echo $configurationPaymentMethod["displayName"][substr(get_locale(), 0, 2)] ?>"
                   placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Minimum order amount', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_methods_minAmount_<?php echo $method; ?>[EUR]"
                   id="woocommerce_hipayenterprise_methods_<?php echo $method; ?>_minAmount"
                   style=""
                   value="<?php echo $configurationPaymentMethod["minAmount"]["EUR"]; ?>"
                   placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Maximum order amount', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_methods_maxAmount_<?php echo $method; ?>[EUR]"
                   id="woocommerce_hipayenterprise_methods_<?php echo $method; ?>_maxAmount"
                   style=""
                   value="<?php echo $configurationPaymentMethod["maxAmount"]["EUR"]; ?>"
                   placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Currencies', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <?php if ($configurationPaymentMethod["currencySelectorReadOnly"]): ?>
                <?php foreach ($configurationPaymentMethod["currencies"] as $currency): ?>
                    <span class="label-value col-lg-2"><?php echo $currency ?></span>
                    <input
                            type="hidden" value="<?php echo $currency ?>"
                            name="woocommerce_hipayenterprise_methods_currencies_<?php echo $method; ?>[]"
                    />
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                $activatedCurrencies = get_woocommerce_currency();
                echo '<input class="form-control" type="checkbox" name="woocommerce_hipayenterprise_methods_currencies_' .
                    $method . '[]" id="woocommerce_hipayenterprise_methods_currencies_' . '_currencies" style="" value="' .
                    $activatedCurrencies .
                    '"';
                if (is_array($configurationPaymentMethod["currencies"]) &&
                    array_search($activatedCurrencies, $configurationPaymentMethod["currencies"]) !== false) {
                    echo ' checked="checked"';
                }
                echo "><span style='padding-right:18px;'>" . $activatedCurrencies . "</span>";
                ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Countries', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <?php if ($configurationPaymentMethod["countrySelectorReadOnly"]): ?>
                <?php foreach ($configurationPaymentMethod["countries"] as $country): ?>
                    <span class="label-value col-lg-2"><?php echo $country ?></span>
                    <input
                            type="hidden" value="<?php echo $country ?>"
                            name="woocommerce_hipayenterprise_methods_countries_<?php echo $method; ?>[]"
                    />
                <?php endforeach; ?>
            <?php else: ?>
                <select multiple
                        class="form-control woocommerce_hipayenterprise_methods_countries"
                        name="woocommerce_hipayenterprise_methods_countries_<?php echo $method; ?>[]"
                        id="woocommerce_hipayenterprise_methods<?php echo $method; ?>countries">
                    <?php
                    $countries_wc = new WC_Countries();
                    $countries = $countries_wc->__get('countries');

                    foreach ($countries as $countryKey => $countryValue) {
                        $class = "";
                        if (is_array($configurationPaymentMethod["countries"]) &&
                            array_search($countryKey, $configurationPaymentMethod["countries"]) !== false ||
                            $configurationPaymentMethod["countries"] == $countryKey) {
                            $class = "selected";
                        }
                        echo "<option value='" .
                            $countryKey .
                            "' " .
                            $class .
                            ">" .
                            $countryValue .
                            "</option>";
                    }
                    ?>
                </select>
            <?php endif; ?>
        </div>
    </div>
</div>


