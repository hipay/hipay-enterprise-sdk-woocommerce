<div class="panel panel-default panel-primary-hipay">
    <div class="panel-heading">
        <h3 class="wc-settings-sub-title " id="woocommerce_hipayenterprise_api_tab_methods_2">
            <?php _e('Credit card', "hipayenterprise"); ?>
        </h3>
    </div>
    <div class="panel-body">
        <div class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_ccDisplayName">
                    <?php _e(
                        'Display name',
                        "hipayenterprise"
                    ); ?>
                </label>
                <div class="col-lg-8">
                    <?php
                        if (isset($paymentCommon["ccDisplayName"][substr(get_locale(), 0, 2)])) {
                            $displayName = $paymentCommon["ccDisplayName"][substr(get_locale(), 0, 2)];
                        } else {
                            $displayName = $paymentCommon["ccDisplayName"]['en'];
                        }
                    ?>
                    <input class="form-control" type="text"
                           name="ccDisplayName[<?php echo substr(get_locale(), 0, 2); ?>]"
                           id="woocommerce_hipayenterprise_ccDisplayName"
                           style=""
                           value="<?php echo $displayName; ?>"
                           placeholder="">
                </div>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div role="tabpanel">
            <ul class="nav nav-pills nav-stacked col-md-2" role="tablist">
                <li role="presentation" class="disabled summary credit-card-title"></li>
                <?php
                $first = true;
                if (is_array($configurationPaymentMethod)) {
                    foreach ($configurationPaymentMethod as $card => $value) {
                        $selected = $first ? "active" : "";
                        echo '<li role="presentation" class="' .
                            $selected .
                            '"><a href="#' .
                            $card .
                            '" aria-controls="' .
                            $card .
                            '" role="tab" data-toggle="tab">' .
                            $value["displayName"] .
                            '</a></li>';
                        $first = false;
                    }
                }
                ?>
            </ul>

            <div class="tab-content col-md-10">
                <?php
                $first = true;
                if (is_array($configurationPaymentMethod)) {
                    foreach ($configurationPaymentMethod as $card => $value) {
                        $selected = $first ? "active" : "";
                        echo '<div role="tabpanel" id="' . $card . '" class="tab-pane ' . $selected . '">';
                        ?>

                        <div class="row">
                            <h4 class="col-lg-4 col-lg-offset-2">
                                <?php echo $value["displayName"] ?>
                            </h4>
                        </div>
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="control-label col-lg-2"
                                       for="woocommerce_hipayenterprise_methods_<?php echo $card; ?>_activated"
                                ><?php _e(
                                        'Activated',
                                        "hipayenterprise"
                                    ); ?></label>
                                <div class="col-lg-8">
                                    <input class="form-control" type="checkbox"
                                           name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_activated_<?php echo $card; ?>"
                                           id="woocommerce_hipayenterprise_methods_<?php echo $card; ?>_activated"
                                           style=""
                                           value="1" <?php if ($value["activated"]) {
                                        echo 'checked="checked"';
                                    } ?> >
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2"
                                        for="woocommerce_hipayenterprise_methods_<?php echo $card; ?>_minAmount"
                                ><?php _e(
                                        'Minimum order amount',
                                        "hipayenterprise"
                                    ); ?></label>
                                <div class="col-lg-8">
                                    <input class="form-control" type="number"
                                           name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_minAmount_<?php echo $card; ?>[EUR]"
                                           id="woocommerce_hipayenterprise_methods_<?php echo $card; ?>_minAmount"
                                           style=""
                                           value="<?php echo $value["minAmount"]["EUR"]; ?>"
                                           placeholder="">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2"
                                       for="woocommerce_hipayenterprise_methods_<?php echo $card; ?>_maxAmount"
                                ><?php _e(
                                        'Maximum order amount',
                                        "hipayenterprise"
                                    ); ?></label>
                                <div class="col-lg-8">
                                    <input class="form-control" type="number"
                                           name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_maxAmount_<?php echo $card; ?>[EUR]"
                                           id="woocommerce_hipayenterprise_methods_<?php echo $card; ?>_maxAmount"
                                           style=""
                                           value="<?php echo $value["maxAmount"]["EUR"]; ?>"
                                           placeholder="">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2"
                                       for="woocommerce_hipayenterprise_methods_<?php echo $card?>_currencies">
                                    <?php _e(
                                        'Currencies',
                                        "hipayenterprise"
                                    ); ?></label>
                                <div class="col-lg-8">
                                    <?php
                                    $activatedCurrencies = get_woocommerce_currency();
                                    echo '<input style="float:left" class="form-control" type="checkbox" name="woocommerce_hipayenterprise_methods_' .
                                        $methods .
                                        '_currencies_' .
                                        $card .
                                        '[]" id="woocommerce_hipayenterprise_methods_' .
                                        $card .
                                        '_currencies" style="" value="' .
                                        $activatedCurrencies .
                                        '"';
                                    if (is_array($value["currencies"]) &&
                                        array_search($activatedCurrencies, $value["currencies"]) !== false) {
                                        echo ' checked="checked"';
                                    }
                                    echo "><span style='padding:7px 10px;float:left'>" . $activatedCurrencies . "</span>";
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2"
                                       for="woocommerce_hipayenterprise_methods<?php echo $card; ?>countries[<?php echo $card; ?>]"
                                ><?php _e(
                                        'Countries',
                                        "hipayenterprise"
                                    ); ?></label>
                                <div class="col-lg-8">
                                    <select multiple
                                            class="form-control woocommerce_hipayenterprise_methods_countries"
                                            name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_countries_<?php echo $card; ?>[]"
                                            id="woocommerce_hipayenterprise_methods<?php echo $card; ?>countries[<?php echo $card; ?>]">
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
                            </div>

                        </div>
                        <?php
                        echo '</div>';
                        $first = false;
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>



