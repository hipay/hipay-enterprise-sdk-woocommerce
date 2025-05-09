<div class="hipay-container-hosted-fields woocommerce-SavedPaymentMethods-saveNew" id="hipayHF-container-card">
    <?php if($useOneClick):?>
        <div class="one-click hipay-form-row">
            <div class="hipay-field-container">
                <div class="hipay-field" id="hipay-card-saved-cards"></div>
            </div>
        </div>
    <?php endif;?>
    <div id="hipayHF-card-form-container">
        <div class="hipay-form-row">
            <div class="hipay-field-container">
                <div class="hipay-field" id="hipay-card-field-cardHolder"></div>
                <label class="hipay-field-label" for="hipay-card-field-cardHolder">
                    <?php _e('Fullname', "hipayenterprise"); ?>
                </label>
                <div class="hipay-field-baseline"></div>
                <div class="hipay-field-error" data-hipay-id='hipay-card-field-error-cardHolder'></div>
            </div>
        </div>
        <div class="hipay-form-row">
            <div class="hipay-field-container">
                <div class="hipay-field" id="hipay-card-field-cardNumber"></div>
                <label class="hipay-field-label" for="hipay-card-field-cardNumber">
                    <?php _e('Card Number', "hipayenterprise"); ?>
                </label>
                <div class="hipay-field-baseline"></div>
                <div class="hipay-field-error" data-hipay-id='hipay-card-field-error-cardNumber'></div>
            </div>
        </div>
        <div class="hipay-form-row">
            <div class="hipay-field-container hipay-field-container-half">
                <div class="hipay-field" id="hipay-card-field-expiryDate"></div>
                <label class="hipay-field-label" for="hipay-card-field-expiryDate">
                    <?php _e('Expiry Date', "hipayenterprise"); ?>
                </label>
                <div class="hipay-field-baseline"></div>
                <div class="hipay-field-error" data-hipay-id='hipay-card-field-error-expiryDate'></div>
            </div>
            <div class="hipay-field-container hipay-field-container-half">
                <div class="hipay-field" id="hipay-card-field-cvc"></div>
                <label class="hipay-field-label" for="hipay-card-field-cvc">
                    <?php _e('CVC', "hipayenterprise"); ?>
                </label>
                <div class="hipay-field-baseline"></div>
                <div class="hipay-field-error" data-hipay-id='hipay-card-field-error-cvc'></div>
            </div>
        </div>
        <div class="hipay-form-row">
            <div class="hipay-element-container">
                <div id="hipay-help-cvc"  data-hipay-id='hipay-help-cvc'></div>
            </div>
        </div>
        <?php if($useOneClick):?>
            <div class="hipay-form-row">
                <div class="hipay-field-container hipay-saved-card">
                    <div class="hipay-field" id="hipay-saved-card-btn"></div>
                </div>
            </div>
        <?php endif;?>
        <div id="error-js-card" style="display:none" class="woocommerce-hipay-error"></div>
    </div>
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
        'savedCreditCards': <?php  echo json_encode($savedCreditCards,JSON_PRETTY_PRINT)?>,
        'defaultFirstname': '',
        'defaultLastname': ''
    };
    /* ]]> */
</script>


