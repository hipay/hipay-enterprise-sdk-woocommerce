<div class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Minimum order amount', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_minAmount[<?php echo $card; ?>][EUR]"
                   id="woocommerce_hipayenterprise_methods_<?php echo $card; ?>_minAmount"
                   style=""
                   value="<?php echo $configurationPaymentMethod["minAmount"]["EUR"]; ?>"
                   placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-2"><?php _e('Maximum order amount', "hipayenterprise"); ?></label>
        <div class="col-lg-8">
            <input class="form-control" type="text"
                   name="woocommerce_hipayenterprise_methods_<?php echo $methods; ?>_maxAmount[<?php echo $card; ?>][EUR]"
                   id="woocommerce_hipayenterprise_methods_<?php echo $card; ?>_maxAmount"
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
                            name="woocommerce_hipayenterprise_methods_currencies[<?php echo $method; ?>][]"
                    />
                <?php endforeach; ?>
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
                            name="woocommerce_hipayenterprise_methods_countries[<?php echo $method; ?>][]"
                    />
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>


