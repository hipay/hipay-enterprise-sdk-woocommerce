<tr valign="top">
    <th scope="row" class="">
        <?php
        $first = true;
        if (is_array($configurationPaymentMethod)) {
            foreach ($configurationPaymentMethod as $card => $value) {
                $selected = $first ? $methods . "_admin_menu_sel" : "";
                echo "<div data-id='" .
                    $card .
                    "' class='" .
                    $methods .
                    "_admin_menu " .
                    $selected .
                    "'>" .
                    $card .
                    "<br></div>";
                $first = false;
            }
        }
        ?>
    </th>
    <td class="forminp" valign="top">
        <fieldset>
            <?php
            $first = true;
            if (is_array($configurationPaymentMethod)) {
                foreach ($configurationPaymentMethod as $card => $value) {
                    $selected = !$first ? "hidden" : "";
                    echo "<div data-id='" .
                        $card .
                        "' class='" .
                        $methods .
                        "_admin_config_" .
                        $card .
                        " " .
                        $methods .
                        "_admin_config " .
                        $selected .
                        "'>";
                    echo "<b>" . $card . "</b><hr>";
                    ?>
                    <table>
                        <tr valign="top">
                            <td align="right"><?php _e('Activated', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
                            <td class="forminp">
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php _e('Use Oneclick', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                                    </legend>
                                    <input class="" type="checkbox"
                                           name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_activated[<?php echo $card; ?>]"
                                           id="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_activated"
                                           style=""
                                           value="1" <?php if ($value["activated"]) {
                                        echo 'checked="checked"';
                                    } ?>>
                                    <br>
                                </fieldset>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td valign="top"
                                align="right"><?php _e('Minimum order amount', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
                            <td class="forminp">
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php _e("Minimum order amount", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                                    </legend>
                                    <input class="input-text regular-input " type="text"
                                           name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_minAmount[<?php echo $card; ?>][EUR]"
                                           id="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_minAmount"
                                           style=""
                                           value="<?php echo $value["minAmount"]["EUR"]; ?>"
                                           placeholder="">
                                </fieldset>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td align="right"><?php _e('Maximum order amount', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
                            <td class="forminp">
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php _e("Maximum order amount", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span>
                                    </legend>
                                    <input class="input-text regular-input " type="text"
                                           name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_maxAmount[<?php echo $card; ?>][EUR]"
                                           id="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_maxAmount"
                                           style=""
                                           value="<?php echo $value["maxAmount"]["EUR"]; ?>"
                                           placeholder="">
                                </fieldset>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td valign="top"
                                align="right"><?php _e('Currencies', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>
                            <td class="forminp">
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?php _e("Currencies", Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></span></legend>
                                    <?php
                                    $activatedCurrencies = get_woocommerce_currency();
                                    echo '<input class="" type="checkbox" name="woocommerce_hipayenterprise_methods_' .
                                        $methods .
                                        '_currencies[' .
                                        $card .
                                        '][]" id="woocommerce_hipayenterprise_methods_' .
                                        $methods .
                                        '_currencies" style="" value="' .
                                        $activatedCurrencies .
                                        '"';
                                    if (is_array($value["currencies"]) &&
                                        array_search($activatedCurrencies, $value["currencies"]) !== false) {
                                        echo ' checked="checked"';
                                    }
                                    echo "><span style='padding-right:18px;'>" . $activatedCurrencies . "</span>";
                                    ?>
                                </fieldset>
                            </td>

                        </tr>

                        <tr valign="top">
                            <td valign="top" align="right"
                                style='vertical-align:top;'>
                                <?php _e('Countries', Hipay_Gateway_Abstract::TEXT_DOMAIN); ?></td>

                            <td class="forminp">
                                <fieldset>
                                    <div style="float:left;">
                                        <select multiple
                                                class="input-text woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_countries regular-input woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_countries_"
                                                name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_countries[<?php echo $card; ?>][]"
                                                id="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_countries[<?php echo $card; ?>]">
                                            <?php

                                            $countries_wc = new WC_Countries();
                                            $countries = $countries_wc->__get('countries');

                                            foreach ($countries as $countryKey => $countryValue) {
                                                $class = "";
                                                if (is_array($value["countries"]) &&
                                                    array_search($countryKey, $value["countries"]) !== false ||
                                                    $value["countries"] == $countryKey) {
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
                                    </div>

                                </fieldset>
                            </td>

                        </tr>


                    </table>
                    <?php
                    echo "</div>";
                    $first = false;
                }
            }
            ?>
        </fieldset>
    </td>
</tr>


