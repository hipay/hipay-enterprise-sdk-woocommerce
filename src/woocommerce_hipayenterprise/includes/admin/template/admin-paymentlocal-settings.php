<div class="form-horizontal" id="hipay-container-admin">
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_displayName<?php echo $method; ?>">
            <?php _e('Display name', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php
            if (isset($configurationPaymentMethod["displayName"][substr(get_locale(), 0, 2)])) {
                $displayName = $configurationPaymentMethod["displayName"][substr(get_locale(), 0, 2)];
            } else {
                $displayName = $configurationPaymentMethod["displayName"]['en'];
            }
            ?>
            <input class="form-control" type="text"
                name="woocommerce_hipayenterprise_methods_displayName_<?php echo $method; ?>[<?php echo substr(get_locale(), 0, 2); ?>]"
                id="woocommerce_hipayenterprise_methods_displayName<?php echo $method; ?>"
                value="<?php echo $displayName; ?>"
                placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_<?php echo $method; ?>_minAmount">
            <?php _e('Minimum order amount', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php if (isset($almaProducts) && isset($almaProducts[$method])) : ?>
                <p class="form-control-static text-info bigger-bolder-text"><?php echo esc_html($almaProducts[$method]['min']); ?> EUR</p>
                <input type="hidden"
                       name="woocommerce_hipayenterprise_methods_minAmount_<?php echo $method; ?>[EUR]"
                       value="<?php echo esc_attr($almaProducts[$method]['min']); ?>"
                />
                <input type="hidden"
                       name="woocommerce_hipayenterprise_methods_minAmount_<?php echo $method; ?>[fixed]"
                       value="1"
                />
            <?php else : ?>
                <input class="form-control" type="number"
                       name="woocommerce_hipayenterprise_methods_minAmount_<?php echo $method; ?>[EUR]"
                       id="woocommerce_hipayenterprise_methods_<?php echo $method; ?>_minAmount"
                       value="<?php echo $configurationPaymentMethod["minAmount"]["EUR"]; ?>"
                       placeholder=""
                    <?php echo $configurationPaymentMethod["minAmount"]["fixed"] ? "readonly" : ""; ?>
                >
                <input type="hidden"
                       name="woocommerce_hipayenterprise_methods_minAmount_<?php echo $method; ?>[fixed]"
                       value="<?php echo $configurationPaymentMethod["minAmount"]["fixed"]; ?>"
                />
            <?php endif; ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_<?php echo $method; ?>_maxAmount">
            <?php _e('Maximum order amount', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php if (isset($almaProducts) && isset($almaProducts[$method])) : ?>

                <p class="form-control-static text-info bigger-bolder-text"><?php echo esc_html($almaProducts[$method]['max']); ?> EUR</p>
                <input type="hidden"
                       name="woocommerce_hipayenterprise_methods_maxAmount_<?php echo $method; ?>[EUR]"
                       value="<?php echo esc_attr($almaProducts[$method]['max']); ?>"
                />
                <input type="hidden"
                       name="woocommerce_hipayenterprise_methods_maxAmount_<?php echo $method; ?>[fixed]"
                       value="1"
                />
            <?php else : ?>
                <input class="form-control" type="number"
                       name="woocommerce_hipayenterprise_methods_maxAmount_<?php echo $method; ?>[EUR]"
                       id="woocommerce_hipayenterprise_methods_<?php echo $method; ?>_maxAmount"
                       value="<?php echo $configurationPaymentMethod["maxAmount"]["EUR"]; ?>"
                       placeholder=""
                    <?php echo $configurationPaymentMethod["maxAmount"]["fixed"] ? "readonly" : ""; ?>
                >
                <input type="hidden"
                       name="woocommerce_hipayenterprise_methods_maxAmount_<?php echo $method; ?>[fixed]"
                       value="<?php echo $configurationPaymentMethod["maxAmount"]["fixed"]; ?>"
                />
            <?php endif; ?>
        </div>
    </div>
    <?php if (isset($configurationPaymentMethod["orderExpirationTime"])) : ?>
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Order expiration date', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <select name="woocommerce_hipayenterprise_methods_orderExpirationTime_<?php echo $method; ?>"
                id="woocommerce_hipayenterprise_methods_orderExpirationTime_<?php echo $method; ?>">
                <option value="3" <?php echo $configurationPaymentMethod["orderExpirationTime"] == "3" ? 'selected' : '' ?>><?php _e('3 days', "hipayenterprise"); ?></option>
                <option value="30" <?php echo $configurationPaymentMethod["orderExpirationTime"] == "30" ? 'selected' : '' ?>><?php _e('30 days', "hipayenterprise"); ?></option>
                <option value="90" <?php echo $configurationPaymentMethod["orderExpirationTime"] == "90" ? 'selected' : '' ?>><?php _e('90 days', "hipayenterprise"); ?></option>
            </select>
        </div>
    </div>
    <?php endif; ?>
    <?php if (isset($configurationPaymentMethod["merchantPromotion"])) : ?>
    <div class="form-group" for="woocommerce_hipayenterprise_methods_merchantPromotion_<?php echo $method; ?>">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_merchantPromotion_<?php echo $method; ?>">
            <?php _e('Merchant Promotion', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <input name="woocommerce_hipayenterprise_methods_merchantPromotion_<?php echo $method; ?>"
                id="woocommerce_hipayenterprise_methods_merchantPromotion_<?php echo $method; ?>"
                value="<?php echo $configurationPaymentMethod["merchantPromotion"]; ?>" placeholder="">
        </div>
    </div>
    <?php endif; ?>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_currencies__currencies">
            <?php _e('Currencies', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php if ($configurationPaymentMethod["currencySelectorReadOnly"]) : ?>
                <?php foreach ($configurationPaymentMethod["currencies"] as $currency) : ?>
                <span class="label-value col-lg-2"><?php echo $currency ?></span>
                <input type="hidden" value="<?php echo $currency ?>"
                    name="woocommerce_hipayenterprise_methods_currencies_<?php echo $method; ?>[]" />
                <?php endforeach; ?>
            <?php else : ?>
                <?php
                $activatedCurrencies = get_woocommerce_currency();
                echo '<input style="float:left" class="form-control" type="checkbox" name="woocommerce_hipayenterprise_methods_currencies_' .
                    $method . '[]" id="woocommerce_hipayenterprise_methods_currencies_' . '_currencies" style="" value="' .
                    $activatedCurrencies .
                    '"';
                if (is_array($configurationPaymentMethod["currencies"]) &&
                    array_search($activatedCurrencies, $configurationPaymentMethod["currencies"]) !== false) {
                    echo ' checked="checked"';
                }
                echo "><span style='padding:7px 10px;float:left'>" . $activatedCurrencies . "</span>";
                ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods<?php echo $method; ?>countries">
            <?php _e('Countries', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php if ($configurationPaymentMethod["countrySelectorReadOnly"]) : ?>
                <?php foreach ($configurationPaymentMethod["countries"] as $country) : ?>
                <span class="label-value col-lg-2"><?php echo $country ?></span>
                <input type="hidden" value="<?php echo $country ?>"
                    name="woocommerce_hipayenterprise_methods_countries_<?php echo $method; ?>[]" />
                <?php endforeach; ?>
            <?php else : ?>
            <select multiple class="form-control woocommerce_hipayenterprise_methods_countries"
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
    <?php if ($configurationPaymentMethod["productCode"] === 'paypal') : ?>
    <div class="form-group">
        <label class="control-label col-lg-2"></label>
        <div class="col-lg-8">
            <?php if (empty($isPayPalV2)) : ?>
                <p class="paypalv2 alert-info">
                    <b><?php _e('NEW'
                        ,"hipayenterprise")?></b><br/>
                    <?php _e('The new PayPal integration allows you to pay with PayPal without redirection and to offer payment with installments.'
                        ,"hipayenterprise")?><br/>
                    <?php _e('Available by'
                        ,"hipayenterprise")?>
                    <b><?php _e('invitation only'
                        ,"hipayenterprise")?></b>
                    <?php _e('at this time, please contact our support or your account manager for more information.'
                        ,"hipayenterprise")?>
                </p>

            <?php endif;?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_buttonShape<?php echo $method; ?>">
            <?php _e('Button Shape', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php
            $buttonShape = $configurationPaymentMethod["buttonShape"]??'';
            ?>
            <select class="form-control <?php if (empty($isPayPalV2)) echo 'readonly'?>" name="woocommerce_hipayenterprise_methods_buttonShape_<?php echo $method; ?>"
                    id="woocommerce_hipayenterprise_methods_buttonShape<?php echo $method; ?>"
            >
                <option value="rect" <?php if($buttonShape == "rect"){ echo "selected"; } ?>>
                    <?php _e('Rectangular', "hipayenterprise"); ?>
                </option>
                <option value="pill" <?php if($buttonShape == "pill"){ echo "selected"; } ?>>
                    <?php _e('Rounded', "hipayenterprise"); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_buttonLabel<?php echo $method; ?>">
            <?php _e('Button Label', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php
            $buttonLabel = $configurationPaymentMethod["buttonLabel"]??'';
            ?>
            <select class="form-control <?php if (empty($isPayPalV2)) echo 'readonly'?>" name="woocommerce_hipayenterprise_methods_buttonLabel_<?php echo $method; ?>"
                    id="woocommerce_hipayenterprise_methods_buttonLabel<?php echo $method; ?>"
            >
                <option value="paypal" <?php if($buttonLabel == "paypal"){ echo "selected"; } ?>>
                    <?php _e('Paypal', "hipayenterprise"); ?>
                </option>
                <option value="pay" <?php if($buttonLabel == "pay"){ echo "selected"; } ?>>
                    <?php _e('Pay', "hipayenterprise"); ?>
                </option>
                <option value="subscribe" <?php if($buttonLabel == "subscribe"){ echo "selected"; } ?>>
                    <?php _e('Subscribe', "hipayenterprise"); ?>
                </option>
                <option value="checkout" <?php if($buttonLabel == "checkout"){ echo "selected"; } ?>>
                    <?php _e('Checkout', "hipayenterprise"); ?>
                </option>
                <option value="buynow" <?php if($buttonLabel == "buynow"){ echo "selected"; } ?>>
                    <?php _e('Buy Now', "hipayenterprise"); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_buttonColor<?php echo $method; ?>">
            <?php _e('Button Color', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php
            $buttonColor = $configurationPaymentMethod["buttonColor"]??'';
            ?>
            <select class="form-control <?php if (empty($isPayPalV2)) echo 'readonly'?>" name="woocommerce_hipayenterprise_methods_buttonColor_<?php echo $method; ?>"
                    id="woocommerce_hipayenterprise_methods_buttonColor<?php echo $method; ?>"
            >
                <option value="gold" <?php if($buttonColor == "gold"){ echo "selected"; } ?>>
                    <?php _e('Gold', "hipayenterprise"); ?>
                </option>
                <option value="blue" <?php if($buttonColor == "blue"){ echo "selected"; } ?>>
                    <?php _e('Blue', "hipayenterprise"); ?>
                </option>
                <option value="black" <?php if($buttonColor == "black"){ echo "selected"; } ?>>
                    <?php _e('Black', "hipayenterprise"); ?>
                </option>
                <option value="silver" <?php if($buttonColor == "silver"){ echo "selected"; } ?>>
                    <?php _e('Silver', "hipayenterprise"); ?>
                </option>
                <option value="white" <?php if($buttonColor == "white"){ echo "selected"; } ?>>
                    <?php _e('White', "hipayenterprise"); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_buttonHeight<?php echo $method; ?>">
            <?php _e('Button height', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">

            <?php
            $buttonHeight = $configurationPaymentMethod["buttonHeight"]??'';
            ?>
            <input class="form-control <?php if (empty($isPayPalV2)) echo 'readonly'?>" type="number"
                   name="woocommerce_hipayenterprise_methods_buttonHeight_<?php echo $method; ?>"
                   id="woocommerce_hipayenterprise_methods_buttonHeight<?php echo $method; ?>"
                   value="<?php echo $buttonHeight; ?>"
                   placeholder="" min="25" max="55">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2" for="woocommerce_hipayenterprise_methods_bnpl<?php echo $method; ?>">
            <?php _e('Pay Later Button', "hipayenterprise"); ?>
        </label>
        <div class="col-lg-8">
            <?php
            $bnpl = $configurationPaymentMethod["bnpl"] ?? false;
            ?>
            <input class="form-control <?php if (empty($isPayPalV2)) echo 'readonly'?>" type="checkbox"
                   name="woocommerce_hipayenterprise_methods_bnpl_<?php echo $method; ?>"
                   id="woocommerce_hipayenterprise_methods_bnpl<?php echo $method; ?>"
                   style=""
                   value="1" <?php if ($bnpl) {
                echo 'checked="checked"';
            } ?> >
            <p class="alert-info">
                <?php _e('The "Buy now, Pay later" feature is only available if the store currency is euros and if the basket amount is between 30 and 2000.',"hipayenterprise"); ?>
            </p>
        </div>
    </div>
    <?php endif; ?>
</div>
