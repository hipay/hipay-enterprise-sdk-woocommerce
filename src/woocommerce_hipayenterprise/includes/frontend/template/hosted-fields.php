<div class="hipay-container-hosted-fields" id="hipayHF-container">
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
    <div class="hipay-row">
        <div class="hipay-element-container">
            <div id="hipay-help-cvc"></div>
        </div>
    </div>
    <div id="error-js" style="display:none" class="woocommerce-hipay-error">
        <ul>
            <li class="error"></li>
        </ul>
    </div>
    <input type="hidden" class="payment-method-hidden-fields" id="payment-product" name="payment-product" value=""/>
    <input type="hidden" class="payment-method-hidden-fields" id="card-token" name="card-token" value=""/>
</div>
<script type="text/javascript">
    /* <![CDATA[ */
    var hipay_config_current_cart = {
        'activatedCreditCard':
            [
                <?php
                echo $activatedCreditCard;
                ?>
            ],
        'defaultFirstname': '',
        'defaultLastname': ''
    };
    /* ]]> */
</script>


