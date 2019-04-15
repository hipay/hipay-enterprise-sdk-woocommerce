<div
    <?php if (!$token->get_is_default()): ?> style="display: none" <?php endif; ?>
        id="hipay-token-force-cvv-<?php echo $token->get_id() ?>"
        class="hipay-token-force-cvv"
>
    <input id="hipay-token-value-<?php echo $token->get_id(); ?>" type="hidden" value="<?php echo $token->get_token() ?>"/>
    <input id="hipay-token-year-<?php echo $token->get_id() ?>" type="hidden" value="<?php echo $token->get_expiry_year() ?>"/>
    <input id="hipay-token-month-<?php echo $token->get_id() ?>" type="hidden" value="<?php echo $token->get_expiry_month() ?>"/>
    <input id="hipay-token-type-<?php echo $token->get_id() ?>" type="hidden" value="<?php echo $token->get_payment_product() ?>"/>
    <div id="hipay-container-oneclick-<?php echo $token->get_id() ?>" class="hipay-form-row hipay-container-oneclick">
        <div class="hipay-field-container hipay-field-container-half ">
            <input type="text" class="hipay-field hipay-field-oneclick hipay-cvv-oneclick" id="hipay-token-cvv-<?php echo $token->get_id() ?>" placeholder="<?php _e('CVC', "hipayenterprise"); ?>"/>
            <div class="hipay-field-baseline"></div>
            <div class="hipay-field-error" data-hipay-id='hipay-oneclick-<?php echo $token->get_id() ?>-field-error-cvc'></div>
        </div>
        <button id="hipay-token-update-<?php echo $token->get_id() ?>" class="hipay-token-update" >OK</button>
    </div>
    <div id="error-js-oneclick-<?php echo $token->get_id() ?>" style="display:none" class="woocommerce-hipay-error"></div>
    <div id="success-js-oneclick-<?php echo $token->get_id() ?>" style="display:none" class="woocommerce-hipay-success"></div>
</div>
