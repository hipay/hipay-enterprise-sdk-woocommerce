<div class="hipay-container-hosted-fields" id="hipay-container-hosted-fields-woocomerce">
    <div class="hipay-row">
        <div class="hipay-field-container">
            <div class="hipay-field" id="hipay-card-holder"></div>
            <label class="hipay-label" for="hipay-card-holder">
                <?php _e('Card Owner Name', 'hipayenterprise'); ?>
            </label>
            <div class="hipay-baseline"></div>
        </div>
    </div>
    <div class="hipay-row">
        <div class="hipay-field-container">
            <div class="hipay-field" id="hipay-card-number"></div>
            <label class="hipay-label" for="hipay-card-number">
                <?php _e('Credit Card Number', 'hipayenterprise'); ?>
            </label>
            <div class="hipay-baseline"></div>
        </div>
    </div>
    <div class="hipay-row">
        <div class="hipay-field-container hipay-field-container-half">
            <div class="hipay-field" id="hipay-date-expiry"></div>
            <label class="hipay-label" for="hipay-date-expiry">
                <?php _e('Expiration Date', 'hipayenterprise'); ?>
            </label>
            <div class="hipay-baseline"></div>
        </div>
        <div class="hipay-field-container hipay-field-container-half">
            <div class="hipay-field" id="hipay-cvc"></div>
            <label class="hipay-label" for="hipay-cvc">
                <?php _e('Card Verification Number', 'hipayenterprise'); ?>
            </label>
            <div class="hipay-baseline"></div>
        </div>
    </div>
    <div id="error-js" style="display:none" class="woocommerce-hipay-error">
        <ul>
            <li class="error"></li>
        </ul>
    </div>
   <input type="hidden" class="payment-method-hidden-fields" id="card-token" name="card-token" value="" />
   <input type="hidden" class="payment-method-hidden-fields" id="card-brand" name="card-brand" value="" />
   <input type="hidden" class="payment-method-hidden-fields" id="card-pan" name="card-pan" value="" />
    <input type="hidden" class="payment-method-hidden-fields" id="card-holder" name="card-holder" value="" />
    <input type="hidden" class="payment-method-hidden-fields" id="card-expiry-month" name="card-expiry-month" value="" />
    <input type="hidden" class="payment-method-hidden-fields" id="card-expiry-year" name="card-expiry-year" value="" />
    <input type="hidden" class="payment-method-hidden-fields" id="card-issuer" name="card-issuer" value="" />
    <input type="hidden" class="payment-method-hidden-fields" id="card-country" name="card-country" value="" />
</div>