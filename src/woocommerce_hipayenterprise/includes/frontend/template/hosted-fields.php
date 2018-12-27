<div class="hipay-container-hosted-fields" id="hipayHF-container">
    <div class="hipay-row">
        <div class="hipay-field-container">
            <div class="hipay-field" id="hipay-field-cardHolder"></div>
            <label class="hipay-label" for="hipay-field-cardHolder">
                <?php _e('Fullname', "hipayenterprise"); ?>
            </label>
            <div class="hipay-baseline"></div>
        </div>
    </div>
    <div class="hipay-row">
        <div class="hipay-field-container">
            <div class="hipay-field" id="hipay-field-cardNumber"></div>
            <label class="hipay-label" for="hipay-field-cardNumber">
                <?php _e('Card Number', "hipayenterprise"); ?>
            </label>
            <div class="hipay-baseline"></div>
        </div>
    </div>
    <div class="hipay-row">
        <div class="hipay-field-container hipay-field-container-half">
            <div class="hipay-field" id="hipay-field-expiryDate"></div>
            <label class="hipay-label" for="hipay-field-expiryDate">
                <?php _e('Expiry Date', "hipayenterprise"); ?>
            </label>
            <div class="hipay-baseline"></div>
        </div>
        <div class="hipay-field-container hipay-field-container-half">
            <div class="hipay-field" id="hipay-field-cvc"></div>
            <label class="hipay-label" for="hipay-field-cvc">
                <?php _e('CVC', "hipayenterprise"); ?>
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
    <input type="hidden" class="payment-method-hidden-fields" id="card-holder" name="card-holder" value=""/>
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


